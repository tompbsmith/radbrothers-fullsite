<?php
/** @var array $s */
$shortcode    = trim( (string) rb( $s, 'shortcode' ) );
$show_details = ! isset( $s['show_details'] ) || $s['show_details'];
$phone        = rb_opt( 'phone' );
$email        = rb_opt( 'email' );
$address      = rb_opt( 'address' );
$hours        = rb_opt( 'hours' );
$coverage     = rb_opt( 'coverage' );
if ( ! rb( $s, 'anchor' ) ) {
	$s['anchor'] = 'contact';
}
rb_section_open( $s, '', 'light' );
?>
<div class="container">
	<div class="contact<?php echo $show_details ? '' : ' contact--solo'; ?>">
		<div class="contact__info">
			<?php rb_section_head( $s ); ?>
			<?php if ( $show_details ) : ?>
				<ul class="contact__details">
					<?php if ( $phone ) : ?><li class="reveal" style="--d:3"><span class="contact__icon"><?php echo rb_icon( 'phone' ); ?></span><div><small>Call us</small><a href="<?php echo esc_attr( rb_tel( $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></div></li><?php endif; ?>
					<?php if ( $email ) : ?><li class="reveal" style="--d:4"><span class="contact__icon"><?php echo rb_icon( 'mail' ); ?></span><div><small>Email</small><a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a></div></li><?php endif; ?>
					<?php if ( $address ) : ?><li class="reveal" style="--d:5"><span class="contact__icon"><?php echo rb_icon( 'pin' ); ?></span><div><small>Find us</small><span><?php echo wp_kses( $address, [ 'br' => [] ] ); ?></span></div></li><?php endif; ?>
					<?php if ( $hours ) : ?><li class="reveal" style="--d:6"><span class="contact__icon"><?php echo rb_icon( 'clock' ); ?></span><div><small>Office hours</small><span><?php echo esc_html( $hours ); ?></span></div></li><?php endif; ?>
					<?php if ( $coverage ) : ?><li class="reveal" style="--d:7"><span class="contact__icon"><?php echo rb_icon( 'route' ); ?></span><div><small>Where we work</small><span><?php echo esc_html( $coverage ); ?></span></div></li><?php endif; ?>
				</ul>
			<?php endif; ?>
		</div>
		<div class="contact__form reveal" data-reveal="right" style="--d:2" id="quote-form">
			<?php
			if ( $shortcode ) {
				echo do_shortcode( $shortcode );
			} else {
				echo rb_contact_form( rb( $s, 'form', 'full' ) ); // phpcs:ignore -- escaped inside.
			}
			?>
		</div>
	</div>
</div>
<?php rb_section_close(); ?>
