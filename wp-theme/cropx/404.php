<?php
/**
 * 404 — Not Found template.
 *
 * WordPress loads this file automatically when no content is found for
 * the requested URL. The page is assembled from CropX blocks just like
 * any other page — do_blocks() renders the markup directly instead of
 * going through the normal posts loop (there is no post to query).
 *
 * Structure:
 *   Hero (soil-health photo, "404 Error" heading + helpful subheading, Contact Us CTA)
 *       └── Nav — rendered internally by hero-curved-standard itself (see
 *           cropx_nav_already_rendered check in its render.php). There is
 *           deliberately no separate wp:cropx/nav block here — this page
 *           used to have one, which produced two stacked nav bars (Aug 2026).
 *   Cards — Latest Results & Research  (queryMode: auto, default post type = Customer Stories)
 *   Cards — Recent Ag Industry Insights (queryMode: auto, single query, Blog Posts,
 *                                     categories: Company News, Ag Insights,
 *                                     Research, Press Releases)
 *   "404 Error Contact Form + CTA" pattern (slug '404-error-contact-form-cta' —
 *       looked up by slug so this stays correct across environments; carries
 *       its own anchor="contact" so the hero's Contact Us CTA still scrolls here.
 *       This pattern bundles its OWN pre-footer CTA block internally — that's
 *       what the "+ CTA" in its name means — so there is deliberately no
 *       separate wp:cropx/pre-footer-cta block below it. This page used to
 *       have one, which produced two stacked pre-footer CTAs (Aug 2026).)
 *   Footer                           (rendered by footer.php via get_footer())
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$staging = 'http://ec2-100-25-145-190.compute-1.amazonaws.com';

$blocks = <<<BLOCKS
<!-- wp:cropx/hero-curved-standard {"heading":"404 Error","subheading":"Looks like this page doesn’t exist — or it’s been moved. Browse our other content, or contact us if you need help with something specific.","ctaLabel":"Contact Us","ctaUrl":"#contact","bgImageId":181,"bgImageUrl":"{$staging}/wp-content/uploads/2026/06/cropx-soil-health-monitoring-technology.webp","bgFocalY":0.57,"bgZoom":108,"showEyebrow":false,"showDeviceImage":false,"showAppImage":false} /-->
<!-- wp:cropx/cards {"cardVariant":"dark","heading":"Latest Results & Research","queryMode":"auto"} /-->
<!-- wp:cropx/cards {"cardVariant":"dark","heading":"Recent Ag Industry Insights","queryMode":"auto","queryPostType":"post","queryAutoType":"single","queryCategories":["company-news","ag-insights","research","press-releases"]} /-->
BLOCKS;

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo do_blocks( $blocks );

// ── "404 Error Contact Form + CTA" pattern ──────────────────────────────────────
// Sits at the bottom of the page, same position the old contact-form block
// occupied. Looked up by slug so this stays correct across environments
// (numeric wp_block post IDs differ between local/staging/production).
// Renders nothing if the pattern doesn't exist yet on this environment, so
// nothing breaks if it hasn't been created here.
//
// No separate wp:cropx/pre-footer-cta block follows this — the pattern
// already contains its own pre-footer-cta block (that's the "+ CTA" in its
// name). Adding another one here duplicated the CTA band on the live page.
$_404_contact_ref = cropx_get_synced_block_ref( '404-error-contact-form-cta' );
if ( $_404_contact_ref ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo do_blocks( '<!-- wp:block {"ref":' . $_404_contact_ref . '} /-->' );
}

get_footer();
