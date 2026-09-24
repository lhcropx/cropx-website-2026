<?php
/**
 * Pre-launch SEO hardening (Lauren, Sep 2026).
 *
 * Yoast SEO owns the site's virtual robots.txt (see the "# START YOAST
 * BLOCK" content and the auto-generated Sitemap: line — there's no physical
 * robots.txt file to edit). This appends the launch-specific Disallow rules
 * Yoast doesn't add on its own: wp-admin, the internal thank-you page, and
 * search/preview URLs, none of which should show up in search results.
 *
 * Guarded require in functions.php, same pattern as inc/legacy-redirects.php —
 * this ships in a small WP File Manager patch and that extraction process
 * has a documented history of dropping files (see CLAUDE.md), so a missing
 * copy of this file should just mean "robots.txt is a little looser than
 * intended" rather than a site-wide fatal error.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'robots_txt', function ( $output, $public ) {
	// If "Discourage search engines from indexing this site" (Settings >
	// Reading) is still checked, $public is '0' and WordPress's own
	// "Disallow: /" block already handles this — adding narrower rules
	// alongside a blanket disallow would just be noise. Get that setting
	// flipped before launch; see the go-live checklist in PROGRESS.md.
	if ( '0' === (string) $public ) {
		return $output;
	}

	$output .= "\n# ---------------------------\n";
	$output .= "# CropX launch rules\n";
	$output .= "# ---------------------------\n";
	$output .= "User-agent: *\n";
	$output .= "Disallow: /wp-admin/\n";
	$output .= "Allow: /wp-admin/admin-ajax.php\n";
	$output .= "Disallow: /thank-you/\n";
	$output .= "Disallow: /search/\n";
	$output .= "Disallow: /*?s=\n";
	$output .= "Disallow: /*preview=true\n";

	return $output;
}, 20, 2 );
