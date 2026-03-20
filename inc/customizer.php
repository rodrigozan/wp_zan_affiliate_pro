<?php
/**
 * Theme Customizer
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

add_action( 'customize_register', function( WP_Customize_Manager $wp_customize ): void {

    // ── Colors Panel ──────────────────────────────────────────────────────────
    $wp_customize->add_panel( 'zap_colors', [
        'title'    => __( 'Cores do Tema', 'zan-affiliate-pro' ),
        'priority' => 30,
    ] );

    $color_settings = [
        'primary'   => [ 'label' => __( 'Cor Primária', 'zan-affiliate-pro' ),   'default' => '#2563eb' ],
        'secondary' => [ 'label' => __( 'Cor Secundária', 'zan-affiliate-pro' ), 'default' => '#7c3aed' ],
        'accent'    => [ 'label' => __( 'Cor de Destaque', 'zan-affiliate-pro' ),'default' => '#f59e0b' ],
        'text'      => [ 'label' => __( 'Cor do Texto', 'zan-affiliate-pro' ),   'default' => '#1f2937' ],
        'bg'        => [ 'label' => __( 'Cor de Fundo', 'zan-affiliate-pro' ),   'default' => '#ffffff' ],
    ];

    $wp_customize->add_section( 'zap_color_section', [
        'title' => __( 'Cores', 'zan-affiliate-pro' ),
        'panel' => 'zap_colors',
    ] );

    foreach ( $color_settings as $key => $cfg ) {
        $wp_customize->add_setting( "zap_color_$key", [
            'default'           => $cfg['default'],
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage',
        ] );
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, "zap_color_$key", [
            'label'   => $cfg['label'],
            'section' => 'zap_color_section',
        ] ) );
    }

    // ── Typography ────────────────────────────────────────────────────────────
    $wp_customize->add_section( 'zap_typography', [
        'title'    => __( 'Tipografia', 'zan-affiliate-pro' ),
        'priority' => 35,
    ] );

    $font_choices = [
        'Inter'      => 'Inter',
        'Roboto'     => 'Roboto',
        'Open Sans'  => 'Open Sans',
        'Lato'       => 'Lato',
        'Poppins'    => 'Poppins',
        'Nunito'     => 'Nunito',
        'Merriweather' => 'Merriweather (Serif)',
        'Playfair Display' => 'Playfair Display (Serif)',
    ];

    $wp_customize->add_setting( 'zap_font_body', [
        'default'           => 'Inter',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ] );
    $wp_customize->add_control( 'zap_font_body', [
        'label'   => __( 'Fonte do Corpo', 'zan-affiliate-pro' ),
        'section' => 'zap_typography',
        'type'    => 'select',
        'choices' => $font_choices,
    ] );

    $wp_customize->add_setting( 'zap_font_size_base', [
        'default'           => '16',
        'sanitize_callback' => 'absint',
        'transport'         => 'postMessage',
    ] );
    $wp_customize->add_control( 'zap_font_size_base', [
        'label'   => __( 'Tamanho Base (px)', 'zan-affiliate-pro' ),
        'section' => 'zap_typography',
        'type'    => 'number',
        'input_attrs' => [ 'min' => 12, 'max' => 24, 'step' => 1 ],
    ] );

    // ── Header ────────────────────────────────────────────────────────────────
    $wp_customize->add_section( 'zap_header', [
        'title'    => __( 'Header', 'zan-affiliate-pro' ),
        'priority' => 40,
    ] );

    $wp_customize->add_setting( 'zap_header_sticky', [
        'default'           => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ] );
    $wp_customize->add_control( 'zap_header_sticky', [
        'label'   => __( 'Header fixo (sticky)', 'zan-affiliate-pro' ),
        'section' => 'zap_header',
        'type'    => 'checkbox',
    ] );

    $wp_customize->add_setting( 'zap_header_bg', [
        'default'           => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ] );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'zap_header_bg', [
        'label'   => __( 'Cor de fundo do header', 'zan-affiliate-pro' ),
        'section' => 'zap_header',
    ] ) );

    // ── Footer ────────────────────────────────────────────────────────────────
    $wp_customize->add_section( 'zap_footer', [
        'title'    => __( 'Rodapé', 'zan-affiliate-pro' ),
        'priority' => 45,
    ] );

    $wp_customize->add_setting( 'zap_footer_text', [
        'default'           => '© ' . date( 'Y' ) . ' ' . get_bloginfo( 'name' ) . '. Todos os direitos reservados.',
        'sanitize_callback' => 'wp_kses_post',
    ] );
    $wp_customize->add_control( 'zap_footer_text', [
        'label'   => __( 'Texto do rodapé', 'zan-affiliate-pro' ),
        'section' => 'zap_footer',
        'type'    => 'textarea',
    ] );
} );

// Output customizer CSS
add_action( 'wp_head', function(): void {
    $primary   = get_theme_mod( 'zap_color_primary',   '#2563eb' );
    $secondary = get_theme_mod( 'zap_color_secondary', '#7c3aed' );
    $accent    = get_theme_mod( 'zap_color_accent',    '#f59e0b' );
    $text      = get_theme_mod( 'zap_color_text',      '#1f2937' );
    $bg        = get_theme_mod( 'zap_color_bg',        '#ffffff' );
    $font      = get_theme_mod( 'zap_font_body',       'Inter' );
    $font_size = get_theme_mod( 'zap_font_size_base',  16 );
    $header_bg = get_theme_mod( 'zap_header_bg',       '#ffffff' );

    printf(
        '<style id="zap-customizer-css">:root{--color-primary:%s;--color-secondary:%s;--color-accent:%s;--color-gray-800:%s;--color-white:%s;--font-sans:"%s",sans-serif;--header-bg:%s;}html{font-size:%dpx;}</style>' . "\n",
        esc_attr( $primary ),
        esc_attr( $secondary ),
        esc_attr( $accent ),
        esc_attr( $text ),
        esc_attr( $bg ),
        esc_attr( $font ),
        esc_attr( $header_bg ),
        (int) $font_size
    );
}, 99 );
