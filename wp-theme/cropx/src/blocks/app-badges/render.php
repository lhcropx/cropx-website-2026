<?php
/**
 * App Store Badges block — front-end render.
 *
 * Deliberately minimal: a centered row of up to 2 badge links (App Store,
 * Google Play), an optional eyebrow, and the usual 3-way background choice.
 * No JS, no viewScript — these are two static links.
 *
 * Badge artwork is NOT bundled with the theme. Apple and Google each publish
 * their own official "Download on the App Store" / "Get it on Google Play"
 * badge assets (with their own trademark/usage guidelines) via their badge
 * generator pages — Lauren uploads those directly through the sidebar's Media
 * Upload fields. If a badge's image isn't set yet, that badge link simply
 * isn't rendered (same graceful-degrade approach as the Logo Carousel block
 * when it has no logos) rather than showing a broken image or a recreated
 * stand-in for trademarked artwork.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$eyebrow       = $attributes['eyebrow']      ?? '';
$eyebrow_color = $attributes['eyebrowColor'] ?? 'cropx-blue';
$show_eyebrow  = (bool) ( $attributes['showEyebrow'] ?? false );

$bg_color = $attributes['bgColor'] ?? 'taupe';
if ( ! in_array( $bg_color, array( 'taupe', 'white', 'deep-blue' ), true ) ) {
	$bg_color = 'taupe';
}

$alignment = $attributes['alignment'] ?? 'center';
if ( ! in_array( $alignment, array( 'left', 'center', 'right' ), true ) ) {
	$alignment = 'center';
}

// ── Resolve each badge's image URL from its attachment ID if available, so
// media-library edits (renames, replacements) propagate automatically. ──
// Guarded with function_exists(): render.php is include()'d fresh on every
// render, so a plain function declaration here would fatal with "Cannot
// redeclare function" if this block is ever placed twice on the same page.
if ( ! function_exists( 'cropx_app_badges_resolve_image' ) ) {
	function cropx_app_badges_resolve_image( $id, $fallback_url ) {
		$id = (int) $id;
		if ( $id ) {
			$resolved = wp_get_attachment_url( $id );
			if ( $resolved ) {
				return $resolved;
			}
		}
		return $fallback_url;
	}
}

$app_store_url_img    = cropx_app_badges_resolve_image( $attributes['appStoreImageId'] ?? 0, $attributes['appStoreImageUrl'] ?? '' );
$app_store_alt        = $attributes['appStoreImageAlt'] ?? 'Download on the App Store';
$app_store_link       = $attributes['appStoreUrl'] ?? '#';

$google_play_url_img  = cropx_app_badges_resolve_image( $attributes['googlePlayImageId'] ?? 0, $attributes['googlePlayImageUrl'] ?? '' );
$google_play_alt      = $attributes['googlePlayImageAlt'] ?? 'Get it on Google Play';
$google_play_link     = $attributes['googlePlayUrl'] ?? '#';

$has_app_store   = ! empty( $app_store_url_img );
$has_google_play = ! empty( $google_play_url_img );

// Nothing at all to show — bail, same as Logo Carousel with an empty logo set.
if ( ! $has_app_store && ! $has_google_play && ! ( $show_eyebrow && $eyebrow ) ) {
	return;
}

$ab_wrapper_extra_attrs = array(
	'class'           => 'app-badges app-badges--bg-' . $bg_color,
	'data-section-bg' => $bg_color,
);

// PageSpeed fix (Aug 2026 gotcha — see CLAUDE.md / PROGRESS.md "WordPress
// block development gotchas"): drift-pattern.svg must be injected as a real,
// cacheable URL via a CSS custom property, never referenced by relative
// path in style.css — a relative url() gets base64-inlined by webpack (the
// SVG is ~90KB), which has previously bloated a block's compiled CSS enough
// to silently fail mid-extraction during a real WP File Manager deploy. Same
// technique as Logo Carousel, FAQ Accordion, Pre-footer CTA, etc.
if ( 'deep-blue' === $bg_color ) {
	$ab_wrapper_extra_attrs['style'] = '--ab-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');';
}

$wrapper_attrs = get_block_wrapper_attributes( $ab_wrapper_extra_attrs );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="app-badges-inner">

		<?php if ( $show_eyebrow && $eyebrow ) : ?>
			<p class="section-eyebrow" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( $has_app_store || $has_google_play ) : ?>
			<div class="ab-row ab-row--<?php echo esc_attr( $alignment ); ?>">
				<?php if ( $has_app_store ) : ?>
					<a href="<?php echo esc_url( $app_store_link ); ?>" class="ab-badge" target="_blank" rel="noopener noreferrer">
						<img src="<?php echo esc_url( $app_store_url_img ); ?>" alt="<?php echo esc_attr( $app_store_alt ); ?>" loading="lazy" decoding="async">
					</a>
				<?php endif; ?>
				<?php if ( $has_google_play ) : ?>
					<a href="<?php echo esc_url( $google_play_link ); ?>" class="ab-badge" target="_blank" rel="noopener noreferrer">
						<img src="<?php echo esc_url( $google_play_url_img ); ?>" alt="<?php echo esc_attr( $google_play_alt ); ?>" loading="lazy" decoding="async">
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	</div>
</section>
