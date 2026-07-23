<?php
/**
 * Product Grid block — front-end render.
 *
 * Single flat product card grid with optional section header (eyebrow,
 * heading with em underline support, body paragraph).
 *
 * Background variants: white | taupe | deep-blue.
 * Deep-blue variant adds an animated topo overlay; cards always stay white.
 *
 * Each item supports:
 *   - Background photo (photoId preferred, photoUrl as fallback)
 *   - Illustration overlay: card-bleed | contained | none
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$eyebrow      = $attributes['eyebrow']      ?? '';
$heading      = $attributes['heading']      ?? '';
$body         = $attributes['body']         ?? '';
$show_eyebrow = (bool) ( $attributes['showEyebrow'] ?? true );
$items        = $attributes['items']        ?? [];

$bg_color = $attributes['bgColor'] ?? 'white';
if ( ! in_array( $bg_color, [ 'white', 'taupe', 'deep-blue' ], true ) ) {
	$bg_color = 'white';
}

// Arrow SVG — shared with product-tabs block
$svg_arrow = '<svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

/**
 * Render the product card grid HTML.
 *
 * Uses shared pg-* card classes so photo focal point, zoom, and illustration
 * overlays work identically across product-grid and product-tabs blocks.
 *
 * Wrapped in function_exists() so multiple block instances on the same page
 * don't trigger a PHP "Cannot redeclare" fatal error.
 *
 * @param array  $items     Array of item attribute objects.
 * @param string $svg_arrow Pre-built arrow SVG string.
 * @return string           HTML string.
 */
if ( ! function_exists( 'cropx_pg_render_grid' ) ) :
function cropx_pg_render_grid( array $items, string $svg_arrow ): string {
	if ( empty( $items ) ) {
		return '';
	}

	$html = '<div class="pg-grid">';

	foreach ( $items as $item ) {
		$name    = esc_html( $item['name']        ?? '' );
		$desc    = esc_html( $item['description'] ?? '' );
		$url     = esc_url( cropx_url( $item['url'] ?? '#' ) );
		$img_url = $item['photoUrl'] ?? '';
		$img_alt = esc_attr( $item['photoAlt']   ?? $name );

		// Prefer WP attachment ID for proper srcset
		$photo_id = (int) ( $item['photoId'] ?? 0 );
		if ( $photo_id ) {
			$src = wp_get_attachment_image_src( $photo_id, 'large' );
			if ( $src ) {
				$img_url = $src[0];
				if ( ! ( $item['photoAlt'] ?? '' ) ) {
					$img_alt = esc_attr( get_post_meta( $photo_id, '_wp_attachment_image_alt', true ) ?: '' );
				}
			}
		}
		$img_url = esc_url( $img_url );

		// Overlay attachment
		$overlay_id  = (int) ( $item['overlayId'] ?? 0 );
		$overlay_url = $item['overlayUrl'] ?? '';
		if ( $overlay_id ) {
			$src = wp_get_attachment_image_src( $overlay_id, 'full' );
			if ( $src ) {
				$overlay_url = $src[0];
			}
		}
		$overlay_url = esc_url( $overlay_url );

		// Photo positioning
		$focal_x = round( floatval( $item['photoFocalX'] ?? 0.5 ) * 100, 1 );
		$focal_y = round( floatval( $item['photoFocalY'] ?? 0.5 ) * 100, 1 );
		$zoom    = floatval( $item['photoZoom'] ?? 100 );

		// Overlay settings
		$overlay_type     = $item['overlayType']    ?? 'none';
		$overlay_padding  = intval( $item['overlayPadding'] ?? 0 );
		$overlay_h        = floatval( $item['overlayH']     ?? 100 );
		$overlay_x        = floatval( $item['overlayX']     ?? 0 );
		$overlay_centered = ! empty( $item['overlayCentered'] );
		$overlay_anchor   = $item['overlayAnchor'] ?? 'center';

		if ( ! in_array( $overlay_type, [ 'none', 'card-bleed', 'contained' ], true ) ) {
			$overlay_type = 'none';
		}

		// Card modifier classes
		$card_classes = [ 'pg-item' ];
		if ( $overlay_type === 'card-bleed' && $overlay_centered ) {
			$card_classes[] = 'pg-item--overlay-centered';
		}
		if ( $overlay_type === 'card-bleed' && $overlay_anchor === 'bottom' ) {
			$card_classes[] = 'pg-item--overlay-bottom';
		}

		// Inline CSS vars for overlay sizing
		$card_style = '';
		if ( $overlay_type === 'card-bleed' && $overlay_url ) {
			$card_style .= '--pg-overlay-h: ' . $overlay_h . '%;';
			if ( ! $overlay_centered ) {
				$card_style .= ' --pg-overlay-x: ' . $overlay_x . 'px;';
			}
		}
		$card_style_attr = $card_style ? ' style="' . esc_attr( $card_style ) . '"' : '';

		$html .= '<a class="' . esc_attr( implode( ' ', $card_classes ) ) . '" href="' . $url . '"' . $card_style_attr . '>';

		// Photo column — right (order: 2 via CSS)
		$thumb_style = "--pg-photo-focal-x:{$focal_x}%; --pg-photo-focal-y:{$focal_y}%; --pg-photo-zoom:{$zoom}";
		$html .= '<div class="pg-thumb-outer">';
		$html .= '<div class="pg-thumb" style="' . esc_attr( $thumb_style ) . '">';
		if ( $img_url ) {
			$html .= '<img class="pg-thumb-img" src="' . $img_url . '" alt="' . $img_alt . '" loading="lazy">';
		}
		if ( $overlay_type === 'contained' && $overlay_url ) {
			$pad = $overlay_padding ? ' style="padding-block:' . $overlay_padding . '%"' : '';
			$html .= '<img class="pg-overlay--contained" src="' . $overlay_url . '" alt=""' . $pad . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		$html .= '</div>'; // .pg-thumb
		$html .= '</div>'; // .pg-thumb-outer

		// Text column — left
		$html .= '<div class="pg-text">';
		$html .= '<strong class="pg-name">';
		$html .= $name . '&nbsp;';
		$html .= '<span class="pg-arrow" aria-hidden="true">' . $svg_arrow . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$html .= '</strong>';
		if ( $desc ) {
			$html .= '<p class="pg-desc">' . $desc . '</p>';
		}
		$html .= '</div>'; // .pg-text

		// Card-bleed overlay — direct child of .pg-item (outside photo column)
		if ( $overlay_type === 'card-bleed' && $overlay_url ) {
			$html .= '<img class="pg-overlay--card-bleed" src="' . $overlay_url . '" alt="">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		$html .= '</a>'; // .pg-item
	}

	$html .= '</div>'; // .pg-grid
	return $html;
}
endif; // function_exists cropx_pg_render_grid

// Allow <em> in heading so authors can apply the accent underline to a key phrase
$heading_kses = [ 'em' => [] ];
?>
<section <?php echo get_block_wrapper_attributes( [ 'class' => 'pg-block pg-block--bg-' . $bg_color, 'data-section-bg' => $bg_color ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="pg-inner">

		<?php if ( $eyebrow || $heading || $body ) : ?>
		<header class="pg-header">
			<?php if ( $show_eyebrow && $eyebrow ) : ?>
				<span class="section-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( $heading ) : ?>
				<h2 class="pg-heading"><?php echo wp_kses( $heading, $heading_kses ); ?></h2>
			<?php endif; ?>
			<?php if ( $body ) : ?>
				<p class="pg-body"><?php echo esc_html( $body ); ?></p>
			<?php endif; ?>
		</header>
		<?php endif; ?>

		<?php echo cropx_pg_render_grid( $items, $svg_arrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	</div>
</section>
