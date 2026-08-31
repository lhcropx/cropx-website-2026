<?php
/**
 * Footer Brand Column block — front-end render.
 *
 * Extracted verbatim from the old monolithic cropx/footer block's brand
 * column markup (logo, tagline, contact links, social icons). Kept as one
 * block rather than decomposed into core Image/Paragraph/Social Links
 * blocks because:
 *   - the contact row icons (mail, location pin) are inline currentColor
 *     SVGs whose colour must track the surrounding link's hover state —
 *     core/image can't do that without a second hover-state image asset.
 *   - the social icons use the same currentColor + circular-border hover
 *     treatment, distinct from core/social-links' own styling.
 *
 * CSS: this block does NOT declare its own "style" — .footer-brand,
 * .footer-brand-identity, .footer-tagline, .footer-contact, .footer-social,
 * .footer-logo are all defined in the sitewide styles/footer.css (enqueued
 * unconditionally in inc/enqueue.php, since the footer appears on every
 * page). Scoped under the pattern's own ".ftr-c" ancestor class exactly like
 * they were under the old block.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$tagline        = $attributes['tagline']        ?? 'Soil intelligence for growers, agronomists, and food companies.';
$contact_email  = $attributes['contactEmail']   ?? 'sales@cropx.com';
$show_locations = (bool) ( $attributes['showLocations'] ?? true );
$locations_text = esc_html( $attributes['locationsText'] ?? 'Anaheim · Melbourne · Wellington · Haren · Netanya' );
$linkedin_url   = $attributes['linkedinUrl']    ?? '#';
$x_url          = $attributes['xUrl']           ?? '#';
$youtube_url    = $attributes['youtubeUrl']     ?? '#';
$facebook_url   = $attributes['facebookUrl']    ?? '#';
$instagram_url  = $attributes['instagramUrl']   ?? '#';

$logo_src      = CROPX_THEME_URI . 'assets/logos/cropx-wordmark.svg';
$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'footer-brand' ) );
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="footer-brand-identity">
		<img src="<?php echo esc_url( $logo_src ); ?>" alt="CropX" class="footer-logo">
		<p class="footer-tagline"><?php echo esc_html( $tagline ); ?></p>
	</div>

	<div class="footer-contact">
		<a href="mailto:<?php echo esc_attr( $contact_email ); ?>">
			<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M2 4h12v9H2V4zm0 0l6 5 6-5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>
			<?php echo esc_html( $contact_email ); ?>
		</a>
	<?php if ( $show_locations ) : ?>
		<a href="#">
			<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 1.5A4.5 4.5 0 018 10.5c-2 0-5 2.5-5 2.5s.5-3 2-4.5A4.5 4.5 0 018 1.5z" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><circle cx="8" cy="6" r="1.5" stroke="currentColor" stroke-width="1.25"/></svg>
			<span class="footer-locations-text"><?php echo $locations_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — already esc_html'd above ?></span>
		</a>
	<?php endif; ?>
	</div>

	<div class="footer-social">
		<a href="<?php echo esc_url( cropx_url( $linkedin_url ) ); ?>" aria-label="LinkedIn">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2zm2-5a2 2 0 110 4 2 2 0 010-4z"/></svg>
		</a>
		<a href="<?php echo esc_url( cropx_url( $x_url ) ); ?>" aria-label="X">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
		</a>
		<a href="<?php echo esc_url( cropx_url( $youtube_url ) ); ?>" aria-label="YouTube">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23 7s-.3-1.9-1.1-2.7c-1.1-1.1-2.3-1.1-2.8-1.2C16.6 3 12 3 12 3s-4.6 0-7.1.1c-.6.1-1.8.1-2.8 1.2C1.3 5.1 1 7 1 7S.7 9.1.7 11.3v2c0 2.1.3 4.3.3 4.3s.3 1.9 1.1 2.7c1.1 1.1 2.5 1 3.1 1.1C7.2 21.6 12 21.6 12 21.6s4.6 0 7.1-.2c.6-.1 1.8-.1 2.8-1.2.8-.8 1.1-2.7 1.1-2.7s.3-2.1.3-4.3v-2C23.3 9.1 23 7 23 7zM9.7 15.5V8.4l7.6 3.6-7.6 3.5z"/></svg>
		</a>
		<a href="<?php echo esc_url( cropx_url( $facebook_url ) ); ?>" aria-label="Facebook">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
		</a>
		<a href="<?php echo esc_url( cropx_url( $instagram_url ) ); ?>" aria-label="Instagram">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2c-2.716 0-3.056.013-4.123.06-1.064.049-1.791.218-2.427.465a4.902 4.902 0 00-1.772 1.153A4.902 4.902 0 002.525 5.45c-.247.636-.416 1.363-.465 2.427C2.013 8.944 2 9.284 2 12c0 2.716.013 3.056.06 4.123.049 1.064.218 1.791.465 2.427a4.902 4.902 0 001.153 1.772 4.902 4.902 0 001.772 1.153c.636.247 1.363.416 2.427.465C8.944 21.987 9.284 22 12 22c2.716 0 3.056-.013 4.123-.06 1.064-.049 1.791-.218 2.427-.465a4.902 4.902 0 001.772-1.153 4.902 4.902 0 001.153-1.772c.247-.636.416-1.363.465-2.427.047-1.067.06-1.407.06-4.123 0-2.716-.013-3.056-.06-4.123-.049-1.064-.218-1.791-.465-2.427a4.902 4.902 0 00-1.153-1.772A4.902 4.902 0 0018.55 2.525c-.636-.247-1.363-.416-2.427-.465C15.056 2.013 14.716 2 12 2zm0 2c2.67 0 2.986.01 4.04.058.976.045 1.505.207 1.858.344.466.182.8.398 1.15.748.35.35.566.683.748 1.15.137.353.3.882.344 1.857.048 1.055.058 1.37.058 4.041 0 2.67-.01 2.986-.058 4.04-.045.976-.207 1.505-.344 1.858a3.097 3.097 0 01-.748 1.15 3.098 3.098 0 01-1.15.748c-.353.137-.882.3-1.857.344-1.054.048-1.37.058-4.041.058-2.67 0-2.987-.01-4.04-.058-.976-.045-1.505-.207-1.858-.344a3.098 3.098 0 01-1.15-.748 3.098 3.098 0 01-.748-1.15c-.137-.353-.3-.882-.344-1.857-.048-1.055-.058-1.37-.058-4.041 0-2.67.01-2.986.058-4.04.045-.976.207-1.505.344-1.858.182-.466.398-.8.748-1.15.35-.35.683-.566 1.15-.748.353-.137.882-.3 1.857-.344C9.014 4.01 9.33 4 12 4zm0 3a5 5 0 100 10A5 5 0 0012 7zm0 2a3 3 0 110 6 3 3 0 010-6zm5.25-3.5a1.25 1.25 0 100 2.5 1.25 1.25 0 000-2.5z"/></svg>
		</a>
	</div>
</div>
