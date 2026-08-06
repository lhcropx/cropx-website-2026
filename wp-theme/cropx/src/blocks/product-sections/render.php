<?php
/**
 * Product Sections block — front-end render.
 *
 * Outputs:
 *   1. Sticky jump nav — anchor links that highlight as you scroll.
 *   2. Platform section — always visible, full pg-card grid.
 *   3. Hardware section — always visible, full pg-card grid.
 *
 * Both sections are always in the DOM (no hidden attribute). The jump nav
 * shows which section is currently in view via JavaScript / IntersectionObserver.
 *
 * Card layout mirrors the product-grid block: text left (1fr), photo right
 * (126px fixed). Scroll behaviour is handled by view.js.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$uid = wp_unique_id( 'psec-' );

// Jump nav labels
$bg_color = $attributes['bgColor'] ?? 'taupe';
if ( ! in_array( $bg_color, array( 'taupe', 'white' ), true ) ) {
	$bg_color = 'taupe';
}

$platform_label = $attributes['platformLabel'] ?? 'CropX Platform';
$hardware_label = $attributes['hardwareLabel'] ?? 'CropX Hardware';

// Per-section headers
$platform_eyebrow = $attributes['platformEyebrow'] ?? '';
$platform_heading = $attributes['platformHeading'] ?? '';
$platform_blurb   = $attributes['platformBlurb']   ?? '';

$hardware_eyebrow = $attributes['hardwareEyebrow'] ?? '';
$hardware_heading = $attributes['hardwareHeading'] ?? '';
$hardware_blurb   = $attributes['hardwareBlurb']   ?? '';

// Items
$platform_items = $attributes['platformItems'] ?? [];
$hardware_items = $attributes['hardwareItems'] ?? [];

// Section IDs — unique per block instance so multiple blocks on one page work correctly.
$id_platform = $uid . '-platform';
$id_hardware = $uid . '-hardware';

// Arrow SVG — same style as product-grid block
$svg_arrow = '<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

/**
 * Render the card grid HTML for one section.
 *
 * Wrapped in function_exists() so multiple block instances on the same page
 * don't trigger a PHP "Cannot redeclare" fatal error.
 *
 * @param array  $items      Array of item attribute objects.
 * @param string $svg_arrow  Pre-built arrow SVG string.
 * @return string            HTML string.
 */
if ( ! function_exists( 'cropx_psec_render_grid' ) ) :
function cropx_psec_render_grid( array $items, string $svg_arrow ): string {
	if ( empty( $items ) ) {
		return '';
	}

	$html = '<div class="pg-grid">';

	foreach ( $items as $item ) {
		$name    = esc_html( $item['name']        ?? '' );
		$desc    = esc_html( $item['description'] ?? '' );
		$url     = esc_url( cropx_url( $item['url'] ?? '#' ) );
		$img_url = esc_url( $item['photoUrl']     ?? '' );
		$img_alt = esc_attr( $item['photoAlt']    ?? $name );

		// Photo positioning
		$focal_x = round( floatval( $item['photoFocalX'] ?? 0.5 ) * 100 );
		$focal_y = round( floatval( $item['photoFocalY'] ?? 0.5 ) * 100 );
		$zoom    = intval( $item['photoZoom']   ?? 100 );

		// Overlay
		$overlay_type     = $item['overlayType']    ?? 'none';
		$overlay_url      = esc_url( $item['overlayUrl']    ?? '' );
		$overlay_padding  = intval( $item['overlayPadding'] ?? 0 );
		$overlay_h        = intval( $item['overlayH']       ?? 100 );
		$overlay_x        = intval( $item['overlayX']       ?? 0 );
		$overlay_y        = intval( $item['overlayY']       ?? 0 );
		$overlay_centered = ! empty( $item['overlayCentered'] );
		$overlay_anchor   = $item['overlayAnchor'] ?? 'center';
		// Clamp 0–20: how far the card-bleed illustration is allowed to
		// poke out above the top of the card before it gets clipped.
		$overlay_top_bleed = max( 0, min( 20, intval( $item['overlayTopBleed'] ?? 0 ) ) );

		// Card modifier classes
		$card_classes = [ 'pg-item' ];
		if ( $overlay_type === 'card-bleed' && $overlay_centered ) {
			$card_classes[] = 'pg-item--overlay-centered';
		}
		if ( $overlay_type === 'card-bleed' && $overlay_anchor === 'bottom' ) {
			$card_classes[] = 'pg-item--overlay-bottom';
		}
		$card_class_str = implode( ' ', $card_classes );

		// Inline CSS vars for overlay
		$card_style = '';
		if ( $overlay_type === 'card-bleed' && $overlay_url ) {
			$card_style .= "--pg-overlay-h:{$overlay_h}%;";
			if ( ! $overlay_centered ) {
				$card_style .= "--pg-overlay-x:{$overlay_x}px;";
			}
			if ( $overlay_y ) {
				$card_style .= "--pg-overlay-y:{$overlay_y}px;";
			}
			if ( $overlay_top_bleed > 0 ) {
				// Negative — extends the clip-path's top inset outward so the
				// illustration can render up to this many px above the card.
				$card_style .= "--pg-overlay-top-bleed:-{$overlay_top_bleed}px;";
			}
		}
		$card_style_attr = $card_style ? ' style="' . esc_attr( $card_style ) . '"' : '';

		$html .= '<a class="' . esc_attr( $card_class_str ) . '" href="' . $url . '"' . $card_style_attr . '>';

		// Photo column — right (order: 2 via CSS)
		$thumb_style = "--pg-photo-focal-x:{$focal_x}%; --pg-photo-focal-y:{$focal_y}%; --pg-photo-zoom:{$zoom}";
		$html .= '<div class="pg-thumb-outer">';
		$html .= '<div class="pg-thumb" style="' . esc_attr( $thumb_style ) . '">';
		if ( $img_url ) {
			$html .= '<img class="pg-thumb-img" src="' . $img_url . '" alt="' . $img_alt . '" loading="lazy">';
		}
		if ( $overlay_type === 'contained' && $overlay_url ) {
			$pad = $overlay_padding ? ' style="padding-block:' . esc_attr( $overlay_padding ) . '%"' : '';
			$html .= '<img class="pg-overlay--contained" src="' . $overlay_url . '" alt=""' . $pad . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		$html .= '</div>'; // .pg-thumb
		$html .= '</div>'; // .pg-thumb-outer

		// Text column — left
		$html .= '<div class="pg-text">';
		$html .= '<strong class="pg-name">';
		$html .= $name . '&nbsp;';
		$html .= '<span class="pg-arrow">' . $svg_arrow . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$html .= '</strong>';
		if ( $desc ) {
			$html .= '<p class="pg-desc">' . $desc . '</p>';
		}
		$html .= '</div>'; // .pg-text

		// Card-bleed overlay — wrapped in .pg-overlay-clip so it can be allowed
		// to bleed above the card's top edge (via --pg-overlay-top-bleed).
		if ( $overlay_type === 'card-bleed' && $overlay_url ) {
			$html .= '<div class="pg-overlay-clip"><img class="pg-overlay--card-bleed" src="' . $overlay_url . '" alt=""></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		$html .= '</a>'; // .pg-item
	}

	$html .= '</div>'; // .pg-grid
	return $html;
}
endif; // function_exists( 'cropx_psec_render_grid' )
?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => 'psec-block psec-block--bg-' . $bg_color, 'data-section-bg' => $bg_color ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php /* ── Sticky jump nav ── */ ?>
	<div class="psec-jump-wrapper">
		<div class="psec-inner">
			<nav class="psec-jump-bar" aria-label="<?php esc_attr_e( 'Jump to section', 'cropx' ); ?>">
				<a
					class="psec-jump-tab is-active"
					href="#<?php echo esc_attr( $id_platform ); ?>"
				><?php echo esc_html( $platform_label ); ?></a>

				<a
					class="psec-jump-tab"
					href="#<?php echo esc_attr( $id_hardware ); ?>"
				><?php echo esc_html( $hardware_label ); ?></a>
			</nav>
		</div>
	</div>

	<?php /* ── Platform section ── */ ?>
	<section
		id="<?php echo esc_attr( $id_platform ); ?>"
		class="psec-section"
	>
		<div class="psec-section-content">
			<div class="psec-inner">
				<?php if ( $platform_eyebrow || $platform_heading || $platform_blurb ) : ?>
				<header class="psec-panel-header">
					<?php if ( $platform_eyebrow ) : ?>
						<span class="psec-panel-eyebrow"><?php echo esc_html( $platform_eyebrow ); ?></span>
					<?php endif; ?>
					<?php if ( $platform_heading ) : ?>
						<h2 class="psec-panel-heading"><?php echo esc_html( $platform_heading ); ?></h2>
					<?php endif; ?>
					<?php if ( $platform_blurb ) : ?>
						<p class="psec-panel-blurb"><?php echo esc_html( $platform_blurb ); ?></p>
					<?php endif; ?>
				</header>
				<?php endif; ?>
				<?php echo cropx_psec_render_grid( $platform_items, $svg_arrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</section>

	<?php /* ── Hardware section ── */ ?>
	<section
		id="<?php echo esc_attr( $id_hardware ); ?>"
		class="psec-section"
	>
		<div class="psec-section-content">
			<div class="psec-inner">
				<?php if ( $hardware_eyebrow || $hardware_heading || $hardware_blurb ) : ?>
				<header class="psec-panel-header">
					<?php if ( $hardware_eyebrow ) : ?>
						<span class="psec-panel-eyebrow"><?php echo esc_html( $hardware_eyebrow ); ?></span>
					<?php endif; ?>
					<?php if ( $hardware_heading ) : ?>
						<h2 class="psec-panel-heading"><?php echo esc_html( $hardware_heading ); ?></h2>
					<?php endif; ?>
					<?php if ( $hardware_blurb ) : ?>
						<p class="psec-panel-blurb"><?php echo esc_html( $hardware_blurb ); ?></p>
					<?php endif; ?>
				</header>
				<?php endif; ?>
				<?php echo cropx_psec_render_grid( $hardware_items, $svg_arrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</section>

</div>
