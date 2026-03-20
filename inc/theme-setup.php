<?php
/**
 * Theme Setup
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'zap_setup' ) ) :
    function zap_setup() {
        // Translations
        load_theme_textdomain( 'zan-affiliate-pro', ZAP_DIR . '/languages' );

        // Core supports
        add_theme_support( 'automatic-feed-links' );
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'html5', [
            'search-form', 'comment-form', 'comment-list',
            'gallery', 'caption', 'style', 'script',
        ] );
        add_theme_support( 'customize-selective-refresh-widgets' );
        add_theme_support( 'wp-block-styles' );
        add_theme_support( 'align-wide' );
        add_theme_support( 'responsive-embeds' );
        add_theme_support( 'editor-styles' );

        // Custom logo
        add_theme_support( 'custom-logo', [
            'height'      => 60,
            'width'       => 200,
            'flex-height' => true,
            'flex-width'  => true,
        ] );

        // Custom header
        add_theme_support( 'custom-header', [
            'default-image'      => '',
            'default-text-color' => '000000',
            'width'              => 1920,
            'height'             => 600,
            'flex-height'        => true,
        ] );

        // Post formats
        add_theme_support( 'post-formats', [
            'aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio',
        ] );

        // Image sizes
        add_image_size( 'zap-thumbnail',    400, 300, true );
        add_image_size( 'zap-medium',       800, 450, true );
        add_image_size( 'zap-wide',         1280, 640, true );
        add_image_size( 'zap-hero',         1920, 800, true );
        add_image_size( 'zap-square',       400, 400, true );

        // Menus
        register_nav_menus( [
            'primary'   => __( 'Menu Principal', 'zan-affiliate-pro' ),
            'secondary' => __( 'Menu Secundário', 'zan-affiliate-pro' ),
            'footer'    => __( 'Menu Rodapé', 'zan-affiliate-pro' ),
            'social'    => __( 'Redes Sociais', 'zan-affiliate-pro' ),
        ] );

        // Editor color palette
        add_theme_support( 'editor-color-palette', [
            [ 'name' => __( 'Primário', 'zan-affiliate-pro' ),    'slug' => 'primary',   'color' => '#2563eb' ],
            [ 'name' => __( 'Secundário', 'zan-affiliate-pro' ),  'slug' => 'secondary', 'color' => '#7c3aed' ],
            [ 'name' => __( 'Destaque', 'zan-affiliate-pro' ),    'slug' => 'accent',    'color' => '#f59e0b' ],
            [ 'name' => __( 'Sucesso', 'zan-affiliate-pro' ),     'slug' => 'success',   'color' => '#10b981' ],
            [ 'name' => __( 'Perigo', 'zan-affiliate-pro' ),      'slug' => 'danger',    'color' => '#ef4444' ],
            [ 'name' => __( 'Cinza Escuro', 'zan-affiliate-pro' ),'slug' => 'dark',      'color' => '#1f2937' ],
            [ 'name' => __( 'Branco', 'zan-affiliate-pro' ),      'slug' => 'white',     'color' => '#ffffff' ],
        ] );

        // Editor gradient presets
        add_theme_support( 'editor-gradient-presets', [
            [ 'name' => __( 'Primário', 'zan-affiliate-pro' ),  'gradient' => 'linear-gradient(135deg, #2563eb, #7c3aed)', 'slug' => 'primary-gradient' ],
            [ 'name' => __( 'Pôr do Sol', 'zan-affiliate-pro' ),'gradient' => 'linear-gradient(135deg, #f59e0b, #ef4444)', 'slug' => 'sunset' ],
        ] );
    }
endif;
add_action( 'after_setup_theme', 'zap_setup' );

// Widgets
function zap_widgets_init() {
    $defaults = [
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ];

    register_sidebar( array_merge( $defaults, [
        'name' => __( 'Sidebar Principal', 'zan-affiliate-pro' ),
        'id'   => 'sidebar-1',
    ] ) );

    for ( $i = 1; $i <= 4; $i++ ) {
        register_sidebar( array_merge( $defaults, [
            'name' => sprintf( __( 'Rodapé %d', 'zan-affiliate-pro' ), $i ),
            'id'   => "footer-$i",
        ] ) );
    }

    register_sidebar( array_merge( $defaults, [
        'name' => __( 'Antes do Conteúdo', 'zan-affiliate-pro' ),
        'id'   => 'before-content',
    ] ) );

    register_sidebar( array_merge( $defaults, [
        'name' => __( 'Após o Conteúdo', 'zan-affiliate-pro' ),
        'id'   => 'after-content',
    ] ) );
}
add_action( 'widgets_init', 'zap_widgets_init' );

// Content width
if ( ! isset( $content_width ) ) {
    $content_width = 1280;
}
