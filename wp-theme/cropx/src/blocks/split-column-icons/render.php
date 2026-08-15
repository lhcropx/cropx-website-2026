<?php
/**
 * Split Header + Icon Columns block — front-end render.
 *
 * Layout: two-zone grid.
 *   Left  (1/3) — eyebrow, h2, body paragraph
 *   Right (2/3) — 3-column icon grid (same feature set as three-column-icons)
 *
 * Variants:
 *   backgroundVariant=white/taupe + segment accent → tinted icon boxes
 *   backgroundVariant=blue                        → white icon boxes always
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$eyebrow        = $attributes['eyebrow']           ?? '';
$heading        = $attributes['heading']            ?? '';
$body           = $attributes['body']               ?? '';
$bg_variant     = $attributes['backgroundVariant']  ?? 'white';
$segment_accent = $attributes['segmentAccent']      ?? 'general';
$eyebrow_color  = $attributes['eyebrowColor']       ?? 'cropx-blue';
$show_eyebrow   = (bool) ( $attributes['showEyebrow'] ?? true );
$show_body      = (bool) ( $attributes['showBody']    ?? true );
$show_icons     = (bool) ( $attributes['showIcons']   ?? true );

$columns = $attributes['columns'] ?? array();

// Validate enums.
if ( ! in_array( $bg_variant, array( 'taupe', 'white', 'blue' ), true ) ) {
	$bg_variant = 'white';
}
if ( ! in_array( $segment_accent, array( 'general', 'enterprise', 'service-provider', 'on-farm' ), true ) ) {
	$segment_accent = 'general';
}

$allowed_icons = cropx_allowed_icon_slugs();

$section_class = 'spi-section spi-section--' . $bg_variant;
if ( in_array( $bg_variant, array( 'white', 'taupe' ), true ) && 'general' !== $segment_accent ) {
	$section_class .= ' spi-segment-' . $segment_accent;
}

$_spi_attrs = array( 'class' => $section_class );
if ( in_array( $bg_variant, array( 'taupe', 'white' ), true ) ) {
	$_spi_attrs['data-section-bg'] = $bg_variant;
}

// Deep-blue topo overlay: inject the pattern's real asset URL via a CSS
// custom property instead of letting the CSS reference it by relative path.
// See two-column-video/render.php for the full rationale (relative url()
// gets base64-inlined by webpack, ballooning style-index.css to a size that
// silently fails to overwrite during WP File Manager zip deploys). edit.js
// sets the same property for the editor preview.
if ( 'blue' === $bg_variant ) {
	$_spi_attrs['style'] = '--spi-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');';
}

$wrapper_attrs = get_block_wrapper_attributes( $_spi_attrs );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
$allowed_body = array_merge( $allowed_inline, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="spi-inner">

		<!-- Left: section header -->
		<div class="spi-header">
			<?php if ( $show_eyebrow && $eyebrow ) : ?>
				<span class="section-eyebrow" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></span>
			<?php endif; ?>
			<?php if ( $heading ) : ?>
				<h2 class="section-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
			<?php endif; ?>
			<?php if ( $show_body && $body ) : ?>
				<p class="section-body"><?php echo wp_kses( $body, $allowed_body ); ?></p>
			<?php endif; ?>
		</div>

		<!-- Right: 3-column icon grid -->
		<div class="spi-grid">
			<?php foreach ( $columns as $col ) :
				$icon      = $col['icon']     ?? 'fields';
				$col_head  = $col['heading']  ?? '';
				$col_body  = $col['body']     ?? '';
				$cta_label = $col['ctaLabel'] ?? '';
				$cta_url   = $col['ctaUrl']   ?? '#';

				if ( '' === $col_head && '' === $col_body ) {
					continue;
				}

				if ( ! in_array( $icon, $allowed_icons, true ) ) {
					$icon = 'fields';
				}
			?>
			<div class="spi-item">
				<?php if ( $show_icons ) : ?>
				<div class="spi-icon" aria-hidden="true">
					<img
						src="<?php echo esc_url( CROPX_THEME_URI . 'assets/icons/' . $icon . '.svg' ); ?>"
						alt=""
						width="24"
						height="24"
					>
				</div>
				<?php endif; ?>

				<?php if ( $col_head ) : ?>
					<h3 class="spi-item-heading"><?php echo wp_kses( $col_head, $allowed_inline ); ?></h3>
				<?php endif; ?>

				<?php if ( $col_body ) : ?>
					<p class="spi-body"><?php echo wp_kses( $col_body, $allowed_body ); ?></p>
				<?php endif; ?>

				<?php if ( $cta_label ) : ?>
					<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="spi-cta">
						<?php echo esc_html( $cta_label ); ?>
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
							<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</a>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
