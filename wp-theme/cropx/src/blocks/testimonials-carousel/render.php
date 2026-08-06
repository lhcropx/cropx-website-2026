<?php
/**
 * Testimonials Carousel block — front-end render.
 *
 * Horizontal scroll-snap row of testimonial cards. 5 cards by default; editors
 * can add or remove via InspectorControls. view.js handles arrow navigation,
 * dot generation, and scroll sync — the dots container is rendered empty.
 *
 * Photo: wp_get_attachment_image() for srcset + lazy-loading.
 * Initials fallback: up to 2 initials extracted from authorName. Renders "?"
 * when authorName is empty so the slot is never visually collapsed.
 *
 * CSS adds curly quotes via ::before/::after on .testimonial-quote p.
 * Editors should NOT type quotation marks in the quote field.
 *
 * Section header: eyebrow and heading are each skipped when empty after trim.
 * The entire header block is omitted when both are empty.
 *
 * Content source:
 *   manual — testimonial data stored directly in block attributes.
 *   pick   — picks specific cropx_testimonial CPT posts in editor-defined order.
 *   auto   — WP_Query by optional category, newest first.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$eyebrow        = trim( $attributes['eyebrow']       ?? '' );
$heading        = trim( $attributes['heading']        ?? '' );
$eyebrow_color  = $attributes['eyebrowColor']         ?? 'cropx-blue';
$show_eyebrow   = (bool) ( $attributes['showEyebrow'] ?? true );
$content_source = $attributes['contentSource']        ?? 'manual';

$bg_color = $attributes['bgColor'] ?? 'taupe';
if ( ! in_array( $bg_color, array( 'taupe', 'white' ), true ) ) {
	$bg_color = 'taupe';
}

$auto_advance = (bool) ( $attributes['autoAdvance'] ?? false );

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class'                => 'testimonials-section testimonials-section--' . $bg_color,
	'data-section-bg'      => $bg_color,
	'data-tc-auto-advance' => $auto_advance ? 'true' : 'false',
) );

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);

// Scoped closure — safe for multiple block instances on the same page.
$tc_initials = static function ( string $name ): string {
	$name = trim( $name );
	if ( ! $name ) { return '?'; }
	$words    = preg_split( '/\s+/', $name );
	$initials = '';
	foreach ( array_slice( $words, 0, 2 ) as $word ) {
		if ( $word ) { $initials .= strtoupper( $word[0] ); }
	}
	return $initials ?: '?';
};

// ── Build the $testimonials array for rendering ─────────────────────────────
$testimonials = array();

if ( $content_source === 'manual' ) {

	// Manual: use data stored directly in block attributes.
	$testimonials = $attributes['testimonials'] ?? array();

} elseif ( $content_source === 'pick' ) {

	// Pick: selected CPT posts in the exact order chosen by the editor.
	$ids = array_filter( array_map( 'intval', $attributes['testimonialIds'] ?? array() ) );
	if ( $ids ) {
		$q = new WP_Query( array(
			'post_type'              => 'cropx_testimonial',
			'post__in'               => $ids,
			'orderby'                => 'post__in',  // respect editor order
			'posts_per_page'         => count( $ids ),
			'post_status'            => 'publish',
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
		) );
		foreach ( $q->posts as $post ) {
			$photo_id = (int) get_post_thumbnail_id( $post->ID );
			$testimonials[] = array(
				'quote'       => get_post_meta( $post->ID, 'quote_text',   true ) ?: '',
				'authorName'  => $post->post_title,
				'authorTitle' => get_post_meta( $post->ID, 'attribution',  true ) ?: '',
				'photoId'     => $photo_id,
				'photoAlt'    => $post->post_title,
			);
		}
		wp_reset_postdata();
	}

} elseif ( $content_source === 'auto' ) {

	// Auto: newest testimonials first, optional category filter.
	$category = trim( $attributes['testimonialCategory'] ?? '' );
	$limit    = max( 1, (int) ( $attributes['testimonialLimit'] ?? 5 ) );

	$query_args = array(
		'post_type'              => 'cropx_testimonial',
		'posts_per_page'         => $limit,
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'post_status'            => 'publish',
		'no_found_rows'          => true,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => (bool) $category,
	);

	if ( $category ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy' => 'cropx_testimonial_category',
				'field'    => 'slug',
				'terms'    => $category,
			),
		);
	}

	$q = new WP_Query( $query_args );
	foreach ( $q->posts as $post ) {
		$photo_id = (int) get_post_thumbnail_id( $post->ID );
		$testimonials[] = array(
			'quote'       => get_post_meta( $post->ID, 'quote_text',  true ) ?: '',
			'authorName'  => $post->post_title,
			'authorTitle' => get_post_meta( $post->ID, 'attribution', true ) ?: '',
			'photoId'     => $photo_id,
			'photoAlt'    => $post->post_title,
		);
	}
	wp_reset_postdata();
}
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php if ( ( $show_eyebrow && $eyebrow ) || $heading ) : ?>
		<div class="testimonials-inner">
			<div class="testimonials-header">
				<?php if ( $show_eyebrow && $eyebrow ) : ?>
					<span class="section-eyebrow" style="color: var(--<?php echo esc_attr( $eyebrow_color ); ?>)"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h2 class="section-heading"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="tcarousel" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Customer testimonials', 'cropx' ); ?>">
		<div class="tcarousel-viewport">
			<div class="tcarousel-track" tabindex="0">

				<?php foreach ( $testimonials as $t ) :
					$quote        = trim( $t['quote']       ?? '' );
					$author_name  = trim( $t['authorName']  ?? '' );
					$author_title = trim( $t['authorTitle'] ?? '' );
					$photo_id     = (int) ( $t['photoId']   ?? 0 );
					$photo_alt    = $t['photoAlt'] ?? '';

					if ( $photo_id ) {
						$photo_markup = wp_get_attachment_image( $photo_id, 'cropx-testimonial-avatar', false, array(
							'alt'     => $photo_alt,
							'loading' => 'lazy',
						) );
					} else {
						$photo_markup = '';
					}
				?>
					<article class="testimonial-card" role="group" aria-roledescription="slide">

						<?php if ( $quote ) : ?>
							<div class="testimonial-quote">
								<p><?php echo wp_kses( $quote, $allowed_inline ); ?></p>
							</div>
						<?php endif; ?>

						<div class="testimonial-author">
							<div class="author-icon" aria-hidden="true">
								<?php if ( $photo_markup ) : ?>
									<?php echo $photo_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php else : ?>
									<span class="author-initials"><?php echo esc_html( $tc_initials( $author_name ) ); ?></span>
								<?php endif; ?>
							</div>
							<div>
								<?php if ( $author_name ) : ?>
									<p class="author-name"><?php echo esc_html( $author_name ); ?></p>
								<?php endif; ?>
								<?php if ( $author_title ) : ?>
									<p class="author-title"><?php echo esc_html( $author_title ); ?></p>
								<?php endif; ?>
							</div>
						</div>

					</article>
				<?php endforeach; ?>

			</div>
		</div>

		<div class="tcarousel-controls">
			<button class="tcarousel-arrow tcarousel-prev" aria-label="<?php esc_attr_e( 'Previous testimonial', 'cropx' ); ?>">
				<svg viewBox="0 0 18 18" fill="none" aria-hidden="true"><path d="M11 4l-5 5 5 5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
			<div class="tcarousel-dots" role="tablist" aria-label="<?php esc_attr_e( 'Testimonial position', 'cropx' ); ?>"></div>
			<button class="tcarousel-arrow tcarousel-next" aria-label="<?php esc_attr_e( 'Next testimonial', 'cropx' ); ?>">
				<svg viewBox="0 0 18 18" fill="none" aria-hidden="true"><path d="M7 4l5 5-5 5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
		</div>
	</div>

</section>
