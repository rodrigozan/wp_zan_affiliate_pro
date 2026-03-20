<?php
/**
 * Template: Review de Produto
 *
 * Schema Review, rating, pros/cons, CTA otimizados.
 *
 * @package ZanAffiliatePro
 * @zap-template review
 */

get_header();
$post_id = get_the_ID();
$rating  = get_post_meta( $post_id, '_zap_post_rating', true );
?>
<main id="primary" class="site-main template-review">
    <div class="container py-16">
        <?php zap_breadcrumbs(); ?>
        <div class="has-sidebar content-area">
            <div id="main" role="main">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry-review' ); ?> itemscope itemtype="https://schema.org/Review">

                        <header class="entry-header">
                            <?php the_title( '<h1 class="entry-title" itemprop="name">', '</h1>' ); ?>
                            <?php zap_post_meta(); ?>
                        </header>

                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="post-thumbnail">
                                <?php the_post_thumbnail( 'zap-wide' ); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( $rating ) : ?>
                            <div class="review-rating-summary card card-body" style="margin-bottom:2rem;">
                                <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;">
                                    <div style="font-size:4rem;font-weight:800;color:var(--color-accent);"><?php echo esc_html( number_format( (float) $rating, 1 ) ); ?></div>
                                    <div>
                                        <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                            <span style="font-size:1.5rem;color:<?php echo $i <= $rating ? 'var(--color-accent)' : 'var(--color-gray-300)'; ?>">&#9733;</span>
                                        <?php endfor; ?>
                                        <div style="font-size:.875rem;color:var(--color-gray-500);margin-top:.25rem;"><?php esc_html_e( 'Nossa avaliação', 'zan-affiliate-pro' ); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="entry-content" itemprop="reviewBody">
                            <?php the_content(); ?>
                        </div>

                        <footer class="entry-footer">
                            <?php zap_entry_footer(); ?>
                        </footer>
                    </article>

                    <?php if ( comments_open() || get_comments_number() ) : ?>
                        <?php comments_template(); ?>
                    <?php endif; ?>
                <?php endwhile; ?>
            </div>

            <aside id="secondary" class="widget-area" role="complementary">
                <?php dynamic_sidebar( 'sidebar-1' ); ?>
            </aside>
        </div>
    </div>
</main>
<?php get_footer(); ?>
