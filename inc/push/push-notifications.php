<?php
/**
 * Push Notifications Infrastructure
 *
 * Registers the Service Worker endpoint and subscription management.
 * Actual push delivery requires a VAPID-capable backend (e.g. web-push library).
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

class ZAP_Push_Notifications {

    private static ?self $instance = null;
    private const TABLE = 'zap_push_subscriptions';

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        add_action( 'wp_enqueue_scripts',            [ $this, 'enqueue' ] );
        add_action( 'wp_ajax_zap_push_subscribe',    [ $this, 'subscribe' ] );
        add_action( 'wp_ajax_nopriv_zap_push_subscribe', [ $this, 'subscribe' ] );
        add_action( 'wp_ajax_zap_push_unsubscribe',  [ $this, 'unsubscribe' ] );
        add_action( 'wp_ajax_nopriv_zap_push_unsubscribe', [ $this, 'unsubscribe' ] );
        add_action( 'init',                          [ $this, 'serve_sw' ] );
        add_action( 'publish_post',                  [ $this, 'notify_on_publish' ], 10, 2 );
        register_activation_hook( ZAP_DIR . '/functions.php', [ $this, 'create_table' ] );
        add_action( 'after_switch_theme',            [ $this, 'create_table' ] );
    }

    public function create_table(): void {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            endpoint text NOT NULL,
            p256dh varchar(255) NOT NULL,
            auth varchar(255) NOT NULL,
            user_agent varchar(255) DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY endpoint_hash (endpoint(191))
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    // ── Service Worker ────────────────────────────────────────────────────────

    public function serve_sw(): void {
        if ( $_SERVER['REQUEST_URI'] !== '/zap-sw.js' ) return;
        header( 'Content-Type: application/javascript; charset=UTF-8' );
        header( 'Service-Worker-Allowed: /' );
        readfile( ZAP_DIR . '/assets/js/service-worker.js' );
        exit;
    }

    // ── JS ────────────────────────────────────────────────────────────────────

    public function enqueue(): void {
        if ( ! zap_option( 'push_enabled', false ) ) return;
        $vapid_public = zap_option( 'push_vapid_public', '' );
        if ( ! $vapid_public ) return;

        wp_enqueue_script( 'zap-push', ZAP_ASSETS . '/js/push.js', [ 'zap-main' ], ZAP_VERSION, true );
        wp_localize_script( 'zap-push', 'zapPush', [
            'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( 'zap_nonce' ),
            'vapidPublic' => $vapid_public,
            'swUrl'       => home_url( '/zap-sw.js' ),
            'enabled'     => true,
            'strings'     => [
                'subscribe'   => __( 'Receber notificações', 'zan-affiliate-pro' ),
                'unsubscribe' => __( 'Cancelar notificações', 'zan-affiliate-pro' ),
                'blocked'     => __( 'Notificações bloqueadas no seu navegador.', 'zan-affiliate-pro' ),
            ],
        ] );
    }

    // ── AJAX: Subscribe ───────────────────────────────────────────────────────

    public function subscribe(): void {
        check_ajax_referer( 'zap_nonce', 'nonce' );

        $endpoint = sanitize_text_field( $_POST['endpoint'] ?? '' );
        $p256dh   = sanitize_text_field( $_POST['p256dh']   ?? '' );
        $auth     = sanitize_text_field( $_POST['auth']      ?? '' );

        if ( ! $endpoint || ! $p256dh || ! $auth ) {
            wp_send_json_error( 'Dados inválidos.' );
        }

        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;
        $wpdb->replace( $table, [
            'endpoint'   => $endpoint,
            'p256dh'     => $p256dh,
            'auth'       => $auth,
            'user_agent' => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ),
        ] );

        wp_send_json_success( [ 'subscribed' => true ] );
    }

    // ── AJAX: Unsubscribe ─────────────────────────────────────────────────────

    public function unsubscribe(): void {
        check_ajax_referer( 'zap_nonce', 'nonce' );
        $endpoint = sanitize_text_field( $_POST['endpoint'] ?? '' );
        if ( ! $endpoint ) {
            wp_send_json_error();
        }
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;
        $wpdb->delete( $table, [ 'endpoint' => $endpoint ] );
        wp_send_json_success();
    }

    // ── Notify on publish ─────────────────────────────────────────────────────

    public function notify_on_publish( int $post_id, WP_Post $post ): void {
        if ( ! zap_option( 'push_on_publish', true ) ) return;
        // Queue a background job to send push notifications
        // (actual sending requires web-push or an external service)
        as_schedule_single_action(
            time() + 30,
            'zap_send_push_notifications',
            [ 'post_id' => $post_id ],
            'zap-push'
        );
    }
}

// Hook for Action Scheduler (if available)
add_action( 'zap_send_push_notifications', function( int $post_id ): void {
    // Implementation: iterate subscriptions and send via web-push library
    // Requires: composer require minishlink/web-push
    do_action( 'zap_push_send', $post_id );
} );

ZAP_Push_Notifications::instance();
