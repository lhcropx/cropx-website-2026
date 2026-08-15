<?php
/**
 * 2-Column with Zoho Contact Form block — front-end render.
 *
 * Layout is a direct copy of cropx/contact-form's two-column design (intro
 * text + optional contact channels left, form card right, same topo-overlay
 * treatment on a deep-blue section) — see that block for the layout
 * precedent. The only real difference is the form itself: instead of
 * posting to CropX's own /wp-json/cropx/v1/contact REST endpoint, this
 * form POSTs directly to a Zoho Forms endpoint, using the field set from a
 * Zoho "Export as HTML & CSS" download (see cropx/zoho-form, which is the
 * single-column version of this same Zoho-backed form).
 *
 * IMPORTANT — field names are NOT decorative. Name_First, Name_Last, Email,
 * Dropdown1 (Country), Dropdown2 (Role), PhoneNumber_countrycode, MultiLine,
 * Dropdown (source), and DecisionBox are the exact field identifiers Zoho's
 * backend expects. Renaming or removing any of them will make that field's
 * submitted value disappear silently on Zoho's side. If fields are
 * added/removed/renamed on the Zoho form itself, re-export it and update
 * this file's field names (and cropx_zoho_country_list() in
 * inc/helpers.php, if the Country list itself changed) to match — label
 * text and the intro column are free to edit any time. Role (Dropdown2)
 * and Country (Dropdown1) are both real Zoho Dropdown fields, so their
 * <option> values must match Zoho's exactly too.
 *
 * Because the form posts to Zoho's cross-origin endpoint, this block can't
 * read back whether the submission actually succeeded — see view.js, which
 * only runs required-field/email validation before allowing that
 * navigation. Set redirectUrl to send visitors to a CropX thank-you page
 * after a successful submit; leave blank to land on Zoho's own default
 * response.
 *
 * bgColor   'deep-blue' | 'white' | 'taupe'   — section background
 * cardColor 'white'     | 'deep-blue'          — form card background
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ── Attributes ────────────────────────────────────────────────────────────────
$bg_color     = $attributes['bgColor']     ?? 'deep-blue';
$card_color   = $attributes['cardColor']   ?? 'white';
$show_eyebrow = (bool) ( $attributes['showEyebrow'] ?? true );
$eyebrow      = $attributes['eyebrow']     ?? '';
$heading      = $attributes['heading']     ?? '';
$intro_text   = $attributes['introText']   ?? '';
$show_contact = (bool) ( $attributes['showContactDetails'] ?? false );
$contact_email   = $attributes['contactEmail']   ?? '';
$contact_phone   = $attributes['contactPhone']   ?? '';
$contact_address = $attributes['contactAddress'] ?? '';
$submit_label  = $attributes['submitLabel']  ?? 'Submit';
$action_url    = $attributes['formActionUrl'] ?? '';
$redirect_url  = $attributes['redirectUrl']  ?? '';
$referrer_name = $attributes['referrerName'] ?? '';
$privacy_url   = $attributes['privacyUrl']   ?? 'https://cropx.com/privacy-policy/';
$terms_url     = $attributes['termsUrl']     ?? '';

// Validate enums.
if ( ! in_array( $bg_color, array( 'deep-blue', 'white', 'taupe' ), true ) ) {
	$bg_color = 'deep-blue';
}
if ( ! in_array( $card_color, array( 'white', 'deep-blue' ), true ) ) {
	$card_color = 'white';
}

// $block_id is also used to namespace the form field id/for attributes below
// (unrelated to the topo overlay) — keep it regardless of bgColor.
$block_id = wp_unique_id( 'zcf-' );

// ── Topo drift injection ──────────────────────────────────────────────────────
// Inject the pattern's real asset URL via a CSS custom property instead of
// a relative url() in style.css (webpack would base64-inline the ~90KB SVG).
// edit.js sets the same property for the editor preview. Same technique as
// two-column-video.

// ── Section + wrapper ─────────────────────────────────────────────────────────
$section_class = 'zcf-section zcf-section--bg-' . $bg_color;

$wrapper_extra = array();
if ( 'deep-blue' === $bg_color ) {
	$wrapper_extra['style'] = '--zcf-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');';
}

$wrapper_attrs = get_block_wrapper_attributes( array_merge(
	array( 'class' => $section_class ),
	$wrapper_extra
) );

$card_class = 'zcf-card zcf-card--' . $card_color;

// Allowed HTML for intro body — includes <p> so wpautop() output passes through.
$allowed_inline = array(
	'p'      => array(),
	'a'      => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);

// ── Channel icons (inline SVG) ─────────────────────────────────────────────────
$icon_email = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>';
$icon_phone = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8a19.79 19.79 0 01-3.07-8.68A2 2 0 012.18 1h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.91 8.1a16 16 0 006 6l1.46-1.46a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 14.92z"/></svg>';
$icon_addr  = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>';

?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput ?>>

	<div class="section-inner zcf-inner">

		<!-- ── Intro column ──────────────────────────────────────────────── -->
		<div class="zcf-intro">

			<?php if ( $show_eyebrow && $eyebrow ) : ?>
				<span class="section-eyebrow zcf-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
			<?php endif; ?>

			<h2 class="section-heading zcf-heading"><?php echo esc_html( $heading ); ?></h2>

			<?php if ( $intro_text ) : ?>
				<div class="section-body zcf-intro-body"><?php echo wp_kses( wpautop( $intro_text ), $allowed_inline ); ?></div>
			<?php endif; ?>

			<?php
			$has_channels = $show_contact && ( $contact_email || $contact_phone || $contact_address );
			if ( $has_channels ) : ?>
				<div class="zcf-channels">

					<?php if ( $contact_email ) : ?>
						<div class="zcf-channel">
							<div class="zcf-ch-icon"><?php echo $icon_email; // phpcs:ignore ?></div>
							<div class="zcf-ch-text">
								<span class="zcf-ch-label"><?php esc_html_e( 'Email', 'cropx' ); ?></span>
								<a class="zcf-ch-value" href="mailto:<?php echo esc_attr( $contact_email ); ?>"><?php echo esc_html( $contact_email ); ?></a>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $contact_phone ) : ?>
						<div class="zcf-channel">
							<div class="zcf-ch-icon"><?php echo $icon_phone; // phpcs:ignore ?></div>
							<div class="zcf-ch-text">
								<span class="zcf-ch-label"><?php esc_html_e( 'Phone', 'cropx' ); ?></span>
								<a class="zcf-ch-value" href="tel:<?php echo esc_attr( preg_replace( '/[^+\d]/', '', $contact_phone ) ); ?>"><?php echo esc_html( $contact_phone ); ?></a>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $contact_address ) : ?>
						<div class="zcf-channel">
							<div class="zcf-ch-icon"><?php echo $icon_addr; // phpcs:ignore ?></div>
							<div class="zcf-ch-text">
								<span class="zcf-ch-label"><?php esc_html_e( 'Address', 'cropx' ); ?></span>
								<span class="zcf-ch-value"><?php echo nl2br( esc_html( $contact_address ) ); ?></span>
							</div>
						</div>
					<?php endif; ?>

				</div><!-- .zcf-channels -->
			<?php endif; ?>

		</div><!-- .zcf-intro -->

		<!-- ── Form card ─────────────────────────────────────────────────── -->
		<div class="<?php echo esc_attr( $card_class ); ?>">
			<form
				class="zcf-form"
				action="<?php echo esc_url( $action_url ); ?>"
				method="POST"
				enctype="multipart/form-data"
				accept-charset="UTF-8"
				novalidate
			>
				<input type="hidden" name="zf_referrer_name" value="<?php echo esc_attr( $referrer_name ); ?>">
				<input type="hidden" name="zf_redirect_url" value="<?php echo esc_attr( $redirect_url ); ?>">
				<input type="hidden" name="zc_gad" value="">

				<!-- Name row -->
				<div class="zcf-row">
					<div class="zcf-field">
						<label class="zcf-label" for="zcf-first-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'First Name', 'cropx' ); ?>
						</label>
						<input
							class="zcf-input"
							type="text"
							id="zcf-first-<?php echo esc_attr( $block_id ); ?>"
							name="Name_First"
							maxlength="255"
							autocomplete="given-name"
						>
					</div>
					<div class="zcf-field">
						<label class="zcf-label" for="zcf-last-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'Last Name', 'cropx' ); ?>
						</label>
						<input
							class="zcf-input"
							type="text"
							id="zcf-last-<?php echo esc_attr( $block_id ); ?>"
							name="Name_Last"
							maxlength="255"
							autocomplete="family-name"
						>
					</div>
				</div>

				<!-- Email + Country row -->
				<div class="zcf-row">
					<div class="zcf-field">
						<label class="zcf-label" for="zcf-email-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'Email', 'cropx' ); ?> <span class="zcf-req" aria-hidden="true">*</span>
						</label>
						<input
							class="zcf-input"
							type="email"
							id="zcf-email-<?php echo esc_attr( $block_id ); ?>"
							name="Email"
							maxlength="255"
							required
							autocomplete="email"
						>
					</div>
					<div class="zcf-field">
						<label class="zcf-label" for="zcf-country-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'Country', 'cropx' ); ?>
						</label>
						<select class="zcf-select" id="zcf-country-<?php echo esc_attr( $block_id ); ?>" name="Dropdown1">
							<option selected value="-Select-"><?php esc_html_e( '-Select-', 'cropx' ); ?></option>
							<?php foreach ( cropx_zoho_country_list() as $country ) : ?>
								<option value="<?php echo esc_attr( $country ); ?>"><?php echo esc_html( $country ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<!-- Role + Phone row -->
				<div class="zcf-row">
					<div class="zcf-field">
						<label class="zcf-label" for="zcf-role-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'What is your role?', 'cropx' ); ?> <span class="zcf-req" aria-hidden="true">*</span>
						</label>
						<select class="zcf-select" id="zcf-role-<?php echo esc_attr( $block_id ); ?>" name="Dropdown2" required>
							<option selected value="-Select-"><?php esc_html_e( '-Select-', 'cropx' ); ?></option>
							<option value="Farmer, grower, or producer of any scale"><?php esc_html_e( 'Farmer, grower, or producer of any scale', 'cropx' ); ?></option>
							<option value="Dealer, advisor, or ag consultant"><?php esc_html_e( 'Dealer, advisor, or ag consultant', 'cropx' ); ?></option>
							<option value="Large corp, retailer, cooperative, or other agribusiness"><?php esc_html_e( 'Large corp, retailer, cooperative, or other agribusiness', 'cropx' ); ?></option>
						</select>
					</div>
					<div class="zcf-field">
						<label class="zcf-label" for="zcf-phone-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'Phone', 'cropx' ); ?>
						</label>
						<input
							class="zcf-input"
							type="tel"
							id="zcf-phone-<?php echo esc_attr( $block_id ); ?>"
							name="PhoneNumber_countrycode"
							maxlength="20"
							autocomplete="tel"
						>
					</div>
				</div>

				<!-- Message -->
				<div class="zcf-field">
					<label class="zcf-label" for="zcf-message-<?php echo esc_attr( $block_id ); ?>">
						<?php esc_html_e( 'How can we help?', 'cropx' ); ?>
					</label>
					<textarea
						class="zcf-textarea"
						id="zcf-message-<?php echo esc_attr( $block_id ); ?>"
						name="MultiLine"
						maxlength="65535"
						rows="4"
					></textarea>
				</div>

				<!-- Source -->
				<div class="zcf-field">
					<label class="zcf-label" for="zcf-source-<?php echo esc_attr( $block_id ); ?>">
						<?php esc_html_e( 'How did you hear about us?', 'cropx' ); ?>
					</label>
					<select class="zcf-select" id="zcf-source-<?php echo esc_attr( $block_id ); ?>" name="Dropdown">
						<option selected value="-Select-"><?php esc_html_e( '-Select-', 'cropx' ); ?></option>
						<option value="Online search"><?php esc_html_e( 'Online search', 'cropx' ); ?></option>
						<option value="Social media (LinkedIn, X, Facebook, Instagram)"><?php esc_html_e( 'Social media (LinkedIn, X, Facebook, Instagram)', 'cropx' ); ?></option>
						<option value="Event (trade show, field day, conference)"><?php esc_html_e( 'Event (trade show, field day, conference)', 'cropx' ); ?></option>
						<option value="Referral"><?php esc_html_e( 'Referral', 'cropx' ); ?></option>
						<option value="Ad (radio, podcast, newsletter)"><?php esc_html_e( 'Ad (radio, podcast, newsletter)', 'cropx' ); ?></option>
					</select>
				</div>

				<!-- Marketing / SMS consent -->
				<div class="zcf-consent">
					<label class="zcf-checkbox-wrap">
						<input class="zcf-checkbox" type="checkbox" id="zcf-consent-<?php echo esc_attr( $block_id ); ?>" name="DecisionBox">
						<span class="zcf-checkbox-label"><?php esc_html_e( 'Agree to receive marketing messages', 'cropx' ); ?></span>
					</label>
					<p class="zcf-legal">
						<?php esc_html_e( 'By checking above box, you agree to receive automated promotional marketing text messages from CropX about its services. Message and data rates may apply. You may reply STOP to opt-out at any time. For assistance, text HELP or visit our website at', 'cropx' ); ?>
						<a href="http://www.cropx.com/" target="_blank" rel="noopener noreferrer">www.cropx.com</a>.
						<?php esc_html_e( 'Message frequency will vary.', 'cropx' ); ?>
						<br>
						<a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Review our Privacy Policy', 'cropx' ); ?></a>
						<?php if ( $terms_url ) : ?>
							&nbsp;·&nbsp;
							<a href="<?php echo esc_url( $terms_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Review our Terms and Conditions', 'cropx' ); ?></a>
						<?php endif; ?>
					</p>
				</div>

				<!-- Submit -->
				<div class="zcf-submit-row">
					<button class="zcf-submit btn-primary" type="submit">
						<?php echo esc_html( $submit_label ); ?>
					</button>
				</div>

			</form>
		</div><!-- .zcf-card -->

	</div><!-- .section-inner -->
</section>
