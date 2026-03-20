<?php
/**
 * Related Posts
 *
 * Displays related posts based on categories and tags after single post content.
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

function zap_get_related_posts( int $post_id = 0, int $count = 4 ): array {
    $post_id = $post_id ?: get_the_ID();
    if ( ! $post_id ) return [];

    $cats    = wp_get_post_categories( $post_id, [ 'fields' => 'ids' ] );
    $tags    = wp_get_post_tags( $post_id, [ 'fields' => 'ids' ] );
    $args    = [
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $count,
        'post__not_in'   => [ $post_id ],
        'orderby'        => 'relevance',
        'no_found_rows'  => true,
    ];

    if ( $cats || $tags ) {
        $args['tax_query'] = [ 'relation' => 'OR' ];
        if ( $cats ) {
            $args['tax_query'][] = [
                'taxonomy' => 'category',
                'field'    => 'term_id',
                'terms'    => $cats,
            ];
        }
        if ( $tags ) {
            $args['tax_query'][] = [
                'taxonomy' => 'post_tag',
                'field'    => 'term_id',
                'terms'    => $tags,
            ];
        }
    }

    $query = new WP_Query( $args );
    return $query->posts ?: [];
}

function zap_related_posts( int $count = 4 ): void {
    $posts = zap_get_related_posts( 0, $count );
    if ( empty( $posts ) ) return;
    ?>
    <div class="zap-related-posts">
        <h3 class="zap-related-title"><?php esc_html_e( 'Você também pode gostar', 'zan-affiliate-pro' ); ?></h3>
        <div class="zap-related-grid">
            <?php foreach ( $posts as $post ) :
                setup_postdata( $post );
                $img = get_the_post_thumbnail_url( $post->ID, 'zap-thumbnail' );
                ?>
                <article class="zap-related-item card">
                    <?php if ( $img ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="zap-related-img-link" tabindex="-1">
                            <img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $post->post_title ); ?>" loading="lazy" />
                        </a>
                    <?php endif; ?>
                    <div class="card-body">
                        <h4 class="zap-related-post-title">
                            <a href="<?php echo esc_url( get_permalink( $post ) ); ?>">
                                <?php echo esc_html( $post->post_title ); ?>
                            </a>
                        </h4>
                        <div class="zap-related-meta">
                            <time datetime="<?php echo esc_attr( get_the_date( 'c', $post ) ); ?>">
                                <?php echo esc_html( get_the_date( '', $post ) ); ?>
                            </time>
                            <span>&bull;</span>
                            <span><?php echo esc_html( zap_get_reading_time( $post->ID ) ); ?></span>
                        </div>
                    </div>
                </article>
            <?php endforeach;
            wp_reset_postdata(); ?>
        </div>
    </div>
    <?php
}

// Auto-append after single post content
add_filter( 'the_content', function( string $content ): string {
    if ( ! is_single() || is_admin() || ! zap_option( 'auto_related', true ) ) return $content;
    ob_start();
    zap_related_posts();
    return $content . ob_get_clean();
} );
