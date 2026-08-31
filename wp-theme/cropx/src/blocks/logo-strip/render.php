<?php
/**
 * Logo strip block — front-end render.
 *
 * Full-width scrolling marquee of customer logos. The track renders each logo
 * twice so the CSS translateX(-50%) animation loops seamlessly.
 *
 * If the editor has uploaded custom logos via the sidebar, those are used.
 * Otherwise the block falls back to the built-in default set so that existing
 * instances (and new blocks before any logos are uploaded) still look correct.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$eyebrow       = $attributes['eyebrow']      ?? 'Trusted by leading brands worldwide';
$eyebrow_color = $attributes['eyebrowColor'] ?? 'cropx-blue';
$show_eyebrow  = (bool)($attributes['showEyebrow'] ?? true);
$custom_logos  = (array) ( $attributes['logos'] ?? array() );

$bg_color = $attributes['bgColor'] ?? 'taupe';
if ( ! in_array( $bg_color, array( 'taupe', 'white' ), true ) ) {
	$bg_color = 'taupe';
}

$auto_advance = (bool) ( $attributes['autoAdvance'] ?? true );
$marquee_class = 'ls-marquee' . ( $auto_advance ? '' : ' ls-marquee--static' );

$logo_spacing = (int) ( $attributes['logoSpacing'] ?? 80 );
if ( $logo_spacing < 0 ) {
	$logo_spacing = 0;
}

// ── Resolve logos ──────────────────────────────────────────────────────────
// Each logo in the rendered output is just [ 'url' => '...', 'alt' => '...' ].

if ( ! empty( $custom_logos ) ) {
	// Use editor-uploaded logos. Resolve the latest URL from the attachment ID
	// if available so media-library edits (renames, replacements) propagate.
	$logos = array();
	foreach ( $custom_logos as $entry ) {
		$id   = (int) ( $entry['id']      ?? 0 );
		$url  =       $entry['url']       ?? '';
		$alt  =       $entry['alt']       ?? '';
		$link = trim( (string) ( $entry['linkUrl'] ?? '' ) );

		if ( $id ) {
			$resolved = wp_get_attachment_url( $id );
			if ( $resolved ) {
				$url = $resolved;
			}
			// Fall back to the attachment's stored alt text if the editor left it blank.
			if ( '' === $alt ) {
				$alt = get_post_meta( $id, '_wp_attachment_image_alt', true ) ?: '';
			}
		}

		if ( $url ) {
			$logos[] = array( 'url' => $url, 'alt' => $alt, 'link' => $link );
		}
	}
} else {
	// Hardcoded default set — used until the editor uploads custom logos.
	// No click-through links on the defaults; 'link' key kept for shape parity
	// with the custom-logos branch so the render loop below doesn't need to
	// special-case which branch produced $logos.
	$logos_dir = CROPX_THEME_URI . 'assets/logos/';
	$logos     = array(
		array( 'url' => $logos_dir . 'anheuser-busch-a.svg', 'alt' => 'AB InBev',        'link' => '' ),
		array( 'url' => $logos_dir . 'dairy-holdings.svg',   'alt' => 'Dairy Holdings',  'link' => '' ),
		array( 'url' => $logos_dir . 'general-mills.svg',    'alt' => 'General Mills',   'link' => '' ),
		array( 'url' => $logos_dir . 'hzpc.svg',             'alt' => 'HZPC',            'link' => '' ),
		array( 'url' => $logos_dir . 'mccain.svg',           'alt' => 'McCain',          'link' => '' ),
		array( 'url' => $logos_dir . 'nasa.svg',             'alt' => 'NASA',            'link' => '' ),
		array( 'url' => $logos_dir . 'nec.svg',              'alt' => 'NEC',             'link' => '' ),
		array( 'url' => $logos_dir . 'nestle.svg',           'alt' => 'Nestlé',          'link' => '' ),
		array( 'url' => $logos_dir . 'pepsico.svg',          'alt' => 'PepsiCo',         'link' => '' ),
		array( 'url' => $logos_dir . 'ritter-sport.svg',     'alt' => 'Ritter Sport',    'link' => '' ),
	);
}

if ( empty( $logos ) ) {
	return; // Nothing to render.
}

$ls_wrapper_extra_attrs = array(
	'class'           => 'logo-strip logo-strip--bg-' . $bg_color,
	'data-section-bg' => $bg_color,
);

// PageSpeed fix (Aug 2026): drift-pattern.svg was a relative-path url() in
// style.css, which webpack base64-embeds (89KB SVG, past the ~10KB inlining
// cutoff — CLAUDE.md gotcha #7). $bg_color is currently validated to only
// taupe/white above, so 'deep-blue' is unreachable via the editor UI today —
// but this is injected conditionally to match the convention used everywhere
// else (testimonials-carousel, un-goals-two-column, etc.) and to be correct
// if that validation is ever loosened.
if ( 'deep-blue' === $bg_color ) {
	$ls_wrapper_extra_attrs['style'] = '--ls-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');';
}

$wrapper_attrs = get_block_wrapper_attributes( $ls_wrapper_extra_attrs );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-label="<?php esc_attr_e( 'Customer logos', 'cropx' ); ?>">
	<div class="logo-strip-inner">
		<?php if ( $show_eyebrow && $eyebrow ) : ?>
			<p class="section-eyebrow" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>
	</div>

	<div class="<?php echo esc_attr( $marquee_class ); ?>">
		<div class="ls-track" style="--ls-gap: <?php echo esc_attr( $logo_spacing ); ?>px">
			<?php foreach ( $logos as $logo ) : ?>
				<div class="ls-logo-slot">
					<?php if ( ! empty( $logo['link'] ) ) : ?>
						<a
							href="<?php echo esc_url( $logo['link'] ); ?>"
							class="ls-logo-link"
							target="_blank"
							rel="noopener noreferrer"
						>
							<img
								src="<?php echo esc_url( $logo['url'] ); ?>"
								alt="<?php echo esc_attr( $logo['alt'] ); ?>"
								class="ls-logo"
							>
						</a>
					<?php else : ?>
						<img
							src="<?php echo esc_url( $logo['url'] ); ?>"
							alt="<?php echo esc_attr( $logo['alt'] ); ?>"
							class="ls-logo"
						>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
			<?php // Second (aria-hidden) pass is a pure visual duplicate for the seamless
			// marquee loop — never wrapped in a link, so this duplicate copy can't
			// take keyboard focus or be tabbed into twice. ?>
			<?php foreach ( $logos as $logo ) : ?>
				<div class="ls-logo-slot">
					<img
						src="<?php echo esc_url( $logo['url'] ); ?>"
						alt=""
						aria-hidden="true"
						class="ls-logo"
					>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
