<?php
/**
 * CTA Blocks
 *
 * Shortcodes for styled call-to-action blocks commonly used in affiliate posts.
 *
 * [zap_cta url="..." label="Comprar Agora" badge="Oferta Especial" img="..." price="R$ 97" old_price="R$ 197"]
 * [zap_cta_box title="..." text="..." url="..." label="..." style="highlight|warning|success"]
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

class ZAP_CTA_Blocks {

    private static ?self $instance = null;

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        add_shortcode( 'zap_cta',      [ $this, 'cta_shortcode' ] );
        add_shortcode( 'zap_cta_box',  [ $this, 'cta_box_shortcode' ] );
        add_shortcode( 'zap_button',   [ $this, 'button_shortcode' ] );
        add_shortcode( 'zap_notice',   [ $this, 'notice_shortcode' ] );
        add_shortcode( 'zap_product_card', [ $this, 'product_card_shortcode' ] );
    }

    // ── CTA Principal ─────────────────────────────────────────────────────────

    public function cta_shortcode( array $atts ): string {
        $atts = shortcode_atts( [
            'url'       => '',
            'label'     => __( 'Comprar Agora', 'zan-affiliate-pro' ),
            'badge'     => '',
            'img'       => '',
            'price'     => '',
            'old_price' => '',
            'discount'  => '',
            'style'     => 'primary',   // primary | success | warning
            'target'    => '_blank',
            'nofollow'  => 'yes',
        ], $atts, 'zap_cta' );

        $rel = $atts['nofollow'] === 'yes' ? 'nofollow sponsored' : '';
        ob_start();
        ?>
        <div class="zap-cta zap-cta--<?php echo esc_attr( $atts['style'] ); ?>">
            <?php if ( $atts['badge'] ) : ?>
                <div class="zap-cta-badge"><?php echo esc_html( $atts['badge'] ); ?></div>
            <?php endif; ?>
            <div class="zap-cta-inner">
                <?php if ( $atts['img'] ) : ?>
                    <div class="zap-cta-img">
                        <img src="<?php echo esc_url( $atts['img'] ); ?>" alt="" loading="lazy" />
                    </div>
                <?php endif; ?>
                <div class="zap-cta-content">
                    <?php if ( $atts['price'] || $atts['old_price'] ) : ?>
                        <div class="zap-cta-pricing">
                            <?php if ( $atts['old_price'] ) : ?>
                                <span class="zap-old-price"><?php echo esc_html( $atts['old_price'] ); ?></span>
                            <?php endif; ?>
                            <?php if ( $atts['price'] ) : ?>
                                <span class="zap-price"><?php echo esc_html( $atts['price'] ); ?></span>
                            <?php endif; ?>
                            <?php if ( $atts['discount'] ) : ?>
                                <span class="zap-discount-badge"><?php echo esc_html( $atts['discount'] ); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ( $atts['url'] ) : ?>
                        <a href="<?php echo esc_url( $atts['url'] ); ?>"
                           class="btn btn-<?php echo esc_attr( $atts['style'] ); ?> btn-lg btn-block zap-cta-btn"
                           rel="<?php echo esc_attr( $rel ); ?>"
                           target="<?php echo esc_attr( $atts['target'] ); ?>">
                            <?php echo esc_html( $atts['label'] ); ?>
                            <span class="zap-cta-arrow">&#8594;</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // ── CTA Box ───────────────────────────────────────────────────────────────

    public function cta_box_shortcode( array $atts, ?string $content = null ): string {
        $atts = shortcode_atts( [
            'title'  => '',
            'text'   => '',
            'url'    => '',
            'label'  => __( 'Saiba Mais', 'zan-affiliate-pro' ),
            'style'  => 'highlight',   // highlight | warning | success | info
            'icon'   => '',
        ], $atts, 'zap_cta_box' );

        ob_start();
        ?>
        <div class="zap-cta-box zap-cta-box--<?php echo esc_attr( $atts['style'] ); ?>">
            <?php if ( $atts['icon'] ) : ?>
                <div class="zap-cta-box-icon"><?php echo esc_html( $atts['icon'] ); ?></div>
            <?php endif; ?>
            <div class="zap-cta-box-body">
                <?php if ( $atts['title'] ) : ?>
                    <h4 class="zap-cta-box-title"><?php echo esc_html( $atts['title'] ); ?></h4>
                <?php endif; ?>
                <?php if ( $atts['text'] ) : ?>
                    <p><?php echo esc_html( $atts['text'] ); ?></p>
                <?php endif; ?>
                <?php if ( $content ) : ?>
                    <div><?php echo wp_kses_post( do_shortcode( $content ) ); ?></div>
                <?php endif; ?>
                <?php if ( $atts['url'] ) : ?>
                    <a href="<?php echo esc_url( $atts['url'] ); ?>"
                       class="btn btn-primary"
                       rel="nofollow sponsored"
                       target="_blank">
                        <?php echo esc_html( $atts['label'] ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // ── Button Shortcode ──────────────────────────────────────────────────────

    public function button_shortcode( array $atts, ?string $content = null ): string {
        $atts = shortcode_atts( [
            'url'      => '',
            'label'    => '',
            'style'    => 'primary',
            'size'     => '',
            'target'   => '_blank',
            'nofollow' => 'yes',
            'icon'     => '',
        ], $atts, 'zap_button' );

        $label    = $content ?: $atts['label'];
        $rel      = $atts['nofollow'] === 'yes' ? 'nofollow sponsored' : '';
        $size_cls = $atts['size'] ? 'btn-' . $atts['size'] : '';

        return sprintf(
            '<a href="%s" class="btn btn-%s %s" rel="%s" target="%s">%s%s</a>',
            esc_url( $atts['url'] ),
            esc_attr( $atts['style'] ),
            esc_attr( $size_cls ),
            esc_attr( $rel ),
            esc_attr( $atts['target'] ),
            $atts['icon'] ? '<span class="btn-icon">' . esc_html( $atts['icon'] ) . '</span> ' : '',
            esc_html( $label )
        );
    }

    // ── Notice / Alert Shortcode ──────────────────────────────────────────────

    public function notice_shortcode( array $atts, ?string $content = null ): string {
        $atts = shortcode_atts( [
            'type'  => 'info',   // info | success | warning | danger
            'title' => '',
            'icon'  => '',
        ], $atts, 'zap_notice' );

        $icons = [
            'info'    => 'ℹ️',
            'success' => '✅',
            'warning' => '⚠️',
            'danger'  => '🚫',
        ];
        $icon = $atts['icon'] ?: ( $icons[ $atts['type'] ] ?? '' );

        ob_start();
        ?>
        <div class="zap-notice zap-notice--<?php echo esc_attr( $atts['type'] ); ?>" role="alert">
            <?php if ( $icon ) : ?>
                <span class="zap-notice-icon" aria-hidden="true"><?php echo esc_html( $icon ); ?></span>
            <?php endif; ?>
            <div class="zap-notice-body">
                <?php if ( $atts['title'] ) : ?>
                    <strong class="zap-notice-title"><?php echo esc_html( $atts['title'] ); ?></strong>
                <?php endif; ?>
                <?php if ( $content ) : ?>
                    <div><?php echo wp_kses_post( do_shortcode( $content ) ); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // ── Product Card Shortcode ────────────────────────────────────────────────

    public function product_card_shortcode( array $atts ): string {
        $atts = shortcode_atts( [
            'id'        => '',
            'name'      => '',
            'img'       => '',
            'price'     => '',
            'old_price' => '',
            'rating'    => '',
            'url'       => '',
            'label'     => __( 'Ver Oferta', 'zan-affiliate-pro' ),
            'badge'     => '',
        ], $atts, 'zap_product_card' );

        // If ID provided, fetch from CPT
        if ( $atts['id'] ) {
            $post_id = (int) $atts['id'];
            $atts['name']      = $atts['name']      ?: get_the_title( $post_id );
            $atts['img']       = $atts['img']        ?: get_the_post_thumbnail_url( $post_id, 'zap-square' );
            $atts['price']     = $atts['price']      ?: get_post_meta( $post_id, '_zap_product_preco', true );
            $atts['rating']    = $atts['rating']     ?: get_post_meta( $post_id, '_zap_product_avaliacao', true );
            $atts['url']       = $atts['url']        ?: get_post_meta( $post_id, '_zap_product_aff_link', true );
        }

        ob_start();
        ?>
        <div class="zap-product-card card">
            <?php if ( $atts['badge'] ) : ?>
                <div class="zap-product-card-badge badge badge-danger"><?php echo esc_html( $atts['badge'] ); ?></div>
            <?php endif; ?>
            <?php if ( $atts['img'] ) : ?>
                <div class="card-img">
                    <img src="<?php echo esc_url( $atts['img'] ); ?>" alt="<?php echo esc_attr( $atts['name'] ); ?>" loading="lazy" />
                </div>
            <?php endif; ?>
            <div class="card-body">
                <?php if ( $atts['name'] ) : ?>
                    <h4 class="zap-product-card-name"><?php echo esc_html( $atts['name'] ); ?></h4>
                <?php endif; ?>
                <?php if ( $atts['rating'] ) :
                    $score = (float) $atts['rating'];
                    ?>
                    <div class="zap-product-card-rating">
                        <?php for ( $i = 1; $i <= 5; $i++ ) :
                            $cls = $i <= $score ? 'full' : ( $i <= $score + 0.5 ? 'half' : 'empty' );
                            ?>
                            <span class="zap-star zap-star--<?php echo esc_attr( $cls ); ?>">&#9733;</span>
                        <?php endfor; ?>
                        <span>(<?php echo esc_html( number_format( $score, 1 ) ); ?>)</span>
                    </div>
                <?php endif; ?>
                <?php if ( $atts['price'] || $atts['old_price'] ) : ?>
                    <div class="zap-product-card-price">
                        <?php if ( $atts['old_price'] ) : ?>
                            <del class="zap-old-price"><?php echo esc_html( $atts['old_price'] ); ?></del>
                        <?php endif; ?>
                        <?php if ( $atts['price'] ) : ?>
                            <strong class="zap-price"><?php echo esc_html( $atts['price'] ); ?></strong>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if ( $atts['url'] ) : ?>
                    <a href="<?php echo esc_url( $atts['url'] ); ?>"
                       class="btn btn-primary btn-block"
                       rel="nofollow sponsored"
                       target="_blank">
                        <?php echo esc_html( $atts['label'] ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

ZAP_CTA_Blocks::instance();
