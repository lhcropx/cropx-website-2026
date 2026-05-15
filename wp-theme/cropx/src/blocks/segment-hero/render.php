<?php
/*
 * Nav structure is duplicated in cropx/nav — extract to a shared partial in Phase 3.
 * See: wp-theme/cropx/src/blocks/nav/render.php
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$segment    = $attributes['segment']    ?? 'enterprise';
$eyebrow    = $attributes['eyebrow']    ?? '';
$heading    = $attributes['heading']    ?? '';
$subheading = $attributes['subheading'] ?? '';
$cta_label  = $attributes['ctaLabel']   ?? '';
$cta_url    = $attributes['ctaUrl']     ?? '';

$bg_image_id  = (int) ( $attributes['bgImageId']     ?? 0 );
$bg_image_url = $attributes['bgImageUrl']             ?? '';
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
$bg_style = '';
if ( $bg_image_id ) {
	$src = wp_get_attachment_image_src( $bg_image_id, 'full' );
	if ( $src ) {
		$bg_style = 'background-image: url(' . esc_url( $src[0] ) . ');';
	}
} elseif ( $bg_image_url ) {
	$bg_style = 'background-image: url(' . esc_url( $bg_image_url ) . ');';
}

// Device (sensor) image.
$theme_uri = CROPX_THEME_URI;
if ( $device_id ) {
	$device_markup = wp_get_attachment_image( $device_id, 'full', false, array(
		'class' => 'sgh-device',
		'alt'   => esc_attr__( 'CropX soil sensor', 'cropx' ),
		'style' => 'height: auto;',
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

$logo_url = esc_url( $theme_uri . 'assets/logos/cropx-wordmark.svg' );

// Unique IDs so multiple instances on one page don't conflict.
$uid          = wp_unique_id( 'sgh-' );
$id_platform  = $uid . '-platform';
$id_solutions = $uid . '-solutions';
$id_mobile    = $uid . '-mobile';

// Allowed tags for RichText output.
$heading_tags = array( 'em' => array(), 'strong' => array(), 'br' => array() );
$sub_tags     = array_merge( $heading_tags, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => 'sgh-block sgh-segment-' . esc_attr( $segment ),
) );

$chevron_svg = '<svg class="sgh-nav-chevron" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 4l4 4 4-4"/></svg>';
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<nav class="sgh-nav" aria-label="<?php esc_attr_e( 'Main navigation', 'cropx' ); ?>">
		<div class="sgh-nav-inner">
			<a href="/" class="sgh-nav-logo-link">
				<img src="<?php echo $logo_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="CropX" class="sgh-nav-logo" width="120" height="30" loading="eager">
			</a>

			<div class="sgh-badge" aria-hidden="true">
				<span><?php echo esc_html( $badge['line1'] ); ?><br><?php echo esc_html( $badge['line2'] ); ?></span>
			</div>

			<ul class="sgh-nav-links" role="list">
				<li class="sgh-nav-item">
					<button class="sgh-nav-btn" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $id_platform ); ?>">
						<?php esc_html_e( 'Platform', 'cropx' ); ?>
						<?php echo $chevron_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
					<div class="sgh-dropdown sgh-dropdown--mega" id="<?php echo esc_attr( $id_platform ); ?>">
						<div class="sgh-mega-inner">
							<div class="sgh-mega-group">
								<p class="sgh-mega-heading"><?php esc_html_e( 'Sensing', 'cropx' ); ?></p>
								<a href="#"><span class="sgh-mega-link-title"><?php esc_html_e( 'Soil Sensing', 'cropx' ); ?></span><span class="sgh-mega-link-desc"><?php esc_html_e( 'Real-time moisture, temp & salinity at depth', 'cropx' ); ?></span></a>
								<a href="#"><span class="sgh-mega-link-title"><?php esc_html_e( 'Crop Monitoring', 'cropx' ); ?></span><span class="sgh-mega-link-desc"><?php esc_html_e( 'NDVI, growth stages & stress alerts', 'cropx' ); ?></span></a>
							</div>
							<div class="sgh-mega-group">
								<p class="sgh-mega-heading"><?php esc_html_e( 'Planning', 'cropx' ); ?></p>
								<a href="#"><span class="sgh-mega-link-title"><?php esc_html_e( 'Irrigation Planning', 'cropx' ); ?></span><span class="sgh-mega-link-desc"><?php esc_html_e( 'Data-driven scheduling & weather forecasts', 'cropx' ); ?></span></a>
								<a href="#"><span class="sgh-mega-link-title"><?php esc_html_e( 'Nutrient Management', 'cropx' ); ?></span><span class="sgh-mega-link-desc"><?php esc_html_e( 'EC mapping & fertilisation plans', 'cropx' ); ?></span></a>
							</div>
							<div class="sgh-mega-group">
								<p class="sgh-mega-heading"><?php esc_html_e( 'Reporting', 'cropx' ); ?></p>
								<a href="#"><span class="sgh-mega-link-title"><?php esc_html_e( 'Sustainability Reporting', 'cropx' ); ?></span><span class="sgh-mega-link-desc"><?php esc_html_e( 'Scope 3, EUDR & audit-ready farm data', 'cropx' ); ?></span></a>
								<a href="#"><span class="sgh-mega-link-title"><?php esc_html_e( 'Analytics Dashboard', 'cropx' ); ?></span><span class="sgh-mega-link-desc"><?php esc_html_e( 'Farm-level insights & benchmarks', 'cropx' ); ?></span></a>
							</div>
							<div class="sgh-mega-group">
								<p class="sgh-mega-heading"><?php esc_html_e( 'Integrations', 'cropx' ); ?></p>
								<a href="#"><span class="sgh-mega-link-title"><?php esc_html_e( 'API & Data Feeds', 'cropx' ); ?></span><span class="sgh-mega-link-desc"><?php esc_html_e( 'Connect your existing agri stack', 'cropx' ); ?></span></a>
								<a href="#"><span class="sgh-mega-link-title"><?php esc_html_e( 'Hardware Partners', 'cropx' ); ?></span><span class="sgh-mega-link-desc"><?php esc_html_e( 'Compatible sensors & devices', 'cropx' ); ?></span></a>
							</div>
						</div>
					</div>
				</li>
				<li class="sgh-nav-item">
					<button class="sgh-nav-btn" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $id_solutions ); ?>">
						<?php esc_html_e( 'Solutions', 'cropx' ); ?>
						<?php echo $chevron_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
					<div class="sgh-dropdown sgh-dropdown--simple" id="<?php echo esc_attr( $id_solutions ); ?>">
						<a href="#"><?php esc_html_e( 'Enterprise', 'cropx' ); ?></a>
						<a href="#"><?php esc_html_e( 'Service Providers', 'cropx' ); ?></a>
						<a href="#"><?php esc_html_e( 'On-Farm', 'cropx' ); ?></a>
					</div>
				</li>
				<li class="sgh-nav-item"><a href="#" class="sgh-nav-link"><?php esc_html_e( 'Resources', 'cropx' ); ?></a></li>
				<li class="sgh-nav-item"><a href="#" class="sgh-nav-link"><?php esc_html_e( 'Company', 'cropx' ); ?></a></li>
			</ul>

			<a href="#" class="sgh-nav-login"><?php esc_html_e( 'Log in', 'cropx' ); ?></a>

			<!-- Hamburger — visible only at ≤900px -->
			<button
				class="sgh-hamburger"
				type="button"
				aria-expanded="false"
				aria-controls="<?php echo esc_attr( $id_mobile ); ?>"
				aria-label="<?php esc_attr_e( 'Open navigation menu', 'cropx' ); ?>"
			>
				<span class="sgh-hamburger-bar" aria-hidden="true"></span>
				<span class="sgh-hamburger-bar" aria-hidden="true"></span>
				<span class="sgh-hamburger-bar" aria-hidden="true"></span>
			</button>
		</div>

		<!-- Mobile panel -->
		<div class="sgh-mobile-panel" id="<?php echo esc_attr( $id_mobile ); ?>" aria-hidden="true">
			<ul class="sgh-mobile-links" role="list">

				<li class="sgh-mobile-item">
					<button class="sgh-mobile-btn" type="button" aria-expanded="false">
						<?php esc_html_e( 'Platform', 'cropx' ); ?>
						<svg class="sgh-mobile-chevron" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 4l4 4 4-4"/></svg>
					</button>
					<div class="sgh-mobile-sub">
						<p class="sgh-mobile-sub-heading"><?php esc_html_e( 'Sensing', 'cropx' ); ?></p>
						<a href="#"><?php esc_html_e( 'Soil Sensing', 'cropx' ); ?></a>
						<a href="#"><?php esc_html_e( 'Crop Monitoring', 'cropx' ); ?></a>
						<p class="sgh-mobile-sub-heading"><?php esc_html_e( 'Planning', 'cropx' ); ?></p>
						<a href="#"><?php esc_html_e( 'Irrigation Planning', 'cropx' ); ?></a>
						<a href="#"><?php esc_html_e( 'Nutrient Management', 'cropx' ); ?></a>
						<p class="sgh-mobile-sub-heading"><?php esc_html_e( 'Reporting', 'cropx' ); ?></p>
						<a href="#"><?php esc_html_e( 'Sustainability Reporting', 'cropx' ); ?></a>
						<a href="#"><?php esc_html_e( 'Analytics Dashboard', 'cropx' ); ?></a>
						<p class="sgh-mobile-sub-heading"><?php esc_html_e( 'Integrations', 'cropx' ); ?></p>
						<a href="#"><?php esc_html_e( 'API & Data Feeds', 'cropx' ); ?></a>
						<a href="#"><?php esc_html_e( 'Hardware Partners', 'cropx' ); ?></a>
					</div>
				</li>

				<li class="sgh-mobile-item">
					<button class="sgh-mobile-btn" type="button" aria-expanded="false">
						<?php esc_html_e( 'Solutions', 'cropx' ); ?>
						<svg class="sgh-mobile-chevron" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 4l4 4 4-4"/></svg>
					</button>
					<div class="sgh-mobile-sub">
						<a href="#"><?php esc_html_e( 'Enterprise', 'cropx' ); ?></a>
						<a href="#"><?php esc_html_e( 'Service Providers', 'cropx' ); ?></a>
						<a href="#"><?php esc_html_e( 'On-Farm', 'cropx' ); ?></a>
					</div>
				</li>

				<li class="sgh-mobile-item"><a href="#"><?php esc_html_e( 'Resources', 'cropx' ); ?></a></li>
				<li class="sgh-mobile-item"><a href="#"><?php esc_html_e( 'Company', 'cropx' ); ?></a></li>
				<li class="sgh-mobile-item"><a href="#" class="sgh-mobile-login"><?php esc_html_e( 'Log in', 'cropx' ); ?></a></li>

			</ul>
		</div>
	</nav>

	<div class="sgh-bleed-wrap">
		<section class="sgh-hero">
			<div class="sgh-bg"<?php echo $bg_style ? ' style="' . esc_attr( $bg_style ) . '"' : ''; ?>></div>
			<div class="sgh-overlay"></div>
			<div class="sgh-pattern"></div>
			<div class="sgh-content">
				<?php if ( $eyebrow ) : ?>
					<p class="sgh-eyebrow"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></p>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h1 class="sgh-headline"><?php echo wp_kses( $heading, $heading_tags ); ?></h1>
				<?php endif; ?>
				<?php if ( $subheading ) : ?>
					<p class="sgh-subheadline"><?php echo wp_kses( $subheading, $sub_tags ); ?></p>
				<?php endif; ?>
				<?php if ( $cta_label && $cta_url ) : ?>
					<a class="sgh-cta" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_label ); ?></a>
				<?php endif; ?>
			</div>
			<?php echo $device_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</section>
		<?php echo $phone_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>

	<div class="sgh-border" aria-hidden="true"></div>

</div>
