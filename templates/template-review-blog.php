<?php
/**
 * Template: Review Blog
 *
 * Blog de reviews de produtos com sidebar sticky, breadcrumbs,
 * meta de avaliação no topo, sumário automático e posts relacionados.
 * Inspirado no estilo de sites como Promobit, Zoom e TechTudo.
 *
 * @package ZanAffiliatePro
 * @zap-template review-blog
 */

get_header();

$post_id  = get_the_ID();
$rating   = get_post_meta( $post_id, '_zap_post_rating', true );
$reviews  = get_post_meta( $post_id, '_zap_post_reviews', true );
$aff_url  = get_post_meta( $post_id, '_zap_main_aff_url', true );
$aff_label= get_post_meta( $post_id, '_zap_main_aff_label', true ) ?: __( 'Ver Melhor Preço', 'zan-affiliate-pro' );
$pros     = get_post_meta( $post_id, '_zap_pros', true );
$cons     = get_post_meta( $post_id, '_zap_cons', true );
?>

<div class="zap-tpl zap-tpl--review-blog">

    <!-- ── Breadcrumb ──────────────────────────────────────────────────── -->
    <div class="zap-tpl-breadcrumb-bar">
        <div class="container"><?php zap_breadcrumbs(); ?></div>
    </div>

    <div class="container">
        <div class="zap-tpl-grid">

            <!-- ── Main Content ──────────────────────────────────────── -->
            <main id="primary" class="zap-tpl-main">

                <?php while ( have_posts() ) : the_post(); ?>

                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'zap-review-blog-article' ); ?>>

                        <!-- Header -->
                        <header class="zap-rb-header">
                            <?php
                            $cats = get_the_category();
                            if ( $cats ) : ?>
                                <div class="zap-rb-cats">
                                    <?php foreach ( $cats as $cat ) : ?>
                                        <a href="<?php echo esc_url( get_category_link( $cat ) ); ?>" class="zap-rb-cat-badge">
                                            <?php echo esc_html( $cat->name ); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <h1 class="zap-rb-title"><?php the_title(); ?></h1>

                            <div class="zap-rb-meta">
                                <div class="zap-rb-author">
                                    <?php echo get_avatar( get_the_author_meta( 'ID' ), 32 ); ?>
                                    <span><?php the_author(); ?></span>
                                </div>
                                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                                    <?php echo esc_html( get_the_date() ); ?>
                                </time>
                                <span class="zap-rb-reading"><?php echo esc_html( zap_get_reading_time() ); ?></span>
                            </div>
                        </header>

                        <!-- Hero image -->
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="zap-rb-hero-img">
                                <?php the_post_thumbnail( 'zap-wide', [ 'loading' => 'eager' ] ); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Quick verdict box -->
                        <?php if ( $rating || $pros || $cons ) : ?>
                            <div class="zap-rb-verdict">
                                <h2 class="zap-rb-verdict-title">
                                    <?php esc_html_e( 'Veredicto Rápido', 'zan-affiliate-pro' ); ?>
                                </h2>
                                <div class="zap-rb-verdict-inner">

                                    <?php if ( $rating ) : ?>
                                        <div class="zap-rb-score">
                                            <div class="zap-rb-score-num"><?php echo esc_html( number_format( (float) $rating, 1 ) ); ?></div>
                                            <div class="zap-rb-score-stars">
                                                <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                                    <span class="zap-star <?php echo $i <= $rating ? 'zap-star--full' : ( $i <= $rating + 0.5 ? 'zap-star--half' : 'zap-star--empty' ); ?>">&#9733;</span>
                                                <?php endfor; ?>
                                            </div>
                                            <?php if ( $reviews ) : ?>
                                                <div class="zap-rb-score-count"><?php printf( _n( '%s avaliação', '%s avaliações', (int) $reviews, 'zan-affiliate-pro' ), number_format_i18n( (int) $reviews ) ); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="zap-rb-pc">
                                        <?php if ( $pros ) : ?>
                                            <div class="zap-rb-pros">
                                                <h4><?php esc_html_e( '✔ Prós', 'zan-affiliate-pro' ); ?></h4>
                                                <ul>
                                                    <?php foreach ( array_filter( array_map( 'trim', explode( "\n", $pros ) ) ) as $pro ) : ?>
                                                        <li><?php echo esc_html( $pro ); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ( $cons ) : ?>
                                            <div class="zap-rb-cons">
                                                <h4><?php esc_html_e( '✘ Contras', 'zan-affiliate-pro' ); ?></h4>
                                                <ul>
                                                    <?php foreach ( array_filter( array_map( 'trim', explode( "\n", $cons ) ) ) as $con ) : ?>
                                                        <li><?php echo esc_html( $con ); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ( $aff_url ) : ?>
                                        <div class="zap-rb-verdict-cta">
                                            <a href="<?php echo esc_url( $aff_url ); ?>" class="btn btn-primary btn-lg" rel="nofollow sponsored" target="_blank">
                                                <?php echo esc_html( $aff_label ); ?> →
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Disclosure -->
                        <?php if ( ! get_post_meta( $post_id, '_zap_disable_disclosure', true ) ) : ?>
                            <?php zap_affiliate_disclosure(); ?>
                        <?php endif; ?>

                        <!-- Content -->
                        <div class="entry-content zap-rb-content">
                            <?php the_content(); ?>
                        </div>

                        <!-- Footer CTA -->
                        <?php if ( $aff_url ) : ?>
                            <div class="zap-rb-footer-cta">
                                <p><?php esc_html_e( 'Gostou? Aproveite a oferta:', 'zan-affiliate-pro' ); ?></p>
                                <a href="<?php echo esc_url( $aff_url ); ?>" class="btn btn-accent btn-lg" rel="nofollow sponsored" target="_blank">
                                    <?php echo esc_html( $aff_label ); ?> →
                                </a>
                            </div>
                        <?php endif; ?>

                        <footer class="entry-footer">
                            <?php zap_entry_footer(); ?>
                        </footer>

                    </article>

                    <!-- Comments -->
                    <?php if ( comments_open() || get_comments_number() ) : ?>
                        <?php comments_template(); ?>
                    <?php endif; ?>

                <?php endwhile; ?>

                <!-- Related Posts -->
                <?php zap_related_posts( 3 ); ?>

            </main><!-- .zap-tpl-main -->

            <!-- ── Sticky Sidebar ─────────────────────────────────────── -->
            <aside class="zap-tpl-sidebar zap-tpl-sidebar--sticky" role="complementary">

                <!-- CTA Box no topo da sidebar -->
                <?php if ( $aff_url ) : ?>
                    <div class="zap-sidebar-cta-box widget">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'zap-square', [ 'class' => 'zap-sidebar-cta-img' ] ); ?>
                        <?php endif; ?>
                        <?php if ( $rating ) : ?>
                            <div class="zap-sidebar-score">
                                <strong><?php echo esc_html( number_format( (float) $rating, 1 ) ); ?></strong>
                                <span>/5</span>
                            </div>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( $aff_url ); ?>" class="btn btn-primary btn-block" rel="nofollow sponsored" target="_blank">
                            <?php echo esc_html( $aff_label ); ?>
                        </a>
                        <p class="zap-sidebar-disclaimer"><?php esc_html_e( 'Link de afiliado', 'zan-affiliate-pro' ); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Widgets padrão -->
                <?php dynamic_sidebar( 'sidebar-1' ); ?>

            </aside>

        </div><!-- .zap-tpl-grid -->
    </div><!-- .container -->
</div><!-- .zap-tpl--review-blog -->

<?php get_footer(); ?>
