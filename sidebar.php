<?php
/**
 * Sidebar
 *
 * @package ZanAffiliatePro
 */

if ( ! is_active_sidebar( 'sidebar-1' ) ) return;
?>
<aside id="secondary" class="widget-area" role="complementary" aria-label="<?php esc_attr_e( 'Sidebar', 'zan-affiliate-pro' ); ?>">
    <?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
