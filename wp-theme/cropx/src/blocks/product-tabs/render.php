<?php
/**
 * Product Tabs block — front-end render.
 *
 * Outputs:
 *   1. Section header (eyebrow + heading)
 *   2. Sticky tab strip with WAI-ARIA roles
 *   3. Two panels — each with a panel header + ptabs-grid of horizontal cards
 *
 * Card layout mirrors the product-grid block: text left (1fr), photo right
 * (126px fixed). Tab switching is handled by view.js.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$uid = wp_unique_id( 'ptabs-' );

// Tab labels
$platform_label = $attributes['platformLabel'] ?? 'CropX Platform';
$hardware_label = $attributes['hardwareLabel'] ?? 'CropX Hardware';

// Per-panel headers
$platform_eyebrow = $attributes['platformEyebrow'] ?? '';
$platform_heading = $attributes['platformHeading'] ?? '';
$platform_blurb   = $attributes['platformBlurb']   ?? '';

$hardware_eyebrow = $attributes['hardwareEyebrow'] ?? '';
$hardware_heading = $attributes['hardwareHeading'] ?? '';
$hardware_blurb   = $attributes['hardwareBlurb']   ?? '';

// Items
$platform_items = $attributes['platformItems'] ?? [];
$hardware_items = $attributes['hardwareItems'] ?? [];

// IDs for aria wiring
$tab_platform   = $uid . '-tab-platform';
$tab_hardware   = $uid . '-tab-hardware';
$panel_platform = $uid . '-panel-platform';
$panel_hardware = $uid . '-panel-hardware';

// Arrow SVG — same style as product-grid block
$svg_arrow = '<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

/**
 * Render the card grid HTML for one panel.
 *
 * Uses pg-* card classes (same as the product-grid block) so photo focal point,
 * zoom, and illustration overlay work identically. The pg-* CSS is included in
 * this block's style.css so it's self-contained even without product-grid.
 *
 * Wrapped in function_exists() because render.php is executed once per block
 * instance, and a page with multiple product-tabs blocks would otherwise trigger
 * a PHP "Cannot redeclare" fatal error.
 *
 * @param array  $items      Array of item attribute objects.
 * @param string $svg_arrow  Pre-built arrow SVG string.
 * @return string            HTML string.
 */
if ( ! function_exists( 'cropx_ptabs_render_grid' ) ) :
function cropx_ptabs_render_grid( array $items, string $svg_arrow ): string {
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
		$overlay_centered = ! empty( $item['overlayCentered'] );
		$overlay_anchor   = $item['overlayAnchor'] ?? 'center';

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

		// Card-bleed overlay — direct child of the card (not inside the photo column)
		if ( $overlay_type === 'card-bleed' && $overlay_url ) {
			$html .= '<img class="pg-overlay--card-bleed" src="' . $overlay_url . '" alt="">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		$html .= '</a>'; // .pg-item
	}

	$html .= '</div>'; // .pg-grid
	return $html;
}
endif; // function_exists( 'cropx_ptabs_render_grid' )
?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => 'ptabs-block' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<div class="ptabs-strip-wrapper">
		<div class="ptabs-inner">
			<div
				class="ptabs-tab-bar"
				role="tablist"
				aria-label="<?php echo esc_attr( $platform_label . ' / ' . $hardware_label ); ?>"
			>
				<button
					id="<?php echo esc_attr( $tab_platform ); ?>"
					class="ptabs-tab"
					role="tab"
					aria-selected="true"
					aria-controls="<?php echo esc_attr( $panel_platform ); ?>"
					tabindex="0"
					type="button"
				><?php echo esc_html( $platform_label ); ?></button>

				<button
					id="<?php echo esc_attr( $tab_hardware ); ?>"
					class="ptabs-tab"
					role="tab"
					aria-selected="false"
					aria-controls="<?php echo esc_attr( $panel_hardware ); ?>"
					tabindex="-1"
					type="button"
				><?php echo esc_html( $hardware_label ); ?></button>
			</div>
		</div>
	</div>

	<?php /* ── Platform panel ── */ ?>
	<div
		id="<?php echo esc_attr( $panel_platform ); ?>"
		class="ptabs-panel"
		role="tabpanel"
		aria-labelledby="<?php echo esc_attr( $tab_platform ); ?>"
		tabindex="0"
	>
		<div class="ptabs-panel-content">
			<div class="ptabs-inner">
				<?php if ( $platform_eyebrow || $platform_heading || $platform_blurb ) : ?>
				<header class="ptabs-panel-header">
					<?php if ( $platform_eyebrow ) : ?>
						<span class="ptabs-panel-eyebrow"><?php echo esc_html( $platform_eyebrow ); ?></span>
					<?php endif; ?>
					<?php if ( $platform_heading ) : ?>
						<h2 class="ptabs-panel-heading"><?php echo esc_html( $platform_heading ); ?></h2>
					<?php endif; ?>
					<?php if ( $platform_blurb ) : ?>
						<p class="ptabs-panel-blurb"><?php echo esc_html( $platform_blurb ); ?></p>
					<?php endif; ?>
				</header>
				<?php endif; ?>
				<?php echo cropx_ptabs_render_grid( $platform_items, $svg_arrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</div>

	<?php /* ── Hardware panel ── */ ?>
	<div
		id="<?php echo esc_attr( $panel_hardware ); ?>"
		class="ptabs-panel"
		role="tabpanel"
		aria-labelledby="<?php echo esc_attr( $tab_hardware ); ?>"
		tabindex="0"
		hidden
	>
		<div class="ptabs-panel-content">
			<div class="ptabs-inner">
				<?php if ( $hardware_eyebrow || $hardware_heading || $hardware_blurb ) : ?>
				<header class="ptabs-panel-header">
					<?php if ( $hardware_eyebrow ) : ?>
						<span class="ptabs-panel-eyebrow"><?php echo esc_html( $hardware_eyebrow ); ?></span>
					<?php endif; ?>
					<?php if ( $hardware_heading ) : ?>
						<h2 class="ptabs-panel-heading"><?php echo esc_html( $hardware_heading ); ?></h2>
					<?php endif; ?>
					<?php if ( $hardware_blurb ) : ?>
						<p class="ptabs-panel-blurb"><?php echo esc_html( $hardware_blurb ); ?></p>
					<?php endif; ?>
				</header>
				<?php endif; ?>
				<?php echo cropx_ptabs_render_grid( $hardware_items, $svg_arrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</div>

</div>
