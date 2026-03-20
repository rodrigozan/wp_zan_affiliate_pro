<?php
/**
 * Archive template
 *
 * @package ZanAffiliatePro
 */

get_header();
?>
<main id="primary" class="site-main">
    <div class="container py-16">
        <?php zap_breadcrumbs(); ?>
        <?php if ( have_posts() ) : ?>
            <header class="archive-header">
                <h1 class="archive-title"><?php the_archive_title(); ?></h1>
                <?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
            </header>
            <div class="has-sidebar content-area">
                <div id="main" role="main">
                    <div class="post-grid">
                        <?php while ( have_posts() ) : the_post(); ?>
                            <?php zap_part( 'content' ); ?>
                        <?php endwhile; ?>
                    </div>
                    <?php the_posts_pagination(); ?>
                </div>
                <aside id="secondary" class="widget-area" role="complementary">
                    <?php dynamic_sidebar( 'sidebar-1' ); ?>
                </aside>
            </div>
        <?php else : ?>
            <?php zap_part( 'content', 'none' ); ?>
        <?php endif; ?>
    </div>
</main>
<?php
get_footer();
