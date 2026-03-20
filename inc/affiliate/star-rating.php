<?php
/**
 * Star Rating System
 *
 * Shortcode: [zap_rating score="4.5" max="5" label="Nossa avaliação" reviews="127"]
 * Also adds Schema.org Review markup.
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

class ZAP_Star_Rating {

    private static ?self $instance = null;

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        add_shortcode( 'zap_rating',      [ $this, 'rating_shortcode' ] );
        add_shortcode( 'zap_pros_cons',   [ $this, 'pros_cons_shortcode' ] );
        add_action( 'add_meta_boxes',     [ $this, 'meta_boxes' ] );
        add_action( 'save_post',          [ $this, 'save_meta' ] );
        add_action( 'wp_footer',          [ $this, 'inject_review_schema' ] );
        add_filter( 'the_content',        [ $this, 'maybe_prepend_rating' ] );
    }

    // ── Rating Shortcode ──────────────────────────────────────────────────────

    public function rating_shortcode( array $atts, ?string $content = null ): string {
        $atts = shortcode_atts( [
            'score'   => '0',
            'max'     => '5',
            'label'   => __( 'Nossa Avaliação', 'zan-affiliate-pro' ),
            'reviews' => '',
            'name'    => '',   // product name for schema
        ], $atts, 'zap_rating' );

        $score = (float) $atts['score'];
        $max   = (float) $atts['max'];
        $pct   = ( $max > 0 ) ? ( $score / $max ) * 100 : 0;

        ob_start();
        ?>
        <div class="zap-rating-box" itemscope itemtype="https://schema.org/Review">
            <?php if ( $atts['name'] ) : ?>
                <meta itemprop="name" content="<?php echo esc_attr( $atts['name'] ); ?>" />
                <div itemprop="itemReviewed" itemscope itemtype="https://schema.org/Product">
                    <meta itemprop="name" content="<?php echo esc_attr( $atts['name'] ); ?>" />
                </div>
            <?php endif; ?>

            <div class="zap-rating-label"><?php echo esc_html( $atts['label'] ); ?></div>

            <div class="zap-rating-stars" aria-label="<?php echo esc_attr( "$score de $max" ); ?>">
                <?php echo $this->build_stars( $score, (int) $max ); // phpcs:ignore ?>
            </div>

            <div class="zap-rating-score" itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">
                <meta itemprop="worstRating" content="0" />
                <meta itemprop="bestRating" content="<?php echo esc_attr( $max ); ?>" />
                <strong itemprop="ratingValue"><?php echo esc_html( number_format( $score, 1 ) ); ?></strong>
                <span>/ <?php echo esc_html( $max ); ?></span>
            </div>

            <div class="zap-rating-bar">
                <div class="zap-rating-bar-fill" style="width:<?php echo esc_attr( $pct ); ?>%"></div>
            </div>

            <?php if ( $atts['reviews'] ) : ?>
                <div class="zap-rating-count">
                    <?php printf( _n( 'Baseado em %s avaliação', 'Baseado em %s avaliações', (int) $atts['reviews'], 'zan-affiliate-pro' ), number_format_i18n( (int) $atts['reviews'] ) ); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    // ── Pros & Cons Shortcode ─────────────────────────────────────────────────

    public function pros_cons_shortcode( array $atts ): string {
        $atts = shortcode_atts( [
            'pros'  => '',
            'cons'  => '',
            'title' => '',
        ], $atts, 'zap_pros_cons' );

        $pros = array_filter( array_map( 'trim', explode( '|', $atts['pros'] ) ) );
        $cons = array_filter( array_map( 'trim', explode( '|', $atts['cons'] ) ) );

        ob_start();
        ?>
        <div class="zap-pros-cons">
            <?php if ( $atts['title'] ) : ?>
                <h4 class="zap-pc-title"><?php echo esc_html( $atts['title'] ); ?></h4>
            <?php endif; ?>
            <div class="zap-pc-grid">
                <?php if ( $pros ) : ?>
                    <div class="zap-pros">
                        <h5 class="zap-pros-title">
                            <span class="zap-icon-check">&#10003;</span>
                            <?php esc_html_e( 'Pontos Positivos', 'zan-affiliate-pro' ); ?>
                        </h5>
                        <ul>
                            <?php foreach ( $pros as $pro ) : ?>
                                <li><?php echo esc_html( $pro ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if ( $cons ) : ?>
                    <div class="zap-cons">
                        <h5 class="zap-cons-title">
                            <span class="zap-icon-x">&#10007;</span>
                            <?php esc_html_e( 'Pontos Negativos', 'zan-affiliate-pro' ); ?>
                        </h5>
                        <ul>
                            <?php foreach ( $cons as $con ) : ?>
                                <li><?php echo esc_html( $con ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // ── Stars builder ─────────────────────────────────────────────────────────

    private function build_stars( float $score, int $max = 5 ): string {
        $out  = '';
        for ( $i = 1; $i <= $max; $i++ ) {
            if ( $i <= floor( $score ) ) {
                $out .= '<span class="zap-star zap-star--full">&#9733;</span>';
            } elseif ( $i <= $score + 0.5 ) {
                $out .= '<span class="zap-star zap-star--half">&#9733;</span>';
            } else {
                $out .= '<span class="zap-star zap-star--empty">&#9734;</span>';
            }
        }
        return $out;
    }

    // ── Post meta box (per-post rating) ───────────────────────────────────────

    public function meta_boxes(): void {
        add_meta_box(
            'zap-post-rating',
            __( 'Avaliação do Post', 'zan-affiliate-pro' ),
            [ $this, 'render_meta' ],
            [ 'post', 'page' ],
            'side'
        );
    }

    public function render_meta( WP_Post $post ): void {
        wp_nonce_field( 'zap_rating_meta', 'zap_rating_nonce' );
        $score   = get_post_meta( $post->ID, '_zap_post_rating', true );
        $reviews = get_post_meta( $post->ID, '_zap_post_reviews', true );
        $show    = get_post_meta( $post->ID, '_zap_show_rating', true );
        ?>
        <table class="form-table" style="margin:0">
            <tr>
                <th style="width:120px"><?php esc_html_e( 'Exibir rating?', 'zan-affiliate-pro' ); ?></th>
                <td><input type="checkbox" name="zap_show_rating" value="1" <?php checked( $show, '1' ); ?> /></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Nota (0-5)', 'zan-affiliate-pro' ); ?></th>
                <td><input type="number" name="zap_post_rating" value="<?php echo esc_attr( $score ); ?>" min="0" max="5" step="0.1" style="width:80px" /></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Nº avaliações', 'zan-affiliate-pro' ); ?></th>
                <td><input type="number" name="zap_post_reviews" value="<?php echo esc_attr( $reviews ); ?>" min="0" style="width:80px" /></td>
            </tr>
        </table>
        <?php
    }

    public function save_meta( int $post_id ): void {
        if (
            ! isset( $_POST['zap_rating_nonce'] ) ||
            ! wp_verify_nonce( $_POST['zap_rating_nonce'], 'zap_rating_meta' ) ||
            defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ||
            ! current_user_can( 'edit_post', $post_id )
        ) return;

        update_post_meta( $post_id, '_zap_show_rating', isset( $_POST['zap_show_rating'] ) ? '1' : '0' );
        if ( isset( $_POST['zap_post_rating'] ) ) {
            update_post_meta( $post_id, '_zap_post_rating', min( 5, max( 0, (float) $_POST['zap_post_rating'] ) ) );
        }
        if ( isset( $_POST['zap_post_reviews'] ) ) {
            update_post_meta( $post_id, '_zap_post_reviews', absint( $_POST['zap_post_reviews'] ) );
        }
    }

    // ── Auto-prepend rating ───────────────────────────────────────────────────

    public function maybe_prepend_rating( string $content ): string {
        if ( ! is_singular() || is_admin() ) return $content;
        $show  = get_post_meta( get_the_ID(), '_zap_show_rating', true );
        if ( ! $show ) return $content;
        $score = get_post_meta( get_the_ID(), '_zap_post_rating', true );
        if ( ! $score ) return $content;
        $reviews = get_post_meta( get_the_ID(), '_zap_post_reviews', true );
        $box = $this->rating_shortcode( [
            'score'   => $score,
            'reviews' => $reviews,
            'name'    => get_the_title(),
        ] );
        return $box . $content;
    }

    // ── Schema.org Review injection ───────────────────────────────────────────

    public function inject_review_schema(): void {
        if ( ! is_singular() ) return;
        $score = get_post_meta( get_the_ID(), '_zap_post_rating', true );
        if ( ! $score ) return;
        $reviews = (int) get_post_meta( get_the_ID(), '_zap_post_reviews', true );
        $schema  = [
            '@context'     => 'https://schema.org',
            '@type'        => 'Review',
            'name'         => get_the_title(),
            'reviewRating' => [
                '@type'       => 'Rating',
                'ratingValue' => (float) $score,
                'bestRating'  => 5,
                'worstRating' => 0,
            ],
            'author' => [
                '@type' => 'Person',
                'name'  => get_the_author(),
            ],
        ];
        if ( $reviews ) {
            $schema['reviewCount'] = $reviews;
        }
        printf(
            '<script type="application/ld+json">%s</script>' . "\n",
            wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
        );
    }
}

ZAP_Star_Rating::instance();
