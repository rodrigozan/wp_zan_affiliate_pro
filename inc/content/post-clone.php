<?php
/**
 * Post Clone (WP Clone Master)
 *
 * Adds a "Clonar" row action in wp-admin posts/pages list.
 * Creates a draft copy preserving all content, meta fields, categories and tags.
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

class ZAP_Post_Clone {

    private static ?self $instance = null;

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        add_filter( 'post_row_actions',  [ $this, 'add_row_action' ], 10, 2 );
        add_filter( 'page_row_actions',  [ $this, 'add_row_action' ], 10, 2 );
        add_action( 'admin_action_zap_clone_post', [ $this, 'clone_post' ] );
    }

    public function add_row_action( array $actions, WP_Post $post ): array {
        if ( ! current_user_can( 'edit_posts' ) ) return $actions;

        $url = wp_nonce_url(
            admin_url( 'admin.php?action=zap_clone_post&post=' . $post->ID ),
            'zap_clone_' . $post->ID
        );

        $actions['zap_clone'] = '<a href="' . esc_url( $url ) . '" title="' . esc_attr__( 'Clonar este item como rascunho', 'zan-affiliate-pro' ) . '">'
            . __( 'Clonar', 'zan-affiliate-pro' )
            . '</a>';

        return $actions;
    }

    public function clone_post(): void {
        $post_id = (int) ( $_GET['post'] ?? 0 );

        if ( ! $post_id ) wp_die( 'ID inválido.' );
        check_admin_referer( 'zap_clone_' . $post_id );
        if ( ! current_user_can( 'edit_post', $post_id ) ) wp_die( 'Sem permissão.' );

        $original = get_post( $post_id );
        if ( ! $original ) wp_die( 'Post não encontrado.' );

        // Create the clone
        $new_id = wp_insert_post( [
            'post_title'     => $original->post_title . ' ' . __( '(Cópia)', 'zan-affiliate-pro' ),
            'post_content'   => $original->post_content,
            'post_excerpt'   => $original->post_excerpt,
            'post_status'    => 'draft',
            'post_type'      => $original->post_type,
            'post_author'    => get_current_user_id(),
            'menu_order'     => $original->menu_order,
            'comment_status' => $original->comment_status,
            'ping_status'    => $original->ping_status,
        ] );

        if ( is_wp_error( $new_id ) ) {
            wp_die( esc_html( $new_id->get_error_message() ) );
        }

        // Copy meta
        $meta = get_post_meta( $post_id );
        if ( $meta ) {
            foreach ( $meta as $key => $values ) {
                // Skip internal WP keys and our click counters
                if ( in_array( $key, [ '_edit_lock', '_edit_last' ], true ) ) continue;
                if ( str_starts_with( $key, '_zap_clicks' ) ) continue;

                foreach ( $values as $value ) {
                    add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
                }
            }
        }

        // Copy taxonomy terms
        $taxonomies = get_object_taxonomies( $original->post_type );
        foreach ( $taxonomies as $taxonomy ) {
            $terms = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'ids' ] );
            if ( $terms && ! is_wp_error( $terms ) ) {
                wp_set_object_terms( $new_id, $terms, $taxonomy );
            }
        }

        // Copy featured image
        $thumb_id = get_post_thumbnail_id( $post_id );
        if ( $thumb_id ) {
            set_post_thumbnail( $new_id, $thumb_id );
        }

        // Redirect to edit screen of new post
        $redirect = admin_url( 'post.php?action=edit&post=' . $new_id );
        wp_redirect( $redirect );
        exit;
    }
}

ZAP_Post_Clone::instance();
