<?php
/** @var array $s */
$display = rb( $s, 'display', 'single' );
$items   = rb( $s, 'items', [] );
rb_section_open( $s, 'testimonials--' . $display, 'light' );
?>
<div class="container">
	<?php rb_section_head( $s, 'single' === $display ? 'section-head--center' : '' ); ?>
	<?php if ( 'single' === $display ) : ?>
		<div class="quote-slider reveal" style="--d:2" data-slider>
			<div class="quote-slider__mark" aria-hidden="true"><?php echo rb_icon( 'quote' ); ?></div>
			<div class="quote-slider__track" aria-live="polite">
				<?php foreach ( $items as $i => $t ) : ?>
					<figure class="quote-slide<?php echo 0 === $i ? ' is-active' : ''; ?>" data-slide <?php echo $i ? 'hidden' : ''; ?>>
						<blockquote><p><?php echo esc_html( rb( $t, 'quote' ) ); ?></p></blockquote>
						<figcaption><strong><?php echo esc_html( rb( $t, 'name' ) ); ?></strong><span><?php echo esc_html( trim( rb( $t, 'role' ) . ( rb( $t, 'organisation' ) ? ', ' . $t['organisation'] : '' ), ', ' ) ); ?></span></figcaption>
					</figure>
				<?php endforeach; ?>
			</div>
			<?php if ( count( $items ) > 1 ) : ?>
				<div class="quote-slider__nav">
					<button type="button" class="round-btn round-btn--prev" data-prev aria-label="Previous testimonial"><?php echo rb_icon( 'chevron' ); ?></button>
					<div class="dots">
						<?php foreach ( $items as $i => $t ) : ?><button type="button" class="dot<?php echo 0 === $i ? ' is-active' : ''; ?>" data-dot="<?php echo (int) $i; ?>" aria-label="Show testimonial <?php echo (int) $i + 1; ?>"></button><?php endforeach; ?>
					</div>
					<button type="button" class="round-btn" data-next aria-label="Next testimonial"><?php echo rb_icon( 'chevron' ); ?></button>
				</div>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<div class="quote-grid">
			<?php foreach ( $items as $i => $t ) : ?>
				<figure class="quote-card reveal" style="--d:<?php echo (int) ( $i % 3 ); ?>">
					<span class="quote-card__mark" aria-hidden="true"><?php echo rb_icon( 'quote' ); ?></span>
					<blockquote><p><?php echo esc_html( rb( $t, 'quote' ) ); ?></p></blockquote>
					<figcaption><strong><?php echo esc_html( rb( $t, 'name' ) ); ?></strong><span><?php echo esc_html( trim( rb( $t, 'role' ) . ( rb( $t, 'organisation' ) ? ', ' . $t['organisation'] : '' ), ', ' ) ); ?></span></figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php if ( ! empty( $s['buttons'] ) ) : ?>
		<div class="section-foot section-foot--center reveal"><?php echo rb_buttons( $s['buttons'] ); ?></div>
	<?php endif; ?>
</div>
<?php rb_section_close(); ?>
