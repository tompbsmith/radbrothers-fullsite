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
			if ( 'New stand designed and made' === get_post_meta( $page->ID, $base . '_rows_' . $r . '_label', true ) ) {
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
