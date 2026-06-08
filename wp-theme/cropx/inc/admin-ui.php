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

// ── Media library picker for Resource download URL ───────────────────────────
// Loads the WP media scripts and wires the "Choose from Media Library" button
// on the Resource edit screen. Clicking the button opens the media browser;
// selecting a file populates the download URL field automatically.
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	global $post;
	if ( ( $hook === 'post-new.php' || $hook === 'post.php' )
		&& isset( $post ) && $post->post_type === 'cropx_resource' ) {

		wp_enqueue_media();

		wp_add_inline_script( 'jquery-core', "
			jQuery( function( $ ) {
				$( '#cropx_download_url_btn' ).on( 'click', function( e ) {
					e.preventDefault();
					var frame = wp.media( {
						title:    'Select or Upload File',
						button:   { text: 'Use this file' },
						multiple: false,
					} );
					frame.on( 'select', function() {
						var attachment = frame.state().get( 'selection' ).first().toJSON();
						$( '#cropx_download_url' ).val( attachment.url ).trigger( 'change' );
					} );
					frame.open();
				} );
			} );
		" );
	}
} );

// ── Title field placeholder text per CPT ─────────────────────────────────────
// Replaces the generic "Add title" placeholder with a descriptive prompt so
// editors immediately know what the Title field expects for each content type.
add_filter( 'enter_title_here', function ( $placeholder, $post ) {
	switch ( $post->post_type ) {
		case 'cropx_publication':
			return __( 'Publication title (e.g. How Reinke Reduced Water Usage by 30%)', 'cropx' );
		case 'cropx_resource':
			return __( 'Document title (e.g. CropX Evato Sensor Datasheet)', 'cropx' );
		case 'cropx_team_member':
			return __( "Employee's full name (e.g. Jane Smith)", 'cropx' );
		case 'cropx_dealer':
			return __( 'Dealer or company name (e.g. Agri Partners Inc.)', 'cropx' );
		case 'cropx_testimonial':
			return __( "Person's full name (e.g. Jane Smith)", 'cropx' );
		default:
			return $placeholder;
	}
}, 10, 2 );

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
