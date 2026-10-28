<?php
/**
 * Rad Brothers theme bootstrap.
 *
 * Page content is built from the "Page sections" flexible content field
 * (registered in inc/fields.php). Requires Secure Custom Fields (free) or ACF Pro,
 * because it uses Flexible Content, Repeater and Options Page.
 */

defined( 'ABSPATH' ) || exit;

define( 'RB_VERSION', '1.1.0' );
define( 'RB_DIR', get_template_directory() );
define( 'RB_URI', get_template_directory_uri() );

require RB_DIR . '/inc/helpers.php';
require RB_DIR . '/inc/fields.php';
require RB_DIR . '/inc/contact.php';
require RB_DIR . '/inc/seed.php';

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'responsive-embeds' );
	add_image_size( 'rb-hero', 2000, 1200, true );
	add_image_size( 'rb-card', 900, 640, true );
	register_nav_menus( [
		'primary' => __( 'Main menu', 'radbrothers' ),
		'footer'  => __( 'Footer quick links', 'radbrothers' ),
		'legal'   => __( 'Footer legal links', 'radbrothers' ),
	] );
} );

add_action( 'wp_enqueue_scripts', function () {
	$css = RB_DIR . '/assets/css/main.css';
	$js  = RB_DIR . '/assets/js/main.js';
	wp_enqueue_style( 'rb-main', RB_URI . '/assets/css/main.css', [], RB_VERSION . '-' . filemtime( $css ) );
	wp_enqueue_script( 'rb-main', RB_URI . '/assets/js/main.js', [], RB_VERSION . '-' . filemtime( $js ), [ 'strategy' => 'defer', 'in_footer' => true ] );
	// Remove block library CSS on the front end; this theme does not use blocks.
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
} );

// Preload the two fonts used above the fold.
add_action( 'wp_head', function () {
	foreach ( [ 'SpaceGrotesk-VF.woff2', 'Jost-VF.woff2' ] as $f ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( RB_URI . '/assets/fonts/' . $f ) );
	}
	printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( RB_URI . '/assets/img/brand/arrow.svg' ) );
	echo '<meta name="theme-color" content="#0f273c">' . "\n";
}, 1 );

// "js" class on <html> so content is only hidden for animation when JS is running.
add_action( 'wp_head', function () {
	echo "<script>document.documentElement.classList.add('js');</script>\n";
}, 0 );

// Body classes for header style.
add_filter( 'body_class', function ( $classes ) {
	$sections = rb_sections();
	if ( $sections && 'hero' === ( $sections[0]['acf_fc_layout'] ?? '' ) ) {
		$classes[] = 'has-hero';
	}
	return $classes;
} );

// Admin notice if SCF / ACF is missing.
add_action( 'admin_notices', function () {
	if ( function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p><strong>Rad Brothers theme:</strong> please install and activate <a href="' . esc_url( admin_url( 'plugin-install.php?s=secure+custom+fields&tab=search&type=term' ) ) . '">Secure Custom Fields</a> (or ACF Pro). Page sections will not show without it.</p></div>';
} );
