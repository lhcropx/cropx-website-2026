<?php
/**
 * CropX theme bootstrap.
 *
 * Loads asset enqueueing, registers our custom blocks, and adds the
 * "CropX" block category that all of our blocks live under in the
 * editor's inserter.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CROPX_THEME_VERSION', '0.2.0' );
define( 'CROPX_THEME_DIR',     trailingslashit( get_template_directory() ) );
define( 'CROPX_THEME_URI',     trailingslashit( get_template_directory_uri() ) );

require_once CROPX_THEME_DIR . 'inc/theme-setup.php';
require_once CROPX_THEME_DIR . 'inc/enqueue.php';
require_once CROPX_THEME_DIR . 'inc/blocks.php';
require_once CROPX_THEME_DIR . 'inc/cpts.php';
require_once CROPX_THEME_DIR . 'inc/customer-stories.php';
require_once CROPX_THEME_DIR . 'inc/insights-archive.php';
require_once CROPX_THEME_DIR . 'inc/patterns.php';
require_once CROPX_THEME_DIR . 'inc/helpers.php';
require_once CROPX_THEME_DIR . 'inc/admin-ui.php';

// Guarded rather than a plain require_once: WP File Manager's zip extraction
// on staging has a documented history of leaving a file missing or
// truncated after a "successful" extract (see CLAUDE.md). A plain
// require_once on a missing/corrupt file is a fatal error on *every* page
// load, site-wide — this degrades to "Duplicate just doesn't show up",
// which is recoverable without touching functions.php by hand.
if ( file_exists( CROPX_THEME_DIR . 'inc/duplicate-post.php' ) ) {
	require_once CROPX_THEME_DIR . 'inc/duplicate-post.php';
}
require_once CROPX_THEME_DIR . 'inc/menus.php';
require_once CROPX_THEME_DIR . 'inc/admin-help.php';
require_once CROPX_THEME_DIR . 'inc/parts/nav.php';
require_once CROPX_THEME_DIR . 'inc/cropx-settings.php';
require_once CROPX_THEME_DIR . 'inc/dealer-finder-api.php';
require_once CROPX_THEME_DIR . 'inc/contact-form-api.php';
require_once CROPX_THEME_DIR . 'inc/block-shadow.php';
require_once CROPX_THEME_DIR . 'inc/image-corner-radius.php';
require_once CROPX_THEME_DIR . 'inc/popular-posts.php';
require_once CROPX_THEME_DIR . 'inc/workable-jobs-api.php';
