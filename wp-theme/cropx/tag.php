<?php
/**
 * Tag archive — tag.php
 *
 * WordPress's template for every /tag/{slug}/ URL — more specific than
 * archive.php, which used to handle tag archives too (via the generic ba-*
 * blog design, same as every other tag link on the site). Lauren flagged
 * that inconsistency once the Ag Insights tag list started linking out to
 * tags (Aug 2026): a tag click landed on a page that looked nothing like
 * the page it came from. This gives every tag its own page in the same
 * polished agr-* Ag Insights design instead — sharing all of its rendering
 * logic with category.php and page-insights.php via inc/insights-archive.php.
 * archive.php now only handles date archives — see its docblock.
 *
 * Unlike a category, a tag has no registered "group" of its own (see
 * inc/insights-archive.php's registry) and CAN span posts from completely
 * different categories — an Ag Insights post and a News post might both be
 * tagged "sustainability." So there's no filter-pill row here (nothing
 * meaningful to filter by), and each card's badge falls back to that post's
 * own first category — cropx_get_archive_post_badge() already does this by
 * default when handed an empty scope, so this template just passes empty
 * badge/accent arrays rather than a group's scope.
 *
 * That said, a tag CAN still effectively "belong" to one group in practice —
 * every tag on the Ag Insights page's own tag list, for instance, is only
 * ever used on Ag Insights posts. cropx_get_tag_archive_group_slug() checks
 * for exactly that (every post carrying the tag falls within one group's
 * categories) and, when true, this template shows that group's hero content
 * and swaps in its pre-footer pattern — same treatment as category.php,
 * added Aug 2026 after Lauren noticed the tag pages linked from /insights/
 * were missing both.
 *
 * The main $wp_query is already correctly scoped by WordPress — tags have no
 * hierarchy, so unlike category.php there's no pre_get_posts rewrite needed
 * here to pull in "child" tags.
 *
 * Show More pagination (ag-archive.js) needs to know to filter by tag ID
 * rather than category ID when it fetches additional pages — that's what
 * cropx_render_insights_grid()'s trailing 'tags' argument below is for (see
 * its $filter_param docblock).
 *
 * Assets:
 *   styles/ag-archive.css   — enqueued on is_tag() in inc/enqueue.php
 *   assets/js/ag-archive.js — enqueued + localized in inc/enqueue.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// ── Nav ───────────────────────────────────────────────────────────────────────
$GLOBALS['cropx_nav_already_rendered'] = true;
cropx_render_nav( array( 'login_url' => CROPX_LOGIN_URL ) );

// ── Queried tag ─────────────────────────────────────────────────────────────────
$queried_tag     = get_queried_object();
$is_wp_term      = $queried_tag instanceof WP_Term;
$tag_group_slug  = $is_wp_term ? cropx_get_tag_archive_group_slug( $queried_tag ) : '';

// ── Hero — same content as the owning group's own combined page, if any ───────
if ( $tag_group_slug ) {
	cropx_render_archive_group_hero( $tag_group_slug );
}

$heading = $is_wp_term ? $queried_tag->name : __( 'Articles', 'cropx' );
$intro   = ( $is_wp_term && $queried_tag->description )
	? $queried_tag->description
	: __( 'Browse CropX articles on this topic.', 'cropx' );

// The REST filter scope for Show More — a single tag ID here, not a category
// list, hence the 'tags' filter_param passed to cropx_render_insights_grid()
// below so ag-archive.js requests &tags= instead of &categories=.
$query_ids = $is_wp_term ? array( $queried_tag->term_id ) : array();

// ── Grid — uses the main query, already correctly scoped to this tag ──────────
global $wp_query;
cropx_render_insights_grid(
	$wp_query,
	$query_ids,
	array(), // badge scope — empty on purpose, see docblock above.
	array(), // accent scope — no accent colour on a mixed-category tag view.
	array(), // pills — nothing meaningful to filter a single tag by.
	$heading,
	$intro,
	array(), // tags — no secondary tag list on a tag page itself.
	true,    // show_badges — on, since posts here can span categories.
	'tags'
);

// ── Newsletter CTA ──────────────────────────────────────────────────────────────
// White, matching category.php and both group hub pages — see category.php's
// comment on this same line for why (Aug 2026).
echo do_blocks( '<!-- wp:cropx/newsletter-cta {"bgColor":"white"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

// ── Pre-footer CTA ──────────────────────────────────────────────────────────────
// Same swap as category.php: a tag "owned" by a registered group gets that
// group's "Cat. Pg. Demo Form + CTA" pattern instead of the plain default
// block — Ag Insights (Aug 2026), News (Aug 2026).
$_group_pfc_patterns = array(
	'insights' => 'insights-cat-pg-demo-form-cta',
	'news'     => 'news-cat-pg-demo-form-cta',
);
$_tag_pfc_ref = isset( $_group_pfc_patterns[ $tag_group_slug ] )
	? cropx_get_synced_block_ref( $_group_pfc_patterns[ $tag_group_slug ] )
	: null;
echo do_blocks( $_tag_pfc_ref
	? '<!-- wp:block {"ref":' . $_tag_pfc_ref . '} /-->'
	: '<!-- wp:cropx/pre-footer-cta /-->'
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
