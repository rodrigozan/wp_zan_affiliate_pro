<?php
/**
 * Table of Contents
 *
 * Auto-generates TOC from headings in post content.
 * Shortcode: [zap_toc] — or auto-insert via option.
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

class ZAP_TOC {

    private static ?self $instance = null;

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        add_shortcode( 'zap_toc', [ $this, 'shortcode' ] );
        add_filter( 'the_content', [ $this, 'process_content' ], 10 );
    }

    public function process_content( string $content ): string {
        if ( ! is_singular() || is_admin() ) return $content;

        // Add IDs to headings
        $content = $this->add_heading_ids( $content );

        // Auto-insert TOC if enabled
        $post_id   = get_the_ID();
        $auto_toc  = zap_option( 'auto_toc', false );
        $post_meta = get_post_meta( $post_id, '_zap_show_toc', true );

        if ( $auto_toc || $post_meta === '1' ) {
            // Insert after first paragraph if [zap_toc] not already in content
            if ( ! has_shortcode( $content, 'zap_toc' ) ) {
                $toc = $this->build_toc( $content );
                if ( $toc ) {
                    // Insert after first </p>
                    $pos     = strpos( $content, '</p>' );
                    $content = $pos !== false
                        ? substr_replace( $content, '</p>' . $toc, $pos, 4 )
                        : $toc . $content;
                }
            }
        }

        return $content;
    }

    public function shortcode( array $atts ): string {
        global $post;
        if ( ! $post ) return '';
        $content = $post->post_content;
        $content = $this->add_heading_ids( $content );
        return $this->build_toc( $content, $atts ) ?: '';
    }

    private function add_heading_ids( string $content ): string {
        $ids = [];
        return preg_replace_callback(
            '/<(h[2-4])([^>]*)>(.*?)<\/h[2-4]>/is',
            function( array $m ) use ( &$ids ): string {
                $tag    = $m[1];
                $attrs  = $m[2];
                $text   = wp_strip_all_tags( $m[3] );

                // Already has id
                if ( preg_match( '/\bid=["\'][^"\']+["\']/i', $attrs ) ) {
                    return $m[0];
                }

                $id = $this->slugify( $text );
                // Ensure uniqueness
                if ( in_array( $id, $ids, true ) ) {
                    $count = 1;
                    while ( in_array( $id . '-' . $count, $ids, true ) ) $count++;
                    $id .= '-' . $count;
                }
                $ids[] = $id;

                return "<$tag$attrs id=\"$id\">{$m[3]}</$tag>";
            },
            $content
        );
    }

    private function build_toc( string $content, array $atts = [] ): string {
        $atts = shortcode_atts( [
            'title'     => __( 'Neste Artigo', 'zan-affiliate-pro' ),
            'min_items' => 3,
            'max_depth' => 3,
            'numbered'  => 'yes',
        ], $atts );

        preg_match_all( '/<h([2-4])[^>]*id="([^"]+)"[^>]*>(.*?)<\/h[2-4]>/is', $content, $matches, PREG_SET_ORDER );

        if ( count( $matches ) < (int) $atts['min_items'] ) return '';

        $numbered = $atts['numbered'] === 'yes';
        $list_tag = $numbered ? 'ol' : 'ul';

        $html  = '<div class="zap-toc" id="zap-toc">';
        if ( $atts['title'] ) {
            $html .= '<div class="zap-toc-title">';
            $html .= '<span>' . esc_html( $atts['title'] ) . '</span>';
            $html .= '<button class="zap-toc-toggle" aria-label="' . esc_attr__( 'Mostrar/ocultar', 'zan-affiliate-pro' ) . '">&#8722;</button>';
            $html .= '</div>';
        }
        $html .= '<nav class="zap-toc-nav" aria-label="' . esc_attr__( 'Índice', 'zan-affiliate-pro' ) . '">';
        $html .= "<$list_tag class=\"zap-toc-list\">";

        $prev_level = 2;
        foreach ( $matches as $m ) {
            $level = (int) $m[1];
            $id    = $m[2];
            $text  = wp_strip_all_tags( $m[3] );

            if ( $level > (int) $atts['max_depth'] + 1 ) continue;

            if ( $level > $prev_level ) {
                $html .= "<$list_tag class=\"zap-toc-sub\">";
            } elseif ( $level < $prev_level ) {
                $html .= str_repeat( "</$list_tag></li>", $prev_level - $level );
            }

            $html .= '<li class="zap-toc-item zap-toc-level-' . esc_attr( $level ) . '">';
            $html .= '<a href="#' . esc_attr( $id ) . '">' . esc_html( $text ) . '</a>';

            $prev_level = $level;
        }

        // Close any open sub-lists
        if ( $prev_level > 2 ) {
            $html .= str_repeat( "</$list_tag></li>", $prev_level - 2 );
        }

        $html .= "</$list_tag></nav></div>";
        return $html;
    }

    private function slugify( string $text ): string {
        $text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
        $text = strtolower( $text );
        $text = preg_replace( '/[^a-z0-9\s\-]/', '', $text );
        $text = preg_replace( '/[\s\-]+/', '-', $text );
        return trim( $text, '-' );
    }
}

ZAP_TOC::instance();
