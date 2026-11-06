<?php
/**
 * One-off content updates for sites already set up with the starter content.
 * Each step runs once, edits only the specific stored values it targets,
 * and leaves anything the client has changed alone.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'rb_run_migrations', 20 );

function rb_run_migrations() {
	$done = get_option( 'rb_migrations', [] );
	if ( ! is_array( $done ) ) {
		$done = [];
	}
	$steps = [
		'1.1.3-compare-anchor' => 'rb_migrate_compare_anchor',
		'1.1.4-design-build'   => 'rb_migrate_design_build',
		'1.1.5-service-steps'  => 'rb_migrate_service_steps',
		'1.1.6-print-wording'  => 'rb_migrate_print_wording',
		'1.1.7-two-levels'     => 'rb_migrate_two_levels',
		'1.1.8-dashed-extra'   => 'rb_migrate_dashed_extra',
		'1.1.11-delivery-store' => 'rb_migrate_delivery_store',
		'1.1.12-storage-card'   => 'rb_migrate_storage_card',
		'1.1.13-team-facts'     => 'rb_migrate_team_facts',
	];
	$changed = false;
	foreach ( $steps as $id => $fn ) {
		if ( in_array( $id, $done, true ) ) {
			continue;
		}
		$fn();
		$done[]  = $id;
		$changed = true;
	}
	if ( $changed ) {
		update_option( 'rb_migrations', $done, false );
	}
}

/**
 * Home "Three ways to work with us" cards linked to #service-levels, which is the
 * same three cards again. Point them at the comparison table below instead.
 */
function rb_migrate_compare_anchor() {
	global $wpdb;

	// 1. Give the comparison section on Exhibition Services the "compare" anchor (if it has none).
	$page = get_page_by_path( 'exhibition-services' );
	if ( $page ) {
		$layouts = get_post_meta( $page->ID, 'sections', true );
		if ( is_array( $layouts ) ) {
			foreach ( $layouts as $i => $layout ) {
				if ( 'comparison' !== $layout ) {
					continue;
				}
				$key = 'sections_' . $i . '_anchor';
				if ( '' === (string) get_post_meta( $page->ID, $key, true ) ) {
					update_post_meta( $page->ID, $key, 'compare' );
					update_post_meta( $page->ID, '_' . $key, 'field_rb_comparison_anchor' );
				}
				break;
			}
		}
	}

	// 2. Repoint card links that still use the old target.
	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT meta_id, post_id FROM {$wpdb->postmeta} WHERE meta_key LIKE %s AND meta_value = %s",
		'sections\_%\_cards\_%\_link\_url',
		'/exhibition-services/#service-levels'
	) );
	foreach ( $rows as $r ) {
		update_metadata_by_mid( 'post', (int) $r->meta_id, '/exhibition-services/#compare' );
		clean_post_cache( (int) $r->post_id );
	}
}

/**
 * Replace a section value only where it still matches the starter wording exactly.
 *
 * @param string $key_like SQL LIKE pattern for the meta key.
 */
function rb_migrate_replace_value( $key_like, $old, $new ) {
	global $wpdb;
	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT meta_id, post_id FROM {$wpdb->postmeta} WHERE meta_key LIKE %s AND meta_value = %s",
		$key_like,
		$old
	) );
	foreach ( $rows as $r ) {
		update_metadata_by_mid( 'post', (int) $r->meta_id, $new );
		clean_post_cache( (int) $r->post_id );
	}
}

/**
 * "Build Partner" (logistics around the client's own builder) becomes "Design & Build":
 * a new stand designed and made through a partner, or working alongside the client's builder.
 */
function rb_migrate_design_build() {
	$any = 'sections\_%';
	$map = [
		[ $any . '\_title', 'Build Partner', 'Design & Build' ],
		[ $any . '\_text', 'Already working with a stand designer or builder? We handle all the logistics around them so the build runs on time.', 'Need a new stand? We get it designed and made through a trusted stand-maker partner, then handle all the logistics around it.' ],
		[ $any . '\_points', "Logistics planned around your builder\nDeliveries timed to the build slot\nOne contact for moving everything", "Stand designed and made by our partner\nDelivered, built and broken down by us\nHappy to work with your own builder too" ],
		[ $any . '\_columns\_%\_subtitle', 'With your builder', 'New stand, made for you' ],
		[ $any . '\_rows\_%\_col\_3', 'Your builder', 'yes' ],
		[ $any . '\_rows\_%\_col\_3', 'With your builder', 'yes' ],
		[ $any . '\_answer', 'Absolutely. Our Build Partner service is designed for exactly that.', 'Yes. Our Design & Build service covers both: we can get a new stand designed and made for you, or work alongside your own builder.' ],
		[ $any . '\_quote', 'Placeholder testimonial. They worked around our stand builder perfectly. Deliveries arrived to the minute and the breakdown was spotless.', 'Placeholder testimonial. They sorted our new stand from design to build, then got it to the show and back without us lifting a finger.' ],
		[ $any . '\_text', 'We agree the plan and price, book venue slots and access, and coordinate with your stand builder if you have one. The job appears in your portal calendar.', 'We agree the plan and price, book venue slots and access, and coordinate with any stand maker involved. The job appears in your portal calendar.' ],
	];
	foreach ( $map as $m ) {
		rb_migrate_replace_value( $m[0], $m[1], $m[2] );
	}

	// Add a "New stand designed and made" row to the top of the comparison table,
	// if the table still has the Design & Build column and no such row yet.
	$page = get_page_by_path( 'exhibition-services' );
	if ( ! $page ) {
		return;
	}
	$layouts = get_post_meta( $page->ID, 'sections', true );
	if ( ! is_array( $layouts ) ) {
		return;
	}
	foreach ( $layouts as $i => $layout ) {
		if ( 'comparison' !== $layout ) {
			continue;
		}
		$base = 'sections_' . $i;
		if ( 'Design & Build' !== get_post_meta( $page->ID, $base . '_columns_2_title', true ) ) {
			break;
		}
		$count = (int) get_post_meta( $page->ID, $base . '_rows', true );
		$subs  = [ 'label', 'col_1', 'col_2', 'col_3' ];
		for ( $r = 0; $r < $count; $r++ ) {
			if ( in_array( get_post_meta( $page->ID, $base . '_rows_' . $r . '_label', true ), [ 'New stand designed and made', 'New stand designed and printed', 'New stand, banners or flags designed and printed' ], true ) ) {
				break 2;
			}
		}
		// Shift existing rows down one place.
		for ( $r = $count - 1; $r >= 0; $r-- ) {
			foreach ( $subs as $sub ) {
				$from = $base . '_rows_' . $r . '_' . $sub;
				$to   = $base . '_rows_' . ( $r + 1 ) . '_' . $sub;
				update_post_meta( $page->ID, $to, get_post_meta( $page->ID, $from, true ) );
				update_post_meta( $page->ID, '_' . $to, 'field_rb_comparison_r_' . $sub );
			}
		}
		$new = [ 'label' => 'New stand designed and made', 'col_1' => 'no', 'col_2' => 'no', 'col_3' => 'yes' ];
		foreach ( $new as $sub => $v ) {
			$k = $base . '_rows_0_' . $sub;
			update_post_meta( $page->ID, $k, $v );
			update_post_meta( $page->ID, '_' . $k, 'field_rb_comparison_r_' . $sub );
		}
		update_post_meta( $page->ID, $base . '_rows', $count + 1 );
		clean_post_cache( $page->ID );
		break;
	}
}

/**
 * Insert a row into a repeater inside a flexible content section, shifting later rows down.
 *
 * @param array $values  Sub field name => value.
 * @param string $key_prefix Field key prefix for the sub fields, e.g. field_rb_process_s_.
 */
function rb_migrate_insert_row( $post_id, $base, $index, $values, $key_prefix ) {
	$count = (int) get_post_meta( $post_id, $base, true );
	$subs  = array_keys( $values );
	for ( $r = $count - 1; $r >= $index; $r-- ) {
		foreach ( $subs as $sub ) {
			$to = $base . '_' . ( $r + 1 ) . '_' . $sub;
			update_post_meta( $post_id, $to, get_post_meta( $post_id, $base . '_' . $r . '_' . $sub, true ) );
			update_post_meta( $post_id, '_' . $to, $key_prefix . $sub );
		}
	}
	foreach ( $values as $sub => $v ) {
		$k = $base . '_' . $index . '_' . $sub;
		update_post_meta( $post_id, $k, $v );
		update_post_meta( $post_id, '_' . $k, $key_prefix . $sub );
	}
	update_post_meta( $post_id, $base, $count + 1 );
}

/**
 * Delivery Only has no storage. Full Service and Design & Build both include storage,
 * and Design & Build adds a design and print step before collection.
 */
function rb_migrate_service_steps() {
	global $wpdb;
	$any = 'sections\_%';
	$map = [
		[ $any . '\_points', "Collection from you or our warehouse\nTimed delivery to the venue\nCollection and return after the event", "Collection from your office, campus or school\nTimed delivery to the venue\nCollection and return after the event" ],
		[ $any . '\_points', "Stand designed and made by our partner\nDelivered, built and broken down by us\nHappy to work with your own builder too", "New stand designed and printed by our partner\nStored, delivered, built and broken down by us\nHappy to work with your own builder too" ],
		[ $any . '\_rows\_%\_col\_1', 'Add-on', 'no' ],
		[ $any . '\_rows\_%\_col\_3', 'Add-on', 'yes' ],
		[ $any . '\_rows\_%\_label', 'New stand designed and made', 'New stand designed and printed' ],
		[ $any . '\_steps\_%\_text', 'We collect your stand, graphics and materials from you, or pick them up from our warehouse.', 'We collect your stand, graphics and materials from you or the stand maker, or pick them up from our warehouse.' ],
	];
	foreach ( $map as $m ) {
		rb_migrate_replace_value( $m[0], $m[1], $m[2] );
	}

	// Process timelines that show service levels.
	$posts = $wpdb->get_col( "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key LIKE 'sections\\_%\\_steps\\_%\\_tiers'" );
	foreach ( $posts as $post_id ) {
		$post_id = (int) $post_id;
		$layouts = get_post_meta( $post_id, 'sections', true );
		if ( ! is_array( $layouts ) ) {
			continue;
		}
		foreach ( $layouts as $i => $layout ) {
			if ( 'process' !== $layout ) {
				continue;
			}
			$base  = 'sections_' . $i . '_steps';
			$count = (int) get_post_meta( $post_id, $base, true );
			$plan  = -1;
			$has_partner = false;
			$has_design  = false;
			for ( $r = 0; $r < $count; $r++ ) {
				$title = get_post_meta( $post_id, $base . '_' . $r . '_title', true );
				$tiers = (array) get_post_meta( $post_id, $base . '_' . $r . '_tiers', true );
				if ( in_array( 'partner', $tiers, true ) ) {
					$has_partner = true;
				}
				if ( 'Plan' === $title ) {
					$plan = $r;
				}
				if ( 'Design & print' === $title ) {
					$has_design = true;
				}
				// Storage is part of Design & Build too.
				if ( 'Store' === $title && [ 'full' ] === array_values( $tiers ) ) {
					update_post_meta( $post_id, $base . '_' . $r . '_tiers', [ 'full', 'partner' ] );
				}
			}
			if ( $has_partner && ! $has_design && $plan >= 0 ) {
				rb_migrate_insert_row( $post_id, $base, $plan + 1, [
					'icon'   => 'design',
					'title'  => 'Design & print',
					'text'   => 'Our stand-making partner designs and prints your new stand and graphics, and we make sure it is ready in good time.',
					'client' => 'Share your brief and sign off the design.',
					'tiers'  => [ 'partner' ],
				], 'field_rb_process_s_' );
			}
		}
		clean_post_cache( $post_id );
	}
}

/** Design & Build covers banners, flags and other printed display materials, not just stands. */
function rb_migrate_print_wording() {
	$any = 'sections\\_%';
	$map = [
		[ $any . '\_text', 'Need a new stand? We get it designed and made through a trusted stand-maker partner, then handle all the logistics around it.', 'Need a new stand, banners or flags? We get them designed and printed through a trusted design and print partner, then handle all the logistics around them.' ],
		[ $any . '\_points', "New stand designed and printed by our partner\nStored, delivered, built and broken down by us\nHappy to work with your own builder too", "Stands, banners and flags designed and printed by our partner\nStored, delivered, built and broken down by us\nHappy to work with your own builder too" ],
		[ $any . '\_quote', 'Placeholder testimonial. They sorted our new stand from design to build, then got it to the show and back without us lifting a finger.', 'Placeholder testimonial. They sorted our new stand and banners from design to print, then got everything to the show and back without us lifting a finger.' ],
		[ $any . '\_columns\_%\_subtitle', 'New stand, made for you', 'Designed and printed for you' ],
		[ $any . '\_rows\_%\_label', 'New stand designed and printed', 'New stand, banners or flags designed and printed' ],
		[ $any . '\_answer', 'Yes. Our Design & Build service covers both: we can get a new stand designed and made for you, or work alongside your own builder.', 'Yes. Our Design & Build service covers both: we can get your stand, banners or flags designed and printed, or work alongside your own builder.' ],
		[ $any . '\_steps\_%\_text', 'We agree the plan and price, book venue slots and access, and coordinate with any stand maker involved. The job appears in your portal calendar.', 'We agree the plan and price, book venue slots and access, and coordinate with any printers or stand suppliers involved. The job appears in your portal calendar.' ],
		[ $any . '\_steps\_%\_text', 'Our stand-making partner designs and prints your new stand and graphics, and we make sure it is ready in good time.', 'Our design and print partner creates your stand, banners, flags or other display materials, and we make sure everything is ready in good time.' ],
		[ $any . '\_steps\_%\_text', 'We collect your stand, graphics and materials from you or the stand maker, or pick them up from our warehouse.', 'We collect your stand, graphics and materials from you or our print partner, or pick them up from our warehouse.' ],
	];
	foreach ( $map as $m ) {
		rb_migrate_replace_value( $m[0], $m[1], $m[2] );
	}
}

/** Remove a row from a repeater inside a flexible content section, shifting later rows up. */
function rb_migrate_remove_row( $post_id, $base, $index, $subs ) {
	$count = (int) get_post_meta( $post_id, $base, true );
	for ( $r = $index; $r < $count - 1; $r++ ) {
		foreach ( $subs as $sub ) {
			update_post_meta( $post_id, $base . '_' . $r . '_' . $sub, get_post_meta( $post_id, $base . '_' . ( $r + 1 ) . '_' . $sub, true ) );
		}
	}
	foreach ( $subs as $sub ) {
		delete_post_meta( $post_id, $base . '_' . ( $count - 1 ) . '_' . $sub );
		delete_post_meta( $post_id, '_' . $base . '_' . ( $count - 1 ) . '_' . $sub );
	}
	update_post_meta( $post_id, $base, max( 0, $count - 1 ) );
}

/** IDs of posts that have a given flexible content layout. */
function rb_migrate_posts_with_layout( $layout ) {
	global $wpdb;
	$ids = $wpdb->get_col( $wpdb->prepare(
		"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'sections' AND meta_value LIKE %s",
		'%' . $wpdb->esc_like( '"' . $layout . '"' ) . '%'
	) );
	return array_map( 'intval', $ids );
}

/**
 * Two service levels (Delivery Only, Full Service) plus Design & Print as an
 * optional extra for either, replacing the Design & Build third level.
 */
function rb_migrate_two_levels() {
	$any = 'sections\_%';
	$map = [
		[ $any . '\_heading', 'Three ways to work with us', 'How we can help' ],
		[ $any . '\_text', 'Three service levels, one family team. Pick the one that fits your event, or tell us what you need and we will recommend it.', 'Two service levels, one family team, plus design and print when you need it. Pick the one that fits your event, or tell us what you need and we will recommend it.' ],
		[ $any . '\_answer', 'Yes. Our Design & Build service covers both: we can get your stand, banners or flags designed and printed, or work alongside your own builder.', 'Yes. We will work alongside your existing supplier on either service, or our Design & Print partner can create new stands, banners or flags for you.' ],
		[ $any . '\_cards\_%\_tag', 'Most popular', '' ],
	];
	foreach ( $map as $m ) {
		rb_migrate_replace_value( $m[0], $m[1], $m[2] );
	}

	// Cards: Design & Build becomes the Design & Print extra.
	foreach ( rb_migrate_posts_with_layout( 'cards' ) as $post_id ) {
		$layouts = get_post_meta( $post_id, 'sections', true );
		foreach ( (array) $layouts as $i => $layout ) {
			if ( 'cards' !== $layout ) {
				continue;
			}
			$base  = 'sections_' . $i . '_cards';
			$count = (int) get_post_meta( $post_id, $base, true );
			for ( $r = 0; $r < $count; $r++ ) {
				$k = $base . '_' . $r . '_';
				if ( 'Design & Build' !== get_post_meta( $post_id, $k . 'title', true ) ) {
					continue;
				}
				$vals = [
					'title'  => 'Design & Print',
					'tag'    => 'Add to either service',
					'text'   => 'Need a new stand, banners or flags? We get them designed and printed through a trusted partner, ready to go out with the rest of your kit.',
					'points' => "Stands, banners and flags designed and printed\nWorks with Delivery Only or Full Service\nOne contact from artwork to event day",
				];
				if ( 'tools' === get_post_meta( $post_id, $k . 'icon', true ) ) {
					$vals['icon'] = 'design';
				}
				foreach ( $vals as $sub => $v ) {
					update_post_meta( $post_id, $k . $sub, $v );
					update_post_meta( $post_id, '_' . $k . $sub, 'field_rb_cards_c_' . $sub );
				}
			}
		}
		clean_post_cache( $post_id );
	}

	// Comparison: drop the third column and move design and print to an optional-extra row.
	foreach ( rb_migrate_posts_with_layout( 'comparison' ) as $post_id ) {
		$layouts = get_post_meta( $post_id, 'sections', true );
		foreach ( (array) $layouts as $i => $layout ) {
			if ( 'comparison' !== $layout ) {
				continue;
			}
			$base = 'sections_' . $i;
			if ( 'Design & Build' !== get_post_meta( $post_id, $base . '_columns_2_title', true ) ) {
				continue;
			}
			rb_migrate_remove_row( $post_id, $base . '_columns', 2, [ 'title', 'subtitle', 'highlight' ] );

			$rows  = $base . '_rows';
			$count = (int) get_post_meta( $post_id, $rows, true );
			for ( $r = 0; $r < $count; $r++ ) {
				if ( 'New stand, banners or flags designed and printed' === get_post_meta( $post_id, $rows . '_' . $r . '_label', true ) ) {
					rb_migrate_remove_row( $post_id, $rows, $r, [ 'label', 'col_1', 'col_2', 'col_3' ] );
					rb_migrate_insert_row( $post_id, $rows, $count - 1, [
						'label' => 'Stands, banners or flags designed and printed',
						'col_1' => 'Optional extra',
						'col_2' => 'Optional extra',
						'col_3' => '',
					], 'field_rb_comparison_r_' );
					break;
				}
			}
		}
		clean_post_cache( $post_id );
	}

	// Process timelines: Design & print becomes an optional extra; drop the old third level.
	foreach ( rb_migrate_posts_with_layout( 'process' ) as $post_id ) {
		$layouts = get_post_meta( $post_id, 'sections', true );
		foreach ( (array) $layouts as $i => $layout ) {
			if ( 'process' !== $layout ) {
				continue;
			}
			$base  = 'sections_' . $i . '_steps';
			$count = (int) get_post_meta( $post_id, $base, true );
			for ( $r = 0; $r < $count; $r++ ) {
				$key   = $base . '_' . $r . '_tiers';
				$tiers = get_post_meta( $post_id, $key, true );
				if ( ! is_array( $tiers ) || ! in_array( 'partner', $tiers, true ) ) {
					continue;
				}
				if ( 'Design & print' === get_post_meta( $post_id, $base . '_' . $r . '_title', true ) ) {
					$tiers = [ 'extra' ];
				} else {
					$tiers = array_values( array_diff( $tiers, [ 'partner' ] ) );
				}
				update_post_meta( $post_id, $key, $tiers );
			}
		}
		clean_post_cache( $post_id );
	}
}

/** The Design & Print card gets a dashed border to mark it as an optional extra. */
function rb_migrate_dashed_extra() {
	foreach ( rb_migrate_posts_with_layout( 'cards' ) as $post_id ) {
		$layouts = get_post_meta( $post_id, 'sections', true );
		foreach ( (array) $layouts as $i => $layout ) {
			if ( 'cards' !== $layout ) {
				continue;
			}
			$base  = 'sections_' . $i . '_cards';
			$count = (int) get_post_meta( $post_id, $base, true );
			for ( $r = 0; $r < $count; $r++ ) {
				$k = $base . '_' . $r . '_dashed';
				if ( 'Design & Print' === get_post_meta( $post_id, $base . '_' . $r . '_title', true ) && '' === (string) get_post_meta( $post_id, $k, true ) ) {
					update_post_meta( $post_id, $k, 1 );
					update_post_meta( $post_id, '_' . $k, 'field_rb_cards_c_dashed' );
				}
			}
		}
		clean_post_cache( $post_id );
	}
}

/** Delivery Only includes the Store step on process timelines. */
function rb_migrate_delivery_store() {
	foreach ( rb_migrate_posts_with_layout( 'process' ) as $post_id ) {
		$layouts = get_post_meta( $post_id, 'sections', true );
		foreach ( (array) $layouts as $i => $layout ) {
			if ( 'process' !== $layout ) {
				continue;
			}
			$base  = 'sections_' . $i . '_steps';
			$count = (int) get_post_meta( $post_id, $base, true );
			for ( $r = 0; $r < $count; $r++ ) {
				$key   = $base . '_' . $r . '_tiers';
				$tiers = get_post_meta( $post_id, $key, true );
				if ( 'Store' === get_post_meta( $post_id, $base . '_' . $r . '_title', true ) && [ 'full' ] === $tiers ) {
					update_post_meta( $post_id, $key, [ 'delivery', 'full' ] );
				}
			}
		}
		clean_post_cache( $post_id );
	}
}

/** Storage card on Other Services: drop the portal bullet and the "About the portal" link. */
function rb_migrate_storage_card() {
	foreach ( rb_migrate_posts_with_layout( 'cards' ) as $post_id ) {
		$layouts = get_post_meta( $post_id, 'sections', true );
		foreach ( (array) $layouts as $i => $layout ) {
			if ( 'cards' !== $layout ) {
				continue;
			}
			$base  = 'sections_' . $i . '_cards';
			$count = (int) get_post_meta( $post_id, $base, true );
			for ( $r = 0; $r < $count; $r++ ) {
				$k = $base . '_' . $r . '_';
				if ( 'Storage' !== get_post_meta( $post_id, $k . 'title', true ) ) {
					continue;
				}
				if ( "Short and long term\nDelivery and collection on request\nSee your stock in the client portal" === get_post_meta( $post_id, $k . 'points', true ) ) {
					update_post_meta( $post_id, $k . 'points', "Short and long term\nDelivery and collection on request" );
				}
				if ( 'About the portal' === get_post_meta( $post_id, $k . 'link_label', true ) ) {
					update_post_meta( $post_id, $k . 'link_label', '' );
					update_post_meta( $post_id, $k . 'link_url', '' );
				}
			}
		}
		clean_post_cache( $post_id );
	}
}

/** Lighter, less try-hard fun facts for the placeholder team. */
function rb_migrate_team_facts() {
	$map = [
		[ 'Can reverse a Luton into any loading bay in the country.', 'Always first in, usually with a bacon roll.' ],
		[ 'Knows every motorway services between here and the NEC.', 'Has strong opinions on the best motorway services.' ],
		[ 'Has never missed a build slot. Not once.', 'Still answers the phone on Sundays, despite being told not to.' ],
		[ 'Came up with the arrow on the van.', 'Runs the office fantasy football league, badly.' ],
		[ 'Has a spreadsheet for everything, including the office tea round.', 'In charge of the tea round. Takes it very seriously.' ],
		[ 'Can put up a pop-up stand in under ten minutes.', 'Owns more Allen keys than anyone needs.' ],
	];
	foreach ( $map as $m ) {
		rb_migrate_replace_value( 'sections\\_%\\_members\\_%\\_fact', $m[0], $m[1] );
	}
}
