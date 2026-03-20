<?php
/**
 * Search results template
 *
 * @package ZanAffiliatePro
 */

get_header();
?>
<main id="primary" class="site-main">
    <div class="container py-16">
        <header class="page-header">
            <h1 class="page-title">
                <?php printf( __( 'Resultados para: %s', 'zan-affiliate-pro' ), '<span>' . esc_html( get_search_query() ) . '</span>' ); ?>
            </h1>
        </header>
        <div class="has-sidebar content-area">
            <div id="main" role="main">
                <?php if ( have_posts() ) : ?>
                    <div class="post-grid">
                        <?php while ( have_posts() ) : the_post(); ?>
                            <?php zap_part( 'content' ); ?>
                        <?php endwhile; ?>
                    </div>
                    <?php the_posts_pagination(); ?>
                <?php else : ?>
                    <p><?php esc_html_e( 'Nenhum resultado encontrado.', 'zan-affiliate-pro' ); ?></p>
                    <?php get_search_form(); ?>
                <?php endif; ?>
            </div>
            <aside id="secondary" class="widget-area" role="complementary">
                <?php dynamic_sidebar( 'sidebar-1' ); ?>
            </aside>
        </div>
    </div>
</main>
<?php
get_footer();
