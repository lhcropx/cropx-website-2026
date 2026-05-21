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
$eyebrow        = $attributes['eyebrow']       ?? '';
$heading        = $attributes['heading']       ?? '';
$body           = $attributes['body']          ?? '';
$cta_label      = $attributes['ctaLabel']      ?? '';
$cta_url        = $attributes['ctaUrl']        ?? '#';
$cta_style      = $attributes['ctaStyle']      ?? 'button';
$photo_id       = $attributes['photoId']       ?? 0;
$photo_url      = $attributes['photoUrl']      ?? '';
$photo_alt      = $attributes['photoAlt']      ?? '';
$eyebrow_color  = $attributes['eyebrowColor']  ?? 'cropx-blue';

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

// Build section class.
$section_class = 'tcv-section';
if ( 'left' === $photo_position )    { $section_class .= ' tcv-section--visual-left'; }
if ( 'png'  === $visual_type )       { $section_class .= ' tcv-section--png'; }
if ( 'general' !== $segment_accent ) { $section_class .= ' tcv-segment-' . $segment_accent; }

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => $section_class ) );

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
$visual_img = '';
if ( $photo_id ) {
	$visual_img = wp_get_attachment_image( $photo_id, 'full', false, array(
		'class'   => esc_attr( $img_class ),
		'alt'     => esc_attr( $photo_alt ),
		'loading' => 'lazy',
	) );
} elseif ( $photo_url ) {
	$visual_img = '<img class="' . esc_attr( $img_class ) . '" src="' . esc_url( $photo_url ) . '" alt="' . esc_attr( $photo_alt ) . '" loading="lazy">';
}
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="tcv-inner">
		<div class="tcv-grid">

			<div class="tcv-content">
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

				<?php if ( $eyebrow ) : ?>
					<span class="tcv-eyebrow" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></span>
				<?php endif; ?>

				<?php if ( $heading ) : ?>
					<h2 class="tcv-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
				<?php endif; ?>

				<?php if ( $body ) : ?>
					<p class="tcv-body"><?php echo wp_kses( $body, $allowed_body ); ?></p>
				<?php endif; ?>

				<?php if ( $cta_label ) : ?>
					<?php if ( 'link' === $cta_style ) : ?>
						<a href="<?php echo esc_url( $cta_url ); ?>" class="tcv-link">
							<?php echo esc_html( $cta_label ); ?>
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
								<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( $cta_url ); ?>" class="tcv-cta">
							<?php echo esc_html( $cta_label ); ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<div class="tcv-visual-col">
				<?php if ( $visual_img ) : ?>
					<?php echo $visual_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
			</div>

		</div>
	</div>
</section>
