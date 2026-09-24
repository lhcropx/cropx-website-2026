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
		'photo_id'  => (int) ( $attributes['enterprisePhotoId'] ?? 0 ),
	),
	'service' => array(
		'css'       => 'service',
		'eyebrow'   => $attributes['serviceEyebrow'] ?? 'Service Providers',
		'heading'   => $attributes['serviceHeading'] ?? 'Service Providers',
		'body'      => $attributes['serviceBody']    ?? '',
		'url'       => $attributes['serviceUrl']     ?? '#',
		'photo_url' => $attributes['servicePhotoUrl'] ?? '',
		'photo_id'  => (int) ( $attributes['servicePhotoId'] ?? 0 ),
	),
	'onFarm' => array(
		'css'       => 'on-farm',
		'eyebrow'   => $attributes['onFarmEyebrow'] ?? 'On-Farm',
		'heading'   => $attributes['onFarmHeading'] ?? 'On-Farm Solutions',
		'body'      => $attributes['onFarmBody']    ?? '',
		'url'       => $attributes['onFarmUrl']     ?? '#',
		'photo_url' => $attributes['onFarmPhotoUrl'] ?? '',
		'photo_id'  => (int) ( $attributes['onFarmPhotoId'] ?? 0 ),
	),
);

// Build ordered array, skipping unknown keys gracefully.
$segments = array();
$i = 0;
foreach ( $order as $key ) {
	if ( ! isset( $type_map[ $key ] ) ) { continue; }
	$data = $type_map[ $key ];

	// Re-resolve the photo URL fresh from the attachment ID on every page
	// load — the same pattern as logo-strip/render.php. $data['photo_url']
	// is just a snapshot from whenever the photo was uploaded; if the site's
	// address has changed since, that snapshot goes stale even though the
	// attachment itself is fine.
	$photo_url = $data['photo_url'];
	if ( $data['photo_id'] ) {
		$resolved = wp_get_attachment_url( $data['photo_id'] );
		if ( $resolved ) {
			$photo_url = $resolved;
		}
	}

	$segments[] = array(
		'tab_id'    => $uid . '-tab-' . ( $i + 1 ),
		'panel_id'  => $uid . '-panel-' . ( $i + 1 ),
		'css'       => $data['css'],
		'eyebrow'   => $data['eyebrow'],
		'heading'   => $data['heading'],
		'body'      => $data['body'],
		'url'       => $data['url'],
		'photo_url' => $photo_url,
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
		// reveal-group: scroll-reveal observed root (see src/shared/scrollReveal.js) —
		// view.js observes '.seg-section'.
		'class' => 'seg-section reveal-group',
		'style' => '--seg-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');',
	) );
	?>
<section <?php echo $seg_wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="seg-inner">

		<?php if ( $show_header && ( $section_eyebrow || $section_heading ) ) : ?>
			<div class="seg-section-header">
				<?php if ( $show_section_eyebrow && $section_eyebrow ) : ?>
					<p class="seg-section-eyebrow reveal-up" style="--reveal-delay:0.05s"><?php echo esc_html( $section_eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( $section_heading ) : ?>
					<h2 class="seg-section-heading reveal-up" style="--reveal-delay:0.15s"><?php echo esc_html( $section_heading ); ?></h2>
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
			<?php foreach ( $segments as $seg_index => $seg ) : ?>
				<?php
				// Lazy-load (Sep 2026): the photo URL is passed via data-bg instead of
				// an inline background-image style, so the browser doesn't fetch it
				// until view.js's IntersectionObserver sees the panel approaching the
				// viewport — see the "LAZY-LOAD BACKGROUND IMAGES" block in view.js.
				// The <noscript> fallback below covers the rare no-JS visitor: without
				// it, a photo would silently never load for that one case, only ever
				// falling back to the tinted placeholder color in style.css.
				$photo_data_bg = '';
				$photo_noscript = '';
				if ( ! empty( $seg['photo_url'] ) ) {
					$photo_data_bg  = ' data-bg="' . esc_url( $seg['photo_url'] ) . '"';
					$photo_noscript = '<noscript><style>#' . esc_attr( $seg['panel_id'] ) . ' .seg-panel-photo{background-image:url(' . esc_url( $seg['photo_url'] ) . ')}</style></noscript>';
				}
				// reveal-item: staggered scroll-reveal delay — see
				// src/shared/scrollReveal.js and the reveal-group on the wrapper above.
				$seg_reveal_delay = 0.3 + ( min( $seg_index, 8 ) * 0.06 );
				?>
				<a
					href="<?php echo esc_url( cropx_url( $seg['url'] ) ); ?>"
					id="<?php echo esc_attr( $seg['panel_id'] ); ?>"
					class="seg-panel seg-panel--<?php echo esc_attr( $seg['css'] ); ?><?php echo $seg['first'] ? ' active' : ''; ?> reveal-item"
					style="--reveal-delay:<?php echo esc_attr( $seg_reveal_delay ); ?>s"
					role="tabpanel"
					aria-labelledby="<?php echo esc_attr( $seg['tab_id'] ); ?>"
				>
					<?php /* Photo layer — absolutely positioned behind everything */ ?>
					<div class="seg-panel-photo"<?php echo $photo_data_bg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>></div>
					<?php echo $photo_noscript; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

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
