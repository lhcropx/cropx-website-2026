<?php
/**
 * Segments block — front-end render.
 *
 * Desktop: three photo panels side by side, each with a square tag at the
 * top-left, body copy + CTA at the bottom, and a 6px accent strip at the
 * very bottom. Border-radius: 0 40px 0 0 (top-right rounded only).
 *
 * Mobile: WAI-ARIA tab bar shows one panel at a time (view.js handles
 * the switching logic — no change from previous version).
 */

$uid              = wp_unique_id( 'seg-' );
$show_eyebrow     = (bool) ( $attributes['showEyebrow']     ?? true );
$show_header          = (bool) ( $attributes['showSectionHeader']  ?? true );
$show_section_eyebrow = (bool) ( $attributes['showSectionEyebrow'] ?? true );
$section_eyebrow  = $attributes['sectionEyebrow'] ?? 'Who we serve';
$section_heading  = $attributes['sectionHeading'] ?? 'Solutions built for every part of agriculture';
$order            = $attributes['segmentOrder'] ?? array( 'enterprise', 'service', 'onFarm' );

// Per-type data lookup.
$type_map = array(
	'enterprise' => array(
		'css'       => 'enterprise',
		'eyebrow'   => $attributes['enterpriseEyebrow'] ?? 'Enterprise',
		'heading'   => $attributes['enterpriseHeading'] ?? 'Enterprise Solutions',
		'body'      => $attributes['enterpriseBody']    ?? '',
		'url'       => $attributes['enterpriseUrl']     ?? '#',
		'photo_url' => $attributes['enterprisePhotoUrl'] ?? '',
	),
	'service' => array(
		'css'       => 'service',
		'eyebrow'   => $attributes['serviceEyebrow'] ?? 'Service Providers',
		'heading'   => $attributes['serviceHeading'] ?? 'Service Providers',
		'body'      => $attributes['serviceBody']    ?? '',
		'url'       => $attributes['serviceUrl']     ?? '#',
		'photo_url' => $attributes['servicePhotoUrl'] ?? '',
	),
	'onFarm' => array(
		'css'       => 'on-farm',
		'eyebrow'   => $attributes['onFarmEyebrow'] ?? 'On-Farm',
		'heading'   => $attributes['onFarmHeading'] ?? 'On-Farm Solutions',
		'body'      => $attributes['onFarmBody']    ?? '',
		'url'       => $attributes['onFarmUrl']     ?? '#',
		'photo_url' => $attributes['onFarmPhotoUrl'] ?? '',
	),
);

// Build ordered array, skipping unknown keys gracefully.
$segments = array();
$i = 0;
foreach ( $order as $key ) {
	if ( ! isset( $type_map[ $key ] ) ) { continue; }
	$data       = $type_map[ $key ];
	$segments[] = array(
		'tab_id'    => $uid . '-tab-' . ( $i + 1 ),
		'panel_id'  => $uid . '-panel-' . ( $i + 1 ),
		'css'       => $data['css'],
		'eyebrow'   => $data['eyebrow'],
		'heading'   => $data['heading'],
		'body'      => $data['body'],
		'url'       => $data['url'],
		'photo_url' => $data['photo_url'],
		'first'     => $i === 0,
	);
	$i++;
}

$svg_arrow = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12H19M19 12L13 6M19 12L13 18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
	<?php
	// PageSpeed fix (Aug 2026): drift-pattern.svg was a relative-path url() in
	// style.css, which webpack base64-embeds (89KB SVG, past the ~10KB inlining
	// cutoff — CLAUDE.md gotcha #7), bloating this block's compiled CSS with a
	// duplicate copy of the same image. Segments is always Deep Blue, so this
	// runs unconditionally. Real, cacheable URL via CSS custom property instead.
	$seg_wrapper_attrs = get_block_wrapper_attributes( array(
		'class' => 'seg-section',
		'style' => '--seg-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');',
	) );
	?>
<section <?php echo $seg_wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="seg-inner">

		<?php if ( $show_header && ( $section_eyebrow || $section_heading ) ) : ?>
			<div class="seg-section-header">
				<?php if ( $show_section_eyebrow && $section_eyebrow ) : ?>
					<p class="seg-section-eyebrow"><?php echo esc_html( $section_eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( $section_heading ) : ?>
					<h2 class="seg-section-heading"><?php echo esc_html( $section_heading ); ?></h2>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php /* Mobile tab bar — hidden on desktop via CSS, shown at ≤900px */ ?>
		<div class="seg-tab-bar" role="tablist">
			<?php foreach ( $segments as $seg ) : ?>
				<button
					id="<?php echo esc_attr( $seg['tab_id'] ); ?>"
					class="seg-tab-btn seg-tab-btn--<?php echo esc_attr( $seg['css'] ); ?><?php echo $seg['first'] ? ' active' : ''; ?>"
					role="tab"
					aria-selected="<?php echo $seg['first'] ? 'true' : 'false'; ?>"
					aria-controls="<?php echo esc_attr( $seg['panel_id'] ); ?>"
					tabindex="<?php echo $seg['first'] ? '0' : '-1'; ?>"
					type="button"
				>
					<?php echo esc_html( $seg['heading'] ); ?>
				</button>
			<?php endforeach; ?>
		</div>

		<div class="seg-grid">
			<?php foreach ( $segments as $seg ) : ?>
				<?php
				// Inline background-image style — only set when a photo URL exists.
				$photo_style = '';
				if ( ! empty( $seg['photo_url'] ) ) {
					$photo_style = ' style="background-image: url(' . esc_url( $seg['photo_url'] ) . ');"';
				}
				?>
				<a
					href="<?php echo esc_url( cropx_url( $seg['url'] ) ); ?>"
					id="<?php echo esc_attr( $seg['panel_id'] ); ?>"
					class="seg-panel seg-panel--<?php echo esc_attr( $seg['css'] ); ?><?php echo $seg['first'] ? ' active' : ''; ?>"
					role="tabpanel"
					aria-labelledby="<?php echo esc_attr( $seg['tab_id'] ); ?>"
				>
					<?php /* Photo layer — absolutely positioned behind everything */ ?>
					<div class="seg-panel-photo"<?php echo $photo_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>></div>

					<?php /* Dark gradient overlay for text legibility */ ?>
					<div class="seg-panel-overlay" aria-hidden="true"></div>

					<?php /* Segment tag — top-left corner */ ?>
					<?php if ( $show_eyebrow && $seg['eyebrow'] ) : ?>
						<div class="seg-panel-tag-wrap">
							<span class="seg-panel-tag"><?php echo esc_html( $seg['eyebrow'] ); ?></span>
						</div>
					<?php endif; ?>

					<?php /* Content — name, body, CTA — sits above the strip */ ?>
					<div class="seg-panel-body">
						<h3 class="seg-panel-name">
							<?php echo esc_html( $seg['heading'] ); ?>
						</h3>

						<?php if ( $seg['body'] ) : ?>
							<p class="seg-panel-desc"><?php echo wp_kses_post( $seg['body'] ); ?></p>
						<?php endif; ?>

						<span class="seg-panel-cta" aria-hidden="true">
							<?php esc_html_e( 'Learn more', 'cropx' ); ?>
							<?php echo $svg_arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
					</div>

					<?php /* Accent-coloured bottom strip */ ?>
					<div class="seg-panel-strip" aria-hidden="true"></div>
				</a>
			<?php endforeach; ?>
		</div>

	</div>
</section>
