<?php
/**
 * Mid-page CTA — front-end render
 *
 * Attributes:
 *   backgroundStyle  string  'white' | 'dark'
 *   showEyebrow      bool
 *   eyebrowColor     string  'cropx-blue' | 'muted-gold' | 'terra' | 'new-leaf'
 *   eyebrow          string
 *   heading          string
 *   showBody         bool
 *   body             string
 *   primaryLabel     string
 *   primaryUrl       string
 *   showSecondary    bool
 *   secondaryStyle   string  'link' | 'ghost'
 *   secondaryLabel   string
 *   secondaryUrl     string
 */

$bg_style        = $attributes['backgroundStyle'] ?? 'taupe';
$show_eyebrow    = $attributes['showEyebrow'] ?? true;
$eyebrow_color_map = [
	'cropx-blue' => 'var(--cropx-blue)',
	'deep-blue'  => 'var(--deep-blue)',
	'white'      => '#fff',
];
$eyebrow_color_css = $eyebrow_color_map[ $attributes['eyebrowColor'] ?? 'cropx-blue' ] ?? 'var(--cropx-blue)';
$eyebrow         = $attributes['eyebrow'] ?? '';
$heading         = $attributes['heading'] ?? '';
$show_body       = $attributes['showBody'] ?? true;
$body            = $attributes['body'] ?? '';
$primary_label   = $attributes['primaryLabel'] ?? '';
$primary_url     = $attributes['primaryUrl'] ?? '#';
$show_secondary  = $attributes['showSecondary'] ?? true;
$secondary_style = $attributes['secondaryStyle'] ?? 'link';
$secondary_label = $attributes['secondaryLabel'] ?? '';
$secondary_url   = $attributes['secondaryUrl'] ?? '#';

// Primary/secondary CTA can each point to a URL (default) or a media-library
// file download. In file mode the href resolves straight to the attachment
// URL. Open in browser, not force-download (Aug 2026, sitewide change —
// Lauren): the anchor gets target="_blank" rel="noopener noreferrer" instead
// of a `download` attribute, so the PDF opens in a new tab using the
// browser's own viewer rather than dropping straight into the visitor's
// downloads folder — see resource-downloads/render.php's doc comment for the
// full reasoning. The secondary CTA additionally swaps its animated arrow
// icon for a static download icon — see the shared icon markup below, reused
// from resource-downloads/render.php.
$primary_link_type   = $attributes['primaryLinkType']   ?? 'url';
$primary_file_url     = $attributes['primaryFileUrl']    ?? '';
$primary_is_file      = ( 'file' === $primary_link_type && $primary_file_url );
$primary_href          = $primary_is_file ? $primary_file_url : $primary_url;

$secondary_link_type = $attributes['secondaryLinkType'] ?? 'url';
$secondary_file_url   = $attributes['secondaryFileUrl']  ?? '';
$secondary_is_file    = ( 'file' === $secondary_link_type && $secondary_file_url );
$secondary_href        = $secondary_is_file ? $secondary_file_url : $secondary_url;

$is_dark = $bg_style === 'dark';

$bg_class_map  = array( 'dark' => 'mcta--dark', 'taupe' => 'mcta--taupe', 'white' => 'mcta--white' );
$section_class = 'cropx-mid-cta ' . ( $bg_class_map[ $bg_style ] ?? 'mcta--taupe' );

// Button classes
$primary_class   = $is_dark ? 'mcta-btn mcta-btn--white' : 'mcta-btn mcta-btn--primary';
$secondary_class = 'mcta-btn-secondary';

// Arrow SVG for secondary CTA (text link with arrow) — swapped for a static
// download icon when the secondary CTA points to a media-file download.
$arrow_svg = $secondary_is_file
	? '<svg class="cta-icon--static" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/><polyline points="7 10 12 15 17 10" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>'
	: '<svg width="18" height="18" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

// Inject the drift pattern's real asset URL via a CSS custom property
// instead of a relative url() in style.css (webpack would base64-inline the
// ~90KB SVG). edit.js sets the same property for the editor preview. Same
// technique as two-column-video.
$_mcta_attrs = [
	'class' => esc_attr( $section_class ),
];
if ( $is_dark ) {
	$_mcta_attrs['style'] = '--mcta-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');';
}
if ( in_array( $bg_style, [ 'taupe', 'white' ], true ) ) {
	$_mcta_attrs['data-section-bg'] = $bg_style;
}
$wrapper_attrs = get_block_wrapper_attributes( $_mcta_attrs );
?>
<section <?php echo $wrapper_attrs; ?>>
	<div class="mcta-inner">

		<?php if ( $show_eyebrow && $eyebrow ) : ?>
			<span class="section-eyebrow" style="color: <?php echo esc_attr( $eyebrow_color_css ); ?>"><?php echo esc_html( $eyebrow ); ?></span>
		<?php endif; ?>

		<?php if ( $heading ) : ?>
			<h2 class="section-heading"><?php echo wp_kses_post( $heading ); ?></h2>
		<?php endif; ?>

		<?php if ( $show_body && $body ) : ?>
			<div class="section-body"><?php echo wp_kses_post( $body ); ?></div>
		<?php endif; ?>

		<?php if ( $primary_label ) : ?>
		<div class="mcta-actions">
			<a href="<?php echo esc_url( $primary_href ); ?>" class="<?php echo esc_attr( $primary_class ); ?>"<?php echo $primary_is_file ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
				<?php echo esc_html( $primary_label ); ?>
			</a>

			<?php if ( $show_secondary && $secondary_label ) : ?>
				<a href="<?php echo esc_url( $secondary_href ); ?>" class="<?php echo esc_attr( $secondary_class ); ?>"<?php echo $secondary_is_file ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
					<?php echo esc_html( $secondary_label ); ?>
					<?php echo $arrow_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			<?php endif; ?>
		</div>
		<?php endif; ?>

	</div>
</section>
