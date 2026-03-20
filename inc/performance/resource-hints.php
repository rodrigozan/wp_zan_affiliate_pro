<?php
/**
 * Resource Hints (preconnect, dns-prefetch, preload)
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', function(): void {
    echo "\n<!-- Resource Hints -->\n";
    // Preconnect for Google Fonts
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";

    // DNS prefetch for common affiliate networks
    $domains = apply_filters( 'zap_dns_prefetch_domains', [
        'https://hotmart.com',
        'https://kiwify.app',
        'https://eduzz.com',
        'https://monetizze.com.br',
        'https://amazon.com.br',
    ] );
    foreach ( $domains as $domain ) {
        echo '<link rel="dns-prefetch" href="' . esc_url( $domain ) . '">' . "\n";
    }
    echo "<!-- /Resource Hints -->\n\n";
}, 1 );

// Preload hero image if header image is set
add_action( 'wp_head', function(): void {
    if ( is_singular() && has_post_thumbnail() ) {
        $img = get_the_post_thumbnail_url( get_the_ID(), 'zap-wide' );
        if ( $img ) {
            echo '<link rel="preload" as="image" href="' . esc_url( $img ) . '">' . "\n";
        }
    }
}, 2 );
