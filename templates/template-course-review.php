<?php
/**
 * Template: Course / Infoproduto Review
 *
 * Review de curso online/infoproduto com:
 * - Hero com logo + preço + CTA
 * - Para quem é indicado
 * - Grade curricular / módulos
 * - Depoimentos de alunos
 * - Sobre o criador
 * - Comparativo de planos
 * Estilo Hotmart blog, Curso em Vídeo reviews.
 *
 * @package ZanAffiliatePro
 * @zap-template course-review
 */

get_header();

$post_id    = get_the_ID();
$rating     = (float) get_post_meta( $post_id, '_zap_post_rating', true );
$reviews_ct = (int)   get_post_meta( $post_id, '_zap_post_reviews', true );
$aff_url    = get_post_meta( $post_id, '_zap_main_aff_url', true );
$aff_label  = get_post_meta( $post_id, '_zap_main_aff_label', true ) ?: __( 'Garantir Minha Vaga', 'zan-affiliate-pro' );
$price_from = get_post_meta( $post_id, '_zap_price_from', true );
$free_trial = get_post_meta( $post_id, '_zap_free_trial', true );
$guarantee  = get_post_meta( $post_id, '_zap_guarantee_text', true ) ?: __( '7 dias de garantia', 'zan-affiliate-pro' );
$platform   = get_post_meta( $post_id, '_zap_sw_category', true );
$hero_sub   = get_post_meta( $post_id, '_zap_hero_subtitle', true );

// Módulos: "Módulo X — Título|Desc" por linha
$modules_raw = get_post_meta( $post_id, '_zap_features', true );
$modules     = array_filter( array_map( 'trim', explode( "\n", $modules_raw ?: '' ) ) );

// Para quem é: "emoji|texto" por linha
$for_who_raw = get_post_meta( $post_id, '_zap_benefits', true );
$for_who     = array_filter( array_map( 'trim', explode( "\n", $for_who_raw ?: '' ) ) );

// Depoimentos: "Nome|Profissão|Texto" por linha
$testis_raw = get_post_meta( $post_id, '_zap_testimonials', true );
$testis     = array_filter( array_map( 'trim', explode( "\n", $testis_raw ?: '' ) ) );

$pros_raw = array_filter( array_map( 'trim', explode( "\n", get_post_meta( $post_id, '_zap_pros', true ) ?: '' ) ) );
$cons_raw = array_filter( array_map( 'trim', explode( "\n", get_post_meta( $post_id, '_zap_cons', true ) ?: '' ) ) );
?>

<div class="zap-tpl zap-tpl--course-review">

    <!-- ══ HERO ══════════════════════════════════════════════════════════ -->
    <div class="zap-cr-hero">
        <div class="container">
            <div class="zap-cr-hero-inner">

                <div class="zap-cr-hero-text">
                    <?php if ( $platform ) : ?>
                        <span class="zap-rb-cat-badge">📚 <?php echo esc_html( $platform ); ?></span>
                    <?php endif; ?>
                    <h1 class="zap-cr-hero-title"><?php the_title(); ?></h1>
                    <?php if ( $hero_sub ) : ?>
                        <p class="zap-cr-hero-sub"><?php echo esc_html( $hero_sub ); ?></p>
                    <?php endif; ?>

                    <?php if ( $rating ) : ?>
                        <div class="zap-cr-hero-rating">
                            <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                <span class="zap-star <?php echo $i <= $rating ? 'zap-star--full' : 'zap-star--empty'; ?>" style="font-size:1.5rem">&#9733;</span>
                            <?php endfor; ?>
                            <strong><?php echo esc_html( number_format( $rating, 1 ) ); ?>/5</strong>
                            <?php if ( $reviews_ct ) : ?><span>(<?php echo esc_html( number_format_i18n( $reviews_ct ) ); ?> alunos)</span><?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="zap-cr-hero-pills">
                        <?php if ( $price_from ) : ?>
                            <span class="zap-sw-pill">💰 <?php printf( esc_html__( 'a partir de %s', 'zan-affiliate-pro' ), esc_html( $price_from ) ); ?></span>
                        <?php endif; ?>
                        <?php if ( $free_trial ) : ?>
                            <span class="zap-sw-pill zap-sw-pill--green">✔ <?php echo esc_html( $free_trial ); ?></span>
                        <?php endif; ?>
                        <span class="zap-sw-pill">🔒 <?php echo esc_html( $guarantee ); ?></span>
                    </div>

                    <?php if ( $aff_url ) : ?>
                        <a href="<?php echo esc_url( $aff_url ); ?>" class="btn btn-accent btn-lg" rel="nofollow sponsored" target="_blank">
                            <?php echo esc_html( $aff_label ); ?> →
                        </a>
                    <?php endif; ?>
                </div>

                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="zap-cr-hero-img">
                        <?php the_post_thumbnail( 'zap-medium', [ 'loading' => 'eager', 'class' => 'zap-cr-course-cover' ] ); ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <div class="container">
        <div class="zap-tpl-grid">
            <main id="primary" class="zap-tpl-main">

                <?php zap_affiliate_disclosure(); ?>

                <!-- ══ PARA QUEM É ════════════════════════════════════ -->
                <?php if ( $for_who ) : ?>
                    <section class="zap-cr-for-who">
                        <h2><?php esc_html_e( 'Para quem é este curso?', 'zan-affiliate-pro' ); ?></h2>
                        <ul class="zap-hp-benefits">
                            <?php foreach ( $for_who as $fw ) :
                                $p    = explode( '|', $fw, 2 );
                                $icon = trim( $p[0] ?? '✔' );
                                $text = trim( $p[1] ?? $p[0] );
                                ?>
                                <li><span class="zap-hp-benefit-icon"><?php echo esc_html( $icon ); ?></span><?php echo esc_html( $text ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>

                <!-- ══ CONTEÚDO EDITORIAL ══════════════════════════ -->
                <div class="entry-content">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php the_content(); ?>
                    <?php endwhile; ?>
                </div>

                <!-- ══ GRADE CURRICULAR ══════════════════════════════ -->
                <?php if ( $modules ) : ?>
                    <section class="zap-cr-modules">
                        <h2><?php esc_html_e( 'Grade Curricular', 'zan-affiliate-pro' ); ?></h2>
                        <div class="zap-cr-modules-list">
                            <?php foreach ( $modules as $idx => $mod ) :
                                $p     = explode( '|', $mod, 2 );
                                $title = trim( $p[0] ?? '' );
                                $desc  = trim( $p[1] ?? '' );
                                ?>
                                <details class="zap-cr-module">
                                    <summary class="zap-cr-module-header">
                                        <span class="zap-cr-module-num"><?php printf( esc_html__( 'Módulo %d', 'zan-affiliate-pro' ), $idx + 1 ); ?></span>
                                        <span class="zap-cr-module-title"><?php echo esc_html( $title ); ?></span>
                                        <span class="zap-cr-module-arrow">›</span>
                                    </summary>
                                    <?php if ( $desc ) : ?>
                                        <div class="zap-cr-module-body"><?php echo esc_html( $desc ); ?></div>
                                    <?php endif; ?>
                                </details>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- ══ PROS & CONS ════════════════════════════════════ -->
                <?php if ( $pros_raw || $cons_raw ) : ?>
                    <section>
                        <h2><?php esc_html_e( 'Pontos Positivos e Negativos', 'zan-affiliate-pro' ); ?></h2>
                        <div class="zap-pc-grid">
                            <?php if ( $pros_raw ) : ?>
                                <div class="zap-pros"><h5 class="zap-pros-title"><span class="zap-icon-check">✔</span> <?php esc_html_e( 'Prós', 'zan-affiliate-pro' ); ?></h5><ul><?php foreach ( $pros_raw as $p ) : ?><li><?php echo esc_html( $p ); ?></li><?php endforeach; ?></ul></div>
                            <?php endif; ?>
                            <?php if ( $cons_raw ) : ?>
                                <div class="zap-cons"><h5 class="zap-cons-title"><span class="zap-icon-x">✘</span> <?php esc_html_e( 'Contras', 'zan-affiliate-pro' ); ?></h5><ul><?php foreach ( $cons_raw as $c ) : ?><li><?php echo esc_html( $c ); ?></li><?php endforeach; ?></ul></div>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- ══ DEPOIMENTOS ════════════════════════════════════ -->
                <?php if ( $testis ) : ?>
                    <section class="zap-nl-testimonials">
                        <h2><?php esc_html_e( 'O que os alunos dizem', 'zan-affiliate-pro' ); ?></h2>
                        <div class="zap-nl-testis-grid">
                            <?php foreach ( $testis as $t ) :
                                $p = explode( '|', $t );
                                ?>
                                <div class="zap-nl-testi-card">
                                    <div class="zap-nl-testi-stars">★★★★★</div>
                                    <?php if ( ! empty( $p[2] ) ) : ?><blockquote class="zap-nl-testi-text"><?php echo esc_html( trim( $p[2] ) ); ?></blockquote><?php endif; ?>
                                    <div class="zap-nl-testi-author">
                                        <strong><?php echo esc_html( trim( $p[0] ?? '' ) ); ?></strong>
                                        <?php if ( ! empty( $p[1] ) ) : ?><span><?php echo esc_html( trim( $p[1] ) ); ?></span><?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- ══ CTA FINAL ══════════════════════════════════════ -->
                <?php if ( $aff_url ) : ?>
                    <section class="zap-nl-final-cta">
                        <div class="text-center">
                            <h2><?php esc_html_e( 'Pronto para transformar seu conhecimento?', 'zan-affiliate-pro' ); ?></h2>
                            <a href="<?php echo esc_url( $aff_url ); ?>" class="btn btn-accent btn-lg" rel="nofollow sponsored" target="_blank">
                                <?php echo esc_html( $aff_label ); ?> →
                            </a>
                            <p style="margin-top:.75rem;font-size:.875rem;color:#6b7280">🔒 <?php echo esc_html( $guarantee ); ?></p>
                        </div>
                    </section>
                <?php endif; ?>

                <footer class="entry-footer"><?php zap_entry_footer(); ?></footer>
                <?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
                <?php zap_related_posts( 3 ); ?>

            </main>

            <aside class="zap-tpl-sidebar zap-tpl-sidebar--sticky" role="complementary">
                <?php if ( $aff_url ) : ?>
                    <div class="widget zap-sidebar-cta-box">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'zap-square', [ 'class' => 'zap-sidebar-cta-img', 'loading' => 'lazy' ] ); ?>
                        <?php endif; ?>
                        <?php if ( $rating ) : ?>
                            <div class="zap-sidebar-score"><strong><?php echo esc_html( number_format( $rating, 1 ) ); ?></strong><span>/5</span></div>
                        <?php endif; ?>
                        <?php if ( $price_from ) : ?><p style="font-size:.875rem"><?php printf( esc_html__( 'A partir de %s', 'zan-affiliate-pro' ), esc_html( $price_from ) ); ?></p><?php endif; ?>
                        <a href="<?php echo esc_url( $aff_url ); ?>" class="btn btn-accent btn-block" rel="nofollow sponsored" target="_blank"><?php echo esc_html( $aff_label ); ?></a>
                        <p class="zap-sidebar-disclaimer">🔒 <?php echo esc_html( $guarantee ); ?></p>
                    </div>
                <?php endif; ?>
                <?php dynamic_sidebar( 'sidebar-1' ); ?>
            </aside>

        </div>
    </div>
</div>

<?php get_footer(); ?>
