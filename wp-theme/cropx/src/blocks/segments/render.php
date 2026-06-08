<?php
$uid          = wp_unique_id( 'seg-' );
$show_eyebrow = (bool) ( $attributes['showEyebrow'] ?? true );

$segments = [
	[
		'tab_id'   => $uid . '-tab-1',
		'panel_id' => $uid . '-panel-1',
		'eyebrow'  => $attributes['enterpriseEyebrow'] ?? 'CropX',
		'heading'  => $attributes['enterpriseHeading']  ?? 'Enterprise Solutions',
		'url'      => $attributes['enterpriseUrl']       ?? '#',
		'body'     => $attributes['enterpriseBody']      ?? '',
	],
	[
		'tab_id'   => $uid . '-tab-2',
		'panel_id' => $uid . '-panel-2',
		'eyebrow'  => $attributes['serviceEyebrow'] ?? 'CropX for',
		'heading'  => $attributes['serviceHeading']  ?? 'Service Providers',
		'url'      => $attributes['serviceUrl']       ?? '#',
		'body'     => $attributes['serviceBody']      ?? '',
	],
	[
		'tab_id'   => $uid . '-tab-3',
		'panel_id' => $uid . '-panel-3',
		'eyebrow'  => $attributes['onFarmEyebrow'] ?? 'CropX',
		'heading'  => $attributes['onFarmHeading']  ?? 'On-Farm Solutions',
		'url'      => $attributes['onFarmUrl']       ?? '#',
		'body'     => $attributes['onFarmBody']      ?? '',
	],
];

$svg_arrow = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12H19M19 12L13 6M19 12L13 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<section <?php echo get_block_wrapper_attributes( [ 'class' => 'seg-section' ] ); ?>>
	<div class="seg-inner">

		<div class="seg-tab-bar" role="tablist">
			<?php foreach ( $segments as $i => $seg ) : ?>
				<button
					id="<?php echo esc_attr( $seg['tab_id'] ); ?>"
					class="seg-tab-btn<?php echo $i === 0 ? ' active' : ''; ?>"
					role="tab"
					aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
					aria-controls="<?php echo esc_attr( $seg['panel_id'] ); ?>"
					tabindex="<?php echo $i === 0 ? '0' : '-1'; ?>"
					type="button"
				>
					<?php echo esc_html( $seg['heading'] ); ?>
				</button>
			<?php endforeach; ?>
		</div>

		<div class="seg-grid">
			<?php foreach ( $segments as $i => $seg ) : ?>
				<div
					id="<?php echo esc_attr( $seg['panel_id'] ); ?>"
					class="seg-col<?php echo $i === 0 ? ' active' : ''; ?>"
					role="tabpanel"
					aria-labelledby="<?php echo esc_attr( $seg['tab_id'] ); ?>"
				>
					<?php if ( $show_eyebrow && $seg['eyebrow'] ) : ?>
						<p class="seg-eyebrow"><?php echo esc_html( $seg['eyebrow'] ); ?></p>
					<?php endif; ?>

					<div class="seg-name-wrap">
						<h2 class="seg-name">
							<a href="<?php echo esc_url( cropx_url( $seg['url'] ) ); ?>">
								<span class="seg-underline">
									<?php echo esc_html( $seg['heading'] ); ?>
									<span class="seg-arrow" aria-hidden="true">
										<?php echo $svg_arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</span>
								</span>
							</a>
						</h2>
					</div>

					<?php if ( $seg['body'] ) : ?>
						<p class="seg-body"><?php echo wp_kses_post( $seg['body'] ); ?></p>
					<?php endif; ?>

					<a href="<?php echo esc_url( cropx_url( $seg['url'] ) ); ?>" class="seg-learn-more">
						<?php esc_html_e( 'Learn more', 'cropx' ); ?>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12H19M19 12L13 6M19 12L13 18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</a>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
