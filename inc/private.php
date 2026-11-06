<?php
/**
 * Private site mode.
 *
 * When "Make the site private" is on in Site settings, visitors who aren't logged in
 * see a branded holding page. Logged-in users see the site as normal, and anyone with
 * the preview link can see it too until the link expires or is replaced.
 */

defined( 'ABSPATH' ) || exit;

const RB_PREVIEW_PARAM  = 'rb_preview';
const RB_PREVIEW_COOKIE = 'rb_preview';

function rb_site_is_private() {
	return (bool) rb_opt( 'private_on' );
}

/** The current preview key, or '' if none has been made yet. */
function rb_preview_key() {
	return (string) get_option( 'rb_preview_key', '' );
}

/** Unix time the preview link stops working, 0 for no expiry, or -1 if there is no link. */
function rb_preview_expires() {
	if ( ! rb_preview_key() ) {
		return -1;
	}
	$days = (int) rb_opt( 'preview_days', 7 );
	if ( $days <= 0 ) {
		return 0;
	}
	return (int) get_option( 'rb_preview_created', 0 ) + $days * DAY_IN_SECONDS;
}

function rb_preview_is_live() {
	$expires = rb_preview_expires();
	return 0 === $expires || $expires > time();
}

function rb_preview_url() {
	return rb_preview_key() ? add_query_arg( RB_PREVIEW_PARAM, rb_preview_key(), home_url( '/' ) ) : '';
}

/** What the visitor's cookie must hold: tied to the key, so a new key locks out old cookies. */
function rb_preview_cookie_value() {
	return hash_hmac( 'sha256', rb_preview_key(), wp_salt( 'auth' ) );
}

function rb_has_preview_access() {
	if ( ! rb_preview_key() || ! rb_preview_is_live() || empty( $_COOKIE[ RB_PREVIEW_COOKIE ] ) ) {
		return false;
	}
	return hash_equals( rb_preview_cookie_value(), (string) wp_unslash( $_COOKIE[ RB_PREVIEW_COOKIE ] ) );
}

/** Can the current visitor see the real site? */
function rb_can_view_site() {
	return ! rb_site_is_private() || is_user_logged_in() || rb_has_preview_access();
}

// The gate. Runs before anything else renders, including sitemaps and feeds.
add_action( 'template_redirect', function () {
	if ( ! rb_site_is_private() || is_user_logged_in() ) {
		return;
	}

	// Arriving with the preview link: remember the visitor, then tidy the address.
	if ( isset( $_GET[ RB_PREVIEW_PARAM ] ) ) {
		$given = sanitize_text_field( wp_unslash( $_GET[ RB_PREVIEW_PARAM ] ) );
		if ( rb_preview_key() && rb_preview_is_live() && hash_equals( rb_preview_key(), $given ) ) {
			$expires = rb_preview_expires();
			setcookie( RB_PREVIEW_COOKIE, rb_preview_cookie_value(), [
				'expires'  => $expires ? $expires : time() + 30 * DAY_IN_SECONDS,
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => COOKIE_DOMAIN,
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			] );
			nocache_headers();
			wp_safe_redirect( remove_query_arg( RB_PREVIEW_PARAM ) );
			exit;
		}
	}

	if ( rb_has_preview_access() ) {
		nocache_headers(); // so a cache never hands the real site to someone without access
		return;
	}

	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow' );
	status_header( 200 );
	get_template_part( 'template-parts/private-page' );
	exit;
}, 0 );

// Keep the site out of search engines while it's private.
add_filter( 'wp_robots', function ( $robots ) {
	if ( rb_site_is_private() ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
	}
	return $robots;
} );

// The REST API would otherwise show page content to anyone.
add_filter( 'rest_authentication_errors', function ( $result ) {
	if ( null === $result && rb_site_is_private() && ! is_user_logged_in() && ! rb_has_preview_access() ) {
		return new WP_Error( 'rb_private', 'This site is private for now.', [ 'status' => 401 ] );
	}
	return $result;
} );

// Make a preview link the first time the site goes private, or when "Make a new preview link" is on.
add_action( 'acf/save_post', function ( $post_id ) {
	if ( 'options' !== $post_id || ! rb_site_is_private() ) {
		return;
	}
	if ( ! rb_preview_key() || rb_opt( 'preview_new' ) ) {
		update_option( 'rb_preview_key', wp_generate_password( 24, false ), false );
		update_option( 'rb_preview_created', time(), false );
		update_field( 'field_rb_opt_preview_new', 0, 'option' );
	}
}, 20 );

// Show the preview link, and when it runs out, in Site settings.
add_filter( 'acf/load_field/key=field_rb_opt_preview_info', function ( $field ) {
	if ( ! rb_preview_key() ) {
		$field['message'] = '<p>Save these settings with the site set to Private to create a preview link.</p>';
		return $field;
	}
	$expires = rb_preview_expires();
	if ( 0 === $expires ) {
		$when = 'It works until you make a new link.';
	} elseif ( $expires > time() ) {
		$when = 'It works until ' . esc_html( wp_date( 'j F Y, g:ia', $expires ) ) . '.';
	} else {
		$when = '<strong>This link has expired.</strong> Turn on "Make a new preview link" and save, or choose a longer time.';
	}
	$field['message'] = '<p>Send this to anyone who needs to see the site without logging in. Opening it once lets them browse the whole site on that device.</p>'
		. '<p><input type="text" readonly value="' . esc_attr( rb_preview_url() ) . '" style="width:100%;font-family:monospace" onclick="this.select()"></p>'
		. '<p>' . $when . '</p>';
	return $field;
} );

// A reminder in the admin bar while the site is private.
add_action( 'admin_bar_menu', function ( $bar ) {
	if ( ! rb_site_is_private() || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$bar->add_node( [
		'id'    => 'rb-private',
		'title' => '🔒 Site is private',
		'href'  => admin_url( 'admin.php?page=rb-settings' ),
		'meta'  => [ 'title' => 'Only logged-in users and people with the preview link can see the site. Change this in Site settings → Private site.' ],
	] );
}, 100 );
