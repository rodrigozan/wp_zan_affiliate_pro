<?php
/**
 * Comparison Table
 *
 * Shortcode: [zap_compare ids="1,2,3" fields="preco,avaliacao,garantia"]
 * Also registers a Gutenberg block via REST.
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

class ZAP_Comparison_Table {

    private static ?self $instance = null;

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        add_shortcode( 'zap_compare', [ $this, 'shortcode' ] );
        add_action( 'init',           [ $this, 'register_product_cpt' ] );
        add_action( 'add_meta_boxes', [ $this, 'meta_boxes' ] );
        add_action( 'save_post_zap_product', [ $this, 'save_product_meta' ] );
        add_action( 'wp_ajax_zap_get_comparison', [ $this, 'ajax_get' ] );
        add_action( 'wp_ajax_nopriv_zap_get_comparison', [ $this, 'ajax_get' ] );
    }

    // ── Product CPT ───────────────────────────────────────────────────────────

    public function register_product_cpt(): void {
        register_post_type( 'zap_product', [
            'labels' => [
                'name'          => __( 'Produtos', 'zan-affiliate-pro' ),
                'singular_name' => __( 'Produto', 'zan-affiliate-pro' ),
                'add_new_item'  => __( 'Novo Produto', 'zan-affiliate-pro' ),
                'edit_item'     => __( 'Editar Produto', 'zan-affiliate-pro' ),
            ],
            'public'         => false,
            'show_ui'        => true,
            'show_in_menu'   => 'zap-options',
            'supports'       => [ 'title', 'thumbnail', 'editor' ],
            'menu_icon'      => 'dashicons-products',
        ] );
    }

    // ── Shortcode ─────────────────────────────────────────────────────────────

    public function shortcode( array $atts ): string {
        $atts = shortcode_atts( [
            'ids'    => '',
            'fields' => 'preco,avaliacao,garantia,suporte,aff_link',
            'title'  => '',
            'class'  => '',
        ], $atts, 'zap_compare' );

        $ids = array_filter( array_map( 'intval', explode( ',', $atts['ids'] ) ) );
        if ( empty( $ids ) ) return '';

        $fields = array_map( 'trim', explode( ',', $atts['fields'] ) );
        $products = [];
        foreach ( $ids as $id ) {
            $post = get_post( $id ) ?: $this->get_product_by_title( $id );
            if ( $post && $post->post_type === 'zap_product' ) {
                $products[] = $post;
            }
        }

        if ( empty( $products ) ) return '';

        ob_start();
        $this->render_table( $products, $fields, $atts );
        return ob_get_clean();
    }

    private function render_table( array $products, array $fields, array $atts ): void {
        $field_labels = $this->field_labels();
        $class = esc_attr( 'zap-comparison-table ' . $atts['class'] );
        ?>
        <div class="<?php echo $class; ?>">
            <?php if ( $atts['title'] ) : ?>
                <h3 class="zap-comparison-title"><?php echo esc_html( $atts['title'] ); ?></h3>
            <?php endif; ?>
            <div class="zap-comparison-scroll">
                <table>
                    <thead>
                        <tr>
                            <th class="zap-field-col"><?php esc_html_e( 'Característica', 'zan-affiliate-pro' ); ?></th>
                            <?php foreach ( $products as $product ) : ?>
                                <th class="zap-product-col">
                                    <?php if ( has_post_thumbnail( $product->ID ) ) : ?>
                                        <?php echo get_the_post_thumbnail( $product->ID, [ 80, 80 ], [ 'class' => 'zap-product-img' ] ); ?>
                                    <?php endif; ?>
                                    <span><?php echo esc_html( $product->post_title ); ?></span>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $fields as $field ) :
                            $label = $field_labels[ $field ] ?? ucfirst( $field );
                            ?>
                            <tr class="zap-row-<?php echo esc_attr( $field ); ?>">
                                <td class="zap-field-label"><strong><?php echo esc_html( $label ); ?></strong></td>
                                <?php foreach ( $products as $product ) : ?>
                                    <td class="zap-field-value">
                                        <?php echo $this->render_field( $product->ID, $field ); // phpcs:ignore ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        <!-- CTA row -->
                        <tr class="zap-cta-row">
                            <td><?php esc_html_e( 'Ver oferta', 'zan-affiliate-pro' ); ?></td>
                            <?php foreach ( $products as $product ) :
                                $link  = get_post_meta( $product->ID, '_zap_product_aff_link', true );
                                $label = get_post_meta( $product->ID, '_zap_product_cta_label', true ) ?: __( 'Ver oferta', 'zan-affiliate-pro' );
                                ?>
                                <td>
                                    <?php if ( $link ) : ?>
                                        <a href="<?php echo esc_url( $link ); ?>"
                                           class="btn btn-primary btn-sm"
                                           rel="nofollow sponsored"
                                           target="_blank">
                                            <?php echo esc_html( $label ); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    private function render_field( int $product_id, string $field ): string {
        $value = get_post_meta( $product_id, "_zap_product_$field", true );

        // Boolean fields
        if ( in_array( $field, [ 'suporte', 'garantia', 'frete_gratis', 'parcelamento' ], true ) ) {
            if ( $value === '1' || $value === 'yes' ) {
                return '<span class="zap-yes">&#10003;</span>';
            } elseif ( $value === '0' || $value === 'no' ) {
                return '<span class="zap-no">&#10007;</span>';
            }
        }

        // Rating field
        if ( $field === 'avaliacao' ) {
            return $value ? $this->render_stars( (float) $value ) : '—';
        }

        return $value ? esc_html( $value ) : '<span class="zap-na">—</span>';
    }

    private function render_stars( float $rating ): string {
        $rating  = min( 5, max( 0, $rating ) );
        $full    = floor( $rating );
        $half    = ( $rating - $full ) >= 0.5 ? 1 : 0;
        $empty   = 5 - $full - $half;
        $out     = '<span class="zap-stars" aria-label="' . esc_attr( "$rating de 5" ) . '">';
        $out    .= str_repeat( '<span class="zap-star full">&#9733;</span>', $full );
        if ( $half ) $out .= '<span class="zap-star half">&#9734;</span>';
        $out    .= str_repeat( '<span class="zap-star empty">&#9734;</span>', (int) $empty );
        $out    .= '</span>';
        return $out;
    }

    private function field_labels(): array {
        return [
            'preco'         => __( 'Preço', 'zan-affiliate-pro' ),
            'avaliacao'     => __( 'Avaliação', 'zan-affiliate-pro' ),
            'garantia'      => __( 'Garantia', 'zan-affiliate-pro' ),
            'suporte'       => __( 'Suporte', 'zan-affiliate-pro' ),
            'frete_gratis'  => __( 'Frete Grátis', 'zan-affiliate-pro' ),
            'parcelamento'  => __( 'Parcelamento', 'zan-affiliate-pro' ),
            'desconto'      => __( 'Desconto', 'zan-affiliate-pro' ),
            'plataforma'    => __( 'Plataforma', 'zan-affiliate-pro' ),
            'aff_link'      => __( 'Link', 'zan-affiliate-pro' ),
        ];
    }

    private function get_product_by_title( $id ): ?WP_Post {
        return null; // future: search by title
    }

    // ── Product Meta Boxes ────────────────────────────────────────────────────

    public function meta_boxes(): void {
        add_meta_box(
            'zap-product-data',
            __( 'Dados do Produto', 'zan-affiliate-pro' ),
            [ $this, 'render_product_meta' ],
            'zap_product',
            'normal',
            'high'
        );
    }

    public function render_product_meta( WP_Post $post ): void {
        wp_nonce_field( 'zap_product_meta', 'zap_product_nonce' );
        $meta_fields = [
            '_zap_product_preco'      => [ 'label' => __( 'Preço', 'zan-affiliate-pro' ),           'type' => 'text' ],
            '_zap_product_avaliacao'  => [ 'label' => __( 'Avaliação (0-5)', 'zan-affiliate-pro' ),  'type' => 'number', 'step' => '0.1', 'min' => '0', 'max' => '5' ],
            '_zap_product_aff_link'   => [ 'label' => __( 'Link de Afiliado', 'zan-affiliate-pro' ), 'type' => 'url' ],
            '_zap_product_cta_label'  => [ 'label' => __( 'Texto do Botão CTA', 'zan-affiliate-pro' ), 'type' => 'text' ],
            '_zap_product_plataforma' => [ 'label' => __( 'Plataforma', 'zan-affiliate-pro' ),       'type' => 'text' ],
            '_zap_product_desconto'   => [ 'label' => __( 'Desconto', 'zan-affiliate-pro' ),          'type' => 'text' ],
        ];
        $bool_fields = [
            '_zap_product_garantia'    => __( 'Tem Garantia?', 'zan-affiliate-pro' ),
            '_zap_product_suporte'     => __( 'Tem Suporte?', 'zan-affiliate-pro' ),
            '_zap_product_frete_gratis'=> __( 'Frete Grátis?', 'zan-affiliate-pro' ),
            '_zap_product_parcelamento'=> __( 'Parcelamento?', 'zan-affiliate-pro' ),
        ];
        echo '<table class="form-table">';
        foreach ( $meta_fields as $key => $field ) {
            $val    = get_post_meta( $post->ID, $key, true );
            $name   = ltrim( $key, '_' );
            $extras = '';
            foreach ( $field as $attr => $v ) {
                if ( ! in_array( $attr, [ 'label', 'type' ], true ) ) {
                    $extras .= " $attr=\"" . esc_attr( $v ) . '"';
                }
            }
            printf(
                '<tr><th>%s</th><td><input type="%s" name="%s" value="%s" style="width:100%%" %s /></td></tr>',
                esc_html( $field['label'] ),
                esc_attr( $field['type'] ),
                esc_attr( $name ),
                esc_attr( $val ),
                $extras
            );
        }
        foreach ( $bool_fields as $key => $label ) {
            $val  = get_post_meta( $post->ID, $key, true );
            $name = ltrim( $key, '_' );
            printf(
                '<tr><th>%s</th><td><select name="%s"><option value="">—</option><option value="1" %s>%s</option><option value="0" %s>%s</option></select></td></tr>',
                esc_html( $label ),
                esc_attr( $name ),
                selected( $val, '1', false ),
                esc_html__( 'Sim', 'zan-affiliate-pro' ),
                selected( $val, '0', false ),
                esc_html__( 'Não', 'zan-affiliate-pro' )
            );
        }
        echo '</table>';
    }

    public function save_product_meta( int $post_id ): void {
        if (
            ! isset( $_POST['zap_product_nonce'] ) ||
            ! wp_verify_nonce( $_POST['zap_product_nonce'], 'zap_product_meta' ) ||
            ! current_user_can( 'edit_post', $post_id )
        ) return;

        $fields = [
            'zap_product_preco', 'zap_product_avaliacao', 'zap_product_aff_link',
            'zap_product_cta_label', 'zap_product_plataforma', 'zap_product_desconto',
            'zap_product_garantia', 'zap_product_suporte', 'zap_product_frete_gratis',
            'zap_product_parcelamento',
        ];
        foreach ( $fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                $meta_key = '_' . $field;
                $value    = $field === 'zap_product_aff_link'
                    ? esc_url_raw( $_POST[ $field ] )
                    : sanitize_text_field( $_POST[ $field ] );
                update_post_meta( $post_id, $meta_key, $value );
            }
        }
    }

    // ── AJAX ──────────────────────────────────────────────────────────────────

    public function ajax_get(): void {
        check_ajax_referer( 'zap_nonce', 'nonce' );
        $ids    = array_filter( array_map( 'intval', explode( ',', sanitize_text_field( $_POST['ids'] ?? '' ) ) ) );
        $fields = array_map( 'sanitize_key', explode( ',', sanitize_text_field( $_POST['fields'] ?? 'preco,avaliacao,garantia' ) ) );

        ob_start();
        $products = array_filter( array_map( 'get_post', $ids ), fn( $p ) => $p && $p->post_type === 'zap_product' );
        if ( $products ) {
            $this->render_table( array_values( $products ), $fields, [ 'title' => '', 'class' => '' ] );
        }
        wp_send_json_success( [ 'html' => ob_get_clean() ] );
    }
}

ZAP_Comparison_Table::instance();
