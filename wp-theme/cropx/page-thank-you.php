<?php
/**
 * Post-Submit Thank You — page-thank-you.php
 *
 * WordPress auto-selects this template for the Page whose slug is
 * "thank-you" (the page-{slug}.php template hierarchy) — no manual "Page
 * Template" selection needed. Just make sure the Page's slug is exactly
 * "thank-you". If a different slug is preferred, rename this file to
 * page-{slug}.php to match.
 *
 * Modeled directly on 404.php's layout, per Lauren's request (Aug 2026):
 * same two "three column card layout" (cropx/cards, Auto query mode)
 * blocks, assembled the same way via do_blocks() rather than the normal
 * posts loop — there's no meaningful post content to edit for this page,
 * it's fully composed here.
 *
 * Structure:
 *   Hero — "Post-Submit Thank You Hero" pattern (looked up by slug so this
 *       stays correct across environments). Nav is rendered internally by
 *       whatever hero block the pattern wraps (see cropx_nav_already_rendered
 *       check in the curved hero render.php files) — do NOT add a separate
 *       wp:cropx/nav block here, or it'll double up (see 404.php's note on
 *       the same gotcha). Falls back to a plain hero-curved-standard with
 *       generic thank-you copy if the pattern doesn't exist yet on this
 *       environment.
 *   Cards — Latest Results & Research   (queryMode: auto, default post type = Customer Stories)
 *   Cards — Recent Ag Industry Insights (queryMode: auto, single query, Blog Posts,
 *                                        categories: Company News, Ag Insights,
 *                                        Research, Press Releases)
 *   "Post-Submit Thank You Pre-Footer CTA" pattern (slug
 *       'post-submit-thank-you-pre-footer-cta') — replaces the standard
 *       wp:cropx/pre-footer-cta block. Falls back to the standard block if
 *       the pattern doesn't exist yet on this environment.
 *   Footer                              (rendered by footer.php via get_footer())
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// ── Hero — "Post-Submit Thank You Hero" pattern ─────────────────────────────────
$_ty_hero_ref = cropx_get_synced_block_ref( 'post-submit-thank-you-hero' );
if ( $_ty_hero_ref ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo do_blocks( '<!-- wp:block {"ref":' . $_ty_hero_ref . '} /-->' );
} else {
	// Safe fallback so the page still renders (with nav) if the pattern
	// hasn't been created yet on this environment.
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo do_blocks( '<!-- wp:cropx/hero-curved-standard {"heading":"Thank You","subheading":"We’ve received your submission — a member of our team will be in touch soon.","showEyebrow":false,"showDeviceImage":false,"showAppImage":false} /-->' );
}

// ── Card grids — identical to 404.php ───────────────────────────────────────────
$blocks = <<<BLOCKS
<!-- wp:cropx/cards {"cardVariant":"dark","heading":"Latest Results & Research","queryMode":"auto"} /-->
<!-- wp:cropx/cards {"cardVariant":"dark","heading":"Recent Ag Industry Insights","queryMode":"auto","queryPostType":"post","queryAutoType":"single","queryCategories":["company-news","ag-insights","research","press-releases"]} /-->
BLOCKS;

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo do_blocks( $blocks );

// ── "Post-Submit Thank You Pre-Footer CTA" pattern ──────────────────────────────
// Looked up by slug so this stays correct across environments (numeric
// wp_block post IDs differ between local/staging/production). Falls back to
// the standard pre-footer CTA block if the pattern doesn't exist yet here.
$_ty_pfc_ref = cropx_get_synced_block_ref( 'post-submit-thank-you-pre-footer-cta' );
if ( $_ty_pfc_ref ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo do_blocks( '<!-- wp:block {"ref":' . $_ty_pfc_ref . '} /-->' );
} else {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo do_blocks( '<!-- wp:cropx/pre-footer-cta /-->' );
}

get_footer();
