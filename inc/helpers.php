<?php
/**
 * Template helpers.
 */

defined( 'ABSPATH' ) || exit;

/** Sections for the current (or given) page. */
function rb_sections( $post_id = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return [];
	}
	if ( ! $post_id ) {
		$post_id = is_singular() ? get_queried_object_id() : 0;
	}
	if ( ! $post_id ) {
		return [];
	}
	$s = get_field( 'sections', $post_id );
	return is_array( $s ) ? $s : [];
}

/** Site setting from the options page, with a fallback. */
function rb_opt( $name, $default = '' ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}
	$v = get_field( $name, 'option' );
	return ( null === $v || '' === $v || false === $v ) ? $default : $v;
}

/** Safe array getter. */
function rb( $arr, $key, $default = '' ) {
	return ( is_array( $arr ) && isset( $arr[ $key ] ) && '' !== $arr[ $key ] && null !== $arr[ $key ] ) ? $arr[ $key ] : $default;
}

/** Inline line icons (24px grid, stroke = currentColor). */
function rb_icon_paths() {
	return [
		'truck'     => '<path d="M3 6h11v10H3z"/><path d="M14 9h4l3 3v4h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/>',
		'box'       => '<path d="M3 7l9-4 9 4v10l-9 4-9-4z"/><path d="M3 7l9 4 9-4"/><path d="M12 11v10"/>',
		'warehouse' => '<path d="M3 21V9l9-5 9 5v12"/><path d="M7 21v-8h10v8"/><path d="M7 17h10"/>',
		'tools'     => '<path d="M14.5 6.5a4 4 0 0 0-5 5L4 17l3 3 5.5-5.5a4 4 0 0 0 5-5l-2.5 2.5-2.5-.5-.5-2.5z"/>',
		'users'     => '<circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17" cy="9" r="2.5"/><path d="M15.5 14.2c3 .2 5.5 2.6 5.5 5.8"/>',
		'shield'    => '<path d="M12 3l8 3v6c0 4.5-3.4 8-8 9-4.6-1-8-4.5-8-9V6z"/><path d="M8.5 12l2.5 2.5 4.5-5"/>',
		'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'react'     => '<path d="M20 11a8 8 0 0 0-14.5-4.5L4 8"/><path d="M4 4v4h4"/><path d="M4 13a8 8 0 0 0 14.5 4.5L20 16"/><path d="M20 20v-4h-4"/>',
		'education' => '<path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/><path d="M22 9v6"/>',
		'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/>',
		'pin'       => '<path d="M12 21s-7-6.2-7-12a7 7 0 0 1 14 0c0 5.8-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/>',
		'phone'     => '<path d="M5 3h4l2 5-2.5 1.5a11 11 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2A17 17 0 0 1 3 5a2 2 0 0 1 2-2z"/>',
		'mail'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
		'calendar'  => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
		'check'     => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
		'route'     => '<circle cx="6" cy="18" r="2.5"/><circle cx="18" cy="6" r="2.5"/><path d="M8.5 18H16a3.5 3.5 0 0 0 0-7H8a3.5 3.5 0 0 1 0-7h7.5"/>',
		'heart'     => '<path d="M12 20s-8-4.8-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 9c0 6.2-8 11-8 11z"/>',
		'monitor'   => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/>',
		'building'  => '<path d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"/><path d="M16 9h3a1 1 0 0 1 1 1v11"/><path d="M8 7h4M8 11h4M8 15h4M3 21h18"/>',
		'arrow'     => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'chevron'   => '<path d="M9 6l6 6-6 6"/>',
		'linkedin'  => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 10v7M8 7v.01M12 17v-4a2 2 0 0 1 4 0v4M12 10v7"/>',
		'quote'     => '<path fill="currentColor" stroke="none" d="M4 18v-5.5C4 8.4 6 6 9.5 5l.8 1.6C8.3 7.5 7.4 9 7.3 11H10v7H4zm10 0v-5.5c0-4.1 2-6.5 5.5-7.5l.8 1.6c-2 .9-2.9 2.4-3 4.4H20v7h-6z"/>',
		'close'     => '<path d="M6 6l12 12M18 6L6 18"/>',
		'plus'      => '<path d="M12 5v14M5 12h14"/>',
	];
}

function rb_icon( $name, $class = '' ) {
	$icons = rb_icon_paths();
	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="icon %s" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%s</svg>',
		esc_attr( $class ),
		$icons[ $name ]
	);
}

/** Icon choices for the fields UI. */
function rb_icon_choices() {
	$out = [];
	foreach ( array_keys( rb_icon_paths() ) as $k ) {
		$out[ $k ] = ucfirst( $k );
	}
	return $out;
}

/** The brand arrow mark, inline, so it can take currentColor. */
function rb_arrow_mark( $class = '' ) {
	return '<svg class="arrow-mark ' . esc_attr( $class ) . '" viewBox="0 0 158.83 146.87" aria-hidden="true" focusable="false"><path fill="currentColor" fill-rule="evenodd" d="M 43.23 100.69 L 47.73 86.96 L 52.23 73.24 L 47.70 59.81 L 43.15 46.38 L 43.14 46.28 L 43.13 46.18 L 58.74 46.18 L 102.09 73.24 L 58.74 100.69 Z M 42.04 0.00 L 0.00 0.00 L 0.02 0.27 L 0.04 0.55 L 12.28 36.74 L 24.51 72.92 L 0.25 146.86 L 42.04 146.86 L 158.82 72.92 Z M 42.04 0.00"/></svg>';
}

/** Logo image (brand SVG). $variant: longform | condensed ; $tone: '' | white */
function rb_logo( $variant = 'longform', $tone = '', $class = '' ) {
	$file = $variant . ( $tone ? '-' . $tone : '' ) . '.svg';
	return sprintf(
		'<img class="logo logo--%1$s %4$s" src="%2$s" alt="%3$s" width="%5$d" height="%6$d">',
		esc_attr( $variant ),
		esc_url( RB_URI . '/assets/img/brand/' . $file ),
		esc_attr( get_bloginfo( 'name' ) ),
		esc_attr( $class ),
		'longform' === $variant ? 825 : 372,
		'longform' === $variant ? 124 : 192
	);
}

/** Responsive image from an ACF image array/ID. */
function rb_image( $img, $size = 'large', $attrs = [] ) {
	$id = is_array( $img ) ? ( $img['ID'] ?? 0 ) : (int) $img;
	if ( ! $id ) {
		return '';
	}
	$attrs = array_merge( [ 'loading' => 'lazy', 'decoding' => 'async' ], $attrs );
	return wp_get_attachment_image( $id, $size, false, $attrs );
}

function rb_image_url( $img, $size = 'large' ) {
	$id = is_array( $img ) ? ( $img['ID'] ?? 0 ) : (int) $img;
	return $id ? wp_get_attachment_image_url( $id, $size ) : '';
}

/** Buttons repeater output. */
function rb_buttons( $buttons, $class = '' ) {
	if ( empty( $buttons ) || ! is_array( $buttons ) ) {
		return '';
	}
	$html = '<div class="buttons ' . esc_attr( $class ) . '">';
	foreach ( $buttons as $b ) {
		if ( empty( $b['label'] ) || empty( $b['url'] ) ) {
			continue;
		}
		$style = rb( $b, 'style', 'primary' );
		$html .= sprintf(
			'<a class="btn btn--%s" href="%s"><span>%s</span>%s</a>',
			esc_attr( $style ),
			esc_url( $b['url'] ),
			esc_html( $b['label'] ),
			rb_icon( 'arrow', 'btn__icon' )
		);
	}
	return $html . '</div>';
}

/** Opening tag for a section. */
function rb_section_open( $s, $extra_class = '', $default_bg = 'white' ) {
	$layout = $s['acf_fc_layout'] ?? 'section';
	$bg     = rb( $s, 'background', $default_bg );
	$id     = rb( $s, 'anchor' );
	printf(
		'<section class="section section--%1$s bg-%2$s %3$s"%4$s data-animate>',
		esc_attr( str_replace( '_', '-', $layout ) ),
		esc_attr( $bg ),
		esc_attr( $extra_class ),
		$id ? ' id="' . esc_attr( sanitize_title( $id ) ) . '"' : ''
	);
}

function rb_section_close() {
	echo '</section>';
}

/** Eyebrow / heading / intro block. */
function rb_section_head( $s, $class = '', $tag = 'h2' ) {
	$eyebrow = rb( $s, 'eyebrow' );
	$heading = rb( $s, 'heading' );
	$intro   = rb( $s, 'intro' );
	if ( ! $eyebrow && ! $heading && ! $intro ) {
		return;
	}
	echo '<header class="section-head ' . esc_attr( $class ) . '">';
	if ( $eyebrow ) {
		echo '<p class="eyebrow reveal">' . rb_arrow_mark() . '<span>' . esc_html( $eyebrow ) . '</span></p>';
	}
	if ( $heading ) {
		printf( '<%1$s class="section-title reveal" style="--d:1">%2$s</%1$s>', tag_escape( $tag ), nl2br( esc_html( $heading ) ) );
	}
	if ( $intro ) {
		echo '<p class="section-intro reveal" style="--d:2">' . nl2br( esc_html( $intro ) ) . '</p>';
	}
	echo '</header>';
}

/** Render all sections for a page. */
function rb_render_sections( $sections ) {
	foreach ( $sections as $i => $s ) {
		$layout = $s['acf_fc_layout'] ?? '';
		$file   = RB_DIR . '/template-parts/sections/' . str_replace( '_', '-', $layout ) . '.php';
		if ( $layout && file_exists( $file ) ) {
			$section_index = $i;
			include $file;
		}
	}
}

/** Phone number as tel: link. */
function rb_tel( $phone ) {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', (string) $phone );
}

/** Nav menu fallback: list top-level pages. */
function rb_menu_fallback( $args ) {
	echo '<ul class="' . esc_attr( $args['menu_class'] ?? 'menu' ) . '">';
	wp_list_pages( [ 'title_li' => '', 'depth' => 2 ] );
	echo '</ul>';
}
