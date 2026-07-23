<?php
/**
 * Block separator control.
 *
 * Injects a `sectionSeparator` string attribute (default 'shadow') into every
 * cropx/* block at registration time — no individual block.json changes needed.
 *
 * Values:
 *   'shadow'  (default) — subtle upward drop shadow; CSS default rule handles it
 *   'none'              — no separator at all
 *
 * Note: 'line' and 'both' were removed. Any block still storing those values
 * is remapped to 'shadow' by the render_block filter below.
 *
 * For any non-default value, a data-separator="…" attribute is injected into
 * the block wrapper so the CSS override rules in tokens.css can target it with
 * higher specificity than the baseline shadow rule.
 *
 * Editor UI (SelectControl) lives in src/admin/editor-panels.js, compiled to
 * build/admin/editor-panels.js and enqueued by inc/enqueue.php.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Inject sectionSeparator into every cropx/* block at registration time.
 */
add_filter( 'register_block_type_args', function ( $args, $block_type ) {
	if ( ! str_starts_with( (string) $block_type, 'cropx/' ) ) {
		return $args;
	}
	if ( ! isset( $args['attributes'] ) ) {
		$args['attributes'] = [];
	}
	$args['attributes']['sectionSeparator'] = [
		'type'    => 'string',
		'default' => 'shadow',
		'enum'    => [ 'shadow', 'none' ],
	];
	return $args;
}, 10, 2 );

/**
 * For non-default separator values, inject data-separator="…" before the
 * first class="…" in the rendered HTML so the CSS override rules apply.
 * The 'shadow' default needs no attribute — the baseline CSS rule handles it.
 */
add_filter( 'render_block', function ( $block_content, $block ) {
	if ( ! str_starts_with( (string) ( $block['blockName'] ?? '' ), 'cropx/' ) ) {
		return $block_content;
	}
	$separator = $block['attrs']['sectionSeparator'] ?? 'shadow';
	// Remap removed options to the default.
	if ( in_array( $separator, [ 'line', 'both' ], true ) ) {
		$separator = 'shadow';
	}
	if ( ! in_array( $separator, [ 'shadow', 'none' ], true ) ) {
		$separator = 'shadow';
	}
	if ( 'shadow' === $separator ) {
		return $block_content; // default — CSS handles it, no attribute needed
	}
	return preg_replace(
		'/\bclass="/',
		'data-separator="' . esc_attr( $separator ) . '" class="',
		$block_content,
		1
	);
}, 10, 2 );
