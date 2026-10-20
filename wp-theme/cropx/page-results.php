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
cropx_render_nav( array( 'login_url' => CROPX_LOGIN_URL ) );

// ── Page content — hero, [cropx_customer_stories_grid] shortcode, anything else ──
while ( have_posts() ) :
	the_post();
	the_content();
endwhile;
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
// under Appearance → Patterns with slug "results-pre-footer-cta" — see
// cropx_get_synced_block_ref() in inc/helpers.php). Falls back to the plain
// default block until that pattern exists, so nothing goes missing. This is
// a separate pattern from Press Room's / Ag Insights' — editing one does
// NOT affect the others, or any other page's Pre-footer CTA.
$_pfc_ref = cropx_get_synced_block_ref( 'results-pre-footer-cta' );
echo do_blocks( $_pfc_ref
	? '<!-- wp:block {"ref":' . $_pfc_ref . '} /-->'
	: '<!-- wp:cropx/pre-footer-cta /-->'
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

get_footer();
