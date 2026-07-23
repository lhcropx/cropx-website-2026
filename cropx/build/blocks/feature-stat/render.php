<?php
/**
 * Feature + Stat Card block — front-end render.
 *
 * 4 variants from 2 toggles:
 *   photoPosition: right (default) | left  → fstat-section--photo-left
 *   backgroundVariant: white | blue        → fstat-section--deep-blue
 *
 * --accent cascades from the section to the icon box background and the
 * stat card border. Default is --cropx-blue; segment classes override.
 *
 * Stat card BG flips automatically via CSS:
 *   White section  → card is Deep Blue, text is white
 *   Deep Blue section → card is white, text is Deep Blue
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$photo_position  = $attributes['photoPosition']     ?? 'right';
$bg_variant      = $attributes['backgroundVariant'] ?? 'white';
$segment_accent  = $attributes['segmentAccent']     ?? 'general';
$icon            = $attributes['icon']              ?? 'sensor-cloud';
$show_icon       = (bool) ( $attributes['showIcon']    ?? true );
$show_eyebrow    = (bool) ( $attributes['showEyebrow'] ?? true );
$show_cta        = (bool) ( $attributes['showCta']     ?? true );
$eyebrow         = $attributes['eyebrow']           ?? '';
$eyebrow_color   = $attributes['eyebrowColor']      ?? 'cropx-blue';
$heading         = $attributes['heading']           ?? '';
$body            = $attributes['body']              ?? '';
$cta_label       = $attributes['ctaLabel']          ?? '';
$cta_url         = $attributes['ctaUrl']            ?? '#';
$cta_style       = $attributes['ctaStyle']          ?? 'link';
$photo_id        = $attributes['photoId']           ?? 0;
$photo_url       = $attributes['photoUrl']          ?? '';
$photo_alt       = $attributes['photoAlt']          ?? '';
$photo_focal_x   = isset( $attributes['photoFocalX'] ) ? round( (float) $attributes['photoFocalX'] * 100, 1 ) : 50;
$photo_focal_y   = isset( $attributes['photoFocalY'] ) ? round( (float) $attributes['photoFocalY'] * 100, 1 ) : 50;
$photo_zoom      = isset( $attributes['photoZoom'] ) ? (float) $attributes['photoZoom'] : 100;
$card_context    = $attributes['cardContext']       ?? '';
$card_number     = $attributes['cardNumber']        ?? '';
$card_metric     = $attributes['cardMetric']        ?? '';

// Validate enums.
if ( ! in_array( $photo_position, array( 'right', 'left' ), true ) ) {
	$photo_position = 'right';
}
if ( ! in_array( $bg_variant, array( 'white', 'taupe', 'blue' ), true ) ) {
	$bg_variant = 'white';
}
if ( ! in_array( $segment_accent, array( 'general', 'enterprise', 'service-provider', 'on-farm' ), true ) ) {
	$segment_accent = 'general';
}
$allowed_icons = array( 'alarm-clock', 'antenna', 'corn', 'field-sun', 'fields', 'language', 'nutrition', 'sensor-cloud', 'speed', 'valve-irrigation' );
if ( ! in_array( $icon, $allowed_icons, true ) ) {
	$icon = 'sensor-cloud';
}

$mobile_stack = $attributes['mobileStack'] ?? 'visual-first';

// Build section class.
$section_class = 'fstat-section';
if ( 'left' === $photo_position )     { $section_class .= ' fstat-section--photo-left'; }
if ( 'blue' === $bg_variant )         { $section_class .= ' fstat-section--deep-blue';  }
if ( 'taupe' === $bg_variant )        { $section_class .= ' fstat-section--bg-taupe';   }
if ( 'general' !== $segment_accent )  { $section_class .= ' fstat-segment-' . $segment_accent; }
if ( 'text-first' === $mobile_stack ) { $section_class .= ' fstat-section--mobile-text-first'; }

$_fstat_attrs = array( 'class' => $section_class );
if ( 'white' === $bg_variant ) {
	$_fstat_attrs['data-section-bg'] = 'white';
}
$wrapper_attrs = get_block_wrapper_attributes( $_fstat_attrs );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
$allowed_body = array_merge( $allowed_inline, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

// Resolve photo markup — prefer attachment ID for auto srcset, fall back to raw URL.
$fstat_img_style = sprintf(
	'object-position: %s%% %s%%; transform: scale(%s); transform-origin: %s%% %s%%;',
	esc_attr( $photo_focal_x ),
	esc_attr( $photo_focal_y ),
	esc_attr( number_format( $photo_zoom / 100, 4, '.', '' ) ),
	esc_attr( $photo_focal_x ),
	esc_attr( $photo_focal_y )
);
$photo_img = '';
if ( $photo_id ) {
	$photo_img = wp_get_attachment_image( $photo_id, 'full', false, array(
		'alt'     => esc_attr( $photo_alt ),
		'loading' => 'lazy',
		'style'   => $fstat_img_style,
	) );
} elseif ( $photo_url ) {
	$photo_img = '<img src="' . esc_url( $photo_url ) . '" alt="' . esc_attr( $photo_alt ) . '" loading="lazy" style="' . esc_attr( $fstat_img_style ) . '">';
}
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="fstat-inner">

		<div class="fstat-content">
			<?php if ( $show_icon ) : ?>
			<div class="fstat-icon-wrap">
				<div class="fstat-icon" aria-hidden="true">
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
				<h2 class="fstat-h2"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
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
				<?php if ( 'button' === $cta_style ) : ?>
					<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="fstat-btn">
						<?php echo esc_html( $cta_label ); ?>
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="fstat-link">
						<?php echo esc_html( $cta_label ); ?>
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
							<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</a>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<div class="fstat-visual">
			<?php if ( $photo_img ) : ?>
				<div class="fstat-photo-wrap">
					<?php echo $photo_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<?php if ( $card_number ) : ?>
				<div class="fstat-card">
					<?php if ( $card_context ) : ?>
						<p class="fstat-card-context"><?php echo esc_html( $card_context ); ?></p>
					<?php endif; ?>
					<span class="fstat-card-num"><?php echo esc_html( $card_number ); ?></span>
					<?php if ( $card_metric ) : ?>
						<p class="fstat-card-metric"><?php echo esc_html( $card_metric ); ?></p>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>

	</div>
</section>
