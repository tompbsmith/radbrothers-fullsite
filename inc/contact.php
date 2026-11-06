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
		'service'     => [ 'Full Service', 'Delivery Only', 'Design & Print', 'Storage', 'Client portal demo', 'Courier', 'Removals', 'Not sure yet' ],
	];
}

/** Services that don't involve a specific event, so the event fields are hidden. */
function rb_form_no_event_services() {
	return [ 'Client portal demo', 'Not sure yet' ];
}

add_action( 'admin_post_nopriv_rb_contact', 'rb_handle_contact' );
add_action( 'admin_post_rb_contact', 'rb_handle_contact' );

/** Field labels, used for emails and error messages. */
function rb_form_labels() {
	return [
		'name' => 'Name', 'organisation' => 'Organisation', 'email' => 'Email', 'phone' => 'Phone',
		'client_type' => 'Client type', 'service' => 'Service', 'event' => 'Event & venue', 'dates' => 'Event date(s)', 'message' => 'Message',
	];
}

/**
 * Send the visitor back to the form with an error, keeping what they typed
 * (held for 10 minutes, then discarded) so they don't have to start again.
 */
function rb_form_fail( $back, $error, $fields = [], $missing = [] ) {
	$token = wp_generate_password( 12, false );
	set_transient( 'rb_form_' . $token, [ 'fields' => $fields, 'missing' => $missing ], 10 * MINUTE_IN_SECONDS );
	wp_safe_redirect( add_query_arg( [ 'rb_error' => $error, 'rb_f' => $token ], $back ) . '#quote-form' );
	exit;
}

/** What was typed before a failed send, and which fields were missing (read once). */
function rb_form_state() {
	static $state = null;
	if ( null !== $state ) {
		return $state;
	}
	$state = [ 'fields' => [], 'missing' => [] ];
	$token = isset( $_GET['rb_f'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', (string) wp_unslash( $_GET['rb_f'] ) ) : '';
	if ( $token ) {
		$saved = get_transient( 'rb_form_' . $token );
		if ( is_array( $saved ) ) {
			$state = array_merge( $state, $saved );
			delete_transient( 'rb_form_' . $token );
		}
	}
	return $state;
}

function rb_handle_contact() {
	$back = wp_get_referer() ?: home_url( '/' );
	$back = remove_query_arg( [ 'rb_sent', 'rb_error', 'rb_f' ], $back );

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

	// No event details for services that aren't tied to an event.
	if ( in_array( $fields['service'], rb_form_no_event_services(), true ) ) {
		$fields['event'] = '';
		$fields['dates'] = '';
	}

	if ( ! isset( $_POST['rb_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rb_nonce'] ) ), 'rb_contact' ) ) {
		rb_form_fail( $back, 'expired', $fields );
	}

	$missing = [];
	if ( ! $fields['name'] ) {
		$missing[] = 'name';
	}
	if ( ! is_email( $fields['email'] ) ) {
		$missing[] = 'email';
	}
	if ( ! $fields['message'] ) {
		$missing[] = 'message';
	}
	if ( $missing ) {
		rb_form_fail( $back, 'missing', $fields, $missing );
	}

	$labels = rb_form_labels();
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

	// Test mode: behave exactly as if it sent, but email nobody.
	if ( rb_opt( 'form_test_mode' ) ) {
		wp_safe_redirect( add_query_arg( 'rb_sent', 'test', $back ) . '#quote-form' );
		exit;
	}

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

	if ( ! $ok ) {
		rb_form_fail( $back, 'send', $fields );
	}
	wp_safe_redirect( add_query_arg( 'rb_sent', '1', $back ) . '#quote-form' );
	exit;
}

/** Render the built-in form. $variant: full | short */
function rb_contact_form( $variant = 'full' ) {
	$opts    = rb_form_options();
	$state   = rb_form_state();
	$v       = $state['fields'];
	$missing = $state['missing'];
	$error   = isset( $_GET['rb_error'] ) ? sanitize_key( wp_unslash( $_GET['rb_error'] ) ) : '';
	$phone   = rb_opt( 'phone', '' );
	$val     = function ( $k ) use ( $v ) { return isset( $v[ $k ] ) ? (string) $v[ $k ] : ''; };
	$invalid = function ( $k ) use ( $missing ) {
		return in_array( $k, $missing, true ) ? ' aria-invalid="true" aria-describedby="rb-' . esc_attr( $k ) . '-error"' : '';
	};
	$field_error = function ( $k, $text ) use ( $missing ) {
		return in_array( $k, $missing, true ) ? '<span class="field-error" id="rb-' . esc_attr( $k ) . '-error">' . esc_html( $text ) . '</span>' : '';
	};
	$ids    = [ 'name' => 'rb-name', 'email' => 'rb-email', 'message' => 'rb-msg' ];
	$labels = rb_form_labels();

	// Client type: nothing chosen by default, except on the trade shows page.
	$type = $val( 'client_type' );
	if ( ! $type && is_page() && false !== strpos( (string) get_post_field( 'post_name', get_queried_object_id() ), 'trade' ) ) {
		$type = 'Business / exhibitor';
	}

	ob_start();
	if ( isset( $_GET['rb_sent'] ) && 'test' === $_GET['rb_sent'] ) : ?>
		<div class="form-notice form-notice--ok" role="status"><?php echo rb_icon( 'check' ); ?><div><strong>The form worked.</strong> The site is in test mode, so this enquiry wasn't sent to anyone.</div></div>
	<?php elseif ( isset( $_GET['rb_sent'] ) ) : ?>
		<div class="form-notice form-notice--ok" role="status"><?php echo rb_icon( 'check' ); ?><div><strong>Thanks, we have your enquiry.</strong> <?php echo esc_html( rb_opt( 'response_time', 'We reply within one working day.' ) ); ?></div></div>
	<?php elseif ( $error ) : ?>
		<div class="form-notice form-notice--error" role="alert"><div>
			<?php if ( 'missing' === $error && $missing ) : ?>
				<strong>Please check <?php echo 1 === count( $missing ) ? 'this field' : 'these fields'; ?>:</strong>
				<?php
				$links = array_map( function ( $k ) use ( $ids, $labels ) {
					return '<a href="#' . esc_attr( $ids[ $k ] ?? '' ) . '">' . esc_html( $labels[ $k ] ?? $k ) . '</a>';
				}, $missing );
				echo wp_kses_post( implode( ', ', $links ) ) . '.';
				?>
				Everything else you typed is still here.
			<?php elseif ( 'expired' === $error ) : ?>
				<strong>This page was open for a while, so please press send again.</strong> Everything you typed is still here.
			<?php else : ?>
				<strong>Sorry, that did not send.</strong> Please try again<?php echo $phone ? ', or call us on ' . esc_html( $phone ) : ''; ?>.
			<?php endif; ?>
		</div></div>
	<?php endif; ?>
	<form class="quote-form quote-form--<?php echo esc_attr( $variant ); ?>" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<input type="hidden" name="action" value="rb_contact">
		<?php wp_nonce_field( 'rb_contact', 'rb_nonce' ); ?>
		<p class="form-required-note">Fields marked <span aria-hidden="true">*</span> are required.</p>
		<div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
		<div class="field"><label for="rb-name">Your name <span aria-hidden="true">*</span></label><input id="rb-name" name="name" type="text" autocomplete="name" required value="<?php echo esc_attr( $val( 'name' ) ); ?>"<?php echo $invalid( 'name' ); ?>><?php echo $field_error( 'name', 'Please add your name.' ); ?></div>
		<div class="field"><label for="rb-org">Organisation</label><input id="rb-org" name="organisation" type="text" autocomplete="organization" value="<?php echo esc_attr( $val( 'organisation' ) ); ?>"></div>
		<div class="field"><label for="rb-email">Email <span aria-hidden="true">*</span></label><input id="rb-email" name="email" type="email" autocomplete="email" required value="<?php echo esc_attr( $val( 'email' ) ); ?>"<?php echo $invalid( 'email' ); ?>><?php echo $field_error( 'email', 'Please add an email address we can reply to, like name@example.com.' ); ?></div>
		<div class="field"><label for="rb-phone">Phone</label><input id="rb-phone" name="phone" type="tel" autocomplete="tel" value="<?php echo esc_attr( $val( 'phone' ) ); ?>"></div>
		<?php if ( 'full' === $variant ) : ?>
			<fieldset class="field field--full chips">
				<legend>I'm enquiring for a…</legend>
				<?php foreach ( $opts['client_type'] as $o ) : ?>
					<label class="chip"><input type="radio" name="client_type" value="<?php echo esc_attr( $o ); ?>" <?php checked( $type, $o ); ?>><span><?php echo esc_html( $o ); ?></span></label>
				<?php endforeach; ?>
			</fieldset>
			<div class="field"><label for="rb-service">Service needed</label>
				<select id="rb-service" name="service" data-no-event="<?php echo esc_attr( wp_json_encode( rb_form_no_event_services() ) ); ?>">
					<?php
					// Pre-select from ?service=portal (or any option name) so "Book a demo" buttons land ready to go.
					$want = isset( $_GET['service'] ) ? strtolower( sanitize_text_field( wp_unslash( $_GET['service'] ) ) ) : '';
					foreach ( $opts['service'] as $o ) :
						$sel = $val( 'service' ) ? $val( 'service' ) === $o : ( $want && false !== strpos( strtolower( $o ), $want ) );
						?>
						<option<?php echo $sel ? ' selected' : ''; ?>><?php echo esc_html( $o ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="field" data-event-field><label for="rb-dates">Event date(s)</label><input id="rb-dates" name="dates" type="text" placeholder="e.g. 14–16 March" value="<?php echo esc_attr( $val( 'dates' ) ); ?>"></div>
			<div class="field field--full" data-event-field><label for="rb-event">Event name and venue</label><input id="rb-event" name="event" type="text" placeholder="e.g. Spring careers fair, NEC Birmingham" value="<?php echo esc_attr( $val( 'event' ) ); ?>"></div>
		<?php endif; ?>
		<div class="field field--full"><label for="rb-msg">How can we help? <span aria-hidden="true">*</span></label><textarea id="rb-msg" name="message" rows="<?php echo 'full' === $variant ? 5 : 4; ?>" required<?php echo $invalid( 'message' ); ?>><?php echo esc_textarea( $val( 'message' ) ); ?></textarea><?php echo $field_error( 'message', 'Please tell us a little about what you need.' ); ?></div>
		<div class="field field--full form-foot">
			<button class="btn btn--primary" type="submit"><span>Send enquiry</span><?php echo rb_icon( 'arrow', 'btn__icon' ); ?></button>
			<p class="form-small"><?php echo esc_html( rb_opt( 'response_time', 'We reply within one working day.' ) ); ?></p>
		</div>
	</form>
	<?php
	return ob_get_clean();
}
