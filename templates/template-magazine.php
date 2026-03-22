<?php
/**
 * Template: Magazine / News Portal
 *
 * Portal de conteúdo com grid de posts, destaque principal, categorias
 * em abas, sidebar de mais lidos. Estilo Catraca Livre, Infomoney, Olhar Digital.
 *
 * @package ZanAffiliatePro
 * @zap-template magazine
 */

get_header();

$post_id   = get_the_ID();
$cats_show = get_post_meta( $post_id, '_zap_mag_categories', true );
$cat_ids   = $cats_show
    ? array_filter( array_map( 'intval', explode( ',', $cats_show ) ) )
    : [];

// Fallback: todos os posts sem category filter
$featured_args = [
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 1,
    'meta_key'       => '_thumbnail_id',
];
$featured_query = new WP_Query( $featured_args );

$latest_args = [
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 8,
    'offset'         => 1,
];
$latest_query = new WP_Query( $latest_args );

$popular_args = [
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 5,
    'orderby'        => 'comment_count',
];
$popular_query = new WP_Query( $popular_args );
?>

<div class="zap-tpl zap-tpl--magazine">

    <!-- ══ BREAKING / HERO DESTAQUE ══════════════════════════════════════ -->
    <?php if ( $featured_query->have_posts() ) : ?>
        <section class="zap-mag-featured">
            <div class="container">
                <?php while ( $featured_query->have_posts() ) : $featured_query->the_post(); ?>
                    <article class="zap-mag-hero-post">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <a href="<?php the_permalink(); ?>" class="zap-mag-hero-img-link">
                                <?php the_post_thumbnail( 'zap-hero', [ 'loading' => 'eager' ] ); ?>
                            </a>
                        <?php endif; ?>
                        <div class="zap-mag-hero-content">
                            <?php $cats = get_the_category(); if ( $cats ) : ?>
                                <a href="<?php echo esc_url( get_category_link( $cats[0] ) ); ?>" class="zap-mag-cat"><?php echo esc_html( $cats[0]->name ); ?></a>
                            <?php endif; ?>
                            <?php the_title( '<h1 class="zap-mag-hero-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h1>' ); ?>
                            <div class="zap-mag-hero-meta">
                                <span><?php the_author(); ?></span>
                                <span>&bull;</span>
                                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php the_date(); ?></time>
                                <span>&bull;</span>
                                <span><?php echo esc_html( zap_get_reading_time() ); ?></span>
                            </div>
                        </div>
                    </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- ══ CONTEÚDO + SIDEBAR ════════════════════════════════════════════ -->
    <div class="container">
        <div class="zap-tpl-grid" style="margin-top:var(--spacing-8)">

            <main id="primary" class="zap-tpl-main">

                <!-- Conteúdo da página (editor) se existir -->
                <?php
                $page_content = get_the_content( null, false, $post_id );
                if ( $page_content ) : ?>
                    <div class="entry-content zap-mag-intro">
                        <?php echo apply_filters( 'the_content', $page_content ); // phpcs:ignore ?>
                    </div>
                <?php endif; ?>

                <!-- ── Últimas Notícias ─────────────────────────────── -->
                <section class="zap-mag-latest">
                    <h2 class="zap-mag-section-title"><?php esc_html_e( 'Últimas Publicações', 'zan-affiliate-pro' ); ?></h2>
                    <?php if ( $latest_query->have_posts() ) : ?>
                        <div class="zap-mag-grid">
                            <?php while ( $latest_query->have_posts() ) : $latest_query->the_post(); ?>
                                <article <?php post_class( 'zap-mag-card card' ); ?>>
                                    <?php if ( has_post_thumbnail() ) : ?>
                                        <a href="<?php the_permalink(); ?>" tabindex="-1">
                                            <?php the_post_thumbnail( 'zap-thumbnail', [ 'loading' => 'lazy' ] ); ?>
                                        </a>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <?php $cats = get_the_category(); if ( $cats ) : ?>
                                            <a href="<?php echo esc_url( get_category_link( $cats[0] ) ); ?>" class="zap-mag-cat"><?php echo esc_html( $cats[0]->name ); ?></a>
                                        <?php endif; ?>
                                        <?php the_title( '<h3 class="zap-mag-card-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h3>' ); ?>
                                        <div class="zap-mag-card-meta">
                                            <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php the_date(); ?></time>
                                            <span>&bull;</span>
                                            <span><?php echo esc_html( zap_get_reading_time() ); ?></span>
                                        </div>
                                    </div>
                                </article>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </div>
                        <div class="text-center" style="margin-top:var(--spacing-8)">
                            <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="btn btn-secondary">
                                <?php esc_html_e( 'Ver Todos os Artigos', 'zan-affiliate-pro' ); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- ── Por Categoria ────────────────────────────────── -->
                <?php if ( $cat_ids ) : ?>
                    <?php foreach ( $cat_ids as $cat_id ) :
                        $cat = get_category( $cat_id );
                        if ( ! $cat ) continue;
                        $cat_query = new WP_Query( [
                            'post_type'      => 'post',
                            'post_status'    => 'publish',
                            'posts_per_page' => 4,
                            'cat'            => $cat_id,
                        ] );
                        if ( ! $cat_query->have_posts() ) continue;
                        ?>
                        <section class="zap-mag-cat-section">
                            <div class="zap-mag-cat-header">
                                <h2 class="zap-mag-section-title"><?php echo esc_html( $cat->name ); ?></h2>
                                <a href="<?php echo esc_url( get_category_link( $cat_id ) ); ?>" class="zap-mag-see-all">
                                    <?php esc_html_e( 'Ver todos →', 'zan-affiliate-pro' ); ?>
                                </a>
                            </div>
                            <div class="zap-mag-grid">
                                <?php while ( $cat_query->have_posts() ) : $cat_query->the_post(); ?>
                                    <article <?php post_class( 'zap-mag-card card' ); ?>>
                                        <?php if ( has_post_thumbnail() ) : ?>
                                            <a href="<?php the_permalink(); ?>" tabindex="-1"><?php the_post_thumbnail( 'zap-thumbnail', [ 'loading' => 'lazy' ] ); ?></a>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <?php the_title( '<h3 class="zap-mag-card-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h3>' ); ?>
                                            <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php the_date(); ?></time>
                                        </div>
                                    </article>
                                <?php endwhile; wp_reset_postdata(); ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                <?php endif; ?>

            </main><!-- .zap-tpl-main -->

            <!-- ── Sidebar ──────────────────────────────────────────── -->
            <aside class="zap-tpl-sidebar" role="complementary">

                <!-- Mais Lidos -->
                <?php if ( $popular_query->have_posts() ) : ?>
                    <div class="widget zap-mag-popular">
                        <h3 class="widget-title"><?php esc_html_e( 'Mais Lidos', 'zan-affiliate-pro' ); ?></h3>
                        <ol class="zap-mag-popular-list">
                            <?php $i = 1; while ( $popular_query->have_posts() ) : $popular_query->the_post(); ?>
                                <li>
                                    <span class="zap-mag-popular-num"><?php echo esc_html( $i++ ); ?></span>
                                    <div class="zap-mag-popular-info">
                                        <a href="<?php the_permalink(); ?>" class="zap-mag-popular-title"><?php the_title(); ?></a>
                                        <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php the_date(); ?></time>
                                    </div>
                                </li>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </ol>
                    </div>
                <?php endif; ?>

                <?php dynamic_sidebar( 'sidebar-1' ); ?>

            </aside>

        </div>
    </div>

</div><!-- .zap-tpl--magazine -->

<?php get_footer(); ?>
