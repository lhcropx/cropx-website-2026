<?php
/**
 * Theme helper functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Convert a same-domain absolute URL to a root-relative path.
 *
 * Use this for every link href output in render.php files. It strips the
 * site's own domain so links work correctly across local → staging → production
 * without needing database search-replace on deployment.
 *
 * Rules:
 *   - Empty string or '#'  → returned as-is
 *   - Same-domain URL      → domain stripped, relative path returned
 *   - External URL         → returned as-is
 *
 * Examples (on cropx.com):
 *   cropx_url('https://cropx.com/enterprise/')   → '/enterprise/'
 *   cropx_url('http://cropx-2026-2.local/about') → '/about'
 *   cropx_url('/already-relative')               → '/already-relative'
 *   cropx_url('https://example.com/page')        → 'https://example.com/page'
 *   cropx_url('#')                               → '#'
 *
 * DO NOT use for image src attributes — only for link hrefs and navigation URLs.
 *
 * @param  string $url The URL to normalise.
 * @return string      Root-relative path for same-domain URLs; original for all others.
 */
/**
 * Estimate reading time for a post.
 *
 * Strips HTML, counts words, and divides by a 200 wpm reading speed.
 * Returns a localised string like "5 min read".
 *
 * @param  int|null $post_id Post ID, or null to use the current global post.
 * @return string            Localised reading-time label.
 */
/**
 * Get the post ID of a synced WordPress pattern (wp_block CPT) by slug.
 *
 * Synced patterns are stored as wp_block posts. Their numeric IDs differ
 * between environments (local / staging / production), so we look them up
 * by slug which is consistent. Used by page pattern PHP files so they stay
 * portable across environments — no hardcoded IDs needed.
 *
 * Usage in a pattern file:
 *   $ref = cropx_get_synced_block_ref( 'segment-navigation' );
 *   if ( $ref ) echo '<!-- wp:block {"ref":' . $ref . '} /-->';
 *
 * @param  string $slug The wp_block post slug (e.g. 'segment-navigation').
 * @return int          The post ID, or 0 if the pattern doesn't exist yet.
 */
function cropx_get_synced_block_ref( string $slug ): int {
	$post = get_page_by_path( $slug, OBJECT, 'wp_block' );
	return $post ? (int) $post->ID : 0;
}

function cropx_reading_time( ?int $post_id = null ): string {
	$content    = get_post_field( 'post_content', $post_id );
	$word_count = str_word_count( wp_strip_all_tags( $content ) );
	$minutes    = max( 1, (int) round( $word_count / 200 ) );
	/* translators: %d: number of minutes */
	return sprintf( _n( '%d min read', '%d min read', $minutes, 'cropx' ), $minutes );
}

function cropx_url( string $url ): string {
	if ( empty( $url ) || $url === '#' ) {
		return $url;
	}

	// Already relative — nothing to do.
	if ( strncmp( $url, '/', 1 ) === 0 && strncmp( $url, '//', 2 ) !== 0 ) {
		return $url;
	}

	$home = untrailingslashit( home_url() );

	// Case-insensitive check: does this URL start with our home URL?
	if ( strncasecmp( $url, $home, strlen( $home ) ) === 0 ) {
		$path = substr( $url, strlen( $home ) );
		return $path !== '' ? $path : '/';
	}

	return $url;
}
