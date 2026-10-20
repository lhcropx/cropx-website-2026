<?php
/**
 * Pricing Tiers — front-end render.
 *
 * Three-column plan comparison: name, price line, feature checklist (green
 * checkmarks), and a Contact button per tier. Any tier can be flagged
 * `featured` in the editor sidebar — that tier's card renders with
 * .pt-card--featured, which style.css uses to visually elevate it (raised
 * position, larger shadow) above its siblings, the same "one card pops"
 * pattern as the FarmLook pricing table this block was modeled on. Featured
 * is a per-card toggle, not hardcoded to the middle position, so an editor
 * can highlight whichever plan makes sense (or none, or more than one).
 *
 * Mirrors edit.js's DOM structure exactly, minus the editor-only "+ Add
 * feature" / "×" remove controls on the feature list (interactive editing
 * affordances never ship to the front end — same pattern as every other
 * repeatable-list block in this theme, e.g. resource-downloads' subsection
 * editor).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$eyebrow       = $attributes['eyebrow']      ?? '';
$heading       = $attributes['heading']      ?? '';
$show_eyebrow  = (bool) ( $attributes['showEyebrow'] ?? true );
$show_heading  = (bool) ( $attributes['showHeading'] ?? true );
$eyebrow_color = $attributes['eyebrowColor'] ?? 'cropx-blue';
$bg_color      = $attributes['bgColor']      ?? 'taupe';
$tiers         = (array) ( $attributes['tiers'] ?? [] );

if ( ! in_array( $bg_color, [ 'taupe', 'deep-blue' ], true ) ) {
	$bg_color = 'taupe';
}

$is_dark = 'deep-blue' === $bg_color;

$eyebrow_css_map = [
	'cropx-blue' => 'var(--cropx-blue)',
	'deep-blue'  => 'var(--deep-blue)',
	'white'      => '#fff',
];
$eyebrow_css = $eyebrow_css_map[ $eyebrow_color ] ?? 'var(--cropx-blue)';

$bg_class_map  = [ 'white' => 'pt-section--white', 'taupe' => 'pt-section--taupe', 'deep-blue' => 'pt-section--deep-blue' ];
$section_class = 'pt-section reveal-group ' . ( $bg_class_map[ $bg_color ] ?? 'pt-section--white' );

// ── Deep-blue topographic drift pattern ──────────────────────────────────
// Same per-instance inline <style> technique as mid-page-cta / video /
// resource-downloads — keyed on a unique data attribute so multiple
// instances on one page don't collide.
$block_id = uniqid( 'pt-' );
if ( $is_dark ) {
	$drift_url = esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' );
	echo '<style>.pt-section[data-pt-drift="' . esc_attr( $block_id ) . '"]::before{background-image:url(' . $drift_url . ')}</style>';
}

$wrapper_extra = $is_dark ? [ 'data-pt-drift' => $block_id ] : [];
$wrapper_attrs = get_block_wrapper_attributes( array_merge( [ 'class' => $section_class ], $wrapper_extra ) );

$check_icon = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true" class="pt-check-icon">'
	. '<path d="M3 8.5L6 11.5L13 4.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>'
	. '</svg>';
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="pt-inner section-inner">

		<?php if ( ( $eyebrow && $show_eyebrow ) || ( $heading && $show_heading ) ) : ?>
			<div class="section-header">
				<?php if ( $eyebrow && $show_eyebrow ) : ?>
					<span class="section-eyebrow reveal-up" style="--reveal-delay:0.05s;color:<?php echo esc_attr( $eyebrow_css ); ?>">
						<?php echo esc_html( $eyebrow ); ?>
					</span>
				<?php endif; ?>
				<?php if ( $heading && $show_heading ) : ?>
					<h2 class="section-heading reveal-up<?php echo $is_dark ? ' section-heading--white' : ''; ?>" style="--reveal-delay:0.15s">
						<?php echo wp_kses_post( $heading ); ?>
					</h2>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="pt-grid">
			<?php foreach ( $tiers as $idx => $tier ) :
				$name       = $tier['name']       ?? '';
				$price      = $tier['price']      ?? '';
				$features   = (array) ( $tier['features'] ?? [] );
				$cta_label  = $tier['ctaLabel']   ?? '';
				$cta_url    = $tier['ctaUrl']     ?? '';
				$featured   = (bool) ( $tier['featured'] ?? false );
				$badge_label = $tier['badgeLabel'] ?? '';

				// Staggered per-card reveal delay (scroll-reveal.css), same
				// rationale/formula as the other scroll-reveal blocks.
				$pt_reveal_delay = 0.3 + ( min( $idx, 8 ) * 0.06 );
			?>
				<div class="pt-card reveal-item<?php echo $featured ? ' pt-card--featured' : ''; ?>" style="--reveal-delay:<?php echo esc_attr( $pt_reveal_delay ); ?>s">

					<?php if ( $featured && $badge_label ) : ?>
						<span class="pt-badge"><?php echo esc_html( $badge_label ); ?></span>
					<?php endif; ?>

					<?php if ( $name ) : ?>
						<h3 class="pt-name"><?php echo wp_kses_post( $name ); ?></h3>
					<?php endif; ?>

					<?php if ( $price ) : ?>
						<p class="pt-price"><?php echo wp_kses_post( $price ); ?></p>
					<?php endif; ?>

					<hr class="pt-divider">

					<?php if ( ! empty( $features ) ) : ?>
						<ul class="pt-features">
							<?php foreach ( $features as $feature ) :
								if ( '' === trim( (string) $feature ) ) {
									continue;
								}
							?>
								<li class="pt-feature">
									<?php echo $check_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<span class="pt-feature-text"><?php echo wp_kses_post( $feature ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<a href="<?php echo esc_url( cropx_url( $cta_url ?: '/contact-us/' ) ); ?>" class="pt-cta btn-primary">
						<?php echo esc_html( $cta_label ?: __( 'Contact', 'cropx' ) ); ?>
					</a>

				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
