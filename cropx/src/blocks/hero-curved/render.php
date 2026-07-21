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
$show_eyebrow      = (bool)( $attributes['showEyebrow']     ?? true );
$show_cta          = (bool)( $attributes['showCta']         ?? true );
$show_device_image = (bool)( $attributes['showDeviceImage'] ?? true );
$show_app_image    = (bool)( $attributes['showAppImage']    ?? true );
$device_id    = (int) ( $attributes['deviceImageId']  ?? 0 );
$device_url   = $attributes['deviceImageUrl']         ?? '';
$phone_id     = (int) ( $attributes['phoneImageId']   ?? 0 );
$phone_url    = $attributes['phoneImageUrl']          ?? '';

// Image position / scale attributes.
$device_scale    = (int)( $attributes['deviceScale']   ?? 100 );
$device_offset_x = (int)( $attributes['deviceOffsetX'] ?? 0 );
$device_offset_y = (int)( $attributes['deviceOffsetY'] ?? 0 );
$phone_scale     = (int)( $attributes['phoneScale']    ?? 100 );
$phone_offset_x  = (int)( $attributes['phoneOffsetX']  ?? 0 );
$phone_offset_y  = (int)( $attributes['phoneOffsetY']  ?? 0 );

// Swoop fill — auto-detected from the next block's bgColor, with an optional
// manual override. White is the safe fallback (native/plain content after hero).
$swoop_fill     = cropx_get_swoop_fill( 'cropx/hero-curved', $attributes['swoopFill'] ?? '' );
$image_css_vars = sprintf(
	'--hc-device-scale:%d;--hc-device-x:%dpx;--hc-device-y:%dpx;--hc-phone-scale:%d;--hc-phone-x:%dpx;--hc-phone-y:%dpx;--hc-swoop-fill:%s',
	$device_scale, $device_offset_x, $device_offset_y,
	$phone_scale, $phone_offset_x, $phone_offset_y,
	esc_attr( $swoop_fill )
);

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

// Accent colour maps — used to inline the SVG gradient stops.
$accent_config = array(
	'enterprise'       => array( 'base' => '#E9C242', 'dark' => '#C9A130' ),
	'service-provider' => array( 'base' => '#E48C4D', 'dark' => '#C4733A' ),
	'on-farm'          => array( 'base' => '#96C05A', 'dark' => '#7BA348' ),
);
$accent = $accent_config[ $segment ];

// Background image.
$bg_focal_x = isset( $attributes['bgFocalX'] ) ? round( (float) $attributes['bgFocalX'] * 100, 1 ) : 50;
$bg_focal_y = isset( $attributes['bgFocalY'] ) ? round( (float) $attributes['bgFocalY'] * 100, 1 ) : 30;
$bg_zoom    = isset( $attributes['bgZoom'] )   ? (float) $attributes['bgZoom'] : 100;

$bg_style = '';
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
	$bg_style = sprintf(
		'background-image: url(%s); background-position: %s%% %s%%; transform: scale(%s); transform-origin: %s%% %s%%;',
		esc_url( $resolved_bg_url ),
		esc_attr( $bg_focal_x ),
		esc_attr( $bg_focal_y ),
		esc_attr( number_format( $bg_zoom / 100, 4, '.', '' ) ),
		esc_attr( $bg_focal_x ),
		esc_attr( $bg_focal_y )
	);
}

// Device (sensor) image — stays inside hero, clipped at the bottom by overflow:hidden.
$theme_uri = CROPX_THEME_URI;
if ( $device_id ) {
	$device_markup = wp_get_attachment_image( $device_id, 'full', false, array(
		'class' => 'hc-device',
		'alt'   => esc_attr__( 'CropX soil sensor', 'cropx' ),
		'style' => 'height: 100%; width: auto;',
	) );
} elseif ( $device_url ) {
	$device_markup = '<img class="hc-device" src="' . esc_url( $device_url ) . '" alt="' . esc_attr__( 'CropX soil sensor', 'cropx' ) . '" loading="eager">';
} else {
	$device_markup  = '<picture>';
	$device_markup .= '<source type="image/webp" srcset="' . esc_url( $theme_uri . 'assets/images/illustrations/vertex-partial-a.webp' ) . '">';
	$device_markup .= '<img class="hc-device" src="' . esc_url( $theme_uri . 'assets/images/illustrations/vertex-partial-a.png' ) . '" alt="' . esc_attr__( 'CropX soil sensor', 'cropx' ) . '" loading="eager">';
	$device_markup .= '</picture>';
}

// Phone image — lives OUTSIDE hc-hero in the bleed-wrap so it bleeds past the curve.
if ( $phone_id ) {
	$phone_markup = wp_get_attachment_image( $phone_id, 'full', false, array(
		'class' => 'hc-phone',
		'alt'   => esc_attr__( 'CropX app on iPhone', 'cropx' ),
		'style' => 'height: auto;',
	) );
} elseif ( $phone_url ) {
	$phone_markup = '<img class="hc-phone" src="' . esc_url( $phone_url ) . '" alt="' . esc_attr__( 'CropX app on iPhone', 'cropx' ) . '" loading="eager">';
} else {
	$phone_markup  = '<picture>';
	$phone_markup .= '<source type="image/webp" srcset="' . esc_url( $theme_uri . 'assets/images/illustrations/phone-mockup-b.webp' ) . '">';
	$phone_markup .= '<img class="hc-phone" src="' . esc_url( $theme_uri . 'assets/images/illustrations/phone-mockup-b.png' ) . '" alt="' . esc_attr__( 'CropX app on iPhone', 'cropx' ) . '" loading="eager">';
	$phone_markup .= '</picture>';
}

// Allowed tags for RichText output.
$heading_tags = array( 'em' => array(), 'strong' => array(), 'br' => array() );
$sub_tags     = array_merge( $heading_tags, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => 'hc-block hc-segment-' . esc_attr( $segment ),
) );

// SVG gradient stop colors for the swoop stroke — inlined so they resolve at
// render time rather than requiring a CSS variable that SVG can't see.
$stop_base = esc_attr( $accent['base'] );
$stop_dark = esc_attr( $accent['dark'] );
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php
	cropx_render_nav( array(
		'badge_line1' => $badge['line1'],
		'badge_line2' => $badge['line2'],
	) );
	?>

	<!--
		.hc-bleed-wrap is position:relative and defines --hc-edge, the inset from
		the viewport edge to the max-width container. Both the sensor (inside hc-hero)
		and the phone (outside hc-hero, as a direct child of hc-bleed-wrap) reference
		this variable — so they always move in sync as the viewport resizes.
		The phone is a sibling of hc-hero (not a child) so it is never subject to
		hc-hero's overflow:hidden; it bleeds 30px below the curve cleanly.
	-->
	<div class="hc-bleed-wrap" style="<?php echo esc_attr( $image_css_vars ); ?>">
		<section class="hc-hero">
			<div class="hc-bg"<?php echo $bg_style ? ' style="' . esc_attr( $bg_style ) . '"' : ''; ?>></div>
			<div class="hc-overlay"></div>
			<div class="hc-pattern"></div>

			<div class="hc-content<?php echo ( ! $show_device_image && ! $show_app_image ) ? ' hc-content--wide' : ''; ?>">
				<?php if ( $show_eyebrow && $eyebrow ) : ?>
					<p class="hc-eyebrow"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></p>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h1 class="hc-headline"><?php echo wp_kses( $heading, $heading_tags ); ?></h1>
				<?php endif; ?>
				<?php if ( $subheading ) : ?>
					<p class="hc-subheadline"><?php echo wp_kses( $subheading, $sub_tags ); ?></p>
				<?php endif; ?>
				<?php if ( $show_cta && $cta_label && $cta_url ) : ?>
					<a class="hc-cta" href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>"><?php echo esc_html( $cta_label ); ?></a>
				<?php endif; ?>
			</div>

			<?php if ( $show_device_image ) : ?>
				<?php echo $device_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>

			<!--
				Quadratic bezier swoop.
				preserveAspectRatio="none" lets the SVG stretch to any viewport width.
				vector-effect="non-scaling-stroke" keeps the stroke at a constant 20px
				screen pixels regardless of how wide the SVG is stretched.

				Taupe fill (M0,80 Q720,80 1440,30): top edge runs from y=80 at x=0 (flush
				with the hero bottom) to y=30 at x=1440 — a 50px rise from left to right.
				This masks the bottom-right corner of the hero with the page background.

				Animated stroke (M0,70 Q720,70 1440,20): exactly 10px above the fill path
				at every t value (the quadratic's y_stroke = y_fill - 10 for all t). The
				stroke's outer edge sits precisely on the fill boundary; the full 20px
				width of the stroke lands inside the hero so it is never clipped.

				The gradient is 2× the SVG width (gradientUnits="userSpaceOnUse",
				x2="2880"). animateTransform slides it left by 1440px then snaps back.
				Because the gradient is symmetric (same hue at 0%, 50%, 100%) the seam
				is invisible and the loop runs for 14 seconds.
			-->
			<div class="hc-swoop" aria-hidden="true">
				<svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg"
				     preserveAspectRatio="none">
					<defs>
						<linearGradient id="hc-curve-flow-<?php echo esc_attr( $segment ); ?>"
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
					<path class="hc-fill-path" d="M0,80 Q720,80 1440,30 L1440,80 L0,80 Z"/>
					<path d="M0,70 Q720,70 1440,20" fill="none"
					      stroke="url(#hc-curve-flow-<?php echo esc_attr( $segment ); ?>)"
					      stroke-width="20" stroke-linecap="round"
					      vector-effect="non-scaling-stroke"/>
				</svg>
			</div>
		</section>

		<?php if ( $show_app_image ) : ?>
			<?php echo $phone_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>
	</div>

</div>
