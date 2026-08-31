<?php
/**
 * Story Tag taxonomy archive — taxonomy-cropx_story_tag.php
 *
 * Handles filtered views:
 *   /story-tag/irrigation/
 *   /story-tag/corn/
 *   …any Story Tag added to a Customer Story post in the editor.
 *
 * Renders the exact same "Results & Research" Page (slug: results, see
 * page-results.php) used at /results/ — same hero, same everything —
 * instead of a separate template, mirroring taxonomy-cropx_content_type.php.
 * The [cropx_customer_stories_grid] shortcode embedded in that Page's content
 * auto-detects is_tax( 'cropx_story_tag' ) and filters + highlights itself
 * to the active tag on its own, so there's nothing tag-specific to do here
 * beyond rendering the Page.
 *
 * Falls back to just the grid (no hero) if the "results" Page doesn't exist
 * yet, so a filtered view never renders blank.
 *
 * Uses the full 'the_content' filter chain (via setup_postdata + the_content())
 * rather than do_blocks() alone — do_blocks() only renders Gutenberg blocks,
 * so the Shortcode block's raw [cropx_customer_stories_grid] text would print
 * literally instead of expanding, since do_shortcode() is a separate filter
 * on 'the_content' that do_blocks() doesn't run.
 *
 * Story Tags were made public alongside this template (Aug 2026) — see the
 * cropx_story_tag registration in inc/cpts.php for the "why now" and the
 * one-time permalink flush required after deploying.
 *
 * Uses cropx_get_results_page() rather than get_page_by_path( 'results' ) —
 * the latter silently returns nothing because this Page is nested under a
 * parent (currently /knowledge-hub/results/) and get_page_by_path() requires
 * the full hierarchical path when given just a leaf slug. Getting this wrong
 * was why the hero was missing on every /story-tag/{term}/ view until this
 * fix (Aug 2026) — same root cause as the earlier Ag Insights hero bug.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$GLOBALS['cropx_nav_already_rendered'] = true;
cropx_render_nav( array( 'login_url' => CROPX_LOGIN_URL ) );

$results_page = cropx_get_results_page();

if ( $results_page && ! empty( $results_page->post_content ) ) {
	global $post;
	$cropx_original_post = $post;
	$post = $results_page; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	setup_postdata( $post );
	the_content();
	wp_reset_postdata();
	$post = $cropx_original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
} else {
	cropx_render_customer_stories_grid();
}

// ── Newsletter CTA ──────────────────────────────────────────────────────────────
// White, matching page-results.php and taxonomy-cropx_content_type.php (Aug 2026).
echo do_blocks( '<!-- wp:cropx/newsletter-cta {"bgColor":"white"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

// ── Pre-footer CTA ──────────────────────────────────────────────────────────────
// Lauren's "Results Cat. Pg. Demo Form + CTA" pattern, same as
// taxonomy-cropx_content_type.php and both single Customer Story templates
// (Aug 2026). Looked up by slug so this stays correct across environments;
// falls back to the plain default block if the pattern hasn't been created
// yet on this environment, so nothing goes missing.
$_tag_pfc_ref = cropx_get_synced_block_ref( 'results-cat-pg-demo-form-cta' );
echo do_blocks( $_tag_pfc_ref
	? '<!-- wp:block {"ref":' . $_tag_pfc_ref . '} /-->'
	: '<!-- wp:cropx/pre-footer-cta /-->'
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
