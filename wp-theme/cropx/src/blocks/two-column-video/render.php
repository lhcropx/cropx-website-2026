<?php
/**
 * Two-Column Text + Video block — front-end render.
 *
 * Text content (icon, eyebrow, heading, InnerBlocks body, CTA) on one side;
 * a single video (YouTube/Vimeo embed or media library file) on the other.
 *
 * Attributes:
 *   videoPosition   string  'right' | 'left'
 *   segmentAccent   string  'general' | 'enterprise' | 'service-provider' | 'on-farm'
 *   icon            string  icon slug
 *   eyebrow         string
 *   heading         string
 *   ctaLabel        string
 *   ctaUrl          string
 *   eyebrowColor    string  'cropx-blue' | 'deep-blue' | 'white'
 *   ctaStyle        string  'button' | 'link'
 *   showIcon        bool
 *   showEyebrow     bool
 *   showCta         bool
 *   bgColor         string  'taupe' | 'white'
 *   mobileStack     string  'visual-first' | 'text-first'
 *   displayMode     string  'inline' | 'lightbox'
 *   videoSource     string  'url' | 'media'
 *   videoUrl        string  YouTube / Vimeo URL
 *   videoMediaId    int
 *   videoMediaSrc   string
 *   thumbnailUrl    string
 *   thumbnailAlt    string
 *   showCaption     bool
 *   videoTitle      string
 *   videoDesc       string
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$video_position = $attributes['videoPosition'] ?? 'right';
$segment_accent = $attributes['segmentAccent'] ?? 'general';
$icon           = $attributes['icon']          ?? 'field-sun';
$show_icon      = (bool) ( $attributes['showIcon']    ?? true );
$show_eyebrow   = (bool) ( $attributes['showEyebrow'] ?? true );
$show_cta       = (bool) ( $attributes['showCta']     ?? true );
$eyebrow        = $attributes['eyebrow']    ?? '';
$heading        = $attributes['heading']    ?? '';
$body           = $attributes['body']       ?? '';
$cta_label      = $attributes['ctaLabel']   ?? '';
$cta_url        = $attributes['ctaUrl']     ?? '#';
$cta_style      = $attributes['ctaStyle']   ?? 'button';
$eyebrow_color  = $attributes['eyebrowColor'] ?? 'cropx-blue';
$bg_color       = $attributes['bgColor']    ?? 'taupe';
$mobile_stack   = $attributes['mobileStack'] ?? 'visual-first';

// Video attributes.
$display_mode    = $attributes['displayMode']    ?? 'inline';
$video_source    = $attributes['videoSource']    ?? 'url';
$video_url       = $attributes['videoUrl']       ?? '';
$video_media_id  = (int) ( $attributes['videoMediaId']  ?? 0 );
$video_media_src = $attributes['videoMediaSrc']  ?? '';
$thumbnail_url   = $attributes['thumbnailUrl']   ?? '';
$thumbnail_alt   = $attributes['thumbnailAlt']   ?? '';
$show_caption    = (bool) ( $attributes['showCaption'] ?? false );
$video_title     = $attributes['videoTitle']     ?? '';
$video_desc      = $attributes['videoDesc']      ?? '';
$caption_align   = $attributes['captionAlignment'] ?? 'left';

// Validate enums.
if ( ! in_array( $video_position, array( 'right', 'left' ), true ) ) { $video_position = 'right'; }
if ( ! in_array( $segment_accent, array( 'general', 'enterprise', 'service-provider', 'on-farm' ), true ) ) { $segment_accent = 'general'; }
if ( ! in_array( $bg_color, array( 'taupe', 'white', 'deep-blue' ), true ) ) { $bg_color = 'taupe'; }
if ( ! in_array( $display_mode, array( 'inline', 'lightbox' ), true ) ) { $display_mode = 'inline'; }
if ( ! in_array( $video_source, array( 'url', 'media' ), true ) ) { $video_source = 'url'; }
if ( ! in_array( $caption_align, array( 'left', 'center' ), true ) ) { $caption_align = 'left'; }

// Matches the "1- or 2-Column Video Showcase" block's caption-alignment pattern exactly.
$caption_class = 'vid-caption' . ( 'center' === $caption_align ? ' vid-caption--centered' : '' );
$allowed_icons = array( 'alarm-clock', 'antenna', 'corn', 'field-sun', 'fields', 'language', 'nutrition', 'sensor-cloud', 'speed', 'valve-irrigation' );
if ( ! in_array( $icon, $allowed_icons, true ) ) { $icon = 'field-sun'; }

// Derived flags.
$is_media  = ( 'media' === $video_source && ! empty( $video_media_src ) );
$has_url   = ( 'url'   === $video_source && ! empty( $video_url ) );
$has_video = $is_media || $has_url;

// Build section class.
$section_class = 'tcvid-section';
if ( 'left'    === $video_position )  { $section_class .= ' tcvid-section--visual-left'; }
if ( 'general' !== $segment_accent )  { $section_class .= ' tcvid-segment-' . $segment_accent; }
if ( 'text-first' === $mobile_stack ) { $section_class .= ' tcvid-section--mobile-text-first'; }
$section_class .= ' tcvid-section--bg-' . $bg_color;

$wrapper_extra_attrs = array( 'class' => $section_class, 'data-section-bg' => $bg_color );

// Deep-blue topo overlay: inject the pattern's real asset URL via a CSS
// custom property instead of letting the CSS reference it by relative path.
// A relative url() in style.css gets base64-inlined by webpack (the SVG is
// ~90KB — well past the size where that's a good idea), ballooning
// style-index.css to ~127KB. That's large enough that it silently failed to
// overwrite during a real deploy via WP File Manager's zip extraction, while
// every smaller file in the same block folder (block.json, index.js, etc.)
// updated fine — the deep-blue feature looked "not deployed" even though the
// right zip was uploaded. Keeping this asset out of the CSS bundle avoids the
// whole class of problem. edit.js sets the same property for the editor preview.
if ( 'deep-blue' === $bg_color ) {
	$wrapper_extra_attrs['style'] = '--tcvid-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');';
}

$wrapper_attrs = get_block_wrapper_attributes( $wrapper_extra_attrs );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);

// aria-label for the video frame link / button.
$vid_esc_title = $video_title ? esc_attr( $video_title ) : '';
$link_label    = $vid_esc_title
	? esc_attr( sprintf( __( 'Watch: %s', 'cropx' ), $video_title ) )
	: esc_attr( __( 'Watch video', 'cropx' ) );

$play_svg = '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.14v14l11-7-11-7z"/></svg>';
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="tcvid-inner">
		<div class="tcvid-grid">

			<?php /* ── Text column ── */ ?>
			<div class="tcvid-content">
				<?php if ( $show_icon ) : ?>
				<div class="tcvid-icon-wrap">
					<div class="tcvid-icon" aria-hidden="true">
						<img
							src="<?php echo esc_url( CROPX_THEME_URI . 'assets/icons/' . $icon . '.svg' ); ?>"
							alt=""
							width="28"
							height="28"
						>
					</div>
				</div>
				<?php endif; ?>

				<?php if ( $eyebrow && $show_eyebrow ) : ?>
					<span class="section-eyebrow" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( wp_strip_all_tags( $eyebrow ) ); ?></span>
				<?php endif; ?>

				<?php if ( $heading ) : ?>
					<h2 class="section-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
				<?php endif; ?>

				<?php
				// $content holds serialized inner blocks (paragraphs, lists, etc.);
				// fall back to legacy $body attribute so existing blocks keep their content.
				$has_inner = ! empty( trim( strip_tags( $content ) ) );
				if ( $has_inner ) :
				?>
					<div class="section-body"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php elseif ( $body ) : ?>
					<div class="section-body"><?php echo wp_kses_post( $body ); ?></div>
				<?php endif; ?>

				<?php if ( $cta_label && $show_cta ) : ?>
					<?php if ( 'link' === $cta_style ) : ?>
						<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="tcvid-link">
							<?php echo esc_html( $cta_label ); ?>
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
								<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="tcvid-cta">
							<?php echo esc_html( $cta_label ); ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<?php /* ── Video column ── */ ?>
			<div class="tcvid-visual">
				<?php
				$is_lightbox = ( 'lightbox' === $display_mode && $has_video );
				if ( $is_lightbox ) :
					$lb_data = $is_media
						? 'data-media-src="' . esc_url( $video_media_src ) . '"'
						: 'data-video-url="' . esc_url( $video_url ) . '"';
				?>
				<div class="vid-frame vid-frame--lightbox" <?php echo $lb_data; ?> role="button" tabindex="0" aria-label="<?php echo $link_label; ?>">
				<?php elseif ( $has_url ) : ?>
				<a href="<?php echo esc_url( $video_url ); ?>" class="vid-frame" data-video-url="<?php echo esc_url( $video_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo $link_label; ?>">
				<?php elseif ( $is_media ) : ?>
				<div class="vid-frame" data-media-src="<?php echo esc_url( $video_media_src ); ?>" role="button" tabindex="0" aria-label="<?php echo $link_label; ?>" style="cursor:pointer;">
				<?php else : ?>
				<div class="vid-frame">
				<?php endif; ?>

					<?php if ( $thumbnail_url ) : ?>
						<img
							src="<?php echo esc_url( $thumbnail_url ); ?>"
							alt="<?php echo esc_attr( $thumbnail_alt ); ?>"
							class="vid-thumb"
							loading="lazy"
						>
					<?php endif; ?>

					<div class="vid-play">
						<div class="vid-play-btn">
							<?php echo $play_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>

					<?php if ( ! $has_video ) : ?>
						<span class="vid-frame-label"><?php echo esc_html( __( 'Video', 'cropx' ) ); ?></span>
					<?php endif; ?>

				<?php if ( $has_url && ! $is_lightbox ) : ?>
				</a>
				<?php else : ?>
				</div>
				<?php endif; ?>

				<?php if ( $show_caption && ( $video_title || $video_desc ) ) : ?>
				<div class="<?php echo esc_attr( $caption_class ); ?>">
					<?php if ( $video_title ) : ?>
						<p class="vid-title"><?php echo esc_html( $video_title ); ?></p>
					<?php endif; ?>
					<?php if ( $video_desc ) : ?>
						<p class="vid-desc"><?php echo esc_html( $video_desc ); ?></p>
					<?php endif; ?>
				</div>
				<?php endif; ?>

			</div>

		</div>
	</div>
</section>
