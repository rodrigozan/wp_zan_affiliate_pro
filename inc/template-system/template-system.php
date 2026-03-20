<?php
/**
 * Template System — BeTheme-style multi-template infrastructure
 *
 * Allows creating pre-built page layouts selectable per-post/page
 * from the admin. New templates are added by dropping a PHP file into
 * the /templates/ directory and registering it here.
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

class ZAP_Template_System {

    /** Registered templates */
    private static array $templates = [];

    /** Singleton */
    private static ?self $instance = null;

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        add_action( 'init',                      [ $this, 'register_default_templates' ] );
        add_filter( 'theme_page_templates',      [ $this, 'add_page_templates' ] );
        add_filter( 'template_include',          [ $this, 'load_template' ] );
        add_action( 'wp_ajax_zap_preview_template', [ $this, 'ajax_preview' ] );
    }

    // ── Registration ──────────────────────────────────────────────────────────

    public static function register( string $slug, array $config ): void {
        self::$templates[ $slug ] = wp_parse_args( $config, [
            'name'        => $slug,
            'description' => '',
            'preview'     => '',   // URL of preview image
            'category'    => 'general',
            'post_types'  => [ 'page' ],
            'file'        => ZAP_DIR . "/templates/template-{$slug}.php",
        ] );
    }

    public function register_default_templates(): void {
        // Built-in templates
        self::register( 'full-width', [
            'name'        => __( 'Largura Total', 'zan-affiliate-pro' ),
            'description' => __( 'Layout sem sidebar, conteúdo em largura total.', 'zan-affiliate-pro' ),
            'category'    => 'layout',
            'post_types'  => [ 'page', 'post' ],
            'preview'     => ZAP_URI . '/assets/images/templates/full-width.jpg',
        ] );

        self::register( 'landing', [
            'name'        => __( 'Landing Page', 'zan-affiliate-pro' ),
            'description' => __( 'Template de landing page sem header/footer padrão, focado em conversão.', 'zan-affiliate-pro' ),
            'category'    => 'affiliate',
            'post_types'  => [ 'page' ],
            'preview'     => ZAP_URI . '/assets/images/templates/landing.jpg',
        ] );

        self::register( 'review', [
            'name'        => __( 'Review de Produto', 'zan-affiliate-pro' ),
            'description' => __( 'Template otimizado para reviews de produtos com schema Review.', 'zan-affiliate-pro' ),
            'category'    => 'affiliate',
            'post_types'  => [ 'page', 'post' ],
            'preview'     => ZAP_URI . '/assets/images/templates/review.jpg',
        ] );

        self::register( 'comparison', [
            'name'        => __( 'Comparativo', 'zan-affiliate-pro' ),
            'description' => __( 'Template para páginas de comparação entre produtos.', 'zan-affiliate-pro' ),
            'category'    => 'affiliate',
            'post_types'  => [ 'page', 'post' ],
            'preview'     => ZAP_URI . '/assets/images/templates/comparison.jpg',
        ] );

        self::register( 'category-hub', [
            'name'        => __( 'Hub de Categoria', 'zan-affiliate-pro' ),
            'description' => __( 'Página-hub com listagem de posts da categoria + sidebar.', 'zan-affiliate-pro' ),
            'category'    => 'layout',
            'post_types'  => [ 'page' ],
            'preview'     => ZAP_URI . '/assets/images/templates/category-hub.jpg',
        ] );

        self::register( 'mini-landing', [
            'name'        => __( 'Mini Landing', 'zan-affiliate-pro' ),
            'description' => __( 'Versão compacta de landing page para produtos específicos.', 'zan-affiliate-pro' ),
            'category'    => 'affiliate',
            'post_types'  => [ 'page' ],
            'preview'     => ZAP_URI . '/assets/images/templates/mini-landing.jpg',
        ] );

        // Allow plugins/child-themes to add templates
        do_action( 'zap_register_templates' );
    }

    // ── WordPress integration ─────────────────────────────────────────────────

    /** Expose templates to WordPress "Page Template" dropdown */
    public function add_page_templates( array $templates ): array {
        foreach ( self::$templates as $slug => $config ) {
            $templates[ "zap:$slug" ] = $config['name'];
        }
        return $templates;
    }

    /** Intercept template loading */
    public function load_template( string $template ): string {
        if ( ! is_singular() ) return $template;

        $post_id  = get_the_ID();
        $selected = get_post_meta( $post_id, '_wp_page_template', true )
                 ?: get_post_meta( $post_id, '_zap_template', true );

        if ( ! $selected || ! str_starts_with( $selected, 'zap:' ) ) {
            return $template;
        }

        $slug = substr( $selected, 4 );

        if ( isset( self::$templates[ $slug ] ) ) {
            $file = self::$templates[ $slug ]['file'];
            if ( file_exists( $file ) ) {
                return $file;
            }
        }

        return $template;
    }

    /** AJAX: return template preview image URL */
    public function ajax_preview(): void {
        check_ajax_referer( 'zap_admin_nonce', 'nonce' );
        $slug = sanitize_key( $_POST['template'] ?? '' );
        $tmpl = self::$templates[ $slug ] ?? null;
        wp_send_json_success( [
            'preview'     => $tmpl['preview']     ?? '',
            'description' => $tmpl['description'] ?? '',
        ] );
    }

    // ── Getters ───────────────────────────────────────────────────────────────

    public static function get_templates( ?string $category = null ): array {
        if ( $category ) {
            return array_filter( self::$templates, fn( $t ) => $t['category'] === $category );
        }
        return self::$templates;
    }

    public static function get_categories(): array {
        $cats = array_column( self::$templates, 'category' );
        return array_unique( $cats );
    }
}

// Boot
ZAP_Template_System::instance();

/**
 * Helper: register a template from outside this class.
 *
 * Usage in child theme or plugin:
 *   add_action('zap_register_templates', function() {
 *       zap_register_template('my-template', [...]);
 *   });
 */
function zap_register_template( string $slug, array $config ): void {
    ZAP_Template_System::register( $slug, $config );
}
