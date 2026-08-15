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
 * The "results" Page object itself, matched on post_name rather than path.
 *
 * get_page_by_path( 'results' ) requires the FULL hierarchical path when a
 * page is nested under a parent — this Page currently lives at
 * /knowledge-hub/results/, so passing just the leaf slug 'results' silently
 * finds nothing. Same bug, same fix as cropx_get_archive_group_page() in
 * inc/insights-archive.php (found there first during the Ag Insights work,
 * Aug 2026) — get_posts() filtered by 'name' matches on post_name regardless
 * of where the page sits in the tree.
 */
function cropx_get_results_page() {
	static $page      = null;
	static $looked_up = false;
	if ( ! $looked_up ) {
		$looked_up = true;
		$pages     = get_posts( array(
			'name'           => 'results',
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
		) );
		$page      = $pages[0] ?? null;
	}
	return $page;
}

/**
 * Stable "Results & Research" page URL. has_archive is off for cropx_publication
 * (see inc/cpts.php) so get_post_type_archive_link() always returns false now —
 * this resolves the real Page's permalink instead, with a hardcoded fallback so
 * filter links never break even if the Page hasn't been created yet. Uses
 * cropx_get_results_page() rather than get_page_by_path() so this returns the
 * true /knowledge-hub/results/ URL directly instead of the un-nested /results/
 * guess (which still works today via WordPress's canonical redirect, but adds
 * an avoidable extra hop on every breadcrumb and filter pill link).
 */
function cropx_get_customer_stories_url() {
	static $url = null;
	if ( null === $url ) {
		$page = cropx_get_results_page();
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
 * Renders the grid header — Story Tag pills on the left (where the "Results &
 * Research" H2 + intro copy used to sit) and Content Type pills on the right,
 * unchanged in position — followed by the 3-column card grid and Show More
 * button. Auto-detects is_tax('cropx_content_type') and is_tax('cropx_story_tag')
 * to filter the query and highlight the active pill in whichever pill row
 * applies — pass no args to get the default "all customer stories" view.
 *
 * The H2 heading + intro copy that used to render here was removed (Aug 2026,
 * matching the same treatment given to the Ag Insights archive) in favor of
 * putting Story Tag pills in that freed space. $atts['heading']/['intro'] are
 * kept for back-compat with the shortcode signature but are no longer rendered.
 *
 * @param array $atts { 'heading' => string, 'intro' => string } — accepted but unused.
 */
function cropx_render_customer_stories_grid( $atts = array() ) {

	$atts = shortcode_atts( array(
		'heading' => __( 'Results & Research', 'cropx' ),
		'intro'   => __( 'Real case studies and customer video testimonials that document how CropX precision agronomy delivers measurable outcomes across crops and climates.', 'cropx' ),
	), $atts, 'cropx_customer_stories_grid' );

	$icon_arrow_sm = '<svg width="13" height="13" viewBox="0 0 16 16" fill="none" aria-hidden="true">'
		. '<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
		. '</svg>';

	// Content Type filter (Case Study / Video Testimonial) — right-hand pills.
	$active_type = is_tax( 'cropx_content_type' ) ? get_queried_object() : null;
	if ( ! ( $active_type instanceof WP_Term ) ) {
		$active_type = null;
	}

	// Story Tag filter (free-tagging, e.g. "irrigation", "corn") — left-hand
	// pills, in the spot the H2 + intro used to occupy.
	$active_tag = is_tax( 'cropx_story_tag' ) ? get_queried_object() : null;
	if ( ! ( $active_tag instanceof WP_Term ) ) {
		$active_tag = null;
	}

	// Build our own WP_Query rather than relying on the main query — this
	// shortcode can run inside a Page's content (main query = the Page itself)
	// just as easily as on a taxonomy archive (where the main query IS already
	// the right posts, but we query independently anyway for consistency).
	// Only one of Content Type / Story Tag can be the active filter at a time
	// (each has its own archive URL), so this is a single tax_query, not a
	// combined AND across both.
	$query_args = array(
		'post_type'      => 'cropx_publication',
		'posts_per_page' => get_option( 'posts_per_page' ),
		'paged'          => 1,
	);
	if ( $active_type ) {
		$query_args['tax_query'] = array( array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			'taxonomy' => 'cropx_content_type',
			'field'    => 'term_id',
			'terms'    => array( $active_type->term_id ),
		) );
	} elseif ( $active_tag ) {
		$query_args['tax_query'] = array( array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			'taxonomy' => 'cropx_story_tag',
			'field'    => 'term_id',
			'terms'    => array( $active_tag->term_id ),
		) );
	}
	$query = new WP_Query( $query_args );

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
			<?php if ( $active_type || $active_tag ) : ?>
			<p>
				<a href="<?php echo esc_url( cropx_get_customer_stories_url() ); ?>">
					<?php esc_html_e( '← View all customer stories', 'cropx' ); ?>
				</a>
			</p>
			<?php endif; ?>
		</div>

		<?php else : ?>

		<div class="pa-grid-header pa-grid-header--dual-pills">

			<?php
			// Story Tag pills — left-hand column, in the spot the H2 + intro used
			// to occupy. Renders nothing (leaving the space empty) until at least
			// one Customer Story post has a Story Tag assigned in the editor.
			$story_tags = get_terms( array(
				'taxonomy'   => 'cropx_story_tag',
				'hide_empty' => true,
				'orderby'    => 'name',
				'order'      => 'ASC',
			) );

			if ( ! is_wp_error( $story_tags ) && ! empty( $story_tags ) ) :
			?>
			<div class="pa-grid-header-storytags">
				<h3 class="pa-grid-cat-label"><?php esc_html_e( 'Filter by topic', 'cropx' ); ?></h3>
				<div class="pa-grid-cat-pills">

					<a href="<?php echo esc_url( cropx_get_customer_stories_url() ); ?>"
					   class="pa-filter-pill <?php echo ! $active_tag ? 'pa-filter-pill--active' : ''; ?>"
					   <?php echo ! $active_tag ? 'aria-current="true"' : ''; ?>>
						<?php esc_html_e( 'All', 'cropx' ); ?>
					</a>

					<?php foreach ( $story_tags as $tag ) :
						$tag_active = $active_tag && (int) $active_tag->term_id === (int) $tag->term_id;
					?>
					<a href="<?php echo esc_url( get_term_link( $tag ) ); ?>"
					   class="pa-filter-pill <?php echo $tag_active ? 'pa-filter-pill--active' : ''; ?>"
					   <?php echo $tag_active ? 'aria-current="true"' : ''; ?>>
						<?php echo esc_html( $tag->name ); ?>
					</a>
					<?php endforeach; ?>

				</div>
			</div>
			<?php endif; ?>

			<?php
			// Content Type pills — right-hand column, unchanged in position.
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
					   class="pa-filter-pill <?php echo ! $active_type ? 'pa-filter-pill--active' : ''; ?>"
					   <?php echo ! $active_type ? 'aria-current="true"' : ''; ?>>
						<?php esc_html_e( 'All', 'cropx' ); ?>
					</a>

					<?php foreach ( $content_types as $term ) :
						$term_active = $active_type && (int) $active_type->term_id === (int) $term->term_id;
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
				$excerpt = cropx_get_card_excerpt( null, 20 );
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
