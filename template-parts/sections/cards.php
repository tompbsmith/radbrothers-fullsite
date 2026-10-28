<?php
/** @var array $s */
$cols  = rb( $s, 'columns', '3' );
$cards = rb( $s, 'cards', [] );
rb_section_open( $s );
?>
<div class="container">
	<?php rb_section_head( $s ); ?>
	<div class="cards cards--<?php echo esc_attr( $cols ); ?>">
		<?php foreach ( $cards as $i => $c ) :
			$link  = rb( $c, 'link_url' );
			$img   = rb( $c, 'image' );
			$icon  = rb( $c, 'icon' );
			$tag   = rb( $c, 'tag' );
			$pts   = array_filter( array_map( 'trim', explode( "\n", (string) rb( $c, 'points' ) ) ) );
			?>
			<article class="card<?php echo $img ? ' card--image' : ''; ?><?php echo $tag ? ' card--tagged' : ''; ?> reveal" style="--d:<?php echo (int) $i; ?>">
				<?php if ( $img ) : ?>
					<div class="card__media"><?php echo rb_image( $img, 'rb-card' ); ?></div>
				<?php endif; ?>
				<div class="card__body">
					<?php if ( $tag ) : ?><span class="card__tag"><?php echo esc_html( $tag ); ?></span><?php endif; ?>
					<?php if ( $icon && ! $img ) : ?><span class="card__icon"><?php echo rb_icon( $icon ); ?></span><?php endif; ?>
					<h3 class="card__title"><?php echo esc_html( rb( $c, 'title' ) ); ?></h3>
					<?php if ( rb( $c, 'text' ) ) : ?><p class="card__text"><?php echo esc_html( $c['text'] ); ?></p><?php endif; ?>
					<?php if ( $pts ) : ?>
						<ul class="ticks">
							<?php foreach ( $pts as $p ) : ?><li><?php echo rb_icon( 'check' ); ?><span><?php echo esc_html( $p ); ?></span></li><?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php if ( $link ) : ?>
						<a class="card__link" href="<?php echo esc_url( $link ); ?>"><span><?php echo esc_html( rb( $c, 'link_label', 'Find out more' ) ); ?></span><?php echo rb_icon( 'arrow' ); ?><span class="card__hit" aria-hidden="true"></span></a>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
	<?php if ( ! empty( $s['buttons'] ) ) : ?>
		<div class="section-foot reveal"><?php echo rb_buttons( $s['buttons'] ); ?></div>
	<?php endif; ?>
</div>
<?php rb_section_close(); ?>
