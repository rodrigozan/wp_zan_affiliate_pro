<?php
/**
 * Template: Finance Comparator
 *
 * Comparador de produtos financeiros: cartões de crédito, seguros,
 * empréstimos, corretoras. Hero sério + tabela de vantagens + cards de produto.
 * Estilo Nubank Blog, Mobills, Yubb.
 *
 * @package ZanAffiliatePro
 * @zap-template finance-comparator
 */

get_header();

$post_id   = get_the_ID();
$hero_sub  = get_post_meta( $post_id, '_zap_hero_subtitle', true );
$hero_badge= get_post_meta( $post_id, '_zap_hero_badge', true ) ?: __( '💳 Comparativo Atualizado', 'zan-affiliate-pro' );

// Produtos para comparar (IDs do CPT zap_product, pipe-separated)
$item_ids_raw = get_post_meta( $post_id, '_zap_listicle_items', true );
$item_ids     = $item_ids_raw
    ? array_filter( array_map( 'intval', explode( '|', $item_ids_raw ) ) )
    : [];

// Stats de autoridade: "Número|Label" por linha
$stats_raw = get_post_meta( $post_id, '_zap_stats', true );
$stats     = array_filter( array_map( 'trim', explode( "\n", $stats_raw ?: '' ) ) );
?>

<div class="zap-tpl zap-tpl--finance-comparator">

    <!-- ══ HERO FINANCEIRO ═════════════════════════════════════════════ -->
    <section class="zap-fin-hero">
        <div class="container">
            <div class="zap-fin-hero-content">
                <?php if ( $hero_badge ) : ?>
                    <div class="zap-fin-badge"><?php echo esc_html( $hero_badge ); ?></div>
                <?php endif; ?>
                <h1 class="zap-fin-hero-title"><?php the_title(); ?></h1>
                <?php if ( $hero_sub ) : ?>
                    <p class="zap-fin-hero-sub"><?php echo esc_html( $hero_sub ); ?></p>
                <?php endif; ?>
                <div class="zap-rb-meta">
                    <span><?php esc_html_e( 'Por', 'zan-affiliate-pro' ); ?> <?php the_author(); ?></span>
                    <time datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>">
                        <?php printf( esc_html__( 'Atualizado em %s', 'zan-affiliate-pro' ), get_the_modified_date() ); ?>
                    </time>
                </div>
            </div>
            <?php if ( $stats ) : ?>
                <div class="zap-fin-stats">
                    <?php foreach ( $stats as $stat ) :
                        $p = explode( '|', $stat );
                        ?>
                        <div class="zap-fin-stat">
                            <div class="zap-fin-stat-num"><?php echo esc_html( trim( $p[0] ?? '' ) ); ?></div>
                            <div class="zap-fin-stat-label"><?php echo esc_html( trim( $p[1] ?? '' ) ); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="container">
        <div class="zap-tpl-grid">
            <main id="primary" class="zap-tpl-main">

                <?php zap_breadcrumbs(); ?>
                <?php zap_affiliate_disclosure(); ?>

                <!-- ══ CARDS DE PRODUTO ═══════════════════════════════ -->
                <?php if ( $item_ids ) : ?>
                    <div class="zap-fin-cards">
                        <?php foreach ( $item_ids as $rank => $pid ) :
                            $name     = get_the_title( $pid );
                            $thumb    = get_the_post_thumbnail_url( $pid, 'zap-thumbnail' );
                            $nota     = get_post_meta( $pid, '_zap_product_avaliacao', true );
                            $preco    = get_post_meta( $pid, '_zap_product_preco', true );
                            $desc     = get_post_meta( $pid, '_zap_product_desconto', true );
                            $aff_link = get_post_meta( $pid, '_zap_product_aff_link', true );
                            $cta_lbl  = get_post_meta( $pid, '_zap_product_cta_label', true ) ?: __( 'Solicitar Agora', 'zan-affiliate-pro' );
                            $garantia = get_post_meta( $pid, '_zap_product_garantia', true );
                            $excerpt  = get_post_field( 'post_excerpt', $pid ) ?: wp_trim_words( get_post_field( 'post_content', $pid ), 20 );
                            ?>
                            <div class="zap-fin-card <?php echo $rank === 0 ? 'zap-fin-card--top' : ''; ?>">
                                <?php if ( $rank === 0 ) : ?>
                                    <div class="zap-fin-card-top-label"><?php esc_html_e( '⭐ Editor\'s Choice', 'zan-affiliate-pro' ); ?></div>
                                <?php endif; ?>
                                <div class="zap-fin-card-rank">#<?php echo esc_html( $rank + 1 ); ?></div>

                                <div class="zap-fin-card-inner">
                                    <?php if ( $thumb ) : ?>
                                        <div class="zap-fin-card-logo">
                                            <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" />
                                        </div>
                                    <?php endif; ?>

                                    <div class="zap-fin-card-info">
                                        <h3 class="zap-fin-card-name"><?php echo esc_html( $name ); ?></h3>
                                        <?php if ( $excerpt ) : ?>
                                            <p class="zap-fin-card-desc"><?php echo esc_html( $excerpt ); ?></p>
                                        <?php endif; ?>
                                        <div class="zap-fin-card-badges">
                                            <?php if ( $preco ) : ?><span class="zap-fin-badge-item">💰 <?php echo esc_html( $preco ); ?></span><?php endif; ?>
                                            <?php if ( $desc ) : ?><span class="zap-fin-badge-item zap-fin-badge-item--green">🎁 <?php echo esc_html( $desc ); ?></span><?php endif; ?>
                                            <?php if ( $garantia === '1' ) : ?><span class="zap-fin-badge-item">✔ <?php esc_html_e( 'Seguro', 'zan-affiliate-pro' ); ?></span><?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="zap-fin-card-side">
                                        <?php if ( $nota ) : ?>
                                            <div class="zap-fin-card-score"><?php echo esc_html( number_format( (float) $nota, 1 ) ); ?><span>/5</span></div>
                                        <?php endif; ?>
                                        <?php if ( $aff_link ) : ?>
                                            <a href="<?php echo esc_url( $aff_link ); ?>" class="btn <?php echo $rank === 0 ? 'btn-accent' : 'btn-primary'; ?>" rel="nofollow sponsored" target="_blank">
                                                <?php echo esc_html( $cta_lbl ); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- ══ CONTEÚDO EDITORIAL ════════════════════════════ -->
                <div class="entry-content">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php the_content(); ?>
                    <?php endwhile; ?>
                </div>

                <!-- ══ TABELA COMPARATIVA ════════════════════════════ -->
                <?php if ( count( $item_ids ) >= 2 ) : ?>
                    <div class="zap-fin-comparison">
                        <h2><?php esc_html_e( 'Comparativo Direto', 'zan-affiliate-pro' ); ?></h2>
                        <?php
                        echo do_shortcode( '[zap_compare ids="' . implode( ',', $item_ids ) . '" fields="preco,avaliacao,desconto,garantia,suporte,parcelamento"]' );
                        ?>
                    </div>
                <?php endif; ?>

                <footer class="entry-footer"><?php zap_entry_footer(); ?></footer>
                <?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
                <?php zap_related_posts( 3 ); ?>

            </main>

            <aside class="zap-tpl-sidebar zap-tpl-sidebar--sticky" role="complementary">
                <?php if ( $item_ids ) : ?>
                    <div class="widget">
                        <h3 class="widget-title"><?php esc_html_e( 'Top Escolhas', 'zan-affiliate-pro' ); ?></h3>
                        <?php foreach ( array_slice( $item_ids, 0, 5 ) as $i => $pid ) :
                            $aff_link = get_post_meta( $pid, '_zap_product_aff_link', true );
                            $cta_lbl  = get_post_meta( $pid, '_zap_product_cta_label', true ) ?: __( 'Ver', 'zan-affiliate-pro' );
                            ?>
                            <div class="zap-fin-sb-item">
                                <span class="zap-l10-sb-num"><?php echo esc_html( $i + 1 ); ?></span>
                                <span class="zap-l10-sb-name"><?php echo esc_html( get_the_title( $pid ) ); ?></span>
                                <?php if ( $aff_link ) : ?>
                                    <a href="<?php echo esc_url( $aff_link ); ?>" class="btn btn-primary btn-sm" rel="nofollow sponsored" target="_blank"><?php echo esc_html( $cta_lbl ); ?></a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php dynamic_sidebar( 'sidebar-1' ); ?>
            </aside>

        </div>
    </div>
</div>

<?php get_footer(); ?>
