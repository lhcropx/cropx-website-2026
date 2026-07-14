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
 *   Nav
 *   Hero (soil-health photo, "404 Error" heading + helpful subheading, Contact Us CTA)
 *   Cards — Recent Case Studies    (queryMode: auto)
 *   Cards — Latest Industry Insights (queryMode: auto)
 *   Contact Form
 *   Pre-footer CTA
 *   Footer                         (rendered by footer.php via get_footer())
 *
 * NOTE: Both cards blocks currently use queryMode:"auto" without a
 * queryPostType attribute, so they will default to the same post type
 * (blog posts). If you want "Recent Case Studies" to show cropx_publication
 * entries instead, add "queryPostType":"cropx_publication" to that block's
 * attributes in the $blocks string below.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$staging = 'http://ec2-100-25-145-190.compute-1.amazonaws.com';

$blocks = <<<BLOCKS
<!-- wp:cropx/nav /-->
<!-- wp:cropx/hero-curved-standard {"heading":"404 Error","subheading":"Looks like this page doesn’t exist — or it’s been moved. Browse our other content, or contact us if you need help with something specific..","ctaLabel":"Contact Us","ctaUrl":"#contact","bgImageId":181,"bgImageUrl":"{$staging}/wp-content/uploads/2026/06/cropx-soil-health-monitoring-technology.webp","bgFocalY":0.57,"bgZoom":108,"showEyebrow":false,"showDeviceImage":false,"showAppImage":false} /-->
<!-- wp:cropx/cards {"cardVariant":"dark","heading":"Recent Case Studies","queryMode":"auto"} /-->
<!-- wp:cropx/cards {"cardVariant":"dark","heading":"Latest Industry Insights","queryMode":"auto"} /-->
<!-- wp:cropx/contact-form {"anchor":"contact"} /-->
<!-- wp:cropx/pre-footer-cta {"backgroundImageId":578,"backgroundImageUrl":"{$staging}/wp-content/uploads/2026/06/cropx-smart-farm-field-sensors-farmer-agronomist-tablet.webp","bgFocalX":0,"bgFocalY":0.26,"bgZoom":112} /-->
BLOCKS;

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo do_blocks( $blocks );

get_footer();
