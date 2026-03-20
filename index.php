<?php
/**
 * Main template file — Blog index / archive fallback
 *
 * @package ZanAffiliatePro
 */

get_header();
?>
<main id="primary" class="site-main">
    <div class="container py-16">
        <?php zap_breadcrumbs(); ?>
        <div class="<?php echo zap_has_sidebar() ? 'has-sidebar' : ''; ?> content-area">
            <div id="main" role="main">
                <?php if ( have_posts() ) : ?>

                    <?php if ( is_home() && ! is_front_page() ) : ?>
                        <header class="page-header">
                            <h1 class="page-title"><?php single_post_title(); ?></h1>
                        </header>
                    <?php endif; ?>

                    <div class="post-grid">
                        <?php while ( have_posts() ) : the_post(); ?>
                            <?php zap_part( 'content', get_post_type() ); ?>
                        <?php endwhile; ?>
                    </div>

                    <?php the_posts_pagination( [
                        'prev_text' => __( '&laquo; Anterior', 'zan-affiliate-pro' ),
                        'next_text' => __( 'Próximo &raquo;', 'zan-affiliate-pro' ),
                        'class'     => 'pagination',
                    ] ); ?>

                <?php else : ?>
                    <?php zap_part( 'content', 'none' ); ?>
                <?php endif; ?>
            </div><!-- #main -->

            <?php if ( zap_has_sidebar() ) : ?>
                <aside id="secondary" class="widget-area" role="complementary">
                    <?php dynamic_sidebar( 'sidebar-1' ); ?>
                </aside>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php
get_footer();
