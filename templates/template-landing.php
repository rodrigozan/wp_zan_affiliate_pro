<?php
/**
 * Template: Landing Page
 *
 * Sem header/footer padrão — otimizado para conversão.
 *
 * @package ZanAffiliatePro
 * @zap-template landing
 */

// Minimal head (no sidebar, no header nav)
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'template-landing no-header no-footer' ); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site landing-page">

    <!-- Landing Header — minimal -->
    <header class="landing-header">
        <div class="container">
            <div style="display:flex;align-items:center;justify-content:center;padding:1rem 0;">
                <?php zap_the_logo(); ?>
            </div>
        </div>
    </header>

    <main id="primary" class="site-main">
        <?php while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </main>

    <!-- Landing Footer — minimal -->
    <footer class="landing-footer">
        <div class="container text-center" style="padding:2rem 0;font-size:.875rem;color:#6b7280;">
            <?php
            $footer_text = get_theme_mod( 'zap_footer_text', '&copy; ' . date( 'Y' ) . ' ' . get_bloginfo( 'name' ) . '. Todos os direitos reservados.' );
            echo wp_kses_post( $footer_text );
            ?>
        </div>
    </footer>

</div>
<?php wp_footer(); ?>
</body>
</html>
