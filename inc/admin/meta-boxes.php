<?php
/**
 * General Admin Meta Boxes
 *
 * TOC toggle per post.
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes', function(): void {
    add_meta_box(
        'zap-post-options',
        __( 'Zan Affiliate — Opções do Post', 'zan-affiliate-pro' ),
        function( WP_Post $post ): void {
            wp_nonce_field( 'zap_post_options', 'zap_post_options_nonce' );
            $show_toc = get_post_meta( $post->ID, '_zap_show_toc', true );
            $show_disc = get_post_meta( $post->ID, '_zap_disable_disclosure', true );
            ?>
            <table class="form-table" style="margin:0">
                <tr>
                    <th><?php esc_html_e( 'Mostrar Índice (TOC)', 'zan-affiliate-pro' ); ?></th>
                    <td><input type="checkbox" name="zap_show_toc" value="1" <?php checked( $show_toc, '1' ); ?> /></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Desativar aviso de afiliado', 'zan-affiliate-pro' ); ?></th>
                    <td><input type="checkbox" name="zap_disable_disclosure" value="1" <?php checked( $show_disc, '1' ); ?> /></td>
                </tr>
            </table>
            <?php
        },
        [ 'post', 'page' ],
        'side'
    );
} );

add_action( 'save_post', function( int $post_id ): void {
    if (
        ! isset( $_POST['zap_post_options_nonce'] ) ||
        ! wp_verify_nonce( $_POST['zap_post_options_nonce'], 'zap_post_options' ) ||
        defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ||
        ! current_user_can( 'edit_post', $post_id )
    ) return;

    update_post_meta( $post_id, '_zap_show_toc', isset( $_POST['zap_show_toc'] ) ? '1' : '0' );
    update_post_meta( $post_id, '_zap_disable_disclosure', isset( $_POST['zap_disable_disclosure'] ) ? '1' : '0' );
} );
