<?php
/** @var array $s */
rb_section_open( $s );
?>
<div class="container">
	<?php rb_section_head( $s ); ?>
	<div class="split">
		<?php foreach ( rb( $s, 'panels', [] ) as $i => $p ) : $link = rb( $p, 'link_url' ); ?>
			<article class="split__panel reveal" data-reveal="<?php echo $i ? 'right' : 'left'; ?>" style="--d:<?php echo (int) $i; ?>">
				<div class="split__media"><?php echo rb_image( rb( $p, 'image' ), 'large' ); ?></div>
				<div class="split__body">
					<?php if ( rb( $p, 'icon' ) ) : ?><span class="split__icon"><?php echo rb_icon( $p['icon'] ); ?></span><?php endif; ?>
					<h3 class="split__title"><?php echo esc_html( rb( $p, 'title' ) ); ?></h3>
					<p><?php echo esc_html( rb( $p, 'text' ) ); ?></p>
					<?php if ( $link ) : ?>
						<a class="split__link" href="<?php echo esc_url( $link ); ?>"><span><?php echo esc_html( rb( $p, 'link_label', 'Find out more' ) ); ?></span><?php echo rb_icon( 'arrow' ); ?><span class="card__hit" aria-hidden="true"></span></a>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</div>
<?php rb_section_close(); ?>
