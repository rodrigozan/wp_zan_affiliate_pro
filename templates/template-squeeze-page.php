<?php
/**
 * Template: One Page Squeeze / Lead Capture
 *
 * Página de captura de leads com:
 * - Hero com headline + formulário de opt-in
 * - Benefícios do lead magnet
 * - Prova social (número de inscritos)
 * - Garantia de privacidade
 * Sem header de navegação, rodapé mínimo. Foco em conversão.
 * Estilo típico de afiliados de email marketing.
 *
 * @package ZanAffiliatePro
 * @zap-template squeeze-page
 */

// Minimal head — sem menu, sem distrações
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'template-squeeze no-header no-footer' ); ?>>
<?php wp_body_open(); ?>

<?php
$post_id     = get_the_ID();
$hero_sub    = get_post_meta( $post_id, '_zap_hero_subtitle', true );
$hero_badge  = get_post_meta( $post_id, '_zap_hero_badge', true );
$urgency     = get_post_meta( $post_id, '_zap_urgency_text', true );
$guarantee   = get_post_meta( $post_id, '_zap_guarantee_text', true ) ?: __( 'Seus dados estão 100% seguros. Sem spam.', 'zan-affiliate-pro' );
$subscribers = get_post_meta( $post_id, '_zap_stats', true );
$aff_url     = get_post_meta( $post_id, '_zap_main_aff_url', true );
$aff_label   = get_post_meta( $post_id, '_zap_main_aff_label', true ) ?: __( 'Quero Receber Grátis!', 'zan-affiliate-pro' );

// Benefícios: "emoji|texto" por linha
$benefits_raw = get_post_meta( $post_id, '_zap_benefits', true );
$benefits     = array_filter( array_map( 'trim', explode( "\n", $benefits_raw ?: '' ) ) );

// Subscribers/stats: "Número|Label" por linha
$stats_raw = get_post_meta( $post_id, '_zap_stats', true );
$stats     = array_filter( array_map( 'trim', explode( "\n", $stats_raw ?: '' ) ) );
?>

<div class="zap-tpl zap-tpl--squeeze-page">

    <!-- Logo topo centralizado -->
    <header class="zap-sq-header">
        <div class="zap-sq-logo"><?php zap_the_logo(); ?></div>
        <?php if ( $urgency ) : ?>
            <div class="zap-sq-urgency-bar">⚡ <?php echo esc_html( $urgency ); ?></div>
        <?php endif; ?>
    </header>

    <!-- ══ HERO + FORM ════════════════════════════════════════════════════ -->
    <section class="zap-sq-hero" <?php if ( has_post_thumbnail() ) : ?>style="background-image:url(<?php echo esc_url( get_the_post_thumbnail_url( $post_id, 'zap-hero' ) ); ?>)"<?php endif; ?>>
        <div class="zap-sq-hero-overlay"></div>
        <div class="container">
            <div class="zap-sq-hero-inner">

                <!-- Left: headline + benefícios -->
                <div class="zap-sq-hero-text">
                    <?php if ( $hero_badge ) : ?>
                        <div class="zap-nl-badge"><?php echo esc_html( $hero_badge ); ?></div>
                    <?php endif; ?>

                    <h1 class="zap-sq-title"><?php the_title(); ?></h1>

                    <?php if ( $hero_sub ) : ?>
                        <p class="zap-sq-sub"><?php echo esc_html( $hero_sub ); ?></p>
                    <?php endif; ?>

                    <?php if ( $benefits ) : ?>
                        <ul class="zap-sq-benefits">
                            <?php foreach ( $benefits as $b ) :
                                $p    = explode( '|', $b, 2 );
                                $icon = trim( $p[0] ?? '✔' );
                                $text = trim( $p[1] ?? $p[0] );
                                ?>
                                <li><span><?php echo esc_html( $icon ); ?></span><?php echo esc_html( $text ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if ( $stats ) : ?>
                        <div class="zap-sq-social-proof">
                            <?php foreach ( $stats as $s ) :
                                $p = explode( '|', $s );
                                ?>
                                <div class="zap-sq-stat">
                                    <strong><?php echo esc_html( trim( $p[0] ?? '' ) ); ?></strong>
                                    <span><?php echo esc_html( trim( $p[1] ?? '' ) ); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right: opt-in box -->
                <div class="zap-sq-optin-box">
                    <?php if ( $aff_url ) : ?>
                        <!-- CTA direto (para redirecionar a um formulário externo ou página de vendas) -->
                        <div class="zap-sq-optin-inner">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'zap-square', [ 'class' => 'zap-sq-leadmagnet-img', 'loading' => 'eager' ] ); ?>
                            <?php endif; ?>
                            <h2 class="zap-sq-optin-title"><?php esc_html_e( 'Acesso Imediato e Gratuito', 'zan-affiliate-pro' ); ?></h2>
                            <a href="<?php echo esc_url( $aff_url ); ?>" class="btn btn-accent btn-lg btn-block zap-sq-btn" rel="nofollow" target="_blank">
                                <?php echo esc_html( $aff_label ); ?> →
                            </a>
                            <p class="zap-sq-guarantee">🔒 <?php echo esc_html( $guarantee ); ?></p>
                        </div>
                    <?php else : ?>
                        <!-- Formulário HTML nativo (integra com qualquer ESP via action URL) -->
                        <div class="zap-sq-optin-inner">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'zap-square', [ 'class' => 'zap-sq-leadmagnet-img', 'loading' => 'eager' ] ); ?>
                            <?php endif; ?>
                            <h2 class="zap-sq-optin-title"><?php esc_html_e( 'Acesso Gratuito Agora', 'zan-affiliate-pro' ); ?></h2>
                            <?php
                            // Render CF7 / WPForms shortcode if configured, else native form
                            $form_sc = get_post_meta( $post_id, '_zap_form_shortcode', true );
                            if ( $form_sc ) {
                                echo do_shortcode( wp_kses_post( $form_sc ) );
                            } else {
                                ?>
                                <form class="zap-sq-native-form" method="post" action="#">
                                    <input type="text"  name="first_name" placeholder="<?php esc_attr_e( 'Seu primeiro nome', 'zan-affiliate-pro' ); ?>" required />
                                    <input type="email" name="email"      placeholder="<?php esc_attr_e( 'Seu melhor e-mail', 'zan-affiliate-pro' ); ?>" required />
                                    <button type="submit" class="btn btn-accent btn-lg btn-block zap-sq-btn">
                                        <?php echo esc_html( $aff_label ); ?> →
                                    </button>
                                </form>
                                <?php
                            }
                            ?>
                            <p class="zap-sq-guarantee">🔒 <?php echo esc_html( $guarantee ); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </section>

    <!-- ══ CONTEÚDO EDITORIAL ════════════════════════════════════════════ -->
    <div class="container zap-sq-content">
        <div class="entry-content" style="max-width:760px;margin:0 auto">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php the_content(); ?>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Footer mínimo -->
    <footer class="landing-footer">
        <div class="container text-center" style="padding:2rem 0;font-size:.8rem;color:#9ca3af">
            <?php echo wp_kses_post( get_theme_mod( 'zap_footer_text', '&copy; ' . date( 'Y' ) . ' ' . get_bloginfo( 'name' ) ) ); ?>
            &bull;
            <a href="<?php echo esc_url( home_url( '/politica-de-privacidade/' ) ); ?>" style="color:inherit">
                <?php esc_html_e( 'Política de Privacidade', 'zan-affiliate-pro' ); ?>
            </a>
        </div>
    </footer>

</div><!-- .zap-tpl--squeeze-page -->

<?php wp_footer(); ?>
</body>
</html>
