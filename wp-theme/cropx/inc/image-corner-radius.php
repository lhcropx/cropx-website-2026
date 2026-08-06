<?php
/**
 * Per-corner border radius control for the core Image block.
 *
 * The sitewide default (--radius-photo, 60px 2px 60px 2px) is applied to
 * every core/image via the base `.wp-block-image img` rule in
 * styles/content.css. This adds four attributes so editors can override
 * that radius per image, corner by corner, without touching any other
 * image on the site.
 *
 * Attributes are registered SERVER-SIDE ONLY, via register_block_type_args.
 * WordPress automatically syncs server-registered block attributes to the
 * client editor at boot (the same mechanism inc/block-shadow.php relies on
 * for cropx/* blocks' sectionSeparator attribute) — no client-side
 * attribute schema is needed for core/image to pick these up.
 *
 * Empty attribute values (the default) mean "not customized" — the image
 * keeps whatever radius the surrounding CSS already gives it. The moment
 * any corner is set, all four are written explicitly (see the Inspector
 * panel in src/admin/editor-panels.js), so there's never ambiguity about
 * which corners are custom vs. inherited.
 *
 * The four values are applied as CSS custom properties on the block's
 * <figure> wrapper (not inline on the <img> itself) so the exact same
 * content.css rule drives both the front end (set here) and the editor
 * canvas (set by the matching editor.BlockListBlock filter in
 * editor-panels.js) — one rule, two sources, always in sync.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Inject the four radius attributes into core/image at registration time.
 */
add_filter( 'register_block_type_args', function ( $args, $block_type ) {
	if ( 'core/image' !== $block_type ) {
		return $args;
	}
	if ( ! isset( $args['attributes'] ) ) {
		$args['attributes'] = [];
	}
	foreach ( [ 'cropxRadiusTopLeft', 'cropxRadiusTopRight', 'cropxRadiusBottomRight', 'cropxRadiusBottomLeft' ] as $key ) {
		$args['attributes'][ $key ] = [
			'type'    => 'string',
			'default' => '',
		];
	}
	return $args;
}, 10, 2 );

/**
 * When any corner has been customized, set the four --cropx-img-radius-*
 * custom properties on the block's <figure> wrapper so content.css's
 * .wp-block-image img rule picks them up. Untouched (default) images are
 * left completely alone — no attribute injected, no markup change.
 */
add_filter( 'render_block_core/image', function ( $block_content, $block ) {
	$attrs = $block['attrs'] ?? [];
	$tl    = $attrs['cropxRadiusTopLeft']     ?? '';
	$tr    = $attrs['cropxRadiusTopRight']    ?? '';
	$br    = $attrs['cropxRadiusBottomRight'] ?? '';
	$bl    = $attrs['cropxRadiusBottomLeft']  ?? '';

	if ( '' === $tl && '' === $tr && '' === $br && '' === $bl ) {
		return $block_content;
	}

	$style = sprintf(
		'--cropx-img-radius-tl:%1$s;--cropx-img-radius-tr:%2$s;--cropx-img-radius-br:%3$s;--cropx-img-radius-bl:%4$s;',
		esc_attr( $tl ?: '0' ),
		esc_attr( $tr ?: '0' ),
		esc_attr( $br ?: '0' ),
		esc_attr( $bl ?: '0' )
	);

	// WP_HTML_Tag_Processor (6.2+) is the robust path — it merges cleanly
	// with any style the figure might already carry.
	if ( class_exists( 'WP_HTML_Tag_Processor' ) ) {
		$tags = new WP_HTML_Tag_Processor( $block_content );
		if ( $tags->next_tag( 'figure' ) ) {
			$existing = $tags->get_attribute( 'style' );
			$tags->set_attribute( 'style', ( $existing ? rtrim( trim( $existing ), ';' ) . ';' : '' ) . $style );
			return $tags->get_updated_html();
		}
		return $block_content;
	}

	// Fallback for older WP — merge into an existing style="" on <figure> if
	// present, otherwise add one.
	if ( preg_match( '/<figure\b[^>]*\bstyle="([^"]*)"/', $block_content, $m ) ) {
		$merged = rtrim( trim( $m[1] ), ';' ) . ';' . $style;
		return preg_replace( '/(<figure\b[^>]*\bstyle=")[^"]*(")/', '$1' . esc_attr( $merged ) . '$2', $block_content, 1 );
	}
	return preg_replace( '/<figure\s/', '<figure style="' . esc_attr( $style ) . '" ', $block_content, 1 );
}, 10, 2 );
