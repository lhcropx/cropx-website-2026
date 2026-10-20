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
if ( ! in_array( $bg_color, array( 'taupe' ), true ) ) {
	$bg_color = 'taupe';
}

$auto_advance = (bool) ( $attributes['autoAdvance'] ?? true );
$marquee_class = 'ls-marquee' . ( $auto_advance ? '' : ' ls-marquee--static' );

$logo_spacing = (int) ( $attributes['logoSpacing'] ?? 80 );
if ( $logo_spacing < 0 ) {
	$logo_spacing = 0;
}

// Marquee scroll speed — named presets rather than a raw seconds value so the
// editor UI stays simple. Higher seconds = slower scroll (it's a CSS
// animation-duration: the marquee travels the same distance either way, a
// longer duration just spreads that travel over more time).
$speed_durations = array(
	'normal' => 35,
	'slow'   => 55,
	'slower' => 80,
);
$speed = $attributes['speed'] ?? 'normal';
if ( ! isset( $speed_durations[ $speed ] ) ) {
	$speed = 'normal';
}
$scroll_duration = $speed_durations[ $speed ];

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
			$logos[] = array( 'id' => $id, 'url' => $url, 'alt' => $alt, 'link' => $link );
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

// reveal-group: scroll-reveal observed root (see src/shared/scrollReveal.js
// and scroll-reveal.css) — view.js observes '.logo-strip', and this class is
// what the CSS keys off to cascade the fade-up onto the eyebrow and the
// marquee itself (as two reveal-up elements, not per-logo — the marquee's
// own track already renders every logo twice for its infinite-scroll loop,
// so staggering individual logos would double up and fight that animation).
$ls_wrapper_extra_attrs = array(
	'class'           => 'logo-strip logo-strip--bg-' . $bg_color . ' reveal-group',
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
			<p class="section-eyebrow reveal-up" style="--reveal-delay:0.05s;color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>
	</div>

	<div class="<?php echo esc_attr( $marquee_class ); ?> reveal-up" style="--reveal-delay:0.15s">
		<div class="ls-track" style="--ls-gap: <?php echo esc_attr( $logo_spacing ); ?>px; --ls-duration: <?php echo esc_attr( $scroll_duration ); ?>s">
			<?php foreach ( $logos as $logo ) : ?>
				<?php
				// PageSpeed fix (Sep 2026): serve WP's built-in 'medium' size
				// instead of the full-resolution original — see
				// cropx_logo_img_url() in inc/helpers.php.
				$logo_id  = (int) ( $logo['id'] ?? 0 );
				$logo_src = cropx_logo_img_url( $logo_id, $logo['url'] );
				?>
				<div class="ls-logo-slot">
					<?php if ( ! empty( $logo['link'] ) ) : ?>
						<a
							href="<?php echo esc_url( $logo['link'] ); ?>"
							class="ls-logo-link"
							target="_blank"
							rel="noopener noreferrer"
						>
							<img
								src="<?php echo esc_url( $logo_src ); ?>"
								alt="<?php echo esc_attr( $logo['alt'] ); ?>"
								class="ls-logo"
								<?php echo cropx_img_dims_attr( $logo_id, $logo['url'] ); ?>
								loading="lazy"
							>
						</a>
					<?php else : ?>
						<img
							src="<?php echo esc_url( $logo_src ); ?>"
							alt="<?php echo esc_attr( $logo['alt'] ); ?>"
							class="ls-logo"
							<?php echo cropx_img_dims_attr( $logo_id, $logo['url'] ); ?>
							loading="lazy"
						>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
			<?php // Second pass is a pure visual duplicate for the seamless marquee
			// loop. It still needs a working link when the logo has one —
			// otherwise the marquee becomes unclickable the moment it scrolls
			// past the first set — but the link is aria-hidden + tabindex="-1"
			// so this duplicate copy can't take keyboard focus or be announced
			// twice to screen readers; only pointer/mouse clicks reach it. ?>
			<?php foreach ( $logos as $logo ) : ?>
				<?php
				$logo_id  = (int) ( $logo['id'] ?? 0 );
				$logo_src = cropx_logo_img_url( $logo_id, $logo['url'] );
				?>
				<div class="ls-logo-slot">
					<?php if ( ! empty( $logo['link'] ) ) : ?>
						<a
							href="<?php echo esc_url( $logo['link'] ); ?>"
							class="ls-logo-link"
							target="_blank"
							rel="noopener noreferrer"
							aria-hidden="true"
							tabindex="-1"
						>
							<img
								src="<?php echo esc_url( $logo_src ); ?>"
								alt=""
								class="ls-logo"
								<?php echo cropx_img_dims_attr( $logo_id, $logo['url'] ); ?>
								loading="lazy"
							>
						</a>
					<?php else : ?>
						<img
							src="<?php echo esc_url( $logo_src ); ?>"
							alt=""
							aria-hidden="true"
							class="ls-logo"
							<?php echo cropx_img_dims_attr( $logo_id, $logo['url'] ); ?>
							loading="lazy"
						>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
