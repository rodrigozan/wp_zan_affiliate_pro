<?php
/**
 * Lazy Loading
 *
 * Adds loading="lazy" to images/iframes not already having it,
 * and enables native lazy loading for embedded content.
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

// Native lazy load on WP 5.5+ images (extends core)
add_filter( 'wp_lazy_loading_enabled', '__return_true' );

// Also add to iframes in content
add_filter( 'the_content', function( string $content ): string {
    if ( is_admin() || is_feed() ) return $content;

    // Iframes
    $content = preg_replace_callback(
        '/<iframe\s[^>]*>/i',
        function( array $m ) {
            if ( str_contains( $m[0], 'loading=' ) ) return $m[0];
            return str_replace( '<iframe ', '<iframe loading="lazy" ', $m[0] );
        },
        $content
    );

    // Images missing loading attribute
    $content = preg_replace_callback(
        '/<img\s[^>]*>/i',
        function( array $m ) {
            if ( str_contains( $m[0], 'loading=' ) ) return $m[0];
            return str_replace( '<img ', '<img loading="lazy" ', $m[0] );
        },
        $content
    );

    return $content;
}, 20 );

// Add loading=lazy to post thumbnails
add_filter( 'post_thumbnail_html', function( string $html ): string {
    if ( str_contains( $html, 'loading=' ) ) return $html;
    return str_replace( '<img ', '<img loading="lazy" ', $html );
} );

// Add decoding=async for performance
add_filter( 'wp_get_attachment_image_attributes', function( array $attr ): array {
    $attr['loading']  = 'lazy';
    $attr['decoding'] = 'async';
    return $attr;
} );
