<?php
/**
 * Cookie Consent Banner — Option A (compact single-row bar)
 * inc/parts/cookie-consent.php
 *
 * Included in footer.php before wp_footer(). Hidden on page load; JS shows it
 * only to visitors who haven't yet made a consent choice.
 *
 * Consent cookie:   cropx_consent
 *   'all'           user accepted all cookies
 *   'essential'     user rejected non-essential cookies
 *   (not set)       first visit — show the banner
 *
 * Privacy policy URL: uses get_privacy_policy_url() if configured in
 * WP → Settings → Privacy, otherwise falls back to /privacy-policy/.
 *
 * HTML classes reuse:
 *   .section-inner  — max-width centring wrapper (shared.css)
 *   .btn-primary    — Accept All button (shared.css)
 *   .cta-link       — "Manage Preferences" text link (shared.css)
 * Everything else is prefixed cc-* and lives in styles/cookie-consent.css.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$cc_policy_url = get_privacy_policy_url() ?: home_url( '/privacy-policy/' );
?>

<div id="cc-banner" class="cc-banner" role="region"
     aria-label="<?php esc_attr_e( 'Cookie consent', 'cropx' ); ?>"
     aria-live="polite" hidden>
	<div class="cc-inner section-inner">
		<div class="cc-row">

			<p class="cc-text">
				<?php esc_html_e( 'We use cookies to analyse site traffic and support our marketing. Strictly necessary cookies are always on.', 'cropx' ); ?>
				<a href="<?php echo esc_url( $cc_policy_url ); ?>">
					<?php esc_html_e( 'Privacy &amp; Cookie Policy', 'cropx' ); ?>
				</a>
			</p>

			<div class="cc-actions">
				<button id="cc-reject" class="cc-btn-reject" type="button">
					<?php esc_html_e( 'Reject Non-Essential', 'cropx' ); ?>
				</button>
				<button id="cc-accept" class="btn-primary" type="button">
					<?php esc_html_e( 'Accept All', 'cropx' ); ?>
				</button>
				<div class="cc-divider" aria-hidden="true"></div>
				<button id="cc-manage" class="cta-link" type="button"
				        style="background:none;border:none;cursor:pointer;padding:0;">
					<?php esc_html_e( 'Manage Preferences', 'cropx' ); ?>
				</button>
			</div>

		</div>
	</div>
</div>

<!-- Preferences panel — shown when "Manage Preferences" is clicked -->
<div id="cc-prefs" class="cc-prefs" role="region"
     aria-label="<?php esc_attr_e( 'Cookie preferences', 'cropx' ); ?>" hidden>
	<div class="section-inner">

		<div class="cc-prefs-header">
			<p class="cc-prefs-title"><?php esc_html_e( 'Manage Cookie Preferences', 'cropx' ); ?></p>
			<button id="cc-prefs-close" class="cc-prefs-close" type="button"
			        aria-label="<?php esc_attr_e( 'Close preferences', 'cropx' ); ?>">&#x2715;</button>
		</div>

		<div class="cc-prefs-grid">

			<div class="cc-pref-card">
				<div class="cc-pref-header">
					<span class="cc-pref-title"><?php esc_html_e( 'Strictly Necessary', 'cropx' ); ?></span>
					<label class="cc-toggle" title="<?php esc_attr_e( 'Cannot be disabled', 'cropx' ); ?>">
						<input type="checkbox" checked disabled
						       aria-label="<?php esc_attr_e( 'Strictly Necessary — always active', 'cropx' ); ?>">
						<span class="cc-toggle-track"></span>
					</label>
				</div>
				<p class="cc-pref-body">
					<?php esc_html_e( 'Essential for the site to work — session management, security, and load balancing. These cannot be switched off.', 'cropx' ); ?>
				</p>
			</div>

			<div class="cc-pref-card">
				<div class="cc-pref-header">
					<span class="cc-pref-title"><?php esc_html_e( 'Analytics', 'cropx' ); ?></span>
					<label class="cc-toggle">
						<input type="checkbox" id="cc-toggle-analytics"
						       aria-label="<?php esc_attr_e( 'Analytics cookies', 'cropx' ); ?>">
						<span class="cc-toggle-track"></span>
					</label>
				</div>
				<p class="cc-pref-body">
					<?php esc_html_e( 'Help us understand how visitors use our site (e.g. Google Analytics). No personally identifiable data is collected.', 'cropx' ); ?>
				</p>
			</div>

			<div class="cc-pref-card">
				<div class="cc-pref-header">
					<span class="cc-pref-title"><?php esc_html_e( 'Marketing', 'cropx' ); ?></span>
					<label class="cc-toggle">
						<input type="checkbox" id="cc-toggle-marketing"
						       aria-label="<?php esc_attr_e( 'Marketing cookies', 'cropx' ); ?>">
						<span class="cc-toggle-track"></span>
					</label>
				</div>
				<p class="cc-pref-body">
					<?php esc_html_e( 'Used to deliver relevant ads and measure campaign effectiveness. Data may be shared with advertising partners.', 'cropx' ); ?>
				</p>
			</div>

			<div class="cc-pref-card">
				<div class="cc-pref-header">
					<span class="cc-pref-title"><?php esc_html_e( 'Functional', 'cropx' ); ?></span>
					<label class="cc-toggle">
						<input type="checkbox" id="cc-toggle-functional"
						       aria-label="<?php esc_attr_e( 'Functional cookies', 'cropx' ); ?>">
						<span class="cc-toggle-track"></span>
					</label>
				</div>
				<p class="cc-pref-body">
					<?php esc_html_e( 'Enable enhanced features like video playback and live chat. Some features may be unavailable without these.', 'cropx' ); ?>
				</p>
			</div>

		</div><!-- /cc-prefs-grid -->

		<div class="cc-prefs-footer">
			<div class="cc-prefs-links">
				<a href="<?php echo esc_url( $cc_policy_url ); ?>"><?php esc_html_e( 'Privacy Policy', 'cropx' ); ?></a>
				<a href="<?php echo esc_url( $cc_policy_url ); ?>"><?php esc_html_e( 'Cookie Policy', 'cropx' ); ?></a>
			</div>
			<div class="cc-prefs-actions">
				<button id="cc-prefs-reject" class="cc-btn-reject" type="button">
					<?php esc_html_e( 'Reject Non-Essential', 'cropx' ); ?>
				</button>
				<button id="cc-prefs-save" class="btn-primary" type="button">
					<?php esc_html_e( 'Save My Preferences', 'cropx' ); ?>
				</button>
			</div>
		</div>

	</div><!-- /section-inner -->
</div><!-- /cc-prefs -->
