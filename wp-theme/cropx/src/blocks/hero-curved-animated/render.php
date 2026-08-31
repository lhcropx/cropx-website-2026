<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$segment    = $attributes['segment']    ?? 'cropx';
$eyebrow    = $attributes['eyebrow']    ?? '';
$heading    = $attributes['heading']    ?? '';
$subheading = $attributes['subheading'] ?? '';
$cta_label  = $attributes['ctaLabel']   ?? '';
$cta_url    = $attributes['ctaUrl']     ?? '#';
$cta2_label = $attributes['cta2Label']  ?? '';
$cta2_url   = $attributes['cta2Url']    ?? '#';
$show_cta2  = (bool) ( $attributes['showCta2'] ?? false );

// Primary/secondary CTA can each point to a URL (default) or a media-library
// file download. In file mode the href resolves straight to the attachment
// URL (no cropx_url() relativizing needed — it's already a same-origin
// upload URL). Open in browser, not force-download (Aug 2026, sitewide
// change — Lauren): the anchor gets target="_blank" rel="noopener noreferrer"
// instead of a `download` attribute, so the PDF opens in a new tab using the
// browser's own viewer rather than dropping straight into the visitor's
// downloads folder — see resource-downloads/render.php's doc comment for the
// full reasoning. The secondary CTA additionally swaps its animated arrow
// icon for a static download icon — see the shared icon markup below, reused
// from resource-downloads/render.php.
$cta_link_type  = $attributes['ctaLinkType']  ?? 'url';
$cta_file_url   = $attributes['ctaFileUrl']   ?? '';
$cta_is_file    = ( 'file' === $cta_link_type && $cta_file_url );
$cta_href       = $cta_is_file ? $cta_file_url : cropx_url( $cta_url );

$cta2_link_type = $attributes['cta2LinkType'] ?? 'url';
$cta2_file_url  = $attributes['cta2FileUrl']  ?? '';
$cta2_is_file   = ( 'file' === $cta2_link_type && $cta2_file_url );
$cta2_href      = $cta2_is_file ? $cta2_file_url : cropx_url( $cta2_url );

$cta2_icon = $cta2_is_file
	? '<svg class="cta-icon--static" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/><polyline points="7 10 12 15 17 10" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>'
	: '<svg width="18" height="18" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

$bg_image_id  = (int) ( $attributes['bgImageId']  ?? 0 );
$bg_image_url = $attributes['bgImageUrl']          ?? '';
$show_eyebrow = (bool) ( $attributes['showEyebrow'] ?? true );
$show_cta     = (bool) ( $attributes['showCta']     ?? true );

$pairs     = is_array( $attributes['pairs'] ?? null ) ? array_values( $attributes['pairs'] ) : array();
$has_pairs = ! empty( $pairs );
if ( ! $has_pairs ) {
	// Always at least one pair, so the block never renders with nothing to show.
	$pairs = array( array() );
}
$pair_count = count( $pairs );

// ── Pair slideshow timing ──────────────────────────────────────────────────
// Exactly one pair is ever visible at a time. Each pair's "slot" is:
//   enter (fade + rise/sink into place) → hold (static) → exit (reverse of
//   enter: fade + sink/rise back out) → gap (nothing visible)
// then the next pair's slot begins. After the last pair, the cycle loops back
// to the first with the same gap.
//
// This is driven entirely by CSS: every device/phone image in every pair
// shares the SAME keyframe animation (one for "rise", one for "sink") and the
// SAME animation-duration (the full loop length), but each pair gets a
// different positive animation-delay of (pair index × slot length). Because
// the keyframe is only "visible" during its own local [0, slot] window and
// opacity:0 for the rest of the loop, a positive delay shifts exactly when in
// real time that window lands — pair 0 at t=0, pair 1 at t=slot, pair 2 at
// t=2×slot, and so on, wrapping automatically because animation-iteration-count
// is infinite. See style.css's shca-item--device/--phone rules for the base
// positioning (unrelated to this timing) and the keyframes generated below
// for the actual motion — the exit phase now travels back to the same offset
// the enter phase started from (device sinks back down, phone rises back up),
// so the exit is a mirror image of the entrance rather than a plain fade.
$enter_s = 0.6; // fade + rise/sink duration
$exit_s  = 0.4; // fade + sink/rise-back-out duration (mirrors the entrance)
$gap_s   = 0.25; // fully hidden between pairs
$hold_s  = (float) ( $attributes['pairHoldSeconds'] ?? 5 );
if ( $hold_s < 1 ) {
	$hold_s = 1;
}

// Text width override — see hero-curved-standard/render.php for the full
// rationale (lets editors narrow the headline + subheadline column together
// when a device/app overlay image covers it).
$text_width    = (int) ( $attributes['textWidth'] ?? 100 );
$content_style = sprintf(
	'--shc-headline-w:%1$s;--shc-subhead-w:%1$s;',
	esc_attr( $text_width / 100 )
);
$slot_s  = $enter_s + $hold_s + $exit_s + $gap_s;
$total_s = $slot_s * $pair_count;

// Percent breakpoints, expressed as a fraction of $total_s (since CSS keyframe
// percentages are always relative to the full animation-duration, and every
// pair shares one duration — the full loop length — regardless of pair count).
$pct_enter_end = round( $enter_s / $total_s * 100, 4 );
$pct_hold_end  = round( ( $enter_s + $hold_s ) / $total_s * 100, 4 );
$pct_exit_end  = round( ( $enter_s + $hold_s + $exit_s ) / $total_s * 100, 4 );

// Unique per-instance keyframe names — if this block appears more than once on
// a page with different settings (pair count, hold time), sharing one global
// keyframe name would let whichever instance's <style> renders last silently
// override the timing for every other instance on the page (CSS @keyframes
// are global by name). Namespacing avoids that entirely.
$uid        = wp_unique_id( 'shca-' );
$rise_name  = 'shca-rise-' . $uid;
$sink_name  = 'shca-sink-' . $uid;

// Both keyframes share identical timing/opacity — only the resting Y offset
// they animate FROM/TO differs (device starts below and rises to 0, then on
// exit sinks back down to that same starting offset; phone starts above and
// sinks to 0, then on exit rises back up to that same starting offset). The
// exit is a genuine mirror of the entrance, not just a fade — same distance,
// opposite direction.
$keyframes_css = sprintf(
	'@keyframes %1$s{0%%{opacity:0;transform:translateY(24px);animation-timing-function:cubic-bezier(.16,1,.3,1)}%2$s%%{opacity:1;transform:translateY(0);animation-timing-function:linear}%3$s%%{opacity:1;transform:translateY(0);animation-timing-function:cubic-bezier(.7,0,.84,0)}%4$s%%{opacity:0;transform:translateY(24px)}100%%{opacity:0;transform:translateY(24px)}}'
	. '@keyframes %5$s{0%%{opacity:0;transform:translateY(-24px);animation-timing-function:cubic-bezier(.16,1,.3,1)}%2$s%%{opacity:1;transform:translateY(0);animation-timing-function:linear}%3$s%%{opacity:1;transform:translateY(0);animation-timing-function:cubic-bezier(.7,0,.84,0)}%4$s%%{opacity:0;transform:translateY(-24px)}100%%{opacity:0;transform:translateY(-24px)}}',
	esc_attr( $rise_name ),
	$pct_enter_end,
	$pct_hold_end,
	$pct_exit_end,
	esc_attr( $sink_name )
);

// Swoop fill — auto-detected from the next block's bgColor, with an optional
// manual override. White is the safe fallback (native/plain content after hero).
$swoop_fill = cropx_get_swoop_fill( 'cropx/hero-curved-animated', $attributes['swoopFill'] ?? '' );

// Drifting pattern overlay — see hero-curved-standard's render.php for why this
// is a CSS custom property instead of a relative url() (avoids a ~90KB SVG
// getting base64-inlined into style-index.css, which broke WP File Manager
// zip deploys on that block).
$pattern_css_vars = sprintf(
	'--shc-swoop-fill:%s;--shc-pattern-url:url(%s)',
	esc_attr( $swoop_fill ),
	esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' )
);

$allowed_segments = array( 'cropx', 'enterprise', 'service-provider', 'on-farm' );
if ( ! in_array( $segment, $allowed_segments, true ) ) {
	$segment = 'cropx';
}

// Accent colour maps — used to inline the SVG gradient stops.
$accent_config = array(
	'cropx'            => array( 'base' => '#0CA8C0', 'dark' => '#0A8FA3' ),
	'enterprise'       => array( 'base' => '#E9C242', 'dark' => '#C9A130' ),
	'service-provider' => array( 'base' => '#E48C4D', 'dark' => '#C4733A' ),
	'on-farm'          => array( 'base' => '#96C05A', 'dark' => '#7BA348' ),
);
$accent = $accent_config[ $segment ];

// Background image.
$bg_focal_x = isset( $attributes['bgFocalX'] ) ? round( (float) $attributes['bgFocalX'] * 100, 1 ) : 50;
$bg_focal_y = isset( $attributes['bgFocalY'] ) ? round( (float) $attributes['bgFocalY'] * 100, 1 ) : 30;
$bg_zoom    = isset( $attributes['bgZoom'] )   ? (float) $attributes['bgZoom'] : 100;
$bg_flip_x  = ! empty( $attributes['bgFlipX'] );

// PageSpeed fix (Aug 2026): real <img fetchpriority="high"> instead of a CSS
// background-image, so the browser's preload scanner can discover and
// prioritize this hero photo from the initial HTML. This is unrelated to the
// device/phone pair slideshow below — the background photo itself is always
// static. See .shc-bg / .shc-bg img in style.css.
$bg_img_style    = '';
$resolved_bg_url = '';
if ( $bg_image_id ) {
	$src = wp_get_attachment_image_src( $bg_image_id, 'full' );
	if ( $src ) {
		$resolved_bg_url = $src[0];
	}
} elseif ( $bg_image_url ) {
	$resolved_bg_url = $bg_image_url;
}
if ( $resolved_bg_url ) {
	// Flip lives on the .shc-bg WRAPPER (mirrored around its own center)
	// rather than on this img's own transform — combining the flip into this
	// img's scale() around the focal-point origin caused the mirrored image
	// to fly off-frame whenever the focal point wasn't centered. See the
	// wrapper div below for the flip style.
	$bg_zoom_factor = number_format( $bg_zoom / 100, 4, '.', '' );
	$bg_img_style = sprintf(
		'object-position: %s%% %s%%; transform: scale(%s); transform-origin: %s%% %s%%;',
		esc_attr( $bg_focal_x ),
		esc_attr( $bg_focal_y ),
		esc_attr( $bg_zoom_factor ),
		esc_attr( $bg_focal_x ),
		esc_attr( $bg_focal_y )
	);
}

$theme_uri = CROPX_THEME_URI;

// cropx_hca_render_image() — builds the <img>/<picture> markup for one
// device or phone image — lives in inc/helpers.php, not here. render.php
// templates are include()'d fresh on every render, so a function declared
// inline here would fatal with "Cannot redeclare function" if this block is
// ever used twice on the same page; helpers.php is required exactly once
// per request.
//
// Build each pair's animation shorthand once here (identical for its device
// and phone — that's what makes them appear/disappear "concurrently") and
// pass it straight through to the render helper.
$pair_animations = array();
foreach ( $pairs as $i => $pair ) {
	$delay_s = $i * $slot_s;
	$pair_animations[ $i ] = array(
		'device' => sprintf( '%s %ss linear %ss infinite backwards', $rise_name, number_format( $total_s, 3, '.', '' ), number_format( $delay_s, 3, '.', '' ) ),
		'phone'  => sprintf( '%s %ss linear %ss infinite backwards', $sink_name, number_format( $total_s, 3, '.', '' ), number_format( $delay_s, 3, '.', '' ) ),
	);
}

// Allowed tags for RichText output.
$heading_tags = array( 'em' => array(), 'strong' => array(), 'br' => array() );
$sub_tags     = array_merge( $heading_tags, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => 'shc-block shc-segment-' . esc_attr( $segment ),
) );

// SVG gradient stop colors for the swoop stroke — inlined so they resolve at
// render time rather than requiring a CSS variable that SVG can't see.
$stop_base = esc_attr( $accent['base'] );
$stop_dark = esc_attr( $accent['dark'] );
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php
	if ( empty( $GLOBALS['cropx_nav_already_rendered'] ) ) :
		cropx_render_nav();
	endif;
	?>

	<!-- Per-instance keyframes for this block's pair slideshow — see the timing
	     comment above for why these can't live in the shared style.css. -->
	<style><?php echo $keyframes_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></style>

	<!--
		.shc-bleed-wrap is position:relative and defines --shc-edge, the inset from
		the viewport edge to the max-width container. Device images render inside
		.shc-hero (clipped by its overflow:hidden, subject to the swoop); phone
		images render here as direct children of .shc-bleed-wrap so they bleed
		past the curve. Every image positions itself independently via its own
		--i-scale/--i-x/--i-y (see cropx_hca_render_image() in inc/helpers.php)
		rather than one shared block-level variable, since there can be any
		number of pairs. .shca-device-layer/.shca-phone-layer are plain (non-
		positioned) wrappers that exist only so the reduced-motion CSS in
		style.css can target "just the first pair" via :first-child.
	-->
	<div class="shc-bleed-wrap" style="<?php echo esc_attr( $pattern_css_vars ); ?>">
		<section class="shc-hero">
			<div class="shc-bg"<?php echo $bg_flip_x ? ' style="transform: scaleX(-1);"' : ''; ?>>
				<?php if ( $resolved_bg_url ) : ?>
				<img
					src="<?php echo esc_url( $resolved_bg_url ); ?>"
					alt=""
					fetchpriority="high"
					decoding="async"
					<?php echo $bg_img_style ? ' style="' . esc_attr( $bg_img_style ) . '"' : ''; ?>
				>
				<?php endif; ?>
			</div>
			<div class="shc-overlay"></div>
			<div class="shc-pattern"></div>

			<div class="shc-content<?php echo ! $has_pairs ? ' shc-content--wide' : ''; ?>" style="<?php echo $content_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
				<?php if ( $show_eyebrow && $eyebrow ) : ?>
					<p class="shc-eyebrow"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></p>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h1 class="shc-headline"><?php echo wp_kses( $heading, $heading_tags ); ?></h1>
				<?php endif; ?>
				<?php if ( $subheading ) : ?>
					<p class="shc-subheadline"><?php echo wp_kses( $subheading, $sub_tags ); ?></p>
				<?php endif; ?>
				<?php if ( $show_cta && $cta_label ) : ?>
				<div class="shc-cta-row">
					<a class="shc-cta" href="<?php echo esc_url( $cta_href ); ?>"<?php echo $cta_is_file ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $cta_label ); ?></a>
					<?php if ( $show_cta2 && $cta2_label ) : ?>
					<a class="shc-cta--ghost" href="<?php echo esc_url( $cta2_href ); ?>"<?php echo $cta2_is_file ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
						<?php echo esc_html( $cta2_label ); ?>
						<?php echo $cta2_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>

			<div class="shca-device-layer">
				<?php
				foreach ( $pairs as $index => $pair ) {
					echo cropx_hca_render_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'device',
						(int) ( $pair['deviceId'] ?? 0 ),
						$pair['deviceUrl'] ?? '',
						(int) ( $pair['deviceScale'] ?? 100 ),
						(int) ( $pair['deviceOffsetX'] ?? 0 ),
						(int) ( $pair['deviceOffsetY'] ?? 0 ),
						$pair_animations[ $index ]['device'],
						$theme_uri
					);
				}
				?>
			</div>

			<!--
				Quadratic bezier swoop — identical to hero-curved-standard. See that
				block's render.php for the full geometry explanation.
			-->
			<div class="shc-swoop" aria-hidden="true">
				<svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg"
				     preserveAspectRatio="none">
					<defs>
						<linearGradient id="shca-curve-flow-<?php echo esc_attr( $segment ); ?>"
						                x1="0" y1="0" x2="2880" y2="0"
						                gradientUnits="userSpaceOnUse">
							<stop offset="0%"   stop-color="<?php echo $stop_base; ?>"/>
							<stop offset="25%"  stop-color="<?php echo $stop_dark; ?>"/>
							<stop offset="50%"  stop-color="<?php echo $stop_base; ?>"/>
							<stop offset="75%"  stop-color="<?php echo $stop_dark; ?>"/>
							<stop offset="100%" stop-color="<?php echo $stop_base; ?>"/>
							<animateTransform attributeName="gradientTransform" type="translate"
							  values="0,0; -1440,0; 0,0" dur="14s"
							  repeatCount="indefinite" calcMode="linear"/>
						</linearGradient>
					</defs>
					<path class="shc-fill-path" d="M0,80 Q720,80 1440,30 L1440,80 L0,80 Z"/>
					<path d="M0,70 Q720,70 1440,20" fill="none"
					      stroke="url(#shca-curve-flow-<?php echo esc_attr( $segment ); ?>)"
					      stroke-width="20" stroke-linecap="round"/>
				</svg>
			</div>
		</section>

		<div class="shca-phone-layer">
			<?php
			foreach ( $pairs as $index => $pair ) {
				echo cropx_hca_render_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'phone',
					(int) ( $pair['phoneId'] ?? 0 ),
					$pair['phoneUrl'] ?? '',
					(int) ( $pair['phoneScale'] ?? 100 ),
					(int) ( $pair['phoneOffsetX'] ?? 0 ),
					(int) ( $pair['phoneOffsetY'] ?? 0 ),
					$pair_animations[ $index ]['phone'],
					$theme_uri
				);
			}
			?>
		</div>
	</div>

</div>
