<?php
/** @var array $s */
$display = rb( $s, 'display', 'marquee' );
$logos   = rb( $s, 'logos', [] );
rb_section_open( $s, 'logos--' . $display );
?>
<div class="container">
	<?php rb_section_head( $s, 'section-head--center section-head--small' ); ?>
</div>
<?php if ( $logos ) : ?>
	<?php
	$items = '';
	foreach ( $logos as $l ) {
		$name = rb( $l, 'name' );
		$img  = rb( $l, 'image' );
		$url  = $img ? rb_image_url( $img, 'medium' ) : '';
		$items .= '<li class="logo-item">' . ( $url ? '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $name ) . '" loading="lazy">' : '<span>' . esc_html( $name ) . '</span>' ) . '</li>';
	}
	?>
	<?php if ( 'marquee' === $display ) : ?>
		<div class="marquee reveal" style="--d:1">
			<ul class="marquee__track"><?php echo $items; ?></ul>
			<ul class="marquee__track" aria-hidden="true"><?php echo $items; ?></ul>
		</div>
	<?php else : ?>
		<div class="container"><ul class="logo-grid reveal-group"><?php echo str_replace( '<li class="logo-item">', '<li class="logo-item reveal">', $items ); ?></ul></div>
	<?php endif; ?>
<?php endif; ?>
<?php rb_section_close(); ?>
