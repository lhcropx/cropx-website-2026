<?php
/**
 * Ag Insights & Research — page-insights.php
 *
 * WordPress auto-selects this template for the Page whose slug is "insights"
 * (the page-{slug}.php template hierarchy) — no manual "Page Template"
 * selection needed in Page Attributes. Just make sure the Page's slug is
 * exactly "insights".
 *
 * Combines two parent categories — "Ag Insights" and "Research" — plus all of
 * their child categories, into one curated post listing. Each parent category
 * is also independently browsable at its own /category/{slug}/ URL using the
 * exact same design — see category.php, which shares all its rendering logic
 * with this file via inc/insights-archive.php.
 *
 * This is the "two categories combined" example of a category archive group
 * (see inc/insights-archive.php's registry). page-press-room.php is the
 * simpler single-category example.
 *
 * The hero content above the grid is this Page's own Gutenberg content —
 * add a hero block (or anything else) to it normally in the block editor.
 * category.php pulls this same content by looking up the "insights" page
 * directly, so both views share an identical header.
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
cropx_render_nav( array( 'login_url' => '#' ) );

// ── Hero — this Page's own Gutenberg content ───────────────────────────────────
while ( have_posts() ) :
	the_post();
	the_content();
endwhile;
wp_reset_postdata();

// ── Combined grid: Ag Insights + Research + all of their child categories ─────
$insights_group = cropx_get_archive_group_context( 'insights' );

// WP_Query silently ignores an EMPTY category__in array (it falls back to
// "no filter" — i.e. every post on the site), so if both categories are
// somehow missing, use a category ID that can never exist instead of an
// empty array. That correctly yields zero results and the empty state below,
// rather than accidentally showing an unfiltered feed of every post.
$insights_query_ids = ! empty( $insights_group['all_ids'] ) ? $insights_group['all_ids'] : array( 0 );

$insights_query = new WP_Query( array(
	'post_type'      => 'post',
	'category__in'   => $insights_query_ids,
	'posts_per_page' => get_option( 'posts_per_page' ),
	'paged'          => 1,
) );

// null = we're on the group's own combined page, so "All" is the active pill.
$insights_pills = cropx_build_archive_group_pills( $insights_group, null );

cropx_render_insights_grid(
	$insights_query,
	$insights_query_ids,
	$insights_group['all_ids'],
	$insights_group['accent_ids'],
	$insights_pills,
	$insights_group['heading'],
	$insights_group['intro']
);

wp_reset_postdata();

// ── Newsletter CTA ──────────────────────────────────────────────────────────────
echo do_blocks( '<!-- wp:cropx/newsletter-cta {"bgColor":"taupe"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

// ── Pre-footer CTA ──────────────────────────────────────────────────────────────
echo do_blocks( '<!-- wp:cropx/pre-footer-cta /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
