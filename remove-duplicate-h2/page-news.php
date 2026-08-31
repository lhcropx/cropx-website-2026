<?php
/**
 * News — page-news.php
 *
 * WordPress auto-selects this template for the Page whose slug is
 * "news" (the page-{slug}.php template hierarchy) — no manual "Page
 * Template" selection needed in Page Attributes. Just make sure the Page's
 * slug is exactly "news".
 *
 * Formerly "Press Room" (page-press-room.php) — the Page, its category, and
 * this template were all renamed together in Aug 2026. If this stops
 * rendering the grid/newsletter/pre-footer sections and falls back to a
 * bare page, the most likely cause is the Page's slug no longer being
 * exactly "news" (WordPress silently falls back to the default page
 * template on any page-{slug}.php mismatch — it will NOT warn you).
 *
 * Covers the "News" category plus all of its child categories (Company
 * News, Press Releases, Product Updates & Releases) in one curated post
 * listing. Since this group is just one top-level category, the filter pills
 * are its direct children instead of the category itself (a "News" pill
 * next to "All" would be redundant — both would show identical content) —
 * see the 'children' pill_mode in inc/insights-archive.php's registry. Each
 * child category is also independently browsable at its own /category/{slug}/
 * URL using the exact same design — see category.php, which shares all its
 * rendering logic with this file via inc/insights-archive.php.
 *
 * This is the "single category" example of a category archive group.
 * page-insights.php (Ag Insights & Research) is the two-category example.
 *
 * Header layout (Aug 2026): the H2 + intro copy is dropped in favor of a
 * "Filter by topic" tag pill row, same treatment Ag Insights got — but
 * unlike Ag Insights, News keeps its category-children pills too (they still
 * do real filtering work here), so both pill rows render side by side — tags
 * on the left, categories on the right. See cropx_render_insights_grid()'s
 * "dual pill" layout in inc/insights-archive.php. The tag row stays empty
 * until at least one News post has a tag assigned in the editor.
 *
 * The hero content above the grid is this Page's own Gutenberg content —
 * add a hero block (or anything else) to it normally in the block editor.
 * category.php pulls this same content by looking up the "news" page
 * directly, so both views share an identical header.
 *
 * Design mirrors archive-cropx_publication.php (Results & Research) and
 * page-insights.php closely: same grid/card/pill/show-more structure,
 * agr-* CSS namespace.
 *
 * Assets:
 *   styles/ag-archive.css   — enqueued on is_page('news') / is_category() in inc/enqueue.php
 *   assets/js/ag-archive.js — enqueued + localized in inc/enqueue.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// ── Nav ───────────────────────────────────────────────────────────────────────
$GLOBALS['cropx_nav_already_rendered'] = true;
cropx_render_nav( array( 'login_url' => CROPX_LOGIN_URL ) );

// ── Hero — this Page's own Gutenberg content ───────────────────────────────────
while ( have_posts() ) :
	the_post();
	the_content();
endwhile;
wp_reset_postdata();

// ── Combined grid: News + all of its child categories ──────────────────────────
$news_group = cropx_get_archive_group_context( 'news' );

// See page-insights.php for why this guard exists — an empty category__in
// array makes WP_Query silently ignore the filter and return every post.
$news_query_ids = ! empty( $news_group['all_ids'] ) ? $news_group['all_ids'] : array( 0 );

$news_query = new WP_Query( array(
	'post_type'      => 'post',
	'category__in'   => $news_query_ids,
	'posts_per_page' => get_option( 'posts_per_page' ),
	'paged'          => 1,
) );

// null = we're on the group's own combined page, so "All" is the active pill.
$news_pills = cropx_build_archive_group_pills( $news_group, null );

// Tags actually used across all News posts (Company News, Press Releases,
// Product Updates & Releases combined) — same helper Ag Insights uses,
// scoped to the whole group rather than just the current view so the tag
// row stays constant no matter which News category page you land on.
$news_tags = cropx_get_archive_group_tags( $news_group['all_ids'] );

cropx_render_insights_grid(
	$news_query,
	$news_query_ids,
	$news_group['all_ids'],
	$news_group['accent_ids'],
	$news_pills,
	$news_group['heading'],
	$news_group['intro'],
	$news_tags,
	$news_group['show_badges'],
	'categories',
	true // hide_heading_text — this Page's own hero content (above) already shows "News" as an H1; repeating it here would be redundant (Aug 2026). No visible change today since News' category pills already fill this slot, but keeps this consistent with page-insights.php and safe if that ever changes.
);

wp_reset_postdata();

// ── Newsletter CTA ──────────────────────────────────────────────────────────────
echo do_blocks( '<!-- wp:cropx/newsletter-cta {"bgColor":"white"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

// ── Demo Contact Form ────────────────────────────────────────────────────────────
// Pattern slug 'demo-contact-form' — looked up by slug so this stays correct
// across environments (local/staging/production all assign different numeric
// post IDs to the same pattern). Renders nothing if the pattern doesn't exist
// yet on this environment, so nothing breaks if it hasn't been created here.
$_demo_form_ref = cropx_get_synced_block_ref( 'demo-contact-form' );
if ( $_demo_form_ref ) {
	echo do_blocks( '<!-- wp:block {"ref":' . $_demo_form_ref . '} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

// ── Pre-footer CTA ──────────────────────────────────────────────────────────────
// Independently editable from wp-admin via a synced pattern (create it once
// under Appearance → Patterns with slug "press-room-pre-footer-cta" —
// see cropx_get_synced_block_ref() in inc/helpers.php). Falls back to the
// plain default block until that pattern exists, so nothing goes missing.
// Deliberately left at its original "press-room-pre-footer-cta" slug rather
// than renamed to match the Aug 2026 News rename — this pattern already has
// live custom content under that slug, and renaming the lookup here without
// also renaming the pattern in wp-admin would silently revert this page to
// the plain default CTA. Rename both together if you want the slug to match.
// This is a separate pattern from Ag Insights' / Customer Results' — editing
// one does NOT affect the others, or any other page's Pre-footer CTA.
$_pfc_ref = cropx_get_synced_block_ref( 'press-room-pre-footer-cta' );
echo do_blocks( $_pfc_ref
	? '<!-- wp:block {"ref":' . $_pfc_ref . '} /-->'
	: '<!-- wp:cropx/pre-footer-cta /-->'
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
