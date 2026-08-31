<?php
/**
 * Resource Downloads — front-end render.
 *
 * Renders one or more grids of downloadable resource cards, each linked to a
 * PDF or file. Resources are organized into `groups` — each group is
 * `{ subheading, selectedIds }`. A group with an empty subheading renders
 * just its grid with no heading above it, so "one heading + one grid" is
 * simply the single-group case; adding more groups is what produces
 * "Heading / Subheading / Grid / Subheading / Grid / …". Empty groups (no
 * resources selected) are skipped entirely — no orphan heading, no empty grid.
 *
 * Legacy back-compat: blocks saved before subsections existed only have a
 * flat `selectedIds` array and no `groups` key at all. If no group actually
 * carries any resources but that legacy attribute does, it's treated as a
 * single group with no subheading — this keeps already-published blocks
 * rendering correctly with zero editor action required. Opening and
 * re-saving the block in the editor migrates it to the new `groups` shape
 * (see the migration effect in edit.js).
 *
 * Collapsible subsections (Aug 2026): a group can set `collapsible` (bool)
 * and `collapsedByDefault` (bool). Collapsible only actually applies when
 * the group also has a non-empty subheading — there's nothing to click
 * without one, so `$is_collapsible` below always requires both. When
 * collapsible, the subheading renders as a real <button> (aria-expanded +
 * aria-controls, per the WAI-ARIA accordion pattern of a <button> nested in
 * an <h3>) instead of plain text, and the grid is wrapped in
 * .rsd-subsection-track > .rsd-subsection-content so view.js and CSS can
 * animate it open/closed via a grid-template-rows transition (see style.css
 * — no JS height measurement needed). Collapsed-by-default panels start
 * with the `inert` attribute so their download links/selects aren't
 * keyboard-focusable or screen-reader-exposed while hidden; view.js removes
 * it the moment a visitor expands the panel.
 *
 * Styled to match the FAQ Accordion block (Lauren, Aug 2026): a +/− icon
 * (two crossed lines, the vertical one rotating 90° to form a − when
 * expanded — see .rsd-icon-line--vertical in style.css) instead of a
 * chevron, and a top+bottom divider line framing each collapsible
 * subsection, the same way .faq-list/.faq-item border the FAQ block's rows.
 *
 * Download button logic (see cropx_resource_get_available_versions() in
 * inc/helpers.php for how a resource's language/format versions resolve):
 *   2+ versions on file → a <select> listing every available version
 *     (English A4 pre-selected when present) plus a "Download PDF" button
 *     that points at whichever version is currently selected. view.js wires
 *     the select's change event to the button's href and to re-rendering
 *     the cover thumbnail from the newly-selected version's own PDF.
 *   Exactly 1 version on file → a single plain "Download PDF" button,
 *     identical in every way to how this looked before the picker existed.
 *   0 versions → fall back to the legacy General URL field (download_url)
 *     for a single "Download PDF" button, then to the resource's own
 *     permalink ("View resource") if even that's empty.
 *
 * Open in browser, not force-download (Aug 2026, sitewide change — Lauren):
 * every "Download PDF" button opens the PDF in a new tab (target="_blank"
 * rel="noopener noreferrer") instead of triggering an automatic download via
 * the `download` attribute. Recommended and applied sitewide: letting
 * visitors preview a brochure/datasheet before deciding whether to keep it
 * is the friendlier default, every modern browser's built-in PDF viewer has
 * its own explicit Download button for anyone who does want a local copy,
 * and a surprise file landing in Downloads is often read as an unwanted
 * side effect rather than something the visitor chose. The same change was
 * made to single-cropx_resource.php and the 5 blocks with a media-file CTA
 * option (pre-footer-cta, mid-page-cta, hero-curved, hero-curved-standard,
 * hero-curved-animated) — see PROGRESS.md for the full list.
 *
 * Cover image priority per card:
 *   1. download_attachment_id → wp_get_attachment_image() with 'cropx-doc-cover'.
 *      WordPress auto-generates a first-page thumbnail from any PDF uploaded to
 *      the Media Library when Imagick/Ghostscript is available.
 *   2. Post featured image  → wp_get_attachment_image() with 'cropx-doc-cover'
 *      (equivalent to get_the_post_thumbnail(), used directly so the same
 *      landscape check below can run against whichever source actually wins —
 *      see the "Which source actually renders" note further down).
 *   3. Placeholder, rendered client-side by view.js via pdf.js from
 *      cropx_resource_get_thumbnail_version()'s file (inc/helpers.php) —
 *      the current site language's A4 version, falling back to English A4.
 *      This is intentionally NOT the version a visitor has picked in the
 *      download dropdown: the thumbnail is a stable "what is this resource"
 *      representation, not a live preview of the current download
 *      selection, so it never changes when that dropdown changes (Lauren,
 *      Aug 2026) — only the download link's href does.
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

$groups = (array) ( $attributes['groups'] ?? [] );

// Legacy fallback — see doc comment above.
$legacy_selected_ids = (array) ( $attributes['selectedIds'] ?? [] );
$groups_have_content = false;
foreach ( $groups as $group ) {
	if ( ! empty( $group['selectedIds'] ) ) {
		$groups_have_content = true;
		break;
	}
}
if ( ! $groups_have_content && ! empty( $legacy_selected_ids ) ) {
	$groups = [ [ 'subheading' => '', 'selectedIds' => $legacy_selected_ids ] ];
}

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

// Resolve each group to its published posts (filtering out drafts so nothing
// leaks to the front end), and drop any group that ends up with nothing to
// show — an empty group renders neither a subheading nor an empty grid.
$rendered_groups = [];
foreach ( $groups as $group ) {
	$group_posts = array_filter(
		array_map( 'get_post', (array) ( $group['selectedIds'] ?? [] ) ),
		fn( $p ) => $p && 'publish' === $p->post_status
	);
	if ( empty( $group_posts ) ) {
		continue;
	}
	$group_subheading = trim( (string) ( $group['subheading'] ?? '' ) );
	$rendered_groups[] = [
		'subheading'         => $group_subheading,
		'posts'              => $group_posts,
		// Collapsible requires a subheading to click — see doc comment above.
		'collapsible'        => '' !== $group_subheading && ! empty( $group['collapsible'] ),
		'collapsedByDefault' => ! empty( $group['collapsedByDefault'] ),
	];
}

if ( empty( $rendered_groups ) ) {
	return;
}

$section_class = 'rsd-section rsd-section--bg-' . $bg_color;
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

// +/− icon for collapsible subsection triggers — matches the FAQ Accordion
// block's icon exactly (two crossed lines; the vertical one rotates 90° via
// CSS, keyed off .rsd-subsection--collapsible's is-collapsed class, to turn
// the + into a −). Sized larger than FAQ's 24px box per Lauren's request —
// see .rsd-subheading-icon in style.css.
$toggle_icon = '<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">'
	. '<line class="rsd-icon-line rsd-icon-line--horizontal" x1="5" y1="12" x2="19" y2="12"/>'
	. '<line class="rsd-icon-line rsd-icon-line--vertical" x1="12" y1="5" x2="12" y2="19"/>'
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

		<?php foreach ( $rendered_groups as $group_index => $rendered_group ) :
			$group_posts = $rendered_group['posts'];
			$group_count = count( $group_posts );
			$group_grid_class = 'rsd-grid rsd-grid--cols-' . $columns
				. ( ( 'center' === $grid_align && $group_count < $columns ) ? ' rsd-grid--centered' : '' );

			$is_collapsible   = $rendered_group['collapsible'];
			$collapsed_default = $is_collapsible && $rendered_group['collapsedByDefault'];
			$panel_id           = $block_id . '-panel-' . $group_index;
			$subsection_class   = 'rsd-subsection'
				. ( $is_collapsible ? ' rsd-subsection--collapsible' : '' )
				. ( $collapsed_default ? ' is-collapsed' : '' );
		?>
		<div class="<?php echo esc_attr( $subsection_class ); ?>">

			<?php if ( $rendered_group['subheading'] ) : ?>
				<?php if ( $is_collapsible ) : ?>
					<h3 class="rsd-subheading-heading">
						<button type="button"
						        class="rsd-subheading rsd-subheading--toggle"
						        aria-expanded="<?php echo $collapsed_default ? 'false' : 'true'; ?>"
						        aria-controls="<?php echo esc_attr( $panel_id ); ?>">
							<span class="rsd-subheading-text"><?php echo esc_html( $rendered_group['subheading'] ); ?></span>
							<span class="rsd-subheading-icon" aria-hidden="true">
								<?php echo $toggle_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						</button>
					</h3>
				<?php else : ?>
					<h3 class="rsd-subheading"><?php echo esc_html( $rendered_group['subheading'] ); ?></h3>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( $is_collapsible ) : ?>
			<div class="rsd-subsection-track">
				<div class="rsd-subsection-content" id="<?php echo esc_attr( $panel_id ); ?>"<?php echo $collapsed_default ? ' inert' : ''; ?>>
					<div class="rsd-subsection-inner">
			<?php endif; ?>

			<div class="<?php echo esc_attr( $group_grid_class ); ?>">
			<?php foreach ( $group_posts as $post ) :
				$post_id       = $post->ID;
				$title         = get_the_title( $post );
				$excerpt       = cropx_get_card_excerpt( $post );
				$download_url  = get_post_meta( $post_id, 'download_url',           true );
				$attachment_id = (int) get_post_meta( $post_id, 'download_attachment_id', true );
				$permalink     = get_permalink( $post_id );

				// Language/format versions — see cropx_resource_get_available_versions()
				// in inc/helpers.php. 0 → fall back to $download_url/$permalink below;
				// 1 → a single plain button (identical to pre-picker behavior); 2+ →
				// the <select> + separate download button (see markup further down).
				$versions       = cropx_resource_get_available_versions( $post_id );
				$version_count  = count( $versions );
				$default_version = $versions[0] ?? null;
				foreach ( $versions as $v ) {
					if ( 'en_a4' === $v['code'] ) {
						$default_version = $v;
						break;
					}
				}

				// URL passed to pdf.js for client-side thumbnail generation.
				// Only set when a real PDF file URL is available — never the permalink.
				// pdf.js will render the first page of the PDF as a canvas thumbnail.
				//
				// Deliberately NOT tied to $default_version (the download dropdown's
				// pre-selected option) — the thumbnail always represents the resource
				// itself, not whatever the visitor currently has selected to download.
				// cropx_resource_get_thumbnail_version() resolves independently: the
				// current site language's A4 version today (always English A4 on this
				// English-only site), and will follow a future language switcher
				// without any change needed here. See its doc comment in
				// inc/helpers.php. view.js's version-select handler only ever updates
				// the download link's href — it never touches the cover.
				$thumbnail_version = cropx_resource_get_thumbnail_version( $post_id );
				$pdf_thumb_url      = $thumbnail_version['url'] ?? $download_url;

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
				// a landscape cover forced into .rsd-cover-wrap's fixed portrait
				// frame with object-fit:cover loses its sides, not just its bottom
				// edge, so it needs the two-layer blurred-backdrop treatment
				// instead.
				//
				// Which source actually renders: download_attachment_id (set via
				// the Download Files → General URL Media Library button) wins
				// when WordPress can generate a real image from it — true for
				// image attachments, and for PDFs once Imagick/Ghostscript can
				// rasterize them. When that lookup comes up empty (most often a
				// PDF with no generated image sizes — see cpts.php), the
				// resource's own Featured Image is what actually renders, so the
				// landscape check has to run against THAT source instead.
				//
				// Bug fix (Aug 2026): previously the check always looked at
				// $attachment_id first regardless of whether it produced a usable
				// image, so a landscape Featured Image (PNG/JPEG/WEBP) sitting
				// alongside an unrelated PDF download_attachment_id never got the
				// two-layer treatment — the check silently failed against the PDF,
				// $is_landscape stayed false, and the featured image's sides got
				// cropped off by plain object-fit:cover. Now we resolve which
				// attachment ID will actually supply $cover_html first (validating
				// it has real image dimensions), then check landscape on that
				// same source.
				$cover_html    = '';
				$cover_bg_html = '';
				$is_landscape  = false;

				$cover_src_id   = 0;
				$cover_src_data = false;

				if ( $attachment_id ) {
					$data = wp_get_attachment_image_src( $attachment_id, 'cropx-doc-cover' );
					if ( $data && ! empty( $data[1] ) && ! empty( $data[2] ) ) {
						$cover_src_id   = $attachment_id;
						$cover_src_data = $data;
					}
				}
				if ( ! $cover_src_data && has_post_thumbnail( $post_id ) ) {
					$thumb_id = get_post_thumbnail_id( $post_id );
					$data     = wp_get_attachment_image_src( $thumb_id, 'cropx-doc-cover' );
					if ( $data && ! empty( $data[1] ) && ! empty( $data[2] ) ) {
						$cover_src_id   = $thumb_id;
						$cover_src_data = $data;
					}
				}

				if ( $cover_src_data ) {
					$is_landscape = $cover_src_data[1] > $cover_src_data[2];
				}

				$cover_fg_class = 'rsd-cover' . ( $is_landscape ? ' rsd-cover--fg' : '' );

				if ( $cover_src_id ) {
					$cover_html = wp_get_attachment_image( $cover_src_id, 'cropx-doc-cover', false, [
						'class'   => $cover_fg_class,
						'loading' => 'lazy',
						'alt'     => esc_attr( $title ),
					] );
					if ( $is_landscape ) {
						$cover_bg_html = wp_get_attachment_image( $cover_src_id, 'cropx-doc-cover', false, [
							'class'       => 'rsd-cover rsd-cover--bg',
							'loading'     => 'lazy',
							'alt'         => '',
							'aria-hidden' => 'true',
						] );
					}
				}
			?>
			<article class="rsd-card<?php echo $card_color === 'blue' ? ' rsd-card--blue' : ''; ?>">

				<?php // Cover is intentionally not a link — the download button/picker ?>
				<?php // below is the only way to download. ?>
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

					<?php if ( $version_count >= 2 ) : ?>
						<?php // Language/format picker — select sets which version the ?>
						<?php // Download button points at; view.js updates the button's ?>
						<?php // href on change and re-renders the cover from that version's ?>
						<?php // PDF via pdf.js (see the "Language/format picker" CSS block ?>
						<?php // in style.css and view.js for the front-end behavior). ?>
						<div class="rsd-version-picker">
							<select class="rsd-version-select" aria-label="<?php esc_attr_e( 'Choose language and format', 'cropx' ); ?>">
								<?php foreach ( $versions as $v ) : ?>
									<option value="<?php echo esc_url( $v['url'] ); ?>"
									        data-code="<?php echo esc_attr( $v['code'] ); ?>"
									        <?php selected( $v['code'], $default_version['code'] ?? '' ); ?>>
										<?php echo esc_html( $v['label'] ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<a href="<?php echo esc_url( $default_version['url'] ?? '' ); ?>"
							   class="rsd-download-btn rsd-version-download"
							   target="_blank" rel="noopener noreferrer">
								<?php echo $download_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php esc_html_e( 'Download PDF', 'cropx' ); ?>
							</a>
						</div>
					<?php elseif ( 1 === $version_count ) : ?>
						<?php // Exactly one version on file — plain single button, ?>
						<?php // identical to how this looked before the picker existed. ?>
						<a href="<?php echo esc_url( $default_version['url'] ); ?>"
						   class="rsd-download-btn"
						   target="_blank" rel="noopener noreferrer">
							<?php echo $download_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php esc_html_e( 'Download PDF', 'cropx' ); ?>
						</a>
					<?php elseif ( $download_url ) : ?>
						<?php // Legacy fallback — a resource with no language-picker fields ?>
						<?php // filled in at all, only the older General URL field. ?>
						<a href="<?php echo esc_url( $download_url ); ?>"
						   class="rsd-download-btn"
						   target="_blank" rel="noopener noreferrer">
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

			<?php if ( $is_collapsible ) : ?>
					</div>
				</div>
			</div>
			<?php endif; ?>

		</div>
		<?php endforeach; ?>

	</div>
</section>
