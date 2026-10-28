<?php
/** @var array $s */
$pos   = rb( $s, 'image_position', 'right' );
$badge = rb( $s, 'badge' );
rb_section_open( $s, 'text-image--' . $pos );
?>
<div class="container">
	<div class="text-image">
		<div class="text-image__text">
			<?php if ( rb( $s, 'eyebrow' ) ) : ?><p class="eyebrow reveal"><?php echo rb_arrow_mark(); ?><span><?php echo esc_html( $s['eyebrow'] ); ?></span></p><?php endif; ?>
			<?php if ( rb( $s, 'heading' ) ) : ?><h2 class="section-title reveal" style="--d:1"><?php echo nl2br( esc_html( $s['heading'] ) ); ?></h2><?php endif; ?>
			<div class="prose reveal" style="--d:2"><?php echo wp_kses_post( rb( $s, 'content' ) ); ?></div>
			<?php if ( ! empty( $s['buttons'] ) ) : ?><div class="reveal" style="--d:3"><?php echo rb_buttons( $s['buttons'] ); ?></div><?php endif; ?>
		</div>
		<div class="text-image__media reveal" data-reveal="<?php echo 'right' === $pos ? 'right' : 'left'; ?>" style="--d:1">
			<div class="text-image__frame"><?php echo rb_image( rb( $s, 'image' ), 'large' ); ?></div>
			<?php if ( $badge ) : ?><div class="text-image__badge"><?php echo rb_arrow_mark(); ?><span><?php echo esc_html( $badge ); ?></span></div><?php endif; ?>
		</div>
	</div>
</div>
<?php rb_section_close(); ?>
