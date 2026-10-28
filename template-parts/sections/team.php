<?php
/**
 * Meet the team: one big group photo with a hotspot on each face.
 * Hover / focus / tap a face (or use the arrows and name chips) to see that person's card.
 *
 * @var array $s
 */
$members = rb( $s, 'members', [] );
$img     = rb( $s, 'image' );
$uid     = 'team-' . (int) $section_index;
rb_section_open( $s );
?>
<div class="container">
	<?php rb_section_head( $s ); ?>

	<div class="team" data-team id="<?php echo esc_attr( $uid ); ?>">
		<div class="team__stage reveal" data-reveal="scale" style="--d:2" data-team-stage>
			<?php echo rb_image( $img, 'full', [ 'class' => 'team__photo', 'sizes' => '(min-width: 1280px) 1200px, 100vw' ] ); ?>
			<div class="team__spotlight" aria-hidden="true"></div>

			<?php foreach ( $members as $i => $m ) :
				$x = (float) rb( $m, 'x', 50 );
				$y = (float) rb( $m, 'y', 40 );
				?>
				<button type="button" class="spot" data-spot="<?php echo (int) $i; ?>" style="--x:<?php echo esc_attr( $x ); ?>%;--y:<?php echo esc_attr( $y ); ?>%;--i:<?php echo (int) $i; ?>" aria-controls="<?php echo esc_attr( $uid . '-card-' . $i ); ?>" aria-expanded="false">
					<span class="spot__ring" aria-hidden="true"></span>
					<span class="spot__dot" aria-hidden="true"></span>
					<span class="screen-reader-text"><?php echo esc_html( rb( $m, 'name' ) . ', ' . rb( $m, 'role' ) ); ?></span>
				</button>
				<div class="team-card" id="<?php echo esc_attr( $uid . '-card-' . $i ); ?>" data-card="<?php echo (int) $i; ?>" data-x="<?php echo esc_attr( $x ); ?>" data-y="<?php echo esc_attr( $y ); ?>" style="--x:<?php echo esc_attr( $x ); ?>%;--y:<?php echo esc_attr( $y ); ?>%" role="dialog" aria-label="<?php echo esc_attr( rb( $m, 'name' ) ); ?>">
					<p class="team-card__role"><?php echo esc_html( rb( $m, 'role' ) ); ?></p>
					<h3 class="team-card__name"><?php echo esc_html( rb( $m, 'name' ) ); ?></h3>
					<?php if ( rb( $m, 'bio' ) ) : ?><p class="team-card__bio"><?php echo esc_html( $m['bio'] ); ?></p><?php endif; ?>
					<?php if ( rb( $m, 'fact' ) ) : ?><p class="team-card__fact"><?php echo rb_arrow_mark(); ?><span><?php echo esc_html( $m['fact'] ); ?></span></p><?php endif; ?>
				</div>
			<?php endforeach; ?>

			<p class="team__hint" aria-hidden="true"><span class="team__hint-desktop">Hover over a face to say hello</span><span class="team__hint-touch">Tap a face to say hello</span></p>
		</div>

		<?php if ( $members ) : ?>
			<div class="team__bar reveal" style="--d:3">
				<button type="button" class="round-btn round-btn--prev" data-team-prev aria-label="Previous person"><?php echo rb_icon( 'chevron' ); ?></button>
				<ul class="team__chips" data-team-chips>
					<?php foreach ( $members as $i => $m ) : ?>
						<li><button type="button" class="team-chip" data-chip="<?php echo (int) $i; ?>"><strong><?php echo esc_html( rb( $m, 'name' ) ); ?></strong><span><?php echo esc_html( rb( $m, 'role' ) ); ?></span></button></li>
					<?php endforeach; ?>
				</ul>
				<button type="button" class="round-btn" data-team-next aria-label="Next person"><?php echo rb_icon( 'chevron' ); ?></button>
			</div>
			<div class="team__panel" data-team-panel aria-live="polite"></div>
		<?php endif; ?>
	</div>
</div>
<?php rb_section_close(); ?>
