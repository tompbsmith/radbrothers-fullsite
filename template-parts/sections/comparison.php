<?php
/** @var array $s */
$cols = rb( $s, 'columns', [] );
$rows = rb( $s, 'rows', [] );

$cell = function ( $v ) {
	$v = trim( (string) $v );
	$l = strtolower( $v );
	if ( in_array( $l, [ 'yes', 'y', '1', 'true', '✓' ], true ) ) {
		return '<span class="cmp-yes">' . rb_icon( 'check' ) . '<span class="screen-reader-text">Included</span></span>';
	}
	if ( in_array( $l, [ 'no', 'n', '0', 'false', '', '-' ], true ) ) {
		return '<span class="cmp-no" aria-hidden="true">–</span><span class="screen-reader-text">Not included</span>';
	}
	return '<span class="cmp-text">' . esc_html( $v ) . '</span>';
};
rb_section_open( $s );
?>
<div class="container">
	<?php rb_section_head( $s, 'section-head--center' ); ?>
	<div class="cmp-wrap reveal" style="--d:2">
		<table class="cmp">
			<thead>
				<tr>
					<th scope="col"><span class="screen-reader-text">What's included</span></th>
					<?php foreach ( $cols as $c ) : ?>
						<th scope="col" class="<?php echo ! empty( $c['highlight'] ) ? 'is-highlight' : ''; ?>">
							<?php if ( ! empty( $c['highlight'] ) ) : ?><span class="cmp__flag">Most popular</span><?php endif; ?>
							<strong><?php echo esc_html( rb( $c, 'title' ) ); ?></strong>
							<span><?php echo esc_html( rb( $c, 'subtitle' ) ); ?></span>
						</th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $r ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( rb( $r, 'label' ) ); ?></th>
						<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
							<td class="<?php echo ! empty( $cols[ $i - 1 ]['highlight'] ) ? 'is-highlight' : ''; ?>" data-label="<?php echo esc_attr( rb( $cols[ $i - 1 ] ?? [], 'title' ) ); ?>"><?php echo $cell( $r[ 'col_' . $i ] ?? '' ); ?></td>
						<?php endfor; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php if ( rb( $s, 'note' ) ) : ?><p class="cmp-note reveal"><?php echo esc_html( $s['note'] ); ?></p><?php endif; ?>
</div>
<?php rb_section_close(); ?>
