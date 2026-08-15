<?php
/**
 * Field Photos Gallery — front-end render.
 *
 * Renders a horizontally scrolling strip of product-in-field photos
 * with a full-screen lightbox on click.
 *
 * Structure:
 *   .fph-section
 *     .fph-inner
 *       .fph-header          (optional eyebrow + heading + intro body)
 *     .fph-carousel          (position:relative scroll wrapper)
 *       .fph-viewport
 *         .fph-track         (flex scroll-snap row)
 *           .fph-item ×N     (photo cards)
 *             .fph-photo-wrap
 *               img
 *             .fph-caption   (optional)
 *       .fph-controls        (prev / next arrows)
 *     .fph-lightbox          (fixed overlay, hidden by default)
 *       .fph-lightbox-img
 *       button.fph-lightbox-close
 *       button.fph-lightbox-prev
 *       button.fph-lightbox-next
 *       .fph-lightbox-caption
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$photos        = $attributes['photos']        ?? [];
$eyebrow       = $attributes['eyebrow']       ?? '';
$heading       = $attributes['heading']       ?? '';
$show_eyebrow  = (bool) ( $attributes['showEyebrow']  ?? true );
$show_heading  = (bool) ( $attributes['showHeading']  ?? true );
$intro_body    = $attributes['introBody']     ?? '';
$eyebrow_color = $attributes['eyebrowColor']  ?? 'cropx-blue';
$bg_color      = $attributes['bgColor']       ?? 'white';
$auto_advance  = (bool) ( $attributes['autoAdvance'] ?? false );

// Corner Radius override — empty values mean "not customized," in which
// case .fph-photo-wrap keeps the sitewide default (--radius-photo). The
// moment any corner is set, all four are written as CSS custom properties
// on the section wrapper; style.css falls back to the default per corner.
$radius_tl = $attributes['photoRadiusTopLeft']     ?? '';
$radius_tr = $attributes['photoRadiusTopRight']    ?? '';
$radius_br = $attributes['photoRadiusBottomRight'] ?? '';
$radius_bl = $attributes['photoRadiusBottomLeft']  ?? '';
$radius_style = '';
if ( '' !== $radius_tl || '' !== $radius_tr || '' !== $radius_br || '' !== $radius_bl ) {
	$radius_style = sprintf(
		'--fph-radius-tl:%1$s;--fph-radius-tr:%2$s;--fph-radius-br:%3$s;--fph-radius-bl:%4$s;',
		esc_attr( $radius_tl ?: '0' ),
		esc_attr( $radius_tr ?: '0' ),
		esc_attr( $radius_br ?: '0' ),
		esc_attr( $radius_bl ?: '0' )
	);
}

if ( ! in_array( $bg_color, [ 'white', 'taupe', 'deep-blue' ], true ) ) {
	$bg_color = 'white';
}

// ── Deep-blue topographic drift pattern ────────────────────────────────────
// Inject the pattern's real asset URL via a CSS custom property instead of
// a relative url() in style.css (webpack would base64-inline the ~90KB SVG).
// edit.js sets the same property for the editor preview. Same technique as
// two-column-video. Merged with the corner-radius style string (if any)
// into a single inline style attribute.
$wrapper_extra = [];
$style_parts   = [];
if ( '' !== $radius_style ) {
	$style_parts[] = $radius_style;
}
if ( 'deep-blue' === $bg_color ) {
	$style_parts[] = '--fph-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');';
}
if ( ! empty( $style_parts ) ) {
	$wrapper_extra['style'] = implode( '', $style_parts );
}

$section_class = 'fph-section fph-section--bg-' . $bg_color;

// Eyebrow inline colour is suppressed on deep-blue sections so CSS can apply
// the white override without fighting inline specificity.
$eyebrow_color_style = ( 'deep-blue' !== $bg_color )
	? ' style="color: var(--' . esc_attr( $eyebrow_color ) . ')"'
	: '';

// Build a flat indexed array so view.js can navigate by numeric index.
$photo_list = array_values( array_filter( $photos, fn( $p ) => ! empty( $p['url'] ) ) );
$count      = count( $photo_list );

if ( $count === 0 ) {
	return; // Nothing to render.
}

// ── Lightbox data ───────────────────────────────────────────────────────────
// Build the full-res URL list here (before the wrapper attrs call) so we can
// embed it as a data-fph-photos attribute on the section element directly.
// This is more reliable than the inline-script / document.currentScript
// approach, which can return null in WordPress's block rendering pipeline.
// get_block_wrapper_attributes() HTML-encodes the value via esc_attr();
// the browser decodes HTML entities when JS reads dataset.fphPhotos, so
// JSON.parse() receives valid JSON with real quote characters.
$lightbox_data = [];
foreach ( $photo_list as $photo ) {
	$id       = (int) ( $photo['id'] ?? 0 );
	$full_url = $id
		? ( wp_get_attachment_image_url( $id, 'full' ) ?: esc_url( $photo['url'] ?? '' ) )
		: esc_url( $photo['url'] ?? '' );
	$lightbox_data[] = [
		'src'     => $full_url,
		'alt'     => $photo['alt']     ?? '',
		'caption' => $photo['caption'] ?? '',
	];
}

$wrapper_attrs = get_block_wrapper_attributes( array_merge(
	[
		'class'                 => $section_class,
		'data-fph-photos'       => wp_json_encode( $lightbox_data ),
		'data-fph-auto-advance' => $auto_advance ? 'true' : 'false',
	],
	$wrapper_extra
) );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php if ( ( $eyebrow && $show_eyebrow ) || $show_heading || $intro_body ) : ?>
	<div class="fph-inner">
		<div class="fph-header">
			<?php if ( $eyebrow && $show_eyebrow ) : ?>
				<span class="section-eyebrow"<?php echo $eyebrow_color_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( $heading && $show_heading ) : ?>
				<h2 class="section-heading fph-heading"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>
			<?php if ( $intro_body ) : ?>
				<div class="section-body"><?php echo wp_kses_post( $intro_body ); ?></div>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

	<div class="fph-carousel">
		<div class="fph-viewport">
			<div class="fph-track">
				<?php foreach ( $photo_list as $index => $photo ) :
					$photo_id  = (int) ( $photo['id']  ?? 0 );
					$photo_url = $photo['url']     ?? '';
					$photo_alt = $photo['alt']     ?? '';
					$caption   = trim( $photo['caption'] ?? '' );
				?>
				<div class="fph-item" data-index="<?php echo esc_attr( $index ); ?>">
					<div class="fph-photo-wrap">
						<?php if ( $photo_id ) : ?>
							<?php echo wp_get_attachment_image( $photo_id, 'large', false, [
								'class'   => 'fph-photo',
								'loading' => $index === 0 ? 'eager' : 'lazy',
								'alt'     => esc_attr( $photo_alt ),
							] ); ?>
						<?php else : ?>
							<img
								class="fph-photo"
								src="<?php echo esc_url( $photo_url ); ?>"
								alt="<?php echo esc_attr( $photo_alt ); ?>"
								loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>"
							>
						<?php endif; ?>
					</div>
					<?php if ( $caption ) : ?>
						<p class="fph-caption"><?php echo esc_html( $caption ); ?></p>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="fph-controls" aria-label="<?php esc_attr_e( 'Gallery navigation', 'cropx' ); ?>">
			<button class="fph-prev" type="button" aria-label="<?php esc_attr_e( 'Previous photo', 'cropx' ); ?>" disabled>
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
					<path d="M12 4l-6 6 6 6" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
			<div class="fph-dots" role="tablist" aria-label="<?php esc_attr_e( 'Gallery slides', 'cropx' ); ?>"></div>
			<button class="fph-next" type="button" aria-label="<?php esc_attr_e( 'Next photo', 'cropx' ); ?>">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
					<path d="M8 4l6 6-6 6" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
		</div>
	</div>

	<?php /* ── Lightbox ── Output once per block; view.js populates the image src. */ ?>
	<div class="fph-lightbox" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Photo lightbox', 'cropx' ); ?>" hidden>
		<button class="fph-lightbox-close" type="button" aria-label="<?php esc_attr_e( 'Close lightbox', 'cropx' ); ?>">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
				<path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
			</svg>
		</button>
		<button class="fph-lightbox-prev" type="button" aria-label="<?php esc_attr_e( 'Previous photo', 'cropx' ); ?>">
			<svg width="28" height="28" viewBox="0 0 28 28" fill="none" aria-hidden="true">
				<path d="M17 5l-9 9 9 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</button>
		<img class="fph-lightbox-img" src="" alt="" loading="eager">
		<button class="fph-lightbox-next" type="button" aria-label="<?php esc_attr_e( 'Next photo', 'cropx' ); ?>">
			<svg width="28" height="28" viewBox="0 0 28 28" fill="none" aria-hidden="true">
				<path d="M11 5l9 9-9 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</button>
		<p class="fph-lightbox-caption"></p>
	</div>


</section>
