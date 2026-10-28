<?php
/**
 * Illustrated, anonymised client-portal dashboard (HTML/CSS, no screenshot needed).
 * Mirrors the real portal's building blocks — summary cards, week calendar, request form —
 * with generic placeholder data so no real customer details are ever shown publicly.
 */
$days = [
	[ 'Mon', [] ],
	[ 'Tue', [ 'Open Day' ] ],
	[ 'Wed', [ 'HE Fair', 'School visit' ] ],
	[ 'Thu', [ 'Careers Fair' ] ],
	[ 'Fri', [ 'Open Evening' ] ],
];
?>
<div class="pm" aria-hidden="true">
	<div class="pm__chrome">
		<span class="pm__dots"><i></i><i></i><i></i></span>
		<span class="pm__url"><?php echo rb_icon( 'shield' ); ?>Client portal</span>
	</div>
	<div class="pm__screen">
		<div class="pm__top">
			<img class="pm__logo" src="<?php echo esc_url( RB_URI . '/assets/img/brand/longform.svg' ); ?>" alt="">
			<span class="pm__user"><span class="pm__avatar">U</span>Your University</span>
		</div>
		<p class="pm__title">Dashboard</p>

		<div class="pm__stats">
			<div class="pm__stat" style="--p:0"><strong>6</strong><span>Events this month</span></div>
			<div class="pm__stat" style="--p:1"><strong>14</strong><span>Items in storage</span></div>
			<div class="pm__stat" style="--p:2"><strong>2</strong><span>Requests in progress</span></div>
		</div>

		<div class="pm__grid">
			<div class="pm__card pm__cal" style="--p:3">
				<p class="pm__label"><?php echo rb_icon( 'calendar' ); ?>This week</p>
				<div class="pm__week">
					<?php foreach ( $days as $di => $d ) : ?>
						<div class="pm__day">
							<span><?php echo esc_html( $d[0] ); ?></span>
							<?php foreach ( $d[1] as $ei => $ev ) : ?>
								<em style="--e:<?php echo (int) ( $di + $ei ); ?>"><?php echo esc_html( $ev ); ?></em>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="pm__card pm__req" style="--p:4">
				<p class="pm__label"><?php echo rb_icon( 'box' ); ?>Request items</p>
				<div class="pm__field"><small>Event</small><span>Spring Open Day <?php echo rb_icon( 'chevron' ); ?></span></div>
				<ul class="pm__items">
					<li class="is-on"><i><?php echo rb_icon( 'check' ); ?></i>Pop-up stand</li>
					<li class="is-on"><i><?php echo rb_icon( 'check' ); ?></i>Banners ×2</li>
					<li class="is-on"><i><?php echo rb_icon( 'check' ); ?></i>Prospectus boxes</li>
					<li><i></i>Table &amp; cloth</li>
				</ul>
				<span class="pm__btn"><b class="pm__btn-a">Send request</b><b class="pm__btn-b"><?php echo rb_icon( 'check' ); ?>Booked</b></span>
			</div>
		</div>

		<div class="pm__card pm__log" style="--p:5">
			<p class="pm__label"><?php echo rb_icon( 'route' ); ?>Logbook</p>
			<div class="pm__row"><span>Careers Fair · Main Hall</span><b class="pm__tag pm__tag--go">Out for delivery</b></div>
			<div class="pm__row"><span>HE Fair · Conference Centre</span><b class="pm__tag">Confirmed</b></div>
		</div>
	</div>
	<div class="pm__toast"><?php echo rb_icon( 'check' ); ?><span><strong>Request confirmed</strong>Spring Open Day · stand + 2 banners</span></div>
</div>
