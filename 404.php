<?php
get_header();
?>
<section class="section section--404 bg-navy" data-animate>
	<div class="container">
		<p class="eyebrow reveal"><?php echo rb_arrow_mark(); ?><span>Error 404</span></p>
		<h1 class="section-title reveal" style="--d:1">This page took a wrong turn.</h1>
		<p class="section-intro reveal" style="--d:2">The page you were after has moved or never existed. Let's get you back on the road.</p>
		<div class="buttons reveal" style="--d:3">
			<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span>Back to home</span><?php echo rb_icon( 'arrow', 'btn__icon' ); ?></a>
			<a class="btn btn--ghost" href="<?php echo esc_url( rb_opt( 'cta_url', home_url( '/contact/' ) ) ); ?>"><span>Contact us</span><?php echo rb_icon( 'arrow', 'btn__icon' ); ?></a>
		</div>
	</div>
</section>
<?php
get_footer();
