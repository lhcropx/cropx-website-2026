<?php
/**
 * Stats grid block — front-end render.
 *
 * --accent on the section drives the 6px left border on each stat card.
 * Default is --cropx-blue; segment classes override to gold/terra/new-leaf.
 * --accent is never used as a text color (brand rule).
 *
 * Up to 8 fixed stat slots (stat1..stat8) for backward compatibility — this
 * started as a hardcoded 2x2 (4-stat) grid, so slots 1-4 keep their original
 * attribute names/defaults and any page already using this block is
 * unaffected. statCount (2-8) controls how many of the 8 slots actually
 * render; slots beyond that stay in storage untouched, so shrinking and
 * re-growing the count doesn't lose content. .sg-cards is still a fixed
 * 2-column CSS Grid (see style.css) — it always wraps to a new row every 2
 * items, at any count from 2 to 8, no per-count layout logic needed.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$eyebrow        = $attributes['eyebrow']       ?? '';
$heading        = $attributes['heading']       ?? '';
$body           = $attributes['body']          ?? '';
$cta_label      = $attributes['ctaLabel']      ?? '';
$cta_url        = $attributes['ctaUrl']        ?? '#';
$cta_style      = $attributes['ctaStyle']      ?? 'link';
$segment_accent = $attributes['segmentAccent'] ?? 'general';
$eyebrow_color  = $attributes['eyebrowColor']  ?? 'cropx-blue';
$show_eyebrow   = (bool)($attributes['showEyebrow'] ?? true);
$show_cta       = (bool)($attributes['showCta']     ?? true);
$show_icons     = (bool)($attributes['showIcons']   ?? false);
$show_border    = (bool)($attributes['showBorder']  ?? true);
$bg_color       = $attributes['bgColor'] ?? 'taupe';
if ( ! in_array( $bg_color, array( 'taupe', 'white', 'deep-blue' ), true ) ) {
	$bg_color = 'taupe';
}

$allowed_font_sizes = array( '3rem', '4rem', '5rem', '6rem' );

// Same 118-icon allowlist used by the shared editor IconPicker
// (src/shared/IconPicker.js) — kept in sync manually since blocks each
// validate their own icon attributes server-side (same pattern icon-columns
// uses).
$allowed_icons = cropx_allowed_icon_slugs();

$stat_count = (int) ( $attributes['statCount'] ?? 4 );
if ( $stat_count < 2 ) { $stat_count = 2; }
if ( $stat_count > 8 ) { $stat_count = 8; }

if ( ! in_array( $segment_accent, array( 'general', 'enterprise', 'service-provider', 'on-farm' ), true ) ) {
	$segment_accent = 'general';
}

$section_class = 'sg-section';
if ( 'general' !== $segment_accent ) {
	$section_class .= ' sg-segment-' . $segment_accent;
}
$section_class .= ' sg-section--bg-' . $bg_color;

$sg_wrapper_extra_attrs = array( 'class' => $section_class, 'data-section-bg' => $bg_color );

// PageSpeed fix (Aug 2026): drift-pattern.svg was a relative-path url() in
// style.css, which webpack base64-embeds (89KB SVG, past the ~10KB inlining
// cutoff — CLAUDE.md gotcha #7). Real, cacheable URL via CSS custom property,
// injected only when the Deep Blue variant is active.
if ( 'deep-blue' === $bg_color ) {
	$sg_wrapper_extra_attrs['style'] = '--sg-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');';
}

$wrapper_attrs = get_block_wrapper_attributes( $sg_wrapper_extra_attrs );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
$allowed_body = array_merge( $allowed_inline, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

// Build all 8 fixed slots, then slice to statCount and drop any that are
// completely blank (an editor may leave a trailing slot empty rather than
// lowering statCount — this keeps that from rendering an empty bordered box).
$all_stats = array();
for ( $n = 1; $n <= 8; $n++ ) {
	$font_size = $attributes[ "stat{$n}FontSize" ] ?? '5rem';
	if ( ! in_array( $font_size, $allowed_font_sizes, true ) ) {
		$font_size = '5rem';
	}
	$icon = $attributes[ "stat{$n}Icon" ] ?? 'chart';
	$icon = cropx_resolve_icon_slug( $icon );
	if ( ! in_array( $icon, $allowed_icons, true ) ) {
		$icon = 'chart';
	}
	$all_stats[] = array(
		'number'     => $attributes[ "stat{$n}Number" ]     ?? '',
		'descriptor' => $attributes[ "stat{$n}Descriptor" ] ?? '',
		'font_max'   => (float) str_replace( 'rem', '', $font_size ), // unitless multiplier for --sg-num-max
		'icon'       => $icon,
	);
}

$stats = array_values(
	array_filter(
		array_slice( $all_stats, 0, $stat_count ),
		function ( $stat ) {
			return '' !== trim( $stat['number'] ) || '' !== trim( $stat['descriptor'] );
		}
	)
);
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="sg-inner">

		<div class="sg-content">
			<?php if ( $show_eyebrow && $eyebrow ) : ?>
				<span class="section-eyebrow" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></span>
			<?php endif; ?>
			<?php if ( $heading ) : ?>
				<h2 class="section-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
			<?php endif; ?>
			<?php if ( $body ) : ?>
				<div class="section-body"><?php echo wp_kses_post( $body ); ?></div>
			<?php endif; ?>
			<?php if ( $show_cta && $cta_label ) : ?>
				<?php if ( 'button' === $cta_style ) : ?>
					<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="sg-btn">
						<?php echo esc_html( $cta_label ); ?>
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="sg-cta">
						<?php echo esc_html( $cta_label ); ?>
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
							<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</a>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<div class="sg-cards">
			<?php foreach ( $stats as $stat ) : ?>
			<div class="sg-stat-card<?php echo $show_border ? '' : ' sg-stat-card--no-border'; ?>">
				<?php if ( $show_icons ) : ?>
					<div class="sg-stat-icon" aria-hidden="true">
						<img
							src="<?php echo esc_url( CROPX_THEME_URI . 'assets/icons/' . $stat['icon'] . '.svg' ); ?>"
							alt=""
							width="24"
							height="24"
						>
					</div>
				<?php endif; ?>
				<?php if ( $stat['number'] ) : ?>
					<span class="sg-stat-number" style="--sg-num-max: <?php echo esc_attr( $stat['font_max'] ); ?>"><?php echo esc_html( wp_strip_all_tags( $stat['number'] ) ); ?></span>
				<?php endif; ?>
				<?php if ( $stat['descriptor'] ) : ?>
					<p class="sg-stat-descriptor"><?php echo wp_kses( $stat['descriptor'], $allowed_inline ); ?></p>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
