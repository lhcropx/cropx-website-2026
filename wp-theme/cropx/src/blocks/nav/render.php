<?php
/*
 * Nav structure is duplicated in cropx/segment-hero — extract to a shared partial in Phase 3.
 * See: wp-theme/cropx/src/blocks/segment-hero/render.php
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$login_url        = $attributes['loginUrl']           ?? '#';
$resources_url    = $attributes['resourcesUrl']       ?? '#';
$company_url      = $attributes['companyUrl']         ?? '#';
$enterprise_url   = $attributes['enterpriseUrl']      ?? '#';
$service_prov_url = $attributes['serviceProviderUrl'] ?? '#';
$on_farm_url      = $attributes['onFarmUrl']          ?? '#';

$theme_uri = CROPX_THEME_URI;
$logo_url  = esc_url( $theme_uri . 'assets/logos/cropx-wordmark.svg' );

$uid          = wp_unique_id( 'cnav-' );
$id_platform  = $uid . '-platform';
$id_solutions = $uid . '-solutions';
$id_mobile    = $uid . '-mobile';

$chevron_svg = '<svg class="cnav-chevron" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 4l4 4 4-4"/></svg>';

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'cnav-block' ) );
?>
<nav <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-label="<?php esc_attr_e( 'Main navigation', 'cropx' ); ?>">
	<div class="cnav-inner">
		<a href="/" class="cnav-logo-link">
			<img src="<?php echo $logo_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="CropX" class="cnav-logo" width="120" height="30" loading="eager">
		</a>

		<ul class="cnav-links" role="list">

			<!-- Platform — mega menu (hardcoded Phase 2; URLs to be real links in Phase 3) -->
			<li class="cnav-item">
				<button class="cnav-btn" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $id_platform ); ?>">
					<?php esc_html_e( 'Platform', 'cropx' ); ?>
					<?php echo $chevron_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
				<div class="cnav-dropdown cnav-dropdown--mega" id="<?php echo esc_attr( $id_platform ); ?>">
					<div class="cnav-mega-inner">
						<div class="cnav-mega-group">
							<p class="cnav-mega-heading"><?php esc_html_e( 'Sensing', 'cropx' ); ?></p>
							<a href="#"><span class="cnav-mega-link-title"><?php esc_html_e( 'Soil Sensing', 'cropx' ); ?></span><span class="cnav-mega-link-desc"><?php esc_html_e( 'Real-time moisture, temp & salinity at depth', 'cropx' ); ?></span></a>
							<a href="#"><span class="cnav-mega-link-title"><?php esc_html_e( 'Crop Monitoring', 'cropx' ); ?></span><span class="cnav-mega-link-desc"><?php esc_html_e( 'NDVI, growth stages & stress alerts', 'cropx' ); ?></span></a>
						</div>
						<div class="cnav-mega-group">
							<p class="cnav-mega-heading"><?php esc_html_e( 'Planning', 'cropx' ); ?></p>
							<a href="#"><span class="cnav-mega-link-title"><?php esc_html_e( 'Irrigation Planning', 'cropx' ); ?></span><span class="cnav-mega-link-desc"><?php esc_html_e( 'Data-driven scheduling & weather forecasts', 'cropx' ); ?></span></a>
							<a href="#"><span class="cnav-mega-link-title"><?php esc_html_e( 'Nutrient Management', 'cropx' ); ?></span><span class="cnav-mega-link-desc"><?php esc_html_e( 'EC mapping & fertilisation plans', 'cropx' ); ?></span></a>
						</div>
						<div class="cnav-mega-group">
							<p class="cnav-mega-heading"><?php esc_html_e( 'Reporting', 'cropx' ); ?></p>
							<a href="#"><span class="cnav-mega-link-title"><?php esc_html_e( 'Sustainability Reporting', 'cropx' ); ?></span><span class="cnav-mega-link-desc"><?php esc_html_e( 'Scope 3, EUDR & audit-ready farm data', 'cropx' ); ?></span></a>
							<a href="#"><span class="cnav-mega-link-title"><?php esc_html_e( 'Analytics Dashboard', 'cropx' ); ?></span><span class="cnav-mega-link-desc"><?php esc_html_e( 'Farm-level insights & benchmarks', 'cropx' ); ?></span></a>
						</div>
						<div class="cnav-mega-group">
							<p class="cnav-mega-heading"><?php esc_html_e( 'Integrations', 'cropx' ); ?></p>
							<a href="#"><span class="cnav-mega-link-title"><?php esc_html_e( 'API & Data Feeds', 'cropx' ); ?></span><span class="cnav-mega-link-desc"><?php esc_html_e( 'Connect your existing agri stack', 'cropx' ); ?></span></a>
							<a href="#"><span class="cnav-mega-link-title"><?php esc_html_e( 'Hardware Partners', 'cropx' ); ?></span><span class="cnav-mega-link-desc"><?php esc_html_e( 'Compatible sensors & devices', 'cropx' ); ?></span></a>
						</div>
					</div>
				</div>
			</li>

			<!-- Solutions — configurable URLs -->
			<li class="cnav-item">
				<button class="cnav-btn" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $id_solutions ); ?>">
					<?php esc_html_e( 'Solutions', 'cropx' ); ?>
					<?php echo $chevron_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
				<div class="cnav-dropdown cnav-dropdown--simple" id="<?php echo esc_attr( $id_solutions ); ?>">
					<a href="<?php echo esc_url( $enterprise_url ); ?>"><?php esc_html_e( 'Enterprise', 'cropx' ); ?></a>
					<a href="<?php echo esc_url( $service_prov_url ); ?>"><?php esc_html_e( 'Service Providers', 'cropx' ); ?></a>
					<a href="<?php echo esc_url( $on_farm_url ); ?>"><?php esc_html_e( 'On-Farm', 'cropx' ); ?></a>
				</div>
			</li>

			<li class="cnav-item"><a href="<?php echo esc_url( $resources_url ); ?>" class="cnav-link"><?php esc_html_e( 'Resources', 'cropx' ); ?></a></li>
			<li class="cnav-item"><a href="<?php echo esc_url( $company_url ); ?>" class="cnav-link"><?php esc_html_e( 'Company', 'cropx' ); ?></a></li>

		</ul>

		<a href="<?php echo esc_url( $login_url ); ?>" class="cnav-login"><?php esc_html_e( 'Log in', 'cropx' ); ?></a>

		<!-- Hamburger — visible only at ≤900px -->
		<button
			class="cnav-hamburger"
			type="button"
			aria-expanded="false"
			aria-controls="<?php echo esc_attr( $id_mobile ); ?>"
			aria-label="<?php esc_attr_e( 'Open navigation menu', 'cropx' ); ?>"
		>
			<span class="cnav-hamburger-bar" aria-hidden="true"></span>
			<span class="cnav-hamburger-bar" aria-hidden="true"></span>
			<span class="cnav-hamburger-bar" aria-hidden="true"></span>
		</button>
	</div>

	<!-- Mobile panel -->
	<div class="cnav-mobile-panel" id="<?php echo esc_attr( $id_mobile ); ?>" aria-hidden="true">
		<ul class="cnav-mobile-links" role="list">

			<li class="cnav-mobile-item">
				<button class="cnav-mobile-btn" type="button" aria-expanded="false">
					<?php esc_html_e( 'Platform', 'cropx' ); ?>
					<svg class="cnav-mobile-chevron" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 4l4 4 4-4"/></svg>
				</button>
				<div class="cnav-mobile-sub">
					<p class="cnav-mobile-sub-heading"><?php esc_html_e( 'Sensing', 'cropx' ); ?></p>
					<a href="#"><?php esc_html_e( 'Soil Sensing', 'cropx' ); ?></a>
					<a href="#"><?php esc_html_e( 'Crop Monitoring', 'cropx' ); ?></a>
					<p class="cnav-mobile-sub-heading"><?php esc_html_e( 'Planning', 'cropx' ); ?></p>
					<a href="#"><?php esc_html_e( 'Irrigation Planning', 'cropx' ); ?></a>
					<a href="#"><?php esc_html_e( 'Nutrient Management', 'cropx' ); ?></a>
					<p class="cnav-mobile-sub-heading"><?php esc_html_e( 'Reporting', 'cropx' ); ?></p>
					<a href="#"><?php esc_html_e( 'Sustainability Reporting', 'cropx' ); ?></a>
					<a href="#"><?php esc_html_e( 'Analytics Dashboard', 'cropx' ); ?></a>
					<p class="cnav-mobile-sub-heading"><?php esc_html_e( 'Integrations', 'cropx' ); ?></p>
					<a href="#"><?php esc_html_e( 'API & Data Feeds', 'cropx' ); ?></a>
					<a href="#"><?php esc_html_e( 'Hardware Partners', 'cropx' ); ?></a>
				</div>
			</li>

			<li class="cnav-mobile-item">
				<button class="cnav-mobile-btn" type="button" aria-expanded="false">
					<?php esc_html_e( 'Solutions', 'cropx' ); ?>
					<svg class="cnav-mobile-chevron" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 4l4 4 4-4"/></svg>
				</button>
				<div class="cnav-mobile-sub">
					<a href="<?php echo esc_url( $enterprise_url ); ?>"><?php esc_html_e( 'Enterprise', 'cropx' ); ?></a>
					<a href="<?php echo esc_url( $service_prov_url ); ?>"><?php esc_html_e( 'Service Providers', 'cropx' ); ?></a>
					<a href="<?php echo esc_url( $on_farm_url ); ?>"><?php esc_html_e( 'On-Farm', 'cropx' ); ?></a>
				</div>
			</li>

			<li class="cnav-mobile-item"><a href="<?php echo esc_url( $resources_url ); ?>"><?php esc_html_e( 'Resources', 'cropx' ); ?></a></li>
			<li class="cnav-mobile-item"><a href="<?php echo esc_url( $company_url ); ?>"><?php esc_html_e( 'Company', 'cropx' ); ?></a></li>
			<li class="cnav-mobile-item"><a href="<?php echo esc_url( $login_url ); ?>" class="cnav-mobile-login"><?php esc_html_e( 'Log in', 'cropx' ); ?></a></li>

		</ul>
	</div>
</nav>
