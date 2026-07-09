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

// ── Resolve logos ──────────────────────────────────────────────────────────
// Each logo in the rendered output is just [ 'url' => '...', 'alt' => '...' ].

if ( ! empty( $custom_logos ) ) {
	// Use editor-uploaded logos. Resolve the latest URL from the attachment ID
	// if available so media-library edits (renames, replacements) propagate.
	$logos = array();
	foreach ( $custom_logos as $entry ) {
		$id  = (int) ( $entry['id']  ?? 0 );
		$url =       $entry['url']   ?? '';
		$alt =       $entry['alt']   ?? '';

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
			$logos[] = array( 'url' => $url, 'alt' => $alt );
		}
	}
} else {
	// Hardcoded default set — used until the editor uploads custom logos.
	$logos_dir = CROPX_THEME_URI . 'assets/logos/';
	$logos     = array(
		array( 'url' => $logos_dir . 'anheuser-busch-a.svg', 'alt' => 'AB InBev' ),
		array( 'url' => $logos_dir . 'dairy-holdings.svg',   'alt' => 'Dairy Holdings' ),
		array( 'url' => $logos_dir . 'general-mills.svg',    'alt' => 'General Mills' ),
		array( 'url' => $logos_dir . 'hzpc.svg',             'alt' => 'HZPC' ),
		array( 'url' => $logos_dir . 'mccain.svg',           'alt' => 'McCain' ),
		array( 'url' => $logos_dir . 'nasa.svg',             'alt' => 'NASA' ),
		array( 'url' => $logos_dir . 'nec.svg',              'alt' => 'NEC' ),
		array( 'url' => $logos_dir . 'nestle.svg',           'alt' => 'Nestlé' ),
		array( 'url' => $logos_dir . 'pepsico.svg',          'alt' => 'PepsiCo' ),
		array( 'url' => $logos_dir . 'ritter-sport.svg',     'alt' => 'Ritter Sport' ),
	);
}

if ( empty( $logos ) ) {
	return; // Nothing to render.
}

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'logo-strip logo-strip--bg-' . $bg_color, 'data-section-bg' => $bg_color ) );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-label="<?php esc_attr_e( 'Customer logos', 'cropx' ); ?>">
	<div class="logo-strip-inner">
		<?php if ( $show_eyebrow && $eyebrow ) : ?>
			<p class="section-eyebrow" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>
	</div>

	<div class="ls-marquee">
		<div class="ls-track">
			<?php foreach ( $logos as $logo ) : ?>
				<img
					src="<?php echo esc_url( $logo['url'] ); ?>"
					alt="<?php echo esc_attr( $logo['alt'] ); ?>"
					class="ls-logo"
				>
			<?php endforeach; ?>
			<?php foreach ( $logos as $logo ) : ?>
				<img
					src="<?php echo esc_url( $logo['url'] ); ?>"
					alt=""
					aria-hidden="true"
					class="ls-logo"
				>
			<?php endforeach; ?>
		</div>
	</div>
</section>
