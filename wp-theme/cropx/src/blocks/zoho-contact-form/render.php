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
 * Dropdown (Country), Dropdown2 (Role), PhoneNumber_countrycode, MultiLine,
 * Dropdown3 (source), and DecisionBox are the exact field identifiers
 * Zoho's backend expects. Renaming or removing any of them will make that
 * field's submitted value disappear silently on Zoho's side. If fields are
 * added/removed/renamed on the Zoho form itself, re-export it (or inspect
 * the live form's DOM directly — see below) and update this file's field
 * names (and cropx_zoho_country_list() / cropx_zoho_us_state_list() in
 * inc/helpers.php, if either option list itself changed) to match — label
 * text and the intro column are free to edit any time. Role (Dropdown2),
 * Country (Dropdown), and State/Province/Territory (Dropdown8) are all real
 * Zoho Dropdown fields, so their <option> values must match Zoho's exactly
 * too.
 *
 * Sept 2026 correction: Country and Source were previously posting under
 * the wrong keys (Dropdown1 and Dropdown respectively — Dropdown1 doesn't
 * exist on the live form at all, and Dropdown is actually Country's real
 * key, so "source" answers were silently overwriting Country). Also added:
 * State/Province/Territory (Dropdown8), City (SingleLine2), and County
 * (Dropdown7) — see the Sept 10 update below for the current field-rule
 * scope. These were found by inspecting the live form's own DOM directly
 * (forms.zohopublic.eu/.../formperma/...) rather than by re-exporting, since
 * Zoho's HTML/CSS download saves to a real file we can't read from this
 * environment — field names/required-ness were confirmed from real
 * `name`/`complink`/`mandatory` attributes, not guessed. Full writeup in
 * PROGRESS.md.
 *
 * Sept 10, 2026 update: the original version of this patch only showed
 * State+City for Country = "United States", based on an automated sweep
 * that (wrongly, see cropx_zoho_us_state_list()'s doc comment in
 * inc/helpers.php) concluded no other country triggered anything. Lauren
 * checked Zoho's own Field Rules admin panel directly and found three more
 * rules we were missing. The State/Province/Territory field now shows+
 * requires for Country ∈ {United States, Canada, Australia, Mexico}, with
 * view.js swapping in a different option list per country (from the
 * `data-state-options` JSON below, sourced from
 * cropx_zoho_state_options_by_country()). City stays US-only. A new County
 * field (Dropdown7) is added, shown+required only when State/Province/
 * Territory = "California (CA)".
 *
 * The form's `target` points at a hidden iframe (below) instead of
 * navigating the whole page to Zoho. This is deliberate: an earlier version
 * submitted as a real top-level POST, and whenever Zoho rejected a
 * submission (or any client/server validation mismatch slipped through),
 * the visitor's browser would actually navigate to Zoho's own error
 * response, then bounce back — landing on a bfcache snapshot of this page
 * with the Submit button frozen mid-"Sending…" with no way to recover short
 * of a manual refresh. Submitting into a hidden iframe means the visible
 * page never navigates at all, so that failure mode can't happen. The
 * tradeoff (unchanged from before, just made explicit): we still can't read
 * Zoho's real cross-origin response, so success is optimistic — view.js
 * shows the success message (or fires redirectUrl) as soon as its own
 * required-field checks pass, not once Zoho has confirmed anything.
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
$success_message = $attributes['successMessage'] ?? "Thanks — we've received your message. We'll be in touch soon.";
$referrer_name = $attributes['referrerName'] ?? '';
$privacy_url   = $attributes['privacyUrl']   ?? 'https://cropx.com/privacy-policy/';
$terms_url     = $attributes['termsUrl']     ?? '';

// Validate enums.
if ( ! in_array( $bg_color, array( 'deep-blue', 'taupe' ), true ) ) {
	$bg_color = 'deep-blue';
}
if ( ! in_array( $card_color, array( 'deep-blue' ), true ) ) {
	$card_color = 'white';
}

// $block_id is also used to namespace the form field id/for attributes below
// (unrelated to the topo overlay) — keep it regardless of bgColor.
$block_id = wp_unique_id( 'zcf-' );

// State/Province/Territory's per-country option lists — see the doc comment
// above and cropx_zoho_state_options_by_country() in inc/helpers.php.
$state_options_by_country = cropx_zoho_state_options_by_country();

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
				<span class="section-eyebrow zcf-eyebrow reveal-up" style="--reveal-delay:0.05s"><?php echo esc_html( $eyebrow ); ?></span>
			<?php endif; ?>

			<h2 class="section-heading zcf-heading reveal-up" style="--reveal-delay:0.15s"><?php echo esc_html( $heading ); ?></h2>

			<?php if ( $intro_text ) : ?>
				<div class="section-body zcf-intro-body reveal-up" style="--reveal-delay:0.25s"><?php echo wp_kses( wpautop( $intro_text ), $allowed_inline ); ?></div>
			<?php endif; ?>

			<?php
			$has_channels = $show_contact && ( $contact_email || $contact_phone || $contact_address );
			if ( $has_channels ) : ?>
				<div class="zcf-channels reveal-up" style="--reveal-delay:0.35s">

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
		<div class="<?php echo esc_attr( $card_class ); ?> reveal-up" style="--reveal-delay:0.45s">
			<form
				class="zcf-form"
				action="<?php echo esc_url( $action_url ); ?>"
				method="POST"
				enctype="multipart/form-data"
				accept-charset="UTF-8"
				target="zcf-frame-<?php echo esc_attr( $block_id ); ?>"
				data-success-message="<?php echo esc_attr( $success_message ); ?>"
				novalidate
			>
				<input type="hidden" name="zf_referrer_name" value="<?php echo esc_attr( $referrer_name ); ?>">
				<input type="hidden" name="zf_redirect_url" value="<?php echo esc_attr( $redirect_url ); ?>">
				<input type="hidden" name="zc_gad" value="">

				<!-- Name row -->
				<div class="zcf-row">
					<div class="zcf-field">
						<label class="zcf-label" for="zcf-first-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'First Name', 'cropx' ); ?> <span class="zcf-req" aria-hidden="true">*</span>
						</label>
						<input
							class="zcf-input"
							type="text"
							id="zcf-first-<?php echo esc_attr( $block_id ); ?>"
							name="Name_First"
							maxlength="255"
							required
							autocomplete="given-name"
						>
					</div>
					<div class="zcf-field">
						<label class="zcf-label" for="zcf-last-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'Last Name', 'cropx' ); ?> <span class="zcf-req" aria-hidden="true">*</span>
						</label>
						<input
							class="zcf-input"
							type="text"
							id="zcf-last-<?php echo esc_attr( $block_id ); ?>"
							name="Name_Last"
							maxlength="255"
							required
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
							<?php esc_html_e( 'Country', 'cropx' ); ?> <span class="zcf-req" aria-hidden="true">*</span>
						</label>
						<select class="zcf-select zcf-country-select" id="zcf-country-<?php echo esc_attr( $block_id ); ?>" name="Dropdown" required>
							<option selected value="-Select-"><?php esc_html_e( '-Select-', 'cropx' ); ?></option>
							<?php foreach ( cropx_zoho_country_list() as $country ) : ?>
								<option value="<?php echo esc_attr( $country ); ?>"><?php echo esc_html( $country ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<!-- State/Province/Territory + City row — Zoho's own field rules
				     (confirmed directly in Zoho's Field Rules admin panel, Sept 2026):
				       • "Show States": Country ∈ {United States, Canada, Australia,
				         Mexico} → show + require State/Province/Territory (Dropdown8).
				         view.js swaps in the right option list per country from the
				         data-state-options JSON below.
				       • "Show City in USA": Country = United States → show + require
				         City (SingleLine2). US-only, unlike State.
				     Each field starts hidden here so there's no flash for the ~99% of
				     visitors outside these four countries; view.js toggles `hidden` +
				     `required` on each field individually (they no longer share one
				     visibility condition) and also toggles the row itself so it
				     collapses to nothing when both are hidden. -->
				<div class="zcf-row zcf-row--state-city">
					<div class="zcf-field zcf-field--state" hidden>
						<label class="zcf-label" for="zcf-state-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'State/Province/Territory', 'cropx' ); ?> <span class="zcf-req" aria-hidden="true">*</span>
						</label>
						<select
							class="zcf-select zcf-state-select"
							id="zcf-state-<?php echo esc_attr( $block_id ); ?>"
							name="Dropdown8"
							data-state-options="<?php echo esc_attr( wp_json_encode( $state_options_by_country ) ); ?>"
						>
							<option selected value="-Select-"><?php esc_html_e( '-Select-', 'cropx' ); ?></option>
							<?php foreach ( cropx_zoho_us_state_list() as $state ) : ?>
								<option value="<?php echo esc_attr( $state ); ?>"><?php echo esc_html( $state ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="zcf-field zcf-field--city" hidden>
						<label class="zcf-label" for="zcf-city-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'City', 'cropx' ); ?> <span class="zcf-req" aria-hidden="true">*</span>
						</label>
						<input
							class="zcf-input"
							type="text"
							id="zcf-city-<?php echo esc_attr( $block_id ); ?>"
							name="SingleLine2"
							maxlength="255"
							autocomplete="address-level2"
						>
					</div>
				</div>

				<!-- No County field: Zoho's admin panel lists a "Show California
				     Counties" rule (State/Province/Territory = California -> show
				     County/Dropdown7), but its condition value is literally the
				     string "California" while the live State field's real value is
				     "California (CA)" — confirmed by reading zf_rule.ruleObjs
				     directly out of the live form's own JS. Those never match, so
				     the rule can never actually fire and County never appears for
				     any real visitor, California included (confirmed visually on
				     the live form too). Deliberately not replicated here — adding
				     it would only add friction Zoho's own form doesn't have. If
				     Zoho ever fixes that condition value on their end, this should
				     be revisited (cropx_zoho_california_county_list() in
				     inc/helpers.php is still there, just unused). -->

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
					<select class="zcf-select" id="zcf-source-<?php echo esc_attr( $block_id ); ?>" name="Dropdown3">
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

				<!-- Status message (shown by view.js after submit) -->
				<div class="zcf-status" role="alert" aria-live="polite" hidden></div>

				<!-- Submit -->
				<div class="zcf-submit-row">
					<button class="zcf-submit btn-primary" type="submit">
						<?php echo esc_html( $submit_label ); ?>
					</button>
				</div>

			</form>

			<!-- Hidden submission target — the form posts here instead of navigating
			     the whole page to Zoho. See the doc comment at the top of this file. -->
			<iframe
				name="zcf-frame-<?php echo esc_attr( $block_id ); ?>"
				class="zcf-iframe-target"
				title="<?php esc_attr_e( 'Form submission (hidden)', 'cropx' ); ?>"
				aria-hidden="true"
				tabindex="-1"
			></iframe>
		</div><!-- .zcf-card -->

	</div><!-- .section-inner -->
</section>
