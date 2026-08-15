<?php
/**
 * Category archive — category.php
 *
 * WordPress's template for every /category/{slug}/ URL (more specific than
 * archive.php, which now only handles tag and date archives — see the note
 * in archive.php's docblock).
 *
 * Dispatches to whichever registered category archive group (see
 * inc/insights-archive.php) the queried category belongs to — currently
 * "Ag Insights" (page-insights.php) or "News" (page-news.php) — sharing
 * that group's exact design, hero content, and filter pills (if any — a
 * single-category group like Ag Insights has none, see page-insights.php).
 *
 * The pre_get_posts filter in inc/insights-archive.php has already widened
 * the main query for this request to include the queried category's child
 * categories, not just posts assigned directly to it.
 *
 * A category outside every registered group (a leftover/legacy category, if
 * one still exists) still renders with this same layout, scoped to itself +
 * its own children — it just won't show a hero or any filter pills.
 *
 * Assets:
 *   styles/ag-archive.css   — enqueued on is_page('insights')/is_page('news')/is_category() in inc/enqueue.php
 *   assets/js/ag-archive.js — enqueued + localized in inc/enqueue.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// ── Nav ───────────────────────────────────────────────────────────────────────
$GLOBALS['cropx_nav_already_rendered'] = true;
cropx_render_nav( array( 'login_url' => '#' ) );

// ── Which registered group (if any) does this category belong to? ─────────────
$queried_cat = get_queried_object();
$is_wp_term  = $queried_cat instanceof WP_Term;
$group_slug  = $is_wp_term ? cropx_get_term_archive_group_slug( $queried_cat->term_id ) : '';

if ( $group_slug ) {

	$group = cropx_get_archive_group_context( $group_slug );

	// ── Hero — same content as the group's own combined page, pulled in externally ──
	cropx_render_archive_group_hero( $group_slug );

	$pills   = cropx_build_archive_group_pills( $group, $queried_cat );
	$heading = $queried_cat->name;
	$intro   = $queried_cat->description
		? $queried_cat->description
		: sprintf( __( 'Browse CropX articles on %s.', 'cropx' ), $queried_cat->name );

	$badge_ids   = $group['all_ids'];
	$accent_ids  = $group['accent_ids'];
	$show_badges = $group['show_badges'];

	// Tags actually used across the WHOLE group (not just the category being
	// viewed) — same scope page-insights.php / page-news.php use, so the tag
	// row stays constant across every page within a group. Drops the H2 +
	// intro column in cropx_render_insights_grid() when non-empty — see its
	// docblock. Aug 2026: originally Ag Insights only, extended here so
	// /category/{slug}/ views within any group match their own combined page.
	$tags = cropx_get_archive_group_tags( $group['all_ids'] );

} else {

	// Category outside any registered group — generic fallback: same design,
	// scoped to itself + its own children, but no hero and no filter pills.
	$pills   = array();
	$heading = $is_wp_term ? $queried_cat->name : __( 'Articles', 'cropx' );
	$intro   = ( $is_wp_term && $queried_cat->description )
		? $queried_cat->description
		: __( 'Browse CropX articles on this topic.', 'cropx' );

	$badge_ids   = $is_wp_term ? cropx_category_and_children_ids( $queried_cat->term_id ) : array();
	$accent_ids  = array();
	$show_badges = true;
	$tags        = array();

}

$query_category_ids = $is_wp_term ? cropx_category_and_children_ids( $queried_cat->term_id ) : array();

// ── Grid — uses the main query, already widened to include child categories ──
global $wp_query;
cropx_render_insights_grid( $wp_query, $query_category_ids, $badge_ids, $accent_ids, $pills, $heading, $intro, $tags, $show_badges );

// ── Newsletter CTA ──────────────────────────────────────────────────────────────
// White, matching both group hub pages (page-insights.php, page-news.php) —
// was taupe until Lauren flagged the mismatch on /category/ag-insights/ (Aug 2026).
echo do_blocks( '<!-- wp:cropx/newsletter-cta {"bgColor":"white"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

// ── Pre-footer CTA ──────────────────────────────────────────────────────────────
// Each registered group's category pages get its own "Cat. Pg. Demo Form +
// CTA" pattern instead of the plain default block — Ag Insights (Aug 2026),
// News (Aug 2026). Looked up by slug so this stays correct across
// environments; falls back to the plain block if the pattern hasn't been
// created yet on this environment, so nothing goes missing. Ungrouped
// categories are unaffected — they keep the plain default block.
$_group_pfc_patterns = array(
	'insights' => 'insights-cat-pg-demo-form-cta',
	'news'     => 'news-cat-pg-demo-form-cta',
);
$_cat_pfc_ref = isset( $_group_pfc_patterns[ $group_slug ] )
	? cropx_get_synced_block_ref( $_group_pfc_patterns[ $group_slug ] )
	: null;
echo do_blocks( $_cat_pfc_ref
	? '<!-- wp:block {"ref":' . $_cat_pfc_ref . '} /-->'
	: '<!-- wp:cropx/pre-footer-cta /-->'
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
