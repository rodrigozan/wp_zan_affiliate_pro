<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Ir para o conteúdo', 'zan-affiliate-pro' ); ?></a>

<div id="page" class="site">

    <header id="masthead" class="site-header <?php echo get_theme_mod( 'zap_header_sticky', true ) ? 'is-sticky' : ''; ?>">
        <div class="container">
            <div class="header-inner">

                <div class="site-branding">
                    <?php zap_the_logo(); ?>
                    <?php if ( get_bloginfo( 'description' ) && ( display_header_text() ) ) : ?>
                        <p class="site-description screen-reader-text"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
                    <?php endif; ?>
                </div>

                <nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Menu principal', 'zan-affiliate-pro' ); ?>">
                    <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                        <span></span><span></span><span></span>
                        <span class="screen-reader-text"><?php esc_html_e( 'Menu', 'zan-affiliate-pro' ); ?></span>
                    </button>
                    <?php
                    wp_nav_menu( [
                        'theme_location' => 'primary',
                        'menu_id'        => 'primary-menu',
                        'container'      => false,
                        'fallback_cb'    => false,
                    ] );
                    ?>
                </nav>

            </div>
        </div>
    </header>

    <div id="content" class="site-content">
