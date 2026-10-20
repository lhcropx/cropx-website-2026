<?php
/**
 * Text + Scrolling Photo Gallery — front-end render.
 *
 * Text content (icon, eyebrow, heading, InnerBlocks body, CTA) on one side;
 * a continuously scrolling photo marquee on the other.
 *
 * Two marquee layouts are rendered and CSS shows exactly one of them:
 *   • Desktop / wide tablet (≥ 900px): two vertical columns, scrolling in
 *     opposite directions, inside a fixed-height window.
 *   • Smaller tablet / mobile (< 900px): ONE horizontal row below the text,
 *     exactly as tall as a photo (all tiles share one height).
 * The hidden layout is display:none, so its lazy-loaded <img>s never fetch;
 * duplicate URLs are served from the browser cache anyway.
 *
 * Seamless loop: every track holds its photo set TWICE and animates by -50%.
 * The second copy is aria-hidden so screen readers hear each photo once.
 *
 * Attributes:
 *   galleryPosition  'right' | 'left'
 *   images           [{ id, url, alt, name?, link? }] — name + link make a tile a hover-reveal product link
 *   tileShape        'portrait' | 'square' | 'landscape' | 'masonry' (mixed, follows each photo's own shape)
 *   speed            very-slow … very-fast (7 steps) — vertical columns
 *   speedMobile      'same' | (same 7 steps) — horizontal row
 *   scrollDirection  'up' | 'down'   (desktop column 1 direction)
 *   galleryHeight    int px (desktop window height)
 *   pauseOnHover / showPauseButton   bool
 *   allowDrag        bool — visitors can drag / swipe each column (or the row) to scroll it (view.js)
 *   …plus the usual text-side attributes (see two-column-video).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$gallery_position = $attributes['galleryPosition'] ?? 'right';
$segment_accent   = $attributes['segmentAccent']   ?? 'general';
$icon             = $attributes['icon']            ?? 'field-sun';
$show_icon        = (bool) ( $attributes['showIcon']    ?? false );
$show_eyebrow     = (bool) ( $attributes['showEyebrow'] ?? true );
$show_cta         = (bool) ( $attributes['showCta']     ?? true );
$eyebrow          = $attributes['eyebrow']      ?? '';
$heading          = $attributes['heading']      ?? '';
$cta_label        = $attributes['ctaLabel']     ?? '';
$cta_url          = $attributes['ctaUrl']       ?? '#';
$cta_style        = $attributes['ctaStyle']     ?? 'button';
$eyebrow_color    = $attributes['eyebrowColor'] ?? 'cropx-blue';
$bg_color         = $attributes['bgColor']      ?? 'taupe';
$images           = is_array( $attributes['images'] ?? null ) ? $attributes['images'] : array();
$tile_shape       = $attributes['tileShape']       ?? 'portrait';
$speed            = $attributes['speed']           ?? 'medium';
$speed_mobile     = $attributes['speedMobile']     ?? 'same';
$direction        = $attributes['scrollDirection'] ?? 'up';
$gallery_height   = (int) ( $attributes['galleryHeight'] ?? 560 );
$pause_on_hover   = (bool) ( $attributes['pauseOnHover']    ?? true );
$allow_drag       = (bool) ( $attributes['allowDrag']       ?? true );
$show_pause_btn   = (bool) ( $attributes['showPauseButton'] ?? true );

// Validate enums / clamp.
if ( ! in_array( $gallery_position, array( 'right', 'left' ), true ) ) { $gallery_position = 'right'; }
if ( ! in_array( $segment_accent, array( 'general', 'enterprise', 'service-provider', 'on-farm' ), true ) ) { $segment_accent = 'general'; }
if ( ! in_array( $bg_color, array( 'taupe', 'deep-blue' ), true ) ) { $bg_color = 'taupe'; }
if ( ! in_array( $tile_shape, array( 'portrait', 'square', 'landscape', 'masonry' ), true ) ) { $tile_shape = 'portrait'; }
$speed_keys = array( 'very-slow', 'slow', 'medium-slow', 'medium', 'medium-fast', 'fast', 'very-fast' );
if ( ! in_array( $speed, $speed_keys, true ) ) { $speed = 'medium'; }
if ( 'same' !== $speed_mobile && ! in_array( $speed_mobile, $speed_keys, true ) ) { $speed_mobile = 'same'; }
if ( 'same' === $speed_mobile ) { $speed_mobile = $speed; }
if ( ! in_array( $direction, array( 'up', 'down' ), true ) ) { $direction = 'up'; }
if ( ! in_array( $eyebrow_color, array( 'cropx-blue', 'deep-blue', 'white' ), true ) ) { $eyebrow_color = 'cropx-blue'; }
$gallery_height = max( 360, min( 800, $gallery_height ?: 560 ) );

$allowed_icons = cropx_allowed_icon_slugs();
$icon          = cropx_resolve_icon_slug( $icon );
if ( ! in_array( $icon, $allowed_icons, true ) ) { $icon = 'field-sun'; }

$ratios  = array( 'portrait' => '4 / 5', 'square' => '1 / 1', 'landscape' => '4 / 3', 'masonry' => '1 / 1' );
$masonry = ( 'masonry' === $tile_shape );

// Masonry: snap each photo's own shape to the nearest of three tile shapes
// (landscape / square / portrait) so tiles stay close to the original framing
// while the columns still get a varied rhythm.
$snap_ratio = function ( $w, $h ) {
	if ( $w <= 0 || $h <= 0 ) { return '1 / 1'; }
	$r = $w / $h;
	if ( $r >= 1.15 ) { return '3 / 2'; }
	if ( $r <= 0.88 ) { return '3 / 4'; }
	return '1 / 1';
};

// Seconds each tile takes to travel past — a track's loop duration is this
// times the number of tiles in one copy of its set, so speed feels the same
// whether the gallery has 6 photos or 16.
$sec_per_tile = array(
	'very-slow' => 12, 'slow' => 9, 'medium-slow' => 7.5, 'medium' => 6,
	'medium-fast' => 5, 'fast' => 4, 'very-fast' => 3,
);
$spt        = $sec_per_tile[ $speed ];        // vertical columns (desktop / wide tablet)
$spt_mobile = $sec_per_tile[ $speed_mobile ]; // horizontal row (stacked layout)

// ── Clean the image list (cap 16, drop empties) ─────────────────────────
$clean = array();
foreach ( array_slice( $images, 0, 16 ) as $img ) {
	$id  = (int) ( $img['id'] ?? 0 );
	$url = (string) ( $img['url'] ?? '' );
	if ( $id ) {
		$resolved = wp_get_attachment_url( $id ); // fresh URL — avoids stale-domain snapshots
		if ( $resolved ) { $url = $resolved; }
	}
	if ( ! $url ) { continue; }
	$ratio = '';
	if ( $masonry && $id ) {
		$meta  = wp_get_attachment_metadata( $id );
		$ratio = $snap_ratio( (int) ( $meta['width'] ?? 0 ), (int) ( $meta['height'] ?? 0 ) );
	}
	$clean[] = array(
		'id'    => $id,
		'url'   => $url,
		'alt'   => (string) ( $img['alt'] ?? '' ),
		'ratio' => $ratio,
		'name'  => sanitize_text_field( (string) ( $img['name'] ?? '' ) ),
		'link'  => trim( (string) ( $img['link'] ?? '' ) ),
	);
}

/**
 * Print one <img> tile.
 */
$tile = function ( $img, $hidden ) {
	$alt      = $hidden ? '' : $img['alt'];
	$name     = (string) ( $img['name'] ?? '' );
	$link     = (string) ( $img['link'] ?? '' );
	$has_link = '' !== $link;
	$style    = ! empty( $img['ratio'] ) ? sprintf( ' style="--mg-ratio:%s"', esc_attr( $img['ratio'] ) ) : '';

	if ( $has_link ) {
		// Duplicate copies are decorative: hide from AT and keep out of tab order.
		printf(
			'<a class="mg-tile mg-tile--link" href="%1$s"%2$s%3$s>',
			esc_url( cropx_url( $link ) ),
			$style, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$hidden ? ' tabindex="-1" aria-hidden="true"' : ''
		);
	} else {
		printf( '<div class="mg-tile"%s>', $style ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	if ( $img['id'] && wp_attachment_is_image( $img['id'] ) ) {
		echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$img['id'],
			'medium_large',
			false,
			array(
				'class'    => 'mg-img',
				'alt'      => $alt,
				'loading'  => 'lazy',
				'decoding' => 'async',
				'sizes'    => '(min-width: 900px) 22vw, 60vw',
			)
		);
	} else {
		printf(
			'<img class="mg-img" src="%1$s" alt="%2$s" loading="lazy" decoding="async">',
			esc_url( $img['url'] ),
			esc_attr( $alt )
		);
	}

	if ( $has_link ) {
		echo '<span class="mg-tile-label">';
		if ( '' !== $name ) {
			echo '<span class="mg-tile-name">' . esc_html( $name ) . '</span>';
		}
		echo '<svg class="mg-tile-arrow" width="20" height="20" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		echo '</span></a>';
	} else {
		echo '</div>';
	}
};

/**
 * Print one track = two copies of a (possibly repeated) photo set.
 * $min = minimum tiles per copy so a copy always out-spans its window.
 */
$track = function ( $set, $min, $extra_class, $spt ) use ( $tile ) {
	$n = count( $set );
	if ( 0 === $n ) { return; }
	$reps  = (int) ceil( $min / $n );
	$count = $n * $reps;
	$dur   = (int) round( $count * $spt );
	printf( '<div class="mg-track %1$s" style="--mg-dur:%2$ds">', esc_attr( $extra_class ), (int) $dur );
	for ( $copy = 0; $copy < 2; $copy++ ) {
		$hidden = ( 1 === $copy );
		printf( '<div class="mg-set"%s>', $hidden ? ' aria-hidden="true"' : '' );
		for ( $r = 0; $r < $reps; $r++ ) {
			foreach ( $set as $img ) {
				// Repeats inside copy 1 are also decorative.
				$tile( $img, $hidden || $r > 0 );
			}
		}
		echo '</div>';
	}
	echo '</div>';
};

// Desktop columns: round-robin split.
$col_a = array();
$col_b = array();
foreach ( $clean as $i => $img ) {
	if ( 0 === $i % 2 ) { $col_a[] = $img; } else { $col_b[] = $img; }
}
if ( empty( $col_b ) ) { $col_b = $col_a; } // single photo → mirror it into column 2

// ── Section classes / style ─────────────────────────────────────────────
$section_class  = 'mg-section is-animated';
$section_class .= ' mg-section--bg-' . $bg_color;
if ( 'left' === $gallery_position )  { $section_class .= ' mg-section--gallery-left'; }
if ( 'down' === $direction )         { $section_class .= ' mg-section--dir-down'; }
if ( 'general' !== $segment_accent ) { $section_class .= ' mg-segment-' . $segment_accent; }
if ( $pause_on_hover )               { $section_class .= ' mg-section--pause-hover'; }

$style  = '--mg-height:' . $gallery_height . 'px;';
$style .= '--mg-ratio:' . $ratios[ $tile_shape ] . ';';
// Deep-blue topo overlay — real asset URL via custom property, never a
// relative url() in CSS (webpack would base64-inline the ~90KB SVG).
if ( 'deep-blue' === $bg_color ) {
	$style .= '--mg-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');';
}

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class'           => $section_class,
	'style'           => $style,
	'data-section-bg' => $bg_color,
	'data-draggable'  => $allow_drag ? '1' : '0',
) );

$allowed_inline = array( 'em' => array(), 'strong' => array(), 'br' => array() );
$has_inner      = ! empty( trim( strip_tags( $content ) ) );
$has_images     = ! empty( $clean );

$pause_svg = '<svg class="mg-pause-ico mg-pause-ico--pause" width="14" height="14" viewBox="0 0 14 14" fill="currentColor" aria-hidden="true"><rect x="2" y="1" width="3.5" height="12" rx="1"/><rect x="8.5" y="1" width="3.5" height="12" rx="1"/></svg>'
           . '<svg class="mg-pause-ico mg-pause-ico--play" width="14" height="14" viewBox="0 0 14 14" fill="currentColor" aria-hidden="true"><path d="M3 1.5v11l9-5.5-9-5.5z"/></svg>';
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="mg-inner">
		<div class="mg-grid">

			<?php /* ── Text column ── */ ?>
			<div class="mg-content">
				<?php if ( $show_icon ) : ?>
				<div class="mg-icon-wrap reveal-up" style="--reveal-delay:0.05s">
					<div class="mg-icon" aria-hidden="true">
						<img src="<?php echo esc_url( CROPX_THEME_URI . 'assets/icons/' . $icon . '.svg' ); ?>" alt="" width="28" height="28">
					</div>
				</div>
				<?php endif; ?>

				<?php if ( $eyebrow && $show_eyebrow ) : ?>
					<span class="section-eyebrow reveal-up" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>); --reveal-delay:0.1s"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></span>
				<?php endif; ?>

				<?php if ( $heading ) : ?>
					<h2 class="section-heading reveal-up" style="--reveal-delay:0.2s"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
				<?php endif; ?>

				<?php if ( $has_inner ) : ?>
					<div class="section-body reveal-up" style="--reveal-delay:0.3s"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php endif; ?>

				<?php if ( $cta_label && $show_cta ) : ?>
					<?php if ( 'link' === $cta_style ) : ?>
						<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="mg-link reveal-up" style="--reveal-delay:0.4s">
							<?php echo esc_html( $cta_label ); ?>
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="mg-cta reveal-up" style="--reveal-delay:0.4s">
							<?php echo esc_html( $cta_label ); ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<?php /* ── Gallery column ── */ ?>
			<?php if ( $has_images ) : ?>
			<div class="mg-gallery reveal-up" style="--reveal-delay:0.15s">

				<?php /* Desktop / wide tablet: two vertical columns */ ?>
				<div class="mg-viewport mg-viewport--cols">
					<div class="mg-cols">
						<div class="mg-col mg-col--a"><?php $track( $col_a, $masonry ? 5 : 4, 'mg-track--y', $spt ); ?></div>
						<div class="mg-col mg-col--b"><?php $track( $col_b, $masonry ? 5 : 4, 'mg-track--y', $spt ); ?></div>
					</div>
				</div>

				<?php /* Smaller tablet / mobile: one horizontal row */ ?>
				<div class="mg-viewport mg-viewport--row">
					<div class="mg-rowline"><?php $track( $clean, 6, 'mg-track--x', $spt_mobile ); ?></div>
				</div>

				<?php if ( $show_pause_btn ) : ?>
				<button type="button" class="mg-pause" aria-pressed="false" aria-label="<?php echo esc_attr__( 'Pause scrolling photos', 'cropx' ); ?>" data-label-pause="<?php echo esc_attr__( 'Pause scrolling photos', 'cropx' ); ?>" data-label-play="<?php echo esc_attr__( 'Play scrolling photos', 'cropx' ); ?>">
					<?php echo $pause_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
				<?php endif; ?>

			</div>
			<?php endif; ?>

		</div>
	</div>
</section>
