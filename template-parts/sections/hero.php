<?php
/** @var array $s */
$size    = rb( $s, 'size', 'page' );
$overlay = (int) rb( $s, 'overlay', 60 );
$heading = esc_html( rb( $s, 'heading' ) );
$heading = preg_replace( '/\*(.+?)\*/', '<em>$1</em>', $heading );
$tag     = 0 === $section_index ? 'h1' : 'h2';
$id      = rb( $s, 'anchor' );
?>
<section class="hero hero--<?php echo esc_attr( $size ); ?>"<?php echo $id ? ' id="' . esc_attr( sanitize_title( $id ) ) . '"' : ''; ?> data-animate style="--overlay:<?php echo esc_attr( $overlay / 100 ); ?>">
	<div class="hero__media" data-parallax>
		<?php echo rb_image( rb( $s, 'image' ), 'rb-hero', [ 'loading' => 'eager', 'fetchpriority' => 'high', 'class' => 'hero__img', 'sizes' => '100vw' ] ); ?>
	</div>
	<div class="hero__shade" aria-hidden="true"></div>
	<div class="hero__mark" aria-hidden="true"><?php echo rb_arrow_mark(); ?></div>
	<div class="container hero__inner">
		<div class="hero__content">
			<?php if ( rb( $s, 'eyebrow' ) ) : ?>
				<p class="eyebrow eyebrow--light reveal"><?php echo rb_arrow_mark(); ?><span><?php echo esc_html( $s['eyebrow'] ); ?></span></p>
			<?php endif; ?>
			<?php if ( $heading ) : ?>
				<<?php echo $tag; ?> class="hero__title reveal" style="--d:1"><?php echo nl2br( $heading ); ?></<?php echo $tag; ?>>
			<?php endif; ?>
			<?php if ( rb( $s, 'text' ) ) : ?>
				<p class="hero__text reveal" style="--d:2"><?php echo esc_html( $s['text'] ); ?></p>
			<?php endif; ?>
			<div class="reveal" style="--d:3"><?php echo rb_buttons( rb( $s, 'buttons', [] ), 'buttons--hero' ); ?></div>
			<?php if ( 'home' === $size && ! empty( $s['highlights'] ) ) : ?>
				<ul class="hero__highlights reveal" style="--d:4">
					<?php foreach ( $s['highlights'] as $hl ) : ?>
						<li><?php echo rb_icon( rb( $hl, 'icon', 'check' ) ); ?><span><?php echo esc_html( rb( $hl, 'text' ) ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
	<?php if ( 'home' === $size ) : ?>
		<a class="hero__scroll" href="#after-hero" aria-label="Scroll down"><span></span></a>
	<?php endif; ?>
</section>
<?php if ( 'home' === $size ) : ?><span id="after-hero"></span><?php endif; ?>
