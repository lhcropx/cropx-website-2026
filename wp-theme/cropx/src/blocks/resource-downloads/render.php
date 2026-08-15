<?php
/**
 * Resource Downloads — front-end render.
 *
 * Renders a grid of downloadable resource cards, each linked to a PDF or file.
 * Resources are selected by the editor (stored as an array of post IDs in the
 * `selectedIds` attribute).
 *
 * Download button logic:
 *   If showLetterDownload/showA4Download is on AND the resource has the matching
 *   URL set → show side-by-side "US Letter" / "A4" format buttons.
 *   Otherwise → single "Download PDF" button (download_url) or "View resource" link.
 *
 * Cover image priority per card:
 *   1. download_attachment_id → wp_get_attachment_image() with 'cropx-doc-cover'.
 *      WordPress auto-generates a first-page thumbnail from any PDF uploaded to
 *      the Media Library when Imagick/Ghostscript is available.
 *   2. Post featured image  → get_the_post_thumbnail() with 'cropx-doc-cover'.
 *   3. Placeholder          → an SVG document icon in a tinted block.
 * The cover is deliberately not a link — the download/format buttons in the
 * card body are the only way to download a resource.
 *
 * Alignment (editor-controlled):
 *   introAlign — 'left' | 'center' (default 'center'). Aligns the eyebrow/
 *     heading/body intro block; 'left' also pins it to the left edge instead
 *     of centering the whole block within its own max-width.
 *   gridAlign  — 'left' | 'center' (default 'center'). Only visibly matters
 *     when count($posts) < $columns, since a full row always fills the grid
 *     edge-to-edge either way. When 'center', a partial last row gets
 *     .rsd-grid--centered (flexbox + justify-content:center); when 'left',
 *     that class is omitted and the partial row uses CSS Grid's default
 *     left-aligned auto-placement.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$selected_ids       = (array) ( $attributes['selectedIds']       ?? [] );
$eyebrow            = $attributes['eyebrow']            ?? '';
$heading            = $attributes['heading']            ?? '';
$show_eyebrow       = (bool) ( $attributes['showEyebrow']       ?? true );
$show_heading       = (bool) ( $attributes['showHeading']       ?? true );
$eyebrow_color      = $attributes['eyebrowColor']       ?? 'cropx-blue';
$bg_color           = $attributes['bgColor']            ?? 'white';
$columns            = (int) ( $attributes['columns']    ?? 4 );
$card_color         = $attributes['cardColor']         ?? 'white';
$intro_align        = $attributes['introAlign']        ?? 'center';
$grid_align         = $attributes['gridAlign']         ?? 'center';
$show_type_tag      = (bool) ( $attributes['showTypeTag'] ?? true );

if ( ! in_array( $card_color, [ 'white', 'blue' ], true ) ) {
	$card_color = 'white';
}

if ( ! in_array( $bg_color, [ 'white', 'taupe', 'deep-blue' ], true ) ) {
	$bg_color = 'white';
}
if ( ! in_array( $columns, [ 2, 3, 4, 5, 6 ], true ) ) {
	$columns = 4;
}
if ( ! in_array( $intro_align, [ 'left', 'center' ], true ) ) {
	$intro_align = 'center';
}
if ( ! in_array( $grid_align, [ 'left', 'center' ], true ) ) {
	$grid_align = 'center';
}

// Filter to published posts only so no drafts leak to the front end.
$posts = array_filter(
	array_map( 'get_post', $selected_ids ),
	fn( $p ) => $p && 'publish' === $p->post_status
);

if ( empty( $posts ) ) {
	return;
}

$count         = count( $posts );
$section_class = 'rsd-section rsd-section--bg-' . $bg_color;
$grid_class    = 'rsd-grid rsd-grid--cols-' . $columns
	. ( ( 'center' === $grid_align && $count < $columns ) ? ' rsd-grid--centered' : '' );
$header_class  = 'rsd-header' . ( 'left' === $intro_align ? ' rsd-header--left' : '' );

// ── Deep-blue topographic drift pattern ────────────────────────────────────
// Inject a per-instance inline <style> that sets background-image on the
// ::before pseudo-element, keyed on a unique data attribute. Same technique
// as the video block's drift pattern.
$block_id = uniqid( 'rsd-' );
if ( $bg_color === 'deep-blue' ) {
	$drift_url = esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' );
	echo '<style>.rsd-section[data-rsd-drift="' . esc_attr( $block_id ) . '"]::before{background-image:url(' . $drift_url . ')}</style>';
}

$wrapper_extra = ( $bg_color === 'deep-blue' ) ? [ 'data-rsd-drift' => $block_id ] : [];
$wrapper_attrs = get_block_wrapper_attributes( array_merge( [ 'class' => $section_class ], $wrapper_extra ) );

// Placeholder SVG: document icon used when no cover image is available.
$placeholder_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" aria-hidden="true">'
	. '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>'
	. '<polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>'
	. '<line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'
	. '<line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'
	. '</svg>';

// Download arrow icon used in CTA buttons.
$download_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">'
	. '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>'
	. '<polyline points="7 10 12 15 17 10" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>'
	. '<line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>'
	. '</svg>';
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="rsd-inner">

		<?php if ( ( $eyebrow && $show_eyebrow ) || ( $heading && $show_heading ) ) : ?>
			<div class="<?php echo esc_attr( $header_class ); ?>">
				<?php if ( $eyebrow && $show_eyebrow ) : ?>
					<?php // Color is handled entirely by CSS — cropx-blue on light bg, white on deep-blue bg. ?>
					<span class="section-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>
				<?php if ( $heading && $show_heading ) : ?>
					<h2 class="section-heading"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="<?php echo esc_attr( $grid_class ); ?>">
			<?php foreach ( $posts as $post ) :
				$post_id       = $post->ID;
				$title         = get_the_title( $post );
				$excerpt       = cropx_get_card_excerpt( $post );
				$download_url  = get_post_meta( $post_id, 'download_url',           true );
				$url_letter    = get_post_meta( $post_id, 'download_url_letter',    true );
				$url_a4        = get_post_meta( $post_id, 'download_url_a4',        true );
				$attachment_id = (int) get_post_meta( $post_id, 'download_attachment_id', true );
				$permalink     = get_permalink( $post_id );

				// Format buttons appear automatically when the resource has the URL set.
				// Control is per-resource: add/remove the URL in the resource entry.
				$has_letter = ! empty( $url_letter );
				$has_a4     = ! empty( $url_a4 );

				// URL passed to pdf.js for client-side thumbnail generation.
				// Only set when a real PDF file URL is available — never the permalink.
				// pdf.js will render the first page of the PDF as a canvas thumbnail.
				$pdf_thumb_url = $url_letter ?: ( $url_a4 ?: $download_url );

				// Resource type taxonomy term.
				$type_terms = get_the_terms( $post_id, 'cropx_resource_type' );
				$type_label = ( $type_terms && ! is_wp_error( $type_terms ) )
					? reset( $type_terms )->name
					: '';

				// ── Cover image ────────────────────────────────────────────────────
				// Landscape check: 'cropx-doc-cover' is registered with hard-crop
				// off (add_image_size( 'cropx-doc-cover', 841, 841, false )), so it
				// always preserves the source document's true aspect ratio — a
				// simple width > height check on that size is enough to know
				// whether the page is landscape. See view.js for why this matters:
				// a landscape page forced into .rsd-cover-wrap's fixed portrait
				// frame with object-fit:cover loses its sides, not just its bottom
				// edge, so it needs the two-layer blurred-backdrop treatment
				// instead. This path isn't live yet (no server-generated PDF
				// thumbnails until the ImageMagick policy is fixed — see cpts.php),
				// but is wired up now so the fix survives that migration.
				$cover_html    = '';
				$cover_bg_html = '';
				$is_landscape  = false;

				$cover_src_id = $attachment_id ?: ( has_post_thumbnail( $post_id ) ? get_post_thumbnail_id( $post_id ) : 0 );
				if ( $cover_src_id ) {
					$cover_src_data = wp_get_attachment_image_src( $cover_src_id, 'cropx-doc-cover' );
					if ( $cover_src_data && ! empty( $cover_src_data[1] ) && ! empty( $cover_src_data[2] ) ) {
						$is_landscape = $cover_src_data[1] > $cover_src_data[2];
					}
				}

				$cover_fg_class = 'rsd-cover' . ( $is_landscape ? ' rsd-cover--fg' : '' );

				if ( $attachment_id ) {
					$cover_html = wp_get_attachment_image( $attachment_id, 'cropx-doc-cover', false, [
						'class'   => $cover_fg_class,
						'loading' => 'lazy',
						'alt'     => esc_attr( $title ),
					] );
					if ( $is_landscape ) {
						$cover_bg_html = wp_get_attachment_image( $attachment_id, 'cropx-doc-cover', false, [
							'class'       => 'rsd-cover rsd-cover--bg',
							'loading'     => 'lazy',
							'alt'         => '',
							'aria-hidden' => 'true',
						] );
					}
				}
				if ( ! $cover_html && has_post_thumbnail( $post_id ) ) {
					$cover_html = get_the_post_thumbnail( $post_id, 'cropx-doc-cover', [
						'class'   => $cover_fg_class,
						'loading' => 'lazy',
						'alt'     => esc_attr( $title ),
					] );
					if ( $is_landscape ) {
						$cover_bg_html = get_the_post_thumbnail( $post_id, 'cropx-doc-cover', [
							'class'       => 'rsd-cover rsd-cover--bg',
							'loading'     => 'lazy',
							'alt'         => '',
							'aria-hidden' => 'true',
						] );
					}
				}
			?>
			<article class="rsd-card<?php echo $card_color === 'blue' ? ' rsd-card--blue' : ''; ?>">

				<?php // Cover is intentionally not a link — the format buttons below ?>
				<?php // (US Letter / A4 / Download PDF) are the only way to download. ?>
				<div class="rsd-cover-wrap<?php echo $is_landscape ? ' rsd-cover-wrap--landscape' : ''; ?>">
					<?php if ( $cover_html ) : ?>
						<?php if ( $cover_bg_html ) : ?>
							<?php echo $cover_bg_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php endif; ?>
						<?php echo $cover_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php else : ?>
						<div class="rsd-cover-placeholder"<?php echo $pdf_thumb_url ? ' data-pdf-url="' . esc_url( $pdf_thumb_url ) . '"' : ''; ?>>
							<?php echo $placeholder_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="rsd-card-body">

					<?php if ( $type_label && $show_type_tag ) : ?>
						<span class="rsd-type-tag"><?php echo esc_html( $type_label ); ?></span>
					<?php endif; ?>

					<?php if ( $title ) : ?>
						<?php // Plain text on purpose — not a link. The cover image and the ?>
						<?php // download/format buttons below are the only clickable actions ?>
						<?php // on this card; the title itself should never look or behave ?>
						<?php // like a link (no underline, not clickable). ?>
						<h3 class="rsd-title"><?php echo esc_html( $title ); ?></h3>
					<?php endif; ?>

					<?php if ( $excerpt ) : ?>
						<p class="rsd-excerpt"><?php echo esc_html( $excerpt ); ?></p>
					<?php endif; ?>

					<?php if ( $has_letter || $has_a4 ) : ?>
						<?php // Format-specific download buttons (Letter and/or A4). ?>
						<div class="rsd-download-formats">
							<?php if ( $has_letter ) : ?>
								<a href="<?php echo esc_url( $url_letter ); ?>"
								   class="rsd-format-btn"
								   download>
									<?php echo $download_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php esc_html_e( 'US Letter', 'cropx' ); ?>
								</a>
							<?php endif; ?>
							<?php if ( $has_a4 ) : ?>
								<a href="<?php echo esc_url( $url_a4 ); ?>"
								   class="rsd-format-btn"
								   download>
									<?php echo $download_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php esc_html_e( 'A4', 'cropx' ); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php elseif ( $download_url ) : ?>
						<?php // Single generic download button. ?>
						<a href="<?php echo esc_url( $download_url ); ?>"
						   class="rsd-download-btn"
						   download>
							<?php echo $download_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php esc_html_e( 'Download PDF', 'cropx' ); ?>
						</a>
					<?php elseif ( $permalink ) : ?>
						<?php // No file URL — link to the resource's own page. ?>
						<a href="<?php echo esc_url( cropx_url( $permalink ) ); ?>"
						   class="rsd-download-btn rsd-download-btn--link">
							<?php esc_html_e( 'View resource', 'cropx' ); ?>
							<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
								<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</a>
					<?php endif; ?>

				</div>
			</article>
			<?php endforeach; ?>
		</div>

	</div>
</section>
