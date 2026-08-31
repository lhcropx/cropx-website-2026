<?php
/**
 * Six-column icons block — front-end render.
 *
 * Identical feature set to three-column-icons but laid out in 6 columns.
 * Columns are stored in the `columns` array attribute. Each item has:
 *   icon, heading, body, ctaLabel, ctaUrl
 *
 * Variants:
 *   backgroundVariant=white + segment accent → tinted icon boxes
 *   backgroundVariant=blue                  → white icon boxes always
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$eyebrow        = $attributes['eyebrow']           ?? '';
$heading        = $attributes['heading']            ?? '';
$bg_variant     = $attributes['backgroundVariant']  ?? 'white';
$segment_accent = $attributes['segmentAccent']      ?? 'general';
$eyebrow_color  = $attributes['eyebrowColor']       ?? 'cropx-blue';
$show_eyebrow   = (bool) ( $attributes['showEyebrow'] ?? true );
$show_heading   = (bool) ( $attributes['showHeading'] ?? true );
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

$section_class = 'sci-section sci-section--' . $bg_variant;
if ( in_array( $bg_variant, array( 'white', 'taupe' ), true ) && 'general' !== $segment_accent ) {
	$section_class .= ' sci-segment-' . $segment_accent;
}

$_sci_attrs = array( 'class' => $section_class );
if ( in_array( $bg_variant, array( 'taupe', 'white' ), true ) ) {
	$_sci_attrs['data-section-bg'] = $bg_variant;
}

// Deep-blue topo overlay: inject the pattern's real asset URL via a CSS
// custom property instead of letting the CSS reference it by relative path.
// See two-column-video/render.php for the full rationale (relative url()
// gets base64-inlined by webpack, ballooning style-index.css to a size that
// silently fails to overwrite during WP File Manager zip deploys). edit.js
// sets the same property for the editor preview.
if ( 'blue' === $bg_variant ) {
	$_sci_attrs['style'] = '--sci-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');';
}

$wrapper_attrs = get_block_wrapper_attributes( $_sci_attrs );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
$allowed_body = array_merge( $allowed_inline, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

$has_header = ( $show_eyebrow && $eyebrow ) || ( $show_heading && $heading );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="sci-inner">

		<?php if ( $has_header ) : ?>
		<div class="sci-header">
			<?php if ( $show_eyebrow && $eyebrow ) : ?>
				<span class="section-eyebrow" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></span>
			<?php endif; ?>
			<?php if ( $show_heading && $heading ) : ?>
				<h2 class="section-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<div class="sci-grid">
			<?php foreach ( $columns as $col ) :
				$icon      = $col['icon']     ?? 'fields';
				$col_head  = $col['heading']  ?? '';
				$col_body  = $col['body']     ?? '';
				$cta_label = $col['ctaLabel'] ?? '';
				$cta_url   = $col['ctaUrl']   ?? '#';

				// Skip items that have no heading and no body.
				if ( '' === $col_head && '' === $col_body ) {
					continue;
				}

				// Sanitize icon slug against allowlist.
				$icon = cropx_resolve_icon_slug( $icon );
				if ( ! in_array( $icon, $allowed_icons, true ) ) {
					$icon = 'fields';
				}
			?>
			<div class="sci-item">
				<?php if ( $show_icons ) : ?>
				<div class="sci-icon" aria-hidden="true">
					<img
						src="<?php echo esc_url( CROPX_THEME_URI . 'assets/icons/' . $icon . '.svg' ); ?>"
						alt=""
						width="24"
						height="24"
					>
				</div>
				<?php endif; ?>

				<?php if ( $col_head ) : ?>
					<h3 class="sci-item-heading"><?php echo wp_kses( $col_head, $allowed_inline ); ?></h3>
				<?php endif; ?>

				<?php if ( $col_body ) : ?>
					<p class="sci-body"><?php echo wp_kses( $col_body, $allowed_body ); ?></p>
				<?php endif; ?>

				<?php if ( $cta_label ) : ?>
					<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="sci-cta">
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
