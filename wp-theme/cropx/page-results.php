<?php
/**
 * Customer Results — page-results.php
 *
 * WordPress auto-selects this template for the Page whose slug is "results"
 * (the page-{slug}.php template hierarchy) — no manual "Page Template"
 * selection needed. Just make sure the Page's slug is exactly "results".
 * Title can be anything — "Customer Results" is the working name.
 *
 * Unlike the Ag Insights / Press Room pages, the customer stories grid here
 * is NOT hardcoded to a fixed position. It's inserted via a Shortcode block
 * placed anywhere in this Page's content:
 *
 *     [cropx_customer_stories_grid]
 *
 * That means a hero, intro copy, testimonials, or anything else can go
 * above, below, or around it, in any order, using the normal block editor.
 * The shortcode itself is registered in inc/customer-stories.php and renders
 * the filter-pill + card-grid + Show More design shared with the taxonomy
 * filtered views below.
 *
 * The CPT's automatic archive is turned off (has_archive => false in
 * inc/cpts.php) specifically so this Page can occupy /results/ — single
 * customer story permalinks (/results/{content-type}/{post-name}/) are
 * unaffected, since those use a separate, explicit rewrite rule.
 *
 * /content-type/{term}/ filtered views (e.g. /content-type/case-study/)
 * render this SAME Page's content — see taxonomy-cropx_content_type.php —
 * so both share an identical layout. The shortcode auto-detects is_tax()
 * and filters + highlights itself to the active term with no extra setup.
 *
 * Assets:
 *   styles/pub-archive.css   — enqueued via inc/customer-stories.php's
 *   assets/js/pub-archive.js   shortcode presence check in inc/enqueue.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// ── Nav ───────────────────────────────────────────────────────────────────────
$GLOBALS['cropx_nav_already_rendered'] = true;
cropx_render_nav( array( 'login_url' => '#' ) );

// ── Page content — hero, [cropx_customer_stories_grid] shortcode, anything else ──
while ( have_posts() ) :
	the_post();
	the_content();
endwhile;
wp_reset_postdata();

// ── Newsletter CTA ──────────────────────────────────────────────────────────────
echo do_blocks( '<!-- wp:cropx/newsletter-cta {"bgColor":"taupe"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

// ── Pre-footer CTA ──────────────────────────────────────────────────────────────
echo do_blocks( '<!-- wp:cropx/pre-footer-cta /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
