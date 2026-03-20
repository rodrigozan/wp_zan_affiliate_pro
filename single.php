<?php
/**
 * Single post template
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
                            <?php zap_post_meta(); ?>
                        </header>

                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="post-thumbnail">
                                <?php the_post_thumbnail( 'zap-wide', [ 'class' => 'wp-post-image' ] ); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( is_active_sidebar( 'before-content' ) ) : ?>
                            <div class="before-content-widgets">
                                <?php dynamic_sidebar( 'before-content' ); ?>
                            </div>
                        <?php endif; ?>

                        <div class="entry-content">
                            <?php the_content(); ?>
                            <?php
                            wp_link_pages( [
                                'before' => '<div class="page-links">' . __( 'Páginas:', 'zan-affiliate-pro' ),
                                'after'  => '</div>',
                            ] );
                            ?>
                        </div>

                        <footer class="entry-footer">
                            <?php zap_entry_footer(); ?>
                        </footer>

                        <?php if ( is_active_sidebar( 'after-content' ) ) : ?>
                            <div class="after-content-widgets">
                                <?php dynamic_sidebar( 'after-content' ); ?>
                            </div>
                        <?php endif; ?>
                    </article>

                    <?php
                    the_post_navigation( [
                        'prev_text' => '<span class="nav-subtitle">' . __( 'Anterior', 'zan-affiliate-pro' ) . '</span><span class="nav-title">%title</span>',
                        'next_text' => '<span class="nav-subtitle">' . __( 'Próximo', 'zan-affiliate-pro' ) . '</span><span class="nav-title">%title</span>',
                    ] );
                    ?>

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
