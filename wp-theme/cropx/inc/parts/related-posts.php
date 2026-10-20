<?php
/**
 * Related posts template part.
 *
 * Renders a "More from CropX" section with up to 3 posts from the same
 * category as the current post, padded with the most recent posts if needed.
 *
 * Called from single.php via:
 *   get_template_part( 'inc/parts/related-posts' );
 *
 * Requires: must be called while in the post loop (get_the_ID() must work).
 */

$current_id      = get_the_ID();
$categories      = get_the_category();
$related_cat_ids = $categories ? wp_list_pluck( $categories, 'term_id' ) : array();

// Blog archive URL — uses the "Posts page" if set, otherwise /blog.
$posts_page = (int) get_option( 'page_for_posts' );
$blog_url   = $posts_page ? get_permalink( $posts_page ) : home_url( '/blog' );

// Arrow icon — matches cards/render.php exactly.
$icon_arrow = '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">'
            . '<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
            . '</svg>';

// Query: same-category posts, most recent first.
$related_query = new WP_Query( array(
	'posts_per_page'      => 3,
	'post__not_in'        => array( $current_id ),
	'category__in'        => $related_cat_ids,
	'orderby'             => 'date',
	'order'               => 'DESC',
	'ignore_sticky_posts' => true,
) );

// Pad to 3 with other recent posts if the same-category pool is thin.
if ( $related_query->post_count < 3 ) {
	$found_ids = array_merge( array( $current_id ), wp_list_pluck( $related_query->posts, 'ID' ) );
	$pad_query = new WP_Query( array(
		'posts_per_page'      => 3 - $related_query->post_count,
		'post__not_in'        => $found_ids,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
	) );
	$related_query->posts      = array_merge( $related_query->posts, $pad_query->posts );
	$related_query->post_count = count( $related_query->posts );
	wp_reset_postdata();
}

if ( ! $related_query->have_posts() ) {
	return;
}
?>

<hr class="bsingle-section-divider">

<section class="bsingle-related-posts">
	<div class="wrap">

		<div class="bsingle-related-posts-head">
			<div>
				<span class="bsingle-related-posts-eyebrow"><?php esc_html_e( 'Keep Reading', 'cropx' ); ?></span>
				<h2 class="bsingle-related-posts-title"><?php esc_html_e( 'More from CropX', 'cropx' ); ?></h2>
			</div>
			<a href="<?php echo esc_url( $blog_url ); ?>" class="bsingle-view-all">
				<?php esc_html_e( 'View all posts', 'cropx' ); ?>
				<?php echo $icon_arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>

		<div class="crd-grid">
			<?php while ( $related_query->have_posts() ) : $related_query->the_post(); ?>
				<?php
				$rel_cats     = get_the_category();
				$rel_cat      = $rel_cats ? $rel_cats[0] : null;
				$rel_thumb_id = (int) get_post_thumbnail_id();
				$rel_thumb    = $rel_thumb_id
					? wp_get_attachment_image( $rel_thumb_id, 'large', false, array(
						'class'   => 'crd-card-img',
						'loading' => 'lazy',
						'sizes'   => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
					) )
					: '';
				$rel_exc = cropx_get_card_excerpt();
				?>
				<article class="crd-card crd-card--dark">

					<?php if ( $rel_thumb ) : ?>
						<div class="crd-card-img-wrap">
							<a href="<?php the_permalink(); ?>" class="crd-card-img-link" tabindex="-1" aria-hidden="true">
								<?php echo $rel_thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						</div>
					<?php endif; ?>

					<div class="crd-card-body">

						<?php if ( $rel_cat ) : ?>
							<div class="crd-meta">
								<a href="<?php echo esc_url( get_category_link( $rel_cat->term_id ) ); ?>" class="crd-tag crd-tag--white">
									<?php echo esc_html( $rel_cat->name ); ?>
								</a>
							</div>
						<?php endif; ?>

						<h3 class="crd-title crd-title--lines-2">
							<a href="<?php the_permalink(); ?>" class="crd-title-link"><?php the_title(); ?></a>
						</h3>

						<?php if ( $rel_exc ) : ?>
							<p class="crd-excerpt crd-excerpt--lines-3"><?php echo esc_html( $rel_exc ); ?></p>
						<?php endif; ?>

						<a href="<?php the_permalink(); ?>" class="crd-cta">
							<?php esc_html_e( 'Read more', 'cropx' ); ?>
							<?php echo $icon_arrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>

					</div>

				</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>

	</div>
</section>
