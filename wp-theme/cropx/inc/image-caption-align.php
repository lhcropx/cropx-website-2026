<?php
/**
 * Caption text-alignment toggle for the core Image block.
 *
 * The sitewide default (styles/content.css .wp-block-image figcaption) centers
 * every image caption. Some photos — especially portrait/editorial shots with
 * a long descriptive caption, like a person-in-the-field photo — read better
 * with a left-aligned caption that lines up with the image's left edge
 * instead of floating centered underneath it.
 *
 * This gives editors a plain on/off toggle in the block sidebar (see the
 * "Caption" panel in src/admin/editor-panels.js) rather than requiring anyone
 * to know CSS or type a class name into the Advanced panel. Flipping it on
 * just adds the `caption-align-left` class already defined in content.css —
 * the same class a developer could still add manually via Additional CSS
 * Class(es) if ever needed, so both paths land on the exact same styling.
 *
 * Attribute is registered SERVER-SIDE ONLY, via register_block_type_args —
 * WordPress automatically syncs server-registered block attributes to the
 * client editor at boot (the same mechanism inc/image-corner-radius.php and
 * inc/block-shadow.php rely on) — no client-side attribute schema needed.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Inject the cropxCaptionAlign attribute into core/image at registration time.
 */
add_filter( 'register_block_type_args', function ( $args, $block_type ) {
	if ( 'core/image' !== $block_type ) {
		return $args;
	}
	if ( ! isset( $args['attributes'] ) ) {
		$args['attributes'] = [];
	}
	$args['attributes']['cropxCaptionAlign'] = [
		'type'    => 'string',
		'default' => '',
	];
	return $args;
}, 10, 2 );

/**
 * When set to 'left', add the caption-align-left class to the block's
 * <figure> wrapper so content.css's opt-in rule applies. Anything else
 * (the default '') is left completely alone — no markup change, sitewide
 * centered caption stays exactly as it was.
 */
add_filter( 'render_block_core/image', function ( $block_content, $block ) {
	$align = $block['attrs']['cropxCaptionAlign'] ?? '';

	if ( 'left' !== $align ) {
		return $block_content;
	}

	if ( class_exists( 'WP_HTML_Tag_Processor' ) ) {
		$tags = new WP_HTML_Tag_Processor( $block_content );
		if ( $tags->next_tag( 'figure' ) ) {
			$tags->add_class( 'caption-align-left' );
			return $tags->get_updated_html();
		}
		return $block_content;
	}

	// Fallback for older WP — merge into an existing class="" on <figure> if
	// present, otherwise add one.
	if ( preg_match( '/<figure\b[^>]*\bclass="([^"]*)"/', $block_content, $m ) ) {
		$merged = trim( $m[1] ) . ' caption-align-left';
		return preg_replace( '/(<figure\b[^>]*\bclass=")[^"]*(")/', '$1' . esc_attr( $merged ) . '$2', $block_content, 1 );
	}
	return preg_replace( '/<figure\s/', '<figure class="caption-align-left" ', $block_content, 1 );
}, 10, 2 );
