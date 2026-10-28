<?php
/**
 * Client portal showcase: copy + features on one side, a portal "screen" on the other.
 * Uses the illustrated mockup unless a screenshot is uploaded.
 *
 * @var array $s
 */
$shot     = rb( $s, 'screenshot' );
$features = rb( $s, 'features', [] );
$side     = rb( $s, 'mockup_position', 'right' );
rb_section_open( $s, 'portal--mock-' . $side, 'navy' );
?>
<div class="container">
	<div class="portal">
		<div class="portal__text">
			<?php rb_section_head( $s ); ?>
			<?php if ( $features ) : ?>
				<ul class="portal__features">
					<?php foreach ( $features as $i => $f ) : ?>
						<li class="reveal" style="--d:<?php echo (int) $i + 3; ?>">
							<span class="portal__icon"><?php echo rb_icon( rb( $f, 'icon', 'check' ) ); ?></span>
							<div><strong><?php echo esc_html( rb( $f, 'title' ) ); ?></strong><?php if ( rb( $f, 'text' ) ) : ?><span><?php echo esc_html( $f['text'] ); ?></span><?php endif; ?></div>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php if ( ! empty( $s['buttons'] ) ) : ?>
				<div class="reveal" style="--d:<?php echo count( $features ) + 3; ?>"><?php echo rb_buttons( $s['buttons'] ); ?></div>
			<?php endif; ?>
		</div>
		<div class="portal__media reveal" data-reveal="<?php echo 'right' === $side ? 'right' : 'left'; ?>" style="--d:2">
			<?php if ( $shot ) : ?>
				<div class="pm pm--shot">
					<div class="pm__chrome"><span class="pm__dots"><i></i><i></i><i></i></span><span class="pm__url"><?php echo rb_icon( 'shield' ); ?>Client portal</span></div>
					<?php echo rb_image( $shot, 'large', [ 'class' => 'pm__shot' ] ); ?>
				</div>
			<?php else : ?>
				<?php get_template_part( 'template-parts/portal-mockup' ); ?>
			<?php endif; ?>
			<?php if ( rb( $s, 'badge' ) ) : ?>
				<div class="portal__badge"><?php echo rb_arrow_mark(); ?><span><?php echo esc_html( $s['badge'] ); ?></span></div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php rb_section_close(); ?>
