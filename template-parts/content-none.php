<?php
/**
 * Content-none template part
 *
 * @package ZanAffiliatePro
 */
?>
<section class="no-results not-found">
    <header class="page-header">
        <h1 class="page-title"><?php esc_html_e( 'Nada encontrado', 'zan-affiliate-pro' ); ?></h1>
    </header>
    <div class="page-content">
        <?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>
            <p><?php
                printf(
                    /* translators: %s: Link to new post editor */
                    wp_kses( __( 'Pronto para publicar seu primeiro post? <a href="%s">Começar aqui</a>.', 'zan-affiliate-pro' ), [ 'a' => [ 'href' => [] ] ] ),
                    esc_url( admin_url( 'post-new.php' ) )
                );
            ?></p>
        <?php elseif ( is_search() ) : ?>
            <p><?php esc_html_e( 'Desculpe, mas nada foi encontrado com esses termos. Tente novamente com palavras diferentes.', 'zan-affiliate-pro' ); ?></p>
            <?php get_search_form(); ?>
        <?php else : ?>
            <p><?php esc_html_e( 'Parece que nada foi encontrado aqui. Que tal uma busca?', 'zan-affiliate-pro' ); ?></p>
            <?php get_search_form(); ?>
        <?php endif; ?>
    </div>
</section>
