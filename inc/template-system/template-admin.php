<?php
/**
 * Template System — Admin UI (template picker panel)
 *
 * Adds a visual template selector metabox to post/page editor,
 * similar to BeTheme's layout picker.
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

class ZAP_Template_Admin {

    private static ?self $instance = null;

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
        add_action( 'save_post',      [ $this, 'save_meta' ], 10, 2 );
        add_action( 'admin_menu',     [ $this, 'admin_page' ] );
    }

    public function add_meta_box(): void {
        $post_types = [ 'post', 'page' ];
        foreach ( $post_types as $pt ) {
            add_meta_box(
                'zap-template-picker',
                __( 'Layout do Template', 'zan-affiliate-pro' ),
                [ $this, 'render_meta_box' ],
                $pt,
                'side',
                'high'
            );
        }
    }

    public function render_meta_box( WP_Post $post ): void {
        wp_nonce_field( 'zap_template_meta', 'zap_template_nonce' );
        $current    = get_post_meta( $post->ID, '_zap_template', true );
        $templates  = ZAP_Template_System::get_templates();
        $categories = ZAP_Template_System::get_categories();

        $cat_labels = [
            'layout'    => __( 'Layouts', 'zan-affiliate-pro' ),
            'affiliate' => __( 'Afiliados', 'zan-affiliate-pro' ),
            'general'   => __( 'Geral', 'zan-affiliate-pro' ),
        ];
        ?>
        <div class="zap-template-picker" data-current="<?php echo esc_attr( $current ); ?>">
            <p class="description"><?php esc_html_e( 'Selecione um layout para esta página.', 'zan-affiliate-pro' ); ?></p>

            <?php foreach ( $categories as $cat ) :
                $cat_templates = ZAP_Template_System::get_templates( $cat );
                if ( empty( $cat_templates ) ) continue;
                ?>
                <div class="zap-template-group">
                    <h4><?php echo esc_html( $cat_labels[ $cat ] ?? $cat ); ?></h4>
                    <div class="zap-template-grid">
                        <?php foreach ( $cat_templates as $slug => $tmpl ) : ?>
                            <label class="zap-template-item <?php echo $current === $slug ? 'active' : ''; ?>">
                                <input type="radio"
                                       name="zap_template"
                                       value="<?php echo esc_attr( $slug ); ?>"
                                       <?php checked( $current, $slug ); ?> />
                                <?php if ( $tmpl['preview'] ) : ?>
                                    <img src="<?php echo esc_url( $tmpl['preview'] ); ?>"
                                         alt="<?php echo esc_attr( $tmpl['name'] ); ?>"
                                         loading="lazy" />
                                <?php else : ?>
                                    <div class="zap-template-placeholder">
                                        <span class="dashicons dashicons-layout"></span>
                                    </div>
                                <?php endif; ?>
                                <span class="zap-template-name"><?php echo esc_html( $tmpl['name'] ); ?></span>
                                <?php if ( $tmpl['description'] ) : ?>
                                    <span class="zap-template-desc"><?php echo esc_html( $tmpl['description'] ); ?></span>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Layout options (sidebar, etc.) -->
            <div class="zap-layout-options">
                <h4><?php esc_html_e( 'Opções de Layout', 'zan-affiliate-pro' ); ?></h4>
                <?php
                $layout = get_post_meta( $post->ID, '_zap_layout', true ) ?: 'default';
                $layouts = [
                    'default'    => __( 'Padrão (com sidebar)', 'zan-affiliate-pro' ),
                    'full-width' => __( 'Largura Total', 'zan-affiliate-pro' ),
                    'narrow'     => __( 'Estreito (foco em leitura)', 'zan-affiliate-pro' ),
                ];
                ?>
                <select name="zap_layout" id="zap-layout-select">
                    <?php foreach ( $layouts as $val => $label ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $layout, $val ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php
    }

    public function save_meta( int $post_id, WP_Post $post ): void {
        if (
            ! isset( $_POST['zap_template_nonce'] ) ||
            ! wp_verify_nonce( $_POST['zap_template_nonce'], 'zap_template_meta' ) ||
            defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ||
            ! current_user_can( 'edit_post', $post_id )
        ) {
            return;
        }

        if ( isset( $_POST['zap_template'] ) ) {
            update_post_meta( $post_id, '_zap_template', sanitize_key( $_POST['zap_template'] ) );
        }

        if ( isset( $_POST['zap_layout'] ) ) {
            update_post_meta( $post_id, '_zap_layout', sanitize_key( $_POST['zap_layout'] ) );
        }
    }

    public function admin_page(): void {
        add_submenu_page(
            'zap-options',
            __( 'Templates', 'zan-affiliate-pro' ),
            __( 'Templates', 'zan-affiliate-pro' ),
            'manage_options',
            'zap-templates',
            [ $this, 'render_admin_page' ]
        );
    }

    public function render_admin_page(): void {
        $templates = ZAP_Template_System::get_templates();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Templates Disponíveis', 'zan-affiliate-pro' ); ?></h1>
            <p><?php esc_html_e( 'Lista de todos os templates registrados. Novos templates são adicionados ao diretório /templates/ do tema.', 'zan-affiliate-pro' ); ?></p>

            <div class="zap-templates-admin-grid">
                <?php foreach ( $templates as $slug => $tmpl ) : ?>
                    <div class="zap-template-card">
                        <?php if ( $tmpl['preview'] ) : ?>
                            <img src="<?php echo esc_url( $tmpl['preview'] ); ?>" alt="" />
                        <?php else : ?>
                            <div class="zap-template-no-preview">
                                <span class="dashicons dashicons-layout" style="font-size:48px;width:48px;height:48px;color:#ccc;"></span>
                            </div>
                        <?php endif; ?>
                        <div class="zap-template-card-body">
                            <strong><?php echo esc_html( $tmpl['name'] ); ?></strong>
                            <code><?php echo esc_html( $slug ); ?></code>
                            <p><?php echo esc_html( $tmpl['description'] ); ?></p>
                            <span class="badge"><?php echo esc_html( $tmpl['category'] ); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr />
            <h2><?php esc_html_e( 'Como adicionar um novo template', 'zan-affiliate-pro' ); ?></h2>
            <ol>
                <li><?php esc_html_e( 'Crie um arquivo PHP em /templates/template-{slug}.php no tema.', 'zan-affiliate-pro' ); ?></li>
                <li><?php esc_html_e( 'Registre o template usando zap_register_template() dentro do hook zap_register_templates.', 'zan-affiliate-pro' ); ?></li>
                <li><?php esc_html_e( 'Adicione uma imagem de preview em /assets/images/templates/{slug}.jpg.', 'zan-affiliate-pro' ); ?></li>
            </ol>
        </div>
        <?php
    }
}

ZAP_Template_Admin::instance();
