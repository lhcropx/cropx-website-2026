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

define( 'CROPX_THEME_VERSION', '0.4.16' );
define( 'CROPX_THEME_DIR',     trailingslashit( get_template_directory() ) );
define( 'CROPX_THEME_URI',     trailingslashit( get_template_directory_uri() ) );

// Single source of truth for the sitewide "Log in" button destination — used
// as the default in inc/parts/nav.php and referenced by every template that
// calls cropx_render_nav() directly (the nav isn't an editable block/pattern
// on any page; it's a PHP partial called from ~20 template files). Change
// this one line rather than hunting down every call site.
define( 'CROPX_LOGIN_URL', 'https://myfarm.cropx.com/login' );

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
require_once CROPX_THEME_DIR . 'inc/image-caption-align.php';
require_once CROPX_THEME_DIR . 'inc/popular-posts.php';
require_once CROPX_THEME_DIR . 'inc/workable-jobs-api.php';
require_once CROPX_THEME_DIR . 'inc/accessibility-patches.php';

// Guarded the same way inc/duplicate-post.php is above — this is a brand-new
// file going out in a small targeted WP File Manager patch, which has a
// documented history of missing files on extraction (see CLAUDE.md). A
// missing legacy-redirects.php should just mean "those 3 old URLs still
// 404" rather than a site-wide fatal error.
if ( file_exists( CROPX_THEME_DIR . 'inc/legacy-redirects.php' ) ) {
	require_once CROPX_THEME_DIR . 'inc/legacy-redirects.php';
}
