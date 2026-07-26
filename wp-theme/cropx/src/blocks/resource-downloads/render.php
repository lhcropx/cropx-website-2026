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
 *
 * Grid centering:
 *   When count($posts) < $columns the grid gets .rsd-grid--centered, switching
 *   to flexbox with justify-content:center so a small set of cards sits centered
 *   rather than pinned to the left edge.
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

if ( ! in_array( $card_color, [ 'white', 'blue' ], true ) ) {
	$card_color = 'white';
}

if ( ! in_array( $bg_color, [ 'white', 'taupe', 'deep-blue' ], true ) ) {
	$bg_color = 'white';
}
if ( ! in_array( $columns, [ 2, 3, 4 ], true ) ) {
	$columns = 4;
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
$grid_class    = 'rsd-grid rsd-grid--cols-' . $columns . ( $count < $columns ? ' rsd-grid--centered' : '' );

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
			<div class="rsd-header">
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
				$excerpt       = get_the_excerpt( $post );
				$download_url  = get_post_meta( $post_id, 'download_url',           true );
				$url_letter    = get_post_meta( $post_id, 'download_url_letter',    true );
				$url_a4        = get_post_meta( $post_id, 'download_url_a4',        true );
				$attachment_id = (int) get_post_meta( $post_id, 'download_attachment_id', true );
				$permalink     = get_permalink( $post_id );

				// Format buttons appear automatically when the resource has the URL set.
				// Control is per-resource: add/remove the URL in the resource entry.
				$has_letter = ! empty( $url_letter );
				$has_a4     = ! empty( $url_a4 );

				// Primary link for title / cover (prefer letter URL, then a4, then general, then permalink).
				$primary_url = $url_letter ?: ( $url_a4 ?: ( $download_url ?: $permalink ) );

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
				$cover_html = '';
				if ( $attachment_id ) {
					$cover_html = wp_get_attachment_image( $attachment_id, 'cropx-doc-cover', false, [
						'class'   => 'rsd-cover',
						'loading' => 'lazy',
						'alt'     => esc_attr( $title ),
					] );
				}
				if ( ! $cover_html && has_post_thumbnail( $post_id ) ) {
					$cover_html = get_the_post_thumbnail( $post_id, 'cropx-doc-cover', [
						'class'   => 'rsd-cover',
						'loading' => 'lazy',
						'alt'     => esc_attr( $title ),
					] );
				}
			?>
			<article class="rsd-card<?php echo $card_color === 'blue' ? ' rsd-card--blue' : ''; ?>">

				<a href="<?php echo esc_url( cropx_url( $primary_url ) ); ?>"
				   class="rsd-cover-link"
				   aria-hidden="true"
				   tabindex="-1">
					<div class="rsd-cover-wrap">
						<?php if ( $cover_html ) : ?>
							<?php echo $cover_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php else : ?>
							<div class="rsd-cover-placeholder"<?php echo $pdf_thumb_url ? ' data-pdf-url="' . esc_url( $pdf_thumb_url ) . '"' : ''; ?>>
								<?php echo $placeholder_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						<?php endif; ?>
					</div>
				</a>

				<div class="rsd-card-body">

					<?php if ( $type_label ) : ?>
						<span class="rsd-type-tag"><?php echo esc_html( $type_label ); ?></span>
					<?php endif; ?>

					<?php if ( $title ) : ?>
						<h3 class="rsd-title">
							<a href="<?php echo esc_url( cropx_url( $primary_url ) ); ?>">
								<?php echo esc_html( $title ); ?>
							</a>
						</h3>
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
