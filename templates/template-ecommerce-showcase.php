<?php
/**
 * Template: E-commerce Showcase
 *
 * Vitrine de produtos com filtro por categoria, grid responsivo,
 * cards com preço/desconto/rating e botão comprar. Sidebar com filtros.
 * Estilo Amazon, Promobit, Zoom.
 *
 * @package ZanAffiliatePro
 * @zap-template ecommerce-showcase
 */

get_header();

$post_id     = get_the_ID();
$page_title  = get_the_title();
$page_intro  = get_the_excerpt();

// Category filter via URL ?cat=ID
$filter_cat   = isset( $_GET['cat'] ) ? (int) $_GET['cat'] : 0;

// All product categories
$product_cats = get_terms( [
    'taxonomy'   => 'zap_link_group',
    'hide_empty' => true,
] );

// Products (CPT zap_product) with category filter
$args = [
    'post_type'      => 'zap_product',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'paged'          => get_query_var( 'paged', 1 ),
];
if ( $filter_cat ) {
    $args['tax_query'] = [ [
        'taxonomy' => 'zap_link_group',
        'field'    => 'term_id',
        'terms'    => [ $filter_cat ],
    ] ];
}
$products_query = new WP_Query( $args );

// Sort options
$sort = sanitize_key( $_GET['sort'] ?? 'date' );
if ( $sort === 'rating' ) {
    $args['meta_key'] = '_zap_product_avaliacao';
    $args['orderby']  = 'meta_value_num';
    $args['order']    = 'DESC';
}
?>

<div class="zap-tpl zap-tpl--ecommerce-showcase">

    <!-- ══ PAGE HEADER ═══════════════════════════════════════════════════ -->
    <div class="zap-ec-header">
        <div class="container">
            <?php zap_breadcrumbs(); ?>
            <h1 class="zap-ec-title"><?php echo esc_html( $page_title ); ?></h1>
            <?php if ( $page_intro ) : ?>
                <p class="zap-ec-intro"><?php echo esc_html( $page_intro ); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <div class="zap-tpl-grid">

            <!-- ── Sidebar / Filtros ──────────────────────────────── -->
            <aside class="zap-tpl-sidebar" role="search" aria-label="<?php esc_attr_e( 'Filtros', 'zan-affiliate-pro' ); ?>">

                <div class="widget">
                    <h3 class="widget-title"><?php esc_html_e( 'Filtrar por Categoria', 'zan-affiliate-pro' ); ?></h3>
                    <ul style="list-style:none;padding:0">
                        <li>
                            <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"
                               class="<?php echo ! $filter_cat ? 'zap-ec-filter-active' : ''; ?>">
                                <?php esc_html_e( 'Todos', 'zan-affiliate-pro' ); ?>
                            </a>
                        </li>
                        <?php if ( ! is_wp_error( $product_cats ) ) : ?>
                            <?php foreach ( $product_cats as $pcat ) : ?>
                                <li>
                                    <a href="<?php echo esc_url( add_query_arg( 'cat', $pcat->term_id, get_permalink( $post_id ) ) ); ?>"
                                       class="<?php echo $filter_cat === $pcat->term_id ? 'zap-ec-filter-active' : ''; ?>">
                                        <?php echo esc_html( $pcat->name ); ?>
                                        <span class="zap-ec-filter-count">(<?php echo esc_html( $pcat->count ); ?>)</span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="widget">
                    <h3 class="widget-title"><?php esc_html_e( 'Ordenar por', 'zan-affiliate-pro' ); ?></h3>
                    <ul style="list-style:none;padding:0">
                        <?php
                        $sort_options = [
                            'date'   => __( 'Mais Recentes', 'zan-affiliate-pro' ),
                            'rating' => __( 'Melhor Avaliados', 'zan-affiliate-pro' ),
                        ];
                        foreach ( $sort_options as $val => $label ) : ?>
                            <li>
                                <a href="<?php echo esc_url( add_query_arg( 'sort', $val, get_permalink( $post_id ) ) ); ?>"
                                   class="<?php echo $sort === $val ? 'zap-ec-filter-active' : ''; ?>">
                                    <?php echo esc_html( $label ); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <?php dynamic_sidebar( 'sidebar-1' ); ?>

            </aside>

            <!-- ── Product Grid ───────────────────────────────────── -->
            <main id="primary" class="zap-tpl-main">

                <?php zap_affiliate_disclosure(); ?>

                <?php if ( $products_query->have_posts() ) : ?>

                    <div class="zap-ec-toolbar">
                        <span class="zap-ec-count">
                            <?php printf(
                                _n( '%d produto encontrado', '%d produtos encontrados', $products_query->found_posts, 'zan-affiliate-pro' ),
                                $products_query->found_posts
                            ); ?>
                        </span>
                    </div>

                    <div class="zap-ec-grid">
                        <?php while ( $products_query->have_posts() ) : $products_query->the_post();
                            $pid      = get_the_ID();
                            $thumb    = get_the_post_thumbnail_url( $pid, 'zap-thumbnail' );
                            $nota     = get_post_meta( $pid, '_zap_product_avaliacao', true );
                            $preco    = get_post_meta( $pid, '_zap_product_preco', true );
                            $desc     = get_post_meta( $pid, '_zap_product_desconto', true );
                            $aff_link = get_post_meta( $pid, '_zap_product_aff_link', true );
                            $cta_lbl  = get_post_meta( $pid, '_zap_product_cta_label', true ) ?: __( 'Ver Oferta', 'zan-affiliate-pro' );
                            $plataforma = get_post_meta( $pid, '_zap_product_plataforma', true );
                            ?>
                            <div class="zap-ec-card card">

                                <?php if ( $desc ) : ?>
                                    <div class="zap-ec-card-badge badge badge-danger"><?php echo esc_html( $desc ); ?></div>
                                <?php endif; ?>

                                <?php if ( $thumb ) : ?>
                                    <div class="zap-ec-card-img">
                                        <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                                    </div>
                                <?php endif; ?>

                                <div class="card-body">
                                    <h3 class="zap-ec-card-name">
                                        <a href="<?php echo esc_url( $aff_link ?: get_permalink() ); ?>" rel="<?php echo $aff_link ? 'nofollow sponsored' : ''; ?>" <?php echo $aff_link ? 'target="_blank"' : ''; ?>>
                                            <?php the_title(); ?>
                                        </a>
                                    </h3>

                                    <?php if ( $nota ) : ?>
                                        <div class="zap-ec-card-rating">
                                            <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                                <span class="zap-star <?php echo $i <= (float) $nota ? 'zap-star--full' : 'zap-star--empty'; ?>">&#9733;</span>
                                            <?php endfor; ?>
                                            <span>(<?php echo esc_html( number_format( (float) $nota, 1 ) ); ?>)</span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ( $plataforma ) : ?>
                                        <div style="font-size:.75rem;color:#6b7280;margin-bottom:.5rem"><?php echo esc_html( $plataforma ); ?></div>
                                    <?php endif; ?>

                                    <?php if ( $preco ) : ?>
                                        <div class="zap-ec-card-price"><?php echo esc_html( $preco ); ?></div>
                                    <?php endif; ?>

                                    <?php if ( $aff_link ) : ?>
                                        <a href="<?php echo esc_url( $aff_link ); ?>"
                                           class="btn btn-primary btn-block btn-sm"
                                           rel="nofollow sponsored"
                                           target="_blank">
                                            <?php echo esc_html( $cta_lbl ); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>

                    <!-- Pagination -->
                    <?php
                    echo paginate_links( [
                        'total'   => $products_query->max_num_pages,
                        'current' => get_query_var( 'paged', 1 ),
                        'class'   => 'pagination',
                    ] );
                    ?>

                <?php else : ?>
                    <div class="zap-ec-empty">
                        <p><?php esc_html_e( 'Nenhum produto encontrado. Tente outro filtro.', 'zan-affiliate-pro' ); ?></p>
                        <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="btn btn-secondary">
                            <?php esc_html_e( 'Ver Todos', 'zan-affiliate-pro' ); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Conteúdo editorial abaixo da grid -->
                <div class="entry-content zap-ec-editorial">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php the_content(); ?>
                    <?php endwhile; ?>
                </div>

            </main>

        </div>
    </div>
</div>

<?php get_footer(); ?>
