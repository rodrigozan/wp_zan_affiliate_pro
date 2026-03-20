<?php
/**
 * Template helper functions
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get theme option with fallback.
 */
function zap_option( string $key, $default = '' ) {
    $options = get_option( 'zap_options', [] );
    return $options[ $key ] ?? $default;
}

/**
 * Render the site logo or title.
 */
function zap_the_logo() {
    if ( has_custom_logo() ) {
        the_custom_logo();
    } else {
        echo '<span class="site-title"><a href="' . esc_url( home_url( '/' ) ) . '" rel="home">' . esc_html( get_bloginfo( 'name' ) ) . '</a></span>';
    }
}

/**
 * Return formatted reading time string.
 */
function zap_get_reading_time( int $post_id = 0 ): string {
    $post_id  = $post_id ?: get_the_ID();
    $content  = get_post_field( 'post_content', $post_id );
    $words    = str_word_count( wp_strip_all_tags( $content ) );
    $minutes  = (int) ceil( $words / 200 );
    $minutes  = max( 1, $minutes );
    /* translators: %d = number of minutes */
    return sprintf( _n( '%d min de leitura', '%d min de leitura', $minutes, 'zan-affiliate-pro' ), $minutes );
}

/**
 * Print post meta row (date, author, reading time, categories).
 */
function zap_post_meta( array $parts = [ 'date', 'author', 'reading_time', 'cats' ] ) {
    echo '<div class="entry-meta">';
    if ( in_array( 'date', $parts, true ) ) {
        printf(
            '<span class="posted-on"><time datetime="%s">%s</time></span>',
            esc_attr( get_the_date( 'c' ) ),
            esc_html( get_the_date() )
        );
    }
    if ( in_array( 'author', $parts, true ) ) {
        printf(
            '<span class="byline">%s</span>',
            esc_html( get_the_author() )
        );
    }
    if ( in_array( 'reading_time', $parts, true ) ) {
        echo '<span class="reading-time">' . esc_html( zap_get_reading_time() ) . '</span>';
    }
    if ( in_array( 'cats', $parts, true ) ) {
        $cats = get_the_category_list( ', ' );
        if ( $cats ) {
            echo '<span class="cat-links">' . $cats . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
        }
    }
    echo '</div>';
}

/**
 * Template part wrapper — alias for get_template_part().
 */
function zap_part( string $slug, ?string $name = null, array $args = [] ) {
    get_template_part( "template-parts/$slug", $name, $args );
}

/**
 * Check if sidebar is active.
 */
function zap_has_sidebar(): bool {
    if ( is_page() ) {
        $layout = get_post_meta( get_the_ID(), '_zap_layout', true );
        return $layout !== 'full-width';
    }
    return is_active_sidebar( 'sidebar-1' );
}

/**
 * Print entry footer (tags, share buttons).
 */
function zap_entry_footer() {
    $tags = get_the_tag_list( '', ', ' );
    if ( $tags ) {
        echo '<div class="entry-tags"><span>' . __( 'Tags:', 'zan-affiliate-pro' ) . '</span> ' . $tags . '</div>'; // phpcs:ignore
    }
}

/**
 * Affiliate disclosure notice.
 */
function zap_affiliate_disclosure() {
    $show = zap_option( 'show_disclosure', true );
    if ( ! $show ) return;
    $text = zap_option( 'disclosure_text', __( 'Este post pode conter links de afiliados. Se você comprar algo através desses links, posso receber uma comissão sem custo adicional para você.', 'zan-affiliate-pro' ) );
    printf(
        '<div class="zap-disclosure">%s</div>',
        wp_kses_post( $text )
    );
}
