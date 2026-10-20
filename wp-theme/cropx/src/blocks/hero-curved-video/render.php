<?php
/**
 * Video Hero — Curved Edge (cropx/hero-curved-video)
 *
 * Same nav, segment accent, text styling and animated swoop as
 * hero-curved-standard, but with NO device/phone PNG overlays and NO drift
 * pattern: the background is a looping video.
 *
 * Load-time strategy (the whole point of this block's structure):
 *  1. The POSTER is the page's LCP element — a real <img fetchpriority="high">
 *     (never a CSS background; see CLAUDE.md gotcha #9). It paints first.
 *  2. The <video> is rendered with NO src and preload="none", so the browser
 *     downloads nothing for it. view.js adds the <source> elements only after
 *     the window `load` event (+ an idle callback), so the video can never
 *     compete with the poster, fonts or CSS. It then fades in over the poster.
 *  3. view.js skips the video entirely (poster only, with a Play button) for
 *     prefers-reduced-motion, Data Saver and 2G/3G connections, and on phones
 *     unless a mobile video was uploaded (or the editor opted the desktop
 *     video in for mobile).
 *
 * Only closures are used (no named functions): render.php runs once per block
 * instance, so redeclaring a named function would be fatal with two on a page.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$allowed_segments = array( 'cropx', 'enterprise', 'service-provider', 'on-farm' );
$segment = $attributes['segment'] ?? 'cropx';
if ( ! in_array( $segment, $allowed_segments, true ) ) {
	$segment = 'cropx';
}

$eyebrow     = $attributes['eyebrow']    ?? '';
$heading     = $attributes['heading']    ?? '';
$subheading  = $attributes['subheading'] ?? '';
$show_eyebrow = (bool) ( $attributes['showEyebrow'] ?? true );
$show_cta     = (bool) ( $attributes['showCta']     ?? true );
$cta_label    = $attributes['ctaLabel']  ?? '';
$cta2_label   = $attributes['cta2Label'] ?? '';
$show_cta2    = (bool) ( $attributes['showCta2'] ?? false );
$text_width   = (int) ( $attributes['textWidth'] ?? 100 );
$overlay      = max( 0, min( 100, (int) ( $attributes['overlayStrength'] ?? 100 ) ) );

$content_style = sprintf(
	'--hcv-headline-w:%1$s;--hcv-subhead-w:%1$s;',
	esc_attr( $text_width / 100 )
);

// ── CTAs: URL (default) or a media-library file resolved fresh from its ID,
// opened in a new tab (sitewide convention — see resource-downloads/render.php).
$resolve_cta = function ( string $prefix ) use ( $attributes ) {
	$type    = $attributes[ $prefix . 'LinkType' ] ?? 'url';
	$file_id = (int) ( $attributes[ $prefix . 'FileId' ] ?? 0 );
	$file    = (string) ( $attributes[ $prefix . 'FileUrl' ] ?? '' );
	if ( $file_id ) {
		$fresh = wp_get_attachment_url( $file_id );
		if ( $fresh ) {
			$file = $fresh;
		}
	}
	$is_file = ( 'file' === $type && $file );
	$href    = $is_file ? $file : cropx_url( (string) ( $attributes[ $prefix . 'Url' ] ?? '#' ) );
	return array( $href, $is_file );
};
list( $cta_href, $cta_is_file )   = $resolve_cta( 'cta' );
list( $cta2_href, $cta2_is_file ) = $resolve_cta( 'cta2' );

$cta2_icon = $cta2_is_file
	? '<svg class="cta-icon--static" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/><polyline points="7 10 12 15 17 10" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>'
	: '<svg width="18" height="18" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

// ── Swoop ───────────────────────────────────────────────────────────────────
$swoop_fill = cropx_get_swoop_fill( 'cropx/hero-curved-video', $attributes['swoopFill'] ?? '' );
$accent_config = array(
	'cropx'            => array( 'base' => '#0CA8C0', 'dark' => '#0A8FA3' ),
	'enterprise'       => array( 'base' => '#E9C242', 'dark' => '#C9A130' ),
	'service-provider' => array( 'base' => '#E48C4D', 'dark' => '#C4733A' ),
	'on-farm'          => array( 'base' => '#96C05A', 'dark' => '#7BA348' ),
);
$stop_base = esc_attr( $accent_config[ $segment ]['base'] );
$stop_dark = esc_attr( $accent_config[ $segment ]['dark'] );
$flow_id   = wp_unique_id( 'hcv-flow-' );

// ── Poster (desktop + optional mobile) ──────────────────────────────────────
// Resolve URLs fresh from attachment IDs; the stored *Url is only a snapshot
// that goes stale if the site's domain changes.
$resolve_img = function ( string $id_key, string $url_key ) use ( $attributes ) {
	$id  = (int) ( $attributes[ $id_key ] ?? 0 );
	$url = (string) ( $attributes[ $url_key ] ?? '' );
	if ( $id ) {
		$src = wp_get_attachment_image_src( $id, 'full' );
		if ( $src ) {
			$url = $src[0];
		}
	}
	return array( $id, $url );
};
$poster        = $resolve_img( 'posterId', 'posterUrl' );
$poster_mobile = $resolve_img( 'posterMobileId', 'posterMobileUrl' );
// Whichever exists is the <img> fallback; the other becomes a <source>.
$main_poster = $poster[1] ? $poster : $poster_mobile;

$bg_focal_x = isset( $attributes['bgFocalX'] ) ? round( (float) $attributes['bgFocalX'] * 100, 1 ) : 50;
$bg_focal_y = isset( $attributes['bgFocalY'] ) ? round( (float) $attributes['bgFocalY'] * 100, 1 ) : 30;
$bg_zoom    = isset( $attributes['bgZoom'] ) ? (float) $attributes['bgZoom'] : 100;
$bg_flip_x  = ! empty( $attributes['bgFlipX'] );

// Focal point drives object-position on the poster AND the video (so the swap
// is seamless); zoom is one transform on the shared .hcv-zoom wrapper; flip is
// on the outer .hcv-bg wrapper (see hero-curved-standard for why they're
// separate nested elements).
$pos_style  = sprintf( 'object-position:%s%% %s%%;', esc_attr( $bg_focal_x ), esc_attr( $bg_focal_y ) );
$zoom_style = sprintf(
	'transform:scale(%s);transform-origin:%s%% %s%%;',
	esc_attr( number_format( $bg_zoom / 100, 4, '.', '' ) ),
	esc_attr( $bg_focal_x ),
	esc_attr( $bg_focal_y )
);

// ── Video sources ───────────────────────────────────────────────────────────
$resolve_file = function ( string $id_key, string $url_key ) use ( $attributes ) {
	$id  = (int) ( $attributes[ $id_key ] ?? 0 );
	$url = (string) ( $attributes[ $url_key ] ?? '' );
	if ( $id ) {
		$fresh = wp_get_attachment_url( $id );
		if ( $fresh ) {
			$url = $fresh;
		}
	}
	return $url;
};
$v_mp4      = $resolve_file( 'videoMp4Id', 'videoMp4Url' );
$v_webm     = $resolve_file( 'videoWebmId', 'videoWebmUrl' );
$v_mp4_m    = $resolve_file( 'videoMobileMp4Id', 'videoMobileMp4Url' );
$v_webm_m   = $resolve_file( 'videoMobileWebmId', 'videoMobileWebmUrl' );
$has_video  = (bool) ( $v_mp4 || $v_webm || $v_mp4_m || $v_webm_m );
$video_loop = (bool) ( $attributes['videoLoop'] ?? true );
$show_pause = $has_video && (bool) ( $attributes['showPause'] ?? true );
$mobile_uses_desktop = (bool) ( $attributes['mobileUsesDesktopVideo'] ?? false );

$heading_tags = array( 'em' => array(), 'strong' => array(), 'br' => array() );
$sub_tags     = array_merge( $heading_tags, array(
	'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
) );

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => 'hcv-block hcv-segment-' . $segment . ( $has_video ? ' hcv--has-video' : '' ),
) );
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php
	if ( empty( $GLOBALS['cropx_nav_already_rendered'] ) ) :
		cropx_render_nav();
	endif;
	?>

	<div class="hcv-bleed-wrap" style="--hcv-swoop-fill:<?php echo esc_attr( $swoop_fill ); ?>">
		<section class="hcv-hero">

			<div class="hcv-bg"<?php echo $bg_flip_x ? ' style="transform:scaleX(-1);"' : ''; ?>>
				<div class="hcv-zoom" style="<?php echo esc_attr( $zoom_style ); ?>">
					<?php if ( $main_poster[1] ) :
						$main_srcset = $main_poster[0] ? wp_get_attachment_image_srcset( $main_poster[0], 'full' ) : '';
						?>
					<picture>
						<?php if ( $poster[1] && $poster_mobile[1] ) :
							$m_srcset = $poster_mobile[0] ? wp_get_attachment_image_srcset( $poster_mobile[0], 'full' ) : '';
							?>
							<source media="(max-width: 768px)" srcset="<?php echo $m_srcset ? esc_attr( $m_srcset ) : esc_url( $poster_mobile[1] ); ?>"<?php echo $m_srcset ? ' sizes="100vw"' : ''; ?>>
						<?php endif; ?>
						<img
							class="hcv-poster"
							src="<?php echo esc_url( $main_poster[1] ); ?>"
							alt=""
							fetchpriority="high"
							decoding="async"
							<?php echo $main_srcset ? 'srcset="' . esc_attr( $main_srcset ) . '" sizes="100vw"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php echo cropx_img_dims_attr( (int) $main_poster[0], $main_poster[1] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							style="<?php echo esc_attr( $pos_style ); ?>"
						>
					</picture>
					<?php endif; ?>

					<?php if ( $has_video ) : ?>
					<video
						class="hcv-video"
						muted
						<?php echo $video_loop ? 'loop' : ''; ?>
						playsinline
						preload="none"
						aria-hidden="true"
						tabindex="-1"
						disablepictureinpicture
						disableremoteplayback
						data-mp4="<?php echo esc_url( $v_mp4 ); ?>"
						data-webm="<?php echo esc_url( $v_webm ); ?>"
						data-mp4-m="<?php echo esc_url( $v_mp4_m ); ?>"
						data-webm-m="<?php echo esc_url( $v_webm_m ); ?>"
						data-mobile-fallback="<?php echo $mobile_uses_desktop ? '1' : '0'; ?>"
						style="<?php echo esc_attr( $pos_style ); ?>"
					></video>
					<?php endif; ?>
				</div>
			</div>

			<div class="hcv-overlay"<?php echo 100 !== $overlay ? ' style="opacity:' . esc_attr( $overlay / 100 ) . ';"' : ''; ?>></div>

			<div class="hcv-content" style="<?php echo $content_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
				<?php if ( $show_eyebrow && $eyebrow ) : ?>
					<p class="hcv-eyebrow"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></p>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h1 class="hcv-headline"><?php echo wp_kses( $heading, $heading_tags ); ?></h1>
				<?php endif; ?>
				<?php if ( $subheading ) : ?>
					<p class="hcv-subheadline"><?php echo wp_kses( $subheading, $sub_tags ); ?></p>
				<?php endif; ?>
				<?php if ( $show_cta && $cta_label ) : ?>
				<div class="hcv-cta-row">
					<a class="hcv-cta" href="<?php echo esc_url( $cta_href ); ?>"<?php echo $cta_is_file ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $cta_label ); ?></a>
					<?php if ( $show_cta2 && $cta2_label ) : ?>
					<a class="hcv-cta--ghost" href="<?php echo esc_url( $cta2_href ); ?>"<?php echo $cta2_is_file ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
						<?php echo esc_html( $cta2_label ); ?>
						<?php echo $cta2_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>

			<?php if ( $show_pause ) : ?>
			<?php /* Hidden until view.js decides the video is eligible (so it never shows without JS or when there is nothing to pause). */ ?>
			<div class="hcv-controls" hidden>
				<button type="button" class="hcv-pause" aria-pressed="false" aria-label="<?php echo esc_attr__( 'Pause background video', 'cropx' ); ?>" data-label-pause="<?php echo esc_attr__( 'Pause background video', 'cropx' ); ?>" data-label-play="<?php echo esc_attr__( 'Play background video', 'cropx' ); ?>">
					<svg class="hcv-pause-icon" width="12" height="12" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true"><rect x="2" y="1" width="3" height="10" rx="0.5"/><rect x="7" y="1" width="3" height="10" rx="0.5"/></svg>
					<svg class="hcv-play-icon" width="12" height="12" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true"><path d="M3 1.2v9.6a.4.4 0 0 0 .6.35l7.6-4.8a.4.4 0 0 0 0-.7L3.6.85A.4.4 0 0 0 3 1.2Z"/></svg>
				</button>
			</div>
			<?php endif; ?>

			<!-- Quadratic-bezier swoop, identical to hero-curved-standard — see that
			     block's render.php for the full geometry notes. -->
			<div class="hcv-swoop" aria-hidden="true">
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
					<path class="hcv-fill-path" d="M0,80 Q720,80 1440,30 L1440,80 L0,80 Z"/>
					<path d="M0,70 Q720,70 1440,20" fill="none"
						stroke="url(#<?php echo esc_attr( $flow_id ); ?>)"
						stroke-width="20" stroke-linecap="round"/>
				</svg>
			</div>
		</section>
	</div>

</div>
