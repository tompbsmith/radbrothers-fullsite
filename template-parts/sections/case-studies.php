<?php
/** @var array $s */
rb_section_open( $s );
?>
<div class="container">
	<?php rb_section_head( $s ); ?>
	<div class="cases">
		<?php foreach ( rb( $s, 'items', [] ) as $i => $c ) : ?>
			<article class="case reveal" data-reveal="<?php echo $i % 2 ? 'right' : 'left'; ?>">
				<div class="case__media"><?php echo rb_image( rb( $c, 'image' ), 'large' ); ?><?php if ( rb( $c, 'tag' ) ) : ?><span class="case__tag"><?php echo esc_html( $c['tag'] ); ?></span><?php endif; ?></div>
				<div class="case__body">
					<h3 class="case__title"><?php echo esc_html( rb( $c, 'title' ) ); ?></h3>
					<dl class="case__facts">
						<?php foreach ( [ 'challenge' => 'The challenge', 'solution' => 'What we did', 'outcome' => 'The result' ] as $k => $label ) : if ( ! rb( $c, $k ) ) { continue; } ?>
							<div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $c[ $k ] ); ?></dd></div>
						<?php endforeach; ?>
					</dl>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</div>
<?php rb_section_close(); ?>
