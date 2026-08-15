<?php
/**
 * Zoho Form (Embed) block — front-end render.
 *
 * This recreates the markup Zoho Forms generates when you export a form via
 * "Share → Embed → HTML & CSS" — but restyled with CropX design tokens and
 * with the section-level chrome (bg color, card, optional heading) that our
 * other CTA blocks share. The <form> still POSTs directly to Zoho's own
 * endpoint (formActionUrl), so submissions land in Zoho Forms exactly like
 * a normal Zoho-hosted form would.
 *
 * IMPORTANT — field names are NOT decorative. Name_First, Name_Last, Email,
 * Dropdown1 (Country), Dropdown2 (Role), PhoneNumber_countrycode, MultiLine,
 * Dropdown (source), and DecisionBox are the exact field identifiers Zoho's
 * backend expects for this form. Renaming or removing any of them will make
 * that field's submitted value disappear silently on Zoho's side. If fields
 * are added/removed/renamed on the Zoho form itself, re-export it and update
 * this file's field names (and cropx_zoho_country_list() in inc/helpers.php,
 * if the Country list itself changed) to match — label text is free to edit
 * any time. Role (Dropdown2) and Country (Dropdown1) are both real Zoho
 * Dropdown fields, so their <option> values must match Zoho's exactly too —
 * see cropx_zoho_country_list() for why the Country list is copied verbatim
 * rather than a "nicer" hand-written list.
 *
 * Because the form posts directly to Zoho's cross-origin endpoint, this
 * block cannot read back whether the submission actually succeeded (the
 * browser just navigates to Zoho's response). We only validate client-side
 * (required fields + email format) before allowing that navigation — see
 * view.js. Set redirectUrl to send visitors to a CropX thank-you page after
 * a successful submit; leave blank to land on Zoho's own default response.
 *
 * bgColor   'white' | 'taupe' | 'deep-blue'  — section background
 * cardColor 'white' | 'deep-blue'             — form card background
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ── Attributes ────────────────────────────────────────────────────────────────
$bg_color      = $attributes['bgColor']      ?? 'white';
$card_color    = $attributes['cardColor']    ?? 'white';
$show_eyebrow  = (bool) ( $attributes['showEyebrow'] ?? true );
$eyebrow       = $attributes['eyebrow']      ?? '';
$heading       = $attributes['heading']      ?? '';
$intro_text    = $attributes['introText']    ?? '';
$submit_label  = $attributes['submitLabel']  ?? 'Submit';
$action_url    = $attributes['formActionUrl'] ?? '';
$redirect_url  = $attributes['redirectUrl']  ?? '';
$referrer_name = $attributes['referrerName'] ?? '';
$privacy_url   = $attributes['privacyUrl']   ?? 'https://cropx.com/privacy-policy/';
$terms_url     = $attributes['termsUrl']     ?? '';

// Validate enums.
if ( ! in_array( $bg_color, array( 'white', 'taupe', 'deep-blue' ), true ) ) {
	$bg_color = 'white';
}
if ( ! in_array( $card_color, array( 'white', 'deep-blue' ), true ) ) {
	$card_color = 'white';
}

// $block_id is also used to namespace the form field id/for attributes below
// (unrelated to the topo overlay) — keep it regardless of bgColor.
$block_id = wp_unique_id( 'zform-' );

// ── Topo drift injection (deep-blue bg only) ────────────────────────────────────
// Inject the pattern's real asset URL via a CSS custom property instead of
// a relative url() in style.css (webpack would base64-inline the ~90KB SVG).
// edit.js sets the same property for the editor preview. Same technique as
// two-column-video.

$section_class = 'zform-section zform-section--bg-' . $bg_color;
$wrapper_extra = array();
if ( 'deep-blue' === $bg_color ) {
	$wrapper_extra['style'] = '--zform-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');';
}

$wrapper_attrs = get_block_wrapper_attributes( array_merge(
	array( 'class' => $section_class ),
	$wrapper_extra
) );

$card_class   = 'zform-card zform-card--' . $card_color;

?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput ?>>

	<div class="section-inner zform-inner">

		<?php if ( ( $show_eyebrow && $eyebrow ) || $heading || $intro_text ) : ?>
			<div class="zform-header">
				<?php if ( $show_eyebrow && $eyebrow ) : ?>
					<span class="section-eyebrow zform-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h2 class="section-heading zform-heading"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>
				<?php if ( $intro_text ) : ?>
					<p class="section-body zform-intro"><?php echo esc_html( $intro_text ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="<?php echo esc_attr( $card_class ); ?>">
			<form
				class="zform-form"
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
				<div class="zform-row">
					<div class="zform-field">
						<label class="zform-label" for="zform-first-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'First Name', 'cropx' ); ?>
						</label>
						<input
							class="zform-input"
							type="text"
							id="zform-first-<?php echo esc_attr( $block_id ); ?>"
							name="Name_First"
							maxlength="255"
							autocomplete="given-name"
						>
					</div>
					<div class="zform-field">
						<label class="zform-label" for="zform-last-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'Last Name', 'cropx' ); ?>
						</label>
						<input
							class="zform-input"
							type="text"
							id="zform-last-<?php echo esc_attr( $block_id ); ?>"
							name="Name_Last"
							maxlength="255"
							autocomplete="family-name"
						>
					</div>
				</div>

				<!-- Email + Country row -->
				<div class="zform-row">
					<div class="zform-field">
						<label class="zform-label" for="zform-email-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'Email', 'cropx' ); ?> <span class="zform-req" aria-hidden="true">*</span>
						</label>
						<input
							class="zform-input"
							type="email"
							id="zform-email-<?php echo esc_attr( $block_id ); ?>"
							name="Email"
							maxlength="255"
							required
							autocomplete="email"
						>
					</div>
					<div class="zform-field">
						<label class="zform-label" for="zform-country-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'Country', 'cropx' ); ?>
						</label>
						<select class="zform-select" id="zform-country-<?php echo esc_attr( $block_id ); ?>" name="Dropdown1">
							<option selected value="-Select-"><?php esc_html_e( '-Select-', 'cropx' ); ?></option>
							<?php foreach ( cropx_zoho_country_list() as $country ) : ?>
								<option value="<?php echo esc_attr( $country ); ?>"><?php echo esc_html( $country ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<!-- Role + Phone row -->
				<div class="zform-row">
					<div class="zform-field">
						<label class="zform-label" for="zform-role-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'What is your role?', 'cropx' ); ?> <span class="zform-req" aria-hidden="true">*</span>
						</label>
						<select class="zform-select" id="zform-role-<?php echo esc_attr( $block_id ); ?>" name="Dropdown2" required>
							<option selected value="-Select-"><?php esc_html_e( '-Select-', 'cropx' ); ?></option>
							<option value="Farmer, grower, or producer of any scale"><?php esc_html_e( 'Farmer, grower, or producer of any scale', 'cropx' ); ?></option>
							<option value="Dealer, advisor, or ag consultant"><?php esc_html_e( 'Dealer, advisor, or ag consultant', 'cropx' ); ?></option>
							<option value="Large corp, retailer, cooperative, or other agribusiness"><?php esc_html_e( 'Large corp, retailer, cooperative, or other agribusiness', 'cropx' ); ?></option>
						</select>
					</div>
					<div class="zform-field">
						<label class="zform-label" for="zform-phone-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'Phone', 'cropx' ); ?>
						</label>
						<input
							class="zform-input"
							type="tel"
							id="zform-phone-<?php echo esc_attr( $block_id ); ?>"
							name="PhoneNumber_countrycode"
							maxlength="20"
							autocomplete="tel"
						>
					</div>
				</div>

				<!-- Message -->
				<div class="zform-field">
					<label class="zform-label" for="zform-message-<?php echo esc_attr( $block_id ); ?>">
						<?php esc_html_e( 'How can we help?', 'cropx' ); ?>
					</label>
					<textarea
						class="zform-textarea"
						id="zform-message-<?php echo esc_attr( $block_id ); ?>"
						name="MultiLine"
						maxlength="65535"
						rows="4"
					></textarea>
				</div>

				<!-- Source -->
				<div class="zform-field">
					<label class="zform-label" for="zform-source-<?php echo esc_attr( $block_id ); ?>">
						<?php esc_html_e( 'How did you hear about us?', 'cropx' ); ?>
					</label>
					<select class="zform-select" id="zform-source-<?php echo esc_attr( $block_id ); ?>" name="Dropdown">
						<option selected value="-Select-"><?php esc_html_e( '-Select-', 'cropx' ); ?></option>
						<option value="Online search"><?php esc_html_e( 'Online search', 'cropx' ); ?></option>
						<option value="Social media (LinkedIn, X, Facebook, Instagram)"><?php esc_html_e( 'Social media (LinkedIn, X, Facebook, Instagram)', 'cropx' ); ?></option>
						<option value="Event (trade show, field day, conference)"><?php esc_html_e( 'Event (trade show, field day, conference)', 'cropx' ); ?></option>
						<option value="Referral"><?php esc_html_e( 'Referral', 'cropx' ); ?></option>
						<option value="Ad (radio, podcast, newsletter)"><?php esc_html_e( 'Ad (radio, podcast, newsletter)', 'cropx' ); ?></option>
					</select>
				</div>

				<!-- Marketing / SMS consent -->
				<div class="zform-consent">
					<label class="zform-checkbox-wrap">
						<input class="zform-checkbox" type="checkbox" id="zform-consent-<?php echo esc_attr( $block_id ); ?>" name="DecisionBox">
						<span class="zform-checkbox-label"><?php esc_html_e( 'Agree to receive marketing messages', 'cropx' ); ?></span>
					</label>
					<p class="zform-legal">
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
				<div class="zform-submit-row">
					<button class="zform-submit btn-primary" type="submit">
						<?php echo esc_html( $submit_label ); ?>
					</button>
				</div>

			</form>
		</div><!-- .zform-card -->

	</div><!-- .section-inner -->
</section>
