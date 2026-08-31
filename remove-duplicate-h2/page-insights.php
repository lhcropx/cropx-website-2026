<?php
/**
 * Ag Insights — page-insights.php
 *
 * WordPress auto-selects this template for the Page whose slug is "insights"
 * (the page-{slug}.php template hierarchy) — no manual "Page Template"
 * selection needed in Page Attributes. Just make sure the Page's slug is
 * exactly "insights".
 *
 * As of Aug 2026 this is a single-category group — "Research" was folded
 * back into being just another Ag Insights post rather than its own category
 * (every post that had "Research" was already also tagged "Ag Insights", so
 * nothing was orphaned). See inc/insights-archive.php's registry. Because
 * there's only one category now, there's nothing left to filter between:
 * no category pills, no per-card category badge. In their place, a "Filter
 * by topic" tag list (cropx_get_archive_group_tags()) — real WP tags used on
 * these posts, reusing the exact same pill markup the category filter used.
 *
 * The category is still independently browsable at /category/ag-insights/
 * using the same design — see category.php, which shares all its rendering
 * logic with this file via inc/insights-archive.php.
 *
 * The hero content above the grid is this Page's own Gutenberg content —
 * add a hero block (or anything else) to it normally in the block editor.
 * category.php pulls this same content by looking up the "insights" page
 * directly, so both views share an identical header. NOTE: the hero block's
 * own heading (currently "Ag Insights & Research") lives in that Gutenberg
 * content, in the database — not in this template — so it wasn't touched by
 * the Aug 2026 rename below. Update it in the block editor if you want the
 * hero to read "Ag Insights" too.
 *
 * Design mirrors archive-cropx_publication.php (Results & Research) closely:
 * same grid/card/pill/show-more structure, agr-* CSS namespace instead of pa-*.
 *
 * Assets:
 *   styles/ag-archive.css   — enqueued on is_page('insights') / is_category() in inc/enqueue.php
 *   assets/js/ag-archive.js — enqueued + localized in inc/enqueue.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// ── Nav ───────────────────────────────────────────────────────────────────────
// Always render the nav here. A global flag tells hero blocks placed via the
// editor to skip their own internal cropx_render_nav() call, preventing a
// double-nav — same mechanism used by archive-cropx_publication.php.
$GLOBALS['cropx_nav_already_rendered'] = true;
cropx_render_nav( array( 'login_url' => CROPX_LOGIN_URL ) );

// ── Hero — this Page's own Gutenberg content ───────────────────────────────────
while ( have_posts() ) :
	the_post();
	the_content();
endwhile;
wp_reset_postdata();

// ── Grid: Ag Insights + its child categories ───────────────────────────────
$insights_group = cropx_get_archive_group_context( 'insights' );

// WP_Query silently ignores an EMPTY category__in array (it falls back to
// "no filter" — i.e. every post on the site), so if the category is somehow
// missing, use a category ID that can never exist instead of an empty array.
// That correctly yields zero results and the empty state below, rather than
// accidentally showing an unfiltered feed of every post.
$insights_query_ids = ! empty( $insights_group['all_ids'] ) ? $insights_group['all_ids'] : array( 0 );

$insights_query = new WP_Query( array(
	'post_type'      => 'post',
	'category__in'   => $insights_query_ids,
	'posts_per_page' => get_option( 'posts_per_page' ),
	'paged'          => 1,
) );

// A single-category group has no pills (see cropx_build_archive_group_pills())
// — the tag list below takes over that spot in the header instead.
$insights_pills = cropx_build_archive_group_pills( $insights_group, null );
$insights_tags  = cropx_get_archive_group_tags( $insights_query_ids );

cropx_render_insights_grid(
	$insights_query,
	$insights_query_ids,
	$insights_group['all_ids'],
	$insights_group['accent_ids'],
	$insights_pills,
	$insights_group['heading'],
	$insights_group['intro'],
	$insights_tags,
	$insights_group['show_badges'],
	'categories',
	true // hide_heading_text — this Page's own hero content (above) already shows "Ag Insights" as an H1; repeating it here would be redundant (Aug 2026).
);

wp_reset_postdata();

// ── Newsletter CTA ──────────────────────────────────────────────────────────────
echo do_blocks( '<!-- wp:cropx/newsletter-cta {"bgColor":"white"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

// ── Demo Contact Form ────────────────────────────────────────────────────────────
// Pattern slug 'demo-contact-form' — looked up by slug so this stays correct
// across environments. Renders nothing if the pattern doesn't exist yet here.
$_demo_form_ref = cropx_get_synced_block_ref( 'demo-contact-form' );
if ( $_demo_form_ref ) {
	echo do_blocks( '<!-- wp:block {"ref":' . $_demo_form_ref . '} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

// ── Pre-footer CTA ──────────────────────────────────────────────────────────────
// Independently editable from wp-admin via a synced pattern (create it once
// under Appearance → Patterns with slug "insights-pre-footer-cta" — see
// cropx_get_synced_block_ref() in inc/helpers.php). Falls back to the plain
// default block until that pattern exists, so nothing goes missing. This is
// a separate pattern from Press Room's / Customer Results' — editing one
// does NOT affect the others, or any other page's Pre-footer CTA.
$_pfc_ref = cropx_get_synced_block_ref( 'insights-pre-footer-cta' );
echo do_blocks( $_pfc_ref
	? '<!-- wp:block {"ref":' . $_pfc_ref . '} /-->'
	: '<!-- wp:cropx/pre-footer-cta /-->'
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
