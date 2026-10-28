<?php
/**
 * Starter content: creates every page with its sections, imports placeholder images,
 * builds the menus and fills in Site settings.
 *
 * Run from wp-admin (notice after activating the theme) or with WP-CLI: `wp radbrothers seed`.
 * Safe to run again: pages are matched by slug and their sections are replaced.
 */

defined( 'ABSPATH' ) || exit;

/* ---------- Admin trigger ---------- */
add_action( 'after_switch_theme', function () {
	if ( ! get_option( 'rb_seeded' ) ) {
		update_option( 'rb_show_seed_notice', 1 );
	}
} );

add_action( 'admin_notices', function () {
	if ( ! get_option( 'rb_show_seed_notice' ) || ! current_user_can( 'manage_options' ) || ! function_exists( 'update_field' ) ) {
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=rb_seed' ), 'rb_seed' );
	$dismiss = wp_nonce_url( admin_url( 'admin-post.php?action=rb_seed&dismiss=1' ), 'rb_seed' );
	echo '<div class="notice notice-info"><p><strong>Rad Brothers theme:</strong> import the starter pages, copy, placeholder photos and menus?</p><p><a class="button button-primary" href="' . esc_url( $url ) . '">Import starter content</a> <a class="button" href="' . esc_url( $dismiss ) . '">No thanks</a></p></div>';
} );

add_action( 'admin_post_rb_seed', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'rb_seed' ) ) {
		wp_die( 'Not allowed.' );
	}
	delete_option( 'rb_show_seed_notice' );
	if ( empty( $_GET['dismiss'] ) ) {
		rb_seed_demo();
	}
	wp_safe_redirect( admin_url( 'edit.php?post_type=page' ) );
	exit;
} );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'radbrothers seed', function () {
		rb_seed_demo();
		WP_CLI::success( 'Rad Brothers starter content imported.' );
	} );
}

/* ---------- Helpers ---------- */

/** Import a placeholder image from the theme into the media library (once). */
function rb_seed_image( $name ) {
	$map = get_option( 'rb_seed_media', [] );
	if ( ! empty( $map[ $name ] ) && get_post( $map[ $name ] ) ) {
		return (int) $map[ $name ];
	}
	$src = RB_DIR . '/assets/img/placeholders/' . $name;
	if ( ! file_exists( $src ) ) {
		foreach ( [ '.jpg', '.svg' ] as $ext ) {
			if ( file_exists( $src . $ext ) ) {
				$src  .= $ext;
				$name .= '';
				break;
			}
		}
	}
	if ( ! file_exists( $src ) ) {
		return 0;
	}
	// Allow our own trusted placeholder SVGs just for this import.
	$allow_svg = function ( $m ) { $m['svg'] = 'image/svg+xml'; return $m; };
	add_filter( 'upload_mimes', $allow_svg );
	$upload = wp_upload_bits( basename( $src ), null, file_get_contents( $src ) ); // phpcs:ignore
	remove_filter( 'upload_mimes', $allow_svg );
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}
	$type = wp_check_filetype( $upload['file'] );
	$mime = $type['type'] ?: ( str_ends_with( $src, '.svg' ) ? 'image/svg+xml' : 'image/jpeg' );
	$id   = wp_insert_attachment( [
		'post_mime_type' => $mime,
		'post_title'     => 'Placeholder: ' . ucwords( str_replace( [ '-', '.jpg', '.svg' ], [ ' ', '', '' ], basename( $src ) ) ),
		'post_status'    => 'inherit',
	], $upload['file'] );
	if ( 'image/svg+xml' !== $mime ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	}
	update_post_meta( $id, '_wp_attachment_image_alt', 'Placeholder photo' );
	$map[ $name ] = $id;
	update_option( 'rb_seed_media', $map, false );
	return $id;
}

function rb_seed_page( $slug, $title, $sections, $parent = 0, $content = '' ) {
	$existing = get_page_by_path( $parent ? get_page_uri( $parent ) . '/' . $slug : $slug );
	$args     = [
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_parent'  => $parent,
		'post_content' => $content,
	];
	if ( $existing ) {
		$args['ID'] = $existing->ID;
		$id         = wp_update_post( $args );
	} else {
		$id = wp_insert_post( $args );
	}
	update_field( 'field_rb_sections', $sections, $id );
	return $id;
}

function rb_seed_btn( $label, $url, $style = 'primary' ) {
	return [ 'label' => $label, 'url' => $url, 'style' => $style ];
}

/* ---------- The content ---------- */
function rb_seed_demo() {
	if ( ! function_exists( 'update_field' ) ) {
		return;
	}
	$i   = 'rb_seed_image';
	$b   = 'rb_seed_btn';
	$url = function ( $path ) {
		return home_url( $path );
	};

	$quote_url = '/contact/';

	$client_logos = [];
	foreach ( [ 'University logo', 'PwC', 'Dedalus', 'College logo', 'University logo', 'Company logo', 'University logo', 'School logo' ] as $k => $n ) {
		$client_logos[] = [ 'image' => $i( 'client-' . ( $k + 1 ) . '.svg' ), 'name' => $n ];
	}

	$service_cards = [
		[
			'icon'       => 'truck',
			'title'      => 'Delivery Only',
			'text'       => 'We collect your stand and materials and get them to the venue on time, ready for your team to set up.',
			'points'     => "Collection from you or our warehouse\nTimed delivery to the venue\nCollection and return after the event",
			'link_label' => 'Compare services',
			'link_url'   => '/exhibition-services/#service-levels',
		],
		[
			'icon'       => 'box',
			'tag'        => 'Most popular',
			'title'      => 'Full Service',
			'text'       => 'The whole job, start to finish. We collect, store, deliver, build, break down and return, so you just turn up.',
			'points'     => "Collection, storage and transport\nStand built and ready before doors open\nBreakdown and return, or back into storage",
			'link_label' => 'Compare services',
			'link_url'   => '/exhibition-services/#service-levels',
		],
		[
			'icon'       => 'tools',
			'title'      => 'Build Partner',
			'text'       => 'Already working with a stand designer or builder? We handle all the logistics around them so the build runs on time.',
			'points'     => "Logistics planned around your builder\nDeliveries timed to the build slot\nOne contact for moving everything",
			'link_label' => 'Compare services',
			'link_url'   => '/exhibition-services/#service-levels',
		],
	];

	$audiences = [
		[
			'image'      => $i( 'education.jpg' ),
			'icon'       => 'education',
			'title'      => 'Universities, colleges & schools',
			'text'       => 'Open days, careers fairs and educational exhibitions are our specialism. We know the calendar, the campuses and the deadlines.',
			'link_label' => 'Education & careers fairs',
			'link_url'   => '/exhibition-services/education-careers-fairs/',
		],
		[
			'image'      => $i( 'tradeshow.jpg' ),
			'icon'       => 'briefcase',
			'title'      => 'Businesses & trade shows',
			'text'       => 'Exhibiting at a trade show? We get your stand to the hall, build it in the slot and take it home again, with one team responsible throughout.',
			'link_label' => 'Trade shows & corporate',
			'link_url'   => '/exhibition-services/trade-shows/',
		],
	];

	$uni_quotes = [
		[ 'quote' => 'Placeholder testimonial. Our stand turns up at every open day exactly when it should, and the team know our campus better than some of our staff do.', 'name' => 'Name Surname', 'role' => 'Careers Events Lead', 'organisation' => 'University client' ],
		[ 'quote' => 'Placeholder testimonial. When our fair moved halls two days before the event, one phone call sorted it. That is the difference with a family team.', 'name' => 'Name Surname', 'role' => 'Student Recruitment Manager', 'organisation' => 'University client' ],
		[ 'quote' => 'Placeholder testimonial. We run a dozen events a season and never have to think about where the stand is. It is just there, built and ready.', 'name' => 'Name Surname', 'role' => 'Outreach Officer', 'organisation' => 'College client' ],
	];
	$biz_quotes = [
		[ 'quote' => 'Placeholder testimonial. Three shows in five weeks and not a single missed slot. Rad Brothers made our busiest season the calmest.', 'name' => 'Name Surname', 'role' => 'Events Manager', 'organisation' => 'Commercial client' ],
		[ 'quote' => 'Placeholder testimonial. Having one team responsible from our warehouse to the show floor took a huge weight off our marketing team.', 'name' => 'Name Surname', 'role' => 'Head of Marketing', 'organisation' => 'Commercial client' ],
		[ 'quote' => 'Placeholder testimonial. They worked around our stand builder perfectly. Deliveries arrived to the minute and the breakdown was spotless.', 'name' => 'Name Surname', 'role' => 'Exhibitions Coordinator', 'organisation' => 'Commercial client' ],
	];

	$compact_steps = [
		[ 'icon' => 'truck', 'title' => 'Collect', 'text' => 'From your office or our warehouse.' ],
		[ 'icon' => 'warehouse', 'title' => 'Store', 'text' => 'Safe and dry between events.' ],
		[ 'icon' => 'route', 'title' => 'Transport', 'text' => 'Booked in and on time.' ],
		[ 'icon' => 'tools', 'title' => 'Build', 'text' => 'Ready before the doors open.' ],
		[ 'icon' => 'box', 'title' => 'Breakdown', 'text' => 'Packed with care at close.' ],
		[ 'icon' => 'react', 'title' => 'Return', 'text' => 'Home, or back into storage.' ],
	];

	/* ---------- Home ---------- */
	$home = rb_seed_page( 'home', 'Home', [
		[
			'acf_fc_layout' => 'hero',
			'size'          => 'home',
			'overlay'       => 55,
			'image'         => $i( 'hero-van.jpg' ),
			'eyebrow'       => 'Exhibition logistics · Family run',
			'heading'       => "We do exhibition\n*logistics.*",
			'text'          => 'Specialising in careers and educational events, and trusted by businesses at trade shows. One family team takes your stand from storage to the show floor and back again.',
			'buttons'       => [ $b( 'Get a quote', $quote_url ), $b( 'Our services', '/exhibition-services/', 'ghost' ) ],
			'highlights'    => [
				[ 'icon' => 'users', 'text' => 'One family team, start to finish' ],
				[ 'icon' => 'react', 'text' => 'We react when plans change' ],
				[ 'icon' => 'education', 'text' => 'Trusted by universities' ],
			],
		],
		[
			'acf_fc_layout' => 'logos',
			'background'    => 'white',
			'display'       => 'marquee',
			'heading'       => 'Trusted by universities, colleges and businesses',
			'logos'         => $client_logos,
		],
		[
			'acf_fc_layout' => 'cards',
			'background'    => 'white',
			'columns'       => '3',
			'eyebrow'       => 'What we do',
			'heading'       => 'Three ways to work with us',
			'intro'         => 'Whether you need a stand dropped at the door or the whole event handled, you deal with the same family team every time.',
			'cards'         => $service_cards,
		],
		[
			'acf_fc_layout' => 'split',
			'background'    => 'white',
			'eyebrow'       => 'Who we work with',
			'heading'       => "Built for open days.\nReady for the show floor.",
			'panels'        => $audiences,
		],
		[
			'acf_fc_layout' => 'process',
			'background'    => 'light',
			'style'         => 'compact',
			'eyebrow'       => 'How it works',
			'heading'       => 'From our warehouse to your stand, and back again',
			'intro'         => 'Choose as much or as little as you need. On Full Service, we look after every step.',
			'steps'         => $compact_steps,
			'buttons'       => [ $b( 'See the full process', '/how-it-works/', 'ghost' ) ],
		],
		[
			'acf_fc_layout' => 'features',
			'background'    => 'navy',
			'eyebrow'       => 'Why Rad Brothers',
			'heading'       => "Not just a customer number. A customer.",
			'intro'         => "We're a small, family-run business, so you get people who know your stand, your venue and your name. When something changes, we change with it.",
			'items'         => [
				[ 'icon' => 'users', 'title' => 'One team, start to finish', 'text' => 'The people who collect your stand are the people who build it. No hand-offs, no chasing three different companies.' ],
				[ 'icon' => 'react', 'title' => 'We react, then we deliver', 'text' => 'Venue moved? Extra event added? Being small means we can change plans quickly and still turn up on time.' ],
				[ 'icon' => 'heart', 'title' => 'Family-run and accountable', 'text' => 'Our name is on the van. If something needs sorting, you speak to someone who can sort it.' ],
				[ 'icon' => 'shield', 'title' => 'Careful with your kit', 'text' => 'Stands, banners and brochures are packed, stored and handled as if they were our own.' ],
			],
		],
		[
			'acf_fc_layout' => 'testimonials',
			'background'    => 'light',
			'display'       => 'single',
			'eyebrow'       => 'Kind words',
			'heading'       => 'What our clients say',
			'items'         => array_merge( array_slice( $uni_quotes, 0, 2 ), array_slice( $biz_quotes, 0, 1 ) ),
		],
		[
			'acf_fc_layout'  => 'text_image',
			'background'     => 'white',
			'image_position' => 'right',
			'eyebrow'        => 'Meet the family',
			'heading'        => 'The people behind the van',
			'content'        => '<p>Rad Brothers is a family business through and through. The same small team plans your job, loads the van, builds your stand and brings it home again.</p><p>It is why universities have trusted us for years, and why more businesses are trusting us with their trade shows too.</p>',
			'image'          => $i( 'team.jpg' ),
			'badge'          => 'Family-run, personally managed',
			'buttons'        => [ $b( 'Meet the team', '/about/#team', 'navy' ) ],
		],
		[
			'acf_fc_layout' => 'contact',
			'background'    => 'light',
			'anchor'        => 'contact',
			'form'          => 'short',
			'eyebrow'       => 'Get a quote',
			'heading'       => 'Tell us about your next event',
			'intro'         => 'Send us the basics and we will come back with a plan and a price. No obligation, no call centre.',
			'show_details'  => 1,
		],
	] );

	/* ---------- Exhibition Services ---------- */
	$services = rb_seed_page( 'exhibition-services', 'Exhibition Services', [
		[
			'acf_fc_layout' => 'hero',
			'size'          => 'page',
			'overlay'       => 55,
			'image'         => $i( 'hero-services.jpg' ),
			'eyebrow'       => 'Exhibition services',
			'heading'       => 'Your stand, *handled.*',
			'text'          => 'Three service levels, one family team. Pick the one that fits your event, or tell us what you need and we will recommend it.',
			'buttons'       => [ $b( 'Get a quote', $quote_url ), $b( 'Compare services', '#service-levels', 'ghost' ) ],
		],
		[
			'acf_fc_layout' => 'cards',
			'background'    => 'white',
			'anchor'        => 'service-levels',
			'columns'       => '3',
			'eyebrow'       => 'Service levels',
			'heading'       => 'Choose how much we take on',
			'intro'         => 'Every service includes a named contact, booked venue slots and careful handling of your kit.',
			'cards'         => array_map( function ( $c ) { unset( $c['link_label'], $c['link_url'] ); return $c; }, $service_cards ),
		],
		[
			'acf_fc_layout' => 'comparison',
			'background'    => 'light',
			'eyebrow'       => 'Side by side',
			'heading'       => "What's included",
			'columns'       => [
				[ 'title' => 'Delivery Only', 'subtitle' => 'You set up', 'highlight' => 0 ],
				[ 'title' => 'Full Service', 'subtitle' => 'We do it all', 'highlight' => 1 ],
				[ 'title' => 'Build Partner', 'subtitle' => 'With your builder', 'highlight' => 0 ],
			],
			'rows'          => [
				[ 'label' => 'Collection from you or our warehouse', 'col_1' => 'yes', 'col_2' => 'yes', 'col_3' => 'yes' ],
				[ 'label' => 'Secure storage between events', 'col_1' => 'Add-on', 'col_2' => 'yes', 'col_3' => 'Add-on' ],
				[ 'label' => 'Transport to the venue', 'col_1' => 'yes', 'col_2' => 'yes', 'col_3' => 'yes' ],
				[ 'label' => 'Venue booking-in and access slots', 'col_1' => 'yes', 'col_2' => 'yes', 'col_3' => 'yes' ],
				[ 'label' => 'Stand built on site', 'col_1' => 'no', 'col_2' => 'yes', 'col_3' => 'Your builder' ],
				[ 'label' => 'Breakdown after the event', 'col_1' => 'no', 'col_2' => 'yes', 'col_3' => 'With your builder' ],
				[ 'label' => 'Return home or back into storage', 'col_1' => 'yes', 'col_2' => 'yes', 'col_3' => 'yes' ],
				[ 'label' => 'Named contact from enquiry to return', 'col_1' => 'yes', 'col_2' => 'yes', 'col_3' => 'yes' ],
				[ 'label' => 'Job updates in the client portal', 'col_1' => 'yes', 'col_2' => 'yes', 'col_3' => 'yes' ],
			],
			'note'          => 'Not sure which you need? Tell us about the event and we will recommend the right fit.',
		],
		[
			'acf_fc_layout' => 'split',
			'background'    => 'white',
			'eyebrow'       => 'Who we work with',
			'heading'       => 'Specialists in education. Trusted by business.',
			'panels'        => $audiences,
		],
		[
			'acf_fc_layout'  => 'text_image',
			'background'     => 'light',
			'image_position' => 'left',
			'eyebrow'        => 'Client portal',
			'heading'        => 'Your stand, your stock and your next job, in one place',
			'content'        => '<p>Every client gets access to our online portal. Check what we are storing for you, see upcoming jobs and keep track of where your stand is, without picking up the phone.</p><ul><li>See everything we hold in storage</li><li>View upcoming and past jobs</li><li>Keep your whole team in the loop</li></ul>',
			'image'          => $i( 'portal.jpg' ),
			'badge'          => 'Log in any time',
			'buttons'        => [ $b( 'Client portal login', '#portal', 'navy' ) ],
		],
		[
			'acf_fc_layout' => 'cta',
			'style'         => 'orange',
			'heading'       => 'Not sure which service you need?',
			'text'          => 'Tell us the event, the venue and the dates. We will suggest the simplest way to get your stand there.',
			'buttons'       => [ $b( 'Talk to us', $quote_url ), $b( 'How it works', '/how-it-works/', 'ghost' ) ],
		],
	] );

	/* ---------- Education & Careers Fairs ---------- */
	rb_seed_page( 'education-careers-fairs', 'Education & Careers Fairs', [
		[
			'acf_fc_layout' => 'hero',
			'size'          => 'page',
			'overlay'       => 55,
			'image'         => $i( 'hero-education.jpg' ),
			'eyebrow'       => 'Education & careers fairs',
			'heading'       => "Open days and careers fairs,\n*without the stress.*",
			'text'          => 'Universities, colleges and schools make up most of our work. We know the calendar, the campuses and how much is riding on every event.',
			'buttons'       => [ $b( 'Plan your season', $quote_url ), $b( 'See how it works', '#season', 'ghost' ) ],
		],
		[
			'acf_fc_layout'  => 'text_image',
			'background'     => 'white',
			'image_position' => 'right',
			'eyebrow'        => 'Our specialism',
			'heading'        => 'We know the university calendar',
			'content'        => '<p>Autumn open days, spring careers fairs, summer outreach: the education year has its peaks, and so do we. We plan around them so your stand is where it needs to be, even when you have several events in the same week.</p><p>Many of our university clients keep their stands with us between events. We collect them, deliver them to campus or the venue, build them, then bring them back into storage ready for next time.</p>',
			'image'          => $i( 'education.jpg' ),
			'badge'          => 'Specialising in careers and educational events',
			'buttons'        => [ $b( 'Get a quote', $quote_url ) ],
		],
		[
			'acf_fc_layout' => 'cards',
			'background'    => 'light',
			'columns'       => '4',
			'eyebrow'       => 'What we handle',
			'heading'       => 'Every kind of education event',
			'cards'         => [
				[ 'icon' => 'education', 'title' => 'University open days', 'text' => 'Department stands, banners and displays built and ready before the first visitors arrive.' ],
				[ 'icon' => 'briefcase', 'title' => 'Careers & graduate fairs', 'text' => 'Full service for large fairs in halls and arenas, with breakdown and return straight after.' ],
				[ 'icon' => 'building', 'title' => 'Recruitment exhibitions', 'text' => 'Student recruitment events across the country, with your stand stored between dates.' ],
				[ 'icon' => 'truck', 'title' => 'School & college visits', 'text' => 'Smaller careers events in schools? We will drop off and collect, so your team can focus on the students.' ],
			],
		],
		[
			'acf_fc_layout' => 'process',
			'background'    => 'white',
			'anchor'        => 'season',
			'style'         => 'compact',
			'eyebrow'       => 'A typical season',
			'heading'       => 'How a university season runs with us',
			'steps'         => [
				[ 'icon' => 'warehouse', 'title' => 'Stored with us', 'text' => 'Your stand waits safely in our warehouse.' ],
				[ 'icon' => 'calendar', 'title' => 'Booked in', 'text' => 'Send us the dates; we handle venue slots.' ],
				[ 'icon' => 'truck', 'title' => 'Delivered', 'text' => 'To campus or the venue, on time.' ],
				[ 'icon' => 'tools', 'title' => 'Built', 'text' => 'Ready before visitors arrive.' ],
				[ 'icon' => 'box', 'title' => 'Packed down', 'text' => 'Carefully, as soon as you close.' ],
				[ 'icon' => 'react', 'title' => 'Back to storage', 'text' => 'Ready for the next event.' ],
			],
		],
		[
			'acf_fc_layout' => 'testimonials',
			'background'    => 'light',
			'display'       => 'single',
			'eyebrow'       => 'From our university clients',
			'heading'       => 'Trusted on campus',
			'items'         => $uni_quotes,
		],
		[
			'acf_fc_layout' => 'faq',
			'background'    => 'white',
			'eyebrow'       => 'FAQs',
			'heading'       => 'Questions from universities',
			'intro'         => 'Something we have not covered? Give us a call.',
			'items'         => [
				[ 'question' => 'How far ahead should we book?', 'answer' => 'As early as you know your dates, especially for the busy autumn and spring seasons. We can often help at short notice too, so it is always worth asking.' ],
				[ 'question' => 'Can you handle several events in the same week?', 'answer' => 'Yes. Many of our clients run multiple events across a season. We plan the whole run together so nothing clashes.' ],
				[ 'question' => 'Can you store our stand between events?', 'answer' => 'Yes. Most university clients keep their stands with us, which saves space on campus and means we can go straight from storage to the venue.' ],
				[ 'question' => 'What about campus access and parking?', 'answer' => 'We will work with your estates or events team on access, loading and parking, and we are used to tight campus timings.' ],
				[ 'question' => 'Do you do smaller school and college visits?', 'answer' => 'Yes. For smaller careers events we often just deliver and collect, so you can set up yourselves.' ],
				[ 'question' => 'Can you work with our existing stand supplier?', 'answer' => 'Absolutely. Our Build Partner service is designed for exactly that.' ],
			],
		],
		[
			'acf_fc_layout' => 'cta',
			'style'         => 'navy',
			'heading'       => "Planning this year's open days?",
			'text'          => 'Send us your dates and we will put together a plan for the whole season.',
			'buttons'       => [ $b( 'Plan your season', $quote_url ) ],
		],
	], $services );

	/* ---------- Trade Shows ---------- */
	rb_seed_page( 'trade-shows', 'Trade Shows & Corporate Exhibitions', [
		[
			'acf_fc_layout' => 'hero',
			'size'          => 'page',
			'overlay'       => 55,
			'image'         => $i( 'hero-tradeshow.jpg' ),
			'eyebrow'       => 'Trade shows & corporate exhibitions',
			'heading'       => "Your show, *on time.*\nEvery time.",
			'text'          => 'Trade show logistics from a family team that takes ownership. We get your stand to the hall, build it in your slot, and take it home again.',
			'buttons'       => [ $b( 'Tell us your show dates', '#quote' ), $b( 'Compare services', '/exhibition-services/#service-levels', 'ghost' ) ],
		],
		[
			'acf_fc_layout' => 'cards',
			'background'    => 'white',
			'columns'       => '4',
			'eyebrow'       => 'Sound familiar?',
			'heading'       => 'The usual trade show headaches, sorted',
			'intro'         => 'Exhibiting is stressful enough without worrying whether your stand will turn up.',
			'cards'         => [
				[ 'icon' => 'clock', 'title' => 'Tight build windows', 'text' => 'We book in, arrive on time and build within your slot, so you are ready when the hall opens.' ],
				[ 'icon' => 'react', 'title' => 'Plans that change', 'text' => 'Different hall, extra graphics, new date? We react quickly because we are small enough to.' ],
				[ 'icon' => 'calendar', 'title' => 'A busy show calendar', 'text' => 'Back-to-back shows? We store your stand between them and move it straight on to the next.' ],
				[ 'icon' => 'users', 'title' => 'Too many hand-offs', 'text' => 'One team owns the whole job, from your warehouse to the show floor. No one else to chase.' ],
			],
		],
		[
			'acf_fc_layout'  => 'text_image',
			'background'     => 'light',
			'image_position' => 'left',
			'eyebrow'        => 'Why a family firm',
			'heading'        => 'One team, responsible from start to finish',
			'content'        => '<p>Big logistics companies pass your job between departments. We do not. The people who plan your show are the people who load the van, build the stand and take it down again.</p><ul><li>A named contact who knows your stand and your show</li><li>Venue booking-in and access slots handled for you</li><li>Delivery, positioning, build and breakdown</li><li>Return to your premises, or storage until the next show</li></ul>',
			'image'          => $i( 'tradeshow.jpg' ),
			'badge'          => 'Your stand, ready before doors open',
			'buttons'        => [ $b( 'Get a quote', '#quote' ) ],
		],
		[
			'acf_fc_layout' => 'process',
			'background'    => 'white',
			'style'         => 'compact',
			'eyebrow'       => "What's covered",
			'heading'       => 'Everything between your warehouse and the show floor',
			'steps'         => [
				[ 'icon' => 'calendar', 'title' => 'Booking in', 'text' => 'Venue slots and access arranged.' ],
				[ 'icon' => 'truck', 'title' => 'Delivery', 'text' => 'Delivered and positioned on your space.' ],
				[ 'icon' => 'tools', 'title' => 'Build', 'text' => 'Built within your slot.' ],
				[ 'icon' => 'box', 'title' => 'Breakdown', 'text' => 'Taken down as soon as the show closes.' ],
				[ 'icon' => 'warehouse', 'title' => 'Return or store', 'text' => 'Back to you, or stored for the next show.' ],
			],
		],
		[
			'acf_fc_layout' => 'features',
			'background'    => 'navy',
			'eyebrow'       => 'Peace of mind',
			'heading'       => 'Why exhibitors choose Rad Brothers',
			'intro'         => 'The service you would expect from a large logistics company, with the care you only get from a family business.',
			'image'         => $i( 'build.jpg' ),
			'items'         => [
				[ 'icon' => 'phone', 'title' => 'A direct line to the team', 'text' => 'You speak to the people doing the job, not a call centre.' ],
				[ 'icon' => 'shield', 'title' => 'Handled with care', 'text' => 'Your stand, graphics and kit are packed, moved and stored carefully. Insurance details available on request.' ],
				[ 'icon' => 'monitor', 'title' => 'Client portal', 'text' => 'See what we are storing for you and what is coming up, any time.' ],
				[ 'icon' => 'route', 'title' => 'Venues across the UK', 'text' => 'From conference centres to exhibition halls, we will get you there.' ],
			],
		],
		[
			'acf_fc_layout' => 'logos',
			'background'    => 'white',
			'display'       => 'grid',
			'eyebrow'       => 'Clients',
			'heading'       => 'Exhibitors who trust us',
			'logos'         => [
				[ 'image' => $i( 'client-2.svg' ), 'name' => 'PwC' ],
				[ 'image' => $i( 'client-3.svg' ), 'name' => 'Dedalus' ],
				[ 'image' => $i( 'client-6.svg' ), 'name' => 'Company logo' ],
				[ 'image' => $i( 'client-6.svg' ), 'name' => 'Company logo' ],
			],
		],
		[
			'acf_fc_layout' => 'case_studies',
			'background'    => 'white',
			'eyebrow'       => 'Case study',
			'heading'       => 'Three shows, one stand, no stress',
			'items'         => [
				[
					'image'     => $i( 'case-study-2.jpg' ),
					'tag'       => 'Trade show',
					'title'     => 'Placeholder case study: a busy spring show season',
					'challenge' => 'A commercial client had three trade shows in five weeks, in three different cities, with one stand.',
					'solution'  => 'We stored the stand between shows, booked every venue slot and built and broke down each one, moving straight on to the next.',
					'outcome'   => 'Every slot hit, no damage, and a marketing team that could focus on the shows rather than the logistics.',
				],
			],
		],
		[
			'acf_fc_layout' => 'contact',
			'background'    => 'light',
			'anchor'        => 'quote',
			'form'          => 'full',
			'eyebrow'       => 'Get a quote',
			'heading'       => 'Already exhibiting this season?',
			'intro'         => 'Tell us your show dates and venues and we will come back with a plan and a price.',
			'show_details'  => 1,
		],
	], $services );

	/* ---------- How it works ---------- */
	$how = rb_seed_page( 'how-it-works', 'How It Works', [
		[
			'acf_fc_layout' => 'hero',
			'size'          => 'page',
			'overlay'       => 55,
			'image'         => $i( 'hero-process.jpg' ),
			'eyebrow'       => 'How it works',
			'heading'       => "Nine steps.\n*One team.*",
			'text'          => 'Here is everything that happens between your first call and your stand arriving home. On Full Service, all you do is turn up.',
			'buttons'       => [ $b( 'Start with a quote', $quote_url ) ],
		],
		[
			'acf_fc_layout' => 'process',
			'background'    => 'light',
			'style'         => 'detailed',
			'show_tiers'    => 1,
			'eyebrow'       => 'Step by step',
			'heading'       => 'What we do for you',
			'intro'         => 'Use the buttons to see which steps each service level includes.',
			'steps'         => [
				[ 'icon' => 'phone', 'title' => 'Enquiry', 'text' => 'You tell us about the event: what, where and when. We ask the right questions so nothing is missed.', 'client' => 'Call, email or fill in the quote form.', 'tiers' => [ 'delivery', 'full', 'partner' ] ],
				[ 'icon' => 'calendar', 'title' => 'Plan', 'text' => 'We agree the plan and price, book venue slots and access, and coordinate with your stand builder if you have one.', 'client' => 'Approve the plan.', 'tiers' => [ 'delivery', 'full', 'partner' ] ],
				[ 'icon' => 'truck', 'title' => 'Collect', 'text' => 'We collect your stand, graphics and materials from you, or pick them up from our warehouse.', 'client' => 'Nothing, unless it is coming from your office.', 'tiers' => [ 'delivery', 'full', 'partner' ] ],
				[ 'icon' => 'warehouse', 'title' => 'Store', 'text' => 'Between events, your kit stays safe and dry in our warehouse, ready to go.', 'client' => 'Nothing.', 'tiers' => [ 'full' ] ],
				[ 'icon' => 'route', 'title' => 'Transport', 'text' => 'We deliver to the venue in our own vans, timed to your slot, and position everything on your space.', 'client' => 'Nothing.', 'tiers' => [ 'delivery', 'full', 'partner' ] ],
				[ 'icon' => 'tools', 'title' => 'Build', 'text' => 'Our team builds your stand and checks it over, so it is ready before the doors open.', 'client' => 'Arrive and add the finishing touches.', 'tiers' => [ 'full', 'partner' ] ],
				[ 'icon' => 'users', 'title' => 'Event', 'text' => 'You run your event. We are a phone call away if anything is needed.', 'client' => 'Enjoy the event.', 'tiers' => [ 'delivery', 'full', 'partner' ] ],
				[ 'icon' => 'box', 'title' => 'Breakdown', 'text' => 'As soon as the event closes, we take the stand down and pack it carefully.', 'client' => 'Nothing.', 'tiers' => [ 'full', 'partner' ] ],
				[ 'icon' => 'react', 'title' => 'Return or store', 'text' => 'Everything goes back to you, or into our warehouse ready for next time.', 'client' => 'Nothing. See you at the next one.', 'tiers' => [ 'delivery', 'full', 'partner' ] ],
			],
		],
		[
			'acf_fc_layout' => 'cards',
			'background'    => 'white',
			'columns'       => '3',
			'eyebrow'       => 'Why it runs smoothly',
			'heading'       => 'Small team, big advantages',
			'cards'         => [
				[ 'icon' => 'phone', 'title' => 'One point of contact', 'text' => 'The same person looks after your job from enquiry to return, and knows your stand inside out.' ],
				[ 'icon' => 'pin', 'title' => 'Venue know-how', 'text' => 'We know the halls, the loading bays and the campus back routes, so there are fewer surprises on the day.' ],
				[ 'icon' => 'monitor', 'title' => 'Updates in the portal', 'text' => 'Check what we are storing for you and what is coming up, without having to chase.' ],
			],
		],
		[
			'acf_fc_layout' => 'faq',
			'background'    => 'light',
			'eyebrow'       => 'FAQs',
			'heading'       => 'Good questions',
			'items'         => [
				[ 'question' => 'What do you need from me to quote?', 'answer' => 'The event name, venue, dates, roughly what needs moving, and which service you are after. If you are not sure, we will help you work it out.' ],
				[ 'question' => 'Do you work evenings and weekends?', 'answer' => 'Yes. Events do not keep office hours and neither do build and breakdown slots.' ],
				[ 'question' => 'What happens if the plan changes?', 'answer' => 'Tell us as soon as you know. Reacting to changes is what we do best, and being a small team means we can adapt quickly.' ],
				[ 'question' => 'Is my stand insured while you have it?', 'answer' => 'Placeholder answer: confirm insurance cover details with Rad Brothers before publishing.' ],
			],
		],
		[
			'acf_fc_layout' => 'cta',
			'style'         => 'orange',
			'heading'       => 'Ready to hand it over?',
			'text'          => 'Tell us about your next event and we will take it from there.',
			'buttons'       => [ $b( 'Get a quote', $quote_url ) ],
		],
	] );

	/* ---------- Other services ---------- */
	$other = rb_seed_page( 'other-services', 'Other Services', [
		[
			'acf_fc_layout' => 'hero',
			'size'          => 'page',
			'overlay'       => 55,
			'image'         => $i( 'hero-other.jpg' ),
			'eyebrow'       => 'Other services',
			'heading'       => 'Beyond the *show floor.*',
			'text'          => 'Exhibitions are our focus, but our vans, our warehouse and our careful team are available for a few other jobs too.',
			'buttons'       => [ $b( 'Ask us about a job', '#contact' ) ],
		],
		[
			'acf_fc_layout' => 'cards',
			'background'    => 'white',
			'columns'       => '3',
			'eyebrow'       => 'Also available',
			'heading'       => 'Same van, same care',
			'cards'         => [
				[ 'image' => $i( 'courier.jpg' ), 'title' => 'Courier', 'text' => 'Time-sensitive deliveries handled by our own team, not passed on to a third party.', 'points' => "Same-day and next-day options\nEvent materials, samples and documents\nDirect from door to door" ],
				[ 'image' => $i( 'removals.jpg' ), 'title' => 'Removals', 'text' => 'Small office and specialist moves, planned and carried out with the same care we give to exhibition stands.', 'points' => "Small office moves\nSpecialist and fragile items\nPacking available" ],
				[ 'image' => $i( 'storage.jpg' ), 'title' => 'Storage', 'text' => 'Secure, dry storage for stands, display kit and event materials, with delivery whenever you need it.', 'points' => "Short and long term\nDelivery and collection on request\nSee your stock in the client portal" ],
			],
		],
		[
			'acf_fc_layout' => 'contact',
			'background'    => 'light',
			'anchor'        => 'contact',
			'form'          => 'short',
			'eyebrow'       => 'Get in touch',
			'heading'       => 'Got a job that needs moving?',
			'intro'         => 'Tell us what, where and when, and we will let you know if we can help.',
			'show_details'  => 1,
		],
	] );

	/* ---------- About ---------- */
	$faces   = [ [ 13.75, 40.0 ], [ 28.75, 36.15 ], [ 43.33, 33.08 ], [ 57.5, 33.85 ], [ 71.67, 36.92 ], [ 86.25, 40.38 ] ];
	$people  = [
		[ 'Alex', 'Operations', 'Keeps the vans moving and the warehouse in order. If your stand is in storage, Alex knows exactly where.', 'Can reverse a Luton into any loading bay in the country.' ],
		[ 'Sam', 'Lead driver', 'Has delivered to more campuses than most prospectus photographers. Calm under any build deadline.', 'Knows every motorway services between here and the NEC.' ],
		[ 'Jordan', 'Co-founder & Director', 'Started Rad Brothers with a van and a promise to always turn up on time. Still answers the phone.', 'Has never missed a build slot. Not once.' ],
		[ 'Chris', 'Co-founder & Director', 'Looks after clients and plans the busy seasons, so every event in the diary has a van and a team.', 'Came up with the arrow on the van.' ],
		[ 'Taylor', 'Client coordinator', 'Your first point of contact. Takes the details, books the slots and keeps everyone in the loop.', 'Has a spreadsheet for everything, including the office tea round.' ],
		[ 'Morgan', 'Stand build technician', 'Builds stands of every shape and size, and takes them down without a scratch.', 'Can put up a pop-up stand in under ten minutes.' ],
	];
	$members = [];
	foreach ( $people as $k => $p ) {
		$members[] = [ 'name' => $p[0], 'role' => $p[1], 'bio' => $p[2], 'fact' => $p[3], 'x' => $faces[ $k ][0], 'y' => $faces[ $k ][1] ];
	}

	$about = rb_seed_page( 'about', 'About Us', [
		[
			'acf_fc_layout' => 'hero',
			'size'          => 'page',
			'overlay'       => 55,
			'image'         => $i( 'hero-about.jpg' ),
			'eyebrow'       => 'About us',
			'heading'       => "A family business that\n*reacts and delivers.*",
			'text'          => 'RAD stands for React And Deliver. It is how we work, and it is why our clients keep coming back.',
			'buttons'       => [ $b( 'Meet the team', '#team' ) ],
		],
		[
			'acf_fc_layout'  => 'text_image',
			'background'     => 'white',
			'image_position' => 'right',
			'eyebrow'        => 'Our story',
			'heading'        => 'Where it all started',
			'content'        => '<p><em>Placeholder story: replace with the real Rad Brothers history.</em></p><p>Rad Brothers started as a family business with a simple idea: look after people\'s stands as if they were our own, and always turn up when we say we will.</p><p>Word spread through university events teams, and today we look after open days, careers fairs and educational exhibitions across the country, with more and more businesses trusting us with their trade shows too.</p><p>We have grown, but we have kept what matters. You still deal with the family, and you are still a customer, not a customer number.</p>',
			'image'          => $i( 'story.jpg' ),
			'badge'          => 'React And Deliver',
		],
		[
			'acf_fc_layout' => 'team',
			'background'    => 'light',
			'anchor'        => 'team',
			'eyebrow'       => 'Meet the team',
			'heading'       => 'Say hello to the family',
			'intro'         => 'Hover over a face (or tap on your phone) to meet the people who will be looking after your stand.',
			'image'         => $i( 'team.jpg' ),
			'members'       => $members,
		],
		[
			'acf_fc_layout' => 'features',
			'background'    => 'navy',
			'eyebrow'       => 'What we stand for',
			'heading'       => 'Our values',
			'intro'         => 'Four things we never compromise on, whether it is a school careers visit or a national trade show.',
			'items'         => [
				[ 'icon' => 'heart', 'title' => 'Personal service', 'text' => 'You know who is looking after your job, and they know you.' ],
				[ 'icon' => 'clock', 'title' => 'Reliability', 'text' => 'We turn up when we say we will. Every time.' ],
				[ 'icon' => 'react', 'title' => 'Reacting fast', 'text' => 'When plans change, we change with them, quickly and without fuss.' ],
				[ 'icon' => 'shield', 'title' => 'Taking ownership', 'text' => 'If something needs sorting, we sort it. No passing the buck.' ],
			],
		],
		[
			'acf_fc_layout'  => 'text_image',
			'background'     => 'white',
			'image_position' => 'left',
			'eyebrow'        => 'Behind the scenes',
			'heading'        => 'Our vans and our warehouse',
			'content'        => '<p>We run our own vans and our own warehouse, so your stand never leaves our hands. Look out for the orange arrow on the road.</p>',
			'image'          => $i( 'fleet.jpg' ),
			'buttons'        => [ $b( 'Follow us on LinkedIn', 'https://www.linkedin.com/', 'navy' ) ],
		],
		[
			'acf_fc_layout' => 'cta',
			'style'         => 'orange',
			'heading'       => 'Want to work with the family?',
			'text'          => 'Tell us about your next event and you will hear back from one of us, not a call centre.',
			'buttons'       => [ $b( 'Get in touch', $quote_url ) ],
		],
	] );

	/* ---------- Clients & testimonials ---------- */
	$clients = rb_seed_page( 'clients', 'Clients & Testimonials', [
		[
			'acf_fc_layout' => 'hero',
			'size'          => 'page',
			'overlay'       => 55,
			'image'         => $i( 'hero-clients.jpg' ),
			'eyebrow'       => 'Clients & testimonials',
			'heading'       => "Trusted on campus\n*and on the show floor.*",
			'text'          => 'From university open days to national trade shows, here are some of the organisations we work with and what they say about us.',
		],
		[
			'acf_fc_layout' => 'logos',
			'background'    => 'white',
			'display'       => 'grid',
			'eyebrow'       => 'Education',
			'heading'       => 'Universities, colleges and schools',
			'logos'         => [
				[ 'image' => $i( 'client-1.svg' ), 'name' => 'University logo' ],
				[ 'image' => $i( 'client-4.svg' ), 'name' => 'College logo' ],
				[ 'image' => $i( 'client-5.svg' ), 'name' => 'University logo' ],
				[ 'image' => $i( 'client-7.svg' ), 'name' => 'University logo' ],
				[ 'image' => $i( 'client-8.svg' ), 'name' => 'School logo' ],
				[ 'image' => $i( 'client-1.svg' ), 'name' => 'University logo' ],
			],
		],
		[
			'acf_fc_layout' => 'logos',
			'background'    => 'white',
			'display'       => 'grid',
			'eyebrow'       => 'Commercial',
			'heading'       => 'Businesses and exhibitors',
			'logos'         => [
				[ 'image' => $i( 'client-2.svg' ), 'name' => 'PwC' ],
				[ 'image' => $i( 'client-3.svg' ), 'name' => 'Dedalus' ],
				[ 'image' => $i( 'client-6.svg' ), 'name' => 'Company logo' ],
			],
		],
		[
			'acf_fc_layout' => 'testimonials',
			'background'    => 'light',
			'display'       => 'grid',
			'eyebrow'       => 'Testimonials',
			'heading'       => 'In their words',
			'items'         => array_merge( $uni_quotes, $biz_quotes ),
		],
		[
			'acf_fc_layout' => 'case_studies',
			'background'    => 'white',
			'eyebrow'       => 'Case studies',
			'heading'       => 'A closer look',
			'items'         => [
				[
					'image'     => $i( 'case-study-1.jpg' ),
					'tag'       => 'University',
					'title'     => 'Placeholder case study: a full careers fair season',
					'challenge' => 'A university careers service ran a busy autumn of fairs and open days, with several events in the same weeks.',
					'solution'  => 'We stored their stands, planned every delivery around campus access, built each one and returned it to storage after every event.',
					'outcome'   => 'Every event ready on time, and a careers team free to focus on students and employers.',
				],
				[
					'image'     => $i( 'case-study-2.jpg' ),
					'tag'       => 'Trade show',
					'title'     => 'Placeholder case study: three shows, one stand',
					'challenge' => 'A commercial client had three trade shows in five weeks, in three different cities, with one stand.',
					'solution'  => 'We stored the stand between shows, booked every venue slot, and built and broke down each one.',
					'outcome'   => 'Every slot hit, no damage, and a marketing team that could focus on the shows.',
				],
			],
		],
		[
			'acf_fc_layout' => 'cta',
			'style'         => 'navy',
			'heading'       => 'Join them at your next event',
			'buttons'       => [ $b( 'Get a quote', $quote_url ) ],
		],
	] );

	/* ---------- Contact ---------- */
	$contact = rb_seed_page( 'contact', 'Contact', [
		[
			'acf_fc_layout' => 'hero',
			'size'          => 'page',
			'overlay'       => 60,
			'image'         => $i( 'hero-contact.jpg' ),
			'eyebrow'       => 'Contact',
			'heading'       => "Let's get your next event\n*moving.*",
			'text'          => 'Tell us about the event and we will come back with a plan and a price. Prefer to talk? Give us a call.',
		],
		[
			'acf_fc_layout' => 'contact',
			'background'    => 'light',
			'anchor'        => 'quote',
			'form'          => 'full',
			'eyebrow'       => 'Get a quote',
			'heading'       => 'Tell us what you need',
			'intro'         => 'The more you can tell us, the more accurate the quote. Not sure on some details? Just send what you have.',
			'show_details'  => 1,
		],
		[
			'acf_fc_layout' => 'faq',
			'background'    => 'white',
			'eyebrow'       => 'Before you get in touch',
			'heading'       => 'Quick answers',
			'items'         => [
				[ 'question' => 'How quickly will I hear back?', 'answer' => 'We aim to reply within one working day, often sooner.' ],
				[ 'question' => 'What information helps with a quote?', 'answer' => 'The event name and venue, dates, roughly what needs moving, and whether you want us to build it.' ],
				[ 'question' => 'Can you help at short notice?', 'answer' => 'Often, yes. Give us a call and we will tell you straight away.' ],
			],
		],
	] );

	/* ---------- Legal (placeholders) ---------- */
	$legal_note = '<p><strong>Placeholder text.</strong> Replace with the real policy before launch.</p>';
	$privacy    = rb_seed_page( 'privacy-policy', 'Privacy Policy', [
		[ 'acf_fc_layout' => 'hero', 'size' => 'page', 'eyebrow' => 'Legal', 'heading' => 'Privacy policy' ],
		[ 'acf_fc_layout' => 'rich_text', 'background' => 'white', 'content' => $legal_note . '<h2>Who we are</h2><p>Rad Brothers Ltd is a family-run exhibition logistics company. This policy explains how we use the information you give us.</p><h2>What we collect</h2><p>When you send an enquiry we collect your name, organisation, email address, phone number and details about your event, so that we can reply and provide a quote.</p><h2>How we use it</h2><p>We only use your details to respond to your enquiry and to provide our services. We do not sell your data.</p><h2>Your rights</h2><p>You can ask to see, correct or delete the information we hold about you at any time by emailing contact@radbrothers.co.uk.</p>' ],
	] );
	$cookies    = rb_seed_page( 'cookie-policy', 'Cookie Policy', [
		[ 'acf_fc_layout' => 'hero', 'size' => 'page', 'eyebrow' => 'Legal', 'heading' => 'Cookie policy' ],
		[ 'acf_fc_layout' => 'rich_text', 'background' => 'white', 'content' => $legal_note . '<h2>Our use of cookies</h2><p>This website uses only the cookies needed for it to work. If we add analytics in future, we will ask for your consent first and update this page.</p>' ],
	] );
	$terms      = rb_seed_page( 'terms', 'Terms & Conditions', [
		[ 'acf_fc_layout' => 'hero', 'size' => 'page', 'eyebrow' => 'Legal', 'heading' => 'Terms & conditions' ],
		[ 'acf_fc_layout' => 'rich_text', 'background' => 'white', 'content' => $legal_note . '<h2>Our terms</h2><p>Add Rad Brothers Ltd\'s terms of business here.</p>' ],
	] );

	/* ---------- Reading settings ---------- */
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home );
	update_option( 'blogname', 'Rad Brothers' );
	update_option( 'blogdescription', 'Family-run exhibition logistics, specialising in careers and educational events.' );
	if ( ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
		flush_rewrite_rules();
	}

	/* ---------- Menus ---------- */
	$edu_id   = get_page_by_path( 'exhibition-services/education-careers-fairs' )->ID;
	$trade_id = get_page_by_path( 'exhibition-services/trade-shows' )->ID;

	$make_menu = function ( $name, $location, $items ) {
		$menu = wp_get_nav_menu_object( $name );
		if ( $menu ) {
			wp_delete_nav_menu( $menu->term_id );
		}
		$menu_id = wp_create_nav_menu( $name );
		foreach ( $items as $item ) {
			$parent = wp_update_nav_menu_item( $menu_id, 0, $item[0] );
			foreach ( $item[1] ?? [] as $child ) {
				$child['menu-item-parent-id'] = $parent;
				wp_update_nav_menu_item( $menu_id, 0, $child );
			}
		}
		$locations              = get_theme_mod( 'nav_menu_locations', [] );
		$locations[ $location ] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	};
	$page_item = function ( $id, $title = '' ) {
		return [ 'menu-item-object-id' => $id, 'menu-item-object' => 'page', 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish', 'menu-item-title' => $title ];
	};

	$make_menu( 'Main menu', 'primary', [
		[ $page_item( $services, 'Services' ), [ $page_item( $services, 'Exhibition services' ), $page_item( $edu_id, 'Education & careers fairs' ), $page_item( $trade_id, 'Trade shows & corporate' ), $page_item( $other, 'Other services' ) ] ],
		[ $page_item( $how, 'How it works' ) ],
		[ $page_item( $about, 'About' ) ],
		[ $page_item( $clients, 'Clients' ) ],
		[ $page_item( $contact, 'Contact' ) ],
	] );
	$make_menu( 'Footer menu', 'footer', [
		[ $page_item( $services, 'Exhibition services' ) ],
		[ $page_item( $edu_id, 'Education & careers fairs' ) ],
		[ $page_item( $trade_id, 'Trade shows & corporate' ) ],
		[ $page_item( $how, 'How it works' ) ],
		[ $page_item( $other, 'Other services' ) ],
		[ $page_item( $about, 'About us' ) ],
		[ $page_item( $clients, 'Clients' ) ],
	] );
	$make_menu( 'Legal menu', 'legal', [
		[ $page_item( $privacy, 'Privacy' ) ],
		[ $page_item( $cookies, 'Cookies' ) ],
		[ $page_item( $terms, 'Terms' ) ],
	] );

	/* ---------- Site settings ---------- */
	$opts = [
		'phone'         => '01234 567 890',
		'email'         => 'contact@radbrothers.co.uk',
		'address'       => "Unit 0, Placeholder Business Park\nTown, AB1 2CD",
		'hours'         => 'Mon–Fri, 8am–6pm (events any time)',
		'coverage'      => 'UK-wide',
		'response_time' => 'We reply within one working day.',
		'linkedin'      => 'https://www.linkedin.com/',
		'cta_label'     => 'Get a quote',
		'cta_url'       => $quote_url,
		'portal_label'  => 'Client portal',
		'portal_url'    => home_url( '/#portal' ),
		'footer_blurb'  => 'Family-run exhibition logistics, specialising in careers and educational events. We collect, store, deliver, build and return, so your stand is ready when you are.',
		'form_to'       => 'contact@radbrothers.co.uk',
		'form_from'     => 'Rad Brothers Website <contact@radbrothers.co.uk>',
	];
	foreach ( $opts as $k => $v ) {
		if ( '' === (string) get_field( $k, 'option' ) ) {
			update_field( 'field_rb_opt_' . $k, $v, 'option' );
		}
	}

	update_option( 'rb_seeded', time() );
}
