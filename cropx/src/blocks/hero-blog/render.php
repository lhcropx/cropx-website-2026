<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$segment    = $attributes['segment']    ?? 'cropx';
$eyebrow    = $attributes['eyebrow']    ?? '';
$heading    = $attributes['heading']    ?? '';
$subheading = $attributes['subheading'] ?? '';

$bg_image_id  = (int) ( $attributes['bgImageId']  ?? 0 );
$bg_image_url = $attributes['bgImageUrl']          ?? '';
$show_eyebrow      = (bool)( $attributes['showEyebrow']     ?? true );
$show_device_image = (bool)( $attributes['showDeviceImage'] ?? false );
$show_app_image    = (bool)( $attributes['showAppImage']    ?? false );
$device_id    = (int) ( $attributes['deviceImageId']  ?? 0 );
$device_url   = $attributes['deviceImageUrl']         ?? '';
$phone_id     = (int) ( $attributes['phoneImageId']   ?? 0 );
$phone_url    = $attributes['phoneImageUrl']          ?? '';

// Image position / scale.
$device_scale    = (int)( $attributes['deviceScale']   ?? 100 );
$device_offset_x = (int)( $attributes['deviceOffsetX'] ?? 0 );
$device_offset_y = (int)( $attributes['deviceOffsetY'] ?? 0 );
$phone_scale     = (int)( $attributes['phoneScale']    ?? 100 );
$phone_offset_x  = (int)( $attributes['phoneOffsetX']  ?? 0 );
$phone_offset_y  = (int)( $attributes['phoneOffsetY']  ?? 0 );

// Swoop fill — auto-detected from the next block's bgColor, with an optional
// manual override. White is the safe fallback (native/plain content after hero).
$swoop_fill     = cropx_get_swoop_fill( 'cropx/hero-blog', $attributes['swoopFill'] ?? '' );
$image_css_vars = sprintf(
	'--shc-device-scale:%d;--shc-device-x:%dpx;--shc-device-y:%dpx;--shc-phone-scale:%d;--shc-phone-x:%dpx;--shc-phone-y:%dpx;--shc-swoop-fill:%s',
	$device_scale, $device_offset_x, $device_offset_y,
	$phone_scale, $phone_offset_x, $phone_offset_y,
	esc_attr( $swoop_fill )
);

$allowed_segments = array( 'cropx', 'enterprise', 'service-provider', 'on-farm' );
if ( ! in_array( $segment, $allowed_segments, true ) ) {
	$segment = 'cropx';
}

// Accent colour — used to inline SVG gradient stops.
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

$resolved_bg_url = '';
if ( $bg_image_id ) {
	$src = wp_get_attachment_image_src( $bg_image_id, 'full' );
	if ( $src ) {
		$resolved_bg_url = $src[0];
	}
} elseif ( $bg_image_url ) {
	$resolved_bg_url = $bg_image_url;
}
$bg_style = '';
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

// Optional device / phone images (off by default for blog context).
$theme_uri = CROPX_THEME_URI;
if ( $device_id ) {
	$device_markup = wp_get_attachment_image( $device_id, 'full', false, array(
		'class' => 'shc-device',
		'alt'   => esc_attr__( 'CropX soil sensor', 'cropx' ),
		'style' => 'height: 100%; width: auto;',
	) );
} elseif ( $device_url ) {
	$device_markup = '<img class="shc-device" src="' . esc_url( $device_url ) . '" alt="' . esc_attr__( 'CropX soil sensor', 'cropx' ) . '" loading="eager">';
} else {
	$device_markup  = '<picture>';
	$device_markup .= '<source type="image/webp" srcset="' . esc_url( $theme_uri . 'assets/images/illustrations/vertex-partial-a.webp' ) . '">';
	$device_markup .= '<img class="shc-device" src="' . esc_url( $theme_uri . 'assets/images/illustrations/vertex-partial-a.png' ) . '" alt="' . esc_attr__( 'CropX soil sensor', 'cropx' ) . '" loading="eager">';
	$device_markup .= '</picture>';
}

if ( $phone_id ) {
	$phone_markup = wp_get_attachment_image( $phone_id, 'full', false, array(
		'class' => 'shc-phone',
		'alt'   => esc_attr__( 'CropX app on iPhone', 'cropx' ),
		'style' => 'height: auto;',
	) );
} elseif ( $phone_url ) {
	$phone_markup = '<img class="shc-phone" src="' . esc_url( $phone_url ) . '" alt="' . esc_attr__( 'CropX app on iPhone', 'cropx' ) . '" loading="eager">';
} else {
	$phone_markup  = '<picture>';
	$phone_markup .= '<source type="image/webp" srcset="' . esc_url( $theme_uri . 'assets/images/illustrations/phone-mockup-b.webp' ) . '">';
	$phone_markup .= '<img class="shc-phone" src="' . esc_url( $theme_uri . 'assets/images/illustrations/phone-mockup-b.png' ) . '" alt="' . esc_attr__( 'CropX app on iPhone', 'cropx' ) . '" loading="eager">';
	$phone_markup .= '</picture>';
}

// ── Category pills data ────────────────────────────────────────────────────────
// "All" links to the Posts page set in Settings → Reading.
$hbl_page_id    = (int) get_option( 'page_for_posts' );
$hbl_blog_url   = $hbl_page_id ? get_permalink( $hbl_page_id ) : home_url( '/blog' );
$hbl_categories = get_categories( array(
	'hide_empty' => true,
	'orderby'    => 'name',
	'order'      => 'ASC',
) );
// Detect the currently active category so we can highlight its pill.
$hbl_active_cat = is_category() ? get_queried_object_id() : 0;

// RichText output tags.
$heading_tags = array( 'em' => array(), 'strong' => array(), 'br' => array() );
$sub_tags     = array_merge( $heading_tags, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => 'shc-block hbl-block shc-segment-' . esc_attr( $segment ),
) );

$stop_base = esc_attr( $accent['base'] );
$stop_dark = esc_attr( $accent['dark'] );
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<div class="shc-bleed-wrap" style="<?php echo esc_attr( $image_css_vars ); ?>">
		<section class="shc-hero">
			<div class="shc-bg"<?php echo $bg_style ? ' style="' . esc_attr( $bg_style ) . '"' : ''; ?>></div>
			<div class="shc-overlay"></div>
			<div class="shc-pattern"></div>

			<div class="shc-content">
				<?php if ( $show_eyebrow && $eyebrow ) : ?>
					<p class="shc-eyebrow"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></p>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h1 class="shc-headline"><?php echo wp_kses( $heading, $heading_tags ); ?></h1>
				<?php endif; ?>
				<?php if ( $subheading ) : ?>
					<p class="shc-subheadline"><?php echo wp_kses( $subheading, $sub_tags ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $show_device_image ) : ?>
				<?php echo $device_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>

			<div class="shc-swoop" aria-hidden="true">
				<svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg"
				     preserveAspectRatio="none">
					<defs>
						<linearGradient id="hbl-curve-flow-<?php echo esc_attr( $segment ); ?>"
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
					      stroke="url(#hbl-curve-flow-<?php echo esc_attr( $segment ); ?>)"
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
