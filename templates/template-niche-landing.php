<?php
/**
 * Template: Niche Landing Page
 *
 * Página de nicho estilo "melhor produto de X" com:
 * Hero com gradiente + headline + CTA, seção de benefícios/features,
 * prova social, FAQ e CTA final. Sem sidebar.
 * Inspirado nos layouts de BeTheme App/Software/SaaS.
 *
 * @package ZanAffiliatePro
 * @zap-template niche-landing
 */

get_header();

$post_id    = get_the_ID();
$aff_url    = get_post_meta( $post_id, '_zap_main_aff_url', true );
$aff_label  = get_post_meta( $post_id, '_zap_main_aff_label', true ) ?: __( 'Quero Acessar Agora', 'zan-affiliate-pro' );
$hero_sub   = get_post_meta( $post_id, '_zap_hero_subtitle', true );
$hero_badge = get_post_meta( $post_id, '_zap_hero_badge', true );
$guarantee  = get_post_meta( $post_id, '_zap_guarantee_text', true );

// Features (pipe-separated "ícone|Título|Descrição")
$features_raw = get_post_meta( $post_id, '_zap_features', true );
$features     = array_filter( array_map( 'trim', explode( "\n", $features_raw ?: '' ) ) );

// Testimonials (pipe-separated "Autor|Cargo|Texto")
$testis_raw = get_post_meta( $post_id, '_zap_testimonials', true );
$testis     = array_filter( array_map( 'trim', explode( "\n", $testis_raw ?: '' ) ) );
?>

<div class="zap-tpl zap-tpl--niche-landing">

    <!-- ════════════════════════════════════════════════════════════════
         HERO
    ═══════════════════════════════════════════════════════════════════ -->
    <section class="zap-nl-hero" <?php if ( has_post_thumbnail() ) : ?>style="background-image:url(<?php echo esc_url( get_the_post_thumbnail_url( $post_id, 'zap-hero' ) ); ?>)"<?php endif; ?>>
        <div class="zap-nl-hero-overlay"></div>
        <div class="container">
            <div class="zap-nl-hero-content">

                <?php if ( $hero_badge ) : ?>
                    <div class="zap-nl-badge"><?php echo esc_html( $hero_badge ); ?></div>
                <?php endif; ?>

                <h1 class="zap-nl-hero-title"><?php the_title(); ?></h1>

                <?php if ( $hero_sub ) : ?>
                    <p class="zap-nl-hero-sub"><?php echo esc_html( $hero_sub ); ?></p>
                <?php endif; ?>

                <?php if ( $aff_url ) : ?>
                    <div class="zap-nl-hero-cta">
                        <a href="<?php echo esc_url( $aff_url ); ?>" class="btn btn-accent btn-lg zap-nl-btn" rel="nofollow sponsored" target="_blank">
                            <?php echo esc_html( $aff_label ); ?> &rarr;
                        </a>
                        <?php if ( $guarantee ) : ?>
                            <p class="zap-nl-guarantee">🔒 <?php echo esc_html( $guarantee ); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>

    <!-- ════════════════════════════════════════════════════════════════
         FEATURES / BENEFÍCIOS
    ═══════════════════════════════════════════════════════════════════ -->
    <?php if ( $features ) : ?>
        <section class="zap-nl-features">
            <div class="container">
                <div class="zap-nl-features-grid">
                    <?php foreach ( $features as $feat ) :
                        $parts = explode( '|', $feat );
                        $icon  = trim( $parts[0] ?? '' );
                        $title = trim( $parts[1] ?? '' );
                        $desc  = trim( $parts[2] ?? '' );
                        ?>
                        <div class="zap-nl-feature-card">
                            <?php if ( $icon ) : ?>
                                <div class="zap-nl-feature-icon"><?php echo esc_html( $icon ); ?></div>
                            <?php endif; ?>
                            <?php if ( $title ) : ?>
                                <h3 class="zap-nl-feature-title"><?php echo esc_html( $title ); ?></h3>
                            <?php endif; ?>
                            <?php if ( $desc ) : ?>
                                <p class="zap-nl-feature-desc"><?php echo esc_html( $desc ); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ════════════════════════════════════════════════════════════════
         CONTEÚDO PRINCIPAL
    ═══════════════════════════════════════════════════════════════════ -->
    <section class="zap-nl-content">
        <div class="container zap-nl-content-inner">
            <div class="entry-content">
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php the_content(); ?>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- ════════════════════════════════════════════════════════════════
         DEPOIMENTOS / PROVA SOCIAL
    ═══════════════════════════════════════════════════════════════════ -->
    <?php if ( $testis ) : ?>
        <section class="zap-nl-testimonials">
            <div class="container">
                <h2 class="zap-nl-section-title"><?php esc_html_e( 'O que dizem quem já usou', 'zan-affiliate-pro' ); ?></h2>
                <div class="zap-nl-testis-grid">
                    <?php foreach ( $testis as $testi ) :
                        $parts  = explode( '|', $testi );
                        $author = trim( $parts[0] ?? '' );
                        $role   = trim( $parts[1] ?? '' );
                        $text   = trim( $parts[2] ?? '' );
                        ?>
                        <div class="zap-nl-testi-card">
                            <div class="zap-nl-testi-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                            <?php if ( $text ) : ?>
                                <blockquote class="zap-nl-testi-text"><?php echo esc_html( $text ); ?></blockquote>
                            <?php endif; ?>
                            <div class="zap-nl-testi-author">
                                <strong><?php echo esc_html( $author ); ?></strong>
                                <?php if ( $role ) : ?><span><?php echo esc_html( $role ); ?></span><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ════════════════════════════════════════════════════════════════
         CTA FINAL
    ═══════════════════════════════════════════════════════════════════ -->
    <?php if ( $aff_url ) : ?>
        <section class="zap-nl-final-cta">
            <div class="container text-center">
                <h2 class="zap-nl-final-cta-title"><?php esc_html_e( 'Pronto para começar?', 'zan-affiliate-pro' ); ?></h2>
                <a href="<?php echo esc_url( $aff_url ); ?>" class="btn btn-accent btn-lg zap-nl-btn" rel="nofollow sponsored" target="_blank">
                    <?php echo esc_html( $aff_label ); ?> &rarr;
                </a>
                <?php if ( $guarantee ) : ?>
                    <p class="zap-nl-guarantee zap-nl-guarantee--light">🔒 <?php echo esc_html( $guarantee ); ?></p>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

</div><!-- .zap-tpl--niche-landing -->

<?php get_footer(); ?>
