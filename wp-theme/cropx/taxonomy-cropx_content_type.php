<?php
/**
 * Content Type taxonomy archive — taxonomy-cropx_content_type.php
 *
 * Handles filtered views:
 *   /content-type/case-study/
 *   /content-type/video-testimonial/
 *
 * Renders the exact same "Customer Results" Page (slug: results, see
 * page-results.php) used at /results/ — same hero, same everything —
 * instead of a separate template. The [cropx_customer_stories_grid]
 * shortcode embedded in that Page's content auto-detects is_tax() and
 * filters + highlights itself to the active term on its own, so there's
 * nothing term-specific to do here beyond rendering the Page.
 *
 * Falls back to just the grid (no hero) if the "results" Page doesn't exist
 * yet, so a filtered view never renders blank.
 *
 * Uses the full 'the_content' filter chain (via setup_postdata + the_content())
 * rather than do_blocks() alone — do_blocks() only renders Gutenberg blocks,
 * so the Shortcode block's raw [cropx_customer_stories_grid] text would print
 * literally instead of expanding, since do_shortcode() is a separate filter
 * on 'the_content' that do_blocks() doesn't run.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$GLOBALS['cropx_nav_already_rendered'] = true;
cropx_render_nav( array( 'login_url' => '#' ) );

$results_page = get_page_by_path( 'results' );

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
echo do_blocks( '<!-- wp:cropx/newsletter-cta {"bgColor":"taupe"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

// ── Pre-footer CTA ──────────────────────────────────────────────────────────────
echo do_blocks( '<!-- wp:cropx/pre-footer-cta /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
