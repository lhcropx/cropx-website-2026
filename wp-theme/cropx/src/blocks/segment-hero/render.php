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

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => 'sgh-block sgh-segment-' . esc_attr( $segment ),
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
			<div class="sgh-bg"<?php echo $bg_style ? ' style="' . esc_attr( $bg_style ) . '"' : ''; ?>></div>
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
				<?php if ( $show_cta && $cta_label && $cta_url ) : ?>
					<a class="sgh-cta" href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>"><?php echo esc_html( $cta_label ); ?></a>
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
