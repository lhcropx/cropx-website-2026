<?php
/**
 * Two-Column + Overlay block — front-end render.
 *
 * photoPosition (right/left) drives .tco-section--photo-left.
 * segmentAccent drives .tco-segment-{accent} for icon box tinting.
 *
 * Both photo and overlay are rendered as background-image divs.
 *
 * overlayPosition is one of 8 fixed presets — 4 edges + 4 corners, no
 * "centered/contained" option — and drives a tco-overlay--{position}
 * modifier class; style.css's per-position rules hang the overlay a fixed
 * 40px off that edge/corner (top/left/right/bottom, plus a translate() for
 * centering on the perpendicular axis where relevant). overlayScale (0-100)
 * sizes the overlay as a percentage of the photo's own height (100% = same
 * height as the photo), written inline as --tco-overlay-scale.
 *
 * Size is capped (here AND in edit.js, belt-and-suspenders) so the overlay
 * never gets close enough to the far edge/corner to violate the "at least
 * 40px from the far side" rule — see the comment above $needs_width_cap.
 *
 * overlayFullBleed (bool) only means anything when overlayPosition is
 * 'left' or 'right' — it also hangs the overlay 40px off the top AND
 * bottom, adding a tco-overlay--full-bleed modifier class. Since that locks
 * height independent of Scale, Size switches to controlling width instead
 * for this combination — see the comment above $is_full_bleed. The overlay
 * image itself is NEVER cropped in this or any other combination — it only
 * ever fits within its frame (background-size: contain in style.css).
 *
 * When the overlay hangs off the edge nearest the text column (right edge
 * with photo-left/text-right, or left edge with photo-right/text-left),
 * the text column gets a tco-content--nudge-{left|right} modifier class so
 * its near boundary steps 40px clear of the overlay rather than letting it
 * bleed over the text.
 *
 * Whenever the overlay hangs (or full-bleeds) over the photo's top or
 * bottom edge, the section gets a tco-section--bleed-{top|bottom} modifier
 * class, adding an extra 40px of section padding on that side — see
 * $overlay_bleeds_top/_bottom below.
 *
 * overlayCornerRadius (0-20, default 10) is written inline as
 * --tco-overlay-radius (px), rounding all 4 corners of the overlay PNG's
 * own box — purely cosmetic, independent of position/full-bleed/size.
 *
 * --tco-overlay-ratio (the overlay's real width/height) is written inline
 * too, resolved fresh from the attachment on every render. It drives the
 * CSS aspect-ratio always, and below the mobile breakpoint it also drives
 * which side (width or height) the 500px ceiling applies to — see the
 * calc()/max() formula in style.css.
 *
 * Photo/overlay URLs are resolved at render time from the attachment ID
 * so media-library edits propagate without re-saving the block.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$photo_position   = $attributes['photoPosition']   ?? 'right';
$segment_accent   = $attributes['segmentAccent']   ?? 'general';
$icon             = $attributes['icon']             ?? 'fields';
$show_icon        = (bool) ( $attributes['showIcon']    ?? true );
$show_eyebrow     = (bool) ( $attributes['showEyebrow'] ?? true );
$show_cta         = (bool) ( $attributes['showCta']     ?? true );
$eyebrow          = $attributes['eyebrow']          ?? '';
$eyebrow_color    = $attributes['eyebrowColor']     ?? 'cropx-blue';
$heading          = $attributes['heading']          ?? '';
$body             = $attributes['body']             ?? '';
$cta_label        = $attributes['ctaLabel']         ?? '';
$cta_url          = $attributes['ctaUrl']           ?? '#';
$cta_style        = $attributes['ctaStyle']         ?? 'button';
$photo_id         = (int) ( $attributes['photoId']   ?? 0 );
$photo_url        = $attributes['photoUrl']          ?? '';
$photo_alt        = $attributes['photoAlt']          ?? '';
$overlay_id       = (int) ( $attributes['overlayId'] ?? 0 );
$overlay_url      = $attributes['overlayUrl']        ?? '';
$overlay_alt      = $attributes['overlayAlt']        ?? '';
$overlay_width    = (int) ( $attributes['overlayWidth']  ?? 0 );
$overlay_height   = (int) ( $attributes['overlayHeight'] ?? 0 );
$overlay_scale     = (int) ( $attributes['overlayScale'] ?? 75 );
$overlay_position  = $attributes['overlayPosition'] ?? 'left';
$overlay_full_bleed = (bool) ( $attributes['overlayFullBleed'] ?? false );
$overlay_corner_radius = (int) ( $attributes['overlayCornerRadius'] ?? 10 );
$bg_color         = $attributes['bgColor'] ?? 'white';
if ( ! in_array( $bg_color, array( 'taupe', 'white', 'deep-blue' ), true ) ) {
	$bg_color = 'white';
}
$photo_focal_x    = isset( $attributes['photoFocalX'] ) ? round( (float) $attributes['photoFocalX'] * 100, 1 ) : 50;
$photo_focal_y    = isset( $attributes['photoFocalY'] ) ? round( (float) $attributes['photoFocalY'] * 100, 1 ) : 50;
$photo_zoom       = isset( $attributes['photoZoom'] ) ? (float) $attributes['photoZoom'] : 100;

// Validate enums.
if ( ! in_array( $photo_position, array( 'right', 'left' ), true ) ) {
	$photo_position = 'right';
}
if ( ! in_array( $segment_accent, array( 'general', 'enterprise', 'service-provider', 'on-farm' ), true ) ) {
	$segment_accent = 'general';
}
$allowed_icons = array( 'alarm-clock', 'antenna', 'corn', 'field-sun', 'fields', 'language', 'nutrition', 'sensor-cloud', 'speed', 'valve-irrigation' );
if ( ! in_array( $icon, $allowed_icons, true ) ) {
	$icon = 'fields';
}
$overlay_scale = max( 1, min( 100, $overlay_scale ) );
$overlay_corner_radius = max( 0, min( 20, $overlay_corner_radius ) );
$allowed_overlay_positions = array( 'left', 'right', 'top', 'bottom', 'top-left', 'top-right', 'bottom-left', 'bottom-right' );
if ( ! in_array( $overlay_position, $allowed_overlay_positions, true ) ) {
	$overlay_position = 'left';
}

// Resolve photo URL at render time — auto-updates if the attachment is edited.
if ( $photo_id ) {
	$src = wp_get_attachment_image_src( $photo_id, 'full' );
	if ( $src ) {
		$photo_url = $src[0];
	}
}

// Resolve overlay URL — same approach. Also refresh width/height from the
// attachment itself (rather than trusting the stored attribute) so blocks
// saved before overlayWidth/overlayHeight existed still get a correct
// aspect ratio without needing to be re-saved in the editor.
if ( $overlay_id ) {
	$src = wp_get_attachment_image_src( $overlay_id, 'full' );
	if ( $src ) {
		$overlay_url    = $src[0];
		$overlay_width  = (int) $src[1];
		$overlay_height = (int) $src[2];
	}
}
$overlay_ratio = ( $overlay_width > 0 && $overlay_height > 0 )
	? round( $overlay_width / $overlay_height, 4 )
	: null;

// Full bleed only ever combines with 'left' or 'right' — also hanging the
// overlay 40px off the top and bottom, which locks its height to the
// photo's own height + 80px (via CSS top+bottom rather than the Scale-
// driven height everywhere else). Once height is locked independently of
// Scale, Size switches to controlling WIDTH directly (0-100% of the
// photo's own width) instead — and no dynamic cap is needed for it, because
// at Scale=100% the far edge lands EXACTLY 40px inside the photo's opposite
// edge (satisfying the minimum-gap rule at its boundary), and any lower
// Scale only adds more room. See the matching .tco-overlay--full-bleed
// rule in style.css.
$is_full_bleed = $overlay_full_bleed && in_array( $overlay_position, array( 'left', 'right' ), true );

// Mirrors edit.js's dynamicSizeMax exactly. On any of the 6 (non-full-bleed)
// presets that touch a left/right side, the fixed 40px hang and the
// required 40px minimum gap from the far edge are the same constant, so
// they cancel out to one clean rule: overlay width can never exceed photo
// width. That's a pure ratio (both scale together with the same fluid
// column), so it's safe to enforce here even though the photo's actual
// on-screen size is fluid/unknown to PHP. 125 = 100 / 0.8, where 0.8 is
// .tco-photo's fixed aspect-ratio (5/4) expressed as height/width — see the
// matching constant in style.css.
$needs_width_cap = ! $is_full_bleed && in_array( $overlay_position, array( 'left', 'right', 'top-left', 'top-right', 'bottom-left', 'bottom-right' ), true );
if ( $needs_width_cap && null !== $overlay_ratio && $overlay_ratio > 0 ) {
	$scale_max_for_width = 125 / $overlay_ratio;
	$overlay_scale = min( $overlay_scale, max( 10, (int) floor( $scale_max_for_width ) ) );
}

// Text-column nudge: when the overlay hangs off the edge nearest the text
// column, push that boundary 40px clear so the overlay doesn't bleed over
// the text. photoPosition 'left' means text is on the RIGHT (so a
// right-hanging overlay threatens it); the default 'right' means text is
// on the LEFT (so a left-hanging overlay threatens it). Independent of
// overlayFullBleed — the hang amount on that edge is the same fixed 40px
// either way.
$overlay_touches_right = in_array( $overlay_position, array( 'right', 'top-right', 'bottom-right' ), true );
$overlay_touches_left  = in_array( $overlay_position, array( 'left', 'top-left', 'bottom-left' ), true );
$content_nudge_class = '';
if ( $overlay_url ) {
	if ( 'left' === $photo_position && $overlay_touches_right ) {
		$content_nudge_class = ' tco-content--nudge-left';
	} elseif ( 'right' === $photo_position && $overlay_touches_left ) {
		$content_nudge_class = ' tco-content--nudge-right';
	}
}

// Block-edge spacing: whenever the overlay hangs (or full-bleeds) over the
// photo's top or bottom edge, the section needs an extra 40px of padding on
// that side — on top of whatever gap the grid's own vertical centering
// already gives it, not a minimum/cap. Full bleed always hangs both top AND
// bottom regardless of which of left/right it's paired with.
$overlay_bleeds_top    = $overlay_url && ( $is_full_bleed || in_array( $overlay_position, array( 'top', 'top-left', 'top-right' ), true ) );
$overlay_bleeds_bottom = $overlay_url && ( $is_full_bleed || in_array( $overlay_position, array( 'bottom', 'bottom-left', 'bottom-right' ), true ) );

$mobile_stack = $attributes['mobileStack'] ?? 'visual-first';

// Build section class.
$section_class = 'tco-section';
if ( 'left' === $photo_position )     { $section_class .= ' tco-section--photo-left'; }
if ( 'general' !== $segment_accent )  { $section_class .= ' tco-segment-' . $segment_accent; }
if ( 'text-first' === $mobile_stack ) { $section_class .= ' tco-section--mobile-text-first'; }
if ( $overlay_bleeds_top )    { $section_class .= ' tco-section--bleed-top'; }
if ( $overlay_bleeds_bottom ) { $section_class .= ' tco-section--bleed-bottom'; }
$section_class .= ' tco-section--bg-' . $bg_color;

$wrapper_extra_attrs = array( 'class' => $section_class );
if ( 'deep-blue' === $bg_color ) {
	// See style.css's comment on .tco-section--bg-deep-blue::before — keeps
	// the drift-pattern SVG out of the compiled CSS bundle.
	$wrapper_extra_attrs['style'] = '--tco-pattern-url:url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ')';
}
$wrapper_attrs = get_block_wrapper_attributes( $wrapper_extra_attrs );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
$allowed_body = array_merge( $allowed_inline, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="tco-inner">
		<div class="tco-grid">

			<div class="tco-content<?php echo esc_attr( $content_nudge_class ); ?>">
				<?php if ( $show_icon ) : ?>
				<div class="tco-icon-wrap">
					<div class="tco-icon" aria-hidden="true">
						<img
							src="<?php echo esc_url( CROPX_THEME_URI . 'assets/icons/' . $icon . '.svg' ); ?>"
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
					<h2 class="section-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
				<?php endif; ?>

				<?php
				// $content holds serialized inner blocks (paragraphs, lists, etc.);
				// fall back to legacy $body attribute so existing blocks keep their content.
				$has_inner = ! empty( trim( strip_tags( $content ) ) );
				if ( $has_inner ) :
				?>
					<div class="section-body"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php elseif ( $body ) : ?>
					<div class="section-body"><?php echo wp_kses_post( $body ); ?></div>
				<?php endif; ?>

				<?php if ( $cta_label && $show_cta ) : ?>
					<?php if ( 'link' === $cta_style ) : ?>
						<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="tco-link">
							<?php echo esc_html( $cta_label ); ?>
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
								<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="tco-cta">
							<?php echo esc_html( $cta_label ); ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<div class="tco-visual">
				<?php if ( $photo_url ) : ?>
					<?php
					// Inner bg div gets background-image + zoom transform so the outer
					// .tco-photo frame (overflow:hidden + border-radius) clips the scaled
					// content without the frame itself growing.
					$tco_bg_style = sprintf(
						"background-image: url('%s'); background-position: %s%% %s%%; transform: scale(%s); transform-origin: %s%% %s%%;",
						esc_url( $photo_url ),
						esc_attr( $photo_focal_x ),
						esc_attr( $photo_focal_y ),
						esc_attr( number_format( $photo_zoom / 100, 4, '.', '' ) ),
						esc_attr( $photo_focal_x ),
						esc_attr( $photo_focal_y )
					);
					?>
					<div
						class="tco-photo"
						role="img"
						aria-label="<?php echo esc_attr( $photo_alt ); ?>"
					><div class="tco-photo-bg" style="<?php echo esc_attr( $tco_bg_style ); ?>"></div></div>

					<?php if ( $overlay_url ) : ?>
						<?php
						$overlay_style = sprintf(
							"background-image: url('%s'); --tco-overlay-scale: %d; --tco-overlay-radius: %dpx;",
							esc_url( $overlay_url ),
							$overlay_scale,
							$overlay_corner_radius
						);
						if ( null !== $overlay_ratio ) {
							$overlay_style .= ' --tco-overlay-ratio: ' . esc_attr( $overlay_ratio ) . ';';
						}
						?>
						<div
							class="tco-overlay tco-overlay--<?php echo esc_attr( $overlay_position ); ?><?php echo $is_full_bleed ? ' tco-overlay--full-bleed' : ''; ?>"
							role="img"
							aria-label="<?php echo esc_attr( $overlay_alt ); ?>"
							style="<?php echo esc_attr( $overlay_style ); ?>"
						></div>
					<?php endif; ?>
				<?php endif; ?>
			</div>

		</div>
	</div>
</section>
