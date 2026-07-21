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

/**
 * Resolve the fill colour for the hero curved-swoop bottom-right corner.
 *
 * The swoop SVG path must match the background of the section directly below
 * the hero. When the page author places a CropX block next, we read that
 * block's bgColor attribute and return the matching hex value. When native
 * WordPress content follows (paragraphs, headings, etc.) there is no bgColor
 * to read, so we fall back to white — which matches WordPress's default body
 * background for pages that use the base theme styles.
 *
 * A manual override (the swoopFill block attribute) takes priority over
 * auto-detection; pass an empty string to trigger auto-detection.
 *
 * @param  string $block_name  Full block name of the calling hero, e.g. 'cropx/hero-curved'.
 * @param  string $manual      Manual override value ('white', 'taupe', 'deep-blue', …) or ''
 *                             for auto-detect.
 * @return string              Hex colour string, e.g. '#ffffff'.
 */
function cropx_get_swoop_fill( string $block_name, string $manual = '' ): string {
	// Map of bgColor attribute token values → hex colours.
	$color_map = array(
		'white'          => '#ffffff',
		'taupe'          => '#f3f1f1',
		'deep-blue'      => '#243565',
		'dark'           => '#243565',
		'blue'           => '#0CA8C0',
		'cropx-blue'     => '#0CA8C0',
		'gold'           => '#E9C242',
		'terra'          => '#E48C4D',
		'new-leaf'       => '#96C05A',
	);

	// Manual override takes priority.
	if ( $manual !== '' && isset( $color_map[ $manual ] ) ) {
		return $color_map[ $manual ];
	}

	// Auto-detect: parse the current post's block list and find the first
	// non-empty block that immediately follows our hero block.
	$post = get_post();
	if ( ! $post || empty( $post->post_content ) ) {
		return '#fbfaf9';
	}

	$blocks = parse_blocks( $post->post_content );
	$found  = false;

	foreach ( $blocks as $block ) {
		if ( $found ) {
			// Skip null/whitespace-only blocks that WordPress inserts between blocks.
			if ( null === $block['blockName'] ) {
				continue;
			}
			$bg = $block['attrs']['bgColor'] ?? '';
			return isset( $color_map[ $bg ] ) ? $color_map[ $bg ] : '#fbfaf9';
		}
		if ( $block['blockName'] === $block_name ) {
			$found = true;
		}
	}

	// Hero is last block, or only native content follows → match the site's
	// body background (#fbfaf9, taupe-50) set in theme.json. This is the correct
	// default for privacy/terms/legal pages with native WordPress content.
	return '#fbfaf9';
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
