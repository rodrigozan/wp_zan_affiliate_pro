<?php
/**
 * Theme Options — Admin Page
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

class ZAP_Options_Page {

    private static ?self $instance = null;
    private const OPTION = 'zap_options';

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        add_action( 'admin_menu',    [ $this, 'register_menu' ] );
        add_action( 'admin_init',    [ $this, 'register_settings' ] );
        add_action( 'admin_notices', [ $this, 'saved_notice' ] );
    }

    public function register_menu(): void {
        add_menu_page(
            __( 'Zan Affiliate Pro', 'zan-affiliate-pro' ),
            __( 'Zan Affiliate', 'zan-affiliate-pro' ),
            'manage_options',
            'zap-options',
            [ $this, 'render' ],
            'dashicons-chart-area',
            58
        );
    }

    public function register_settings(): void {
        register_setting( 'zap_options_group', self::OPTION, [ 'sanitize_callback' => [ $this, 'sanitize' ] ] );
    }

    public function sanitize( array $input ): array {
        $clean = [];
        $text_fields = [
            'twitter_handle', 'facebook_url', 'instagram_url', 'youtube_url', 'linkedin_url',
            'google_analytics_id', 'google_tag_manager_id', 'cookie_policy_url',
            'cookie_notice_text', 'cookie_accept_label', 'cookie_reject_label', 'cookie_manage_label',
            'disclosure_text', 'link_base',
        ];
        foreach ( $text_fields as $f ) {
            $clean[ $f ] = sanitize_text_field( $input[ $f ] ?? '' );
        }
        $bool_fields = [
            'auto_toc', 'auto_related', 'auto_link_enabled', 'show_disclosure',
            'cookie_notice_enabled', 'sitemap_ping', 'minify_html',
        ];
        foreach ( $bool_fields as $f ) {
            $clean[ $f ] = ! empty( $input[ $f ] );
        }
        return $clean;
    }

    public function saved_notice(): void {
        $screen = get_current_screen();
        if ( $screen && $screen->id !== 'toplevel_page_zap-options' ) return;
        if ( isset( $_GET['settings-updated'] ) ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Configurações salvas!', 'zan-affiliate-pro' ) . '</p></div>';
        }
    }

    public function render(): void {
        $options = get_option( self::OPTION, [] );
        $tabs    = $this->tabs();
        $current = sanitize_key( $_GET['tab'] ?? 'geral' );
        ?>
        <div class="wrap zap-admin-wrap">
            <h1>
                <span class="dashicons dashicons-chart-area" style="font-size:28px;width:28px;height:28px;"></span>
                <?php esc_html_e( 'Zan Affiliate Pro', 'zan-affiliate-pro' ); ?>
            </h1>

            <nav class="nav-tab-wrapper zap-admin-tabs">
                <?php foreach ( $tabs as $slug => $label ) : ?>
                    <a href="<?php echo esc_url( admin_url( "admin.php?page=zap-options&tab=$slug" ) ); ?>"
                       class="nav-tab <?php echo $current === $slug ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html( $label ); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <form method="post" action="options.php">
                <?php settings_fields( 'zap_options_group' ); ?>

                <div class="zap-admin-tab-content">
                    <?php $this->render_tab( $current, $options ); ?>
                </div>

                <?php submit_button( __( 'Salvar Configurações', 'zan-affiliate-pro' ) ); ?>
            </form>
        </div>
        <?php
    }

    private function tabs(): array {
        return [
            'geral'      => __( 'Geral', 'zan-affiliate-pro' ),
            'seo'        => __( 'SEO', 'zan-affiliate-pro' ),
            'afiliados'  => __( 'Afiliados', 'zan-affiliate-pro' ),
            'conteudo'   => __( 'Conteúdo', 'zan-affiliate-pro' ),
            'legal'      => __( 'Legal / LGPD', 'zan-affiliate-pro' ),
            'analytics'  => __( 'Analytics', 'zan-affiliate-pro' ),
            'social'     => __( 'Redes Sociais', 'zan-affiliate-pro' ),
        ];
    }

    private function render_tab( string $tab, array $o ): void {
        switch ( $tab ) {
            case 'geral':
                $this->tab_geral( $o );
                break;
            case 'seo':
                $this->tab_seo( $o );
                break;
            case 'afiliados':
                $this->tab_afiliados( $o );
                break;
            case 'conteudo':
                $this->tab_conteudo( $o );
                break;
            case 'legal':
                $this->tab_legal( $o );
                break;
            case 'analytics':
                $this->tab_analytics( $o );
                break;
            case 'social':
                $this->tab_social( $o );
                break;
        }
    }

    // ── Tabs ──────────────────────────────────────────────────────────────────

    private function tab_geral( array $o ): void {
        $this->section_start( __( 'Configurações Gerais', 'zan-affiliate-pro' ) );
        $this->field_text( 'link_base', __( 'Base de links de afiliado', 'zan-affiliate-pro' ), $o['link_base'] ?? 'go', __( 'Ex: "go" → seusite.com/go/produto', 'zan-affiliate-pro' ) );
        $this->section_end();
    }

    private function tab_seo( array $o ): void {
        $this->section_start( __( 'SEO', 'zan-affiliate-pro' ) );
        $this->field_checkbox( 'sitemap_ping', __( 'Pingar Google/Bing ao publicar', 'zan-affiliate-pro' ), $o['sitemap_ping'] ?? true );
        $this->field_checkbox( 'minify_html', __( 'Minificar HTML (experimental)', 'zan-affiliate-pro' ), $o['minify_html'] ?? false );
        $this->section_end();
    }

    private function tab_afiliados( array $o ): void {
        $this->section_start( __( 'Links de Afiliado', 'zan-affiliate-pro' ) );
        $this->field_checkbox( 'auto_link_enabled', __( 'Ativar auto-link por palavras-chave', 'zan-affiliate-pro' ), $o['auto_link_enabled'] ?? false );
        $this->field_checkbox( 'show_disclosure', __( 'Exibir aviso de afiliado', 'zan-affiliate-pro' ), $o['show_disclosure'] ?? true );
        $this->field_textarea( 'disclosure_text', __( 'Texto do aviso de afiliado', 'zan-affiliate-pro' ), $o['disclosure_text'] ?? '' );
        $this->section_end();
    }

    private function tab_conteudo( array $o ): void {
        $this->section_start( __( 'Conteúdo', 'zan-affiliate-pro' ) );
        $this->field_checkbox( 'auto_toc', __( 'Inserir índice automaticamente (TOC)', 'zan-affiliate-pro' ), $o['auto_toc'] ?? false );
        $this->field_checkbox( 'auto_related', __( 'Exibir posts relacionados automaticamente', 'zan-affiliate-pro' ), $o['auto_related'] ?? true );
        $this->section_end();
    }

    private function tab_legal( array $o ): void {
        $this->section_start( __( 'Aviso de Cookies / LGPD', 'zan-affiliate-pro' ) );
        $this->field_checkbox( 'cookie_notice_enabled', __( 'Ativar aviso de cookies', 'zan-affiliate-pro' ), $o['cookie_notice_enabled'] ?? true );
        $this->field_textarea( 'cookie_notice_text', __( 'Texto do aviso', 'zan-affiliate-pro' ), $o['cookie_notice_text'] ?? '' );
        $this->field_text( 'cookie_accept_label', __( 'Label do botão Aceitar', 'zan-affiliate-pro' ), $o['cookie_accept_label'] ?? __( 'Aceitar', 'zan-affiliate-pro' ) );
        $this->field_text( 'cookie_reject_label', __( 'Label do botão Recusar', 'zan-affiliate-pro' ), $o['cookie_reject_label'] ?? __( 'Recusar', 'zan-affiliate-pro' ) );
        $this->field_text( 'cookie_manage_label', __( 'Label do link Gerenciar', 'zan-affiliate-pro' ), $o['cookie_manage_label'] ?? __( 'Gerenciar', 'zan-affiliate-pro' ) );
        $this->field_text( 'cookie_policy_url', __( 'URL da Política de Privacidade', 'zan-affiliate-pro' ), $o['cookie_policy_url'] ?? '/politica-de-privacidade/' );
        $this->section_end();
    }

    private function tab_analytics( array $o ): void {
        $this->section_start( __( 'Analytics', 'zan-affiliate-pro' ) );
        $this->field_text( 'google_analytics_id', __( 'Google Analytics ID (UA-XXXXX-X ou G-XXXXXX)', 'zan-affiliate-pro' ), $o['google_analytics_id'] ?? '' );
        $this->field_text( 'google_tag_manager_id', __( 'Google Tag Manager ID (GTM-XXXXXX)', 'zan-affiliate-pro' ), $o['google_tag_manager_id'] ?? '' );
        $this->section_end();
    }

    private function tab_social( array $o ): void {
        $this->section_start( __( 'Redes Sociais', 'zan-affiliate-pro' ) );
        $this->field_text( 'twitter_handle', __( 'Twitter / X handle (sem @)', 'zan-affiliate-pro' ), $o['twitter_handle'] ?? '' );
        $this->field_text( 'facebook_url',   __( 'Facebook URL', 'zan-affiliate-pro' ),  $o['facebook_url']  ?? '' );
        $this->field_text( 'instagram_url',  __( 'Instagram URL', 'zan-affiliate-pro' ), $o['instagram_url'] ?? '' );
        $this->field_text( 'youtube_url',    __( 'YouTube URL', 'zan-affiliate-pro' ),   $o['youtube_url']   ?? '' );
        $this->field_text( 'linkedin_url',   __( 'LinkedIn URL', 'zan-affiliate-pro' ),  $o['linkedin_url']  ?? '' );
        $this->section_end();
    }

    // ── Field helpers ─────────────────────────────────────────────────────────

    private function section_start( string $title ): void {
        echo '<div class="zap-settings-section"><h2>' . esc_html( $title ) . '</h2><table class="form-table">';
    }

    private function section_end(): void {
        echo '</table></div>';
    }

    private function field_text( string $key, string $label, string $value, string $desc = '' ): void {
        $name = self::OPTION . '[' . $key . ']';
        printf(
            '<tr><th scope="row"><label for="%s">%s</label></th><td><input type="text" id="%s" name="%s" value="%s" class="regular-text" />%s</td></tr>',
            esc_attr( $key ),
            esc_html( $label ),
            esc_attr( $key ),
            esc_attr( $name ),
            esc_attr( $value ),
            $desc ? '<p class="description">' . esc_html( $desc ) . '</p>' : ''
        );
    }

    private function field_textarea( string $key, string $label, string $value ): void {
        $name = self::OPTION . '[' . $key . ']';
        printf(
            '<tr><th scope="row"><label for="%s">%s</label></th><td><textarea id="%s" name="%s" rows="4" class="large-text">%s</textarea></td></tr>',
            esc_attr( $key ),
            esc_html( $label ),
            esc_attr( $key ),
            esc_attr( $name ),
            esc_textarea( $value )
        );
    }

    private function field_checkbox( string $key, string $label, bool $checked ): void {
        $name = self::OPTION . '[' . $key . ']';
        printf(
            '<tr><th scope="row">%s</th><td><label><input type="checkbox" name="%s" value="1" %s /> %s</label></td></tr>',
            esc_html( $label ),
            esc_attr( $name ),
            checked( $checked, true, false ),
            esc_html__( 'Ativado', 'zan-affiliate-pro' )
        );
    }
}

ZAP_Options_Page::instance();
