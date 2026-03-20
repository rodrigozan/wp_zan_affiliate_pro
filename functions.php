<?php
/**
 * Zan Affiliate Pro - Functions
 *
 * @package ZanAffiliatePro
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Theme constants
define( 'ZAP_VERSION',   '1.0.0' );
define( 'ZAP_DIR',       get_template_directory() );
define( 'ZAP_URI',       get_template_directory_uri() );
define( 'ZAP_INC',       ZAP_DIR . '/inc' );
define( 'ZAP_ASSETS',    ZAP_URI . '/assets' );

// ── Core ──────────────────────────────────────────────────────────────────────
require_once ZAP_INC . '/theme-setup.php';
require_once ZAP_INC . '/enqueue.php';
require_once ZAP_INC . '/customizer.php';
require_once ZAP_INC . '/template-functions.php';

// ── Template System (BeTheme-style) ───────────────────────────────────────────
require_once ZAP_INC . '/template-system/template-system.php';
require_once ZAP_INC . '/template-system/template-admin.php';

// ── Affiliate ─────────────────────────────────────────────────────────────────
require_once ZAP_INC . '/affiliate/link-manager.php';
require_once ZAP_INC . '/affiliate/comparison-table.php';
require_once ZAP_INC . '/affiliate/star-rating.php';
require_once ZAP_INC . '/affiliate/cta-blocks.php';
require_once ZAP_INC . '/affiliate/disclosure.php';

// ── SEO ───────────────────────────────────────────────────────────────────────
require_once ZAP_INC . '/seo/meta-tags.php';
require_once ZAP_INC . '/seo/schema.php';
require_once ZAP_INC . '/seo/sitemap.php';
require_once ZAP_INC . '/seo/breadcrumbs.php';
require_once ZAP_INC . '/seo/redirects.php';

// ── Performance ───────────────────────────────────────────────────────────────
require_once ZAP_INC . '/performance/lazy-load.php';
require_once ZAP_INC . '/performance/webp-support.php';
require_once ZAP_INC . '/performance/cache-headers.php';
require_once ZAP_INC . '/performance/resource-hints.php';

// ── Content ───────────────────────────────────────────────────────────────────
require_once ZAP_INC . '/content/table-of-contents.php';
require_once ZAP_INC . '/content/related-posts.php';
require_once ZAP_INC . '/content/reading-time.php';
require_once ZAP_INC . '/content/post-clone.php';

// ── Legal ─────────────────────────────────────────────────────────────────────
require_once ZAP_INC . '/legal/cookie-notice.php';

// ── Admin ─────────────────────────────────────────────────────────────────────
require_once ZAP_INC . '/admin/options-page.php';
require_once ZAP_INC . '/admin/meta-boxes.php';

// ── Push Notifications ────────────────────────────────────────────────────────
require_once ZAP_INC . '/push/push-notifications.php';
