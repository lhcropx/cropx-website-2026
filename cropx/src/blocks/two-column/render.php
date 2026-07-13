<?php
/**
 * Two-Column Text + Visual block — front-end render.
 *
 * visualType controls visual column rendering:
 *   photo → <img> with border-radius (var(--radius-photo)), box-shadow, aspect-ratio 5/4
 *   png   → <img> with drop-shadow filter, negative margin bleed, no border-radius
 *
 * photoPosition (right/left) drives .tcv-section--visual-left.
 * segmentAccent drives .tcv-segment-{accent} for icon box tinting.
 *
 * CTA is a primary button (Deep Blue fill), not a text link.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$visual_type    = $attributes['visualType']    ?? 'photo';
$photo_position = $attributes['photoPosition'] ?? 'right';
$segment_accent = $attributes['segmentAccent'] ?? 'general';
$icon           = $attributes['icon']          ?? 'fields';
$show_icon      = (bool) ( $attributes['showIcon']    ?? true );
$show_eyebrow   = (bool) ( $attributes['showEyebrow'] ?? true );
$show_cta       = (bool) ( $attributes['showCta']     ?? true );
$float_image    = (bool) ( $attributes['floatImage']  ?? false );
$eyebrow        = $attributes['eyebrow']       ?? '';
$heading        = $attributes['heading']       ?? '';
$body           = $attributes['body']          ?? '';
$cta_label      = $attributes['ctaLabel']      ?? '';
$cta_url        = $attributes['ctaUrl']        ?? '#';
$cta_style      = $attributes['ctaStyle']      ?? 'button';
$photo_id       = $attributes['photoId']       ?? 0;
$photo_url      = $attributes['photoUrl']      ?? '';
$photo_alt      = $attributes['photoAlt']      ?? '';
$photo_focal_x  = isset( $attributes['photoFocalX'] ) ? round( (float) $attributes['photoFocalX'] * 100, 1 ) : 50;
$photo_focal_y  = isset( $attributes['photoFocalY'] ) ? round( (float) $attributes['photoFocalY'] * 100, 1 ) : 50;
$photo_zoom     = isset( $attributes['photoZoom'] ) ? (float) $attributes['photoZoom'] : 100;
$photo_aspect_ratio = $attributes['photoAspectRatio'] ?? '4/3';
// Allowed ratios — anything else falls back to natural (height:100%) mode.
$allowed_ratios = array( '16/9', '3/2', '4/3', '1/1', '3/4' );
$is_ratio       = 'photo' === $visual_type && in_array( $photo_aspect_ratio, $allowed_ratios, true );
$eyebrow_color  = $attributes['eyebrowColor']  ?? 'cropx-blue';
$bg_color       = $attributes['bgColor'] ?? 'white';
if ( ! in_array( $bg_color, array( 'taupe', 'white', 'deep-blue' ), true ) ) {
	$bg_color = 'white';
}

// Validate enums.
if ( ! in_array( $visual_type, array( 'photo', 'png' ), true ) ) {
	$visual_type = 'photo';
}
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

$mobile_stack = $attributes['mobileStack'] ?? 'visual-first';

// Build section class.
$section_class = 'tcv-section';
if ( 'left' === $photo_position )    { $section_class .= ' tcv-section--visual-left'; }
if ( 'png'  === $visual_type )       { $section_class .= ' tcv-section--png'; }
if ( $float_image )                  { $section_class .= ' tcv-section--float'; }
if ( 'general' !== $segment_accent ) { $section_class .= ' tcv-segment-' . $segment_accent; }
if ( $is_ratio )                      { $section_class .= ' tcv-section--photo-ratio'; }
if ( 'text-first' === $mobile_stack ) { $section_class .= ' tcv-section--mobile-text-first'; }
$section_class .= ' tcv-section--bg-' . $bg_color;

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => $section_class, 'data-section-bg' => $bg_color ) );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
$allowed_body = array_merge( $allowed_inline, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

// Resolve image markup — prefer attachment ID for auto srcset.
$img_class  = 'photo' === $visual_type
	? 'tcv-visual-img tcv-visual-img--photo'
	: 'tcv-visual-img tcv-visual-img--png';

// Focal point + zoom only apply to photo (not transparent PNG).
$img_style = '';
if ( 'photo' === $visual_type ) {
	$img_style = sprintf(
		'object-position: %s%% %s%%; transform: scale(%s); transform-origin: %s%% %s%%;',
		esc_attr( $photo_focal_x ),
		esc_attr( $photo_focal_y ),
		esc_attr( number_format( $photo_zoom / 100, 4, '.', '' ) ),
		esc_attr( $photo_focal_x ),
		esc_attr( $photo_focal_y )
	);
}

$visual_img = '';
if ( $photo_id ) {
	$img_attrs = array(
		'class'   => esc_attr( $img_class ),
		'alt'     => esc_attr( $photo_alt ),
		'loading' => 'lazy',
	);
	if ( $img_style ) {
		$img_attrs['style'] = $img_style;
	}
	$visual_img = wp_get_attachment_image( $photo_id, 'full', false, $img_attrs );
} elseif ( $photo_url ) {
	$style_attr = $img_style ? ' style="' . esc_attr( $img_style ) . '"' : '';
	$visual_img = '<img class="' . esc_attr( $img_class ) . '" src="' . esc_url( $photo_url ) . '" alt="' . esc_attr( $photo_alt ) . '" loading="lazy"' . $style_attr . '>';
}
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="tcv-inner">
		<div class="tcv-grid">

			<div class="tcv-content">
				<?php if ( $show_icon ) : ?>
				<div class="tcv-icon-wrap">
					<div class="tcv-icon" aria-hidden="true">
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
				// $content holds the serialized inner blocks HTML (paragraphs, lists, etc.)
				// Fall back to legacy $body attribute so existing blocks keep their content.
				$has_inner = ! empty( trim( strip_tags( $content ) ) );
				if ( $has_inner ) :
				?>
					<div class="section-body"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php elseif ( $body ) : ?>
					<div class="section-body"><?php echo wp_kses_post( $body ); ?></div>
				<?php endif; ?>

				<?php if ( $cta_label && $show_cta ) : ?>
					<?php if ( 'link' === $cta_style ) : ?>
						<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="tcv-link">
							<?php echo esc_html( $cta_label ); ?>
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
								<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="tcv-cta">
							<?php echo esc_html( $cta_label ); ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<div class="tcv-visual-col">
				<?php if ( $visual_img ) : ?>
					<?php if ( 'photo' === $visual_type ) : ?>
						<div class="tcv-photo-wrap<?php echo $is_ratio ? ' tcv-photo-wrap--ratio' : ''; ?>"<?php
							if ( $is_ratio ) {
								echo ' style="aspect-ratio:' . esc_attr( $photo_aspect_ratio ) . '"';
							}
						?>>
							<?php echo $visual_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					<?php else : ?>
						<?php echo $visual_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endif; ?>
				<?php endif; ?>
			</div>

		</div>
	</div>
</section>
