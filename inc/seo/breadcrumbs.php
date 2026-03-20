<?php
/**
 * Breadcrumbs
 *
 * @package ZanAffiliatePro
 */

defined( 'ABSPATH' ) || exit;

function zap_breadcrumbs(): void {
    if ( is_front_page() ) return;

    $crumbs = zap_get_breadcrumbs();
    if ( empty( $crumbs ) ) return;

    echo '<nav class="zap-breadcrumbs" aria-label="' . esc_attr__( 'Navegação', 'zan-affiliate-pro' ) . '">';
    echo '<ol itemscope itemtype="https://schema.org/BreadcrumbList">';
    foreach ( $crumbs as $i => $crumb ) {
        $pos = $i + 1;
        echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        if ( ! empty( $crumb['url'] ) && $i < count( $crumbs ) - 1 ) {
            echo '<a itemprop="item" href="' . esc_url( $crumb['url'] ) . '">';
            echo '<span itemprop="name">' . esc_html( $crumb['label'] ) . '</span></a>';
        } else {
            echo '<span itemprop="name">' . esc_html( $crumb['label'] ) . '</span>';
        }
        echo '<meta itemprop="position" content="' . esc_attr( $pos ) . '">';
        echo '</li>';
        if ( $i < count( $crumbs ) - 1 ) {
            echo '<li class="zap-breadcrumb-sep" aria-hidden="true">›</li>';
        }
    }
    echo '</ol></nav>';
}

function zap_get_breadcrumbs(): array {
    $crumbs = [];

    // Home
    $crumbs[] = [ 'label' => __( 'Início', 'zan-affiliate-pro' ), 'url' => home_url( '/' ) ];

    if ( is_single() ) {
        $cats = get_the_category();
        if ( $cats ) {
            $crumbs[] = [ 'label' => $cats[0]->name, 'url' => get_category_link( $cats[0]->term_id ) ];
        }
        $crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
    } elseif ( is_page() ) {
        global $post;
        $ancestors = array_reverse( get_post_ancestors( $post ) );
        foreach ( $ancestors as $ancestor ) {
            $crumbs[] = [ 'label' => get_the_title( $ancestor ), 'url' => get_permalink( $ancestor ) ];
        }
        $crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
    } elseif ( is_category() ) {
        $cat = get_queried_object();
        if ( $cat->parent ) {
            $crumbs[] = [ 'label' => get_cat_name( $cat->parent ), 'url' => get_category_link( $cat->parent ) ];
        }
        $crumbs[] = [ 'label' => $cat->name, 'url' => '' ];
    } elseif ( is_tag() ) {
        $crumbs[] = [ 'label' => single_tag_title( '', false ), 'url' => '' ];
    } elseif ( is_author() ) {
        $crumbs[] = [ 'label' => get_the_author(), 'url' => '' ];
    } elseif ( is_search() ) {
        $crumbs[] = [ 'label' => sprintf( __( 'Busca: %s', 'zan-affiliate-pro' ), get_search_query() ), 'url' => '' ];
    } elseif ( is_404() ) {
        $crumbs[] = [ 'label' => __( 'Página não encontrada', 'zan-affiliate-pro' ), 'url' => '' ];
    } elseif ( is_archive() ) {
        $crumbs[] = [ 'label' => get_the_archive_title(), 'url' => '' ];
    }

    return $crumbs;
}

function zap_get_breadcrumb_schema(): ?array {
    $crumbs = zap_get_breadcrumbs();
    if ( empty( $crumbs ) ) return null;
    return [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => array_map( function( $crumb, $i ) {
            return [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $crumb['label'],
                'item'     => $crumb['url'] ?: get_permalink(),
            ];
        }, $crumbs, array_keys( $crumbs ) ),
    ];
}
