<?php
/**
 * Built-in quote/contact form.
 * Sends through Resend when an API key is set (RB_RESEND_API_KEY constant or Site settings),
 * otherwise through wp_mail().
 */

defined( 'ABSPATH' ) || exit;

function rb_form_options() {
	return [
		'client_type' => [ 'University', 'School or college', 'Business / exhibitor', 'Other' ],
		'service'     => [ 'Full Service', 'Delivery Only', 'Build Partner', 'Storage', 'Courier', 'Removals', 'Not sure yet' ],
	];
}

add_action( 'admin_post_nopriv_rb_contact', 'rb_handle_contact' );
add_action( 'admin_post_rb_contact', 'rb_handle_contact' );

function rb_handle_contact() {
	$back = wp_get_referer() ?: home_url( '/' );
	$back = remove_query_arg( [ 'rb_sent', 'rb_error' ], $back );

	if ( ! isset( $_POST['rb_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rb_nonce'] ) ), 'rb_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'rb_error', 'expired', $back ) . '#quote-form' );
		exit;
	}
	// Honeypot: bots fill every field.
	if ( ! empty( $_POST['website'] ) ) {
		wp_safe_redirect( add_query_arg( 'rb_sent', '1', $back ) . '#quote-form' );
		exit;
	}

	$fields = [
		'name'         => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
		'organisation' => sanitize_text_field( wp_unslash( $_POST['organisation'] ?? '' ) ),
		'email'        => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
		'phone'        => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
		'client_type'  => sanitize_text_field( wp_unslash( $_POST['client_type'] ?? '' ) ),
		'service'      => sanitize_text_field( wp_unslash( $_POST['service'] ?? '' ) ),
		'event'        => sanitize_text_field( wp_unslash( $_POST['event'] ?? '' ) ),
		'dates'        => sanitize_text_field( wp_unslash( $_POST['dates'] ?? '' ) ),
		'message'      => sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) ),
	];

	if ( ! $fields['name'] || ! is_email( $fields['email'] ) || ! $fields['message'] ) {
		wp_safe_redirect( add_query_arg( 'rb_error', 'missing', $back ) . '#quote-form' );
		exit;
	}

	$labels = [
		'name' => 'Name', 'organisation' => 'Organisation', 'email' => 'Email', 'phone' => 'Phone',
		'client_type' => 'Client type', 'service' => 'Service', 'event' => 'Event & venue', 'dates' => 'Event date(s)', 'message' => 'Message',
	];
	$text = '';
	$rows = '';
	foreach ( $labels as $k => $label ) {
		if ( '' === $fields[ $k ] ) {
			continue;
		}
		$text .= $label . ': ' . $fields[ $k ] . "\n";
		$rows .= '<tr><td style="padding:6px 12px 6px 0;color:#5b6b7a;vertical-align:top;white-space:nowrap">' . esc_html( $label ) . '</td><td style="padding:6px 0">' . nl2br( esc_html( $fields[ $k ] ) ) . '</td></tr>';
	}
	$html    = '<div style="font-family:Arial,sans-serif;font-size:15px;color:#0f273c"><h2 style="margin:0 0 12px">New enquiry from the website</h2><table>' . $rows . '</table></div>';
	$subject = 'Website enquiry: ' . $fields['name'] . ( $fields['organisation'] ? ' (' . $fields['organisation'] . ')' : '' );

	$to   = rb_opt( 'form_to', get_option( 'admin_email' ) );
	$from = rb_opt( 'form_from', 'Rad Brothers Website <contact@radbrothers.co.uk>' );
	$key  = defined( 'RB_RESEND_API_KEY' ) ? RB_RESEND_API_KEY : rb_opt( 'resend_key' );

	$ok = false;
	if ( $key ) {
		$res = wp_remote_post( 'https://api.resend.com/emails', [
			'timeout' => 15,
			'headers' => [ 'Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json' ],
			'body'    => wp_json_encode( [
				'from'     => $from,
				'to'       => [ $to ],
				'reply_to' => $fields['email'],
				'subject'  => $subject,
				'html'     => $html,
				'text'     => $text,
			] ),
		] );
		$code = is_wp_error( $res ) ? 0 : wp_remote_retrieve_response_code( $res );
		$ok   = $code >= 200 && $code < 300;
		if ( ! $ok ) {
			error_log( 'Rad Brothers form: Resend failed ' . ( is_wp_error( $res ) ? $res->get_error_message() : wp_remote_retrieve_body( $res ) ) );
		}
	} else {
		$ok = wp_mail( $to, $subject, $html, [ 'Content-Type: text/html; charset=UTF-8', 'Reply-To: ' . $fields['name'] . ' <' . $fields['email'] . '>' ] );
	}

	wp_safe_redirect( add_query_arg( $ok ? 'rb_sent' : 'rb_error', $ok ? '1' : 'send', $back ) . '#quote-form' );
	exit;
}

/** Render the built-in form. $variant: full | short */
function rb_contact_form( $variant = 'full' ) {
	$opts = rb_form_options();
	ob_start();
	if ( isset( $_GET['rb_sent'] ) ) : ?>
		<div class="form-notice form-notice--ok" role="status"><?php echo rb_icon( 'check' ); ?><div><strong>Thanks, we have your enquiry.</strong> <?php echo esc_html( rb_opt( 'response_time', 'We reply within one working day.' ) ); ?></div></div>
	<?php elseif ( isset( $_GET['rb_error'] ) ) : ?>
		<div class="form-notice form-notice--error" role="alert"><div><strong>Sorry, that did not send.</strong> Please check the required fields and try again, or call us on <?php echo esc_html( rb_opt( 'phone', '' ) ); ?>.</div></div>
	<?php endif; ?>
	<form class="quote-form quote-form--<?php echo esc_attr( $variant ); ?>" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<input type="hidden" name="action" value="rb_contact">
		<?php wp_nonce_field( 'rb_contact', 'rb_nonce' ); ?>
		<div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
		<div class="field"><label for="rb-name">Your name <span>*</span></label><input id="rb-name" name="name" type="text" autocomplete="name" required></div>
		<div class="field"><label for="rb-org">Organisation</label><input id="rb-org" name="organisation" type="text" autocomplete="organization"></div>
		<div class="field"><label for="rb-email">Email <span>*</span></label><input id="rb-email" name="email" type="email" autocomplete="email" required></div>
		<div class="field"><label for="rb-phone">Phone</label><input id="rb-phone" name="phone" type="tel" autocomplete="tel"></div>
		<?php if ( 'full' === $variant ) : ?>
			<fieldset class="field field--full chips">
				<legend>I'm enquiring for a…</legend>
				<?php foreach ( $opts['client_type'] as $i => $o ) : ?>
					<label class="chip"><input type="radio" name="client_type" value="<?php echo esc_attr( $o ); ?>" <?php checked( 0, $i ); ?>><span><?php echo esc_html( $o ); ?></span></label>
				<?php endforeach; ?>
			</fieldset>
			<div class="field"><label for="rb-service">Service needed</label>
				<select id="rb-service" name="service">
					<?php foreach ( $opts['service'] as $o ) : ?><option><?php echo esc_html( $o ); ?></option><?php endforeach; ?>
				</select>
			</div>
			<div class="field"><label for="rb-dates">Event date(s)</label><input id="rb-dates" name="dates" type="text" placeholder="e.g. 14–16 March"></div>
			<div class="field field--full"><label for="rb-event">Event name and venue</label><input id="rb-event" name="event" type="text" placeholder="e.g. Spring careers fair, NEC Birmingham"></div>
		<?php endif; ?>
		<div class="field field--full"><label for="rb-msg">How can we help? <span>*</span></label><textarea id="rb-msg" name="message" rows="<?php echo 'full' === $variant ? 5 : 4; ?>" required></textarea></div>
		<div class="field field--full form-foot">
			<button class="btn btn--primary" type="submit"><span>Send enquiry</span><?php echo rb_icon( 'arrow', 'btn__icon' ); ?></button>
			<p class="form-small"><?php echo esc_html( rb_opt( 'response_time', 'We reply within one working day.' ) ); ?></p>
		</div>
	</form>
	<?php
	return ob_get_clean();
}
