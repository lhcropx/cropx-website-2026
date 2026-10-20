<?php
/**
 * Hero Carousel — Curved Edge (cropx/hero-carousel)
 *
 * Based on hero-curved-standard (same nav, segment accent, text styling and
 * animated swoop) but with no device/phone overlays: the photography IS the
 * hero. Up to 3 "sets" crossfade; each set holds 4 aspect-ratio variants of
 * one image — extra-wide 21:9 (>=1920px), desktop 16:9 (1025–1919px),
 * tablet 4:3 (601–1024px), mobile 9:16 (<=600px). The hero's height follows
 * the active variant's aspect ratio, so the whole image shows at every width
 * (very tall on phones, by design).
 *
 * Two independent switches, both on by default:
 *  - enableCarousel: off => only the first set renders, no controls/timer.
 *  - enableVariants: off => ONE image per set (the "Desktop" field, falling
 *    back to whichever exists) is used at every size and cover-cropped to a
 *    min-height using the set's focal point.
 *
 * Only closures are used below (no named functions) because render.php runs
 * once per block instance — redeclaring a named function would be fatal when
 * two of these blocks share a page.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$allowed_segments = array( 'cropx', 'enterprise', 'service-provider', 'on-farm' );
$segment = $attributes['segment'] ?? 'cropx';
if ( ! in_array( $segment, $allowed_segments, true ) ) {
	$segment = 'cropx';
}

$show_eyebrow    = (bool) ( $attributes['showEyebrow']    ?? true );
$show_cta        = (bool) ( $attributes['showCta']        ?? true );
$enable_carousel = (bool) ( $attributes['enableCarousel'] ?? true );
$enable_variants = (bool) ( $attributes['enableVariants'] ?? true );
$auto_advance    = (bool) ( $attributes['autoAdvance']    ?? true );
$show_dots       = (bool) ( $attributes['showDots']       ?? true );
$shared_text     = (bool) ( $attributes['sharedText']     ?? false );
$interval_sec    = max( 3, min( 20, (int) ( $attributes['intervalSec'] ?? 6 ) ) );
$text_width      = (int) ( $attributes['textWidth'] ?? 100 );

$content_style = sprintf(
	'--hcr-headline-w:%1$s;--hcr-subhead-w:%1$s;',
	esc_attr( $text_width / 100 )
);

$accent_config = array(
	'cropx'            => array( 'base' => '#0CA8C0', 'dark' => '#0A8FA3' ),
	'enterprise'       => array( 'base' => '#E9C242', 'dark' => '#C9A130' ),
	'service-provider' => array( 'base' => '#E48C4D', 'dark' => '#C4733A' ),
	'on-farm'          => array( 'base' => '#96C05A', 'dark' => '#7BA348' ),
);
$stop_base = esc_attr( $accent_config[ $segment ]['base'] );
$stop_dark = esc_attr( $accent_config[ $segment ]['dark'] );

$swoop_fill = cropx_get_swoop_fill( 'cropx/hero-carousel', $attributes['swoopFill'] ?? '' );

// ── Normalise the 3 sets ────────────────────────────────────────────────────
$slide_defaults = array(
	'eyebrow' => '', 'heading' => '', 'subheading' => '',
	'ctaLabel' => '', 'ctaUrl' => '#', 'ctaLinkType' => 'url', 'ctaFileId' => 0, 'ctaFileUrl' => '',
	'showCta2' => false, 'cta2Label' => '', 'cta2Url' => '#', 'cta2LinkType' => 'url', 'cta2FileId' => 0, 'cta2FileUrl' => '',
	'wideId' => 0, 'wideUrl' => '', 'desktopId' => 0, 'desktopUrl' => '',
	'tabletId' => 0, 'tabletUrl' => '', 'mobileId' => 0, 'mobileUrl' => '',
	'focalX' => 0.5, 'focalY' => 0.5,
);
$slides_attr = is_array( $attributes['slides'] ?? null ) ? $attributes['slides'] : array();
$all_slides  = array();
for ( $i = 0; $i < 3; $i++ ) {
	$raw = ( isset( $slides_attr[ $i ] ) && is_array( $slides_attr[ $i ] ) ) ? $slides_attr[ $i ] : array();
	$all_slides[ $i ] = array_merge( $slide_defaults, $raw );
}

$variant_keys = array( 'wide', 'desktop', 'tablet', 'mobile' );

// Resolve one variant to [ id, url ]. The URL always comes fresh from the
// attachment ID (the stored *Url is only a snapshot that goes stale if the
// site's domain changes — same fix as the other hero blocks).
$resolve_img = function ( array $s, string $key ) {
	$id  = (int) ( $s[ $key . 'Id' ] ?? 0 );
	$url = (string) ( $s[ $key . 'Url' ] ?? '' );
	if ( $id ) {
		$src = wp_get_attachment_image_src( $id, 'full' );
		if ( $src ) {
			$url = $src[0];
		}
	}
	return array( $id, $url );
};

$has_image = function ( array $s ) use ( $resolve_img, $variant_keys ) {
	foreach ( $variant_keys as $k ) {
		$r = $resolve_img( $s, $k );
		if ( $r[1] ) {
			return true;
		}
	}
	return false;
};

// Which sets actually render: only those with an image (so unused set slots
// never show as blank frames). Carousel off => just the first of those.
$active_slides = array();
foreach ( $all_slides as $s ) {
	if ( $has_image( $s ) ) {
		$active_slides[] = $s;
	}
}
if ( empty( $active_slides ) ) {
	$active_slides[] = $all_slides[0];
}
if ( ! $enable_carousel ) {
	$active_slides = array( $active_slides[0] );
}
$slide_count = count( $active_slides );
$is_carousel = $slide_count > 1;

// Text source: each set's own text, or set 1's text for all sets.
$text_for = function ( int $idx ) use ( $active_slides, $all_slides, $shared_text ) {
	return $shared_text ? $all_slides[0] : $active_slides[ $idx ];
};

// ── CTA helper ─────────────────────────────────────────────────────────────
// Each CTA is a URL (default) or a media-library file resolved fresh from its
// attachment ID. Files open in a new tab (no `download` attribute) — sitewide
// convention, see resource-downloads/render.php.
$resolve_cta = function ( array $s, string $prefix ) {
	$type    = $s[ $prefix . 'LinkType' ] ?? 'url';
	$file_id = (int) ( $s[ $prefix . 'FileId' ] ?? 0 );
	$file    = (string) ( $s[ $prefix . 'FileUrl' ] ?? '' );
	if ( $file_id ) {
		$fresh = wp_get_attachment_url( $file_id );
		if ( $fresh ) {
			$file = $fresh;
		}
	}
	$is_file = ( 'file' === $type && $file );
	$href    = $is_file ? $file : cropx_url( (string) ( $s[ $prefix . 'Url' ] ?? '#' ) );
	return array( $href, $is_file );
};

$heading_tags = array( 'em' => array(), 'strong' => array(), 'br' => array() );
$sub_tags     = array_merge( $heading_tags, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

$arrow_icon    = '<svg width="18" height="18" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$download_icon = '<svg class="cta-icon--static" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/><polyline points="7 10 12 15 17 10" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>';

// Prints one text layer. $idx is the set index (null for the single shared
// layer). Only the first layer is the page's <h1>; the rest are <h2> so a
// page never ships several <h1>s.
$print_content = function ( array $s, $idx, bool $is_active, string $style ) use (
	$show_eyebrow, $show_cta, $resolve_cta, $heading_tags, $sub_tags, $arrow_icon, $download_icon
) {
	$tag = ( null === $idx || 0 === $idx ) ? 'h1' : 'h2';
	$cls = 'hcr-content' . ( $is_active ? ' is-active' : '' );
	$attr_idx = ( null === $idx ) ? '' : ' data-slide="' . (int) $idx . '"';
	$hidden   = $is_active ? '' : ' aria-hidden="true"';
	?>
	<div class="<?php echo esc_attr( $cls ); ?>"<?php echo $attr_idx . $hidden; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> style="<?php echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
		<?php if ( $show_eyebrow && ! empty( $s['eyebrow'] ) ) : ?>
			<p class="hcr-eyebrow"><?php echo esc_html( wp_strip_all_tags( $s['eyebrow'] ) ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $s['heading'] ) ) : ?>
			<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="hcr-headline"><?php echo wp_kses( $s['heading'], $heading_tags ); ?></<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php endif; ?>
		<?php if ( ! empty( $s['subheading'] ) ) : ?>
			<p class="hcr-subheadline"><?php echo wp_kses( $s['subheading'], $sub_tags ); ?></p>
		<?php endif; ?>
		<?php if ( $show_cta && ! empty( $s['ctaLabel'] ) ) :
			list( $cta_href, $cta_is_file )   = $resolve_cta( $s, 'cta' );
			list( $cta2_href, $cta2_is_file ) = $resolve_cta( $s, 'cta2' );
			$tab = $is_active ? '' : ' tabindex="-1"';
			?>
			<div class="hcr-cta-row">
				<a class="hcr-cta" href="<?php echo esc_url( $cta_href ); ?>"<?php echo $cta_is_file ? ' target="_blank" rel="noopener noreferrer"' : ''; echo $tab; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $s['ctaLabel'] ); ?></a>
				<?php if ( ! empty( $s['showCta2'] ) && ! empty( $s['cta2Label'] ) ) : ?>
					<a class="hcr-cta--ghost" href="<?php echo esc_url( $cta2_href ); ?>"<?php echo $cta2_is_file ? ' target="_blank" rel="noopener noreferrer"' : ''; echo $tab; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php echo esc_html( $s['cta2Label'] ); ?>
						<?php echo $cta2_is_file ? $download_icon : $arrow_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
};

$block_classes = array(
	'hcr-block',
	'hcr-segment-' . $segment,
	$enable_variants ? 'hcr--variants' : 'hcr--single',
);
if ( $is_carousel ) {
	$block_classes[] = 'hcr--carousel';
}
$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => implode( ' ', $block_classes ),
) );

$flow_id = wp_unique_id( 'hcr-flow-' );
$autoplay = $is_carousel && $auto_advance;
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php if ( $is_carousel ) : ?>
		data-hcr-carousel="1"
		data-hcr-autoplay="<?php echo $autoplay ? '1' : '0'; ?>"
		data-hcr-interval="<?php echo (int) $interval_sec; ?>"
	<?php endif; ?>>

	<?php
	if ( empty( $GLOBALS['cropx_nav_already_rendered'] ) ) :
		cropx_render_nav();
	endif;
	?>

	<div class="hcr-bleed-wrap" style="--hcr-swoop-fill:<?php echo esc_attr( $swoop_fill ); ?>">
		<section class="hcr-hero"<?php echo $is_carousel ? ' aria-roledescription="carousel" aria-label="' . esc_attr__( 'Featured', 'cropx' ) . '"' : ''; ?>>

			<div class="hcr-media">
				<?php foreach ( $active_slides as $idx => $s ) :
					$imgs = array();
					foreach ( $variant_keys as $k ) {
						$imgs[ $k ] = $resolve_img( $s, $k );
					}
					// Nearest-available fallback so a set with a missing variant
					// still renders something sensible at that size.
					$pick = function ( array $order ) use ( $imgs ) {
						foreach ( $order as $k ) {
							if ( $imgs[ $k ][1] ) {
								return $imgs[ $k ];
							}
						}
						return array( 0, '' );
					};
					$is_first = ( 0 === $idx );
					$fx = round( (float) $s['focalX'] * 100, 1 );
					$fy = round( (float) $s['focalY'] * 100, 1 );
					$img_style = sprintf( 'object-position:%s%% %s%%;', esc_attr( $fx ), esc_attr( $fy ) );

					$source = function ( array $img, string $media ) {
						if ( ! $img[1] ) {
							return '';
						}
						$srcset = $img[0] ? wp_get_attachment_image_srcset( $img[0], 'full' ) : '';
						if ( $srcset ) {
							return '<source media="' . esc_attr( $media ) . '" srcset="' . esc_attr( $srcset ) . '" sizes="100vw">';
						}
						return '<source media="' . esc_attr( $media ) . '" srcset="' . esc_url( $img[1] ) . '">';
					};

					if ( $enable_variants ) {
						$wide    = $pick( array( 'wide', 'desktop', 'tablet', 'mobile' ) );
						$desktop = $pick( array( 'desktop', 'wide', 'tablet', 'mobile' ) );
						$tablet  = $pick( array( 'tablet', 'desktop', 'mobile', 'wide' ) );
						$mobile  = $pick( array( 'mobile', 'tablet', 'desktop', 'wide' ) );
						$fallback = $mobile;
					} else {
						$fallback = $pick( array( 'desktop', 'wide', 'tablet', 'mobile' ) );
					}
					$fb_srcset = ( $fallback[0] ) ? wp_get_attachment_image_srcset( $fallback[0], 'full' ) : '';
					$fb_extra  = $fb_srcset ? ' srcset="' . esc_attr( $fb_srcset ) . '" sizes="100vw"' : '';
					?>
					<div class="hcr-slide<?php echo $is_first ? ' is-active' : ''; ?>" data-slide="<?php echo (int) $idx; ?>"<?php echo $is_carousel ? ' role="group" aria-roledescription="slide" aria-label="' . esc_attr( sprintf( /* translators: 1: slide number, 2: total slides */ __( '%1$d of %2$d', 'cropx' ), $idx + 1, $slide_count ) ) . '"' : ''; ?>>
						<?php if ( $fallback[1] ) : ?>
						<picture>
							<?php if ( $enable_variants ) : ?>
								<?php echo $source( $wide, '(min-width: 1920px)' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php echo $source( $desktop, '(min-width: 1025px)' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php echo $source( $tablet, '(min-width: 601px)' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php endif; ?>
							<img
								src="<?php echo esc_url( $fallback[1] ); ?>"
								alt=""
								decoding="async"
								<?php echo $is_first ? 'fetchpriority="high"' : 'loading="lazy" fetchpriority="low"'; ?>
								<?php echo $fb_extra; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php echo cropx_img_dims_attr( (int) $fallback[0], $fallback[1] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								style="<?php echo esc_attr( $img_style ); ?>"
							>
						</picture>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="hcr-overlay"></div>

			<?php
			if ( $shared_text ) {
				$print_content( $all_slides[0], null, true, $content_style );
			} else {
				foreach ( $active_slides as $idx => $s ) {
					$print_content( $s, $idx, 0 === $idx, $content_style );
				}
			}
			?>

			<?php if ( $is_carousel && ( $show_dots || $autoplay ) ) : ?>
				<div class="hcr-controls">
					<?php if ( $show_dots ) : ?>
						<?php foreach ( $active_slides as $idx => $s ) : ?>
							<button type="button" class="hcr-dot<?php echo 0 === $idx ? ' is-active' : ''; ?>" data-go="<?php echo (int) $idx; ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: slide number */ __( 'Show slide %d', 'cropx' ), $idx + 1 ) ); ?>"<?php echo 0 === $idx ? ' aria-current="true"' : ''; ?>></button>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php if ( $autoplay ) : ?>
						<button type="button" class="hcr-pause" aria-pressed="false" aria-label="<?php echo esc_attr__( 'Pause slideshow', 'cropx' ); ?>" data-label-pause="<?php echo esc_attr__( 'Pause slideshow', 'cropx' ); ?>" data-label-play="<?php echo esc_attr__( 'Play slideshow', 'cropx' ); ?>">
							<svg class="hcr-pause-icon" width="12" height="12" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true"><rect x="2" y="1" width="3" height="10" rx="0.5"/><rect x="7" y="1" width="3" height="10" rx="0.5"/></svg>
							<svg class="hcr-play-icon" width="12" height="12" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true"><path d="M3 1.2v9.6a.4.4 0 0 0 .6.35l7.6-4.8a.4.4 0 0 0 0-.7L3.6.85A.4.4 0 0 0 3 1.2Z"/></svg>
						</button>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<!-- Quadratic-bezier swoop, identical to hero-curved-standard — see that
			     block's render.php for the full geometry notes. -->
			<div class="hcr-swoop" aria-hidden="true">
				<svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
					<defs>
						<linearGradient id="<?php echo esc_attr( $flow_id ); ?>" x1="0" y1="0" x2="2880" y2="0" gradientUnits="userSpaceOnUse">
							<stop offset="0%"   stop-color="<?php echo $stop_base; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"/>
							<stop offset="25%"  stop-color="<?php echo $stop_dark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"/>
							<stop offset="50%"  stop-color="<?php echo $stop_base; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"/>
							<stop offset="75%"  stop-color="<?php echo $stop_dark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"/>
							<stop offset="100%" stop-color="<?php echo $stop_base; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"/>
							<animateTransform attributeName="gradientTransform" type="translate"
								values="0,0; -1440,0; 0,0" dur="14s" repeatCount="indefinite" calcMode="linear"/>
						</linearGradient>
					</defs>
					<path class="hcr-fill-path" d="M0,80 Q720,80 1440,30 L1440,80 L0,80 Z"/>
					<path d="M0,70 Q720,70 1440,20" fill="none"
						stroke="url(#<?php echo esc_attr( $flow_id ); ?>)"
						stroke-width="20" stroke-linecap="round"/>
				</svg>
			</div>
		</section>
	</div>

</div>
