<?php
/**
 * Template: Software / SaaS Review
 *
 * Review aprofundado de ferramenta/software com:
 * - Hero com screenshot, preço e botão CTA
 * - Grid de features com ícone/título/descrição
 * - Tabela de planos/preços
 * - Pros & Cons
 * - Veredicto final com nota
 * Estilo G2, Capterra, Tecnoblog.
 *
 * @package ZanAffiliatePro
 * @zap-template software-review
 */

get_header();

$post_id     = get_the_ID();
$rating      = (float) get_post_meta( $post_id, '_zap_post_rating', true );
$reviews     = (int)   get_post_meta( $post_id, '_zap_post_reviews', true );
$aff_url     = get_post_meta( $post_id, '_zap_main_aff_url', true );
$aff_label   = get_post_meta( $post_id, '_zap_main_aff_label', true ) ?: __( 'Testar Grátis', 'zan-affiliate-pro' );
$price_from  = get_post_meta( $post_id, '_zap_price_from', true );
$free_trial  = get_post_meta( $post_id, '_zap_free_trial', true );
$category_sw = get_post_meta( $post_id, '_zap_sw_category', true );

// Features: "emoji|Título|Desc" por linha
$features_raw = get_post_meta( $post_id, '_zap_features', true );
$features     = array_filter( array_map( 'trim', explode( "\n", $features_raw ?: '' ) ) );

// Plans: "Nome|Preço|Desc" por linha
$plans_raw = get_post_meta( $post_id, '_zap_plans', true );
$plans     = array_filter( array_map( 'trim', explode( "\n", $plans_raw ?: '' ) ) );

$pros_raw = get_post_meta( $post_id, '_zap_pros', true );
$cons_raw = get_post_meta( $post_id, '_zap_cons', true );
$pros     = array_filter( array_map( 'trim', explode( "\n", $pros_raw ?: '' ) ) );
$cons     = array_filter( array_map( 'trim', explode( "\n", $cons_raw ?: '' ) ) );
?>

<div class="zap-tpl zap-tpl--software-review">

    <!-- Breadcrumb -->
    <div class="zap-tpl-breadcrumb-bar">
        <div class="container"><?php zap_breadcrumbs(); ?></div>
    </div>

    <!-- ══ HERO ══════════════════════════════════════════════════════════ -->
    <div class="zap-sw-hero">
        <div class="container">
            <div class="zap-sw-hero-inner">

                <div class="zap-sw-hero-text">
                    <?php if ( $category_sw ) : ?>
                        <span class="zap-rb-cat-badge"><?php echo esc_html( $category_sw ); ?></span>
                    <?php endif; ?>
                    <h1 class="zap-sw-hero-title"><?php the_title(); ?></h1>

                    <?php if ( $rating ) : ?>
                        <div class="zap-sw-hero-rating">
                            <div class="zap-l10-item-score" style="font-size:2.5rem"><?php echo esc_html( number_format( $rating, 1 ) ); ?></div>
                            <div>
                                <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                    <span class="zap-star <?php echo $i <= $rating ? 'zap-star--full' : 'zap-star--empty'; ?>">&#9733;</span>
                                <?php endfor; ?>
                                <?php if ( $reviews ) : ?>
                                    <div style="font-size:.875rem;color:#6b7280;margin-top:4px"><?php printf( _n( '%s avaliação', '%s avaliações', $reviews, 'zan-affiliate-pro' ), number_format_i18n( $reviews ) ); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="zap-sw-hero-pills">
                        <?php if ( $price_from ) : ?>
                            <span class="zap-sw-pill">💰 <?php printf( esc_html__( 'A partir de %s', 'zan-affiliate-pro' ), esc_html( $price_from ) ); ?></span>
                        <?php endif; ?>
                        <?php if ( $free_trial ) : ?>
                            <span class="zap-sw-pill zap-sw-pill--green">✔ <?php echo esc_html( $free_trial ); ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ( $aff_url ) : ?>
                        <div class="zap-sw-hero-cta">
                            <a href="<?php echo esc_url( $aff_url ); ?>" class="btn btn-primary btn-lg" rel="nofollow sponsored" target="_blank">
                                <?php echo esc_html( $aff_label ); ?> →
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="zap-sw-hero-screenshot">
                        <?php the_post_thumbnail( 'zap-medium', [ 'class' => 'zap-sw-screenshot-img', 'loading' => 'eager' ] ); ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <div class="container">
        <div class="zap-tpl-grid">
            <main id="primary" class="zap-tpl-main">

                <?php zap_affiliate_disclosure(); ?>

                <!-- ══ FEATURES ══════════════════════════════════════════ -->
                <?php if ( $features ) : ?>
                    <section class="zap-sw-features">
                        <h2><?php esc_html_e( 'Principais Recursos', 'zan-affiliate-pro' ); ?></h2>
                        <div class="zap-sw-features-grid">
                            <?php foreach ( $features as $f ) :
                                $p = explode( '|', $f );
                                ?>
                                <div class="zap-sw-feature">
                                    <?php if ( ! empty( $p[0] ) ) : ?>
                                        <div class="zap-sw-feature-icon"><?php echo esc_html( trim( $p[0] ) ); ?></div>
                                    <?php endif; ?>
                                    <div class="zap-sw-feature-body">
                                        <?php if ( ! empty( $p[1] ) ) : ?>
                                            <strong><?php echo esc_html( trim( $p[1] ) ); ?></strong>
                                        <?php endif; ?>
                                        <?php if ( ! empty( $p[2] ) ) : ?>
                                            <p><?php echo esc_html( trim( $p[2] ) ); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- ══ CONTEÚDO PRINCIPAL ══════════════════════════════ -->
                <div class="entry-content">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php the_content(); ?>
                    <?php endwhile; ?>
                </div>

                <!-- ══ PLANOS DE PREÇO ═════════════════════════════════ -->
                <?php if ( $plans ) : ?>
                    <section class="zap-sw-plans">
                        <h2><?php esc_html_e( 'Planos e Preços', 'zan-affiliate-pro' ); ?></h2>
                        <div class="zap-sw-plans-grid">
                            <?php foreach ( $plans as $idx => $plan ) :
                                $p    = explode( '|', $plan );
                                $name = trim( $p[0] ?? '' );
                                $price= trim( $p[1] ?? '' );
                                $desc = trim( $p[2] ?? '' );
                                $is_popular = $idx === 1;
                                ?>
                                <div class="zap-sw-plan <?php echo $is_popular ? 'zap-sw-plan--popular' : ''; ?>">
                                    <?php if ( $is_popular ) : ?>
                                        <div class="zap-sw-plan-badge"><?php esc_html_e( 'Mais Popular', 'zan-affiliate-pro' ); ?></div>
                                    <?php endif; ?>
                                    <div class="zap-sw-plan-name"><?php echo esc_html( $name ); ?></div>
                                    <div class="zap-sw-plan-price"><?php echo esc_html( $price ); ?></div>
                                    <div class="zap-sw-plan-desc"><?php echo esc_html( $desc ); ?></div>
                                    <?php if ( $aff_url ) : ?>
                                        <a href="<?php echo esc_url( $aff_url ); ?>" class="btn <?php echo $is_popular ? 'btn-primary' : 'btn-secondary'; ?> btn-block" rel="nofollow sponsored" target="_blank">
                                            <?php esc_html_e( 'Escolher Plano', 'zan-affiliate-pro' ); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- ══ PROS & CONS ═════════════════════════════════════ -->
                <?php if ( $pros || $cons ) : ?>
                    <section class="zap-sw-pc">
                        <h2><?php esc_html_e( 'Prós e Contras', 'zan-affiliate-pro' ); ?></h2>
                        <div class="zap-pc-grid">
                            <?php if ( $pros ) : ?>
                                <div class="zap-pros">
                                    <h5 class="zap-pros-title"><span class="zap-icon-check">✔</span> <?php esc_html_e( 'Pontos Positivos', 'zan-affiliate-pro' ); ?></h5>
                                    <ul><?php foreach ( $pros as $p ) : ?><li><?php echo esc_html( $p ); ?></li><?php endforeach; ?></ul>
                                </div>
                            <?php endif; ?>
                            <?php if ( $cons ) : ?>
                                <div class="zap-cons">
                                    <h5 class="zap-cons-title"><span class="zap-icon-x">✘</span> <?php esc_html_e( 'Pontos Negativos', 'zan-affiliate-pro' ); ?></h5>
                                    <ul><?php foreach ( $cons as $c ) : ?><li><?php echo esc_html( $c ); ?></li><?php endforeach; ?></ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- ══ VEREDICTO FINAL ════════════════════════════════ -->
                <?php if ( $rating ) : ?>
                    <section class="zap-sw-verdict">
                        <h2><?php esc_html_e( 'Veredicto Final', 'zan-affiliate-pro' ); ?></h2>
                        <div class="zap-sw-verdict-inner">
                            <div class="zap-sw-verdict-score">
                                <div style="font-size:4rem;font-weight:800;color:var(--color-accent)"><?php echo esc_html( number_format( $rating, 1 ) ); ?></div>
                                <div><?php for ( $i = 1; $i <= 5; $i++ ) : ?><span class="zap-star <?php echo $i <= $rating ? 'zap-star--full' : 'zap-star--empty'; ?>">&#9733;</span><?php endfor; ?></div>
                                <div style="font-size:.875rem;color:#6b7280;margin-top:4px"><?php esc_html_e( 'Nossa nota', 'zan-affiliate-pro' ); ?></div>
                            </div>
                            <?php if ( $aff_url ) : ?>
                                <div class="zap-sw-verdict-cta">
                                    <a href="<?php echo esc_url( $aff_url ); ?>" class="btn btn-primary btn-lg" rel="nofollow sponsored" target="_blank">
                                        <?php echo esc_html( $aff_label ); ?> →
                                    </a>
                                </div>
                            <?php endif; ?>
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
                        <strong class="zap-sw-sidebar-title"><?php the_title(); ?></strong>
                        <?php if ( $rating ) : ?>
                            <div class="zap-sidebar-score">
                                <strong><?php echo esc_html( number_format( $rating, 1 ) ); ?></strong><span>/5</span>
                            </div>
                        <?php endif; ?>
                        <?php if ( $price_from ) : ?><p style="font-size:.875rem;color:#6b7280"><?php printf( esc_html__( 'Planos a partir de %s', 'zan-affiliate-pro' ), esc_html( $price_from ) ); ?></p><?php endif; ?>
                        <a href="<?php echo esc_url( $aff_url ); ?>" class="btn btn-primary btn-block" rel="nofollow sponsored" target="_blank"><?php echo esc_html( $aff_label ); ?></a>
                        <?php if ( $free_trial ) : ?><p class="zap-sidebar-disclaimer">✔ <?php echo esc_html( $free_trial ); ?></p><?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php dynamic_sidebar( 'sidebar-1' ); ?>
            </aside>

        </div>
    </div>
</div>

<?php get_footer(); ?>
