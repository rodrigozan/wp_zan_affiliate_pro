<?php
/**
 * XML Sitemap
 *
 * Generates /sitemap.xml with posts, pages, categories.
 * Pings Google and Bing on publish.
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

class ZAP_Sitemap {

    private static ?self $instance = null;

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        add_action( 'init',                    [ $this, 'add_rewrite' ] );
        add_action( 'template_redirect',       [ $this, 'output_sitemap' ] );
        add_action( 'publish_post',            [ $this, 'ping_search_engines' ] );
        add_action( 'publish_page',            [ $this, 'ping_search_engines' ] );
        add_filter( 'robots_txt',              [ $this, 'add_sitemap_to_robots' ], 10, 2 );
    }

    public function add_rewrite(): void {
        add_rewrite_rule( '^sitemap\.xml$', 'index.php?zap_sitemap=1', 'top' );
        add_rewrite_tag( '%zap_sitemap%', '1' );
    }

    public function output_sitemap(): void {
        if ( ! get_query_var( 'zap_sitemap' ) ) return;

        // Check if Yoast/etc. already handles sitemaps
        if ( class_exists( 'WPSEO_Sitemaps' ) || class_exists( 'RankMath\\Sitemap\\Sitemap' ) ) {
            status_header( 302 );
            wp_redirect( home_url( '/sitemap_index.xml' ) );
            exit;
        }

        header( 'Content-Type: application/xml; charset=UTF-8' );
        header( 'X-Robots-Tag: noindex' );

        $items = $this->collect_items();

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        echo '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        foreach ( $items as $item ) {
            echo "<url>\n";
            echo '  <loc>' . esc_url( $item['loc'] ) . "</loc>\n";
            if ( ! empty( $item['lastmod'] ) ) {
                echo '  <lastmod>' . esc_html( $item['lastmod'] ) . "</lastmod>\n";
            }
            echo '  <changefreq>' . esc_html( $item['changefreq'] ?? 'weekly' ) . "</changefreq>\n";
            echo '  <priority>' . esc_html( $item['priority'] ?? '0.6' ) . "</priority>\n";
            if ( ! empty( $item['image'] ) ) {
                echo "  <image:image>\n";
                echo '    <image:loc>' . esc_url( $item['image'] ) . "</image:loc>\n";
                echo "  </image:image>\n";
            }
            echo "</url>\n";
        }

        echo '</urlset>';
        exit;
    }

    private function collect_items(): array {
        $items = [];

        // Homepage
        $items[] = [
            'loc'        => home_url( '/' ),
            'lastmod'    => date( 'c' ),
            'changefreq' => 'daily',
            'priority'   => '1.0',
        ];

        // Posts
        $posts = get_posts( [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'numberposts'    => -1,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ] );
        foreach ( $posts as $post ) {
            $item = [
                'loc'        => get_permalink( $post ),
                'lastmod'    => get_the_modified_date( 'c', $post ),
                'changefreq' => 'weekly',
                'priority'   => '0.8',
            ];
            if ( has_post_thumbnail( $post->ID ) ) {
                $item['image'] = get_the_post_thumbnail_url( $post->ID, 'full' );
            }
            $items[] = $item;
        }

        // Pages
        $pages = get_pages( [ 'post_status' => 'publish' ] );
        foreach ( $pages as $page ) {
            $noindex = get_post_meta( $page->ID, '_zap_noindex', true );
            if ( $noindex ) continue;
            $items[] = [
                'loc'        => get_permalink( $page ),
                'lastmod'    => get_the_modified_date( 'c', $page ),
                'changefreq' => 'monthly',
                'priority'   => '0.6',
            ];
        }

        // Categories
        $categories = get_categories( [ 'hide_empty' => true ] );
        foreach ( $categories as $cat ) {
            $items[] = [
                'loc'        => get_category_link( $cat->term_id ),
                'changefreq' => 'weekly',
                'priority'   => '0.5',
            ];
        }

        return $items;
    }

    public function ping_search_engines(): void {
        if ( ! zap_option( 'sitemap_ping', true ) ) return;

        $sitemap = urlencode( home_url( '/sitemap.xml' ) );
        $engines = [
            "https://www.google.com/ping?sitemap=$sitemap",
            "https://www.bing.com/ping?sitemap=$sitemap",
        ];
        foreach ( $engines as $url ) {
            wp_remote_get( $url, [ 'blocking' => false, 'timeout' => 5 ] );
        }
    }

    public function add_sitemap_to_robots( string $output, bool $public ): string {
        if ( ! $public ) return $output;
        return $output . "\nSitemap: " . home_url( '/sitemap.xml' ) . "\n";
    }
}

ZAP_Sitemap::instance();
