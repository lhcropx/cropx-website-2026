<?php
/**
 * Two-Column + Overlay block — front-end render.
 *
 * photoPosition (right/left) drives .tco-section--photo-left.
 * overlayPosition (top/center/bottom) drives .tco-overlay--{position}.
 * segmentAccent drives .tco-segment-{accent} for icon box tinting.
 *
 * Both photo and overlay are rendered as background-image divs so the
 * offset-based overlay sizing model works correctly: width auto-resolves
 * from CSS left/right offsets, height is driven by aspect-ratio.
 *
 * overlayAnchor (int, treated as %) and bleedX (float, treated as rem)
 * are written as inline CSS variables on .tco-grid so the gap formula
 * and overlay offsets pick them up automatically.
 *
 * Photo/overlay URLs are resolved at render time from the attachment ID
 * so media-library edits propagate without re-saving the block.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$photo_position   = $attributes['photoPosition']   ?? 'right';
$overlay_position = $attributes['overlayPosition'] ?? 'center';
$segment_accent   = $attributes['segmentAccent']   ?? 'general';
$icon             = $attributes['icon']             ?? 'fields';
$eyebrow          = $attributes['eyebrow']          ?? '';
$heading          = $attributes['heading']          ?? '';
$body             = $attributes['body']             ?? '';
$cta_label        = $attributes['ctaLabel']         ?? '';
$cta_url          = $attributes['ctaUrl']           ?? '#';
$photo_id         = (int) ( $attributes['photoId']   ?? 0 );
$photo_url        = $attributes['photoUrl']          ?? '';
$photo_alt        = $attributes['photoAlt']          ?? '';
$overlay_id       = (int) ( $attributes['overlayId'] ?? 0 );
$overlay_url      = $attributes['overlayUrl']        ?? '';
$overlay_alt      = $attributes['overlayAlt']        ?? '';
$overlay_anchor   = (int) ( $attributes['overlayAnchor'] ?? 25 );
$bleed_x          = (float) ( $attributes['bleedX']      ?? 4 );

// Validate enums.
if ( ! in_array( $photo_position, array( 'right', 'left' ), true ) ) {
	$photo_position = 'right';
}
if ( ! in_array( $overlay_position, array( 'top', 'center', 'bottom' ), true ) ) {
	$overlay_position = 'center';
}
if ( ! in_array( $segment_accent, array( 'general', 'enterprise', 'service-provider', 'on-farm' ), true ) ) {
	$segment_accent = 'general';
}
$allowed_icons = array( 'alarm-clock', 'antenna', 'corn', 'field-sun', 'fields', 'language', 'nutrition', 'sensor-cloud', 'speed', 'valve-irrigation' );
if ( ! in_array( $icon, $allowed_icons, true ) ) {
	$icon = 'fields';
}
$overlay_anchor = max( 0, min( 50, $overlay_anchor ) );
$bleed_x        = max( 0.0, min( 8.0, $bleed_x ) );

// Resolve photo URL at render time — auto-updates if the attachment is edited.
if ( $photo_id ) {
	$src = wp_get_attachment_image_src( $photo_id, 'full' );
	if ( $src ) {
		$photo_url = $src[0];
	}
}

// Resolve overlay URL — same approach.
if ( $overlay_id ) {
	$src = wp_get_attachment_image_src( $overlay_id, 'full' );
	if ( $src ) {
		$overlay_url = $src[0];
	}
}

// Build section class.
$section_class = 'tco-section';
if ( 'left' === $photo_position )    { $section_class .= ' tco-section--photo-left'; }
if ( 'general' !== $segment_accent ) { $section_class .= ' tco-segment-' . $segment_accent; }

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => $section_class ) );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
$allowed_body = array_merge( $allowed_inline, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

// Grid inline styles — drives gap formula and overlay offset calculations.
$bleed_x_css    = rtrim( rtrim( number_format( $bleed_x, 1 ), '0' ), '.' );
$grid_style     = '--tco-anchor: ' . $overlay_anchor . '%; --tco-bleed-x: ' . $bleed_x_css . 'rem;';
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="tco-inner">
		<div class="tco-grid" style="<?php echo esc_attr( $grid_style ); ?>">

			<div class="tco-content">
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

				<?php if ( $eyebrow ) : ?>
					<span class="tco-eyebrow"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></span>
				<?php endif; ?>

				<?php if ( $heading ) : ?>
					<h2 class="tco-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
				<?php endif; ?>

				<?php if ( $body ) : ?>
					<p class="tco-body"><?php echo wp_kses( $body, $allowed_body ); ?></p>
				<?php endif; ?>

				<?php if ( $cta_label ) : ?>
					<a href="<?php echo esc_url( $cta_url ); ?>" class="tco-cta">
						<?php echo esc_html( $cta_label ); ?>
					</a>
				<?php endif; ?>
			</div>

			<div class="tco-visual">
				<?php if ( $photo_url ) : ?>
					<div
						class="tco-photo"
						role="img"
						aria-label="<?php echo esc_attr( $photo_alt ); ?>"
						style="background-image: url('<?php echo esc_url( $photo_url ); ?>');"
					></div>

					<?php if ( $overlay_url ) : ?>
						<div
							class="tco-overlay tco-overlay--<?php echo esc_attr( $overlay_position ); ?>"
							role="img"
							aria-label="<?php echo esc_attr( $overlay_alt ); ?>"
							style="background-image: url('<?php echo esc_url( $overlay_url ); ?>');"
						></div>
					<?php endif; ?>
				<?php endif; ?>
			</div>

		</div>
	</div>
</section>
