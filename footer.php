</main>
<?php
$phone    = rb_opt( 'phone' );
$email    = rb_opt( 'email' );
$address  = rb_opt( 'address' );
$linkedin = rb_opt( 'linkedin' );
?>
<footer class="site-footer">
	<div class="site-footer__arrow" aria-hidden="true"><?php echo rb_arrow_mark(); ?></div>
	<div class="container site-footer__grid">
		<div class="site-footer__brand">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> home"><?php echo rb_logo( 'longform', 'white' ); ?></a>
			<p><?php echo esc_html( rb_opt( 'footer_blurb', get_bloginfo( 'description' ) ) ); ?></p>
			<?php if ( $linkedin ) : ?>
				<a class="social" href="<?php echo esc_url( $linkedin ); ?>" rel="noopener" target="_blank"><?php echo rb_icon( 'linkedin' ); ?><span>Follow us on LinkedIn</span></a>
			<?php endif; ?>
		</div>
		<nav class="site-footer__col" aria-label="Footer">
			<h2 class="site-footer__title">Explore</h2>
			<?php wp_nav_menu( [ 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'footer-menu', 'depth' => 1, 'fallback_cb' => 'rb_menu_fallback' ] ); ?>
		</nav>
		<div class="site-footer__col">
			<h2 class="site-footer__title">Get in touch</h2>
			<ul class="contact-list">
				<?php if ( $phone ) : ?><li><?php echo rb_icon( 'phone' ); ?><a href="<?php echo esc_attr( rb_tel( $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></li><?php endif; ?>
				<?php if ( $email ) : ?><li><?php echo rb_icon( 'mail' ); ?><a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a></li><?php endif; ?>
				<?php if ( $address ) : ?><li><?php echo rb_icon( 'pin' ); ?><span><?php echo wp_kses( $address, [ 'br' => [] ] ); ?></span></li><?php endif; ?>
			</ul>
			<a class="btn btn--primary btn--sm" href="<?php echo esc_url( rb_opt( 'cta_url', home_url( '/contact/' ) ) ); ?>"><span><?php echo esc_html( rb_opt( 'cta_label', 'Get a quote' ) ); ?></span><?php echo rb_icon( 'arrow', 'btn__icon' ); ?></a>
		</div>
	</div>
	<div class="container site-footer__base">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Rad Brothers Ltd<?php
		$co = rb_opt( 'company_number' );
		$ra = rb_opt( 'registered_address' );
		if ( $co ) {
			echo ' · Registered in England &amp; Wales No. ' . esc_html( $co );
		}
		if ( $ra ) {
			echo ' · ' . esc_html( $ra );
		}
		?></p>
		<?php wp_nav_menu( [ 'theme_location' => 'legal', 'container' => false, 'menu_class' => 'legal-menu', 'depth' => 1, 'fallback_cb' => '__return_false' ] ); ?>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
