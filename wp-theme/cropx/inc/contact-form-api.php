<?php
/**
 * Contact Form REST API endpoint.
 *
 * Route:  POST /wp-json/cropx/v1/contact
 * Auth:   WordPress nonce (X-WP-Nonce header, action 'cropx_contact_form')
 * Action: Validates and sanitizes fields, then sends an email via wp_mail().
 *
 * Required fields: first_name, last_name, email, country, role
 * Optional fields: phone, message, source, marketing_consent, notify_email
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'rest_api_init', function () {
	register_rest_route( 'cropx/v1', '/contact', array(
		'methods'             => 'POST',
		'callback'            => 'cropx_handle_contact_form',
		'permission_callback' => 'cropx_contact_form_permission',
	) );
} );

/**
 * Verify the nonce submitted with the form.
 * We accept requests from non-logged-in visitors, so we use a custom nonce
 * action rather than requiring a logged-in capability.
 */
function cropx_contact_form_permission( WP_REST_Request $request ) {
	$nonce = $request->get_header( 'X-WP-Nonce' );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'cropx_contact_form' ) ) {
		return new WP_Error(
			'invalid_nonce',
			__( 'Invalid security token. Please reload the page and try again.', 'cropx' ),
			array( 'status' => 403 )
		);
	}
	return true;
}

/**
 * Process the contact form submission.
 */
function cropx_handle_contact_form( WP_REST_Request $request ) {
	// ── Sanitize inputs ───────────────────────────────────────────────────────
	$first_name         = sanitize_text_field( $request->get_param( 'first_name' )         ?? '' );
	$last_name          = sanitize_text_field( $request->get_param( 'last_name' )          ?? '' );
	$email              = sanitize_email(      $request->get_param( 'email' )              ?? '' );
	$country            = sanitize_text_field( $request->get_param( 'country' )            ?? '' );
	$role               = sanitize_text_field( $request->get_param( 'role' )               ?? '' );
	$phone              = sanitize_text_field( $request->get_param( 'phone' )              ?? '' );
	$message            = sanitize_textarea_field( $request->get_param( 'message' )       ?? '' );
	$source             = sanitize_text_field( $request->get_param( 'source' )             ?? '' );
	$marketing_consent  = (bool) $request->get_param( 'marketing_consent' );
	$notify_email_raw   = sanitize_email( $request->get_param( 'notify_email' ) ?? '' );

	// ── Validate required fields ──────────────────────────────────────────────
	$errors = array();

	if ( empty( $first_name ) ) {
		$errors[] = __( 'First name is required.', 'cropx' );
	}
	if ( empty( $last_name ) ) {
		$errors[] = __( 'Last name is required.', 'cropx' );
	}
	if ( empty( $email ) || ! is_email( $email ) ) {
		$errors[] = __( 'A valid email address is required.', 'cropx' );
	}
	if ( empty( $country ) ) {
		$errors[] = __( 'Please select your country.', 'cropx' );
	}
	if ( empty( $role ) ) {
		$errors[] = __( 'Please select your role.', 'cropx' );
	}

	if ( ! empty( $errors ) ) {
		return new WP_REST_Response(
			array(
				'success' => false,
				'message' => implode( ' ', $errors ),
			),
			422
		);
	}

	// ── Determine notification recipient ──────────────────────────────────────
	// Use the block's configured email if set; fall back to the site admin email.
	$to = ( $notify_email_raw && is_email( $notify_email_raw ) )
		? $notify_email_raw
		: get_option( 'admin_email' );

	// ── Build email ───────────────────────────────────────────────────────────
	$site_name = get_bloginfo( 'name' );
	$subject   = sprintf(
		/* translators: %s: site name */
		__( '[%s] New contact form submission', 'cropx' ),
		$site_name
	);

	$body  = "New contact enquiry from {$site_name}\n";
	$body .= str_repeat( '-', 40 ) . "\n\n";
	$body .= "Name:      {$first_name} {$last_name}\n";
	$body .= "Email:     {$email}\n";
	$body .= "Country:   {$country}\n";
	$body .= "Role:      {$role}\n";

	if ( $phone ) {
		$body .= "Phone:     {$phone}\n";
	}
	if ( $source ) {
		$body .= "Source:    {$source}\n";
	}

	$body .= "\nMarketing consent: " . ( $marketing_consent ? 'Yes' : 'No' ) . "\n";

	if ( $message ) {
		$body .= "\nMessage:\n" . str_repeat( '-', 20 ) . "\n{$message}\n";
	}

	$body .= "\n" . str_repeat( '-', 40 ) . "\n";
	$body .= 'Submitted: ' . current_time( 'Y-m-d H:i:s' ) . " (site time)\n";
	$body .= 'IP: ' . ( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ) . "\n"; // phpcs:ignore

	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		"Reply-To: {$first_name} {$last_name} <{$email}>",
	);

	$sent = wp_mail( $to, $subject, $body, $headers );

	if ( ! $sent ) {
		return new WP_REST_Response(
			array(
				'success' => false,
				'message' => __( 'Your message could not be sent. Please try again or contact us directly.', 'cropx' ),
			),
			500
		);
	}

	return new WP_REST_Response(
		array(
			'success' => true,
			'message' => __( "Thank you — we'll be in touch shortly.", 'cropx' ),
		),
		200
	);
}
