<?php
/** @var array $s */
$style = rb( $s, 'style', 'orange' );
$id    = rb( $s, 'anchor' );
?>
<section class="section section--cta cta--<?php echo esc_attr( $style ); ?>"<?php echo $id ? ' id="' . esc_attr( sanitize_title( $id ) ) . '"' : ''; ?> data-animate>
	<div class="container">
		<div class="cta">
			<div class="cta__mark" aria-hidden="true"><?php echo rb_arrow_mark(); ?></div>
			<div class="cta__text">
				<h2 class="cta__title reveal"><?php echo nl2br( esc_html( rb( $s, 'heading' ) ) ); ?></h2>
				<?php if ( rb( $s, 'text' ) ) : ?><p class="reveal" style="--d:1"><?php echo esc_html( $s['text'] ); ?></p><?php endif; ?>
			</div>
			<div class="reveal" style="--d:2"><?php echo rb_buttons( rb( $s, 'buttons', [] ), 'buttons--cta' ); ?></div>
		</div>
	</div>
</section>
