<?php
/**
 * Cards block — front-end render.
 *
 * queryMode "manual"  — renders the hand-crafted $cards attribute array (original behaviour).
 * queryMode "posts"   — each slot in $manualPosts resolves a real post; optional field overrides.
 * queryMode "auto"    — WP_Query for the latest cropx_publication posts, filtered by content type.
 *
 * cardVariant "white" (default) — white card, deep-blue tags (crd-tag--dark).
 * cardVariant "dark"  — deep-blue card, white tags (crd-tag--white).
 *
 * Photos use wp_get_attachment_image() for srcset + lazy-loading.
 * Cards without both a photo AND a title are skipped.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$card_variant  = $attributes['cardVariant']       ?? 'white';
$show_header   = (bool) ( $attributes['showHeader']    ?? true );
$eyebrow       = $attributes['eyebrow']           ?? '';
$eyebrow_color = $attributes['eyebrowColor']      ?? 'cropx-blue';
$heading       = $attributes['heading']           ?? '';
$query_mode      = $attributes['queryMode']           ?? 'manual';
$query_post_type = $attributes['queryPostType']       ?? 'cropx_publication';
$query_limit     = (int) ( $attributes['queryLimit']  ?? 3 );
$content_types   = (array) ( $attributes['queryContentTypes'] ?? [] );
$excerpt_lines = (int) ( $attributes['excerptLines']  ?? 4 );

$is_dark    = ( $card_variant === 'dark' );
$tag_class  = $is_dark ? 'crd-tag crd-tag--white' : 'crd-tag crd-tag--dark';
$date_class = $is_dark ? 'crd-date crd-date--dark' : 'crd-date';

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'crd-section' ) );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);

$arrow_svg = '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">'
           . '<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
           . '</svg>';

// ── Helper: build a $card array from a WP_Post + optional overrides ────────
if ( ! function_exists( 'cropx_card_from_post' ) ) :
function cropx_card_from_post( $post, $overrides = array() ) {
	$post_id = $post->ID;

	// Title
	$title = ! empty( $overrides['titleOverride'] )
		? $overrides['titleOverride']
		: get_the_title( $post );

	// Excerpt
	if ( ! empty( $overrides['excerptOverride'] ) ) {
		$excerpt = $overrides['excerptOverride'];
	} else {
		$excerpt = get_the_excerpt( $post );
	}

	// Image: override takes precedence over featured image
	$image_id  = (int) ( $overrides['imageId'] ?? 0 );
	$image_url =       $overrides['imageUrl']  ?? '';
	$image_alt =       $overrides['imageAlt']  ?? '';

	if ( ! $image_id ) {
		$image_id = (int) get_post_thumbnail_id( $post_id );
		if ( $image_id ) {
			$src = wp_get_attachment_image_src( $image_id, 'full' );
			if ( $src ) {
				$image_url = $src[0];
			}
			$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ?: '';
		}
	}

	// Tag: use cropx_content_type for case studies, WP category for blog posts.
	$tag     = '';
	$tag_url = '#';
	$post_type = get_post_type( $post_id );
	if ( $post_type === 'post' ) {
		$cats = get_the_category( $post_id );
		if ( $cats ) {
			$cat     = reset( $cats );
			$tag     = $cat->name;
			$tag_url = get_category_link( $cat->term_id ) ?: '#';
		}
	} else {
		$terms = get_the_terms( $post_id, 'cropx_content_type' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$term    = reset( $terms );
			$tag     = $term->name;
			$tag_url = get_term_link( $term );
			if ( is_wp_error( $tag_url ) ) {
				$tag_url = '#';
			}
		}
	}

	// CTA
	$cta_label = ! empty( $overrides['ctaLabel'] ) ? $overrides['ctaLabel'] : __( 'Read more', 'cropx' );
	$cta_url   = get_permalink( $post_id );

	return array(
		'photoId'  => $image_id,
		'photoUrl' => $image_url,
		'photoAlt' => $image_alt,
		'tag'      => $tag,
		'tagUrl'   => $tag_url,
		'date'     => get_the_date( '', $post ),
		'title'    => $title,
		'excerpt'  => $excerpt,
		'ctaLabel' => $cta_label,
		'ctaUrl'   => $cta_url,
	);
}
endif; // function_exists cropx_card_from_post

// ── Build the $cards array depending on queryMode ──────────────────────────
$cards = array();

if ( $query_mode === 'posts' ) {

	$manual_posts = (array) ( $attributes['manualPosts'] ?? array() );
	foreach ( $manual_posts as $slot ) {
		$post_id = (int) ( $slot['postId'] ?? 0 );
		if ( ! $post_id ) {
			continue;
		}
		$post = get_post( $post_id );
		if ( ! $post || $post->post_status !== 'publish' ) {
			continue;
		}
		$cards[] = cropx_card_from_post( $post, $slot );
	}

} elseif ( $query_mode === 'auto' ) {

	$args = array(
		'post_type'      => $query_post_type,
		'posts_per_page' => $query_limit,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	// Content type filter only applies to case studies.
	if ( $query_post_type === 'cropx_publication' && ! empty( $content_types ) ) {
		$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => 'cropx_content_type',
				'field'    => 'slug',
				'terms'    => $content_types,
			),
		);
	}

	$query = new WP_Query( $args );
	foreach ( $query->posts as $post ) {
		$cards[] = cropx_card_from_post( $post );
	}
	wp_reset_postdata();

} else {
	// queryMode === 'manual' (default)
	$cards = (array) ( $attributes['cards'] ?? array() );
}

// ── Excerpt class — line-clamp only for dynamic modes ─────────────────────
$is_dynamic   = ( $query_mode !== 'manual' );
$excerpt_class = $is_dynamic
	? 'crd-excerpt crd-excerpt--lines-' . $excerpt_lines
	: 'crd-excerpt';

?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="crd-inner">

		<?php if ( $show_header && ( $eyebrow || $heading ) ) : ?>
			<div class="crd-header">
				<?php if ( $eyebrow ) : ?>
					<span class="crd-eyebrow" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h2 class="crd-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="crd-grid">
			<?php foreach ( $cards as $card ) :
				$photo_id  = (int)  ( $card['photoId']  ?? 0 );
				$photo_url =         $card['photoUrl']  ?? '';
				$photo_alt =         $card['photoAlt']  ?? '';
				$tag       =         $card['tag']       ?? '';
				$tag_url   =         $card['tagUrl']    ?? '#';
				$date      =         $card['date']      ?? '';
				$title     =         $card['title']     ?? '';
				$excerpt   =         $card['excerpt']   ?? '';
				$cta_label =         $card['ctaLabel']  ?? __( 'Read more', 'cropx' );
				$cta_url   =         $card['ctaUrl']    ?? '#';

				// For manual mode: resolve attachment URL so media-library edits propagate.
				if ( $query_mode === 'manual' && $photo_id ) {
					$src = wp_get_attachment_image_src( $photo_id, 'full' );
					if ( $src ) { $photo_url = $src[0]; }
				}

				if ( ! $photo_url && ! $title ) { continue; }

				// Photo markup — prefer attachment ID for srcset.
				$photo_markup = '';
				if ( $photo_id ) {
					$photo_markup = wp_get_attachment_image( $photo_id, 'large', false, array(
						'class'   => 'crd-card-img',
						'alt'     => $photo_alt,
						'loading' => 'lazy',
						'sizes'   => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
					) );
				} elseif ( $photo_url ) {
					$photo_markup = '<img class="crd-card-img" src="' . esc_url( $photo_url ) . '" alt="' . esc_attr( $photo_alt ) . '" loading="lazy">';
				}
			?>
				<article class="crd-card<?php echo $is_dark ? ' crd-card--dark' : ''; ?>">

					<?php if ( $photo_markup ) : ?>
						<div class="crd-card-img-wrap">
							<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="crd-card-img-link" tabindex="-1" aria-hidden="true">
								<?php echo $photo_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						</div>
					<?php endif; ?>

					<div class="crd-card-body">

						<?php if ( $tag || $date ) : ?>
							<div class="crd-meta">
								<?php if ( $tag ) : ?>
									<a href="<?php echo esc_url( cropx_url( $tag_url ) ); ?>" class="<?php echo esc_attr( $tag_class ); ?>">
										<?php echo esc_html( $tag ); ?>
									</a>
								<?php endif; ?>
								<?php if ( $date ) : ?>
									<span class="<?php echo esc_attr( $date_class ); ?>">
										<?php echo esc_html( $date ); ?>
									</span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( $title ) : ?>
							<h3 class="crd-title">
								<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="crd-title-link">
									<?php echo wp_kses( $title, $allowed_inline ); ?>
								</a>
							</h3>
						<?php endif; ?>

						<?php if ( $excerpt ) : ?>
							<?php if ( $is_dynamic ) : ?>
								<p class="<?php echo esc_attr( $excerpt_class ); ?>"><?php echo esc_html( $excerpt ); ?></p>
							<?php else : ?>
								<p class="crd-excerpt"><?php echo wp_kses( $excerpt, $allowed_inline ); ?></p>
							<?php endif; ?>
						<?php endif; ?>

						<?php if ( $cta_label ) : ?>
							<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="crd-cta">
								<?php echo esc_html( $cta_label ); ?>
								<?php echo $arrow_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						<?php endif; ?>

					</div>
				</article>
			<?php endforeach; ?>
		</div>

	</div>
</section>
