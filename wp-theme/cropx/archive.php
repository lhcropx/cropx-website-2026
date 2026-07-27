<?php
/**
 * Tag / date archive template — archive.php
 *
 * WordPress routes here for /tag/slug/, date archives, etc. Category archives
 * (/category/slug/) now use the more specific category.php instead — see
 * inc/insights-archive.php — since they need the Ag Insights & Research
 * design (agr-* / styles/ag-archive.css), not this file's ba-* blog design.
 * The category-pill code below is effectively dead for categories now; left
 * in place only because /tag/ archives still fall through to this file and
 * historically shared the same code path.
 *
 * The main $wp_query is already filtered by WordPress — no re-query needed.
 *
 * Uses the same ba-* classes and layout as home.php so blog-archive.css
 * covers everything. Key differences from home.php:
 *   - No hero block — replaced with a simple section header
 *   - Category filter pills include an "All" pill + active state on current category
 *   - No popular posts carousel (it isn't filtered to the category, so skip it)
 *   - data-category-id on .ba-grid so Load More JS can keep the filter applied
 *
 * Assets:
 *   styles/blog-archive.css   — enqueued on is_category() in inc/enqueue.php
 *   assets/js/blog-archive.js — enqueued on is_category() in inc/enqueue.php
 */

get_header();

if ( ! have_posts() ) {
	get_footer();
	return;
}

// ── SVG helpers ────────────────────────────────────────────────────────────────
$icon_arrow_sm = '<svg width="13" height="13" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

// ── Tag colour mapping — keep in sync with home.php and blog-archive.js ────────
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

if ( ! function_exists( 'cropx_ba_tag_class' ) ) {
	function cropx_ba_tag_class( $slug ) {
		global $ba_tag_colors;
		return isset( $ba_tag_colors[ $slug ] ) ? $ba_tag_colors[ $slug ] : 'ba-tag--dark';
	}
}

// ── Gradient placeholder cycle ─────────────────────────────────────────────────
$ba_placeholders      = array( 'blue', 'wheat', 'soil', 'sky', 'grove', 'dusk', 'forest', 'gold' );
$ba_placeholder_index = 0;

// ── Queried object — determine active category ─────────────────────────────────
$queried_obj    = get_queried_object();
$is_cat_archive = $queried_obj instanceof WP_Term && 'category' === $queried_obj->taxonomy;
$current_cat_id = $is_cat_archive ? (int) $queried_obj->term_id : 0;

// Strip "Category: " etc. from the default archive title
$archive_title = get_the_archive_title();
$archive_title = preg_replace( '/^[^:]+:\s*/', '', $archive_title );
$archive_desc  = get_the_archive_description();

// ── Blog archive URL (the "All" filter target) ─────────────────────────────────
$blog_archive_url = get_permalink( (int) get_option( 'page_for_posts' ) ) ?: home_url( '/' );

?>

<?php cropx_render_nav( array( 'login_url' => '#' ) ); ?>


<!-- ═══════════════════════════════════════════════════════════════════
     BODY
═══════════════════════════════════════════════════════════════════ -->
<div class="ba-body">
	<div class="wrap">
		<main id="main-content">

			<!-- ── 3-column post grid ────────────────────────────────────────────────── -->
			<section class="ba-grid-section" aria-label="<?php esc_attr_e( 'Articles', 'cropx' ); ?>">

				<div class="ba-grid-header">

					<div class="ba-grid-header-text">
						<h2 class="ba-grid-heading"><?php echo esc_html( $archive_title ); ?></h2>
						<?php if ( $archive_desc ) : ?>
							<p class="ba-grid-intro"><?php echo esc_html( $archive_desc ); ?></p>
						<?php endif; ?>
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
							<a href="<?php echo esc_url( $blog_archive_url ); ?>"
							   class="ba-filter-pill">
								<?php esc_html_e( 'All', 'cropx' ); ?>
							</a>
							<?php foreach ( $ba_all_cats as $ba_cat ) : ?>
								<a href="<?php echo esc_url( get_category_link( $ba_cat->term_id ) ); ?>"
								   class="ba-filter-pill<?php echo ( $is_cat_archive && (int) $ba_cat->term_id === $current_cat_id ) ? ' ba-filter-pill--active' : ''; ?>">
									<?php echo esc_html( $ba_cat->name ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
					<?php endif; ?>

				</div>

				<div class="ba-grid"
				     data-max-pages="<?php echo (int) $wp_query->max_num_pages; ?>"
				     data-per-page="<?php echo (int) get_option( 'posts_per_page' ); ?>"
				     data-category-id="<?php echo $current_cat_id; ?>">

					<?php while ( have_posts() ) : the_post();

						$ba_grid_cats    = get_the_category();
						$ba_grid_cat     = ! empty( $ba_grid_cats ) ? $ba_grid_cats[0] : null;
						$ba_grid_thumb   = get_the_post_thumbnail_url( null, 'medium_large' );
						$ba_grid_excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words( get_the_content(), 20 );
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

		</main><!-- /main-content -->
	</div><!-- /wrap -->
</div><!-- /ba-body -->


<?php
// ── Newsletter CTA ────────────────────────────────────────────────────────────
echo do_blocks( '<!-- wp:cropx/newsletter-cta {"bgColor":"taupe"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

// ── Pre-footer CTA ────────────────────────────────────────────────────────────
echo do_blocks( '<!-- wp:cropx/pre-footer-cta /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<?php get_footer(); ?>
