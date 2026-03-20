    </div><!-- #content -->

    <footer id="colophon" class="site-footer">
        <div class="container">
            <?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) || is_active_sidebar( 'footer-4' ) ) : ?>
                <div class="footer-widgets">
                    <?php for ( $i = 1; $i <= 4; $i++ ) :
                        if ( is_active_sidebar( "footer-$i" ) ) : ?>
                            <div class="footer-widget-area">
                                <?php dynamic_sidebar( "footer-$i" ); ?>
                            </div>
                        <?php endif;
                    endfor; ?>
                </div>
            <?php endif; ?>

            <div class="footer-bottom">
                <div class="footer-copyright">
                    <?php
                    $footer_text = get_theme_mod( 'zap_footer_text', '&copy; ' . date( 'Y' ) . ' ' . get_bloginfo( 'name' ) . '. Todos os direitos reservados.' );
                    echo wp_kses_post( $footer_text );
                    ?>
                </div>
                <?php
                wp_nav_menu( [
                    'theme_location' => 'footer',
                    'container'      => 'nav',
                    'container_class'=> 'footer-nav',
                    'depth'          => 1,
                    'fallback_cb'    => false,
                ] );
                ?>
            </div>
        </div>
    </footer>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
