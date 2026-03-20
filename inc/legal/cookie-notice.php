<?php
/**
 * Cookie Notice (LGPD / GDPR)
 *
 * Floating banner with Accept / Reject options.
 * Stores consent in a cookie (1 year).
 * Conditionally loads analytics scripts only after consent.
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

class ZAP_Cookie_Notice {

    private static ?self $instance = null;

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        add_action( 'wp_footer', [ $this, 'render' ] );
        add_action( 'wp_head',   [ $this, 'inline_script' ], 1 );
    }

    public function render(): void {
        if ( ! zap_option( 'cookie_notice_enabled', true ) ) return;

        $text       = zap_option( 'cookie_notice_text', __( 'Utilizamos cookies para melhorar sua experiência e exibir anúncios personalizados. Ao continuar navegando, você concorda com nossa <a href="/politica-de-privacidade/">Política de Privacidade</a> e conformidade com a <strong>LGPD</strong>.', 'zan-affiliate-pro' ) );
        $accept     = zap_option( 'cookie_accept_label', __( 'Aceitar', 'zan-affiliate-pro' ) );
        $reject     = zap_option( 'cookie_reject_label', __( 'Recusar', 'zan-affiliate-pro' ) );
        $manage     = zap_option( 'cookie_manage_label', __( 'Gerenciar', 'zan-affiliate-pro' ) );
        $policy_url = zap_option( 'cookie_policy_url', '/politica-de-privacidade/' );
        ?>
        <div id="zap-cookie-notice" class="zap-cookie-notice" role="dialog" aria-label="<?php esc_attr_e( 'Aviso de cookies', 'zan-affiliate-pro' ); ?>" aria-describedby="zap-cookie-text" hidden>
            <div class="zap-cookie-inner">
                <div class="zap-cookie-icon" aria-hidden="true">🍪</div>
                <div class="zap-cookie-content">
                    <p id="zap-cookie-text"><?php echo wp_kses_post( $text ); ?></p>
                </div>
                <div class="zap-cookie-actions">
                    <button id="zap-cookie-accept" class="btn btn-primary btn-sm" type="button">
                        <?php echo esc_html( $accept ); ?>
                    </button>
                    <button id="zap-cookie-reject" class="btn btn-secondary btn-sm" type="button">
                        <?php echo esc_html( $reject ); ?>
                    </button>
                    <?php if ( $policy_url ) : ?>
                        <a href="<?php echo esc_url( $policy_url ); ?>" class="zap-cookie-policy-link">
                            <?php echo esc_html( $manage ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function inline_script(): void {
        if ( ! zap_option( 'cookie_notice_enabled', true ) ) return;
        $ga_id  = zap_option( 'google_analytics_id', '' );
        $gtm_id = zap_option( 'google_tag_manager_id', '' );
        ?>
        <script>
        (function() {
            'use strict';
            var COOKIE_NAME = 'zap_cookie_consent';
            var COOKIE_DAYS = 365;

            function getCookie(name) {
                var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
                return v ? decodeURIComponent(v[2]) : null;
            }

            function setCookie(name, value, days) {
                var d = new Date();
                d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
                document.cookie = name + '=' + encodeURIComponent(value) + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
            }

            function loadAnalytics() {
                <?php if ( $ga_id ) : ?>
                (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
                ga('create', '<?php echo esc_js( $ga_id ); ?>', 'auto');
                ga('send', 'pageview');
                <?php endif; ?>
                <?php if ( $gtm_id ) : ?>
                (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
                })(window,document,'script','dataLayer','<?php echo esc_js( $gtm_id ); ?>');
                <?php endif; ?>
                document.dispatchEvent(new Event('zap:consent:accepted'));
            }

            var consent = getCookie(COOKIE_NAME);

            if (consent === 'accepted') {
                loadAnalytics();
            } else if (consent === null) {
                document.addEventListener('DOMContentLoaded', function() {
                    var notice = document.getElementById('zap-cookie-notice');
                    if (notice) notice.hidden = false;

                    var btnAccept = document.getElementById('zap-cookie-accept');
                    var btnReject = document.getElementById('zap-cookie-reject');

                    if (btnAccept) {
                        btnAccept.addEventListener('click', function() {
                            setCookie(COOKIE_NAME, 'accepted', COOKIE_DAYS);
                            notice.hidden = true;
                            loadAnalytics();
                        });
                    }
                    if (btnReject) {
                        btnReject.addEventListener('click', function() {
                            setCookie(COOKIE_NAME, 'rejected', COOKIE_DAYS);
                            notice.hidden = true;
                            document.dispatchEvent(new Event('zap:consent:rejected'));
                        });
                    }
                });
            }
        })();
        </script>
        <?php
    }
}

ZAP_Cookie_Notice::instance();
