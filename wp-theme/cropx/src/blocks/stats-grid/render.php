<?php
/**
 * Stats grid block — front-end render.
 *
 * --accent on the section drives the 6px left border on each stat card.
 * Default is --cropx-blue; segment classes override to gold/terra/new-leaf.
 * --accent is never used as a text color (brand rule).
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

$stat1_number      = $attributes['stat1Number']      ?? '';
$stat1_descriptor  = $attributes['stat1Descriptor']  ?? '';
$stat2_number      = $attributes['stat2Number']      ?? '';
$stat2_descriptor  = $attributes['stat2Descriptor']  ?? '';
$stat3_number      = $attributes['stat3Number']      ?? '';
$stat3_descriptor  = $attributes['stat3Descriptor']  ?? '';
$stat4_number      = $attributes['stat4Number']      ?? '';
$stat4_descriptor  = $attributes['stat4Descriptor']  ?? '';

if ( ! in_array( $segment_accent, array( 'general', 'enterprise', 'service-provider', 'on-farm' ), true ) ) {
	$segment_accent = 'general';
}

$section_class = 'sg-section';
if ( 'general' !== $segment_accent ) {
	$section_class .= ' sg-segment-' . $segment_accent;
}

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => $section_class ) );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);
$allowed_body = array_merge( $allowed_inline, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

$stats = array(
	array( 'number' => $stat1_number, 'descriptor' => $stat1_descriptor ),
	array( 'number' => $stat2_number, 'descriptor' => $stat2_descriptor ),
	array( 'number' => $stat3_number, 'descriptor' => $stat3_descriptor ),
	array( 'number' => $stat4_number, 'descriptor' => $stat4_descriptor ),
);
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="sg-inner">

		<div class="sg-content">
			<?php if ( $show_eyebrow && $eyebrow ) : ?>
				<span class="sg-eyebrow" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></span>
			<?php endif; ?>
			<?php if ( $heading ) : ?>
				<h2 class="sg-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
			<?php endif; ?>
			<?php if ( $body ) : ?>
				<p class="sg-body"><?php echo wp_kses( $body, $allowed_body ); ?></p>
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
			<div class="sg-stat-card">
				<?php if ( $stat['number'] ) : ?>
					<span class="sg-stat-number"><?php echo esc_html( wp_strip_all_tags( $stat['number'] ) ); ?></span>
				<?php endif; ?>
				<?php if ( $stat['descriptor'] ) : ?>
					<p class="sg-stat-descriptor"><?php echo wp_kses( $stat['descriptor'], $allowed_inline ); ?></p>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
