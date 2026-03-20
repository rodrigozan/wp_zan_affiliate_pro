<?php
/**
 * Template: Largura Total
 *
 * @package ZanAffiliatePro
 * @zap-template full-width
 */

get_header();
?>
<main id="primary" class="site-main template-full-width">
    <div class="container py-16">
        <?php zap_breadcrumbs(); ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
                <header class="entry-header">
                    <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                    <?php if ( is_single() ) zap_post_meta(); ?>
                </header>
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="post-thumbnail">
                        <?php the_post_thumbnail( 'zap-hero' ); ?>
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
</main>
<?php get_footer(); ?>
