<?php
/**
 * Interactive Device Diagram block — front-end render.
 *
 * A phone in the middle of a stage; 4–8 product cards radiate out to its left
 * and right (never above/below). One DOM serves two layouts, switched purely by
 * CSS media query (see style.css):
 *
 *   Desktop (fine pointer, > 900px)
 *     Stage is a CSS grid: [left cards] [phone] [right cards]. .dd-carousel and
 *     .dd-track are display:contents so the cards are direct grid items.
 *     view.js draws SVG connector lines and sets the single "active product"
 *     from hover/focus.
 *
 *   Touch / narrow (≤ 900px, or hover:none / pointer:coarse)
 *     Stage is a flex column: phone → swipeable card carousel (CSS scroll-snap)
 *     with prev/next arrows that straddle the focused card's edges. view.js
 *     sets the active product from whichever card is centered.
 *
 * Every card carries the product's image, name, tagline (the "software label")
 * and description, in the HTML in reading order — nothing is hidden behind
 * hover or scroll, so search engines and screen readers get it all.
 *
 * Link model: the whole card is the real link to the product page.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$eyebrow      = $attributes['eyebrow']      ?? '';
$show_eyebrow = (bool) ( $attributes['showEyebrow'] ?? true );
$heading      = $attributes['heading']      ?? '';
$body         = $attributes['body']         ?? '';
$items        = is_array( $attributes['items'] ?? null ) ? $attributes['items'] : [];

$bg_color = $attributes['bgColor'] ?? 'taupe';
if ( ! in_array( $bg_color, [ 'taupe', 'deep-blue' ], true ) ) {
	$bg_color = 'taupe';
}

// Cap at 8 products (design limit) — the editor enforces 4–8, this is a
// belt-and-suspenders guard against stored data from outside the editor.
$items = array_slice( array_values( $items ), 0, 8 );
$count = count( $items );
if ( $count === 0 ) {
	return;
}

$uid = wp_unique_id( 'dd-' );

// Left gets the extra card when the count is odd. We lay the stage out on a
// 2×-resolution row grid (each card spans 2 rows) so the shorter column can sit
// vertically centred against the longer one.
$left_count   = (int) ceil( $count / 2 );
$right_count  = $count - $left_count;
$grid_rows    = $left_count * 2;
$right_offset = $left_count - $right_count; // 0 or 1 half-row

$arrow_svg = '<svg viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

/** Resolve an image fresh from its attachment ID (the stored URL is only a snapshot). */
if ( ! function_exists( 'cropx_dd_image' ) ) :
function cropx_dd_image( int $id, string $fallback_url, string $size ): array {
	$url = $fallback_url;
	$w   = 0;
	$h   = 0;
	if ( $id ) {
		$src = wp_get_attachment_image_src( $id, $size );
		if ( $src ) {
			$url = $src[0];
			$w   = (int) $src[1];
			$h   = (int) $src[2];
		}
	}
	return [ 'url' => esc_url( $url ), 'w' => $w, 'h' => $h ];
}
endif;

$idle_screen = cropx_dd_image( (int) ( $attributes['idleScreenId'] ?? 0 ), $attributes['idleScreenUrl'] ?? '', 'large' );

$wrapper_args = [
	'class'           => 'dd-block reveal-group dd-block--bg-' . $bg_color,
	'data-section-bg' => $bg_color,
];
// Real, cacheable pattern URL via an inline CSS var (not a base64 inline) —
// see CLAUDE.md gotcha #7 / PageSpeed drift-pattern fix (Aug 2026).
if ( $bg_color === 'deep-blue' ) {
	$wrapper_args['style'] = '--dd-pattern-url:url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ')';
}
$wrapper_attrs = get_block_wrapper_attributes( $wrapper_args );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="dd-inner">

		<?php if ( ( $show_eyebrow && $eyebrow ) || $heading || $body ) : ?>
		<header class="dd-header">
			<?php if ( $show_eyebrow && $eyebrow ) : ?>
				<span class="section-eyebrow reveal-up" style="--reveal-delay:0.05s"><?php echo esc_html( $eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( $heading ) : ?>
				<h2 class="dd-heading reveal-up" style="--reveal-delay:0.15s"><?php echo wp_kses( $heading, [ 'em' => [], 'br' => [] ] ); ?></h2>
			<?php endif; ?>
			<?php if ( $body ) : ?>
				<p class="dd-body reveal-up" style="--reveal-delay:0.25s"><?php echo esc_html( $body ); ?></p>
			<?php endif; ?>
		</header>
		<?php endif; ?>

		<div class="dd-stage" style="--dd-rows:<?php echo (int) $grid_rows; ?>">

			<svg class="dd-lines" aria-hidden="true" focusable="false"></svg>

			<?php /* Phone — decorative; each card carries the same text for AT. */ ?>
			<div class="dd-phone reveal-up" style="--reveal-delay:0.2s" aria-hidden="true">
				<div class="dd-phone-body">
					<span class="dd-phone-notch"></span>
					<div class="dd-screen">
						<div class="dd-screen-layer is-active" data-i="-1">
							<?php if ( $idle_screen['url'] ) : ?>
								<img class="dd-screen-img" src="<?php echo $idle_screen['url']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="" loading="lazy" decoding="async"<?php echo $idle_screen['w'] && $idle_screen['h'] ? ' width="' . (int) $idle_screen['w'] . '" height="' . (int) $idle_screen['h'] . '"' : ''; ?>>
						<?php endif; ?>
						</div>
						<?php
						// Per-product screens are optional. A product with no screen image gets no
						// layer, so view.js falls back to the default screen above — one image on
						// the phone through every interaction. No text is ever shown on the screen.
						foreach ( $items as $i => $item ) :
							$screen = cropx_dd_image( (int) ( $item['screenId'] ?? 0 ), $item['screenUrl'] ?? '', 'large' );
							if ( ! $screen['url'] ) { continue; }
							?>
							<div class="dd-screen-layer" data-i="<?php echo (int) $i; ?>">
								<img class="dd-screen-img" src="<?php echo $screen['url']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="" loading="lazy" decoding="async"<?php echo $screen['w'] && $screen['h'] ? ' width="' . (int) $screen['w'] . '" height="' . (int) $screen['h'] . '"' : ''; ?>>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<?php /* Cards — display:contents on desktop (grid items of the stage); a scroll-snap row on touch. */ ?>
			<div class="dd-carousel">
				<div class="dd-track" role="group" aria-label="<?php esc_attr_e( 'Products', 'cropx' ); ?>">
					<?php foreach ( $items as $i => $item ) :
						$is_left = $i < $left_count;
						$side    = $is_left ? 'left' : 'right';
						$row     = $is_left
							? ( $i * 2 ) + 1
							: ( ( $i - $left_count ) * 2 ) + 1 + $right_offset;

						$name = (string) ( $item['name'] ?? '' );
						$tag  = (string) ( $item['softwareLabel'] ?? '' );
						$desc = (string) ( $item['description'] ?? '' );
						$url  = esc_url( cropx_url( $item['url'] ?? '#' ) );

						// Background photo (fills the card's image half, with focal point + zoom) —
						// same model as the Product Grid block.
						$photo = cropx_dd_image( (int) ( $item['photoId'] ?? 0 ), $item['photoUrl'] ?? '', 'medium_large' );
						$alt   = $item['photoAlt'] ?? '';
						if ( ! $alt && ! empty( $item['photoId'] ) ) {
							$alt = (string) get_post_meta( (int) $item['photoId'], '_wp_attachment_image_alt', true );
						}
						$focal_x = round( floatval( $item['photoFocalX'] ?? 0.5 ) * 100, 1 );
						$focal_y = round( floatval( $item['photoFocalY'] ?? 0.5 ) * 100, 1 );
						$zoom    = max( 100, min( 500, floatval( $item['photoZoom'] ?? 100 ) ) );

						// Optional PNG overlay: none | contained (inside the photo area) | card-bleed
						// (overhangs the card). Offsets are written for the image-on-RIGHT card and are
						// auto-mirrored by CSS on the desktop image-on-LEFT cards.
						$overlay_type = $item['overlayType'] ?? 'none';
						if ( ! in_array( $overlay_type, [ 'none', 'card-bleed', 'contained' ], true ) ) {
							$overlay_type = 'none';
						}
						$overlay_id  = (int) ( $item['overlayId'] ?? 0 );
						$overlay     = cropx_dd_image( $overlay_id, $item['overlayUrl'] ?? '', 'full' );
						$has_overlay = ( $overlay_type !== 'none' && $overlay['url'] );
						$ov_padding = max( 0, min( 20, intval( $item['overlayPadding'] ?? 0 ) ) );
						$ov_h       = max( 50, min( 160, floatval( $item['overlayH'] ?? 100 ) ) );
						$ov_x       = max( -80, min( 80, floatval( $item['overlayX'] ?? 0 ) ) );
						$ov_y       = max( -60, min( 60, floatval( $item['overlayY'] ?? 0 ) ) );
						$ov_center  = ! empty( $item['overlayCentered'] );
						$ov_bottom  = ( $item['overlayAnchor'] ?? 'center' ) === 'bottom';
						$ov_bleed   = max( 0, min( 20, intval( $item['overlayTopBleed'] ?? 0 ) ) );

						$card_class = 'dd-card';
						$card_vars  = '';
						if ( $has_overlay && $overlay_type === 'card-bleed' ) {
							if ( $ov_center ) { $card_class .= ' dd-card--overlay-centered'; }
							if ( $ov_bottom ) { $card_class .= ' dd-card--overlay-bottom'; }
							$card_vars = '--dd-overlay-h:' . $ov_h . '%;--dd-overlay-x:' . $ov_x . 'px;--dd-overlay-y:' . $ov_y . 'px;--dd-overlay-top-bleed:-' . $ov_bleed . 'px';
						}

						$delay      = 0.3 + ( min( $i, 8 ) * 0.06 );
						$item_style = sprintf( '--dd-col:%d;--dd-row:%d;--reveal-delay:%ss', $is_left ? 1 : 3, $row, $delay );

						// Name labels the link; tagline + description describe it — keeps the
						// announced link name short instead of reading the whole card.
						$id_name = $uid . '-n' . $i;
						$id_tag  = $uid . '-t' . $i;
						$id_desc = $uid . '-d' . $i;
						$describedby = trim( ( $tag ? $id_tag . ' ' : '' ) . ( $desc ? $id_desc : '' ) );
						?>
						<div class="dd-item reveal-item dd-item--<?php echo esc_attr( $side ); ?>" data-i="<?php echo (int) $i; ?>" data-side="<?php echo esc_attr( $side ); ?>" style="<?php echo esc_attr( $item_style ); ?>">
							<a class="<?php echo esc_attr( $card_class ); ?>"<?php echo $card_vars ? ' style="' . esc_attr( $card_vars ) . '"' : ''; ?> href="<?php echo $url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" aria-labelledby="<?php echo esc_attr( $id_name ); ?>"<?php echo $describedby ? ' aria-describedby="' . esc_attr( $describedby ) . '"' : ''; ?>>
								<span class="dd-card-text">
									<strong class="dd-card-name" id="<?php echo esc_attr( $id_name ); ?>"><?php echo esc_html( $name ); ?>&nbsp;<span class="dd-card-arrow" aria-hidden="true"><?php echo $arrow_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></strong>
									<?php if ( $tag ) : ?>
										<span class="dd-card-tag" id="<?php echo esc_attr( $id_tag ); ?>"><?php echo esc_html( $tag ); ?></span>
									<?php endif; ?>
									<?php if ( $desc ) : ?>
										<span class="dd-card-desc" id="<?php echo esc_attr( $id_desc ); ?>"><?php echo esc_html( $desc ); ?></span>
									<?php endif; ?>
								</span>
								<span class="dd-thumb-outer">
									<span class="dd-thumb" style="<?php echo esc_attr( "--dd-focal-x:{$focal_x}%;--dd-focal-y:{$focal_y}%;--dd-zoom:{$zoom}" ); ?>">
										<?php if ( $photo['url'] ) : ?>
											<img class="dd-thumb-img" src="<?php echo $photo['url']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy" decoding="async"<?php echo $photo['w'] && $photo['h'] ? ' width="' . (int) $photo['w'] . '" height="' . (int) $photo['h'] . '"' : ''; ?>>
										<?php endif; ?>
										<?php if ( $has_overlay && $overlay_type === 'contained' ) : ?>
											<img class="dd-overlay--contained" src="<?php echo $overlay['url']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="" loading="lazy" decoding="async"<?php echo $ov_padding ? ' style="padding-block:' . $ov_padding . '%"' : ''; ?><?php echo $overlay['w'] && $overlay['h'] ? ' width="' . (int) $overlay['w'] . '" height="' . (int) $overlay['h'] . '"' : ''; ?>>
										<?php endif; ?>
									</span>
								</span>
								<?php if ( $has_overlay && $overlay_type === 'card-bleed' ) : ?>
									<span class="dd-overlay-clip" aria-hidden="true"><img class="dd-overlay--card-bleed" src="<?php echo $overlay['url']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" alt="" loading="lazy" decoding="async"<?php echo $overlay['w'] && $overlay['h'] ? ' width="' . (int) $overlay['w'] . '" height="' . (int) $overlay['h'] . '"' : ''; ?>></span>
								<?php endif; ?>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

		</div>
	</div>
</section>
