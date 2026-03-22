<?php
/**
 * Template: Top 10 Listicle
 *
 * Formato "Os 10 Melhores X de [ano]" — ranking numerado com cards de produto,
 * nota, pros/cons e botão CTA por item. Barra de navegação lateral flutuante.
 * Estilo típico de sites como Wirecutter, TechRadar e Melhor Escolha.
 *
 * @package ZanAffiliatePro
 * @zap-template top10-listicle
 */

get_header();

$post_id = get_the_ID();
$year    = get_post_meta( $post_id, '_zap_listicle_year', true ) ?: date( 'Y' );
$intro   = get_post_meta( $post_id, '_zap_listicle_intro', true );

// Items: cada um é um product ID (CPT zap_product) ou bloco manual
// Formato no campo _zap_listicle_items: "ID|ID|ID|..." ou deixar vazio e usar o conteúdo
$item_ids_raw = get_post_meta( $post_id, '_zap_listicle_items', true );
$item_ids     = $item_ids_raw
    ? array_filter( array_map( 'intval', explode( '|', $item_ids_raw ) ) )
    : [];
?>

<div class="zap-tpl zap-tpl--top10-listicle">

    <!-- Breadcrumb -->
    <div class="zap-tpl-breadcrumb-bar">
        <div class="container"><?php zap_breadcrumbs(); ?></div>
    </div>

    <div class="container">
        <div class="zap-tpl-grid">

            <!-- ── Main ───────────────────────────────────────────────── -->
            <main id="primary" class="zap-tpl-main">

                <!-- Header -->
                <header class="zap-l10-header">
                    <?php
                    $cats = get_the_category();
                    if ( $cats ) : ?>
                        <a href="<?php echo esc_url( get_category_link( $cats[0] ) ); ?>" class="zap-rb-cat-badge">
                            <?php echo esc_html( $cats[0]->name ); ?>
                        </a>
                    <?php endif; ?>
                    <h1 class="zap-l10-title"><?php the_title(); ?></h1>
                    <div class="zap-rb-meta">
                        <div class="zap-rb-author">
                            <?php echo get_avatar( get_the_author_meta( 'ID' ), 28 ); ?>
                            <span><?php the_author(); ?></span>
                        </div>
                        <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php the_date(); ?></time>
                        <span class="zap-rb-reading"><?php echo esc_html( zap_get_reading_time() ); ?></span>
                    </div>
                </header>

                <!-- Disclosure -->
                <?php zap_affiliate_disclosure(); ?>

                <!-- Intro text -->
                <?php if ( $intro ) : ?>
                    <div class="zap-l10-intro"><?php echo wp_kses_post( wpautop( $intro ) ); ?></div>
                <?php endif; ?>

                <!-- ── Product Rankings (from CPT) ─────────────────── -->
                <?php if ( $item_ids ) : ?>
                    <nav class="zap-l10-jump-nav" aria-label="<?php esc_attr_e( 'Índice do ranking', 'zan-affiliate-pro' ); ?>">
                        <span><?php esc_html_e( 'Ir para:', 'zan-affiliate-pro' ); ?></span>
                        <ol>
                            <?php foreach ( $item_ids as $rank => $pid ) : ?>
                                <li>
                                    <a href="#produto-<?php echo esc_attr( $rank + 1 ); ?>">
                                        #<?php echo esc_html( $rank + 1 ); ?> <?php echo esc_html( get_the_title( $pid ) ); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </nav>

                    <div class="zap-l10-items">
                        <?php foreach ( $item_ids as $rank => $pid ) :
                            $num      = $rank + 1;
                            $name     = get_the_title( $pid );
                            $thumb    = get_the_post_thumbnail_url( $pid, 'zap-thumbnail' );
                            $preco    = get_post_meta( $pid, '_zap_product_preco', true );
                            $nota     = get_post_meta( $pid, '_zap_product_avaliacao', true );
                            $aff_link = get_post_meta( $pid, '_zap_product_aff_link', true );
                            $cta_lbl  = get_post_meta( $pid, '_zap_product_cta_label', true ) ?: __( 'Ver Oferta', 'zan-affiliate-pro' );
                            $garantia = get_post_meta( $pid, '_zap_product_garantia', true );
                            $suporte  = get_post_meta( $pid, '_zap_product_suporte', true );
                            $descricao= get_post_field( 'post_content', $pid );
                            ?>
                            <div id="produto-<?php echo esc_attr( $num ); ?>" class="zap-l10-item <?php echo $num === 1 ? 'zap-l10-item--top' : ''; ?>">
                                <!-- Rank badge -->
                                <div class="zap-l10-rank">
                                    <?php if ( $num === 1 ) : ?>
                                        <span class="zap-l10-rank-badge zap-l10-rank-badge--gold">#<?php echo esc_html( $num ); ?> Melhor Escolha</span>
                                    <?php elseif ( $num === 2 ) : ?>
                                        <span class="zap-l10-rank-badge zap-l10-rank-badge--silver">#<?php echo esc_html( $num ); ?> Custo-Benefício</span>
                                    <?php elseif ( $num === 3 ) : ?>
                                        <span class="zap-l10-rank-badge zap-l10-rank-badge--bronze">#<?php echo esc_html( $num ); ?> Mais Acessível</span>
                                    <?php else : ?>
                                        <span class="zap-l10-rank-num">#<?php echo esc_html( $num ); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="zap-l10-item-inner">
                                    <!-- Image -->
                                    <?php if ( $thumb ) : ?>
                                        <div class="zap-l10-item-img">
                                            <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" />
                                        </div>
                                    <?php endif; ?>

                                    <!-- Info -->
                                    <div class="zap-l10-item-info">
                                        <h2 class="zap-l10-item-name"><?php echo esc_html( $name ); ?></h2>

                                        <?php if ( $nota ) : ?>
                                            <div class="zap-l10-item-rating">
                                                <div class="zap-l10-item-score"><?php echo esc_html( number_format( (float) $nota, 1 ) ); ?></div>
                                                <div class="zap-l10-item-stars">
                                                    <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                                        <span class="zap-star <?php echo $i <= $nota ? 'zap-star--full' : 'zap-star--empty'; ?>">&#9733;</span>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Badges -->
                                        <div class="zap-l10-item-badges">
                                            <?php if ( $garantia === '1' ) : ?><span class="badge badge-success"><?php esc_html_e( 'Garantia', 'zan-affiliate-pro' ); ?></span><?php endif; ?>
                                            <?php if ( $suporte === '1' ) : ?><span class="badge badge-primary"><?php esc_html_e( 'Suporte', 'zan-affiliate-pro' ); ?></span><?php endif; ?>
                                        </div>

                                        <!-- Excerpt -->
                                        <?php if ( $descricao ) : ?>
                                            <div class="zap-l10-item-desc">
                                                <?php echo wp_kses_post( wpautop( wp_trim_words( $descricao, 30 ) ) ); ?>
                                            </div>
                                        <?php endif; ?>

                                        <!-- CTA -->
                                        <div class="zap-l10-item-cta">
                                            <?php if ( $preco ) : ?>
                                                <span class="zap-l10-price"><?php echo esc_html( $preco ); ?></span>
                                            <?php endif; ?>
                                            <?php if ( $aff_link ) : ?>
                                                <a href="<?php echo esc_url( $aff_link ); ?>"
                                                   class="btn <?php echo $num === 1 ? 'btn-accent' : 'btn-primary'; ?>"
                                                   rel="nofollow sponsored"
                                                   target="_blank">
                                                    <?php echo esc_html( $cta_lbl ); ?> →
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php endif; // item_ids ?>

                <!-- Conteúdo editorial (texto do editor) -->
                <div class="entry-content zap-l10-editorial">
                    <?php the_content(); ?>
                </div>

                <footer class="entry-footer"><?php zap_entry_footer(); ?></footer>

                <!-- Comments -->
                <?php if ( comments_open() || get_comments_number() ) : ?>
                    <?php comments_template(); ?>
                <?php endif; ?>

                <?php zap_related_posts( 4 ); ?>

            </main>

            <!-- ── Sidebar ─────────────────────────────────────────── -->
            <aside class="zap-tpl-sidebar zap-tpl-sidebar--sticky" role="complementary">

                <?php if ( $item_ids ) : ?>
                    <div class="widget zap-l10-sidebar-ranking">
                        <h3 class="widget-title"><?php esc_html_e( 'Ranking Rápido', 'zan-affiliate-pro' ); ?></h3>
                        <ol class="zap-l10-sidebar-list">
                            <?php foreach ( $item_ids as $rank => $pid ) :
                                $nota = get_post_meta( $pid, '_zap_product_avaliacao', true );
                                ?>
                                <li>
                                    <a href="#produto-<?php echo esc_attr( $rank + 1 ); ?>">
                                        <span class="zap-l10-sb-num"><?php echo esc_html( $rank + 1 ); ?></span>
                                        <span class="zap-l10-sb-name"><?php echo esc_html( get_the_title( $pid ) ); ?></span>
                                        <?php if ( $nota ) : ?>
                                            <span class="zap-l10-sb-score"><?php echo esc_html( number_format( (float) $nota, 1 ) ); ?> ★</span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                <?php endif; ?>

                <?php dynamic_sidebar( 'sidebar-1' ); ?>

            </aside>

        </div>
    </div>
</div>

<?php get_footer(); ?>
