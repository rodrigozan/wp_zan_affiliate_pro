<?php
/**
 * Reading Time
 *
 * Already implemented as zap_get_reading_time() in template-functions.php.
 * This file adds the widget and shortcode.
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

add_shortcode( 'zap_reading_time', function( array $atts ): string {
    $atts = shortcode_atts( [ 'post_id' => 0 ], $atts );
    return '<span class="zap-reading-time">' . esc_html( zap_get_reading_time( (int) $atts['post_id'] ) ) . '</span>';
} );
