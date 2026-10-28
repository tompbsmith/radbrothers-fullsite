<?php
/** @var array $s */
$items = rb( $s, 'items', [] );
rb_section_open( $s );
?>
<div class="container">
	<div class="faq">
		<div class="faq__head"><?php rb_section_head( $s ); ?></div>
		<div class="faq__list">
			<?php foreach ( $items as $i => $q ) : ?>
				<details class="faq__item reveal" style="--d:<?php echo (int) $i; ?>">
					<summary><span><?php echo esc_html( rb( $q, 'question' ) ); ?></span><span class="faq__icon" aria-hidden="true"><?php echo rb_icon( 'plus' ); ?></span></summary>
					<div class="faq__answer"><?php echo wp_kses_post( rb( $q, 'answer' ) ); ?></div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<?php
// FAQ structured data.
if ( $items ) {
	$ld = [ '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => [] ];
	foreach ( $items as $q ) {
		$ld['mainEntity'][] = [ '@type' => 'Question', 'name' => wp_strip_all_tags( rb( $q, 'question' ) ), 'acceptedAnswer' => [ '@type' => 'Answer', 'text' => wp_strip_all_tags( rb( $q, 'answer' ) ) ] ];
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $ld ) . '</script>';
}
rb_section_close();
