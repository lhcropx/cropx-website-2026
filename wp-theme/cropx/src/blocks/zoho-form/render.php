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
 * Dropdown (Country), Dropdown2 (Role), PhoneNumber_countrycode, MultiLine,
 * Dropdown3 (source), and DecisionBox are the exact field identifiers
 * Zoho's backend expects for this form. Renaming or removing any of them
 * will make that field's submitted value disappear silently on Zoho's side.
 * If fields are added/removed/renamed on the Zoho form itself, re-export it
 * (or inspect the live form's DOM directly — see below) and update this
 * file's field names (and cropx_zoho_country_list() /
 * cropx_zoho_us_state_list() in inc/helpers.php, if either option list
 * itself changed) to match — label text is free to edit any time. Role
 * (Dropdown2), Country (Dropdown), and State/Province/Territory (Dropdown8)
 * are all real Zoho Dropdown fields, so their <option> values must match
 * Zoho's exactly too — see cropx_zoho_country_list() for why the Country
 * list is copied verbatim rather than a "nicer" hand-written list.
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
 * bgColor   'white' | 'taupe' | 'deep-blue'  — section background
 * cardColor 'white' | 'deep-blue'             — form card background
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ── Attributes ────────────────────────────────────────────────────────────────
$bg_color      = $attributes['bgColor']      ?? 'taupe';
$card_color    = $attributes['cardColor']    ?? 'white';
$show_eyebrow  = (bool) ( $attributes['showEyebrow'] ?? true );
$eyebrow       = $attributes['eyebrow']      ?? '';
$heading       = $attributes['heading']      ?? '';
$intro_text    = $attributes['introText']    ?? '';
$submit_label  = $attributes['submitLabel']  ?? 'Submit';
$action_url    = $attributes['formActionUrl'] ?? '';
$redirect_url  = $attributes['redirectUrl']  ?? '';
$success_message = $attributes['successMessage'] ?? "Thanks — we've received your message. We'll be in touch soon.";
$referrer_name = $attributes['referrerName'] ?? '';
$privacy_url   = $attributes['privacyUrl']   ?? 'https://cropx.com/privacy-policy/';
$terms_url     = $attributes['termsUrl']     ?? '';

// Validate enums.
if ( ! in_array( $bg_color, array( 'taupe', 'deep-blue' ), true ) ) {
	$bg_color = 'taupe';
}
if ( ! in_array( $card_color, array( 'deep-blue' ), true ) ) {
	$card_color = 'white';
}

// $block_id is also used to namespace the form field id/for attributes below
// (unrelated to the topo overlay) — keep it regardless of bgColor.
$block_id = wp_unique_id( 'zform-' );

// State/Province/Territory's per-country option lists — see the doc comment
// above and cropx_zoho_state_options_by_country() in inc/helpers.php.
$state_options_by_country = cropx_zoho_state_options_by_country();

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
					<span class="section-eyebrow zform-eyebrow reveal-up" style="--reveal-delay:0.05s"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h2 class="section-heading zform-heading reveal-up" style="--reveal-delay:0.15s"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>
				<?php if ( $intro_text ) : ?>
					<p class="section-body zform-intro reveal-up" style="--reveal-delay:0.25s"><?php echo esc_html( $intro_text ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="<?php echo esc_attr( $card_class ); ?> reveal-up" style="--reveal-delay:0.35s">
			<form
				class="zform-form"
				action="<?php echo esc_url( $action_url ); ?>"
				method="POST"
				enctype="multipart/form-data"
				accept-charset="UTF-8"
				target="zform-frame-<?php echo esc_attr( $block_id ); ?>"
				data-success-message="<?php echo esc_attr( $success_message ); ?>"
				novalidate
			>
				<input type="hidden" name="zf_referrer_name" value="<?php echo esc_attr( $referrer_name ); ?>">
				<input type="hidden" name="zf_redirect_url" value="<?php echo esc_attr( $redirect_url ); ?>">
				<input type="hidden" name="zc_gad" value="">

				<!-- Name row -->
				<div class="zform-row">
					<div class="zform-field">
						<label class="zform-label" for="zform-first-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'First Name', 'cropx' ); ?> <span class="zform-req" aria-hidden="true">*</span>
						</label>
						<input
							class="zform-input"
							type="text"
							id="zform-first-<?php echo esc_attr( $block_id ); ?>"
							name="Name_First"
							maxlength="255"
							required
							autocomplete="given-name"
						>
					</div>
					<div class="zform-field">
						<label class="zform-label" for="zform-last-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'Last Name', 'cropx' ); ?> <span class="zform-req" aria-hidden="true">*</span>
						</label>
						<input
							class="zform-input"
							type="text"
							id="zform-last-<?php echo esc_attr( $block_id ); ?>"
							name="Name_Last"
							maxlength="255"
							required
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
							<?php esc_html_e( 'Country', 'cropx' ); ?> <span class="zform-req" aria-hidden="true">*</span>
						</label>
						<select class="zform-select zform-country-select" id="zform-country-<?php echo esc_attr( $block_id ); ?>" name="Dropdown" required>
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
				<div class="zform-row zform-row--state-city">
					<div class="zform-field zform-field--state" hidden>
						<label class="zform-label" for="zform-state-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'State/Province/Territory', 'cropx' ); ?> <span class="zform-req" aria-hidden="true">*</span>
						</label>
						<select
							class="zform-select zform-state-select"
							id="zform-state-<?php echo esc_attr( $block_id ); ?>"
							name="Dropdown8"
							data-state-options="<?php echo esc_attr( wp_json_encode( $state_options_by_country ) ); ?>"
						>
							<option selected value="-Select-"><?php esc_html_e( '-Select-', 'cropx' ); ?></option>
							<?php foreach ( cropx_zoho_us_state_list() as $state ) : ?>
								<option value="<?php echo esc_attr( $state ); ?>"><?php echo esc_html( $state ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="zform-field zform-field--city" hidden>
						<label class="zform-label" for="zform-city-<?php echo esc_attr( $block_id ); ?>">
							<?php esc_html_e( 'City', 'cropx' ); ?> <span class="zform-req" aria-hidden="true">*</span>
						</label>
						<input
							class="zform-input"
							type="text"
							id="zform-city-<?php echo esc_attr( $block_id ); ?>"
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
					<select class="zform-select" id="zform-source-<?php echo esc_attr( $block_id ); ?>" name="Dropdown3">
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

				<!-- Status message (shown by view.js after submit) -->
				<div class="zform-status" role="alert" aria-live="polite" hidden></div>

				<!-- Submit -->
				<div class="zform-submit-row">
					<button class="zform-submit btn-primary" type="submit">
						<?php echo esc_html( $submit_label ); ?>
					</button>
				</div>

			</form>

			<!-- Hidden submission target — the form posts here instead of navigating
			     the whole page to Zoho. See the doc comment at the top of this file. -->
			<iframe
				name="zform-frame-<?php echo esc_attr( $block_id ); ?>"
				class="zform-iframe-target"
				title="<?php esc_attr_e( 'Form submission (hidden)', 'cropx' ); ?>"
				aria-hidden="true"
				tabindex="-1"
			></iframe>
		</div><!-- .zform-card -->

	</div><!-- .section-inner -->
</section>
