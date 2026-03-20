<?php
/**
 * 404 template
 *
 * @package ZanAffiliatePro
 */

get_header();
?>
<main id="primary" class="site-main">
    <div class="container py-16">
        <div class="error-404 not-found text-center">
            <div style="font-size:6rem;margin-bottom:1rem;">404</div>
            <h1><?php esc_html_e( 'Página não encontrada', 'zan-affiliate-pro' ); ?></h1>
            <p><?php esc_html_e( 'A página que você está procurando não existe ou foi movida.', 'zan-affiliate-pro' ); ?></p>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary mt-8">
                <?php esc_html_e( 'Voltar para o início', 'zan-affiliate-pro' ); ?>
            </a>
            <div class="mt-8">
                <?php get_search_form(); ?>
            </div>
        </div>
    </div>
</main>
<?php
get_footer();
