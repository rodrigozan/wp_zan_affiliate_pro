<?php
/**
 * Cache Headers
 *
 * Sends browser cache headers for static assets and archive pages.
 * Works alongside server-level caching (nginx, Apache, Varnish).
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

function zap_send_cache_headers(): void {
    if ( is_user_logged_in() || is_admin() ) return;
    if ( is_singular() && comments_open() ) return; // Don't cache comment pages aggressively

    $max_age = 3600; // 1 hour default

    if ( is_front_page() || is_home() ) {
        $max_age = 900; // 15 min for homepage
    } elseif ( is_category() || is_tag() || is_archive() ) {
        $max_age = 1800; // 30 min for archives
    } elseif ( is_singular() ) {
        $max_age = 3600; // 1 hour for single posts
    }

    header( "Cache-Control: public, max-age=$max_age, s-maxage=$max_age" );
    header( 'Vary: Accept-Encoding, Accept' );
}
add_action( 'send_headers', 'zap_send_cache_headers' );

// Remove query strings from static resources (improves cache efficiency)
function zap_remove_query_strings( string $src ): string {
    if ( is_admin() ) return $src;
    $parts = explode( '?', $src );
    return $parts[0];
}
// Optionally enable: add_filter( 'script_loader_src', 'zap_remove_query_strings', 20 );
// add_filter( 'style_loader_src', 'zap_remove_query_strings', 20 );

// Minify HTML output (basic, safe version)
function zap_minify_html( string $buffer ): string {
    if ( ! zap_option( 'minify_html', false ) ) return $buffer;
    if ( is_admin() ) return $buffer;

    // Preserve pre/script/textarea blocks
    $replacements = [];
    $buffer = preg_replace_callback(
        '/<(pre|script|textarea|style)[^>]*>.*?<\/\1>/is',
        function( array $m ) use ( &$replacements ): string {
            $key = '###PRESERVE_' . count( $replacements ) . '###';
            $replacements[ $key ] = $m[0];
            return $key;
        },
        $buffer
    );

    // Collapse whitespace
    $buffer = preg_replace( '/\s{2,}/', ' ', $buffer );
    $buffer = preg_replace( '/>\s+</', '><', $buffer );

    // Restore preserved blocks
    $buffer = str_replace( array_keys( $replacements ), array_values( $replacements ), $buffer );

    return $buffer;
}
// Optional: ob_start( 'zap_minify_html' );
