<?php
/**
 * Video — front-end render
 *
 * Attributes:
 *   backgroundStyle  string  'white' | 'gray'
 *   layout           string  'single' | 'two-column'
 *   layoutAlignment  string  'center' | 'left'
 *   showIntro        bool
 *   introAlignment   string  'center' | 'left'
 *   showEyebrow      bool
 *   eyebrowColor     string  'cropx-blue' | 'deep-blue'
 *   eyebrow          string
 *   heading          string
 *   body             string
 *   videos           array   [ { id, videoSource, title, desc, url, mediaId, mediaSrc, thumbnailUrl, thumbnailAlt, showCaption } ]
 */

$bg_style        = $attributes['backgroundStyle'] ?? 'white';
$display_mode    = $attributes['displayMode'] ?? 'inline';
$layout          = $attributes['layout'] ?? 'single';
$layout_align    = $attributes['layoutAlignment'] ?? 'center';
$show_intro      = $attributes['showIntro'] ?? true;
$intro_align     = $attributes['introAlignment'] ?? 'center';
$show_eyebrow    = $attributes['showEyebrow'] ?? true;

$eyebrow_color_map = [
	'cropx-blue' => 'var(--cropx-blue)',
	'deep-blue'  => 'var(--deep-blue)',
	'white'      => 'var(--white)',
];
$eyebrow_color_css = $eyebrow_color_map[ $attributes['eyebrowColor'] ?? 'cropx-blue' ] ?? 'var(--cropx-blue)';

$eyebrow = $attributes['eyebrow'] ?? '';
$heading = $attributes['heading'] ?? '';
$body    = $attributes['body'] ?? '';
$videos  = $attributes['videos'] ?? [];

// ── CSS classes ────────────────────────────────────────────────────────────
$section_class = 'cropx-video vid-bg--' . esc_attr( $bg_style );
$header_class  = 'section-header' . ( $intro_align === 'left' ? ' section-header--left' : '' );
$caption_class = 'vid-caption' . ( $layout_align === 'center' ? ' vid-caption--centered' : '' );
$grid_class    = 'vid-wrap vid-wrap--' . esc_attr( $layout ) . ' vid-wrap--' . esc_attr( $layout_align );

// ── Drift pattern (deep-blue bg only) ──────────────────────────────────────
// Inject the pattern's real asset URL via a CSS custom property instead of
// a relative url() in style.css (webpack would base64-inline the ~90KB SVG).
// edit.js sets the same property for the editor preview. Same technique as
// two-column-video.

// ── Play button SVG ────────────────────────────────────────────────────────
$play_svg = '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.14v14l11-7-11-7z"/></svg>';

$_vid_attrs = [
	'class' => esc_attr( $section_class ),
];
if ( $bg_style === 'deep-blue' ) {
	$_vid_attrs['style'] = '--vid-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');';
} else {
	$_vid_attrs['data-section-bg'] = $bg_style;
}
$wrapper_attrs = get_block_wrapper_attributes( $_vid_attrs );
?>
<section <?php echo $wrapper_attrs; ?>>
	<div class="section-inner">

		<?php if ( $show_intro ) : ?>
		<div class="<?php echo esc_attr( $header_class ); ?>">
			<?php if ( $show_eyebrow && $eyebrow ) : ?>
				<span class="section-eyebrow" style="color: <?php echo esc_attr( $eyebrow_color_css ); ?>"><?php echo esc_html( $eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( $heading ) : ?>
				<h2 class="section-heading"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>
			<?php if ( $body ) : ?>
				<div class="section-body"><?php echo wp_kses_post( $body ); ?></div>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $videos ) ) : ?>
		<div class="<?php echo esc_attr( $grid_class ); ?>">
			<?php foreach ( $videos as $index => $video ) :
				$vid_title    = esc_html( $video['title'] ?? '' );
				$vid_desc     = esc_html( $video['desc'] ?? '' );
				$video_source = $video['videoSource'] ?? 'url';
				$vid_url      = esc_url( $video['url'] ?? '' );
				$media_src    = esc_url( $video['mediaSrc'] ?? '' );
				$thumb_url    = esc_url( $video['thumbnailUrl'] ?? '' );
				$thumb_alt    = esc_attr( $video['thumbnailAlt'] ?? $video['title'] ?? '' );
				$show_caption = $video['showCaption'] ?? true;
				$is_media     = ( $video_source === 'media' && ! empty( $media_src ) );
				$has_url      = ( $video_source === 'url' && ! empty( $vid_url ) );
				$has_video    = $is_media || $has_url;

				// aria-label for linked / interactive frames
				$link_label = $vid_title
					? esc_attr( sprintf( __( 'Watch: %s', 'cropx' ), $vid_title ) )
					: esc_attr( __( 'Watch video', 'cropx' ) );
			?>
			<div class="vid-item">

				<?php
				$is_lightbox = ( $display_mode === 'lightbox' && $has_video );
				if ( $is_lightbox ) :
					$lb_data = $is_media
						? 'data-media-src="' . $media_src . '"'
						: 'data-video-url="' . $vid_url . '"';
				?>
				<div class="vid-frame vid-frame--lightbox" <?php echo $lb_data; ?> role="button" tabindex="0" aria-label="<?php echo $link_label; ?>">
				<?php elseif ( $has_url ) : ?>
				<a href="<?php echo $vid_url; ?>" class="vid-frame" data-video-url="<?php echo $vid_url; ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo $link_label; ?>">
				<?php elseif ( $is_media ) : ?>
				<div class="vid-frame" data-media-src="<?php echo $media_src; ?>" role="button" tabindex="0" aria-label="<?php echo $link_label; ?>" style="cursor:pointer;">
				<?php else : ?>
				<div class="vid-frame">
				<?php endif; ?>

					<?php if ( $thumb_url ) : ?>
						<img
							src="<?php echo $thumb_url; ?>"
							alt="<?php echo $thumb_alt; ?>"
							class="vid-thumb"
							loading="lazy"
						>
					<?php endif; ?>

					<div class="vid-play">
						<div class="vid-play-btn">
							<?php echo $play_svg; ?>
						</div>
					</div>

					<?php if ( ! $has_video ) : ?>
						<span class="vid-frame-label"><?php echo esc_html( sprintf( __( 'Video %d', 'cropx' ), $index + 1 ) ); ?></span>
					<?php endif; ?>

				<?php if ( $has_url && ! $is_lightbox ) : ?>
				</a>
				<?php else : ?>
				</div>
				<?php endif; ?>

				<?php if ( $show_caption && ( $vid_title || $vid_desc ) ) : ?>
				<div class="<?php echo esc_attr( $caption_class ); ?>">
					<?php if ( $vid_title ) : ?>
						<p class="vid-title"><?php echo $vid_title; ?></p>
					<?php endif; ?>
					<?php if ( $vid_desc ) : ?>
						<p class="vid-desc"><?php echo $vid_desc; ?></p>
					<?php endif; ?>
				</div>
				<?php endif; ?>

			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

	</div>
</section>
