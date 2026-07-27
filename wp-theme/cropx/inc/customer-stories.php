<?php
/**
 * Customer Stories grid — shared rendering logic + [cropx_customer_stories_grid]
 * shortcode.
 *
 * Why a shortcode instead of a fixed template position: the "Customer Results"
 * page (page-results.php, slug "results") is a normal WP Page an editor builds
 * with blocks — hero, intro copy, testimonials, whatever they like, in any
 * order. Dropping this shortcode into a Shortcode block anywhere in that page
 * renders the filter-pill + card-grid + Show More design at exactly that spot,
 * instead of the grid being hardcoded to always appear in one fixed place.
 *
 * The same shortcode also powers /content-type/{term}/ filtered views (see
 * taxonomy-cropx_content_type.php, which renders this same Page's content) —
 * it auto-detects is_tax( 'cropx_content_type' ) and filters + highlights
 * itself accordingly, so there's only one grid implementation to maintain.
 *
 * Content types: Case Study, Video Testimonial (White Paper moved to the
 * cropx_resource CPT — see inc/cpts.php).
 *
 * Assets: reuses styles/pub-archive.css + assets/js/pub-archive.js (the pa-*
 * card/pill/badge design) — see the enqueue block in inc/enqueue.php for when
 * those load.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Content-type colour mapping ────────────────────────────────────────────────
// Maps taxonomy term slugs to CSS badge modifier classes. Add new slugs here
// as terms are created.
function cropx_cs_type_class( $slug ) {
	$colors = array(
		'case-study'        => 'pa-tag--dark',
		'video-testimonial' => 'pa-tag--teal',
	);
	return isset( $colors[ $slug ] ) ? $colors[ $slug ] : 'pa-tag--dark';
}

/**
 * Pluralise a content-type name for filter pill / heading labels.
 * Avoids naive "name + s" which breaks on irregular plurals.
 */
function cropx_cs_type_plural( $name ) {
	$map = array(
		'Case Study'        => 'Case Studies',
		'Video Testimonial' => 'Video Testimonials',
	);
	return isset( $map[ $name ] ) ? $map[ $name ] : $name . 's';
}

/**
 * Stable "Customer Results" page URL. has_archive is off for cropx_publication
 * (see inc/cpts.php) so get_post_type_archive_link() always returns false now —
 * this resolves the real Page instead, with a hardcoded fallback so filter
 * links never break even if the Page hasn't been created yet.
 */
function cropx_get_customer_stories_url() {
	static $url = null;
	if ( null === $url ) {
		$page = get_page_by_path( 'results' );
		$url  = $page ? get_permalink( $page ) : home_url( '/results/' );
	}
	return $url;
}

// ── [cropx_customer_stories_grid] shortcode ────────────────────────────────────
add_shortcode( 'cropx_customer_stories_grid', function ( $atts ) {
	ob_start();
	cropx_render_customer_stories_grid( $atts );
	return ob_get_clean();
} );

/**
 * Renders the two-column grid header (heading + filter pills), the 3-column
 * card grid, and the Show More button. Auto-detects is_tax('cropx_content_type')
 * to filter the query and highlight the active pill — pass no args to get the
 * default "all customer stories" view.
 *
 * @param array $atts { 'heading' => string, 'intro' => string } — used only
 *                     when there's no active taxonomy filter.
 */
function cropx_render_customer_stories_grid( $atts = array() ) {

	$atts = shortcode_atts( array(
		'heading' => __( 'Customer Results', 'cropx' ),
		'intro'   => __( 'Real case studies and customer video testimonials that document how CropX precision agronomy delivers measurable outcomes across crops and climates.', 'cropx' ),
	), $atts, 'cropx_customer_stories_grid' );

	$icon_arrow_sm = '<svg width="13" height="13" viewBox="0 0 16 16" fill="none" aria-hidden="true">'
		. '<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
		. '</svg>';

	$is_tax      = is_tax( 'cropx_content_type' );
	$active_term = $is_tax ? get_queried_object() : null;
	if ( ! ( $active_term instanceof WP_Term ) ) {
		$active_term = null;
	}

	// Build our own WP_Query rather than relying on the main query — this
	// shortcode can run inside a Page's content (main query = the Page itself)
	// just as easily as on a taxonomy archive (where the main query IS already
	// the right posts, but we query independently anyway for consistency).
	$query_args = array(
		'post_type'      => 'cropx_publication',
		'posts_per_page' => get_option( 'posts_per_page' ),
		'paged'          => 1,
	);
	if ( $active_term ) {
		$query_args['tax_query'] = array( array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			'taxonomy' => 'cropx_content_type',
			'field'    => 'term_id',
			'terms'    => array( $active_term->term_id ),
		) );
	}
	$query = new WP_Query( $query_args );

	$heading = $active_term ? cropx_cs_type_plural( $active_term->name ) : $atts['heading'];
	$intro   = ( $active_term && $active_term->description ) ? $active_term->description : $atts['intro'];

	$placeholders      = array( 'blue', 'wheat', 'soil', 'sky', 'grove', 'dusk', 'forest', 'gold' );
	$placeholder_index = 0;
	?>

	<!--
	  .pa-body / .wrap supply the vertical rhythm + max-width/side-padding that
	  archive-cropx_publication.php used to provide around the whole page — the
	  shortcode has to bring its own now since it can land anywhere inside a
	  Page's content, with no guaranteed wrapper around it.
	-->
	<div class="pa-body">
	<div class="wrap">
	<section class="pa-grid-section" aria-label="<?php esc_attr_e( 'Customer Stories', 'cropx' ); ?>">

		<?php if ( ! $query->have_posts() ) : ?>

		<div class="pa-empty">
			<p><?php esc_html_e( 'No customer stories found.', 'cropx' ); ?></p>
			<?php if ( $active_term ) : ?>
			<p>
				<a href="<?php echo esc_url( cropx_get_customer_stories_url() ); ?>">
					<?php esc_html_e( '← View all customer stories', 'cropx' ); ?>
				</a>
			</p>
			<?php endif; ?>
		</div>

		<?php else : ?>

		<div class="pa-grid-header">

			<div class="pa-grid-header-text">
				<h2 class="pa-grid-heading"><?php echo esc_html( $heading ); ?></h2>
				<p class="pa-grid-intro"><?php echo esc_html( $intro ); ?></p>
			</div>

			<?php
			$content_types = get_terms( array(
				'taxonomy'   => 'cropx_content_type',
				'hide_empty' => true,
				'orderby'    => 'name',
				'order'      => 'ASC',
			) );

			if ( ! is_wp_error( $content_types ) && ! empty( $content_types ) ) :
			?>
			<div class="pa-grid-header-tags">
				<h3 class="pa-grid-cat-label"><?php esc_html_e( 'Filter by type', 'cropx' ); ?></h3>
				<div class="pa-grid-cat-pills">

					<a href="<?php echo esc_url( cropx_get_customer_stories_url() ); ?>"
					   class="pa-filter-pill <?php echo ! $active_term ? 'pa-filter-pill--active' : ''; ?>"
					   <?php echo ! $active_term ? 'aria-current="true"' : ''; ?>>
						<?php esc_html_e( 'All', 'cropx' ); ?>
					</a>

					<?php foreach ( $content_types as $term ) :
						$term_active = $active_term && (int) $active_term->term_id === (int) $term->term_id;
					?>
					<a href="<?php echo esc_url( get_term_link( $term ) ); ?>"
					   class="pa-filter-pill <?php echo $term_active ? 'pa-filter-pill--active' : ''; ?>"
					   <?php echo $term_active ? 'aria-current="true"' : ''; ?>>
						<?php echo esc_html( cropx_cs_type_plural( $term->name ) ); ?>
					</a>
					<?php endforeach; ?>

				</div>
			</div>
			<?php endif; ?>

		</div><!-- /pa-grid-header -->

		<div class="pa-grid"
		     data-max-pages="<?php echo (int) $query->max_num_pages; ?>"
		     data-per-page="<?php echo (int) get_option( 'posts_per_page' ); ?>">

			<?php
			while ( $query->have_posts() ) :
				$query->the_post();

				$types   = get_the_terms( get_the_ID(), 'cropx_content_type' );
				$type    = ( $types && ! is_wp_error( $types ) ) ? $types[0] : null;
				$slug    = $type ? $type->slug : '';
				$thumb   = get_the_post_thumbnail_url( null, 'medium_large' );
				$excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words( get_the_content(), 20 );
				$ph_class = 'pa-card-img--' . $placeholders[ $placeholder_index % count( $placeholders ) ];
				$placeholder_index++;
			?>
			<a href="<?php the_permalink(); ?>" class="pa-card">

				<div class="pa-card-img <?php echo $thumb ? '' : esc_attr( $ph_class ); ?>">
					<?php if ( $thumb ) : ?>
						<img src="<?php echo esc_url( $thumb ); ?>"
						     alt="<?php echo esc_attr( get_the_title() ); ?>"
						     loading="lazy" decoding="async">
					<?php endif; ?>
				</div>

				<div class="pa-card-body">

					<?php if ( $type ) : ?>
						<span class="pa-card-badge <?php echo 'video-testimonial' === $slug ? 'pa-card-badge--accent' : ''; ?>">
							<?php echo esc_html( $type->name ); ?>
						</span>
					<?php endif; ?>

					<h3 class="pa-card-title"><?php the_title(); ?></h3>
					<span class="pa-card-date"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></span>
					<p class="pa-card-excerpt"><?php echo esc_html( $excerpt ); ?></p>

					<span class="pa-card-read">
						<?php esc_html_e( 'Read more', 'cropx' ); ?>
						<?php echo $icon_arrow_sm; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>

				</div><!-- /pa-card-body -->

			</a><!-- /pa-card -->
			<?php
			endwhile;
			wp_reset_postdata();
			?>

		</div><!-- /pa-grid -->

		<?php if ( $query->max_num_pages > 1 ) : ?>
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

	</section>
	</div><!-- /wrap -->
	</div><!-- /pa-body -->
	<?php
}

/**
 * Video Testimonial single-template override.
 *
 * WordPress's template hierarchy only supports one single-{post_type}.php
 * per post type — there's no native "single-{post_type}-{term}.php" pattern
 * for a post type + taxonomy term combination. Video testimonials need a
 * visibly different single template than case studies (no right-hand share
 * sidebar, no Key Findings card, no Download CTA, no "At a Glance" case
 * study fields), so this filter swaps in
 * single-cropx_publication-video-testimonial.php whenever the post being
 * viewed is tagged "Video Testimonial". Every other cropx_publication post
 * (case studies, and any customer story left untagged) keeps using the
 * default single-cropx_publication.php.
 */
add_filter( 'template_include', function ( $template ) {
	if ( is_singular( 'cropx_publication' ) && has_term( 'video-testimonial', 'cropx_content_type' ) ) {
		$override = CROPX_THEME_DIR . 'single-cropx_publication-video-testimonial.php';
		if ( file_exists( $override ) ) {
			return $override;
		}
	}
	return $template;
} );
