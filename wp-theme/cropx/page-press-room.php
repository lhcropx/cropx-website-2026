<?php
/**
 * Press Room — page-press-room.php
 *
 * WordPress auto-selects this template for the Page whose slug is
 * "press-room" (the page-{slug}.php template hierarchy) — no manual "Page
 * Template" selection needed in Page Attributes. Just make sure the Page's
 * slug is exactly "press-room".
 *
 * Covers the "Press Room" category plus all of its child categories (Company
 * News, Press Releases, Product Updates & Releases) in one curated post
 * listing. Since this group is just one top-level category, the filter pills
 * are its direct children instead of the category itself (a "Press Room"
 * pill next to "All" would be redundant — both would show identical
 * content) — see the 'children' pill_mode in inc/insights-archive.php's
 * registry. Each child category is also independently browsable at its own
 * /category/{slug}/ URL using the exact same design — see category.php,
 * which shares all its rendering logic with this file via
 * inc/insights-archive.php.
 *
 * This is the "single category" example of a category archive group.
 * page-insights.php (Ag Insights & Research) is the two-category example.
 *
 * The hero content above the grid is this Page's own Gutenberg content —
 * add a hero block (or anything else) to it normally in the block editor.
 * category.php pulls this same content by looking up the "press-room" page
 * directly, so both views share an identical header.
 *
 * Design mirrors archive-cropx_publication.php (Results & Research) and
 * page-insights.php closely: same grid/card/pill/show-more structure,
 * agr-* CSS namespace.
 *
 * Assets:
 *   styles/ag-archive.css   — enqueued on is_page('press-room') / is_category() in inc/enqueue.php
 *   assets/js/ag-archive.js — enqueued + localized in inc/enqueue.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// ── Nav ───────────────────────────────────────────────────────────────────────
$GLOBALS['cropx_nav_already_rendered'] = true;
cropx_render_nav( array( 'login_url' => '#' ) );

// ── Hero — this Page's own Gutenberg content ───────────────────────────────────
while ( have_posts() ) :
	the_post();
	the_content();
endwhile;
wp_reset_postdata();

// ── Combined grid: Press Room + all of its child categories ───────────────────
$press_room_group = cropx_get_archive_group_context( 'press-room' );

// See page-insights.php for why this guard exists — an empty category__in
// array makes WP_Query silently ignore the filter and return every post.
$press_room_query_ids = ! empty( $press_room_group['all_ids'] ) ? $press_room_group['all_ids'] : array( 0 );

$press_room_query = new WP_Query( array(
	'post_type'      => 'post',
	'category__in'   => $press_room_query_ids,
	'posts_per_page' => get_option( 'posts_per_page' ),
	'paged'          => 1,
) );

// null = we're on the group's own combined page, so "All" is the active pill.
$press_room_pills = cropx_build_archive_group_pills( $press_room_group, null );

cropx_render_insights_grid(
	$press_room_query,
	$press_room_query_ids,
	$press_room_group['all_ids'],
	$press_room_group['accent_ids'],
	$press_room_pills,
	$press_room_group['heading'],
	$press_room_group['intro']
);

wp_reset_postdata();

// ── Newsletter CTA ──────────────────────────────────────────────────────────────
echo do_blocks( '<!-- wp:cropx/newsletter-cta {"bgColor":"taupe"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

// ── Pre-footer CTA ──────────────────────────────────────────────────────────────
echo do_blocks( '<!-- wp:cropx/pre-footer-cta /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
