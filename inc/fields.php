<?php
/**
 * Field groups (Secure Custom Fields / ACF Pro), registered in code so they ship with the theme.
 *
 * - "Page sections": one Flexible Content field on every page. Each layout = one section template
 *   in template-parts/sections/{layout}.php.
 * - "Site settings": options page for contact details, footer and form delivery.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tiny field builder so the definitions below stay readable.
 * Keys are derived from the prefix + name, so they stay stable between releases.
 */
function rb_f( $prefix, $name, $type = 'text', $args = [] ) {
	$field = array_merge( [
		'key'   => 'field_rb_' . $prefix . '_' . $name,
		'name'  => $name,
		'label' => ucfirst( str_replace( '_', ' ', $name ) ),
		'type'  => $type,
	], $args );
	if ( 'image' === $type ) {
		$field += [ 'return_format' => 'array', 'preview_size' => 'medium', 'library' => 'all' ];
	}
	if ( 'repeater' === $type ) {
		$field += [ 'layout' => 'block', 'button_label' => 'Add row' ];
	}
	return $field;
}

function rb_bg_field( $p, $default = 'white' ) {
	return rb_f( $p, 'background', 'select', [
		'choices'       => [ 'white' => 'White', 'light' => 'Light grey', 'navy' => 'Navy' ],
		'default_value' => $default,
		'wrapper'       => [ 'width' => 33 ],
	] );
}

function rb_anchor_field( $p ) {
	return rb_f( $p, 'anchor', 'text', [
		'label'        => 'Anchor ID',
		'instructions' => 'Optional. Lets you link straight to this section, e.g. #contact',
		'wrapper'      => [ 'width' => 33 ],
	] );
}

function rb_head_fields( $p ) {
	return [
		rb_f( $p, 'eyebrow', 'text', [ 'instructions' => 'Small label above the heading', 'wrapper' => [ 'width' => 40 ] ] ),
		rb_f( $p, 'heading', 'textarea', [ 'rows' => 2, 'new_lines' => '', 'wrapper' => [ 'width' => 60 ] ] ),
		rb_f( $p, 'intro', 'textarea', [ 'rows' => 3, 'new_lines' => '' ] ),
	];
}

function rb_buttons_field( $p, $max = 2 ) {
	return rb_f( $p, 'buttons', 'repeater', [
		'layout'     => 'table',
		'max'        => $max,
		'sub_fields' => [
			rb_f( $p . '_btn', 'label' ),
			rb_f( $p . '_btn', 'url', 'text', [ 'label' => 'Link', 'instructions' => 'URL or #anchor' ] ),
			rb_f( $p . '_btn', 'style', 'select', [ 'choices' => [ 'primary' => 'Orange', 'ghost' => 'Outline', 'navy' => 'Navy' ], 'default_value' => 'primary' ] ),
		],
	] );
}

function rb_layout( $name, $label, $sub_fields ) {
	return [
		'key'        => 'layout_rb_' . $name,
		'name'       => $name,
		'label'      => $label,
		'display'    => 'block',
		'sub_fields' => $sub_fields,
	];
}

function rb_layouts() {
	$icons = rb_icon_choices();
	$L     = [];

	$p             = 'hero';
	$L['hero']     = rb_layout( 'hero', 'Hero / page header', [
		rb_f( $p, 'size', 'select', [ 'choices' => [ 'home' => 'Large (home page)', 'page' => 'Compact (inner page)' ], 'default_value' => 'page', 'wrapper' => [ 'width' => 33 ] ] ),
		rb_f( $p, 'overlay', 'range', [ 'label' => 'Image darkness', 'min' => 20, 'max' => 90, 'default_value' => 60, 'append' => '%', 'wrapper' => [ 'width' => 33 ] ] ),
		rb_anchor_field( $p ),
		rb_f( $p, 'image', 'image' ),
		rb_f( $p, 'eyebrow', 'text' ),
		rb_f( $p, 'heading', 'textarea', [ 'rows' => 2, 'new_lines' => '', 'instructions' => 'Wrap words in *asterisks* to highlight them in orange.' ] ),
		rb_f( $p, 'text', 'textarea', [ 'rows' => 3, 'new_lines' => '' ] ),
		rb_buttons_field( $p ),
		rb_f( $p, 'highlights', 'repeater', [
			'instructions' => 'Optional short proof points under the buttons (home hero only).',
			'layout'       => 'table',
			'max'          => 4,
			'sub_fields'   => [ rb_f( $p . '_hl', 'icon', 'select', [ 'choices' => $icons ] ), rb_f( $p . '_hl', 'text' ) ],
		] ),
	] );

	$p         = 'logos';
	$L['logos'] = rb_layout( 'logos', 'Client logos', array_merge(
		[ rb_bg_field( $p ), rb_anchor_field( $p ), rb_f( $p, 'display', 'select', [ 'choices' => [ 'marquee' => 'Scrolling strip', 'grid' => 'Grid' ], 'default_value' => 'marquee', 'wrapper' => [ 'width' => 33 ] ] ) ],
		rb_head_fields( $p ),
		[ rb_f( $p, 'logos', 'repeater', [
			'layout'     => 'table',
			'sub_fields' => [ rb_f( $p . '_l', 'image', 'image', [ 'label' => 'Logo' ] ), rb_f( $p . '_l', 'name', 'text', [ 'label' => 'Organisation name' ] ) ],
		] ) ]
	) );

	$p         = 'cards';
	$L['cards'] = rb_layout( 'cards', 'Cards (services, features)', array_merge(
		[ rb_bg_field( $p ), rb_anchor_field( $p ),
		  rb_f( $p, 'columns', 'select', [ 'choices' => [ '2' => '2', '3' => '3', '4' => '4' ], 'default_value' => '3', 'wrapper' => [ 'width' => 33 ] ] ) ],
		rb_head_fields( $p ),
		[ rb_f( $p, 'cards', 'repeater', [
			'sub_fields' => [
				rb_f( $p . '_c', 'icon', 'select', [ 'choices' => [ '' => 'None' ] + $icons, 'allow_null' => 1, 'wrapper' => [ 'width' => 25 ] ] ),
				rb_f( $p . '_c', 'image', 'image', [ 'instructions' => 'Optional photo instead of an icon', 'wrapper' => [ 'width' => 25 ] ] ),
				rb_f( $p . '_c', 'tag', 'text', [ 'instructions' => 'Small label, e.g. "Most popular"', 'wrapper' => [ 'width' => 50 ] ] ),
				rb_f( $p . '_c', 'title' ),
				rb_f( $p . '_c', 'text', 'textarea', [ 'rows' => 3, 'new_lines' => '' ] ),
				rb_f( $p . '_c', 'points', 'textarea', [ 'rows' => 3, 'new_lines' => '', 'instructions' => 'Optional tick list, one per line' ] ),
				rb_f( $p . '_c', 'link_label', 'text', [ 'wrapper' => [ 'width' => 50 ] ] ),
				rb_f( $p . '_c', 'link_url', 'text', [ 'wrapper' => [ 'width' => 50 ] ] ),
			],
		] ),
		rb_buttons_field( $p ) ]
	) );

	$p         = 'split';
	$L['split'] = rb_layout( 'split', 'Two big panels (audiences)', array_merge(
		[ rb_bg_field( $p ), rb_anchor_field( $p ) ],
		rb_head_fields( $p ),
		[ rb_f( $p, 'panels', 'repeater', [
			'min'        => 2,
			'max'        => 2,
			'sub_fields' => [
				rb_f( $p . '_p', 'image', 'image' ),
				rb_f( $p . '_p', 'icon', 'select', [ 'choices' => $icons ] ),
				rb_f( $p . '_p', 'title' ),
				rb_f( $p . '_p', 'text', 'textarea', [ 'rows' => 3, 'new_lines' => '' ] ),
				rb_f( $p . '_p', 'link_label', 'text', [ 'wrapper' => [ 'width' => 50 ] ] ),
				rb_f( $p . '_p', 'link_url', 'text', [ 'wrapper' => [ 'width' => 50 ] ] ),
			],
		] ) ]
	) );

	$p           = 'process';
	$L['process'] = rb_layout( 'process', 'Process / how it works', array_merge(
		[ rb_bg_field( $p, 'light' ), rb_anchor_field( $p ),
		  rb_f( $p, 'style', 'select', [ 'choices' => [ 'compact' => 'Compact strip', 'detailed' => 'Detailed timeline' ], 'default_value' => 'compact', 'wrapper' => [ 'width' => 33 ] ] ) ],
		rb_head_fields( $p ),
		[ rb_f( $p, 'steps', 'repeater', [
			'sub_fields' => [
				rb_f( $p . '_s', 'icon', 'select', [ 'choices' => $icons, 'wrapper' => [ 'width' => 30 ] ] ),
				rb_f( $p . '_s', 'title', 'text', [ 'wrapper' => [ 'width' => 70 ] ] ),
				rb_f( $p . '_s', 'text', 'textarea', [ 'rows' => 2, 'new_lines' => '', 'label' => 'What we do' ] ),
				rb_f( $p . '_s', 'client', 'text', [ 'label' => 'What you do', 'instructions' => 'Detailed timeline only' ] ),
				rb_f( $p . '_s', 'tiers', 'checkbox', [
					'label'   => 'Included in',
					'choices' => [ 'delivery' => 'Delivery Only', 'full' => 'Full Service', 'partner' => 'Build Partner' ],
					'layout'  => 'horizontal',
				] ),
			],
		] ),
		rb_f( $p, 'show_tiers', 'true_false', [ 'label' => 'Show which service level includes each step', 'ui' => 1 ] ),
		rb_buttons_field( $p ) ]
	) );

	$p            = 'features';
	$L['features'] = rb_layout( 'features', 'Why us / feature list', array_merge(
		[ rb_bg_field( $p, 'navy' ), rb_anchor_field( $p ) ],
		rb_head_fields( $p ),
		[ rb_f( $p, 'image', 'image', [ 'instructions' => 'Optional image beside the list' ] ),
		  rb_f( $p, 'items', 'repeater', [
			'layout'     => 'block',
			'sub_fields' => [
				rb_f( $p . '_i', 'icon', 'select', [ 'choices' => $icons, 'wrapper' => [ 'width' => 30 ] ] ),
				rb_f( $p . '_i', 'title', 'text', [ 'wrapper' => [ 'width' => 70 ] ] ),
				rb_f( $p . '_i', 'text', 'textarea', [ 'rows' => 2, 'new_lines' => '' ] ),
			],
		] ) ]
	) );

	$p                = 'testimonials';
	$L['testimonials'] = rb_layout( 'testimonials', 'Testimonials', array_merge(
		[ rb_bg_field( $p, 'light' ), rb_anchor_field( $p ),
		  rb_f( $p, 'display', 'select', [ 'choices' => [ 'single' => 'Featured (one at a time)', 'grid' => 'Grid' ], 'default_value' => 'single', 'wrapper' => [ 'width' => 33 ] ] ) ],
		rb_head_fields( $p ),
		[ rb_f( $p, 'items', 'repeater', [
			'sub_fields' => [
				rb_f( $p . '_t', 'quote', 'textarea', [ 'rows' => 4, 'new_lines' => '' ] ),
				rb_f( $p . '_t', 'name', 'text', [ 'wrapper' => [ 'width' => 33 ] ] ),
				rb_f( $p . '_t', 'role', 'text', [ 'wrapper' => [ 'width' => 33 ] ] ),
				rb_f( $p . '_t', 'organisation', 'text', [ 'wrapper' => [ 'width' => 34 ] ] ),
			],
		] ),
		rb_buttons_field( $p, 1 ) ]
	) );

	$p        = 'team';
	$L['team'] = rb_layout( 'team', 'Meet the team (group photo)', array_merge(
		[ rb_bg_field( $p ), rb_anchor_field( $p ) ],
		rb_head_fields( $p ),
		[ rb_f( $p, 'image', 'image', [ 'label' => 'Group photo', 'instructions' => 'A wide photo of the whole team. Then add a person below for each face.' ] ),
		  rb_f( $p, 'members', 'repeater', [
			'instructions' => 'Position each face with X/Y as a percentage of the photo (0 = left/top, 100 = right/bottom). Tip: open the photo, hover the face and read the position.',
			'layout'       => 'block',
			'button_label' => 'Add person',
			'sub_fields'   => [
				rb_f( $p . '_m', 'name', 'text', [ 'wrapper' => [ 'width' => 35 ] ] ),
				rb_f( $p . '_m', 'role', 'text', [ 'wrapper' => [ 'width' => 35 ] ] ),
				rb_f( $p . '_m', 'x', 'number', [ 'label' => 'Face X %', 'min' => 0, 'max' => 100, 'step' => 0.1, 'wrapper' => [ 'width' => 15 ] ] ),
				rb_f( $p . '_m', 'y', 'number', [ 'label' => 'Face Y %', 'min' => 0, 'max' => 100, 'step' => 0.1, 'wrapper' => [ 'width' => 15 ] ] ),
				rb_f( $p . '_m', 'bio', 'textarea', [ 'rows' => 2, 'new_lines' => '' ] ),
				rb_f( $p . '_m', 'fact', 'text', [ 'label' => 'Fun fact', 'instructions' => 'Optional, e.g. "Can reverse a Luton into any loading bay"' ] ),
			],
		] ) ]
	) );

	$p              = 'text_image';
	$L['text_image'] = rb_layout( 'text_image', 'Text + image', array_merge(
		[ rb_bg_field( $p ), rb_anchor_field( $p ),
		  rb_f( $p, 'image_position', 'select', [ 'choices' => [ 'right' => 'Image right', 'left' => 'Image left' ], 'default_value' => 'right', 'wrapper' => [ 'width' => 33 ] ] ) ],
		[ rb_f( $p, 'eyebrow', 'text' ), rb_f( $p, 'heading', 'textarea', [ 'rows' => 2, 'new_lines' => '' ] ),
		  rb_f( $p, 'content', 'wysiwyg', [ 'tabs' => 'all', 'toolbar' => 'basic', 'media_upload' => 0 ] ),
		  rb_f( $p, 'image', 'image' ),
		  rb_f( $p, 'badge', 'text', [ 'label' => 'Floating badge text', 'instructions' => 'Optional short line shown on a card over the image' ] ),
		  rb_buttons_field( $p ) ]
	) );

	$p              = 'portal';
	$L['portal']     = rb_layout( 'portal', 'Client portal showcase', array_merge(
		[ rb_bg_field( $p, 'navy' ), rb_anchor_field( $p ),
		  rb_f( $p, 'mockup_position', 'select', [ 'label' => 'Portal screen', 'choices' => [ 'right' => 'Right', 'left' => 'Left' ], 'default_value' => 'right', 'wrapper' => [ 'width' => 33 ] ] ) ],
		rb_head_fields( $p ),
		[ rb_f( $p, 'features', 'repeater', [
			'layout'     => 'table',
			'max'        => 5,
			'sub_fields' => [
				rb_f( $p . '_f', 'icon', 'select', [ 'choices' => $icons ] ),
				rb_f( $p . '_f', 'title' ),
				rb_f( $p . '_f', 'text' ),
			],
		] ),
		  rb_buttons_field( $p ),
		  rb_f( $p, 'badge', 'text', [ 'label' => 'Floating badge text', 'instructions' => 'Optional, e.g. "Free for every client"' ] ),
		  rb_f( $p, 'screenshot', 'image', [ 'label' => 'Portal screenshot (optional)', 'instructions' => 'Leave empty to use the built-in illustrated dashboard. If you upload a real screenshot, use a demo account: no real customer names or orders.' ] ) ]
	) );

	$p              = 'comparison';
	$L['comparison'] = rb_layout( 'comparison', 'Comparison table', array_merge(
		[ rb_bg_field( $p ), rb_anchor_field( $p ) ],
		rb_head_fields( $p ),
		[ rb_f( $p, 'columns', 'repeater', [
			'min'        => 3,
			'max'        => 3,
			'layout'     => 'table',
			'sub_fields' => [ rb_f( $p . '_c', 'title' ), rb_f( $p . '_c', 'subtitle' ), rb_f( $p . '_c', 'highlight', 'true_false', [ 'ui' => 1 ] ) ],
		] ),
		  rb_f( $p, 'rows', 'repeater', [
			'layout'     => 'table',
			'sub_fields' => [
				rb_f( $p . '_r', 'label' ),
				rb_f( $p . '_r', 'col_1', 'text', [ 'label' => 'Column 1', 'instructions' => 'yes, no, or short text' ] ),
				rb_f( $p . '_r', 'col_2', 'text', [ 'label' => 'Column 2' ] ),
				rb_f( $p . '_r', 'col_3', 'text', [ 'label' => 'Column 3' ] ),
			],
		] ),
		  rb_f( $p, 'note', 'text' ) ]
	) );

	$p       = 'faq';
	$L['faq'] = rb_layout( 'faq', 'FAQs', array_merge(
		[ rb_bg_field( $p ), rb_anchor_field( $p ) ],
		rb_head_fields( $p ),
		[ rb_f( $p, 'items', 'repeater', [
			'sub_fields' => [ rb_f( $p . '_q', 'question' ), rb_f( $p . '_q', 'answer', 'textarea', [ 'rows' => 3, 'new_lines' => 'wpautop' ] ) ],
		] ) ]
	) );

	$p                = 'case_studies';
	$L['case_studies'] = rb_layout( 'case_studies', 'Case studies', array_merge(
		[ rb_bg_field( $p ), rb_anchor_field( $p ) ],
		rb_head_fields( $p ),
		[ rb_f( $p, 'items', 'repeater', [
			'sub_fields' => [
				rb_f( $p . '_i', 'image', 'image' ),
				rb_f( $p . '_i', 'tag', 'text', [ 'instructions' => 'e.g. University / Trade show' ] ),
				rb_f( $p . '_i', 'title' ),
				rb_f( $p . '_i', 'challenge', 'textarea', [ 'rows' => 2, 'new_lines' => '' ] ),
				rb_f( $p . '_i', 'solution', 'textarea', [ 'rows' => 2, 'new_lines' => '', 'label' => 'What we did' ] ),
				rb_f( $p . '_i', 'outcome', 'textarea', [ 'rows' => 2, 'new_lines' => '' ] ),
			],
		] ) ]
	) );

	$p       = 'cta';
	$L['cta'] = rb_layout( 'cta', 'Call to action band', [
		rb_f( $p, 'style', 'select', [ 'choices' => [ 'orange' => 'Orange', 'navy' => 'Navy' ], 'default_value' => 'orange', 'wrapper' => [ 'width' => 33 ] ] ),
		rb_anchor_field( $p ),
		rb_f( $p, 'heading', 'textarea', [ 'rows' => 2, 'new_lines' => '' ] ),
		rb_f( $p, 'text', 'textarea', [ 'rows' => 2, 'new_lines' => '' ] ),
		rb_buttons_field( $p ),
	] );

	$p           = 'contact';
	$L['contact'] = rb_layout( 'contact', 'Contact / quote form', array_merge(
		[ rb_bg_field( $p, 'light' ), rb_anchor_field( $p ),
		  rb_f( $p, 'form', 'select', [ 'choices' => [ 'full' => 'Full quote form', 'short' => 'Short form' ], 'default_value' => 'full', 'wrapper' => [ 'width' => 33 ] ] ) ],
		rb_head_fields( $p ),
		[ rb_f( $p, 'shortcode', 'text', [ 'label' => 'Form shortcode (optional)', 'instructions' => 'Paste a Contact Form 7 / WPForms / Gravity Forms shortcode to use instead of the built-in form.' ] ),
		  rb_f( $p, 'show_details', 'true_false', [ 'label' => 'Show phone, email and address', 'ui' => 1, 'default_value' => 1 ] ) ]
	) );

	$p             = 'rich_text';
	$L['rich_text'] = rb_layout( 'rich_text', 'Text', [
		rb_bg_field( $p ),
		rb_anchor_field( $p ),
		rb_f( $p, 'heading', 'text' ),
		rb_f( $p, 'content', 'wysiwyg', [ 'tabs' => 'all', 'toolbar' => 'full', 'media_upload' => 1 ] ),
	] );

	return $L;
}

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( [
		'key'                   => 'group_rb_sections',
		'title'                 => 'Page sections',
		'fields'                => [
			[
				'key'          => 'field_rb_sections',
				'name'         => 'sections',
				'label'        => 'Sections',
				'type'         => 'flexible_content',
				'button_label' => 'Add section',
				'layouts'      => rb_layouts(),
			],
		],
		'location'              => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ] ] ],
		'position'              => 'acf_after_title',
		'style'                 => 'seamless',
		'hide_on_screen'        => [ 'the_content', 'excerpt', 'discussion', 'comments', 'format', 'categories', 'tags', 'send-trackbacks' ],
	] );

	if ( function_exists( 'acf_add_options_page' ) ) {
		acf_add_options_page( [
			'page_title' => 'Site settings',
			'menu_title' => 'Site settings',
			'menu_slug'  => 'rb-settings',
			'capability' => 'edit_theme_options',
			'icon_url'   => 'dashicons-admin-site-alt3',
			'position'   => 59,
		] );
	}

	$p = 'opt';
	acf_add_local_field_group( [
		'key'      => 'group_rb_options',
		'title'    => 'Site settings',
		'fields'   => [
			[ 'key' => 'field_rb_opt_tab_contact', 'label' => 'Contact details', 'type' => 'tab' ],
			rb_f( $p, 'phone', 'text', [ 'wrapper' => [ 'width' => 50 ] ] ),
			rb_f( $p, 'email', 'email', [ 'wrapper' => [ 'width' => 50 ] ] ),
			rb_f( $p, 'address', 'textarea', [ 'rows' => 3, 'new_lines' => 'br', 'wrapper' => [ 'width' => 50 ] ] ),
			rb_f( $p, 'hours', 'text', [ 'label' => 'Office hours', 'wrapper' => [ 'width' => 50 ] ] ),
			rb_f( $p, 'coverage', 'text', [ 'label' => 'Coverage area', 'wrapper' => [ 'width' => 50 ] ] ),
			rb_f( $p, 'response_time', 'text', [ 'wrapper' => [ 'width' => 50 ] ] ),
			rb_f( $p, 'linkedin', 'url', [ 'label' => 'LinkedIn page URL' ] ),

			[ 'key' => 'field_rb_opt_tab_header', 'label' => 'Header & footer', 'type' => 'tab' ],
			rb_f( $p, 'cta_label', 'text', [ 'label' => 'Header button label', 'default_value' => 'Get a quote', 'wrapper' => [ 'width' => 50 ] ] ),
			rb_f( $p, 'cta_url', 'text', [ 'label' => 'Header button link', 'wrapper' => [ 'width' => 50 ] ] ),
			rb_f( $p, 'portal_label', 'text', [ 'label' => 'Client portal link label', 'default_value' => 'Client portal', 'wrapper' => [ 'width' => 50 ] ] ),
			rb_f( $p, 'portal_url', 'url', [ 'label' => 'Client portal URL', 'wrapper' => [ 'width' => 50 ] ] ),
			rb_f( $p, 'footer_blurb', 'textarea', [ 'rows' => 3, 'new_lines' => '' ] ),
			rb_f( $p, 'company_number', 'text', [ 'wrapper' => [ 'width' => 50 ] ] ),
			rb_f( $p, 'registered_address', 'text', [ 'wrapper' => [ 'width' => 50 ] ] ),

			[ 'key' => 'field_rb_opt_tab_form', 'label' => 'Contact form', 'type' => 'tab' ],
			rb_f( $p, 'form_to', 'email', [ 'label' => 'Send enquiries to', 'wrapper' => [ 'width' => 50 ] ] ),
			rb_f( $p, 'form_from', 'text', [ 'label' => 'Send from', 'instructions' => 'Must be on a domain verified in Resend, e.g. Rad Brothers Website <contact@radbrothers.co.uk>', 'wrapper' => [ 'width' => 50 ] ] ),
			rb_f( $p, 'resend_key', 'password', [ 'label' => 'Resend API key', 'instructions' => 'Better: define RB_RESEND_API_KEY in wp-config.php and leave this blank. If no key is set, the site falls back to wp_mail().' ] ),
		],
		'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'rb-settings' ] ] ],
	] );
} );

// Show the layout's heading in the collapsed flexible-content bar in the editor.
add_filter( 'acf/fields/flexible_content/layout_title/name=sections', function ( $title, $field, $layout, $i ) {
	$h = get_sub_field( 'heading' );
	if ( $h ) {
		$title .= ' — <span style="opacity:.6;font-weight:400">' . esc_html( wp_trim_words( wp_strip_all_tags( $h ), 8 ) ) . '</span>';
	}
	return $title;
}, 10, 4 );
