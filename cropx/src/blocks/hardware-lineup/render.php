<?php
$eyebrow       = $attributes['eyebrow']      ?? '';
$items         = $attributes['items']        ?? [];
$eyebrow_color = esc_attr( $attributes['eyebrowColor'] ?? 'cropx-blue' );

$bg_color = $attributes['bgColor'] ?? 'white';
if ( ! in_array( $bg_color, array( 'taupe', 'white', 'deep-blue' ), true ) ) {
	$bg_color = 'white';
}

$svg_prev = '<svg viewBox="0 0 18 18" fill="none" aria-hidden="true"><path d="M11 4l-5 5 5 5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$svg_next = '<svg viewBox="0 0 18 18" fill="none" aria-hidden="true"><path d="M7 4l5 5-5 5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$svg_arrow = '<svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$wrapper_attrs = get_block_wrapper_attributes( [ 'class' => 'hwf-section hwf-section--bg-' . $bg_color, 'data-section-bg' => $bg_color ] );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<div class="hwf-header">
		<?php if ( $eyebrow ) : ?>
			<p class="section-eyebrow" style="color: var(--<?php echo $eyebrow_color; ?>)"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>
		<div class="hwc-arrows" aria-hidden="true">
			<button class="hwc-arrow hwc-prev" type="button" aria-label="<?php esc_attr_e( 'Previous hardware', 'cropx' ); ?>">
				<?php echo $svg_prev; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
			<button class="hwc-arrow hwc-next" type="button" aria-label="<?php esc_attr_e( 'Next hardware', 'cropx' ); ?>">
				<?php echo $svg_next; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>
	</div>

	<div class="hwc-marquee"
		aria-roledescription="carousel"
		aria-label="<?php echo esc_attr( $eyebrow ); ?>"
		tabindex="0"
	>
		<div class="hwc-track">
			<?php foreach ( $items as $item ) :
				$thumb_class = 'hwf-thumb' . ( ! empty( $item['thumbCentered'] ) ? ' hwf-thumb--centered' : '' );
				$img_h       = isset( $item['imageHeight'] ) ? intval( $item['imageHeight'] ) : 100;
				$pill_style  = '--hwf-img-h: ' . $img_h . '%;';
			?>
				<a
					class="hwf-pill"
					href="<?php echo esc_url( cropx_url( $item['url'] ?? '#' ) ); ?>"
					style="<?php echo esc_attr( $pill_style ); ?>"
				>
					<span class="hwf-text">
						<span class="hwf-name-row">
							<span class="hwf-name"><?php echo esc_html( $item['name'] ?? '' ); ?></span>
							<span class="hwf-arrow" aria-hidden="true"><?php echo $svg_arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						</span>
						<span class="hwf-desc"><?php echo esc_html( $item['description'] ?? '' ); ?></span>
					</span>
					<span class="<?php echo esc_attr( $thumb_class ); ?>">
						<?php if ( ! empty( $item['imageId'] ) ) :
							echo wp_get_attachment_image( $item['imageId'], 'full', false, [ 'alt' => '' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						endif; ?>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>

</section>
