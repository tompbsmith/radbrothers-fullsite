<?php
/** @var array $s */
$style      = rb( $s, 'style', 'compact' );
$steps      = rb( $s, 'steps', [] );
$show_tiers = ! empty( $s['show_tiers'] );
$tier_names = [ 'delivery' => 'Delivery Only', 'full' => 'Full Service' ];
$extra_name = 'Optional extra';
rb_section_open( $s, 'process--' . $style, 'light' );
?>
<div class="container process-layout">
	<div class="process-layout__aside">
	<?php rb_section_head( $s, 'compact' === $style ? 'section-head--center' : '' ); ?>

	<?php if ( 'detailed' === $style && $show_tiers ) : ?>
		<div class="tier-filter reveal" role="group" aria-label="Show steps for a service level">
			<span class="tier-filter__label">Show me:</span>
			<button type="button" class="tier-btn is-active" data-tier="all" aria-pressed="true">Everything</button>
			<?php foreach ( $tier_names as $k => $n ) : ?>
				<button type="button" class="tier-btn" data-tier="<?php echo esc_attr( $k ); ?>" aria-pressed="false"><?php echo esc_html( $n ); ?></button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	</div>

	<ol class="steps steps--<?php echo esc_attr( $style ); ?>" data-steps>
		<?php foreach ( $steps as $i => $st ) :
			$tiers    = (array) rb( $st, 'tiers', [] );
			$is_extra = in_array( 'extra', $tiers, true );
			// An optional extra can be added to any service level, so it stays visible under every filter.
			$filter   = $is_extra ? array_keys( $tier_names ) : $tiers;
			?>
			<li class="step reveal<?php echo $is_extra ? ' step--extra' : ''; ?>" style="--d:<?php echo (int) $i; ?>" data-tiers="<?php echo esc_attr( implode( ' ', $filter ) ); ?>">
				<div class="step__marker">
					<span class="step__icon"><?php echo rb_icon( rb( $st, 'icon', 'check' ) ); ?></span>
					<span class="step__num"><?php echo esc_html( str_pad( $i + 1, 2, '0', STR_PAD_LEFT ) ); ?></span>
				</div>
				<div class="step__body">
					<h3 class="step__title"><?php echo esc_html( rb( $st, 'title' ) ); ?></h3>
					<?php if ( rb( $st, 'text' ) ) : ?><p class="step__text"><?php echo esc_html( $st['text'] ); ?></p><?php endif; ?>
					<?php if ( 'detailed' === $style && rb( $st, 'client' ) ) : ?>
						<p class="step__you"><span>You</span><?php echo esc_html( $st['client'] ); ?></p>
					<?php endif; ?>
					<?php if ( 'detailed' === $style && $show_tiers && $tiers ) : ?>
						<ul class="step__tiers">
							<?php if ( $is_extra ) : ?>
								<li class="tier tier--extra"><?php echo esc_html( $extra_name ); ?></li>
							<?php else : ?>
								<?php foreach ( $tiers as $t ) : if ( ! isset( $tier_names[ $t ] ) ) { continue; } ?><li class="tier tier--<?php echo esc_attr( $t ); ?>"><?php echo esc_html( $tier_names[ $t ] ); ?></li><?php endforeach; ?>
							<?php endif; ?>
						</ul>
					<?php endif; ?>
				</div>
				<?php if ( 'compact' === $style && $i < count( $steps ) - 1 ) : ?>
					<span class="step__arrow" aria-hidden="true"><?php echo rb_arrow_mark(); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>

	<?php if ( ! empty( $s['buttons'] ) ) : ?>
		<div class="section-foot section-foot--center reveal"><?php echo rb_buttons( $s['buttons'] ); ?></div>
	<?php endif; ?>
</div>
<?php rb_section_close(); ?>
