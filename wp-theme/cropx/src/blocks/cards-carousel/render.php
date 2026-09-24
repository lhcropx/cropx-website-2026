<?php
/**
 * Content Card Carousel block — front-end render.
 *
 * Same content-sourcing engine as the Cards block (queryMode "manual" /
 * "posts" / "auto", including auto's "single" vs "mixed" sub-types) — see
 * src/blocks/cards/render.php for the canonical write-up of each mode. This
 * block only differs in how each resolved card is *laid out*: one wide card
 * at a time (photo left, content right) in a horizontal scroll-snap
 * carousel, instead of a 3-column grid.
 *
 * Uses its own cropx_cards_carousel_card_from_post() helper (not the Cards
 * block's cropx_card_from_post()) so the two blocks never collide if both
 * appear on the same page, and so this block's extra statValue/statLabel
 * fields always survive regardless of render order.
 *
 * Stat/number overlay (e.g. "$12M in credit secured", inspired by Ambrook's
 * customer story carousel): optional per-card badge on the photo. Only
 * meaningful in "manual" and "posts" modes, where an editor is hand-picking
 * cards — there's no sensible automatic source for a stat number on a
 * generic Auto-mode blog post/case study feed, so auto-mode cards never show
 * one.
 *
 * view.js handles arrow navigation, dot generation, and scroll sync — same
 * mechanics as Testimonials Carousel's tcarousel, just renamed cccarousel
 * and with one wide card per snap position instead of three.
 *
 * Layout is full-bleed and viewport-centered (see style.css's file-level doc
 * comment for the breakout technique): the prev/next buttons live inside
 * .cccarousel-viewport, straddling the focused card's left/right edge,
 * rather than in a control bar below — matching the Ambrook reference
 * Lauren shared. Only the dots move with view.js's querySelector() since
 * they're still descendants of .cccarousel either way.
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
$query_categories = (array) ( $attributes['queryCategories'] ?? [] );
$query_auto_type = $attributes['queryAutoType']       ?? 'single';
$query_rules     = (array) ( $attributes['queryRules'] ?? [] );
$excerpt_lines = (int) ( $attributes['excerptLines']  ?? 4 );
$show_excerpt  = (bool) ( $attributes['showExcerpt']  ?? true );
$auto_advance  = (bool) ( $attributes['autoAdvance']  ?? false );

$bg_color = $attributes['bgColor'] ?? 'taupe';
if ( ! in_array( $bg_color, array( 'taupe', 'deep-blue' ), true ) ) {
	$bg_color = 'taupe';
}

$is_dark    = ( $card_variant === 'dark' );
$tag_class  = $is_dark ? 'cc-tag cc-tag--white' : 'cc-tag cc-tag--dark';
$date_class = $is_dark ? 'cc-date cc-date--dark' : 'cc-date';

// reveal-group: scroll-reveal observed root (see src/shared/scrollReveal.js
// and scroll-reveal.css) — view.js observes '.wp-block-cropx-cards-carousel',
// and this class is what the CSS keys off to cascade the fade-up onto the
// header text (.reveal-up) and each card (.reveal-item) below once this
// section scrolls into view.
$wrapper_extra_attrs = array(
	'class'                => 'cc-section cc-section--bg-' . $bg_color . ' reveal-group',
	'data-section-bg'      => $bg_color,
	'data-cc-auto-advance' => $auto_advance ? 'true' : 'false',
);
if ( 'deep-blue' === $bg_color ) {
	$wrapper_extra_attrs['style'] = '--cc-pattern-url: url(' . esc_url( CROPX_THEME_URI . 'assets/decorative/drift-pattern.svg' ) . ');';
}

$wrapper_attrs = get_block_wrapper_attributes( $wrapper_extra_attrs );

$eyebrow_color_style = ( 'deep-blue' !== $bg_color )
	? ' style="color: var(--' . esc_attr( $eyebrow_color ) . ')"'
	: '';

$allowed_inline = array(
	'em'     => array(),
	'strong' => array(),
	'br'     => array(),
);

$arrow_svg = '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">'
           . '<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
           . '</svg>';

// ── Helper: build a $card array from a WP_Post + optional overrides ────────
if ( ! function_exists( 'cropx_cards_carousel_card_from_post' ) ) :
function cropx_cards_carousel_card_from_post( $post, $overrides = array() ) {
	$post_id = $post->ID;

	$title = ! empty( $overrides['titleOverride'] )
		? $overrides['titleOverride']
		: get_the_title( $post );

	if ( ! empty( $overrides['excerptOverride'] ) ) {
		$excerpt = $overrides['excerptOverride'];
	} else {
		$excerpt = cropx_get_card_excerpt( $post );
	}

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

	$cta_label = ! empty( $overrides['ctaLabel'] ) ? $overrides['ctaLabel'] : __( 'Read more', 'cropx' );
	$cta_url   = get_permalink( $post_id );

	return array(
		'photoId'   => $image_id,
		'photoUrl'  => $image_url,
		'photoAlt'  => $image_alt,
		'tag'       => $tag,
		'tagUrl'    => $tag_url,
		'date'      => get_the_date( '', $post ),
		'title'     => $title,
		'excerpt'   => $excerpt,
		'ctaLabel'  => $cta_label,
		'ctaUrl'    => $cta_url,
		'statValue' => trim( $overrides['statValue'] ?? '' ),
		'statLabel' => trim( $overrides['statLabel'] ?? '' ),
	);
}
endif; // function_exists cropx_cards_carousel_card_from_post

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
		$cards[] = cropx_cards_carousel_card_from_post( $post, $slot );
	}

} elseif ( $query_mode === 'auto' && $query_auto_type === 'mixed' ) {

	foreach ( $query_rules as $rule ) {
		$rule_post_type = $rule['postType'] ?? 'cropx_publication';
		$rule_limit     = max( 1, (int) ( $rule['limit'] ?? 1 ) );

		$rule_args = array(
			'post_type'      => $rule_post_type,
			'posts_per_page' => $rule_limit,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if ( $rule_post_type === 'cropx_publication' && ! empty( $rule['contentTypes'] ) ) {
			$rule_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'cropx_content_type',
					'field'    => 'slug',
					'terms'    => (array) $rule['contentTypes'],
				),
			);
		} elseif ( $rule_post_type === 'post' && ! empty( $rule['categories'] ) ) {
			$rule_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'category',
					'field'    => 'slug',
					'terms'    => (array) $rule['categories'],
				),
			);
		}

		$rule_query = new WP_Query( $rule_args );
		foreach ( $rule_query->posts as $post ) {
			$cards[] = cropx_cards_carousel_card_from_post( $post );
		}
		wp_reset_postdata();
	}

} elseif ( $query_mode === 'auto' ) {

	$args = array(
		'post_type'      => $query_post_type,
		'posts_per_page' => $query_limit,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	if ( $query_post_type === 'cropx_publication' && ! empty( $content_types ) ) {
		$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => 'cropx_content_type',
				'field'    => 'slug',
				'terms'    => $content_types,
			),
		);
	}

	if ( $query_post_type === 'post' && ! empty( $query_categories ) ) {
		$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => 'category',
				'field'    => 'slug',
				'terms'    => $query_categories,
			),
		);
	}

	$query = new WP_Query( $args );
	foreach ( $query->posts as $post ) {
		$cards[] = cropx_cards_carousel_card_from_post( $post );
	}
	wp_reset_postdata();

} else {
	// queryMode === 'manual' (default)
	$cards = (array) ( $attributes['cards'] ?? array() );
}

// ── Excerpt class — line-clamp only for dynamic modes ─────────────────────
$is_dynamic   = ( $query_mode !== 'manual' );
$excerpt_class = $is_dynamic
	? 'cc-excerpt cc-excerpt--lines-' . $excerpt_lines
	: 'cc-excerpt';

?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="cc-inner">

		<?php if ( $show_header && ( $eyebrow || $heading ) ) : ?>
			<div class="cc-header">
				<?php if ( $eyebrow ) : ?>
					<span class="section-eyebrow reveal-up" style="--reveal-delay:0.05s"<?php echo $eyebrow_color_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h2 class="section-heading reveal-up" style="--reveal-delay:0.15s"><?php echo wp_kses( $heading, $allowed_inline ); ?></h2>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="cccarousel" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Content cards', 'cropx' ); ?>">
			<div class="cccarousel-viewport">
				<div class="cccarousel-track" tabindex="0">

					<?php foreach ( $cards as $card_index => $card ) :
						$card_reveal_delay = 0.3 + ( min( $card_index, 8 ) * 0.06 );
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
						$stat_value = trim( $card['statValue'] ?? '' );
						$stat_label = trim( $card['statLabel'] ?? '' );

						// For manual mode: resolve attachment URL so media-library edits propagate.
						if ( $query_mode === 'manual' && $photo_id ) {
							$src = wp_get_attachment_image_src( $photo_id, 'full' );
							if ( $src ) { $photo_url = $src[0]; }
						}

						if ( ! $photo_url && ! $title ) { continue; }

						$photo_markup = '';
						if ( $photo_id ) {
							$photo_markup = wp_get_attachment_image( $photo_id, 'large', false, array(
								'class'   => 'cc-card-img',
								'alt'     => $photo_alt,
								'loading' => 'lazy',
								'sizes'   => '(max-width: 900px) 100vw, 45vw',
							) );
						} elseif ( $photo_url ) {
							$photo_markup = '<img class="cc-card-img" src="' . esc_url( $photo_url ) . '" alt="' . esc_attr( $photo_alt ) . '"' . cropx_img_dims_attr( $photo_id, $photo_url ) . ' loading="lazy">';
						}
					?>
						<article class="cc-card reveal-item<?php echo $is_dark ? ' cc-card--dark' : ''; ?>" style="--reveal-delay:<?php echo esc_attr( $card_reveal_delay ); ?>s" role="group" aria-roledescription="slide">

							<?php if ( $photo_markup ) : ?>
								<div class="cc-card-photo">
									<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="cc-card-img-link" tabindex="-1" aria-hidden="true">
										<?php echo $photo_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</a>
									<?php if ( $stat_value || $stat_label ) : ?>
										<div class="cc-stat-badge">
											<?php if ( $stat_value ) : ?><span class="cc-stat-value"><?php echo esc_html( $stat_value ); ?></span><?php endif; ?>
											<?php if ( $stat_label ) : ?><span class="cc-stat-label"><?php echo esc_html( $stat_label ); ?></span><?php endif; ?>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<div class="cc-card-body">

								<?php if ( $tag || $date ) : ?>
									<div class="cc-meta">
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
									<h3 class="cc-title">
										<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="cc-title-link">
											<?php echo wp_kses( $title, $allowed_inline ); ?>
										</a>
									</h3>
								<?php endif; ?>

								<?php if ( $show_excerpt && $excerpt ) : ?>
									<?php if ( $is_dynamic ) : ?>
										<p class="<?php echo esc_attr( $excerpt_class ); ?>"><?php echo esc_html( $excerpt ); ?></p>
									<?php else : ?>
										<p class="cc-excerpt"><?php echo wp_kses( $excerpt, $allowed_inline ); ?></p>
									<?php endif; ?>
								<?php endif; ?>

								<?php if ( $cta_label ) : ?>
									<?php
									$cta_aria_label = $title
										? sprintf( '%s: %s', $cta_label, wp_strip_all_tags( $title ) )
										: $cta_label;
									?>
									<a href="<?php echo esc_url( cropx_url( $cta_url ) ); ?>" class="cc-cta" aria-label="<?php echo esc_attr( $cta_aria_label ); ?>">
										<?php echo esc_html( $cta_label ); ?>
										<?php echo $arrow_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</a>
								<?php endif; ?>

							</div>
						</article>
					<?php endforeach; ?>

				</div>

				<button class="cccarousel-arrow cccarousel-prev" aria-label="<?php esc_attr_e( 'Previous card', 'cropx' ); ?>">
					<svg viewBox="0 0 18 18" fill="none" aria-hidden="true"><path d="M11 4l-5 5 5 5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
				<button class="cccarousel-arrow cccarousel-next" aria-label="<?php esc_attr_e( 'Next card', 'cropx' ); ?>">
					<svg viewBox="0 0 18 18" fill="none" aria-hidden="true"><path d="M7 4l5 5-5 5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
			</div>

			<div class="cccarousel-controls">
				<div class="cccarousel-dots" role="tablist" aria-label="<?php esc_attr_e( 'Card position', 'cropx' ); ?>"></div>
			</div>
		</div>

	</div>
</section>
