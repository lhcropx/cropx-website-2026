<?php
/**
 * Pre-footer CTA block — front-end render.
 *
 * Layers (bottom to top):
 *   .pf-bg      — photo background (inline style from media library)
 *   .pf-overlay — dark radial-gradient overlay (CSS only)
 *   .pf-inner   — eyebrow, heading, subtext, CTA buttons
 *
 * Variant is driven by attribute presence:
 *   subtext + 1 CTA  → Option A
 *   subtext + 2 CTAs → Option B
 *   no subtext + 1 CTA → Option C
 *
 * The 25px top accent stripe and heading <em> underline both use
 * --hero-em-color, set via .pf-segment-* class on the section.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$eyebrow         = $attributes['eyebrow']          ?? '';
$heading         = $attributes['heading']           ?? '';
$subtext         = $attributes['subtext']           ?? '';
$primary_label   = $attributes['primaryLabel']      ?? '';
$primary_url     = $attributes['primaryUrl']        ?? '#';
$secondary_label = $attributes['secondaryLabel']    ?? '';
$secondary_url   = $attributes['secondaryUrl']      ?? '#';
$bg_image_id     = (int) ( $attributes['backgroundImageId']  ?? 0 );
$bg_image_url    = $attributes['backgroundImageUrl']          ?? '';
$segment         = $attributes['segmentAccent']               ?? 'general';
$eyebrow_color   = $attributes['eyebrowColor']                ?? 'cropx-blue';

$allowed_segments = array( 'general', 'enterprise', 'service-provider', 'on-farm' );
if ( ! in_array( $segment, $allowed_segments, true ) ) {
	$segment = 'general';
}

// Resolve background image URL from WP attachment or literal URL.
$bg_url = '';
if ( $bg_image_id ) {
	$src = wp_get_attachment_image_src( $bg_image_id, 'full' );
	if ( $src ) {
		$bg_url = $src[0];
	}
} elseif ( $bg_image_url ) {
	$bg_url = $bg_image_url;
}

$bg_style = $bg_url
	? 'background-image: url(' . esc_url( $bg_url ) . ');'
	: '';

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => 'pf pf-segment-' . esc_attr( $segment ),
) );

$heading_allowed_tags = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
$subtext_allowed_tags = array_merge( $heading_allowed_tags, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="pf-bg"<?php echo $bg_style ? ' style="' . esc_attr( $bg_style ) . '"' : ''; ?>></div>
	<div class="pf-overlay"></div>

	<div class="pf-inner">
		<?php if ( $eyebrow ) : ?>
			<span class="pf-eyebrow" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></span>
		<?php endif; ?>

		<?php if ( $heading ) : ?>
			<h2 class="pf-heading"><?php echo wp_kses( $heading, $heading_allowed_tags ); ?></h2>
		<?php endif; ?>

		<?php if ( $subtext ) : ?>
			<p class="pf-sub"><?php echo wp_kses( $subtext, $subtext_allowed_tags ); ?></p>
		<?php endif; ?>

		<?php if ( $primary_label ) : ?>
			<div class="pf-actions">
				<a href="<?php echo esc_url( $primary_url ); ?>" class="btn-primary">
					<?php echo esc_html( $primary_label ); ?>
				</a>
				<?php if ( $secondary_label ) : ?>
					<a href="<?php echo esc_url( $secondary_url ); ?>" class="btn-ghost">
						<?php echo esc_html( $secondary_label ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
