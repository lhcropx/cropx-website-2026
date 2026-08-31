<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$segment    = $attributes['segment']    ?? 'enterprise';
$eyebrow    = $attributes['eyebrow']    ?? '';
$heading    = $attributes['heading']    ?? '';
$subheading = $attributes['subheading'] ?? '';
$cta_label  = $attributes['ctaLabel']   ?? '';
$cta_url    = $attributes['ctaUrl']     ?? '#';

$bg_image_id  = (int) ( $attributes['bgImageId']     ?? 0 );
$bg_image_url = $attributes['bgImageUrl']             ?? '';
$show_eyebrow      = (bool)($attributes['showEyebrow']     ?? true);
$show_cta          = (bool)($attributes['showCta']         ?? true);
$show_device_image = (bool)($attributes['showDeviceImage'] ?? true);
$secondary_label    = $attributes['secondaryLabel']    ?? '';
$secondary_url      = $attributes['secondaryUrl']      ?? '#';
$show_secondary_cta = (bool)($attributes['showSecondaryCta'] ?? false);
$show_app_image    = (bool)($attributes['showAppImage']    ?? true);
$device_id    = (int) ( $attributes['deviceImageId']  ?? 0 );
$device_url   = $attributes['deviceImageUrl']         ?? '';
$phone_id     = (int) ( $attributes['phoneImageId']   ?? 0 );
$phone_url    = $attributes['phoneImageUrl']          ?? '';

$allowed_segments = array( 'enterprise', 'service-provider', 'on-farm' );
if ( ! in_array( $segment, $allowed_segments, true ) ) {
	$segment = 'enterprise';
}

$badge_config = array(
	'enterprise'       => array( 'line1' => 'Enterprise',  'line2' => 'Solutions' ),
	'service-provider' => array( 'line1' => 'Service',     'line2' => 'Providers' ),
	'on-farm'          => array( 'line1' => 'On-Farm',     'line2' => 'Solutions' ),
);
$badge = $badge_config[ $segment ];

// Background image.
$bg_focal_x = isset( $attributes['bgFocalX'] ) ? round( (float) $attributes['bgFocalX'] * 100, 1 ) : 50;
$bg_focal_y = isset( $attributes['bgFocalY'] ) ? round( (float) $attributes['bgFocalY'] * 100, 1 ) : 50;
$bg_zoom    = isset( $attributes['bgZoom'] )   ? (float) $attributes['bgZoom'] : 100;

// PageSpeed fix (Aug 2026): real <img fetchpriority="high"> instead of a CSS
// background-image, so the browser's preload scanner can discover and
// prioritize this hero photo from the initial HTML. See .sgh-bg / .sgh-bg
// img in style.css.
$bg_img_style = '';
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
	$bg_img_style = sprintf(
		'object-position: %s%% %s%%; transform: scale(%s); transform-origin: %s%% %s%%;',
		esc_attr( $bg_focal_x ),
		esc_attr( $bg_focal_y ),
		esc_attr( number_format( $bg_zoom / 100, 4, '.', '' ) ),
		esc_attr( $bg_focal_x ),
		esc_attr( $bg_focal_y )
	);
}

// Device (sensor) image.
$theme_uri = CROPX_THEME_URI;
if ( $device_id ) {
	$device_markup = wp_get_attachment_image( $device_id, 'full', false, array(
		'class' => 'sgh-device',
		'alt'   => esc_attr__( 'CropX soil sensor', 'cropx' ),
		'style' => 'height: 100%; width: auto;',
	) );
} elseif ( $device_url ) {
	$device_markup = '<img class="sgh-device" src="' . esc_url( $device_url ) . '" alt="' . esc_attr__( 'CropX soil sensor', 'cropx' ) . '" loading="eager">';
} else {
	$device_markup  = '<picture>';
	$device_markup .= '<source type="image/webp" srcset="' . esc_url( $theme_uri . 'assets/images/illustrations/vertex-partial-a.webp' ) . '">';
	$device_markup .= '<img class="sgh-device" src="' . esc_url( $theme_uri . 'assets/images/illustrations/vertex-partial-a.png' ) . '" alt="' . esc_attr__( 'CropX soil sensor', 'cropx' ) . '" loading="eager">';
	$device_markup .= '</picture>';
}

// Phone image (bleeds below hero).
if ( $phone_id ) {
	$phone_markup = wp_get_attachment_image( $phone_id, 'full', false, array(
		'class' => 'sgh-phone--bleed',
		'alt'   => esc_attr__( 'CropX app on iPhone', 'cropx' ),
		'style' => 'height: auto;',
	) );
} elseif ( $phone_url ) {
	$phone_markup = '<img class="sgh-phone--bleed" src="' . esc_url( $phone_url ) . '" alt="' . esc_attr__( 'CropX app on iPhone', 'cropx' ) . '" loading="eager">';
} else {
	$phone_markup  = '<picture>';
	$phone_markup .= '<source type="image/webp" srcset="' . esc_url( $theme_uri . 'assets/images/illustrations/phone-mockup-b.webp' ) . '">';
	$phone_markup .= '<img class="sgh-phone--bleed" src="' . esc_url( $theme_uri . 'assets/images/illustrations/phone-mockup-b.png' ) . '" alt="' . esc_attr__( 'CropX app on iPhone', 'cropx' ) . '" loading="eager">';
	$phone_markup .= '</picture>';
}

// Allowed tags for RichText output.
$heading_tags = array( 'em' => array(), 'strong' => array(), 'br' => array() );
$sub_tags     = array_merge( $heading_tags, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

// PageSpeed fix (Aug 2026): drift-pattern.svg was a relative-path url() in
// style.css, which webpack base64-embeds (89KB SVG, past the ~10KB inlining
// cutoff — CLAUDE.md gotcha #7). This hero always shows the pattern, so the
// real, cacheable URL is injected unconditionally via a CSS custom property.
$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => 'sgh-block sgh-segment-' . esc_attr( $segment ),
	'style' => '--sgh-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');',
) );
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php
	cropx_render_nav( array(
		'badge_line1' => $badge['line1'],
		'badge_line2' => $badge['line2'],
	) );
	?>

	<div class="sgh-bleed-wrap">
		<section class="sgh-hero">
			<div class="sgh-bg">
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
			<div class="sgh-overlay"></div>
			<div class="sgh-pattern"></div>
			<div class="sgh-content">
				<?php if ( $show_eyebrow && $eyebrow ) : ?>
					<p class="sgh-eyebrow"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></p>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h1 class="sgh-headline"><?php echo wp_kses( $heading, $heading_tags ); ?></h1>
				<?php endif; ?>
				<?php if ( $subheading ) : ?>
					<p class="sgh-subheadline"><?php echo wp_kses( $subheading, $sub_tags ); ?></p>
				<?php endif; ?>
				<?php if ( ( $show_cta && $cta_label && $cta_url ) || ( $show_secondary_cta && $secondary_label ) ) : ?>
					<div class="sgh-actions">
						<?php if ( $show_cta && $cta_label && $cta_url ) : ?>
							<a class="sgh-cta" href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>"><?php echo esc_html( $cta_label ); ?></a>
						<?php endif; ?>
						<?php if ( $show_secondary_cta && $secondary_label ) : ?>
							<a class="sgh-sec-cta" href="<?php echo esc_url( cropx_url( $secondary_url ) ); ?>">
								<?php echo esc_html( $secondary_label ); ?>
								<svg width="18" height="18" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( $show_device_image ) : ?>
				<?php echo $device_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</section>
		<?php if ( $show_app_image ) : ?>
			<?php echo $phone_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>
	</div>

	<div class="sgh-border" aria-hidden="true"></div>

</div>
