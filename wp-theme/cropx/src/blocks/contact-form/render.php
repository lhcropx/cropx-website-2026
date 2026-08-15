<?php
/**
 * Contact Form block — front-end render.
 *
 * Two-column layout: intro text (+ optional contact channels) left, form card right.
 *
 * bgColor   'deep-blue' | 'white' | 'taupe'   — section background
 * cardColor 'white'     | 'deep-blue'          — form card background
 *
 * When bgColor is 'deep-blue', an animated topographic SVG is injected via
 * a per-instance <style> rule (same technique as cards, mid-page-cta blocks).
 *
 * showContactDetails toggles the email / phone / address channel rows below
 * the intro copy. Each channel only renders if the field is non-empty.
 *
 * Form submission is handled by view.js, which POSTs to /wp-json/cropx/v1/contact.
 * The nonce is passed via a wp_localize_script()-style data attribute on the form.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ── Attributes ────────────────────────────────────────────────────────────────
$bg_color    = $attributes['bgColor']    ?? 'deep-blue';
$card_color  = $attributes['cardColor']  ?? 'white';
$show_eyebrow = (bool) ( $attributes['showEyebrow'] ?? true );
$eyebrow      = $attributes['eyebrow']    ?? '';
$heading     = $attributes['heading']    ?? '';
$intro_text  = $attributes['introText']  ?? '';
$show_contact = (bool) ( $attributes['showContactDetails'] ?? false );
$contact_email   = $attributes['contactEmail']   ?? '';
$contact_phone   = $attributes['contactPhone']   ?? '';
$contact_address = $attributes['contactAddress'] ?? '';
$privacy_url  = $attributes['privacyUrl']  ?? '/privacy-policy/';
$terms_url    = $attributes['termsUrl']    ?? '/terms-and-conditions/';
$submit_label = $attributes['submitLabel'] ?? 'Submit';
$notify_email = $attributes['notifyEmail'] ?? '';

// Validate enums.
if ( ! in_array( $bg_color, array( 'deep-blue', 'white', 'taupe' ), true ) ) {
	$bg_color = 'deep-blue';
}
if ( ! in_array( $card_color, array( 'white', 'deep-blue' ), true ) ) {
	$card_color = 'white';
}

// $block_id is also used to namespace the form field id/for attributes below
// (unrelated to the topo overlay) — keep it regardless of bgColor.
$block_id = wp_unique_id( 'cf-' );

// ── Topo drift injection ──────────────────────────────────────────────────────
// Inject the pattern's real asset URL via a CSS custom property instead of
// a relative url() in style.css (webpack would base64-inline the ~90KB SVG).
// edit.js sets the same property for the editor preview. Same technique as
// two-column-video.

// ── Section + wrapper ─────────────────────────────────────────────────────────
$section_class = 'cf-section cf-section--bg-' . $bg_color;
$is_dark_bg    = ( 'deep-blue' === $bg_color );

$wrapper_extra = array();
if ( 'deep-blue' === $bg_color ) {
	$wrapper_extra['style'] = '--cf-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');';
}

$wrapper_attrs = get_block_wrapper_attributes( array_merge(
	array( 'class' => $section_class ),
	$wrapper_extra
) );

// ── Color-context helpers (drive CSS modifier classes) ────────────────────────
$card_class  = 'cf-card cf-card--' . $card_color;
$is_dark_card = ( 'deep-blue' === $card_color );

// On a deep-blue section, heading/eyebrow/body text flips white regardless of card.
// Intro column text inherits from section bg.
$intro_on_dark = $is_dark_bg;

// Allowed HTML for intro body — includes <p> so wpautop() output passes through.
$allowed_inline = array(
	'p'      => array(),
	'a'      => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);

// ── Nonce for AJAX submission ─────────────────────────────────────────────────
$nonce = wp_create_nonce( 'cropx_contact_form' );

// ── Channel icons (inline SVG — no img/filter needed on channels) ─────────────
$icon_email = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>';
$icon_phone = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8a19.79 19.79 0 01-3.07-8.68A2 2 0 012.18 1h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.91 8.1a16 16 0 006 6l1.46-1.46a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 14.92z"/></svg>';
$icon_addr  = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>';

?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput ?>>

	<div class="section-inner cf-inner">

		<!-- ── Intro column ──────────────────────────────────────────────── -->
		<div class="cf-intro">

			<?php if ( $show_eyebrow && $eyebrow ) : ?>
				<span class="section-eyebrow cf-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
			<?php endif; ?>

			<h2 class="section-heading cf-heading"><?php echo esc_html( $heading ); ?></h2>

			<?php if ( $intro_text ) : ?>
				<div class="section-body cf-intro-body"><?php echo wp_kses( wpautop( $intro_text ), $allowed_inline ); ?></div>
			<?php endif; ?>

			<?php
			// ── Optional contact channels ──────────────────────────────────
			$has_channels = $show_contact && ( $contact_email || $contact_phone || $contact_address );
			if ( $has_channels ) : ?>
				<div class="cf-channels">

					<?php if ( $contact_email ) : ?>
						<div class="cf-channel">
							<div class="cf-ch-icon"><?php echo $icon_email; // phpcs:ignore ?></div>
							<div class="cf-ch-text">
								<span class="cf-ch-label"><?php esc_html_e( 'Email', 'cropx' ); ?></span>
								<a class="cf-ch-value" href="mailto:<?php echo esc_attr( $contact_email ); ?>"><?php echo esc_html( $contact_email ); ?></a>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $contact_phone ) : ?>
						<div class="cf-channel">
							<div class="cf-ch-icon"><?php echo $icon_phone; // phpcs:ignore ?></div>
							<div class="cf-ch-text">
								<span class="cf-ch-label"><?php esc_html_e( 'Phone', 'cropx' ); ?></span>
								<a class="cf-ch-value" href="tel:<?php echo esc_attr( preg_replace( '/[^+\d]/', '', $contact_phone ) ); ?>"><?php echo esc_html( $contact_phone ); ?></a>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $contact_address ) : ?>
						<div class="cf-channel">
							<div class="cf-ch-icon"><?php echo $icon_addr; // phpcs:ignore ?></div>
							<div class="cf-ch-text">
								<span class="cf-ch-label"><?php esc_html_e( 'Address', 'cropx' ); ?></span>
								<span class="cf-ch-value"><?php echo nl2br( esc_html( $contact_address ) ); ?></span>
							</div>
						</div>
					<?php endif; ?>

				</div><!-- .cf-channels -->
			<?php endif; ?>

		</div><!-- .cf-intro -->

		<!-- ── Form card ─────────────────────────────────────────────────── -->
		<div class="<?php echo esc_attr( $card_class ); ?>">
			<form
				class="cf-form"
				data-nonce="<?php echo esc_attr( $nonce ); ?>"
				data-notify="<?php echo esc_attr( $notify_email ); ?>"
				novalidate
			>
				<!-- Name row -->
				<div class="cf-row">
					<div class="cf-field">
						<label class="cf-label" for="cf-first-name-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'First Name', 'cropx' ); ?> <span class="cf-req" aria-hidden="true">*</span>
						</label>
						<input
							class="cf-input"
							type="text"
							id="cf-first-name-<?php echo esc_attr( $block_id ); ?>"
							name="first_name"
							placeholder="<?php esc_attr_e( 'Jane', 'cropx' ); ?>"
							required
							autocomplete="given-name"
						>
					</div>
					<div class="cf-field">
						<label class="cf-label" for="cf-last-name-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'Last Name', 'cropx' ); ?> <span class="cf-req" aria-hidden="true">*</span>
						</label>
						<input
							class="cf-input"
							type="text"
							id="cf-last-name-<?php echo esc_attr( $block_id ); ?>"
							name="last_name"
							placeholder="<?php esc_attr_e( 'Smith', 'cropx' ); ?>"
							required
							autocomplete="family-name"
						>
					</div>
				</div>

				<!-- Email + Country row -->
				<div class="cf-row">
					<div class="cf-field">
						<label class="cf-label" for="cf-email-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'Email', 'cropx' ); ?> <span class="cf-req" aria-hidden="true">*</span>
						</label>
						<input
							class="cf-input"
							type="email"
							id="cf-email-<?php echo esc_attr( $block_id ); ?>"
							name="email"
							placeholder="<?php esc_attr_e( 'jane@example.com', 'cropx' ); ?>"
							required
							autocomplete="email"
						>
					</div>
					<div class="cf-field">
						<label class="cf-label" for="cf-country-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'Country', 'cropx' ); ?> <span class="cf-req" aria-hidden="true">*</span>
						</label>
						<select class="cf-select" id="cf-country-<?php echo esc_attr( $block_id ); ?>" name="country" required>
							<option value=""><?php esc_html_e( 'Select country…', 'cropx' ); ?></option>
							<option value="AU">Australia</option>
							<option value="BR">Brazil</option>
							<option value="CA">Canada</option>
							<option value="CN">China</option>
							<option value="FR">France</option>
							<option value="DE">Germany</option>
							<option value="IL">Israel</option>
							<option value="MX">Mexico</option>
							<option value="NZ">New Zealand</option>
							<option value="ZA">South Africa</option>
							<option value="ES">Spain</option>
							<option value="TR">Turkey</option>
							<option value="GB">United Kingdom</option>
							<option value="US">United States</option>
							<option value="UY">Uruguay</option>
							<option value="other"><?php esc_html_e( 'Other', 'cropx' ); ?></option>
						</select>
					</div>
				</div>

				<!-- Role + Phone row -->
				<div class="cf-row">
					<div class="cf-field">
						<label class="cf-label" for="cf-role-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'Your role', 'cropx' ); ?> <span class="cf-req" aria-hidden="true">*</span>
						</label>
						<select class="cf-select" id="cf-role-<?php echo esc_attr( $block_id ); ?>" name="role" required>
							<option value=""><?php esc_html_e( 'I am a…', 'cropx' ); ?></option>
							<option value="farmer"><?php esc_html_e( 'Farmer / Grower', 'cropx' ); ?></option>
							<option value="agronomist"><?php esc_html_e( 'Agronomist / Advisor', 'cropx' ); ?></option>
							<option value="dealer"><?php esc_html_e( 'Dealer / Distributor', 'cropx' ); ?></option>
							<option value="service-provider"><?php esc_html_e( 'Service Provider', 'cropx' ); ?></option>
							<option value="enterprise"><?php esc_html_e( 'Enterprise / Cooperative', 'cropx' ); ?></option>
							<option value="other"><?php esc_html_e( 'Other', 'cropx' ); ?></option>
						</select>
					</div>
					<div class="cf-field">
						<label class="cf-label" for="cf-phone-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'Phone', 'cropx' ); ?>
						</label>
						<input
							class="cf-input"
							type="tel"
							id="cf-phone-<?php echo esc_attr( $block_id ); ?>"
							name="phone"
							placeholder="<?php esc_attr_e( '+1 (555) 000-0000', 'cropx' ); ?>"
							autocomplete="tel"
						>
					</div>
				</div>

				<!-- Message -->
				<div class="cf-field">
					<label class="cf-label" for="cf-message-<?php echo esc_attr( $block_id ); ?>">
						<?php esc_html_e( 'How can we help?', 'cropx' ); ?>
					</label>
					<textarea
						class="cf-textarea"
						id="cf-message-<?php echo esc_attr( $block_id ); ?>"
						name="message"
						rows="4"
						placeholder="<?php esc_attr_e( 'Tell us what you\'re looking for…', 'cropx' ); ?>"
					></textarea>
				</div>

				<!-- Source -->
				<div class="cf-field">
					<label class="cf-label" for="cf-source-<?php echo esc_attr( $block_id ); ?>">
						<?php esc_html_e( 'How did you hear about us?', 'cropx' ); ?>
					</label>
					<select class="cf-select" id="cf-source-<?php echo esc_attr( $block_id ); ?>" name="source">
						<option value=""><?php esc_html_e( 'Select…', 'cropx' ); ?></option>
						<option value="search"><?php esc_html_e( 'Search engine', 'cropx' ); ?></option>
						<option value="social"><?php esc_html_e( 'Social media', 'cropx' ); ?></option>
						<option value="tradeshow"><?php esc_html_e( 'Trade show / Event', 'cropx' ); ?></option>
						<option value="word-of-mouth"><?php esc_html_e( 'Word of mouth', 'cropx' ); ?></option>
						<option value="dealer"><?php esc_html_e( 'Dealer / Distributor', 'cropx' ); ?></option>
						<option value="other"><?php esc_html_e( 'Other', 'cropx' ); ?></option>
					</select>
				</div>

				<!-- Marketing consent -->
				<div class="cf-consent">
					<label class="cf-checkbox-wrap">
						<input class="cf-checkbox" type="checkbox" name="marketing_consent" value="1">
						<span class="cf-checkbox-label"><?php esc_html_e( 'Agree to receive marketing messages', 'cropx' ); ?></span>
					</label>
					<p class="cf-legal">
						<?php esc_html_e( 'By checking the above box, you agree to receive automated promotional marketing messages from CropX.', 'cropx' ); ?>
						<a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Privacy Policy', 'cropx' ); ?></a>
						&nbsp;
						<a href="<?php echo esc_url( $terms_url ); ?>"><?php esc_html_e( 'Terms and Conditions', 'cropx' ); ?></a>
					</p>
				</div>

				<!-- Status message (shown by view.js after submit) -->
				<div class="cf-status" role="alert" aria-live="polite" hidden></div>

				<!-- Submit -->
				<div class="cf-submit-row">
					<button class="cf-submit btn-primary" type="submit">
						<?php echo esc_html( $submit_label ); ?>
					</button>
				</div>

			</form>
		</div><!-- .cf-card -->

	</div><!-- .section-inner -->
</section>
