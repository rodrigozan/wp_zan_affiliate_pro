<?php
/**
 * Template: Health & Supplement Product Page
 *
 * Página de produto de saúde/suplemento/beleza com:
 * - Hero com imagem do produto + benefícios rápidos + CTA urgência
 * - Seção "Como funciona" com steps
 * - Ingredientes/composição
 * - Depoimentos com fotos
 * - FAQ
 * - CTA final com garantia
 * Estilo típico de afiliados Hotmart/Eduzz (saúde).
 *
 * @package ZanAffiliatePro
 * @zap-template health-product
 */

get_header();

$post_id    = get_the_ID();
$aff_url    = get_post_meta( $post_id, '_zap_main_aff_url', true );
$aff_label  = get_post_meta( $post_id, '_zap_main_aff_label', true ) ?: __( 'Quero Comprar com Desconto', 'zan-affiliate-pro' );
$guarantee  = get_post_meta( $post_id, '_zap_guarantee_text', true ) ?: __( 'Garantia de 30 dias ou seu dinheiro de volta', 'zan-affiliate-pro' );
$urgency    = get_post_meta( $post_id, '_zap_urgency_text', true );
$hero_sub   = get_post_meta( $post_id, '_zap_hero_subtitle', true );

// Benefícios rápidos: "emoji|texto" por linha
$benefits_raw = get_post_meta( $post_id, '_zap_benefits', true );
$benefits     = array_filter( array_map( 'trim', explode( "\n", $benefits_raw ?: '' ) ) );

// Steps "Como funciona": "Número|Título|Desc" por linha
$steps_raw = get_post_meta( $post_id, '_zap_features', true );
$steps     = array_filter( array_map( 'trim', explode( "\n", $steps_raw ?: '' ) ) );

// Depoimentos: "Nome|Resultado|Texto" por linha
$testis_raw = get_post_meta( $post_id, '_zap_testimonials', true );
$testis     = array_filter( array_map( 'trim', explode( "\n", $testis_raw ?: '' ) ) );

$rating = (float) get_post_meta( $post_id, '_zap_post_rating', true );
?>

<div class="zap-tpl zap-tpl--health-product">

    <!-- ══ HERO ══════════════════════════════════════════════════════════ -->
    <section class="zap-hp-hero">
        <div class="container">
            <div class="zap-hp-hero-inner">

                <!-- Imagem do produto -->
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="zap-hp-product-img">
                        <?php the_post_thumbnail( 'zap-medium', [ 'loading' => 'eager', 'class' => 'zap-hp-img' ] ); ?>
                        <?php if ( $rating ) : ?>
                            <div class="zap-hp-floating-score">
                                <?php for ( $i = 1; $i <= 5; $i++ ) : ?><span class="zap-star <?php echo $i <= $rating ? 'zap-star--full' : 'zap-star--empty'; ?>">&#9733;</span><?php endfor; ?>
                                <span>(<?php echo esc_html( number_format( $rating, 1 ) ); ?>)</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Texto do hero -->
                <div class="zap-hp-hero-text">
                    <h1 class="zap-hp-hero-title"><?php the_title(); ?></h1>
                    <?php if ( $hero_sub ) : ?>
                        <p class="zap-hp-hero-sub"><?php echo esc_html( $hero_sub ); ?></p>
                    <?php endif; ?>

                    <!-- Benefícios rápidos -->
                    <?php if ( $benefits ) : ?>
                        <ul class="zap-hp-benefits">
                            <?php foreach ( $benefits as $b ) :
                                $p = explode( '|', $b, 2 );
                                $icon = trim( $p[0] ?? '✔' );
                                $text = trim( $p[1] ?? $p[0] );
                                ?>
                                <li>
                                    <span class="zap-hp-benefit-icon"><?php echo esc_html( $icon ); ?></span>
                                    <?php echo esc_html( $text ); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <!-- CTA -->
                    <?php if ( $aff_url ) : ?>
                        <div class="zap-hp-cta">
                            <?php if ( $urgency ) : ?>
                                <div class="zap-hp-urgency">⚡ <?php echo esc_html( $urgency ); ?></div>
                            <?php endif; ?>
                            <a href="<?php echo esc_url( $aff_url ); ?>" class="btn btn-accent btn-lg zap-hp-btn" rel="nofollow sponsored" target="_blank">
                                <?php echo esc_html( $aff_label ); ?> →
                            </a>
                            <div class="zap-hp-guarantee-text">🔒 <?php echo esc_html( $guarantee ); ?></div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </section>

    <div class="container">

        <?php zap_affiliate_disclosure(); ?>

        <!-- ══ COMO FUNCIONA ══════════════════════════════════════════════ -->
        <?php if ( $steps ) : ?>
            <section class="zap-hp-steps">
                <h2 class="zap-hp-section-title"><?php esc_html_e( 'Como Funciona', 'zan-affiliate-pro' ); ?></h2>
                <div class="zap-hp-steps-grid">
                    <?php foreach ( $steps as $idx => $step ) :
                        $p = explode( '|', $step );
                        ?>
                        <div class="zap-hp-step">
                            <div class="zap-hp-step-num"><?php echo esc_html( $idx + 1 ); ?></div>
                            <?php if ( ! empty( $p[1] ) ) : ?>
                                <h3><?php echo esc_html( trim( $p[1] ) ); ?></h3>
                            <?php elseif ( ! empty( $p[0] ) ) : ?>
                                <h3><?php echo esc_html( trim( $p[0] ) ); ?></h3>
                            <?php endif; ?>
                            <?php if ( ! empty( $p[2] ) ) : ?>
                                <p><?php echo esc_html( trim( $p[2] ) ); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ══ CONTEÚDO EDITORIAL ══════════════════════════════════════ -->
        <div class="entry-content zap-hp-editorial">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php the_content(); ?>
            <?php endwhile; ?>
        </div>

        <!-- ══ DEPOIMENTOS ════════════════════════════════════════════ -->
        <?php if ( $testis ) : ?>
            <section class="zap-hp-testimonials">
                <h2 class="zap-hp-section-title"><?php esc_html_e( 'Resultados Reais', 'zan-affiliate-pro' ); ?></h2>
                <div class="zap-hp-testis-grid">
                    <?php foreach ( $testis as $t ) :
                        $p      = explode( '|', $t );
                        $name   = trim( $p[0] ?? '' );
                        $result = trim( $p[1] ?? '' );
                        $text   = trim( $p[2] ?? '' );
                        ?>
                        <div class="zap-hp-testi">
                            <div class="zap-hp-testi-stars">★★★★★</div>
                            <?php if ( $result ) : ?>
                                <div class="zap-hp-testi-result"><?php echo esc_html( $result ); ?></div>
                            <?php endif; ?>
                            <?php if ( $text ) : ?>
                                <p class="zap-hp-testi-text">"<?php echo esc_html( $text ); ?>"</p>
                            <?php endif; ?>
                            <div class="zap-hp-testi-name">— <?php echo esc_html( $name ); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ══ CTA FINAL ══════════════════════════════════════════════ -->
        <?php if ( $aff_url ) : ?>
            <section class="zap-hp-final-cta">
                <?php if ( has_post_thumbnail() ) : ?>
                    <?php the_post_thumbnail( 'zap-square', [ 'class' => 'zap-hp-final-img', 'loading' => 'lazy' ] ); ?>
                <?php endif; ?>
                <h2><?php the_title(); ?></h2>
                <?php if ( $urgency ) : ?>
                    <div class="zap-hp-urgency"><?php echo esc_html( $urgency ); ?></div>
                <?php endif; ?>
                <a href="<?php echo esc_url( $aff_url ); ?>" class="btn btn-accent btn-lg zap-hp-btn" rel="nofollow sponsored" target="_blank">
                    <?php echo esc_html( $aff_label ); ?> →
                </a>
                <p class="zap-hp-guarantee-text">🔒 <?php echo esc_html( $guarantee ); ?></p>
            </section>
        <?php endif; ?>

    </div><!-- .container -->

    <footer class="entry-footer container"><?php zap_entry_footer(); ?></footer>
    <?php zap_related_posts( 3 ); ?>

</div><!-- .zap-tpl--health-product -->

<?php get_footer(); ?>
