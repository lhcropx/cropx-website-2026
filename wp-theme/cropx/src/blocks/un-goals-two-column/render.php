<?php
/**
 * UN Goals - Two-Column Text + Visual block — front-end render.
 *
 * Same layout as cropx/two-column, but the icon slot is a custom uploaded
 * image (e.g. a UN SDG goal badge) instead of a picked icon slug — it
 * renders at its own colors with no accent-tinted background and no
 * white filter, inside a 76x76 rounded box.
 *
 * visualType controls visual column rendering:
 *   photo → <img> with border-radius (var(--radius-photo)), box-shadow, aspect-ratio 5/4
 *   png   → <img> with drop-shadow filter, negative margin bleed, no border-radius
 *
 * photoPosition (right/left) drives .ugp-section--visual-left.
 * segmentAccent drives .ugp-segment-{accent} for CTA/segment-aware accents
 * only — it does not tint the icon image, which always renders as uploaded.
 *
 * CTA is a primary button (Deep Blue fill) by default, or a text link.
 * Either style can optionally open a video in a full-screen lightbox
 * instead of linking to a URL — see $cta_has_video below and view.js,
 * which wires the click into the shared cropx-vid-lightbox overlay (the
 * same one the Video and Two-Column Video blocks use).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$visual_type     = $attributes['visualType']    ?? 'photo';
$photo_position  = $attributes['photoPosition'] ?? 'right';
$segment_accent  = $attributes['segmentAccent'] ?? 'general';
$icon_image_id   = (int) ( $attributes['iconImageId']  ?? 0 );
$icon_image_url  = $attributes['iconImageUrl']  ?? '';
$icon_image_alt  = $attributes['iconImageAlt']  ?? '';
$show_icon       = (bool) ( $attributes['showIcon']    ?? true );
$show_eyebrow    = (bool) ( $attributes['showEyebrow'] ?? true );
$show_cta        = (bool) ( $attributes['showCta']     ?? true );
$float_image     = (bool) ( $attributes['floatImage']  ?? false );
$eyebrow         = $attributes['eyebrow']       ?? '';
$heading         = $attributes['heading']       ?? '';
$body            = $attributes['body']          ?? '';
$cta_label       = $attributes['ctaLabel']      ?? '';
$cta_url         = $attributes['ctaUrl']        ?? '#';
$cta_style       = $attributes['ctaStyle']      ?? 'button';
$cta_opens_video    = (bool) ( $attributes['ctaOpensVideo']   ?? false );
$cta_video_source   = $attributes['ctaVideoSource']  ?? 'url';
if ( ! in_array( $cta_video_source, array( 'url', 'media' ), true ) ) {
	$cta_video_source = 'url';
}
$cta_video_url      = $attributes['ctaVideoUrl']      ?? '';
$cta_video_media_id = (int) ( $attributes['ctaVideoMediaId']  ?? 0 );
$cta_video_media_src = $attributes['ctaVideoMediaSrc'] ?? '';

// Resolve the actual video src to use, preferring the attachment ID (same
// pattern as the photo/icon image handling above) when in media mode.
$cta_video_is_media = 'media' === $cta_video_source;
$cta_video_src      = '';
if ( $cta_opens_video ) {
	if ( $cta_video_is_media ) {
		if ( $cta_video_media_id ) {
			$attachment_url = wp_get_attachment_url( $cta_video_media_id );
			if ( $attachment_url ) {
				$cta_video_src = $attachment_url;
			}
		}
		if ( ! $cta_video_src && $cta_video_media_src ) {
			$cta_video_src = $cta_video_media_src;
		}
	} else {
		$cta_video_src = $cta_video_url;
	}
}
$cta_has_video = $cta_opens_video && '' !== trim( $cta_video_src );
$photo_id        = $attributes['photoId']       ?? 0;
$photo_url       = $attributes['photoUrl']      ?? '';
$photo_alt       = $attributes['photoAlt']      ?? '';
$photo_focal_x   = isset( $attributes['photoFocalX'] ) ? round( (float) $attributes['photoFocalX'] * 100, 1 ) : 50;
$photo_focal_y   = isset( $attributes['photoFocalY'] ) ? round( (float) $attributes['photoFocalY'] * 100, 1 ) : 50;
$photo_zoom      = isset( $attributes['photoZoom'] ) ? (float) $attributes['photoZoom'] : 100;
$photo_aspect_ratio = $attributes['photoAspectRatio'] ?? '4/3';
// Allowed ratios — anything else falls back to natural (height:100%) mode.
$allowed_ratios  = array( '16/9', '3/2', '4/3', '1/1', '3/4' );
$is_ratio        = 'photo' === $visual_type && in_array( $photo_aspect_ratio, $allowed_ratios, true );
$eyebrow_color   = $attributes['eyebrowColor']  ?? 'cropx-blue';
$bg_color        = $attributes['bgColor'] ?? 'white';
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

$mobile_stack = $attributes['mobileStack'] ?? 'visual-first';

// Build section class.
$section_class = 'ugp-section';
if ( 'left' === $photo_position )    { $section_class .= ' ugp-section--visual-left'; }
if ( 'png'  === $visual_type )       { $section_class .= ' ugp-section--png'; }
if ( $float_image )                  { $section_class .= ' ugp-section--float'; }
if ( 'general' !== $segment_accent ) { $section_class .= ' ugp-segment-' . $segment_accent; }
if ( $is_ratio )                      { $section_class .= ' ugp-section--photo-ratio'; }
if ( 'text-first' === $mobile_stack ) { $section_class .= ' ugp-section--mobile-text-first'; }
$section_class .= ' ugp-section--bg-' . $bg_color;

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => $section_class, 'data-section-bg' => $bg_color ) );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
$allowed_body = array_merge( $allowed_inline, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

// Resolve icon image markup — prefer attachment ID for auto srcset, same
// pattern as the main visual image below.
$icon_img = '';
if ( $show_icon ) {
	if ( $icon_image_id ) {
		$icon_img = wp_get_attachment_image( $icon_image_id, 'thumbnail', false, array(
			'class'   => 'ugp-icon-img',
			'alt'     => esc_attr( $icon_image_alt ),
			'loading' => 'lazy',
		) );
	} elseif ( $icon_image_url ) {
		$icon_img = '<img class="ugp-icon-img" src="' . esc_url( $icon_image_url ) . '" alt="' . esc_attr( $icon_image_alt ) . '" loading="lazy">';
	}
}

// Resolve visual image markup — prefer attachment ID for auto srcset.
$img_class  = 'photo' === $visual_type
	? 'ugp-visual-img ugp-visual-img--photo'
	: 'ugp-visual-img ugp-visual-img--png';

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
	<div class="ugp-inner">
		<div class="ugp-grid">

			<div class="ugp-content">
				<?php if ( $icon_img ) : ?>
				<div class="ugp-icon-wrap">
					<div class="ugp-icon-box" aria-hidden="true">
						<?php echo $icon_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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
					<?php if ( $cta_has_video ) : ?>
						<?php
						$video_data_attr = $cta_video_is_media ? 'data-media-src' : 'data-video-url';
						$video_aria_label = wp_strip_all_tags( $cta_label ) . ' — opens video';
						?>
						<?php if ( 'link' === $cta_style ) : ?>
							<button type="button" class="ugp-link ugp-link--video" <?php echo esc_attr( $video_data_attr ); ?>="<?php echo esc_url( $cta_video_src ); ?>" aria-label="<?php echo esc_attr( $video_aria_label ); ?>">
								<?php echo esc_html( $cta_label ); ?>
								<svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor" aria-hidden="true">
									<path d="M3 1.5v11l9-5.5-9-5.5z"/>
								</svg>
							</button>
						<?php else : ?>
							<button type="button" class="ugp-cta ugp-cta--video" <?php echo esc_attr( $video_data_attr ); ?>="<?php echo esc_url( $cta_video_src ); ?>" aria-label="<?php echo esc_attr( $video_aria_label ); ?>">
								<?php echo esc_html( $cta_label ); ?>
							</button>
						<?php endif; ?>
					<?php elseif ( 'link' === $cta_style ) : ?>
						<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="ugp-link">
							<?php echo esc_html( $cta_label ); ?>
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
								<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="ugp-cta">
							<?php echo esc_html( $cta_label ); ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<div class="ugp-visual-col">
				<?php if ( $visual_img ) : ?>
					<?php if ( 'photo' === $visual_type ) : ?>
						<div class="ugp-photo-wrap<?php echo $is_ratio ? ' ugp-photo-wrap--ratio' : ''; ?>"<?php
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
