<?php
/**
 * Category archive groups — shared logic for every "category hub" page
 * (page-insights.php, page-press-room.php, ...) and category.php.
 *
 * A "group" combines one or more top-level WordPress categories (+ all of
 * their descendants) into one curated listing at its own page — e.g.
 * /insights/ combines "Ag Insights" and "Research"; /press-room/ covers just
 * "Press Room". Every category in a group is also independently browsable at
 * its own native /category/{slug}/ URL, sharing the exact same design.
 *
 * To add a new group: add an entry to cropx_get_archive_group_registry()
 * below, then create a matching page-{slug}.php modelled on
 * page-press-room.php (the simplest example — a single category).
 * page-insights.php is the two-category example.
 *
 * category.php is the site-wide template for EVERY WordPress category
 * archive (previously that fell through to archive.php's ba-* blog design).
 * A category that isn't part of any registered group still renders with
 * this same layout, scoped to itself + its own children — it just won't
 * highlight a filter pill or show a group hero. Tag and date archives are
 * untouched and still use archive.php.
 *
 * CSS namespace: agr-* (styles/ag-archive.css). JS: assets/js/ag-archive.js.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The registry of category archive groups. Each entry:
 *   categories — top-level category names making up this group (looked up
 *                by NAME, not slug, so this survives however WordPress
 *                slugified them).
 *   pill_mode  — 'parents'  → one filter pill per category listed above
 *                             (used when a group combines 2+ categories,
 *                             e.g. "All / Ag Insights / Research").
 *                'children' → one filter pill per DIRECT CHILD of the
 *                             (single) category listed above. A one-category
 *                             group showing a pill for that same category
 *                             next to "All" would be redundant, since both
 *                             would link to identical content — its children
 *                             are the meaningful next level to filter by.
 *   heading / intro — shown on the group's own combined page.
 *
 * @return array<string, array{categories: string[], pill_mode: string, heading: string, intro: string}>
 */
function cropx_get_archive_group_registry(): array {
	return array(
		'insights' => array(
			'categories' => array( 'Ag Insights', 'Research' ),
			'pill_mode'  => 'parents',
			'heading'    => __( 'Ag Insights & Research', 'cropx' ),
			'intro'      => __( 'Field-tested guidance and independent research from the CropX agronomy team — irrigation strategy, crop health, and the data behind better decisions.', 'cropx' ),
		),
		'press-room' => array(
			'categories' => array( 'Press Room' ),
			'pill_mode'  => 'children',
			'heading'    => __( 'Press Room', 'cropx' ),
			'intro'      => __( 'News, product announcements, and media coverage from CropX.', 'cropx' ),
		),
	);
}

/**
 * Resolve a group's configured category names into real WP_Term objects,
 * their combined descendant-inclusive ID list, its filter-pill terms, and
 * the badge accent ID set. Memoized per group per request.
 *
 * @param  string $group_slug Key from cropx_get_archive_group_registry().
 * @return array{
 *   slug: string, terms: WP_Term[], all_ids: int[], pill_terms: WP_Term[],
 *   accent_ids: int[], heading: string, intro: string,
 * }
 */
function cropx_get_archive_group_context( string $group_slug ): array {
	static $cache = array();
	if ( isset( $cache[ $group_slug ] ) ) {
		return $cache[ $group_slug ];
	}

	$registry = cropx_get_archive_group_registry();
	$config   = $registry[ $group_slug ] ?? null;

	if ( ! $config ) {
		return $cache[ $group_slug ] = array(
			'slug'       => $group_slug,
			'terms'      => array(),
			'all_ids'    => array(),
			'pill_terms' => array(),
			'accent_ids' => array(),
			'heading'    => '',
			'intro'      => '',
		);
	}

	$terms   = array();
	$all_ids = array();
	foreach ( $config['categories'] as $name ) {
		$term = get_term_by( 'name', $name, 'category' ) ?: null;
		if ( $term ) {
			$terms[]  = $term;
			$all_ids  = array_merge( $all_ids, cropx_category_and_children_ids( $term->term_id ) );
		}
	}
	$all_ids = array_values( array_unique( $all_ids ) );

	// Pill terms — the group's own top-level categories ('parents' mode),
	// or the single category's direct children ('children' mode).
	if ( 'children' === ( $config['pill_mode'] ?? '' ) && 1 === count( $terms ) ) {
		$pill_terms = get_categories( array(
			'parent'     => $terms[0]->term_id,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		) );
	} else {
		$pill_terms = $terms;
	}

	// Badge accent colour only makes sense when a group combines exactly two
	// top-level categories — the second one gets the alternate colour so the
	// two are visually distinguishable in the combined grid (e.g. Ag Insights
	// vs Research). A single-category group has nothing to contrast against.
	$accent_ids = ( 2 === count( $terms ) )
		? cropx_category_and_children_ids( $terms[1]->term_id )
		: array();

	return $cache[ $group_slug ] = array(
		'slug'       => $group_slug,
		'terms'      => $terms,
		'all_ids'    => $all_ids,
		'pill_terms' => $pill_terms,
		'accent_ids' => $accent_ids,
		'heading'    => $config['heading'],
		'intro'      => $config['intro'],
	);
}

/**
 * A category term ID plus every descendant term ID beneath it (all levels).
 *
 * @param  int $term_id Category term ID.
 * @return int[]
 */
function cropx_category_and_children_ids( int $term_id ): array {
	$children = get_term_children( $term_id, 'category' );
	$children = is_wp_error( $children ) ? array() : $children;
	return array_values( array_map( 'intval', array_merge( array( $term_id ), $children ) ) );
}

/**
 * Which registered archive group (if any) a category term ID belongs to —
 * used by category.php to decide which group's design/hero/pills to use.
 *
 * @param  int $term_id
 * @return string Group slug, or '' if the category isn't part of any group.
 */
function cropx_get_term_archive_group_slug( int $term_id ): string {
	foreach ( array_keys( cropx_get_archive_group_registry() ) as $group_slug ) {
		$context = cropx_get_archive_group_context( $group_slug );
		if ( in_array( $term_id, $context['all_ids'], true ) ) {
			return $group_slug;
		}
	}
	return '';
}

/**
 * The URL of a group's own combined page (e.g. /press-room/, /insights/) —
 * looked up by the Page's slug (same as the group's registry key) so it stays
 * correct across environments rather than hardcoding a path. Falls back to
 * home_url('/{slug}/') so links never break even if the Page hasn't been
 * created yet. Shared by the group's filter pills and by anything else that
 * needs to link back to the group root, e.g. the Press Room breadcrumb in
 * single.php.
 *
 * @param  string $group_slug Also the group's page slug (e.g. 'press-room').
 * @return string
 */
function cropx_get_archive_group_url( string $group_slug ): string {
	$page = get_page_by_path( $group_slug, OBJECT, 'page' );
	return $page ? get_permalink( $page ) : home_url( '/' . $group_slug . '/' );
}

/**
 * Build the "All / ..." filter pills for a group, given the category term
 * currently being viewed — pass null when rendering the group's own combined
 * page (e.g. /insights/, /press-room/), where "All" is always the active pill.
 *
 * @param  array         $group       Result of cropx_get_archive_group_context().
 * @param  WP_Term|null  $active_term The category currently being viewed, if any.
 * @return array<int, array{url: string, label: string, active: bool}>
 */
function cropx_build_archive_group_pills( array $group, ?WP_Term $active_term ): array {
	$all_url = cropx_get_archive_group_url( $group['slug'] );

	$all_active = true;
	$pills      = array();

	foreach ( $group['pill_terms'] as $term ) {
		$is_active = $active_term && (
			(int) $active_term->term_id === (int) $term->term_id
			|| cat_is_ancestor_of( $term->term_id, $active_term->term_id )
		);
		if ( $is_active ) {
			$all_active = false;
		}
		$pills[] = array(
			'url'    => get_term_link( $term ),
			'label'  => $term->name,
			'active' => (bool) $is_active,
		);
	}

	array_unshift( $pills, array(
		'url'    => $all_url,
		'label'  => __( 'All', 'cropx' ),
		'active' => $all_active,
	) );

	return $pills;
}

/**
 * The category to show as a post's badge in the grid, and whether it should
 * use the accent colour. Prefers whichever of the post's own categories is
 * within the given scope — falls back to the post's first assigned category
 * otherwise (e.g. a post viewed from a category outside any registered group).
 *
 * @param  int   $post_id
 * @param  int[] $badge_category_ids Scope to search within (typically a
 *                                    group's full all_ids, even when the
 *                                    current view is narrowed to one category
 *                                    within it — so the badge always shows
 *                                    the post's most specific relevant term).
 * @param  int[] $accent_ids         IDs that should render with the accent
 *                                    colour instead of the default.
 * @return array{term: WP_Term|null, accent: bool}
 */
function cropx_get_archive_post_badge( int $post_id, array $badge_category_ids, array $accent_ids = array() ): array {
	$terms = get_the_terms( $post_id, 'category' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return array(
			'term'   => null,
			'accent' => false,
		);
	}

	foreach ( $terms as $term ) {
		if ( in_array( $term->term_id, $badge_category_ids, true ) ) {
			return array(
				'term'   => $term,
				'accent' => in_array( $term->term_id, $accent_ids, true ),
			);
		}
	}

	return array(
		'term'   => $terms[0],
		'accent' => false,
	);
}

/**
 * Render the archive hero — blocks from a group's own page content. Used by
 * category.php, which isn't itself that Page and so has no post content of
 * its own to draw on. The group's own page-{slug}.php template doesn't need
 * this — it IS that page, so it uses the normal have_posts()/the_content() loop.
 *
 * @param string $group_slug Also the group's page slug (e.g. 'insights', 'press-room').
 */
function cropx_render_archive_group_hero( string $group_slug ): void {
	$page = get_page_by_path( $group_slug, OBJECT, 'page' );
	if ( $page && ! empty( $page->post_content ) ) {
		echo do_blocks( $page->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_reset_postdata();
	}
}

/**
 * Category archive query rewrite — every /category/{slug}/ archive shows
 * posts from that category AND all of its child categories, not just posts
 * assigned directly to it. This is what lets /category/press-room/ include
 * posts tagged only with one of its children (Company News, Press Releases,
 * Product Updates & Releases).
 *
 * WordPress's default 'cat' query var is cleared and replaced with
 * 'category__in' — leaving 'cat' set as well would AND the two together and
 * incorrectly exclude child-only posts (a post would have to be tagged with
 * the parent directly, not just a child, to satisfy 'cat').
 */
add_action( 'pre_get_posts', function ( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_category() ) {
		return;
	}

	$queried = $query->get_queried_object();
	if ( ! $queried instanceof WP_Term ) {
		return;
	}

	$ids = cropx_category_and_children_ids( $queried->term_id );

	$query->set( 'cat', '' );
	$query->set( 'category_name', '' );
	$query->set( 'category__in', $ids );
} );

/**
 * The shared grid section — post cards, filter pills, and Show More button.
 * Used identically by every group's own page (all of that group's posts
 * combined) and category.php (posts from one specific category + its
 * children). Mirrors archive-cropx_publication.php's pa-grid-section, with
 * the agr-* CSS namespace (styles/ag-archive.css) instead of pa-*.
 *
 * @param WP_Query $query               Already-run query holding the posts to
 *                                       display (a custom query or the main $wp_query).
 * @param int[]    $query_category_ids  The category IDs this view is scoped
 *                                       to — echoed onto the grid so
 *                                       ag-archive.js's Show More fetch stays
 *                                       within the same scope.
 * @param int[]    $badge_category_ids  Scope used to pick each post's badge
 *                                       term — see cropx_get_archive_post_badge().
 * @param int[]    $accent_ids          Category IDs whose badge uses the
 *                                       accent colour.
 * @param array    $pills               Result of cropx_build_archive_group_pills(),
 *                                       or an empty array to hide the pill row
 *                                       entirely (categories outside any group).
 * @param string   $heading             Grid section heading.
 * @param string   $intro               Grid section intro paragraph.
 */
function cropx_render_insights_grid( WP_Query $query, array $query_category_ids, array $badge_category_ids, array $accent_ids, array $pills, string $heading, string $intro ): void {
	$icon_arrow_sm = '<svg width="13" height="13" viewBox="0 0 16 16" fill="none" aria-hidden="true">'
		. '<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
		. '</svg>';

	$placeholders      = array( 'blue', 'wheat', 'soil', 'sky', 'grove', 'dusk', 'forest', 'gold' );
	$placeholder_index = 0;

	// By convention the first pill (if any) is always "All" — used for the
	// empty-state "back to everything" link.
	$all_pill_url    = ! empty( $pills ) ? $pills[0]['url'] : home_url( '/' );
	$all_pill_active = ! empty( $pills ) ? $pills[0]['active'] : true;
	?>

	<div class="agr-body">
		<div class="wrap">
			<main id="main-content">

				<?php if ( ! $query->have_posts() ) : ?>

				<!-- ── Empty state ──────────────────────────────────────────────── -->
				<div class="agr-empty">
					<p><?php esc_html_e( 'No articles found.', 'cropx' ); ?></p>
					<?php if ( ! $all_pill_active ) : ?>
					<p>
						<a href="<?php echo esc_url( $all_pill_url ); ?>">
							<?php esc_html_e( '← View all articles', 'cropx' ); ?>
						</a>
					</p>
					<?php endif; ?>
				</div>

				<?php else : ?>

				<!-- ── 3-column article grid ────────────────────────────────────── -->
				<section class="agr-grid-section" aria-label="<?php esc_attr_e( 'Articles', 'cropx' ); ?>">

					<div class="agr-grid-header">

						<div class="agr-grid-header-text">
							<h2 class="agr-grid-heading"><?php echo esc_html( $heading ); ?></h2>
							<p class="agr-grid-intro"><?php echo esc_html( $intro ); ?></p>
						</div>

						<?php if ( ! empty( $pills ) ) : ?>
						<div class="agr-grid-header-tags">
							<h3 class="agr-grid-cat-label"><?php esc_html_e( 'Filter by topic', 'cropx' ); ?></h3>
							<div class="agr-grid-cat-pills">
								<?php foreach ( $pills as $pill ) : ?>
								<a href="<?php echo esc_url( $pill['url'] ); ?>"
								   class="agr-filter-pill <?php echo $pill['active'] ? 'agr-filter-pill--active' : ''; ?>"
								   <?php echo $pill['active'] ? 'aria-current="true"' : ''; ?>>
									<?php echo esc_html( $pill['label'] ); ?>
								</a>
								<?php endforeach; ?>
							</div>
						</div>
						<?php endif; ?>

					</div><!-- /agr-grid-header -->

					<div class="agr-grid"
					     data-max-pages="<?php echo (int) $query->max_num_pages; ?>"
					     data-per-page="<?php echo (int) get_option( 'posts_per_page' ); ?>"
					     data-category-ids="<?php echo esc_attr( implode( ',', $query_category_ids ) ); ?>">

						<?php
						while ( $query->have_posts() ) :
							$query->the_post();

							$badge        = cropx_get_archive_post_badge( get_the_ID(), $badge_category_ids, $accent_ids );
							$grid_thumb   = get_the_post_thumbnail_url( null, 'medium_large' );
							$grid_excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words( get_the_content(), 20 );
							$ph_class     = 'agr-card-img--' . $placeholders[ $placeholder_index % count( $placeholders ) ];
							$placeholder_index++;
						?>
						<a href="<?php the_permalink(); ?>" class="agr-card">

							<div class="agr-card-img <?php echo $grid_thumb ? '' : esc_attr( $ph_class ); ?>">
								<?php if ( $grid_thumb ) : ?>
									<img src="<?php echo esc_url( $grid_thumb ); ?>"
									     alt="<?php echo esc_attr( get_the_title() ); ?>"
									     loading="lazy" decoding="async">
								<?php endif; ?>
							</div>

							<div class="agr-card-body">

								<?php if ( $badge['term'] ) : ?>
									<span class="agr-card-badge <?php echo $badge['accent'] ? 'agr-card-badge--accent' : ''; ?>">
										<?php echo esc_html( $badge['term']->name ); ?>
									</span>
								<?php endif; ?>

								<h3 class="agr-card-title"><?php the_title(); ?></h3>
								<span class="agr-card-date"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></span>
								<p class="agr-card-excerpt"><?php echo esc_html( $grid_excerpt ); ?></p>

								<span class="agr-card-read">
									<?php esc_html_e( 'Read more', 'cropx' ); ?>
									<?php echo $icon_arrow_sm; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</span>

							</div><!-- /agr-card-body -->

						</a><!-- /agr-card -->
						<?php endwhile; ?>

					</div><!-- /agr-grid -->

				</section><!-- /agr-grid-section -->

				<!-- ── Show More ────────────────────────────────────────────────── -->
				<?php if ( $query->max_num_pages > 1 ) : ?>
				<div class="agr-show-more">
					<button class="agr-show-more-btn" type="button">
						<span class="agr-show-more-label"><?php esc_html_e( 'Show More', 'cropx' ); ?></span>
						<svg class="agr-show-more-icon" width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
							<path d="M8 3v10M4 9l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
				</div>
				<?php endif; ?>

				<?php endif; // have_posts ?>

			</main><!-- /main-content -->
		</div><!-- /wrap -->
	</div><!-- /agr-body -->

	<?php
	wp_reset_postdata();
}
