<?php
/**
 * Shared nav partial.
 *
 * Renders a complete cnav-* navigation. Called by both the standalone
 * `cropx/nav` block and the `cropx/segment-hero` block so the markup
 * (and therefore CSS and JS) lives in one place.
 *
 * Nav links are driven by three WP menus registered in inc/menus.php:
 *   cropx-solutions  — Solutions dropdown items
 *   cropx-platform   — Platform mega menu items (hierarchical)
 *   cropx-utility    — Resources, Company simple links
 *
 * Usage:
 *
 *   cropx_render_nav( array(
 *       // Login button URL (block attribute — not a menu item).
 *       'login_url' => $login_url,
 *
 *       // Optional: raw HTML attribute string from get_block_wrapper_attributes().
 *       // Pass from the nav block so WP block supports land on the <nav> element.
 *       // When absent the nav outputs plain class="cnav-block".
 *       'wrapper_attrs_str' => $wrapper_attrs,
 *
 *       // Optional segment badge (segment-hero only). When badge_line1 is
 *       // non-empty a <div class="cnav-badge"> is inserted after the logo.
 *       'badge_line1' => 'Enterprise',
 *       'badge_line2' => 'Solutions',
 *   ) );
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function cropx_render_nav( array $args = array() ): void {
	$login_url   = $args['login_url']   ?? '#';
	$badge_line1 = $args['badge_line1'] ?? '';
	$badge_line2 = $args['badge_line2'] ?? '';

	$wrapper_attrs_str = $args['wrapper_attrs_str'] ?? '';

	$theme_uri = CROPX_THEME_URI;
	$logo_url  = esc_url( $theme_uri . 'assets/logos/cropx-wordmark.svg' );

	// Unique IDs so multiple nav instances on one page don't conflict.
	$uid           = wp_unique_id( 'cnav-' );
	$id_platform   = $uid . '-platform';
	$id_solutions  = $uid . '-solutions';
	$id_knowledge  = $uid . '-knowledge';
	$id_mobile     = $uid . '-mobile';

	$chevron_svg = '<svg class="cnav-chevron" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 4l4 4 4-4"/></svg>';

	// Shared wp_nav_menu args — no container, no ul wrapper (items_wrap = %3$s),
	// no fallback so missing menus render nothing rather than showing a broken list.
	$menu_base = array(
		'container'   => false,
		'items_wrap'  => '%3$s',
		'fallback_cb' => false,
		'echo'        => true,
	);

	if ( $wrapper_attrs_str ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<nav ' . $wrapper_attrs_str . ' aria-label="' . esc_attr__( 'Main navigation', 'cropx' ) . '">';
	} else {
		echo '<nav class="cnav-block" aria-label="' . esc_attr__( 'Main navigation', 'cropx' ) . '">';
	}
	?>
	<div class="cnav-inner">
		<a href="/" class="cnav-logo-link">
			<img src="<?php echo $logo_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="CropX" class="cnav-logo" width="120" height="30" loading="eager">
		</a>

		<?php if ( $badge_line1 ) : ?>
		<div class="cnav-badge" aria-hidden="true">
			<span><?php echo esc_html( $badge_line1 ); ?><br><?php echo esc_html( $badge_line2 ); ?></span>
		</div>
		<?php endif; ?>

		<ul class="cnav-links" role="list">

			<!-- Platform — triggers the mega dropdown -->
			<li class="cnav-item cnav-item--has-mega">
				<button class="cnav-btn" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $id_platform ); ?>">
					<?php esc_html_e( 'Platform', 'cropx' ); ?>
					<?php echo $chevron_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
			</li>

			<!-- Solutions — triggers simple dropdown, items from cropx-solutions menu -->
			<li class="cnav-item">
				<button class="cnav-btn" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $id_solutions ); ?>">
					<?php esc_html_e( 'Solutions', 'cropx' ); ?>
					<?php echo $chevron_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
				<div class="cnav-dropdown cnav-dropdown--simple" id="<?php echo esc_attr( $id_solutions ); ?>">
					<?php wp_nav_menu( array_merge( $menu_base, array(
						'theme_location' => 'cropx-solutions',
						'walker'         => new CropX_Solutions_Walker(),
					) ) ); ?>
				</div>
			</li>

			<!-- Knowledge Hub — simple dropdown, items from cropx-knowledge-hub menu -->
			<li class="cnav-item">
				<button class="cnav-btn" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $id_knowledge ); ?>">
					<?php esc_html_e( 'Knowledge Hub', 'cropx' ); ?>
					<?php echo $chevron_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
				<div class="cnav-dropdown cnav-dropdown--simple" id="<?php echo esc_attr( $id_knowledge ); ?>">
					<?php wp_nav_menu( array_merge( $menu_base, array(
						'theme_location' => 'cropx-knowledge-hub',
						'walker'         => new CropX_Solutions_Walker(),
					) ) ); ?>
				</div>
			</li>

			<!-- Resources + Company from cropx-utility menu (outputs <li> items directly) -->
			<?php wp_nav_menu( array_merge( $menu_base, array(
				'theme_location' => 'cropx-utility',
				'walker'         => new CropX_Utility_Walker(),
			) ) ); ?>

		</ul>

		<a href="<?php echo esc_url( cropx_url( $login_url ) ); ?>" class="cnav-login"><?php esc_html_e( 'Log in', 'cropx' ); ?></a>

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

	<!-- Platform mega menu — direct child of <nav> so it uses the nav as its containing block -->
	<div class="cnav-dropdown cnav-dropdown--mega" id="<?php echo esc_attr( $id_platform ); ?>">
		<div class="cnav-mega-inner">
			<?php wp_nav_menu( array_merge( $menu_base, array(
				'theme_location' => 'cropx-platform',
				'walker'         => new CropX_Platform_Walker(),
			) ) ); ?>
		</div>
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
					<?php wp_nav_menu( array_merge( $menu_base, array(
						'theme_location' => 'cropx-platform',
						'walker'         => new CropX_Platform_Walker(),
						'cropx_context'  => 'mobile',
					) ) ); ?>
				</div>
			</li>

			<li class="cnav-mobile-item">
				<button class="cnav-mobile-btn" type="button" aria-expanded="false">
					<?php esc_html_e( 'Solutions', 'cropx' ); ?>
					<svg class="cnav-mobile-chevron" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 4l4 4 4-4"/></svg>
				</button>
				<div class="cnav-mobile-sub">
					<?php wp_nav_menu( array_merge( $menu_base, array(
						'theme_location' => 'cropx-solutions',
						'walker'         => new CropX_Solutions_Walker(),
					) ) ); ?>
				</div>
			</li>

			<li class="cnav-mobile-item">
				<button class="cnav-mobile-btn" type="button" aria-expanded="false">
					<?php esc_html_e( 'Knowledge Hub', 'cropx' ); ?>
					<svg class="cnav-mobile-chevron" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 4l4 4 4-4"/></svg>
				</button>
				<div class="cnav-mobile-sub">
					<?php wp_nav_menu( array_merge( $menu_base, array(
						'theme_location' => 'cropx-knowledge-hub',
						'walker'         => new CropX_Solutions_Walker(),
					) ) ); ?>
				</div>
			</li>

			<!-- Resources + Company from cropx-utility menu (outputs <li class="cnav-mobile-item"> items) -->
			<?php wp_nav_menu( array_merge( $menu_base, array(
				'theme_location' => 'cropx-utility',
				'walker'         => new CropX_Utility_Walker(),
				'cropx_context'  => 'mobile',
			) ) ); ?>

			<li class="cnav-mobile-item"><a href="<?php echo esc_url( cropx_url( $login_url ) ); ?>" class="cnav-mobile-login"><?php esc_html_e( 'Log in', 'cropx' ); ?></a></li>

		</ul>
	</div>
	<?php
	echo '</nav>';
}
