<?php
/** @var array $s */
$img = rb( $s, 'image' );
rb_section_open( $s, $img ? 'features--with-image' : '', 'navy' );
?>
<div class="container">
	<div class="features">
		<div class="features__intro">
			<?php rb_section_head( $s ); ?>
			<?php if ( $img ) : ?>
				<div class="features__media reveal" data-reveal="left" style="--d:3"><?php echo rb_image( $img, 'large' ); ?></div>
			<?php endif; ?>
		</div>
		<ul class="features__list">
			<?php foreach ( rb( $s, 'items', [] ) as $i => $it ) : ?>
				<li class="feature reveal" data-reveal="right" style="--d:<?php echo (int) $i; ?>">
					<span class="feature__icon"><?php echo rb_icon( rb( $it, 'icon', 'check' ) ); ?></span>
					<div>
						<h3 class="feature__title"><?php echo esc_html( rb( $it, 'title' ) ); ?></h3>
						<p><?php echo esc_html( rb( $it, 'text' ) ); ?></p>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
<?php rb_section_close(); ?>
