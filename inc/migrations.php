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
