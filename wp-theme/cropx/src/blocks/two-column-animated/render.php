<?php
/**
 * Two-Column Text + Animated Image block — front-end render.
 *
 * Rebuilt (Aug 2026) on top of cropx/two-column-overlay's photo+overlay
 * model rather than the earlier transparent-PNG-rotation design — see
 * PROGRESS.md for why. Each "pair" is a background photo (same
 * aspect-ratio/focal-point/zoom treatment as two-column-overlay's .tco-photo)
 * with an overlay PNG anchored to it. Exactly one pair is ever visible at a
 * time; the rotation loops through all of them.
 *
 * Per pair, in order:
 *   1. Entrance (simultaneous): the photo cross-fades in from transparent;
 *      the overlay fades in and rises up from the photo's bottom edge, at
 *      the same time. This same fade-in window IS the previous pair's
 *      fade-out window (see Crossfade note below) — they're the same span
 *      of real time, just described from two different pairs' points of
 *      view.
 *   2. Hold: both sit still for pairHoldSeconds (editor-controlled, 4-6s).
 *   3. Overlay exit: the overlay sinks back down and fades out — the photo
 *      stays fully visible.
 *   4. Photo crossfade-out: only once the overlay has fully exited does
 *      this pair's photo start fading back to transparent, over the same
 *      $enter_s duration as step 1 — because the NEXT pair's delay is
 *      exactly one slot later, that pair's own step-1 fade-in lands in
 *      exactly this window, so the two dissolve into each other directly
 *      with no blank gap in between.
 *
 * This is driven by TWO separate per-instance keyframes sharing the same
 * total loop duration and per-pair delay (index x slot length) — the
 * technique already established on the Animated Device Hero and the first
 * cut of this block: because each keyframe is only "visible" during its own
 * local window and hidden for the rest of the loop, a positive delay simply
 * shifts when that window lands in real time, wrapping automatically via
 * animation-iteration-count: infinite.
 *   - $photo_kf (applied to .tcap-pair, the wrapper): opacity only — a
 *     previous version also slid the photo in/out via translateX, but per
 *     Lauren's Aug 2026 feedback this is now a straight dissolve instead
 *     (easier on the eye than a slide, and reads as "crossfading into" the
 *     next pair rather than one photo displacing another). Both the fade-in
 *     and fade-out segments use `linear` timing specifically so that, at
 *     any instant during the shared overlap window, outgoing-opacity +
 *     incoming-opacity == 1 — any other easing curve would make the two
 *     pairs' opacities NOT sum to a constant, which shows up as a visible
 *     dip or flash partway through the transition.
 *   - $overlay_kf (applied to .tcap-overlay itself): unchanged by the above —
 *     opacity + an ADDITIONAL translateY layered on top of the overlay's own
 *     static anchor position via the --tcap-ov-tx/-ty custom properties (see
 *     style.css) — composing both into one transform value inside the
 *     keyframe itself, since a keyframe's `transform` declaration would
 *     otherwise simply overwrite whatever static transform the anchor
 *     classes set.
 *
 * No JS is involved in any of this — same reasoning as every other animated
 * block in this theme: CSS animations composited on transform/opacity are
 * cheap, and a fixed set of editor-defined pairs doesn't need runtime
 * measurement the way, say, count-balanced columns do.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$photo_position = $attributes['photoPosition'] ?? 'right';
$segment_accent = $attributes['segmentAccent'] ?? 'general';
$icon           = $attributes['icon']          ?? 'fields';
$show_icon      = (bool) ( $attributes['showIcon']    ?? true );
$show_eyebrow   = (bool) ( $attributes['showEyebrow'] ?? true );
$show_cta       = (bool) ( $attributes['showCta']     ?? true );
$eyebrow        = $attributes['eyebrow']       ?? '';
$heading        = $attributes['heading']       ?? '';
$cta_label      = $attributes['ctaLabel']      ?? '';
$cta_url        = $attributes['ctaUrl']        ?? '#';
$cta_style      = $attributes['ctaStyle']      ?? 'button';
$eyebrow_color  = $attributes['eyebrowColor']  ?? 'cropx-blue';
$bg_color       = $attributes['bgColor'] ?? 'white';
if ( ! in_array( $bg_color, array( 'taupe', 'white', 'deep-blue' ), true ) ) {
	$bg_color = 'white';
}

if ( ! in_array( $photo_position, array( 'right', 'left' ), true ) ) {
	$photo_position = 'right';
}
if ( ! in_array( $segment_accent, array( 'general', 'enterprise', 'service-provider', 'on-farm' ), true ) ) {
	$segment_accent = 'general';
}
$allowed_icons = cropx_allowed_icon_slugs();
if ( ! in_array( $icon, $allowed_icons, true ) ) {
	$icon = 'fields';
}

$mobile_stack      = $attributes['mobileStack'] ?? 'visual-first';
$caption_alignment = $attributes['captionAlignment'] ?? 'left';
if ( ! in_array( $caption_alignment, array( 'left', 'center' ), true ) ) {
	$caption_alignment = 'left';
}
$caption_class = 'tcap-caption' . ( 'center' === $caption_alignment ? ' tcap-caption--centered' : '' );

$section_class = 'tcap-section';
if ( 'left' === $photo_position )     { $section_class .= ' tcap-section--visual-left'; }
if ( 'general' !== $segment_accent )  { $section_class .= ' tcap-segment-' . $segment_accent; }
if ( 'text-first' === $mobile_stack ) { $section_class .= ' tcap-section--mobile-text-first'; }
$section_class .= ' tcap-section--bg-' . $bg_color;

$theme_uri = CROPX_THEME_URI;

$wrapper_style = '';
if ( 'deep-blue' === $bg_color ) {
	// Same reasoning as every other block that carries the drift pattern —
	// see two-column-overlay's style.css: a relative url() here would get
	// base64-inlined into the compiled stylesheet by webpack.
	$wrapper_style = sprintf(
		'--tcap-pattern-url:url(%s)',
		esc_url( $theme_uri . 'assets/decorative/drift-pattern.svg' )
	);
}

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => $section_class,
	'style' => $wrapper_style,
) );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);

// ── Resolve pairs ────────────────────────────────────────────────────────
// Only pairs with a photo actually render anything (an overlay with no
// photo has nothing to anchor to) — filtered up front so timing/delay math
// only accounts for pairs that will actually appear.
$raw_pairs = is_array( $attributes['pairs'] ?? null ) ? array_values( $attributes['pairs'] ) : array();
$pairs     = array();

foreach ( $raw_pairs as $pair ) {
	$photo_id  = (int) ( $pair['photoId'] ?? 0 );
	$photo_url = $pair['photoUrl'] ?? '';

	if ( $photo_id ) {
		$src = wp_get_attachment_image_src( $photo_id, 'full' );
		if ( $src ) {
			$photo_url = $src[0];
		}
	}
	if ( ! $photo_url ) {
		continue; // Nothing to show for this pair.
	}

	$overlay_id     = (int) ( $pair['overlayId'] ?? 0 );
	$overlay_url    = $pair['overlayUrl']    ?? '';
	$overlay_width  = (int) ( $pair['overlayWidth']  ?? 0 );
	$overlay_height = (int) ( $pair['overlayHeight'] ?? 0 );

	if ( $overlay_id ) {
		$src = wp_get_attachment_image_src( $overlay_id, 'full' );
		if ( $src ) {
			$overlay_url    = $src[0];
			$overlay_width  = (int) $src[1];
			$overlay_height = (int) $src[2];
		}
	}

	// Three vertical options, FLUSH edge alignment only (no offset control) —
	// the "hang off top/bottom edge" options tried in an earlier round were
	// removed per Lauren's Aug 2026 feedback.
	$v_anchor = $pair['overlayVAnchor'] ?? 'middle';
	if ( ! in_array( $v_anchor, array( 'top', 'middle', 'bottom' ), true ) ) {
		$v_anchor = 'middle';
	}
	$h_anchor = $pair['overlayHAnchor'] ?? 'center';
	if ( ! in_array( $h_anchor, array( 'left', 'center', 'right' ), true ) ) {
		$h_anchor = 'center';
	}
	$h_offset = max( 0, min( 40, (int) ( $pair['overlayHOffset'] ?? 0 ) ) );
	$scale    = max( 20, min( 160, (int) ( $pair['overlayScale'] ?? 75 ) ) );

	$pairs[] = array(
		'photo_url'      => $photo_url,
		'photo_alt'      => $pair['photoAlt'] ?? '',
		'photo_focal_x'  => isset( $pair['photoFocalX'] ) ? round( (float) $pair['photoFocalX'] * 100, 1 ) : 50,
		'photo_focal_y'  => isset( $pair['photoFocalY'] ) ? round( (float) $pair['photoFocalY'] * 100, 1 ) : 50,
		'photo_zoom'     => isset( $pair['photoZoom'] ) ? (float) $pair['photoZoom'] : 100,
		'overlay_url'    => $overlay_url,
		'overlay_alt'    => $pair['overlayAlt'] ?? '',
		'overlay_ratio'  => ( $overlay_width > 0 && $overlay_height > 0 ) ? round( $overlay_width / $overlay_height, 4 ) : 1.4,
		'overlay_scale'  => $scale,
		'v_anchor'       => $v_anchor,
		'h_anchor'       => $h_anchor,
		'h_offset'       => $h_offset,
		'caption'        => $pair['caption'] ?? '',
	);
}

$pair_count = count( $pairs );

// ── Timing ───────────────────────────────────────────────────────────────
// Photos now cross-fade directly into one another instead of sliding, with
// no blank gap in between — so there's no separate "photo exit" duration or
// fixed gap any more. $slot_s is the spacing between one pair's delay and
// the next's; each pair's own crossfade-out window (at the END of its slot)
// is the SAME real-time window as the following pair's crossfade-in (at the
// START of its slot, one $slot_s later) — see the file header.
$enter_s        = 0.6;  // photo+overlay fade-in duration; doubles as the crossfade duration when this window is shared with the previous pair's fade-out
$overlay_exit_s = 0.4;  // overlay sink + fade duration — unchanged, still happens while the photo is held fully visible
$hold_s         = (float) ( $attributes['pairHoldSeconds'] ?? 5 );
if ( $hold_s < 4 ) {
	$hold_s = 4;
} elseif ( $hold_s > 6 ) {
	$hold_s = 6;
}

$slot_s  = $enter_s + $hold_s + $overlay_exit_s;
$total_s = $pair_count > 0 ? $slot_s * $pair_count : $slot_s;

$pct_enter_end         = round( $enter_s / $total_s * 100, 4 );
$pct_hold_end          = round( ( $enter_s + $hold_s ) / $total_s * 100, 4 );
$pct_overlay_exit_end  = round( ( $enter_s + $hold_s + $overlay_exit_s ) / $total_s * 100, 4 ); // == $slot_s / $total_s — this pair's own crossfade-out begins here
$pct_crossfade_out_end = round( ( $slot_s + $enter_s ) / $total_s * 100, 4 );

// Unique per-instance keyframe names — if this block appears more than once
// on a page, sharing one global keyframe name would let whichever
// instance's <style> renders last silently override the timing for every
// other instance (@keyframes are global by name).
$uid        = wp_unique_id( 'tcap-' );
$photo_kf   = 'tcap-photo-' . $uid;
$overlay_kf = 'tcap-overlay-' . $uid;

$rise_px = 56; // overlay's vertical travel distance

// Straight opacity dissolve — no transform. Both the fade-in ([[0, enter])
// and fade-out ([overlay_exit_end, crossfade_out_end]) segments are pinned
// to `linear` so they sum to a constant with the neighboring pair's mirror
// segment (see file header). The flat hold segment in between doesn't
// change opacity, so its own timing-function is irrelevant.
$photo_keyframes_css = sprintf(
	'@keyframes %1$s{'
	. '0%%{opacity:0;animation-timing-function:linear}'
	. '%2$s%%{opacity:1}'
	. '%3$s%%{opacity:1;animation-timing-function:linear}'
	. '%4$s%%{opacity:0}'
	. '100%%{opacity:0}'
	. '}',
	esc_attr( $photo_kf ),
	$pct_enter_end,
	$pct_overlay_exit_end,
	$pct_crossfade_out_end
);

$overlay_keyframes_css = sprintf(
	'@keyframes %1$s{'
	. '0%%{opacity:0;transform:translate(var(--tcap-ov-tx,0),calc(var(--tcap-ov-ty,0) + %2$dpx));animation-timing-function:cubic-bezier(.16,1,.3,1)}'
	. '%3$s%%{opacity:1;transform:translate(var(--tcap-ov-tx,0),var(--tcap-ov-ty,0));animation-timing-function:linear}'
	. '%4$s%%{opacity:1;transform:translate(var(--tcap-ov-tx,0),var(--tcap-ov-ty,0));animation-timing-function:cubic-bezier(.7,0,.84,0)}'
	. '%5$s%%{opacity:0;transform:translate(var(--tcap-ov-tx,0),calc(var(--tcap-ov-ty,0) + %2$dpx))}'
	. '100%%{opacity:0;transform:translate(var(--tcap-ov-tx,0),calc(var(--tcap-ov-ty,0) + %2$dpx))}'
	. '}',
	esc_attr( $overlay_kf ),
	$rise_px,
	$pct_enter_end,
	$pct_hold_end,
	$pct_overlay_exit_end
);

$heading_tags = array( 'em' => array(), 'strong' => array(), 'br' => array() );

// Captions allow links (per Lauren's Aug 2026 request) in addition to basic
// inline emphasis — unlike $heading_tags above, which deliberately excludes
// <a> since headings aren't meant to carry links.
$caption_tags = array(
	'a'      => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="tcap-inner">
		<div class="tcap-grid">

			<div class="tcap-content">
				<?php if ( $show_icon ) : ?>
				<div class="tcap-icon-wrap">
					<div class="tcap-icon" aria-hidden="true">
						<img
							src="<?php echo esc_url( $theme_uri . 'assets/icons/' . $icon . '.svg' ); ?>"
							alt=""
							width="28"
							height="28"
						>
					</div>
				</div>
				<?php endif; ?>

				<?php if ( $eyebrow && $show_eyebrow ) : ?>
					<span class="section-eyebrow" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></span>
				<?php endif; ?>

				<?php if ( $heading ) : ?>
					<h2 class="section-heading"><?php echo wp_kses( $heading, $heading_tags ); ?></h2>
				<?php endif; ?>

				<?php
				$has_inner = ! empty( trim( strip_tags( $content ) ) );
				if ( $has_inner ) :
				?>
					<div class="section-body"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php endif; ?>

				<?php if ( $cta_label && $show_cta ) : ?>
					<?php if ( 'link' === $cta_style ) : ?>
						<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="tcap-link">
							<?php echo esc_html( $cta_label ); ?>
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
								<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="tcap-cta">
							<?php echo esc_html( $cta_label ); ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<div class="tcap-visual">
				<?php if ( $pair_count > 0 ) : ?>
					<!-- Per-instance keyframes for this block's pair rotation — see
					     the file header for why these can't live in the shared
					     style.css. -->
					<style><?php echo $photo_keyframes_css . $overlay_keyframes_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></style>
					<?php foreach ( $pairs as $index => $pair ) :
						$delay_s          = $index * $slot_s;
						$shared_anim_tail = sprintf( '%ss linear %ss infinite backwards', number_format( $total_s, 3, '.', '' ), number_format( $delay_s, 3, '.', '' ) );
						$photo_animation  = esc_attr( $photo_kf . ' ' . $shared_anim_tail );
						$overlay_animation = esc_attr( $overlay_kf . ' ' . $shared_anim_tail );

						$photo_bg_style = sprintf(
							"background-image: url('%s'); background-position: %s%% %s%%; transform: scale(%s); transform-origin: %s%% %s%%;",
							esc_url( $pair['photo_url'] ),
							esc_attr( $pair['photo_focal_x'] ),
							esc_attr( $pair['photo_focal_y'] ),
							esc_attr( number_format( $pair['photo_zoom'] / 100, 4, '.', '' ) ),
							esc_attr( $pair['photo_focal_x'] ),
							esc_attr( $pair['photo_focal_y'] )
						);
						// Overlay hang compensation: when the overlay is set to hang past the
						// photo's left or right edge, indent the photo (and its .tcap-media
						// positioning box) by that same amount on that side, so the overlay's
						// outer edge lands flush with the original content boundary instead of
						// bumping past it. See .tcap-media--h-left/--h-right in style.css.
						$media_class = 'tcap-media';
						$media_attr  = '';
						if ( $pair['overlay_url'] && $pair['h_offset'] > 0 && in_array( $pair['h_anchor'], array( 'left', 'right' ), true ) ) {
							$media_class .= ' tcap-media--h-' . esc_attr( $pair['h_anchor'] );
							$media_attr   = ' style="--tcap-h-offset: ' . (int) $pair['h_offset'] . 'px;"';
						}
					?>
						<div class="tcap-pair" style="animation: <?php echo $photo_animation; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
							<div class="<?php echo esc_attr( $media_class ); ?>"<?php echo $media_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
								<div class="tcap-photo-crop">
									<div class="tcap-photo" role="img" aria-label="<?php echo esc_attr( $pair['photo_alt'] ); ?>">
										<div class="tcap-photo-bg" style="<?php echo esc_attr( $photo_bg_style ); ?>"></div>
									</div>
								</div>
								<?php if ( $pair['overlay_url'] ) :
									$overlay_style = sprintf(
										"background-image: url('%s'); --tcap-overlay-scale: %d; --tcap-overlay-ratio: %s; --tcap-h-offset: %dpx; animation: %s;",
										esc_url( $pair['overlay_url'] ),
										$pair['overlay_scale'],
										esc_attr( $pair['overlay_ratio'] ),
										$pair['h_offset'],
										$overlay_animation
									);
								?>
									<div
										class="tcap-overlay tcap-overlay--v-<?php echo esc_attr( $pair['v_anchor'] ); ?> tcap-overlay--h-<?php echo esc_attr( $pair['h_anchor'] ); ?>"
										role="img"
										aria-label="<?php echo esc_attr( $pair['overlay_alt'] ); ?>"
										style="<?php echo esc_attr( $overlay_style ); ?>"
									></div>
								<?php endif; ?>
							</div>
							<?php if ( trim( wp_strip_all_tags( $pair['caption'] ) ) !== '' ) : ?>
								<div class="<?php echo esc_attr( $caption_class ); ?>"><?php echo wp_kses( $pair['caption'], $caption_tags ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>

		</div>
	</div>
</section>
