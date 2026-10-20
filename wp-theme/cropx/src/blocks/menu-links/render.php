<?php
/**
 * Footer Menu Links block — front-end render.
 *
 * Deliberately emits NO wrapper markup — just the same plain <a> tags the
 * old monolithic cropx/footer block produced via CropX_Footer_Walker (see
 * inc/menus.php). That means this block drops straight inside a Group
 * block with class "footer-nav-group" in the footer Synced Pattern and the
 * existing `.footer-nav-group a` CSS (footer/style.css) keeps working
 * completely unchanged — no new CSS needed for this block.
 *
 * "source" maps to either a real theme_location (wp_nav_menu(), same menus
 * the header dropdowns use — Appearance → Menus is the single source of
 * truth) or one of the two special Platform-mega-menu sub-groups (Hardware /
 * Software), which aren't separate theme_locations but children of a
 * same-titled top-level item inside the single "Platform Mega Menu" —
 * see cropx_footer_platform_children() in inc/menus.php.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$source = $attributes['source'] ?? 'solutions';

$valid_sources = array(
	'platform-hardware',
	'platform-software',
	'solutions',
	'knowledge-hub',
	'about',
	'contact',
	'legal',
);
if ( ! in_array( $source, $valid_sources, true ) ) {
	$source = 'solutions';
}

// Real theme_location-backed sources.
$theme_location_map = array(
	'solutions'     => 'cropx-solutions',
	'knowledge-hub' => 'cropx-knowledge-hub',
	'about'         => 'cropx-about',
	'contact'       => 'cropx-contact',
	'legal'         => 'footer-legal',
);

$footer_menu_args = array(
	'container'   => false,
	'items_wrap'  => '%3$s',
	'fallback_cb' => false,
	'walker'      => new CropX_Footer_Walker(),
);

if ( isset( $theme_location_map[ $source ] ) ) {
	wp_nav_menu( array_merge( $footer_menu_args, array( 'theme_location' => $theme_location_map[ $source ] ) ) );
} elseif ( 'platform-hardware' === $source ) {
	cropx_footer_platform_children( 'Hardware' );
} elseif ( 'platform-software' === $source ) {
	cropx_footer_platform_children( 'Software' );
}
