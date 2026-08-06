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

$is_dark = $bg_style === 'dark';

$bg_class_map  = array( 'dark' => 'mcta--dark', 'taupe' => 'mcta--taupe', 'white' => 'mcta--white' );
$section_class = 'cropx-mid-cta ' . ( $bg_class_map[ $bg_style ] ?? 'mcta--taupe' );

// Button classes
$primary_class   = $is_dark ? 'mcta-btn mcta-btn--white' : 'mcta-btn mcta-btn--primary';
$secondary_class = 'mcta-btn-secondary';

// Arrow SVG for secondary CTA (text link with arrow)
$arrow_svg = '<svg width="18" height="18" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

// Inject drift pattern URL for dark variant (can't use PHP constants in webpack CSS)
if ( $is_dark ) {
	$drift_url = esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' );
	$block_id  = 'mcta-' . substr( md5( serialize( $attributes ) ), 0, 8 );
	echo '<style>.cropx-mid-cta[data-drift="' . esc_attr( $block_id ) . '"]::before{background-image:url(' . $drift_url . ')}</style>';
}

$_mcta_attrs = [
	'class'      => esc_attr( $section_class ),
	'data-drift' => isset( $block_id ) ? esc_attr( $block_id ) : '',
];
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
			<a href="<?php echo esc_url( $primary_url ); ?>" class="<?php echo esc_attr( $primary_class ); ?>">
				<?php echo esc_html( $primary_label ); ?>
			</a>

			<?php if ( $show_secondary && $secondary_label ) : ?>
				<a href="<?php echo esc_url( $secondary_url ); ?>" class="<?php echo esc_attr( $secondary_class ); ?>">
					<?php echo esc_html( $secondary_label ); ?>
					<?php echo $arrow_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			<?php endif; ?>
		</div>
		<?php endif; ?>

	</div>
</section>
