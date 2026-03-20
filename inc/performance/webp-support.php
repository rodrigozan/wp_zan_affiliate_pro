<?php
/**
 * WebP Support
 *
 * - Serves .webp images automatically when browser supports it
 * - Converts uploaded images to WebP (requires GD/Imagick with WebP support)
 * - Adds <picture> element wrapping for graceful fallback
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

class ZAP_WebP {

    private static ?self $instance = null;

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        // Convert on upload
        add_filter( 'wp_generate_attachment_metadata', [ $this, 'generate_webp_on_upload' ], 10, 2 );
        // Serve WebP via srcset
        add_filter( 'wp_calculate_image_srcset',       [ $this, 'add_webp_srcset' ], 10, 5 );
        // Wrap img in <picture> with WebP source
        add_filter( 'the_content',                     [ $this, 'wrap_images_in_picture' ], 15 );
        // Cleanup WebP on attachment delete
        add_action( 'delete_attachment',               [ $this, 'cleanup_webp' ] );
    }

    /** Generate WebP version alongside uploaded image */
    public function generate_webp_on_upload( array $metadata, int $attachment_id ): array {
        if ( ! $this->webp_supported() ) return $metadata;

        $file = get_attached_file( $attachment_id );
        if ( ! $file ) return $metadata;

        $type = mime_content_type( $file );
        if ( ! in_array( $type, [ 'image/jpeg', 'image/png', 'image/gif' ], true ) ) {
            return $metadata;
        }

        $this->convert_to_webp( $file );

        // Also convert generated sizes
        if ( ! empty( $metadata['sizes'] ) ) {
            $upload_dir = wp_upload_dir();
            $dir        = dirname( $file );
            foreach ( $metadata['sizes'] as $size ) {
                $size_file = $dir . '/' . $size['file'];
                if ( file_exists( $size_file ) ) {
                    $this->convert_to_webp( $size_file );
                }
            }
        }

        return $metadata;
    }

    private function convert_to_webp( string $source ): bool {
        $dest = $source . '.webp';
        if ( file_exists( $dest ) ) return true;

        $type = mime_content_type( $source );

        if ( extension_loaded( 'imagick' ) ) {
            try {
                $img = new \Imagick( $source );
                $img->setImageFormat( 'webp' );
                $img->setImageCompressionQuality( 82 );
                $img->stripImage();
                return $img->writeImage( $dest );
            } catch ( \Exception $e ) {
                // Fall through to GD
            }
        }

        if ( function_exists( 'imagewebp' ) ) {
            switch ( $type ) {
                case 'image/jpeg':
                    $img = imagecreatefromjpeg( $source );
                    break;
                case 'image/png':
                    $img = imagecreatefrompng( $source );
                    break;
                default:
                    return false;
            }
            if ( ! $img ) return false;
            $result = imagewebp( $img, $dest, 82 );
            imagedestroy( $img );
            return $result;
        }

        return false;
    }

    /** Add WebP to srcset */
    public function add_webp_srcset( array $sources, array $size_array, string $image_src, array $image_meta, int $attachment_id ): array {
        if ( ! $this->browser_accepts_webp() ) return $sources;

        foreach ( $sources as $width => $source ) {
            $webp = $source['url'] . '.webp';
            if ( $this->file_exists_by_url( $webp ) ) {
                $sources[ $width ]['url'] = $webp;
            }
        }
        return $sources;
    }

    /** Wrap images in <picture> with WebP source */
    public function wrap_images_in_picture( string $content ): string {
        if ( is_admin() || is_feed() ) return $content;
        if ( ! $this->webp_supported() ) return $content;

        return preg_replace_callback(
            '/<img([^>]+src=["\']([^"\']+\.(jpe?g|png))["\'][^>]*)>/i',
            function( array $m ) {
                $full_tag = $m[0];
                $src      = $m[2];
                $webp_src = $src . '.webp';

                if ( ! $this->file_exists_by_url( $webp_src ) ) return $full_tag;

                return '<picture>'
                    . '<source srcset="' . esc_url( $webp_src ) . '" type="image/webp">'
                    . $full_tag
                    . '</picture>';
            },
            $content
        );
    }

    public function cleanup_webp( int $attachment_id ): void {
        $file = get_attached_file( $attachment_id );
        if ( $file && file_exists( $file . '.webp' ) ) {
            unlink( $file . '.webp' );
        }
    }

    private function webp_supported(): bool {
        return function_exists( 'imagewebp' ) || extension_loaded( 'imagick' );
    }

    private function browser_accepts_webp(): bool {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains( $accept, 'image/webp' );
    }

    private function file_exists_by_url( string $url ): bool {
        $upload_dir = wp_upload_dir();
        $path       = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $url );
        return file_exists( $path );
    }
}

ZAP_WebP::instance();
