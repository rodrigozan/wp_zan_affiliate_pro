<?php
/**
 * Schema.org JSON-LD
 *
 * Outputs structured data for Article, FAQ, Product, BreadcrumbList, WebSite.
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

class ZAP_Schema {

    private static ?self $instance = null;

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        add_action( 'wp_head', [ $this, 'output' ], 5 );
        add_shortcode( 'zap_faq', [ $this, 'faq_shortcode' ] );
    }

    public function output(): void {
        $schemas = [];

        // WebSite (sitelinks search)
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => get_bloginfo( 'name' ),
            'url'      => home_url(),
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => home_url( '/?s={search_term_string}' ),
                'query-input' => 'required name=search_term_string',
            ],
        ];

        // Organization
        $logo = get_custom_logo();
        $logo_url = '';
        if ( has_custom_logo() ) {
            $logo_id  = get_theme_mod( 'custom_logo' );
            $logo_url = wp_get_attachment_image_url( $logo_id, 'full' );
        }
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => get_bloginfo( 'name' ),
            'url'      => home_url(),
            'logo'     => $logo_url ?: '',
            'sameAs'   => array_filter( [
                zap_option( 'social_facebook' ),
                zap_option( 'social_instagram' ),
                zap_option( 'social_twitter' ),
                zap_option( 'social_youtube' ),
                zap_option( 'social_linkedin' ),
            ] ),
        ];

        // Article (singular posts)
        if ( is_singular( 'post' ) ) {
            $schemas[] = $this->article_schema();
        }

        // Breadcrumb
        if ( function_exists( 'zap_get_breadcrumbs' ) ) {
            $bc = zap_get_breadcrumb_schema();
            if ( $bc ) $schemas[] = $bc;
        }

        foreach ( $schemas as $schema ) {
            printf(
                '<script type="application/ld+json">%s</script>' . "\n",
                wp_json_encode( array_filter( $schema ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT )
            );
        }
    }

    private function article_schema(): array {
        global $post;
        $author_id   = $post->post_author;
        $author_name = get_the_author_meta( 'display_name', $author_id );
        $img         = get_the_post_thumbnail_url( $post->ID, 'zap-wide' );

        return [
            '@context'         => 'https://schema.org',
            '@type'            => 'Article',
            'headline'         => get_the_title(),
            'description'      => wp_trim_words( get_the_excerpt(), 25 ),
            'datePublished'    => get_the_date( 'c' ),
            'dateModified'     => get_the_modified_date( 'c' ),
            'url'              => get_permalink(),
            'author'           => [ '@type' => 'Person', 'name' => $author_name ],
            'publisher'        => [
                '@type' => 'Organization',
                'name'  => get_bloginfo( 'name' ),
                'logo'  => [ '@type' => 'ImageObject', 'url' => get_header_image() ?: '' ],
            ],
            'image' => $img ? [
                '@type'  => 'ImageObject',
                'url'    => $img,
                'width'  => 1280,
                'height' => 640,
            ] : null,
            'mainEntityOfPage' => [ '@type' => 'WebPage', '@id' => get_permalink() ],
            'wordCount'        => str_word_count( wp_strip_all_tags( $post->post_content ) ),
        ];
    }

    // ── FAQ Shortcode ─────────────────────────────────────────────────────────

    /**
     * Usage:
     * [zap_faq]
     * [item question="O que é X?" answer="X é..."]
     * [item question="Por que usar X?" answer="Porque..."]
     * [/zap_faq]
     */
    public function faq_shortcode( array $atts, ?string $content = null ): string {
        if ( ! $content ) return '';

        // Extract items
        preg_match_all(
            '/\[item\s+question="([^"]+)"\s+answer="([^"]+)"\s*\/?\]/i',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        if ( empty( $matches ) ) return '';

        $items    = [];
        $html     = '<div class="zap-faq" itemscope itemtype="https://schema.org/FAQPage">';

        foreach ( $matches as $m ) {
            $q      = wp_kses_post( $m[1] );
            $a      = wp_kses_post( $m[2] );
            $items[] = [ 'q' => $q, 'a' => $a ];
            $html   .= '<div class="zap-faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">';
            $html   .= '<button class="zap-faq-question" itemprop="name" aria-expanded="false">';
            $html   .= esc_html( $q );
            $html   .= '<span class="zap-faq-icon" aria-hidden="true">+</span>';
            $html   .= '</button>';
            $html   .= '<div class="zap-faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer" hidden>';
            $html   .= '<div itemprop="text">' . wp_kses_post( $a ) . '</div>';
            $html   .= '</div>';
            $html   .= '</div>';
        }

        $html .= '</div>';

        // Inject FAQ JSON-LD
        $schema = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => array_map( fn( $item ) => [
                '@type'          => 'Question',
                'name'           => $item['q'],
                'acceptedAnswer' => [ '@type' => 'Answer', 'text' => $item['a'] ],
            ], $items ),
        ];
        $html .= '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>';

        return $html;
    }
}

ZAP_Schema::instance();
