<?php
/**
 * Page template
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
                <?php while ( have_posts() ) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
                        <header class="entry-header">
                            <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                        </header>

                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="post-thumbnail">
                                <?php the_post_thumbnail( 'zap-wide' ); ?>
                            </div>
                        <?php endif; ?>

                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    </article>
                    <?php if ( comments_open() || get_comments_number() ) : ?>
                        <?php comments_template(); ?>
                    <?php endif; ?>
                <?php endwhile; ?>
            </div>

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
