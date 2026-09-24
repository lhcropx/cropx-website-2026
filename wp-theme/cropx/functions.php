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

define( 'CROPX_THEME_VERSION', '0.4.76' );
define( 'CROPX_THEME_DIR',     trailingslashit( get_template_directory() ) );
define( 'CROPX_THEME_URI',     trailingslashit( get_template_directory_uri() ) );

// Single source of truth for the sitewide "Log in" button destination — used
// as the default in inc/parts/nav.php and referenced by every template that
// calls cropx_render_nav() directly (the nav isn't an editable block/pattern
// on any page; it's a PHP partial called from ~20 template files). Change
// this one line rather than hunting down every call site.
define( 'CROPX_LOGIN_URL', 'https://myfarm.cropx.com/login' );

// Newsletter CTA — hidden sitewide until an ESP is chosen (Sep 2026). The
// block's own form currently has no real backend (its <form> posts to
// action="#" — see cropx/newsletter-cta's render.php); Lauren asked to hide
// it everywhere rather than remove it, so it's ready to switch back on the
// moment an email platform is picked and the form is wired to it. This
// filter blanks the block's output wherever it appears — home.php,
// single.php, the Ag Insights/News/Results & Research archive templates, and
// any Page where it's been placed manually via the editor — without having
// to touch each of those call sites individually. To bring it back: flip
// this one constant to false. No rebuild needed, this is PHP-only.
define( 'CROPX_HIDE_NEWSLETTER_CTA', true );
add_filter( 'render_block_cropx/newsletter-cta', function ( $block_content ) {
	return CROPX_HIDE_NEWSLETTER_CTA ? '' : $block_content;
} );

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

// Same guarded pattern as above — small launch-day patch, WP File Manager
// extraction can drop files (see CLAUDE.md).
if ( file_exists( CROPX_THEME_DIR . 'inc/seo-launch.php' ) ) {
	require_once CROPX_THEME_DIR . 'inc/seo-launch.php';
}

// Media URL Cleanup, Zoho Form URL Fix, and Unicode Escape Repair were
// one-time admin tools for already-resolved data-cleanup incidents (see
// PROGRESS.md). Removed Sep 2026 once Lauren confirmed all affected content
// was fixed; nothing else in the theme depended on these files.

// CropX System → Platform Rename and Migrate White Backgrounds were
// one-time admin tools for already-resolved content-cleanup passes (see
// PROGRESS.md). Removed Sep 2026 at Lauren's request; their inc/ files have
// been deleted from the repo too, not just unrequired here.

// TEMPORARY diagnostic tool (Tools → CropX Diagnostics): deliberately
// reproduces the wp_update_post() save path used by the tools above, on a
// no-op self-replace ("aggregates" -> "aggregates"), to help pin down
// whether that save pathway alone causes the recurring unicode-escape
// corruption independent of TranslatePress. Guarded the same way as the
// other one-time tools. REMOVE once this diagnostic test has served its
// purpose — see the file's own doc comment for full context.
if ( file_exists( CROPX_THEME_DIR . 'inc/self-replace-diagnostic-test.php' ) ) {
	require_once CROPX_THEME_DIR . 'inc/self-replace-diagnostic-test.php';
}
