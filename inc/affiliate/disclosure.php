<?php
/**
 * Affiliate Disclosure
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'the_content', function( string $content ): string {
    if ( ! is_singular() || is_admin() ) return $content;
    if ( ! zap_option( 'show_disclosure', true ) ) return $content;

    $text = zap_option(
        'disclosure_text',
        __( '<strong>Divulgação:</strong> Este artigo pode conter links de afiliados. Se você comprar algo através desses links, podemos receber uma pequena comissão sem custo adicional para você. Isso nos ajuda a manter o site no ar. Obrigado pelo apoio!', 'zan-affiliate-pro' )
    );

    $notice = '<div class="zap-disclosure" role="note">' . wp_kses_post( $text ) . '</div>';
    return $notice . $content;
} );
