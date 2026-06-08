<?php
/**
 * WordPress admin UI customisations.
 *
 * - Reorders the admin sidebar menu
 * - Hides irrelevant menu items (Media, Comments)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Reorder the admin sidebar ─────────────────────────────────────────────────
// Items not listed here will appear after the listed items in their default order.
// Plugin slugs: All-in-One WP Migration = 'ai1wm_export', Meow Apps = 'meow-apps'.
// If either plugin item doesn't move to the right position, inspect the exact slug
// by hovering the menu item in WP Admin and checking the URL in the status bar.
add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', function ( $menu_order ) {
	return array(
		'index.php',                              // Dashboard
		'edit.php?post_type=page',                // Pages
		'edit.php?post_type=cropx_publication',   // Publications
		'edit.php',                               // Posts
		'edit.php?post_type=cropx_resource',      // Resources
		'edit.php?post_type=cropx_testimonial',   // Testimonials
		'edit.php?post_type=cropx_dealer',        // Dealers
		'edit.php?post_type=cropx_team_member',   // Team
		'upload.php',                             // Media
		'edit-comments.php',                      // Comments
		'separator1',
		'themes.php',                             // Appearance
		'plugins.php',                            // Plugins
		'users.php',                              // Users
		'tools.php',                              // Tools
		'ai1wm_export',                           // All-in-One WP Migration
		'separator2',
		'options-general.php',                    // Settings
		'meow-apps',                              // Meow Apps
	);
} );
