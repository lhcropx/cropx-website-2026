<?php
/**
 * Hero block — front-end render.
 *
 * Receives:
 *   $attributes — block attributes (matches block.json schema)
 *   $content    — inner block content (unused; we have no InnerBlocks)
 *   $block      — the block instance object
 *
 * Markup matches the static blocks/hero.html structure: three stacked
 * background layers (image → gradient overlay → drift pattern) sit behind
 * the content. This gives us the cinematic dark-left-to-clear-right hero
 * look from the original design instead of a flat tint.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$eyebrow      = $attributes['eyebrow']      ?? '';
$heading      = $attributes['heading']      ?? '';
$subheading   = $attributes['subheading']   ?? '';
$cta_label    = $attributes['ctaLabel']     ?? '';
$cta_url      = $attributes['ctaUrl']       ?? '';
$bg_image_id  = (int) ( $attributes['backgroundImageId'] ?? 0 );
$bg_image_url = $attributes['backgroundImageUrl']        ?? '';
$bg_image_alt = $attributes['backgroundImageAlt']        ?? '';
$segment      = $attributes['segmentAccent']             ?? 'general';
$show_eyebrow = (bool)($attributes['showEyebrow'] ?? true);
$show_cta     = (bool)($attributes['showCta']     ?? true);

$allowed_segments = array( 'general', 'enterprise', 'service-provider', 'on-farm' );
if ( ! in_array( $segment, $allowed_segments, true ) ) {
	$segment = 'general';
}

// Resolve the background image URL — prefer the WP attachment ID so we
// can let WordPress pick the right size; fall back to a literal URL.
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
	? sprintf( 'background-image: url(%s);', esc_url( $bg_url ) )
	: '';

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => 'hero-section hero-segment-' . esc_attr( $segment ),
) );

// Tags allowed inside the heading and subheading (RichText permits
// these formats from the editor — keep wp_kses scoped to match).
$heading_allowed_tags = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
$subheading_allowed_tags = array_merge( $heading_allowed_tags, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="hero-bg" <?php echo $bg_style ? 'style="' . esc_attr( $bg_style ) . '"' : ''; ?>></div>
	<div class="hero-overlay"></div>
	<div class="hero-pattern"></div>

	<div class="hero-inner">
		<?php if ( $show_eyebrow && $eyebrow ) : ?>
			<p class="hero-eyebrow"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></p>
		<?php endif; ?>
		<?php if ( $heading ) : ?>
			<h1 class="hero-heading"><?php echo wp_kses( $heading, $heading_allowed_tags ); ?></h1>
		<?php endif; ?>
		<?php if ( $subheading ) : ?>
			<p class="hero-subheading"><?php echo wp_kses( $subheading, $subheading_allowed_tags ); ?></p>
		<?php endif; ?>
		<?php if ( $show_cta && $cta_label && $cta_url ) : ?>
			<a class="hero-cta" href="<?php echo esc_url( $cta_url ); ?>">
				<?php echo esc_html( $cta_label ); ?>
			</a>
		<?php endif; ?>
	</div>
</section>
