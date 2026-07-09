<?php
/**
 * Product Grid block — front-end render.
 *
 * Dynamic block. Markup matches blocks/product-grid-card.html.
 *
 * Each item supports:
 *   - A background photo (photoId preferred, photoUrl as fallback)
 *   - An optional illustration overlay:
 *       "card-bleed"  — PNG bleeds from photo column into text area
 *       "contained"   — PNG contained within the photo column
 *       "none"        — photo only
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$eyebrow       = $attributes['eyebrow']      ?? '';
$eyebrow_color = esc_attr( $attributes['eyebrowColor'] ?? 'cropx-blue' );
$heading       = $attributes['heading']      ?? '';
$blurb         = $attributes['blurb']        ?? '';
$show_header   = (bool) ( $attributes['showHeader'] ?? true );
$items         = $attributes['items']        ?? [];

$bg_color = $attributes['bgColor'] ?? 'taupe';
if ( ! in_array( $bg_color, array( 'taupe', 'white' ), true ) ) {
	$bg_color = 'taupe';
}

$svg_arrow = '<svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

$wrapper_attrs = get_block_wrapper_attributes( [ 'class' => 'pg-block pg-block--bg-' . $bg_color, 'data-section-bg' => $bg_color ] );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="pg-inner">

		<?php if ( $show_header && ( $eyebrow || $heading || $blurb ) ) : ?>
		<header class="pg-header">
			<?php if ( $eyebrow ) : ?>
				<span class="section-eyebrow" style="color: var(--<?php echo $eyebrow_color; ?>)">
					<?php echo esc_html( $eyebrow ); ?>
				</span>
			<?php endif; ?>
			<?php if ( $heading ) : ?>
				<h2 class="section-heading"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>
			<?php if ( $blurb ) : ?>
				<p class="pg-blurb"><?php echo esc_html( $blurb ); ?></p>
			<?php endif; ?>
		</header>
		<?php endif; ?>

		<div class="pg-grid">
		<?php foreach ( $items as $item ) :
			$name            = $item['name']           ?? '';
			$desc            = $item['description']    ?? '';
			$url             = $item['url']             ?? '#';
			$photo_id        = (int) ( $item['photoId']       ?? 0 );
			$photo_url       = $item['photoUrl']        ?? '';
			$photo_alt       = $item['photoAlt']        ?? '';
			$overlay_id      = (int) ( $item['overlayId']     ?? 0 );
			$overlay_url     = $item['overlayUrl']      ?? '';
			$overlay_type    = $item['overlayType']     ?? 'none';
			$overlay_padding = (int) ( $item['overlayPadding'] ?? 0 );
			$overlay_h        = isset( $item['overlayH'] ) ? (float) $item['overlayH'] : 100;
			$overlay_x        = isset( $item['overlayX'] ) ? (float) $item['overlayX'] : 0;
			$overlay_centered = (bool) ( $item['overlayCentered'] ?? false );
			$overlay_anchor   = $item['overlayAnchor'] ?? 'center';
			$photo_focal_x    = isset( $item['photoFocalX'] ) ? round( (float) $item['photoFocalX'] * 100, 1 ) : 50;
			$photo_focal_y    = isset( $item['photoFocalY'] ) ? round( (float) $item['photoFocalY'] * 100, 1 ) : 50;
			$photo_zoom       = isset( $item['photoZoom'] ) ? (float) $item['photoZoom'] : 100;

			// Prefer the WP attachment ID so WordPress can serve the right size.
			if ( $photo_id ) {
				$src = wp_get_attachment_image_src( $photo_id, 'large' );
				if ( $src ) {
					$photo_url = $src[0];
					if ( ! $photo_alt ) {
						$photo_alt = get_post_meta( $photo_id, '_wp_attachment_image_alt', true ) ?: '';
					}
				}
			}

			if ( $overlay_id ) {
				$src = wp_get_attachment_image_src( $overlay_id, 'full' );
				if ( $src ) {
					$overlay_url = $src[0];
				}
			}

			// Sanitise overlay type.
			if ( ! in_array( $overlay_type, [ 'none', 'card-bleed', 'contained' ], true ) ) {
				$overlay_type = 'none';
			}
		?>
			<?php
			// Build item class and inline styles.
			$item_classes = 'pg-item';
			if ( $overlay_centered && 'card-bleed' === $overlay_type ) {
				$item_classes .= ' pg-item--overlay-centered';
			}
			if ( 'bottom' === $overlay_anchor && 'card-bleed' === $overlay_type ) {
				$item_classes .= ' pg-item--overlay-bottom';
			}

			$item_style_parts = [];
			if ( 'card-bleed' === $overlay_type && $overlay_url ) {
				$item_style_parts[] = '--pg-overlay-h: ' . $overlay_h . '%';
				if ( ! $overlay_centered ) {
					$item_style_parts[] = '--pg-overlay-x: ' . $overlay_x . 'px';
				}
			}
			$item_style = $item_style_parts ? 'style="' . esc_attr( implode( '; ', $item_style_parts ) ) . '"' : '';
		?>
		<a class="<?php echo esc_attr( $item_classes ); ?>" href="<?php echo esc_url( cropx_url( $url ) ); ?>" <?php echo $item_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

				<div class="pg-thumb-outer">
					<div class="pg-thumb" style="--pg-photo-focal-x: <?php echo esc_attr( $photo_focal_x ); ?>%; --pg-photo-focal-y: <?php echo esc_attr( $photo_focal_y ); ?>%; --pg-photo-zoom: <?php echo esc_attr( $photo_zoom ); ?>;">
						<?php if ( $photo_url ) : ?>
							<img
								class="pg-thumb-img"
								src="<?php echo esc_url( $photo_url ); ?>"
								alt="<?php echo esc_attr( $photo_alt ); ?>"
								loading="lazy"
							>
						<?php endif; ?>

						<?php if ( 'contained' === $overlay_type && $overlay_url ) :
							$padding_style = $overlay_padding ? 'padding-block: ' . $overlay_padding . '%' : '';
						?>
							<img
								class="pg-overlay--contained"
								src="<?php echo esc_url( $overlay_url ); ?>"
								alt=""
								<?php if ( $padding_style ) : ?>style="<?php echo esc_attr( $padding_style ); ?>"<?php endif; ?>
							>
						<?php endif; ?>
					</div>
				</div>

				<div class="pg-text">
					<strong class="pg-name">
						<?php echo esc_html( $name ); ?>&nbsp;<span class="pg-arrow" aria-hidden="true"><?php echo $svg_arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</strong>
					<p class="pg-desc"><?php echo esc_html( $desc ); ?></p>
				</div>

				<?php if ( 'card-bleed' === $overlay_type && $overlay_url ) : ?>
					<img
						class="pg-overlay--card-bleed"
						src="<?php echo esc_url( $overlay_url ); ?>"
						alt=""
					>
				<?php endif; ?>

			</a>
		<?php endforeach; ?>
		</div>

	</div>
</section>
