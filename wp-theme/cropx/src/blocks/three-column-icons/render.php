<?php
/**
 * Three-column icons block — front-end render.
 *
 * Variants emerge from attribute combinations:
 *   backgroundVariant=white + eyebrow/heading set  → white with header
 *   backgroundVariant=white + eyebrow/heading empty → white, no header
 *   backgroundVariant=blue  + eyebrow/heading set  → Deep Blue with header
 *   backgroundVariant=blue  + eyebrow/heading empty → Deep Blue, no header
 *
 * Icon boxes on white sections are tinted by segmentAccent via .tci-segment-*
 * class. On Deep Blue sections the box is always white.
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

$col1_icon      = $attributes['col1Icon']      ?? 'fields';
$col1_heading   = $attributes['col1Heading']   ?? '';
$col1_body      = $attributes['col1Body']      ?? '';
$col1_cta_label = $attributes['col1CtaLabel']  ?? '';
$col1_cta_url   = $attributes['col1CtaUrl']    ?? '#';

$col2_icon      = $attributes['col2Icon']      ?? 'sensor-cloud';
$col2_heading   = $attributes['col2Heading']   ?? '';
$col2_body      = $attributes['col2Body']      ?? '';
$col2_cta_label = $attributes['col2CtaLabel']  ?? '';
$col2_cta_url   = $attributes['col2CtaUrl']    ?? '#';

$col3_icon      = $attributes['col3Icon']      ?? 'antenna';
$col3_heading   = $attributes['col3Heading']   ?? '';
$col3_body      = $attributes['col3Body']      ?? '';
$col3_cta_label = $attributes['col3CtaLabel']  ?? '';
$col3_cta_url   = $attributes['col3CtaUrl']    ?? '#';

// Validate enums.
if ( ! in_array( $bg_variant, array( 'white', 'blue' ), true ) ) {
	$bg_variant = 'white';
}
if ( ! in_array( $segment_accent, array( 'general', 'enterprise', 'service-provider', 'on-farm' ), true ) ) {
	$segment_accent = 'general';
}
$allowed_icons = array( 'alarm-clock', 'antenna', 'corn', 'field-sun', 'fields', 'language', 'nutrition', 'sensor-cloud', 'speed', 'valve-irrigation' );
foreach ( array( 'col1_icon', 'col2_icon', 'col3_icon' ) as $var ) {
	if ( ! in_array( $$var, $allowed_icons, true ) ) {
		$$var = 'fields';
	}
}

$section_class = 'tci-section tci-section--' . $bg_variant;
if ( 'white' === $bg_variant && 'general' !== $segment_accent ) {
	$section_class .= ' tci-segment-' . $segment_accent;
}

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => $section_class,
) );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
$allowed_body = array_merge( $allowed_inline, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

$columns = array(
	array( 'icon' => $col1_icon, 'heading' => $col1_heading, 'body' => $col1_body, 'cta_label' => $col1_cta_label, 'cta_url' => $col1_cta_url ),
	array( 'icon' => $col2_icon, 'heading' => $col2_heading, 'body' => $col2_body, 'cta_label' => $col2_cta_label, 'cta_url' => $col2_cta_url ),
	array( 'icon' => $col3_icon, 'heading' => $col3_heading, 'body' => $col3_body, 'cta_label' => $col3_cta_label, 'cta_url' => $col3_cta_url ),
);

$has_header = ( $show_eyebrow && $eyebrow ) || ( $show_heading && $heading );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="tci-inner">

		<?php if ( $has_header ) : ?>
		<div class="tci-header">
			<?php if ( $show_eyebrow && $eyebrow ) : ?>
				<span class="tci-eyebrow" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></span>
			<?php endif; ?>
			<?php if ( $show_heading && $heading ) : ?>
				<h2 class="tci-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<div class="tci-grid">
			<?php foreach ( $columns as $col ) : ?>
			<div class="tci-item">
				<?php if ( $show_icons ) : ?>
				<div class="tci-icon" aria-hidden="true">
					<img
						src="<?php echo esc_url( CROPX_THEME_URI . 'assets/icons/' . $col['icon'] . '.svg' ); ?>"
						alt=""
						width="24"
						height="24"
					>
				</div>
				<?php endif; ?>

				<?php if ( $col['heading'] ) : ?>
					<h3 class="tci-item-heading"><?php echo wp_kses( $col['heading'], $allowed_inline ); ?></h3>
				<?php endif; ?>

				<?php if ( $col['body'] ) : ?>
					<p class="tci-body"><?php echo wp_kses( $col['body'], $allowed_body ); ?></p>
				<?php endif; ?>

				<?php if ( $col['cta_label'] ) : ?>
					<a href="<?php echo esc_url( $col['cta_url'] ); ?>" class="tci-cta">
						<?php echo esc_html( $col['cta_label'] ); ?>
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
