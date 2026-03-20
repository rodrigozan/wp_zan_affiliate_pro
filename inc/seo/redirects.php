<?php
/**
 * 301/302 Redirect Manager
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

class ZAP_Redirects {

    private static ?self $instance = null;
    private const OPTION = 'zap_redirects';

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        add_action( 'template_redirect',   [ $this, 'handle' ], 1 );
        add_action( 'admin_menu',          [ $this, 'admin_page' ] );
        add_action( 'admin_post_zap_save_redirect',   [ $this, 'save' ] );
        add_action( 'admin_post_zap_delete_redirect',  [ $this, 'delete' ] );
    }

    public function handle(): void {
        if ( ! is_404() ) return;
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $path        = strtok( $request_uri, '?' );
        $redirects   = get_option( self::OPTION, [] );

        foreach ( $redirects as $redirect ) {
            $from = rtrim( $redirect['from'], '/' );
            $curr = rtrim( $path, '/' );
            if ( $from === $curr ) {
                wp_redirect( esc_url_raw( $redirect['to'] ), (int) $redirect['code'] );
                exit;
            }
            // Wildcard support
            if ( str_ends_with( $from, '*' ) ) {
                $prefix = rtrim( substr( $from, 0, -1 ), '/' );
                if ( str_starts_with( $curr, $prefix ) ) {
                    wp_redirect( esc_url_raw( $redirect['to'] ), (int) $redirect['code'] );
                    exit;
                }
            }
        }
    }

    public function admin_page(): void {
        add_submenu_page(
            'zap-options',
            __( 'Redirecionamentos', 'zan-affiliate-pro' ),
            __( 'Redirecionamentos', 'zan-affiliate-pro' ),
            'manage_options',
            'zap-redirects',
            [ $this, 'render' ]
        );
    }

    public function render(): void {
        $redirects = get_option( self::OPTION, [] );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Gerenciador de Redirecionamentos', 'zan-affiliate-pro' ); ?></h1>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'zap_redirect_nonce', 'zap_nonce' ); ?>
                <input type="hidden" name="action" value="zap_save_redirect" />
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e( 'De (path)', 'zan-affiliate-pro' ); ?></th>
                        <td><input type="text" name="redirect_from" placeholder="/url-antiga" style="width:300px" /></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Para (URL completa)', 'zan-affiliate-pro' ); ?></th>
                        <td><input type="text" name="redirect_to" placeholder="https://..." style="width:300px" /></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Código', 'zan-affiliate-pro' ); ?></th>
                        <td>
                            <select name="redirect_code">
                                <option value="301">301 – Permanente</option>
                                <option value="302">302 – Temporário</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <p><?php submit_button( __( 'Adicionar Redirecionamento', 'zan-affiliate-pro' ), 'primary', 'submit', false ); ?></p>
            </form>

            <h2><?php esc_html_e( 'Redirecionamentos Ativos', 'zan-affiliate-pro' ); ?></h2>
            <?php if ( empty( $redirects ) ) : ?>
                <p><?php esc_html_e( 'Nenhum redirecionamento cadastrado.', 'zan-affiliate-pro' ); ?></p>
            <?php else : ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'De', 'zan-affiliate-pro' ); ?></th>
                            <th><?php esc_html_e( 'Para', 'zan-affiliate-pro' ); ?></th>
                            <th><?php esc_html_e( 'Código', 'zan-affiliate-pro' ); ?></th>
                            <th><?php esc_html_e( 'Ações', 'zan-affiliate-pro' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $redirects as $i => $r ) : ?>
                            <tr>
                                <td><code><?php echo esc_html( $r['from'] ); ?></code></td>
                                <td><a href="<?php echo esc_url( $r['to'] ); ?>" target="_blank"><?php echo esc_html( $r['to'] ); ?></a></td>
                                <td><?php echo esc_html( $r['code'] ); ?></td>
                                <td>
                                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
                                        <?php wp_nonce_field( 'zap_redirect_nonce', 'zap_nonce' ); ?>
                                        <input type="hidden" name="action" value="zap_delete_redirect" />
                                        <input type="hidden" name="index" value="<?php echo esc_attr( $i ); ?>" />
                                        <button type="submit" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Confirmar exclusão?', 'zan-affiliate-pro' ); ?>')">
                                            <?php esc_html_e( 'Excluir', 'zan-affiliate-pro' ); ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    public function save(): void {
        check_admin_referer( 'zap_redirect_nonce', 'zap_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Sem permissão.' );

        $from = '/' . ltrim( sanitize_text_field( $_POST['redirect_from'] ?? '' ), '/' );
        $to   = esc_url_raw( $_POST['redirect_to'] ?? '' );
        $code = in_array( (int) ( $_POST['redirect_code'] ?? 302 ), [ 301, 302 ], true )
            ? (int) $_POST['redirect_code']
            : 302;

        if ( $from && $to ) {
            $redirects   = get_option( self::OPTION, [] );
            $redirects[] = [ 'from' => $from, 'to' => $to, 'code' => $code ];
            update_option( self::OPTION, $redirects );
        }

        wp_redirect( admin_url( 'admin.php?page=zap-redirects&saved=1' ) );
        exit;
    }

    public function delete(): void {
        check_admin_referer( 'zap_redirect_nonce', 'zap_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Sem permissão.' );

        $index     = (int) ( $_POST['index'] ?? -1 );
        $redirects = get_option( self::OPTION, [] );
        if ( isset( $redirects[ $index ] ) ) {
            array_splice( $redirects, $index, 1 );
            update_option( self::OPTION, array_values( $redirects ) );
        }

        wp_redirect( admin_url( 'admin.php?page=zap-redirects&deleted=1' ) );
        exit;
    }
}

ZAP_Redirects::instance();
