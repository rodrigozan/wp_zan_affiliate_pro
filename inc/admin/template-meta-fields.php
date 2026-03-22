<?php
/**
 * Template Meta Fields
 *
 * Metabox dinâmica com campos específicos para cada template ZAP.
 * Aparece no editor de post/page e mostra apenas os campos
 * relevantes ao template selecionado.
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

class ZAP_Template_Meta_Fields {

    private static ?self $instance = null;

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
        add_action( 'save_post',      [ $this, 'save' ] );
    }

    public function add_meta_box(): void {
        add_meta_box(
            'zap-template-fields',
            __( 'Dados do Template', 'zan-affiliate-pro' ),
            [ $this, 'render' ],
            [ 'post', 'page' ],
            'normal',
            'default'
        );
    }

    public function render( WP_Post $post ): void {
        wp_nonce_field( 'zap_tmpl_fields', 'zap_tmpl_fields_nonce' );

        // All field groups with their labels and types
        $field_groups = $this->field_groups();
        ?>
        <style>
            .zap-tmf-group { display: none; }
            .zap-tmf-group.active { display: block; }
            .zap-tmf-table { width: 100%; border-collapse: collapse; }
            .zap-tmf-table th { width: 200px; text-align: left; font-weight: 600; padding: 8px 0; vertical-align: top; padding-right: 16px; }
            .zap-tmf-table td { padding: 6px 0; }
            .zap-tmf-table input[type="text"],
            .zap-tmf-table input[type="url"],
            .zap-tmf-table textarea { width: 100%; }
            .zap-tmf-table textarea { height: 80px; resize: vertical; }
            .zap-tmf-hint { font-size: .75rem; color: #6b7280; margin-top: 3px; }
            .zap-tmf-notice { background: #fffbeb; border: 1px solid #fde68a; padding: 8px 12px; border-radius: 4px; font-size: .85rem; }
        </style>

        <p class="zap-tmf-notice">
            <?php esc_html_e( 'Selecione um template no painel lateral para ver os campos disponíveis. Os campos abaixo são preenchidos pelo template ativo.', 'zan-affiliate-pro' ); ?>
        </p>

        <?php
        $current_template = get_post_meta( $post->ID, '_zap_template', true );
        foreach ( $field_groups as $group_slug => $group ) :
            $active = $current_template === $group_slug || in_array( $current_template, $group['also'] ?? [], true );
            ?>
            <div class="zap-tmf-group <?php echo $active ? 'active' : ''; ?>" data-template="<?php echo esc_attr( $group_slug ); ?>">
                <h4 style="margin-bottom:8px;color:#374151"><?php echo esc_html( $group['title'] ); ?></h4>
                <table class="zap-tmf-table">
                    <?php foreach ( $group['fields'] as $key => $field ) :
                        $val = get_post_meta( $post->ID, "_zap_$key", true );
                        ?>
                        <tr>
                            <th><label for="zap_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
                            <td>
                                <?php $this->render_field( $key, $field, $val ); ?>
                                <?php if ( ! empty( $field['hint'] ) ) : ?>
                                    <p class="zap-tmf-hint"><?php echo esc_html( $field['hint'] ); ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endforeach; ?>

        <script>
        (function() {
            // Show/hide field groups based on selected template
            document.querySelectorAll('.zap-template-item input[type="radio"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    var slug = this.value;
                    document.querySelectorAll('.zap-tmf-group').forEach(function(g) {
                        var tpl = g.dataset.template;
                        g.classList.toggle('active', tpl === slug || tpl === 'common');
                    });
                });
            });
            // Also react to page template dropdown (WP native)
            var pageTemplateSelect = document.querySelector('#page_template');
            if (pageTemplateSelect) {
                pageTemplateSelect.addEventListener('change', function() {
                    var val = this.value; // format: "zap:slug"
                    var slug = val.startsWith('zap:') ? val.slice(4) : '';
                    document.querySelectorAll('.zap-tmf-group').forEach(function(g) {
                        g.classList.toggle('active', g.dataset.template === slug || g.dataset.template === 'common');
                    });
                });
            }
        })();
        </script>
        <?php
    }

    private function render_field( string $key, array $field, $val ): void {
        $id   = 'zap_' . $key;
        $name = 'zap_' . $key;
        switch ( $field['type'] ) {
            case 'url':
                echo '<input type="url" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '" />';
                break;
            case 'textarea':
                echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">' . esc_textarea( $val ) . '</textarea>';
                break;
            default:
                echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '" />';
        }
    }

    private function field_groups(): array {
        $common_fields = [
            'main_aff_url'   => [ 'label' => __( 'URL do Link de Afiliado Principal', 'zan-affiliate-pro' ), 'type' => 'url' ],
            'main_aff_label' => [ 'label' => __( 'Texto do Botão CTA', 'zan-affiliate-pro' ),               'type' => 'text', 'hint' => __( 'Ex: Ver Melhor Preço, Comprar Agora', 'zan-affiliate-pro' ) ],
            'hero_subtitle'  => [ 'label' => __( 'Subtítulo / Descrição do Hero', 'zan-affiliate-pro' ),     'type' => 'text' ],
            'hero_badge'     => [ 'label' => __( 'Badge do Hero', 'zan-affiliate-pro' ),                     'type' => 'text', 'hint' => __( 'Ex: ⭐ Oferta Especial', 'zan-affiliate-pro' ) ],
            'guarantee_text' => [ 'label' => __( 'Texto de Garantia', 'zan-affiliate-pro' ),                 'type' => 'text' ],
            'urgency_text'   => [ 'label' => __( 'Texto de Urgência', 'zan-affiliate-pro' ),                 'type' => 'text', 'hint' => __( 'Ex: Oferta válida até hoje!', 'zan-affiliate-pro' ) ],
        ];

        return [
            'common' => [
                'title'  => __( 'Campos Comuns (todos os templates)', 'zan-affiliate-pro' ),
                'fields' => $common_fields,
            ],
            'review-blog' => [
                'title' => __( 'Review Blog', 'zan-affiliate-pro' ),
                'also'  => [ 'review', 'software-review', 'course-review' ],
                'fields' => [
                    'pros' => [ 'label' => __( 'Prós (um por linha)', 'zan-affiliate-pro' ), 'type' => 'textarea', 'hint' => __( 'Uma vantagem por linha', 'zan-affiliate-pro' ) ],
                    'cons' => [ 'label' => __( 'Contras (um por linha)', 'zan-affiliate-pro' ),'type' => 'textarea' ],
                ],
            ],
            'niche-landing' => [
                'title' => __( 'Niche Landing / Health', 'zan-affiliate-pro' ),
                'also'  => [ 'health-product', 'squeeze-page' ],
                'fields' => [
                    'benefits'     => [ 'label' => __( 'Benefícios', 'zan-affiliate-pro' ),      'type' => 'textarea', 'hint' => __( 'Formato: emoji|Texto — um por linha. Ex: 🚀|Resultado rápido', 'zan-affiliate-pro' ) ],
                    'features'     => [ 'label' => __( 'Features / Steps', 'zan-affiliate-pro' ), 'type' => 'textarea', 'hint' => __( 'Formato: emoji|Título|Descrição — um por linha', 'zan-affiliate-pro' ) ],
                    'testimonials' => [ 'label' => __( 'Depoimentos', 'zan-affiliate-pro' ),      'type' => 'textarea', 'hint' => __( 'Formato: Nome|Cargo/Resultado|Texto — um por linha', 'zan-affiliate-pro' ) ],
                ],
            ],
            'top10-listicle' => [
                'title' => __( 'Top 10 / Finance Comparator', 'zan-affiliate-pro' ),
                'also'  => [ 'finance-comparator' ],
                'fields' => [
                    'listicle_items' => [ 'label' => __( 'IDs dos Produtos (CPT)', 'zan-affiliate-pro' ), 'type' => 'text', 'hint' => __( 'IDs separados por | — ordem = ranking. Ex: 42|17|38', 'zan-affiliate-pro' ) ],
                    'listicle_intro' => [ 'label' => __( 'Texto de Introdução', 'zan-affiliate-pro' ),    'type' => 'textarea' ],
                    'listicle_year'  => [ 'label' => __( 'Ano do Ranking', 'zan-affiliate-pro' ),         'type' => 'text' ],
                ],
            ],
            'software-review' => [
                'title' => __( 'Software / SaaS / Course', 'zan-affiliate-pro' ),
                'also'  => [ 'course-review' ],
                'fields' => [
                    'price_from'  => [ 'label' => __( 'Preço a partir de', 'zan-affiliate-pro' ),  'type' => 'text', 'hint' => __( 'Ex: R$ 97/mês', 'zan-affiliate-pro' ) ],
                    'free_trial'  => [ 'label' => __( 'Período Gratuito', 'zan-affiliate-pro' ),   'type' => 'text', 'hint' => __( 'Ex: 14 dias grátis', 'zan-affiliate-pro' ) ],
                    'sw_category' => [ 'label' => __( 'Categoria/Plataforma', 'zan-affiliate-pro' ),'type' => 'text', 'hint' => __( 'Ex: Email Marketing, Hotmart', 'zan-affiliate-pro' ) ],
                    'features'    => [ 'label' => __( 'Recursos/Módulos', 'zan-affiliate-pro' ),   'type' => 'textarea', 'hint' => __( 'Formato: emoji|Título|Descrição — um por linha', 'zan-affiliate-pro' ) ],
                    'plans'       => [ 'label' => __( 'Planos de Preço', 'zan-affiliate-pro' ),    'type' => 'textarea', 'hint' => __( 'Formato: Nome|Preço|Descrição — um por linha', 'zan-affiliate-pro' ) ],
                    'pros'        => [ 'label' => __( 'Prós (um por linha)', 'zan-affiliate-pro' ), 'type' => 'textarea' ],
                    'cons'        => [ 'label' => __( 'Contras (um por linha)', 'zan-affiliate-pro' ),'type' => 'textarea' ],
                    'testimonials'=> [ 'label' => __( 'Depoimentos', 'zan-affiliate-pro' ),        'type' => 'textarea', 'hint' => __( 'Formato: Nome|Cargo|Texto', 'zan-affiliate-pro' ) ],
                ],
            ],
            'magazine' => [
                'title' => __( 'Magazine / Portal', 'zan-affiliate-pro' ),
                'fields' => [
                    'mag_categories' => [ 'label' => __( 'IDs de Categorias a exibir', 'zan-affiliate-pro' ), 'type' => 'text', 'hint' => __( 'IDs separados por vírgula. Ex: 3,7,12', 'zan-affiliate-pro' ) ],
                ],
            ],
            'ecommerce-showcase' => [
                'title' => __( 'E-commerce Showcase', 'zan-affiliate-pro' ),
                'fields' => [],
            ],
            'squeeze-page' => [
                'title' => __( 'Squeeze Page', 'zan-affiliate-pro' ),
                'also'  => [],
                'fields' => [
                    'stats'         => [ 'label' => __( 'Stats / Prova Social', 'zan-affiliate-pro' ), 'type' => 'textarea', 'hint' => __( 'Formato: Número|Label — um por linha. Ex: +10.000|Inscritos', 'zan-affiliate-pro' ) ],
                    'form_shortcode'=> [ 'label' => __( 'Shortcode do Formulário', 'zan-affiliate-pro' ),'type' => 'text', 'hint' => __( 'Ex: [contact-form-7 id="1"]. Deixe em branco para usar o formulário nativo.', 'zan-affiliate-pro' ) ],
                ],
            ],
        ];
    }

    public function save( int $post_id ): void {
        if (
            ! isset( $_POST['zap_tmpl_fields_nonce'] ) ||
            ! wp_verify_nonce( $_POST['zap_tmpl_fields_nonce'], 'zap_tmpl_fields' ) ||
            defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ||
            ! current_user_can( 'edit_post', $post_id )
        ) return;

        $all_keys = [
            'main_aff_url', 'main_aff_label', 'hero_subtitle', 'hero_badge',
            'guarantee_text', 'urgency_text', 'pros', 'cons', 'benefits',
            'features', 'testimonials', 'listicle_items', 'listicle_intro',
            'listicle_year', 'price_from', 'free_trial', 'sw_category',
            'plans', 'mag_categories', 'stats', 'form_shortcode',
        ];

        foreach ( $all_keys as $key ) {
            $post_key = 'zap_' . $key;
            if ( ! isset( $_POST[ $post_key ] ) ) continue;

            $value = $key === 'main_aff_url'
                ? esc_url_raw( $_POST[ $post_key ] )
                : sanitize_textarea_field( $_POST[ $post_key ] );

            update_post_meta( $post_id, "_zap_$key", $value );
        }
    }
}

ZAP_Template_Meta_Fields::instance();
