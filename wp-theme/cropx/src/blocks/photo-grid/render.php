<?php
/**
 * Photo Grid — front-end render.
 *
 * A responsive grid of photos — no carousel, every photo is visible at
 * once. Column count (3/4/5) and caption treatment are both block-wide
 * settings that apply uniformly to every photo, matching how Corner Radius
 * already works on the Photo Carousel block.
 *
 * Structure:
 *   .pgd-section
 *     .pgd-inner
 *       .pgd-header            (optional eyebrow + heading + intro body)
 *     .pgd-grid                (CSS grid, columns set via --pgd-columns)
 *       .pgd-item ×N
 *         .pgd-photo-wrap
 *           img
 *           .pgd-caption--overlay   (only when captionStyle === 'overlay')
 *         .pgd-caption--standard / .pgd-caption--large   (below the photo,
 *           only when captionStyle !== 'overlay')
 *
 * Scroll-reveal (Sep 2026): section wrapper is the reveal-group observed
 * root (see view.js), eyebrow/heading/intro body get reveal-up, and each
 * .pgd-item gets reveal-item with a per-index stagger delay. See
 * src/shared/scrollReveal.js / scroll-reveal.css for the shared mechanism.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$photos        = $attributes['photos']        ?? [];
$columns       = (int) ( $attributes['columns'] ?? 4 );
if ( ! in_array( $columns, [ 3, 4, 5 ], true ) ) {
	$columns = 4;
}
// Aspect ratio — block-wide, applies to every photo (matches Corner Radius,
// Columns, and Caption Style all being uniform settings for this block).
$aspect_ratio = $attributes['aspectRatio'] ?? '4:3';
if ( ! in_array( $aspect_ratio, [ '1:1', '4:3', '3:2', '16:9', '3:4', '2:3' ], true ) ) {
	$aspect_ratio = '4:3';
}
$caption_style = $attributes['captionStyle']  ?? 'standard';
if ( ! in_array( $caption_style, [ 'standard', 'large', 'overlay' ], true ) ) {
	$caption_style = 'standard';
}
$caption_align = $attributes['captionAlign']  ?? 'left';
if ( ! in_array( $caption_align, [ 'left', 'center', 'right' ], true ) ) {
	$caption_align = 'left';
}
$eyebrow       = $attributes['eyebrow']       ?? '';
$heading       = $attributes['heading']       ?? '';
$show_eyebrow  = (bool) ( $attributes['showEyebrow']  ?? true );
$show_heading  = (bool) ( $attributes['showHeading']  ?? true );
$intro_body    = $attributes['introBody']     ?? '';
$eyebrow_color = $attributes['eyebrowColor']  ?? 'cropx-blue';
$bg_color      = $attributes['bgColor']       ?? 'taupe';

if ( ! in_array( $bg_color, [ 'taupe', 'deep-blue' ], true ) ) {
	$bg_color = 'taupe';
}

// Corner Radius override — empty values mean "not customized," in which
// case .pgd-photo-wrap keeps the sitewide default (--radius-photo). The
// moment any corner is set, all four are written as CSS custom properties
// on the section wrapper; style.css falls back to the default per corner.
$radius_tl = $attributes['photoRadiusTopLeft']     ?? '';
$radius_tr = $attributes['photoRadiusTopRight']    ?? '';
$radius_br = $attributes['photoRadiusBottomRight'] ?? '';
$radius_bl = $attributes['photoRadiusBottomLeft']  ?? '';
$radius_style = '';
if ( '' !== $radius_tl || '' !== $radius_tr || '' !== $radius_br || '' !== $radius_bl ) {
	$radius_style = sprintf(
		'--pgd-radius-tl:%1$s;--pgd-radius-tr:%2$s;--pgd-radius-br:%3$s;--pgd-radius-bl:%4$s;',
		esc_attr( $radius_tl ?: '0' ),
		esc_attr( $radius_tr ?: '0' ),
		esc_attr( $radius_br ?: '0' ),
		esc_attr( $radius_bl ?: '0' )
	);
}

// ── Deep-blue topographic drift pattern ────────────────────────────────────
// Same technique as Photo Carousel / two-column-video — the asset's real
// URL is injected via a CSS custom property rather than a relative url() in
// style.css, which webpack would otherwise base64-inline (~90KB SVG).
$style_parts = [];
$style_parts[] = '--pgd-columns:' . $columns . ';';
$style_parts[] = '--pgd-aspect:' . esc_attr( str_replace( ':', ' / ', $aspect_ratio ) ) . ';';
if ( '' !== $radius_style ) {
	$style_parts[] = $radius_style;
}
if ( 'deep-blue' === $bg_color ) {
	$style_parts[] = '--pgd-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');';
}

$section_class = 'pgd-section reveal-group pgd-section--bg-' . $bg_color;

// Eyebrow inline colour is suppressed on deep-blue sections so CSS can apply
// the white override without fighting inline specificity.
$eyebrow_color_style = ( 'deep-blue' !== $bg_color )
	? ' style="color: var(--' . esc_attr( $eyebrow_color ) . ')"'
	: '';

$photo_list = array_values( array_filter( $photos, fn( $p ) => ! empty( $p['url'] ) ) );

if ( empty( $photo_list ) ) {
	return; // Nothing to render.
}

$wrapper_attrs = get_block_wrapper_attributes( [
	'class' => $section_class,
	'style' => implode( '', $style_parts ),
] );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php if ( ( $eyebrow && $show_eyebrow ) || $show_heading || $intro_body ) : ?>
	<div class="pgd-inner">
		<div class="pgd-header">
			<?php if ( $eyebrow && $show_eyebrow ) : ?>
				<span class="section-eyebrow reveal-up" style="--reveal-delay:0.05s"<?php echo $eyebrow_color_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( $heading && $show_heading ) : ?>
				<h2 class="section-heading pgd-heading reveal-up" style="--reveal-delay:0.15s"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>
			<?php if ( $intro_body ) : ?>
				<div class="section-body reveal-up" style="--reveal-delay:0.25s"><?php echo wp_kses_post( $intro_body ); ?></div>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

	<?php
	// Staggered per-item reveal delay (scroll-reveal.css), same
	// rationale/formula as every other scroll-reveal block — starts after
	// whichever header elements are actually shown (eyebrow 0.05s, heading
	// 0.15s, intro body 0.25s), so items don't wait behind header lines
	// that aren't rendered. Capped at 8.
	$pgd_item_base = ( $intro_body )
		? 0.35
		: ( ( $heading && $show_heading ) ? 0.25 : ( ( $eyebrow && $show_eyebrow ) ? 0.15 : 0.05 ) );
	?>
	<div class="pgd-inner">
		<div class="pgd-grid">
			<?php foreach ( $photo_list as $index => $photo ) :
				$photo_id  = (int) ( $photo['id']  ?? 0 );
				$photo_url = $photo['url']     ?? '';
				$photo_alt = $photo['alt']     ?? '';
				$caption   = trim( $photo['caption'] ?? '' );
				$pgd_reveal_delay = $pgd_item_base + ( min( $index, 8 ) * 0.06 );
			?>
			<div class="pgd-item pgd-item--align-<?php echo esc_attr( $caption_align ); ?> reveal-item" style="--reveal-delay:<?php echo esc_attr( $pgd_reveal_delay ); ?>s">
				<div class="pgd-photo-wrap">
					<?php if ( $photo_id ) : ?>
						<?php echo wp_get_attachment_image( $photo_id, 'large', false, [
							'class'   => 'pgd-photo',
							'loading' => $index < 3 ? 'eager' : 'lazy',
							'alt'     => esc_attr( $photo_alt ),
						] ); ?>
					<?php else : ?>
						<img
							class="pgd-photo"
							src="<?php echo esc_url( $photo_url ); ?>"
							alt="<?php echo esc_attr( $photo_alt ); ?>"
							<?php echo cropx_img_dims_attr( $photo_id, $photo_url ); ?>
							loading="<?php echo $index < 3 ? 'eager' : 'lazy'; ?>"
						>
					<?php endif; ?>
					<?php if ( $caption && 'overlay' === $caption_style ) : ?>
						<p class="pgd-caption pgd-caption--overlay"><?php echo esc_html( $caption ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( $caption && 'overlay' !== $caption_style ) : ?>
					<p class="pgd-caption pgd-caption--<?php echo esc_attr( $caption_style ); ?>"><?php echo esc_html( $caption ); ?></p>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
		</div>
	</div>

</section>
