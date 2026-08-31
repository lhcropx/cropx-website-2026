<?php
/**
 * Blog archive template — home.php
 *
 * WordPress uses this file when the "Posts page" is set in
 * Settings → Reading. The main query ($wp_query) is already
 * populated with posts for the current page — no need to re-query.
 *
 * Page structure:
 *   Nav
 *   ├── Blog hero block (cropx/hero-blog — placed via WP editor on the Posts page)
 *   │       └── Category filter pills live inside the hero
 *   ├── Popular posts carousel (up to 3 manually curated posts)
 *   ├── Post grid (3-col, deep blue cards with excerpt)
 *   ├── Show More (REST API load-more)
 *   ├── Newsletter CTA block
 *   ├── Demo Contact Form pattern (slug 'demo-contact-form', if it exists)
 *   └── Pre-footer CTA block + Footer
 *
 * Assets:
 *   styles/blog-archive.css    — enqueued on is_home() in inc/enqueue.php
 *   assets/js/blog-archive.js  — enqueued on is_home() in inc/enqueue.php
 */

get_header();

if ( ! have_posts() ) {
	get_footer();
	return;
}

// ── SVG helpers ────────────────────────────────────────────────────────────────
$icon_arrow_sm = '<svg width="13" height="13" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$icon_arrow_md = '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

// ── Tag colour mapping (category slug → CSS modifier) ─────────────────────────
// Add new slugs here as categories are created in WP.
$ba_tag_colors = array(
	'irrigation'            => 'ba-tag--teal',
	'precision-ag'          => 'ba-tag--teal',
	'precision-agriculture' => 'ba-tag--teal',
	'partner-stories'       => 'ba-tag--gold',
	'partners'              => 'ba-tag--gold',
	'sustainability'        => 'ba-tag--leaf',
	'environment'           => 'ba-tag--leaf',
	'disease-management'    => 'ba-tag--terra',
	'disease'               => 'ba-tag--terra',
);

/**
 * Return the CSS class for a category tag chip.
 * Defaults to ba-tag--dark (Deep Blue) if the slug isn't mapped.
 */
function cropx_ba_tag_class( $slug ) {
	global $ba_tag_colors;
	return isset( $ba_tag_colors[ $slug ] ) ? $ba_tag_colors[ $slug ] : 'ba-tag--dark';
}

// ── Gradient placeholder cycle (for posts without featured images) ─────────────
$ba_placeholders      = array( 'blue', 'wheat', 'soil', 'sky', 'grove', 'dusk', 'forest', 'gold' );
$ba_placeholder_index = 0;

// ── Popular posts query ────────────────────────────────────────────────────────
// Tries to pull up to 3 posts flagged as popular via the _cropx_is_popular meta
// (set in the post editor's "Blog Archive" sidebar panel — see inc/popular-posts.php).
// Falls back to the 3 most-recent posts if none are flagged, so the carousel is
// always populated even on a fresh install.
$ba_popular_query = new WP_Query( array(
	'post_type'           => 'post',
	'posts_per_page'      => 3,
	'no_found_rows'       => true,
	'ignore_sticky_posts' => true,
	'meta_query'          => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		array(
			'key'     => '_cropx_is_popular',
			'value'   => '1',
			'compare' => '=',
		),
	),
) );

if ( $ba_popular_query->post_count < 1 ) {
	// No flagged posts yet — fall back to most-recent so the section is never empty.
	wp_reset_postdata();
	$ba_popular_query = new WP_Query( array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	) );
}

?>

<?php cropx_render_nav( array( 'login_url' => CROPX_LOGIN_URL ) ); ?>

<?php
// ── Blog page hero ─────────────────────────────────────────────────────────────
// Any blocks placed on the "Posts page" (Settings → Reading) in the WP editor
// are rendered here. Place a cropx/hero-blog block on that page to get the full
// hero with embedded category filter pills.
$ba_posts_page_id = (int) get_option( 'page_for_posts' );
if ( $ba_posts_page_id ) {
	$ba_posts_page = get_post( $ba_posts_page_id );
	if ( $ba_posts_page && ! empty( $ba_posts_page->post_content ) ) {
		echo do_blocks( $ba_posts_page->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
?>



<!-- ═══════════════════════════════════════════════════════════════════
     BODY
═══════════════════════════════════════════════════════════════════ -->
<div class="ba-body">
	<div class="wrap">
		<main id="main-content">

			<!-- ── 3-column post grid ────────────────────────────────────────────────── -->
			<section class="ba-grid-section" aria-label="<?php esc_attr_e( 'Recent articles', 'cropx' ); ?>">

				<div class="ba-grid-header">

					<div class="ba-grid-header-text">
						<h2 class="ba-grid-heading"><?php esc_html_e( 'From the field', 'cropx' ); ?></h2>
						<p class="ba-grid-intro"><?php esc_html_e( 'Practical insights on precision irrigation, soil health, and agronomy — written by the experts behind CropX technology.', 'cropx' ); ?></p>
					</div>

					<?php
					$ba_all_cats = get_categories( array(
						'hide_empty' => true,
						'orderby'    => 'count',
						'order'      => 'DESC',
					) );
					if ( ! empty( $ba_all_cats ) ) :
					?>
					<div class="ba-grid-header-tags">
						<h3 class="ba-grid-cat-label"><?php esc_html_e( 'Categories', 'cropx' ); ?></h3>
						<div class="ba-grid-cat-pills">
							<?php foreach ( $ba_all_cats as $ba_cat ) : ?>
								<a href="<?php echo esc_url( get_category_link( $ba_cat->term_id ) ); ?>"
								   class="ba-filter-pill">
									<?php echo esc_html( $ba_cat->name ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
					<?php endif; ?>

				</div>

				<div class="ba-grid"
				     data-max-pages="<?php echo (int) $wp_query->max_num_pages; ?>"
				     data-per-page="<?php echo (int) get_option( 'posts_per_page' ); ?>">
					<?php
					rewind_posts();
					while ( have_posts() ) :
						the_post();

						$ba_grid_cats    = get_the_category();
						$ba_grid_cat     = ! empty( $ba_grid_cats ) ? $ba_grid_cats[0] : null;
						$ba_grid_thumb   = get_the_post_thumbnail_url( null, 'medium_large' );
						$ba_grid_excerpt = cropx_get_card_excerpt( null, 20 );
						$ba_ph_class     = 'ba-card-img--' . $ba_placeholders[ $ba_placeholder_index % count( $ba_placeholders ) ];
						$ba_placeholder_index++;
					?>
					<a href="<?php the_permalink(); ?>" class="ba-card">

						<div class="ba-card-img <?php echo $ba_grid_thumb ? '' : esc_attr( $ba_ph_class ); ?>">
							<?php if ( $ba_grid_thumb ) : ?>
								<img src="<?php echo esc_url( $ba_grid_thumb ); ?>"
								     alt="<?php echo esc_attr( get_the_title() ); ?>"
								     loading="lazy" decoding="async">
							<?php endif; ?>
						</div>

						<div class="ba-card-body">
							<?php if ( $ba_grid_cat ) : ?>
								<span class="ba-card-badge"><?php echo esc_html( $ba_grid_cat->name ); ?></span>
							<?php endif; ?>

							<h3 class="ba-card-title"><?php the_title(); ?></h3>
							<span class="ba-card-date"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></span>
							<p class="ba-card-excerpt"><?php echo esc_html( $ba_grid_excerpt ); ?></p>

							<span class="ba-card-read">
								<?php esc_html_e( 'Read more', 'cropx' ); ?>
								<?php echo $icon_arrow_sm; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						</div><!-- /ba-card-body -->

					</a><!-- /ba-card -->
					<?php endwhile; ?>
				</div><!-- /ba-grid -->
			</section><!-- /ba-grid-section -->


			<!-- ── Show More ──────────────────────────────────────────────────────────── -->
			<?php if ( $wp_query->max_num_pages > 1 ) : ?>
			<div class="ba-show-more">
				<button class="ba-show-more-btn" type="button">
					<span class="ba-show-more-label"><?php esc_html_e( 'Show More', 'cropx' ); ?></span>
					<svg class="ba-show-more-icon" width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
						<path d="M8 3v10M4 9l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
			</div>
			<?php endif; ?>


			<?php
			// ── Popular posts carousel ─────────────────────────────────────────────────
			if ( $ba_popular_query->have_posts() ) :
				$ba_pop_count = $ba_popular_query->post_count;
			?>
			<hr class="ba-section-break">

			<section class="ba-popular-section" aria-label="<?php esc_attr_e( 'Popular articles', 'cropx' ); ?>">

				<header class="ba-popular-header">
					<h2 class="ba-popular-label"><?php esc_html_e( 'Popular posts', 'cropx' ); ?></h2>
				</header>

				<div class="ba-popular-carousel"
				     data-popular-carousel
				     data-slide-count="<?php echo (int) $ba_pop_count; ?>">

					<div class="ba-popular-track">
						<?php
						$ba_pop_i = 0;
						while ( $ba_popular_query->have_posts() ) :
							$ba_popular_query->the_post();
							$ba_pop_cats    = get_the_category();
							$ba_pop_cat     = ! empty( $ba_pop_cats ) ? $ba_pop_cats[0] : null;
							$ba_pop_thumb   = get_the_post_thumbnail_url( null, 'large' );
							$ba_pop_excerpt = cropx_get_card_excerpt( null, 35 );
							$ba_pop_color = $ba_pop_cat ? cropx_ba_tag_class( $ba_pop_cat->slug ) : 'ba-tag--teal';
							$ba_is_first  = ( 0 === $ba_pop_i );
						?>
						<article class="ba-popular-slide <?php echo $ba_is_first ? 'ba-popular-slide--active' : ''; ?>"
						         data-slide="<?php echo $ba_pop_i; ?>"
						         aria-hidden="<?php echo $ba_is_first ? 'false' : 'true'; ?>">

							<a href="<?php the_permalink(); ?>"
							   class="ba-featured"
							   aria-label="<?php printf( esc_attr__( 'Popular post: %s', 'cropx' ), esc_attr( get_the_title() ) ); ?>">

								<div class="ba-featured-img <?php echo $ba_pop_thumb ? '' : 'ba-featured-img--placeholder'; ?>">
									<?php if ( $ba_pop_thumb ) : ?>
										<img src="<?php echo esc_url( $ba_pop_thumb ); ?>"
										     alt="<?php echo esc_attr( get_the_title() ); ?>"
										     loading="<?php echo $ba_is_first ? 'eager' : 'lazy'; ?>"
										     decoding="async">
									<?php endif; ?>
								</div><!-- /ba-featured-img -->

								<div class="ba-featured-body">

									<?php if ( $ba_pop_cat ) : ?>
										<div class="ba-featured-tag-row">
											<span class="ba-tag <?php echo esc_attr( $ba_pop_color ); ?>">
												<?php echo esc_html( $ba_pop_cat->name ); ?>
											</span>
										</div>
									<?php endif; ?>

									<h2 class="ba-featured-title"><?php echo esc_html( get_the_title() ); ?></h2>
									<p class="ba-featured-date"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></p>
									<p class="ba-featured-excerpt"><?php echo esc_html( $ba_pop_excerpt ); ?></p>

									<span class="ba-read-link">
										<?php esc_html_e( 'Read article', 'cropx' ); ?>
										<?php echo $icon_arrow_sm; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</span>

								</div><!-- /ba-featured-body -->
							</a><!-- /ba-featured -->

						</article><!-- /ba-popular-slide -->
						<?php
						$ba_pop_i++;
						endwhile;
						wp_reset_postdata();
						?>
					</div><!-- /ba-popular-track -->

					<?php if ( $ba_pop_count > 1 ) : ?>
					<div class="ba-popular-controls">

						<button class="ba-popular-arrow ba-popular-arrow--prev" type="button"
						        aria-label="<?php esc_attr_e( 'Previous slide', 'cropx' ); ?>">
							<svg width="18" height="18" viewBox="0 0 16 16" fill="none" aria-hidden="true">
								<path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5"
								      stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</button>

						<div class="ba-popular-dots" role="tablist"
						     aria-label="<?php esc_attr_e( 'Slide navigation', 'cropx' ); ?>">
							<?php for ( $d = 0; $d < $ba_pop_count; $d++ ) : ?>
								<button class="ba-popular-dot <?php echo 0 === $d ? 'ba-popular-dot--active' : ''; ?>"
								        type="button"
								        role="tab"
								        aria-selected="<?php echo 0 === $d ? 'true' : 'false'; ?>"
								        aria-label="<?php printf( esc_attr__( 'Go to slide %d', 'cropx' ), $d + 1 ); ?>"
								        data-target="<?php echo $d; ?>">
								</button>
							<?php endfor; ?>
						</div><!-- /ba-popular-dots -->

						<button class="ba-popular-arrow ba-popular-arrow--next" type="button"
						        aria-label="<?php esc_attr_e( 'Next slide', 'cropx' ); ?>">
							<svg width="18" height="18" viewBox="0 0 16 16" fill="none" aria-hidden="true">
								<path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5"
								      stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</button>

					</div><!-- /ba-popular-controls -->
					<?php endif; // slide count > 1 ?>

				</div><!-- /ba-popular-carousel -->
			</section><!-- /ba-popular-section -->
			<?php endif; // popular posts ?>

		</main><!-- /main-content -->
	</div><!-- /wrap -->
</div><!-- /ba-body -->


<?php
// ── Newsletter CTA ────────────────────────────────────────────────────────────
echo do_blocks( '<!-- wp:cropx/newsletter-cta {"bgColor":"taupe"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

// ── Demo Contact Form ─────────────────────────────────────────────────────────
// Pattern slug 'demo-contact-form' — looked up by slug so this stays correct
// across environments. Renders nothing if the pattern doesn't exist yet here.
$_demo_form_ref = cropx_get_synced_block_ref( 'demo-contact-form' );
if ( $_demo_form_ref ) {
	echo do_blocks( '<!-- wp:block {"ref":' . $_demo_form_ref . '} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

// ── Pre-footer CTA ────────────────────────────────────────────────────────────
// Blog uses the Ag Insights group's "Cat. Pg. Demo Form + CTA" Synced Pattern
// (Aug 2026, per Lauren) instead of the plain default block — same lookup-by-
// slug + fallback approach as category.php/tag.php, so nothing goes missing
// if this pattern hasn't been created yet on a given environment.
$_blog_pfc_ref = cropx_get_synced_block_ref( 'insights-cat-pg-demo-form-cta' );
echo do_blocks( $_blog_pfc_ref
	? '<!-- wp:block {"ref":' . $_blog_pfc_ref . '} /-->'
	: '<!-- wp:cropx/pre-footer-cta /-->'
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<?php get_footer(); ?>
