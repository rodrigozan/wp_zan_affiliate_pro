<?php
/**
 * Content part — post card for loops
 *
 * @package ZanAffiliatePro
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>
    <?php if ( has_post_thumbnail() ) : ?>
        <a href="<?php the_permalink(); ?>" class="card-img" tabindex="-1" aria-hidden="true">
            <?php the_post_thumbnail( 'zap-thumbnail', [ 'loading' => 'lazy' ] ); ?>
        </a>
    <?php endif; ?>
    <div class="card-body">
        <?php zap_post_meta( [ 'date', 'cats' ] ); ?>
        <?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
        <?php if ( '' !== get_the_excerpt() ) : ?>
            <p class="entry-excerpt"><?php the_excerpt(); ?></p>
        <?php endif; ?>
        <a href="<?php the_permalink(); ?>" class="btn btn-secondary btn-sm">
            <?php esc_html_e( 'Ler mais', 'zan-affiliate-pro' ); ?>
        </a>
    </div>
</article>
