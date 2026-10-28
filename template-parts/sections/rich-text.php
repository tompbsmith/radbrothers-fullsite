<?php
/** @var array $s */
rb_section_open( $s );
?>
<div class="container container--narrow">
	<?php if ( rb( $s, 'heading' ) ) : ?><h2 class="section-title reveal"><?php echo esc_html( $s['heading'] ); ?></h2><?php endif; ?>
	<div class="prose reveal" style="--d:1"><?php echo wp_kses_post( rb( $s, 'content' ) ); ?></div>
</div>
<?php rb_section_close(); ?>
