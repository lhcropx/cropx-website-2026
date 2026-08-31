<?php
/**
 * Legacy URL redirects — inc/legacy-redirects.php
 *
 * Small, hand-maintained 301 redirect map for old CPT archive slugs that
 * moved during the Aug 2026 "simple CPT templates" work (see PROGRESS.md).
 * This is NOT a substitute for the Redirection plugin the project still
 * needs to install for the full old-site → new-site URL migration (see
 * PROGRESS.md's "URGENT: No redirects exist for already-changed blog/results
 * permalinks" item) — it's a narrow, purpose-built stopgap for exactly the
 * 3 slugs below, added because nothing else was in place to redirect them
 * yet and leaving them 404ing wasn't acceptable in the meantime.
 *
 * Each entry is an exact root-relative path match (trailing slash optional),
 * guarded by is_404() so this can never hijack a real page — if WordPress
 * successfully resolves something at one of these paths, this code doesn't
 * run at all.
 *
 *   /resources/  → /resources-archive/   (cropx_resource archive moved here — inc/cpts.php)
 *   /team/       → /team-archive/        (cropx_team_member archive moved here — inc/cpts.php)
 *   /dealers/    → /solutions/dealers/   (deliberately NOT /dealer-archive/ — /dealers/ was
 *                                          never this CPT's home, it's the interactive
 *                                          dealer-finder map Page's URL; see the cropx_dealer
 *                                          registration comment in inc/cpts.php)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'template_redirect', function () {

	if ( ! is_404() ) {
		return;
	}

	$path = untrailingslashit( wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?? '' );

	$redirect_map = array(
		'/resources' => '/resources-archive/',
		'/team'      => '/team-archive/',
		'/dealers'   => '/solutions/dealers/',
	);

	if ( isset( $redirect_map[ $path ] ) ) {
		wp_safe_redirect( home_url( $redirect_map[ $path ] ), 301 );
		exit;
	}
} );
