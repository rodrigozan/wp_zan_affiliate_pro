<?php
/**
 * SEO Meta Tags
 *
 * Outputs <title>, meta description, Open Graph, Twitter Card.
 * Designed to coexist with (but override) Yoast/AIOSEO if active.
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

class ZAP_Meta_Tags {

    private static ?self $instance = null;

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        // Only run if no other SEO plugin is active
        add_action( 'wp_head', [ $this, 'output_meta' ], 1 );
        add_filter( 'document_title_parts', [ $this, 'title_parts' ], 20 );
        add_action( 'add_meta_boxes', [ $this, 'meta_boxes' ] );
        add_action( 'save_post', [ $this, 'save_meta' ] );
    }

    private function seo_plugin_active(): bool {
        return class_exists( 'WPSEO_Options' )   // Yoast
            || class_exists( 'AIOSEOP_Class' )   // AIOSEO
            || class_exists( 'RankMath' );        // Rank Math
    }

    public function output_meta(): void {
        if ( $this->seo_plugin_active() ) return;

        $post_id = get_the_ID();
        $desc    = $post_id ? get_post_meta( $post_id, '_zap_seo_desc', true ) : '';
        if ( ! $desc && is_singular() ) {
            $desc = wp_trim_words( get_the_excerpt(), 25 );
        }
        if ( ! $desc ) {
            $desc = get_bloginfo( 'description' );
        }

        $title   = wp_get_document_title();
        $img_url = $post_id && has_post_thumbnail( $post_id )
            ? get_the_post_thumbnail_url( $post_id, 'zap-wide' )
            : ( get_header_image() ?: '' );
        $url     = is_singular() ? get_permalink() : ( is_home() ? home_url() : '' );
        $type    = is_singular() ? 'article' : 'website';
        $site    = get_bloginfo( 'name' );
        $twitter = zap_option( 'twitter_handle', '' );

        echo "\n<!-- Zan Affiliate Pro SEO -->\n";
        echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
        echo '<meta name="robots" content="' . esc_attr( $this->get_robots() ) . '">' . "\n";

        // Open Graph
        echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr( $site ) . '">' . "\n";
        if ( $url ) echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
        if ( $img_url ) {
            echo '<meta property="og:image" content="' . esc_url( $img_url ) . '">' . "\n";
            echo '<meta property="og:image:width" content="1280">' . "\n";
            echo '<meta property="og:image:height" content="640">' . "\n";
        }

        // Twitter Card
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
        if ( $twitter ) echo '<meta name="twitter:site" content="@' . esc_attr( ltrim( $twitter, '@' ) ) . '">' . "\n";
        if ( $img_url ) echo '<meta name="twitter:image" content="' . esc_url( $img_url ) . '">' . "\n";

        // Canonical
        if ( $url ) echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
        echo "<!-- /Zan Affiliate Pro SEO -->\n";
    }

    private function get_robots(): string {
        if ( is_singular() ) {
            $post_id = get_the_ID();
            $noindex = get_post_meta( $post_id, '_zap_noindex', true );
            if ( $noindex ) return 'noindex, nofollow';
        }
        return 'index, follow';
    }

    public function title_parts( array $parts ): array {
        if ( $this->seo_plugin_active() ) return $parts;
        if ( is_singular() ) {
            $custom = get_post_meta( get_the_ID(), '_zap_seo_title', true );
            if ( $custom ) $parts['title'] = $custom;
        }
        return $parts;
    }

    // ── Meta Box ──────────────────────────────────────────────────────────────

    public function meta_boxes(): void {
        if ( $this->seo_plugin_active() ) return;
        add_meta_box(
            'zap-seo-meta',
            __( 'SEO', 'zan-affiliate-pro' ),
            [ $this, 'render_meta_box' ],
            [ 'post', 'page' ],
            'normal',
            'high'
        );
    }

    public function render_meta_box( WP_Post $post ): void {
        wp_nonce_field( 'zap_seo_meta', 'zap_seo_nonce' );
        $title   = get_post_meta( $post->ID, '_zap_seo_title', true );
        $desc    = get_post_meta( $post->ID, '_zap_seo_desc', true );
        $noindex = get_post_meta( $post->ID, '_zap_noindex', true );
        $focus   = get_post_meta( $post->ID, '_zap_focus_kw', true );
        ?>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e( 'Título SEO', 'zan-affiliate-pro' ); ?></th>
                <td>
                    <input type="text" name="zap_seo_title" value="<?php echo esc_attr( $title ); ?>" style="width:100%" maxlength="60" />
                    <div class="zap-char-counter" data-max="60" data-target="zap_seo_title">
                        <span class="current"><?php echo mb_strlen( $title ); ?></span>/60
                    </div>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Meta Descrição', 'zan-affiliate-pro' ); ?></th>
                <td>
                    <textarea name="zap_seo_desc" rows="3" style="width:100%" maxlength="160"><?php echo esc_textarea( $desc ); ?></textarea>
                    <div class="zap-char-counter" data-max="160" data-target="zap_seo_desc">
                        <span class="current"><?php echo mb_strlen( $desc ); ?></span>/160
                    </div>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Palavra-chave focal', 'zan-affiliate-pro' ); ?></th>
                <td><input type="text" name="zap_focus_kw" value="<?php echo esc_attr( $focus ); ?>" style="width:100%" /></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Noindex', 'zan-affiliate-pro' ); ?></th>
                <td><label><input type="checkbox" name="zap_noindex" value="1" <?php checked( $noindex, '1' ); ?> /> <?php esc_html_e( 'Não indexar esta página', 'zan-affiliate-pro' ); ?></label></td>
            </tr>
        </table>

        <!-- Preview snippet -->
        <div class="zap-serp-preview">
            <h4><?php esc_html_e( 'Prévia no Google', 'zan-affiliate-pro' ); ?></h4>
            <div class="zap-serp-box">
                <div class="zap-serp-url"><?php echo esc_url( get_permalink( $post ) ); ?></div>
                <div class="zap-serp-title" id="zap-serp-title"><?php echo esc_html( $title ?: $post->post_title ); ?></div>
                <div class="zap-serp-desc" id="zap-serp-desc"><?php echo esc_html( $desc ?: wp_trim_words( $post->post_excerpt ?: $post->post_content, 25 ) ); ?></div>
            </div>
        </div>
        <?php
    }

    public function save_meta( int $post_id ): void {
        if (
            ! isset( $_POST['zap_seo_nonce'] ) ||
            ! wp_verify_nonce( $_POST['zap_seo_nonce'], 'zap_seo_meta' ) ||
            defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ||
            ! current_user_can( 'edit_post', $post_id )
        ) return;

        update_post_meta( $post_id, '_zap_seo_title', sanitize_text_field( $_POST['zap_seo_title'] ?? '' ) );
        update_post_meta( $post_id, '_zap_seo_desc',  sanitize_textarea_field( $_POST['zap_seo_desc'] ?? '' ) );
        update_post_meta( $post_id, '_zap_focus_kw',  sanitize_text_field( $_POST['zap_focus_kw'] ?? '' ) );
        update_post_meta( $post_id, '_zap_noindex',   isset( $_POST['zap_noindex'] ) ? '1' : '0' );
    }
}

ZAP_Meta_Tags::instance();
