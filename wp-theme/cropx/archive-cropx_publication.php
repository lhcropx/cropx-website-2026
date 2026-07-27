<?php
/**
 * RETIRED — no longer loaded by anything as of the "Customer Results" Page
 * rebuild. has_archive is now false for cropx_publication (see inc/cpts.php),
 * so is_post_type_archive( 'cropx_publication' ) never fires, and
 * taxonomy-cropx_content_type.php now renders the "results" Page's content
 * directly instead of get_template_part()-ing this file.
 *
 * The grid/pill/badge logic here was ported into the
 * [cropx_customer_stories_grid] shortcode in inc/customer-stories.php —
 * see page-results.php and taxonomy-cropx_content_type.php for the current
 * implementation. This file is kept only for reference and can be deleted
 * once you're confident nothing still points at it.
 *
 * ─── Original doc comment below, for context ───────────────────────────────
 *
 * Customer Results archive template — archive-cropx_publication.php
 *
 * Public-facing page name: "Customer Results" (the CPT itself is labeled
 * "Customer Stories" in wp-admin — this archive keeps the /results/ URL and
 * pairs it with a name that reads naturally at that address).
 *
 * Also used by taxonomy-cropx_content_type.php (via get_template_part) so
 * filtered views (/content-type/case-study/, /content-type/video-testimonial/)
 * share the same layout with the correct filter pill highlighted.
 *
 * Structure mirrors home.php (blog archive) exactly — same sections, same card
 * design — but sources data from the cropx_publication CPT and cropx_content_type
 * taxonomy instead of standard posts and WP categories.
 *
 * Content types: Case Study and Video Testimonial (White Paper moved to the
 * cropx_resource CPT — see inc/cpts.php). Any pre-existing "white-paper" term
 * simply stops appearing as a filter pill once no customer stories use it.
 *
 * WordPress routes:
 *   /results/                   → is_post_type_archive( 'cropx_publication' )
 *   /content-type/{term}/       → is_tax( 'cropx_content_type' )
 *
 * Single customer stories live at /results/{content-type-slug}/{post-name}/
 * (e.g. /results/case-study/my-annual-report/) — see the post_type_link
 * filter + rewrite rule in inc/cpts.php.
 *
 * Page structure:
 *   Nav
 *   ├── Archive hero  (blocks from page set via WP option cropx_pub_archive_page_id)
 *   ├── Customer stories grid section
 *   │     ├── Two-column header: title + filter pills (All / Case Studies / Video Testimonials)
 *   │     └── 3-column white card grid
 *   ├── Show More  (REST API load-more — pub-archive.js)
 *   ├── Section break
 *   ├── Featured stories carousel  (mirrors "popular posts" on blog archive)
 *   ├── Newsletter CTA block
 *   └── Pre-footer CTA block + Footer
 *
 * Hero setup:
 *   1. Create a WP page, add your hero block, publish it.
 *   2. Note the page ID.
 *   3. Run in WP CLI or a one-time snippet:
 *        update_option( 'cropx_pub_archive_page_id', YOUR_PAGE_ID );
 *   The page's blocks will then render here on every archive load.
 *
 * Featured carousel setup:
 *   Flag a customer story as featured by setting post meta _cropx_is_featured = 1.
 *   Falls back to the 3 most-recent customer stories if none are flagged.
 *
 * Assets:
 *   styles/pub-archive.css      — enqueued on archive/tax in inc/enqueue.php
 *   assets/js/pub-archive.js    — enqueued + localized in inc/enqueue.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// ── SVG icon helpers ───────────────────────────────────────────────────────────

$icon_arrow_sm = '<svg width="13" height="13" viewBox="0 0 16 16" fill="none" aria-hidden="true">'
	. '<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
	. '</svg>';

// ── Content-type colour mapping ────────────────────────────────────────────────
// Maps taxonomy term slugs to CSS tag-chip modifier classes.
// Add new slugs here as terms are created. Mirrors $ba_tag_colors in home.php.

$pa_type_colors = array(
	'case-study'        => 'pa-tag--dark',
	'video-testimonial' => 'pa-tag--teal',
);

/**
 * Return the CSS modifier for a content-type tag chip.
 * Defaults to pa-tag--dark (Deep Blue) if the slug isn't mapped.
 */
function cropx_pa_type_class( $slug ) {
	global $pa_type_colors;
	return isset( $pa_type_colors[ $slug ] ) ? $pa_type_colors[ $slug ] : 'pa-tag--dark';
}

/**
 * Pluralise a content-type name for the filter pill label.
 * Avoids 'Case Studys' etc.
 */
function pa_type_plural( $name ) {
	$map = array(
		'Case Study'        => 'Case Studies',
		'Video Testimonial' => 'Video Testimonials',
	);
	return isset( $map[ $name ] ) ? $map[ $name ] : $name . 's';
}

// ── Gradient placeholder cycle ─────────────────────────────────────────────────
// For publications that have no featured image. Matches the blog archive cycle.

$pa_placeholders      = array( 'blue', 'wheat', 'soil', 'sky', 'grove', 'dusk', 'forest', 'gold' );
$pa_placeholder_index = 0;

// ── Active filter state ────────────────────────────────────────────────────────

$pa_is_tax      = is_tax( 'cropx_content_type' );
$pa_active_term = $pa_is_tax ? get_queried_object() : null; // WP_Term|null

?>

<?php
// ── Nav ─────────────────────────────────────────────────────────────────────────────
// Always render the nav here. A global flag tells hero-curved to skip its own
// internal cropx_render_nav() call, preventing a double-nav when a hero page
// is configured via cropx_pub_archive_page_id.
$GLOBALS['cropx_nav_already_rendered'] = true;
cropx_render_nav( array( 'login_url' => '#' ) );

// ── Archive hero ─────────────────────────────────────────────────────────────────────────────
// Renders blocks from a WP page whose ID is stored in cropx_pub_archive_page_id.
// The hero-curved block normally calls cropx_render_nav() internally — the flag
// above suppresses that so only one nav appears on the page.
$pa_header_page_id = (int) get_option( 'cropx_pub_archive_page_id', 0 );
if ( $pa_header_page_id ) {
	$pa_header_page = get_post( $pa_header_page_id );
	if ( $pa_header_page && ! empty( $pa_header_page->post_content ) ) {
		echo do_blocks( $pa_header_page->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		// Reset postdata after do_blocks() so the main archive loop (have_posts,
		// the_post, etc.) uses the correct WP_Query and isn't left in a dirty state
		// from any sub-queries run inside the blocks.
		wp_reset_postdata();
	}
}
?>


<!-- ═══════════════════════════════════════════════════════════════════
     BODY
═══════════════════════════════════════════════════════════════════ -->
<div class="pa-body">
	<div class="wrap">
		<main id="main-content">

			<?php if ( ! have_posts() ) : ?>

			<!-- ── Empty state ──────────────────────────────────────────────── -->
			<div class="pa-empty">
				<p><?php esc_html_e( 'No customer stories found.', 'cropx' ); ?></p>
				<?php if ( $pa_is_tax ) : ?>
				<p>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'cropx_publication' ) ); ?>">
						<?php esc_html_e( '← View all customer stories', 'cropx' ); ?>
					</a>
				</p>
				<?php endif; ?>
			</div>

			<?php else : ?>

			<!-- ── 3-column customer story grid ─────────────────────────────── -->
			<section class="pa-grid-section" aria-label="<?php esc_attr_e( 'Customer Stories', 'cropx' ); ?>">

				<div class="pa-grid-header">

					<div class="pa-grid-header-text">
						<h2 class="pa-grid-heading">
							<?php
							echo $pa_active_term
								? esc_html( pa_type_plural( $pa_active_term->name ) )
								: esc_html__( 'Customer Results', 'cropx' );
							?>
						</h2>
						<p class="pa-grid-intro">
							<?php esc_html_e( 'Real case studies and customer video testimonials that document how CropX precision agronomy delivers measurable outcomes across crops and climates.', 'cropx' ); ?>
						</p>
					</div>

					<?php
					// Filter pills — parallels the category pills in home.php
					$pa_content_types = get_terms( array(
						'taxonomy'   => 'cropx_content_type',
						'hide_empty' => true,
						'orderby'    => 'name',
						'order'      => 'ASC',
					) );

					if ( ! is_wp_error( $pa_content_types ) && ! empty( $pa_content_types ) ) :
					?>
					<div class="pa-grid-header-tags">
						<h3 class="pa-grid-cat-label"><?php esc_html_e( 'Filter by type', 'cropx' ); ?></h3>
						<div class="pa-grid-cat-pills">

							<a href="<?php echo esc_url( get_post_type_archive_link( 'cropx_publication' ) ); ?>"
							   class="pa-filter-pill <?php echo ! $pa_is_tax ? 'pa-filter-pill--active' : ''; ?>"
							   <?php echo ! $pa_is_tax ? 'aria-current="true"' : ''; ?>>
								<?php esc_html_e( 'All', 'cropx' ); ?>
							</a>

							<?php foreach ( $pa_content_types as $pa_term ) :
								$pa_term_active = $pa_active_term && (int) $pa_active_term->term_id === (int) $pa_term->term_id;
							?>
							<a href="<?php echo esc_url( get_term_link( $pa_term ) ); ?>"
							   class="pa-filter-pill <?php echo $pa_term_active ? 'pa-filter-pill--active' : ''; ?>"
							   <?php echo $pa_term_active ? 'aria-current="true"' : ''; ?>>
								<?php echo esc_html( pa_type_plural( $pa_term->name ) ); ?>
							</a>
							<?php endforeach; ?>

						</div>
					</div>
					<?php endif; ?>

				</div><!-- /pa-grid-header -->

				<div class="pa-grid"
				     data-max-pages="<?php echo (int) $wp_query->max_num_pages; ?>"
				     data-per-page="<?php echo (int) get_option( 'posts_per_page' ); ?>">

					<?php
					rewind_posts();
					while ( have_posts() ) :
						the_post();

						$pa_grid_types = get_the_terms( get_the_ID(), 'cropx_content_type' );
						$pa_grid_type  = ( $pa_grid_types && ! is_wp_error( $pa_grid_types ) ) ? $pa_grid_types[0] : null;
						$pa_grid_slug  = $pa_grid_type ? $pa_grid_type->slug : '';
						$pa_grid_thumb = get_the_post_thumbnail_url( null, 'medium_large' );
						$pa_grid_excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words( get_the_content(), 20 );
						$pa_ph_class   = 'pa-card-img--' . $pa_placeholders[ $pa_placeholder_index % count( $pa_placeholders ) ];
						$pa_placeholder_index++;
					?>
					<a href="<?php the_permalink(); ?>" class="pa-card">

						<div class="pa-card-img <?php echo $pa_grid_thumb ? '' : esc_attr( $pa_ph_class ); ?>">
							<?php if ( $pa_grid_thumb ) : ?>
								<img src="<?php echo esc_url( $pa_grid_thumb ); ?>"
								     alt="<?php echo esc_attr( get_the_title() ); ?>"
								     loading="lazy" decoding="async">
							<?php endif; ?>
						</div>

						<div class="pa-card-body">

							<?php if ( $pa_grid_type ) : ?>
								<span class="pa-card-badge <?php echo 'video-testimonial' === $pa_grid_slug ? 'pa-card-badge--accent' : ''; ?>">
									<?php echo esc_html( $pa_grid_type->name ); ?>
								</span>
							<?php endif; ?>

							<h3 class="pa-card-title"><?php the_title(); ?></h3>
							<span class="pa-card-date"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></span>
							<p class="pa-card-excerpt"><?php echo esc_html( $pa_grid_excerpt ); ?></p>

							<span class="pa-card-read">
								<?php esc_html_e( 'Read more', 'cropx' ); ?>
								<?php echo $icon_arrow_sm; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>

						</div><!-- /pa-card-body -->

					</a><!-- /pa-card -->
					<?php endwhile; ?>

				</div><!-- /pa-grid -->

			</section><!-- /pa-grid-section -->


			<!-- ── Show More ────────────────────────────────────────────────────── -->
			<?php if ( $wp_query->max_num_pages > 1 ) : ?>
			<div class="pa-show-more">
				<button class="pa-show-more-btn" type="button">
					<span class="pa-show-more-label"><?php esc_html_e( 'Show More', 'cropx' ); ?></span>
					<svg class="pa-show-more-icon" width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
						<path d="M8 3v10M4 9l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
			</div>
			<?php endif; ?>



			<?php endif; // have_posts ?>

		</main><!-- /main-content -->
	</div><!-- /wrap -->
</div><!-- /pa-body -->


<?php
// ── Newsletter CTA ────────────────────────────────────────────────────────────
echo do_blocks( '<!-- wp:cropx/newsletter-cta {"bgColor":"taupe"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

// ── Pre-footer CTA ────────────────────────────────────────────────────────────
echo do_blocks( '<!-- wp:cropx/pre-footer-cta /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<?php get_footer(); ?>
