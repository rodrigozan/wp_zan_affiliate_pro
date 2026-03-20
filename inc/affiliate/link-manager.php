<?php
/**
 * Affiliate Link Manager
 *
 * Cloaks affiliate links via /go/{slug} pretty URLs,
 * tracks clicks and manages link metadata.
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

class ZAP_Link_Manager {

    private static ?self $instance = null;

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        add_action( 'init',               [ $this, 'register_post_type' ] );
        add_action( 'init',               [ $this, 'add_rewrite_rules' ] );
        add_action( 'template_redirect',  [ $this, 'handle_redirect' ] );
        add_action( 'wp_ajax_zap_track_click', [ $this, 'track_click' ] );
        add_action( 'wp_ajax_nopriv_zap_track_click', [ $this, 'track_click' ] );
        add_filter( 'the_content',        [ $this, 'auto_link' ] );
        add_action( 'add_meta_boxes',     [ $this, 'meta_boxes' ] );
        add_action( 'save_post_zap_link', [ $this, 'save_link_meta' ] );
    }

    // ── CPT ───────────────────────────────────────────────────────────────────

    public function register_post_type(): void {
        register_post_type( 'zap_link', [
            'labels' => [
                'name'               => __( 'Links de Afiliado', 'zan-affiliate-pro' ),
                'singular_name'      => __( 'Link de Afiliado', 'zan-affiliate-pro' ),
                'add_new_item'       => __( 'Novo Link', 'zan-affiliate-pro' ),
                'edit_item'          => __( 'Editar Link', 'zan-affiliate-pro' ),
                'search_items'       => __( 'Buscar Links', 'zan-affiliate-pro' ),
            ],
            'public'            => false,
            'show_ui'           => true,
            'show_in_menu'      => 'zap-options',
            'supports'          => [ 'title' ],
            'menu_icon'         => 'dashicons-admin-links',
            'capability_type'   => 'post',
        ] );

        register_taxonomy( 'zap_link_group', 'zap_link', [
            'label'        => __( 'Grupos', 'zan-affiliate-pro' ),
            'hierarchical' => true,
            'show_ui'      => true,
            'show_in_menu' => true,
        ] );
    }

    // ── Rewrite ───────────────────────────────────────────────────────────────

    public function add_rewrite_rules(): void {
        $base = get_option( 'zap_link_base', 'go' );
        add_rewrite_rule( "^$base/([^/]+)/?$", 'index.php?zap_go=$matches[1]', 'top' );
        add_rewrite_tag( '%zap_go%', '([^/]+)' );
    }

    public function handle_redirect(): void {
        $slug = get_query_var( 'zap_go' );
        if ( ! $slug ) return;

        $link = $this->get_link_by_slug( $slug );

        if ( ! $link ) {
            wp_redirect( home_url( '/' ), 302 );
            exit;
        }

        $url         = get_post_meta( $link->ID, '_zap_link_url', true );
        $nofollow    = get_post_meta( $link->ID, '_zap_link_nofollow', true );
        $redirect    = (int) ( get_post_meta( $link->ID, '_zap_link_redirect', true ) ?: 302 );

        // Track the click
        $this->record_click( $link->ID );

        if ( ! $url ) {
            wp_redirect( home_url( '/' ), 302 );
            exit;
        }

        wp_redirect( esc_url_raw( $url ), $redirect );
        exit;
    }

    // ── Click tracking ────────────────────────────────────────────────────────

    private function record_click( int $link_id ): void {
        $clicks = (int) get_post_meta( $link_id, '_zap_clicks', true );
        update_post_meta( $link_id, '_zap_clicks', $clicks + 1 );
        update_post_meta( $link_id, '_zap_last_click', current_time( 'mysql' ) );

        // Monthly stats
        $month    = current_time( 'Y-m' );
        $monthly  = get_post_meta( $link_id, '_zap_clicks_monthly', true ) ?: [];
        $monthly[ $month ] = ( $monthly[ $month ] ?? 0 ) + 1;
        update_post_meta( $link_id, '_zap_clicks_monthly', $monthly );
    }

    public function track_click(): void {
        check_ajax_referer( 'zap_nonce', 'nonce' );
        $link_id = (int) ( $_POST['link_id'] ?? 0 );
        if ( $link_id ) {
            $this->record_click( $link_id );
        }
        wp_send_json_success();
    }

    // ── Auto-linking ──────────────────────────────────────────────────────────

    /**
     * Replace keyword occurrences in content with affiliate links.
     */
    public function auto_link( string $content ): string {
        if ( ! zap_option( 'auto_link_enabled', false ) ) return $content;
        if ( is_admin() || is_feed() ) return $content;

        $links = $this->get_auto_links();
        if ( empty( $links ) ) return $content;

        $base = get_option( 'zap_link_base', 'go' );

        foreach ( $links as $link ) {
            $keywords = get_post_meta( $link->ID, '_zap_keywords', true );
            if ( ! $keywords ) continue;

            $keywords_arr = array_map( 'trim', explode( ',', $keywords ) );
            $slug         = $link->post_name;
            $url          = home_url( "/$base/$slug/" );
            $max          = (int) ( get_post_meta( $link->ID, '_zap_max_auto', true ) ?: 1 );
            $count        = 0;

            foreach ( $keywords_arr as $kw ) {
                if ( empty( $kw ) || $count >= $max ) break;
                $anchor  = sprintf(
                    '<a href="%s" rel="nofollow sponsored" target="_blank" class="zap-aff-link">%s</a>',
                    esc_url( $url ),
                    esc_html( $kw )
                );
                $replace = preg_replace(
                    '/(?<!["\'>])\b' . preg_quote( $kw, '/' ) . '\b(?!["\'])/i',
                    $anchor,
                    $content,
                    $max - $count,
                    $n
                );
                if ( $n > 0 ) {
                    $content = $replace;
                    $count  += $n;
                }
            }
        }

        return $content;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function get_link_by_slug( string $slug ): ?WP_Post {
        $posts = get_posts( [
            'post_type'   => 'zap_link',
            'name'        => $slug,
            'numberposts' => 1,
            'post_status' => 'publish',
        ] );
        return $posts[0] ?? null;
    }

    private function get_auto_links(): array {
        return get_posts( [
            'post_type'  => 'zap_link',
            'numberposts'=> -1,
            'post_status'=> 'publish',
            'meta_key'   => '_zap_keywords',
        ] );
    }

    /**
     * Generate a cloaked URL for a link slug.
     */
    public static function get_url( string $slug ): string {
        $base = get_option( 'zap_link_base', 'go' );
        return home_url( "/$base/$slug/" );
    }

    // ── Meta boxes ────────────────────────────────────────────────────────────

    public function meta_boxes(): void {
        add_meta_box(
            'zap-link-details',
            __( 'Detalhes do Link', 'zan-affiliate-pro' ),
            [ $this, 'render_link_meta' ],
            'zap_link',
            'normal',
            'high'
        );
    }

    public function render_link_meta( WP_Post $post ): void {
        wp_nonce_field( 'zap_link_meta', 'zap_link_nonce' );
        $url      = get_post_meta( $post->ID, '_zap_link_url', true );
        $redirect = get_post_meta( $post->ID, '_zap_link_redirect', true ) ?: '302';
        $nofollow = get_post_meta( $post->ID, '_zap_link_nofollow', true );
        $keywords = get_post_meta( $post->ID, '_zap_keywords', true );
        $max_auto = get_post_meta( $post->ID, '_zap_max_auto', true ) ?: '1';
        $clicks   = (int) get_post_meta( $post->ID, '_zap_clicks', true );
        $base     = get_option( 'zap_link_base', 'go' );
        $slug     = $post->post_name;
        ?>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e( 'URL de Destino', 'zan-affiliate-pro' ); ?></th>
                <td><input type="url" name="zap_link_url" value="<?php echo esc_attr( $url ); ?>" style="width:100%" /></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'URL Curta (slug)', 'zan-affiliate-pro' ); ?></th>
                <td>
                    <code><?php echo esc_url( home_url( "/$base/$slug/" ) ); ?></code>
                    <?php if ( $slug ) : ?>
                        <button type="button" class="button zap-copy-link" data-url="<?php echo esc_url( home_url( "/$base/$slug/" ) ); ?>">
                            <?php esc_html_e( 'Copiar', 'zan-affiliate-pro' ); ?>
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Tipo de Redirect', 'zan-affiliate-pro' ); ?></th>
                <td>
                    <select name="zap_link_redirect">
                        <option value="301" <?php selected( $redirect, '301' ); ?>>301 – Permanente</option>
                        <option value="302" <?php selected( $redirect, '302' ); ?>>302 – Temporário</option>
                        <option value="307" <?php selected( $redirect, '307' ); ?>>307 – Temp (método preservado)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Rel nofollow', 'zan-affiliate-pro' ); ?></th>
                <td><label><input type="checkbox" name="zap_link_nofollow" value="1" <?php checked( $nofollow, '1' ); ?> /> <?php esc_html_e( 'Adicionar rel="nofollow sponsored"', 'zan-affiliate-pro' ); ?></label></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Keywords para auto-link', 'zan-affiliate-pro' ); ?></th>
                <td>
                    <input type="text" name="zap_keywords" value="<?php echo esc_attr( $keywords ); ?>" style="width:100%" />
                    <p class="description"><?php esc_html_e( 'Separe por vírgula. Ex: produto, nome do curso, ferramenta', 'zan-affiliate-pro' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Máx auto-links por post', 'zan-affiliate-pro' ); ?></th>
                <td><input type="number" name="zap_max_auto" value="<?php echo esc_attr( $max_auto ); ?>" min="1" max="10" /></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Total de cliques', 'zan-affiliate-pro' ); ?></th>
                <td><strong><?php echo esc_html( $clicks ); ?></strong></td>
            </tr>
        </table>
        <?php
    }

    public function save_link_meta( int $post_id ): void {
        if (
            ! isset( $_POST['zap_link_nonce'] ) ||
            ! wp_verify_nonce( $_POST['zap_link_nonce'], 'zap_link_meta' ) ||
            ! current_user_can( 'edit_post', $post_id )
        ) return;

        $fields = [
            '_zap_link_url'      => 'esc_url_raw',
            '_zap_link_redirect' => 'sanitize_text_field',
            '_zap_keywords'      => 'sanitize_text_field',
            '_zap_max_auto'      => 'absint',
        ];

        foreach ( $fields as $key => $sanitize ) {
            $post_key = ltrim( $key, '_' );
            if ( isset( $_POST[ $post_key ] ) ) {
                update_post_meta( $post_id, $key, $sanitize( $_POST[ $post_key ] ) );
            }
        }

        update_post_meta( $post_id, '_zap_link_nofollow', isset( $_POST['zap_link_nofollow'] ) ? '1' : '0' );
    }
}

ZAP_Link_Manager::instance();
