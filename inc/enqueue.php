<?php
/**
 * Enqueue Scripts & Styles
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

function zap_enqueue_scripts() {
    // Google Fonts
    wp_enqueue_style(
        'zap-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap',
        [],
        null
    );

    // Main stylesheet (style.css = theme header, loaded automatically)
    // Additional CSS
    wp_enqueue_style(
        'zap-affiliate',
        ZAP_ASSETS . '/css/affiliate.css',
        [ 'zap-google-fonts' ],
        ZAP_VERSION
    );

    wp_enqueue_style(
        'zap-components',
        ZAP_ASSETS . '/css/components.css',
        [ 'zap-affiliate' ],
        ZAP_VERSION
    );

    // Main JS
    wp_enqueue_script(
        'zap-main',
        ZAP_ASSETS . '/js/main.js',
        [],
        ZAP_VERSION,
        true
    );

    wp_enqueue_script(
        'zap-affiliate',
        ZAP_ASSETS . '/js/affiliate.js',
        [ 'zap-main' ],
        ZAP_VERSION,
        true
    );

    // Localize JS config
    wp_localize_script( 'zap-main', 'zapConfig', [
        'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
        'nonce'     => wp_create_nonce( 'zap_nonce' ),
        'homeUrl'   => home_url(),
        'themeUrl'  => ZAP_URI,
        'isLoggedIn'=> is_user_logged_in(),
        'strings'   => [
            'copied'       => __( 'Link copiado!', 'zan-affiliate-pro' ),
            'loading'      => __( 'Carregando...', 'zan-affiliate-pro' ),
            'error'        => __( 'Erro. Tente novamente.', 'zan-affiliate-pro' ),
        ],
    ] );

    // Comments
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'zap_enqueue_scripts' );

// Admin styles
function zap_admin_enqueue( $hook ) {
    wp_enqueue_style(
        'zap-admin',
        ZAP_ASSETS . '/css/admin.css',
        [],
        ZAP_VERSION
    );
    wp_enqueue_script(
        'zap-admin',
        ZAP_ASSETS . '/js/admin.js',
        [ 'jquery' ],
        ZAP_VERSION,
        true
    );
    wp_localize_script( 'zap-admin', 'zapAdmin', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'zap_admin_nonce' ),
    ] );
}
add_action( 'admin_enqueue_scripts', 'zap_admin_enqueue' );

// Remove unused default WordPress styles
function zap_dequeue_defaults() {
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'zap_dequeue_defaults', 100 );
