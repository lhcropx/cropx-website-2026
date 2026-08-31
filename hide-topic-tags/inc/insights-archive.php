<?php
/**
 * Category archive groups — shared logic for every "category hub" page
 * (page-insights.php, page-news.php, ...) and category.php.
 *
 * A "group" combines one or more top-level WordPress categories (+ all of
 * their descendants) into one curated listing at its own page — e.g.
 * /insights/ covers "Ag Insights" (through Aug 2026 this also merged in a
 * separate "Research" category — folded back into a single category once
 * both covered the same kind of content, see page-insights.php); /news/
 * covers just "News" (formerly "Press Room" — renamed Aug 2026, see
 * page-news.php). Every category in a group is also independently browsable
 * at its own native /category/{slug}/ URL, sharing the exact same design.
 *
 * To add a new group: add an entry to cropx_get_archive_group_registry()
 * below, then create a matching page-{slug}.php modelled on page-news.php
 * or page-insights.php (both single-category examples). A group can still
 * combine 2+ categories (see 'pill_mode' below) if a future one needs it.
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
 *              A single-category 'parents'-mode group (nothing to contrast
 *              against) automatically gets zero pills — see
 *              cropx_get_archive_group_context()'s pill_terms logic.
 *   show_badges — whether each card shows its category badge. Defaults to
 *                 true. Set false for a single-category group where every
 *                 card would show the same redundant badge (e.g. Ag Insights,
 *                 Aug 2026 — it dropped "Research" as a separate category and
 *                 uses a tag list instead of category pills, see page-insights.php).
 *   heading / intro — shown on the group's own combined page. Leave intro
 *                     empty to omit the paragraph under the H2 entirely
 *                     (Ag Insights uses this — its tag list replaces the
 *                     intro copy, see cropx_get_archive_group_tags()).
 *   flatten_url — when true, this group's posts get a flat, prefix-less
 *                 permalink (e.g. /post-name/ instead of /ag-insights/post-name/)
 *                 instead of WordPress's normal nested category permalink.
 *                 Defaults to false. See the "Flat URLs" section below for the
 *                 mechanics. Ag Insights opted into this (Aug 2026, SEO —
 *                 Lauren didn't want posts nested under a folder that only
 *                 ever holds one category). News deliberately keeps its
 *                 normal nested /news/{child-category}/ structure.
 *   breadcrumb_show_category — whether single.php's breadcrumb shows a
 *                 middle "category" crumb between the group heading and the
 *                 post title. Defaults to true. Ag Insights sets this false —
 *                 with only one real category in the group, that crumb always
 *                 duplicated the group heading crumb right next to it (e.g.
 *                 "Ag Insights > Ag Insights > Post Title"), so it's dropped
 *                 entirely there. News keeps it — its posts sit on a real
 *                 child category (Company News / Press Releases / Product
 *                 Updates & Releases) that's meaningfully different from the
 *                 "News" group heading.
 *
 * @return array<string, array{categories: string[], pill_mode: string, show_badges?: bool, heading: string, intro: string, flatten_url?: bool, breadcrumb_show_category?: bool}>
 */
function cropx_get_archive_group_registry(): array {
	return array(
		'insights' => array(
			'categories'               => array( 'Ag Insights' ),
			'pill_mode'                => 'parents',
			'show_badges'              => false,
			'heading'                  => __( 'Ag Insights', 'cropx' ),
			'intro'                    => '',
			'flatten_url'              => true,
			'breadcrumb_show_category' => false,
		),
		'news' => array(
			'categories' => array( 'News' ),
			'pill_mode'  => 'children',
			'heading'    => __( 'News', 'cropx' ),
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
			'slug'                     => $group_slug,
			'terms'                    => array(),
			'all_ids'                  => array(),
			'pill_terms'               => array(),
			'accent_ids'               => array(),
			'show_badges'              => true,
			'heading'                  => '',
			'intro'                    => '',
			'flatten_url'              => false,
			'breadcrumb_show_category' => true,
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
	// or the single category's direct children ('children' mode). A
	// single-category 'parents'-mode group has nothing to contrast against
	// (an "All / Ag Insights" pill pair would both link to identical
	// content), so it gets zero pill terms — cropx_build_archive_group_pills()
	// hides the whole pill row when this comes back empty.
	if ( 'children' === ( $config['pill_mode'] ?? '' ) && 1 === count( $terms ) ) {
		$pill_terms = get_categories( array(
			'parent'     => $terms[0]->term_id,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		) );
	} elseif ( count( $terms ) > 1 ) {
		$pill_terms = $terms;
	} else {
		$pill_terms = array();
	}

	// Badge accent colour only makes sense when a group combines exactly two
	// top-level categories — the second one gets the alternate colour so the
	// two are visually distinguishable in the combined grid (e.g. Ag Insights
	// vs Research). A single-category group has nothing to contrast against.
	$accent_ids = ( 2 === count( $terms ) )
		? cropx_category_and_children_ids( $terms[1]->term_id )
		: array();

	return $cache[ $group_slug ] = array(
		'slug'                     => $group_slug,
		'terms'                    => $terms,
		'all_ids'                  => $all_ids,
		'pill_terms'               => $pill_terms,
		'accent_ids'               => $accent_ids,
		'show_badges'              => $config['show_badges'] ?? true,
		'heading'                  => $config['heading'],
		'intro'                    => $config['intro'],
		'flatten_url'              => $config['flatten_url'] ?? false,
		'breadcrumb_show_category' => $config['breadcrumb_show_category'] ?? true,
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
 * Which registered archive group (if any) "owns" a TAG — used by tag.php to
 * decide whether to show a group's hero + pre-footer pattern on a /tag/{slug}/
 * page, the same way category.php does for /category/{slug}/ (Aug 2026, after
 * Lauren pointed out the tag pages linked from the Ag Insights page's own tag
 * list should get the same treatment as its category page).
 *
 * Unlike a category, a tag isn't declared as belonging to a group in the
 * registry — it's just a WordPress taxonomy term that happens to be used on
 * some posts. So "ownership" here is inferred: if EVERY post carrying this
 * tag also has a category within one group's scope, that group owns the tag.
 * A tag actually shared across groups (e.g. a "sustainability" tag used on
 * both an Ag Insights post and a News post) correctly returns '' rather than
 * guessing — tag.php falls back to its plain, hero-less design for those.
 * In practice every tag reachable from the Ag Insights page's tag list is
 * only ever used on Ag Insights posts today, so this resolves to 'insights'
 * for all of them.
 *
 * @param  WP_Term $tag
 * @return string Group slug, or '' if the tag isn't cleanly owned by one group.
 */
function cropx_get_tag_archive_group_slug( WP_Term $tag ): string {
	static $cache = array();
	if ( isset( $cache[ $tag->term_id ] ) ) {
		return $cache[ $tag->term_id ];
	}

	$post_ids = get_posts( array(
		'post_type'      => 'post',
		'tag_id'         => $tag->term_id,
		'posts_per_page' => -1,
		'fields'         => 'ids',
	) );
	if ( empty( $post_ids ) ) {
		return $cache[ $tag->term_id ] = '';
	}

	foreach ( array_keys( cropx_get_archive_group_registry() ) as $group_slug ) {
		$group = cropx_get_archive_group_context( $group_slug );
		if ( empty( $group['all_ids'] ) ) {
			continue;
		}

		$every_post_in_group = true;
		foreach ( $post_ids as $post_id ) {
			$post_cats = wp_get_post_categories( $post_id );
			if ( ! array_intersect( $post_cats, $group['all_ids'] ) ) {
				$every_post_in_group = false;
				break;
			}
		}

		if ( $every_post_in_group ) {
			return $cache[ $tag->term_id ] = $group_slug;
		}
	}

	return $cache[ $tag->term_id ] = '';
}

/**
 * A group's own combined Page, looked up by slug — get_posts() with a 'name'
 * filter rather than get_page_by_path(), because get_page_by_path() requires
 * the FULL hierarchical path (e.g. 'knowledge-hub/insights') and silently
 * fails to find the page if it's nested under a parent and you only pass the
 * leaf slug. The registry only ever stores the leaf slug ('insights', 'news'),
 * and the Ag Insights page lives at /knowledge-hub/insights/ on staging — so
 * the old get_page_by_path() lookup was quietly returning nothing there,
 * which is why the hero block was missing on /category/ag-insights/ (Aug
 * 2026 bug report). This matches purely on post_name regardless of where the
 * page sits in the page tree.
 *
 * @param  string $group_slug Also the group's page slug (e.g. 'insights', 'news').
 * @return WP_Post|null
 */
function cropx_get_archive_group_page( string $group_slug ): ?WP_Post {
	$pages = get_posts( array(
		'name'           => $group_slug,
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
	) );
	return $pages[0] ?? null;
}

/**
 * The URL of a group's own combined page (e.g. /news/, /insights/) —
 * looked up by the Page's slug (same as the group's registry key) so it stays
 * correct across environments rather than hardcoding a path. Falls back to
 * home_url('/{slug}/') so links never break even if the Page hasn't been
 * created yet. Shared by the group's filter pills and by anything else that
 * needs to link back to the group root, e.g. the News breadcrumb in
 * single.php.
 *
 * @param  string $group_slug Also the group's page slug (e.g. 'news').
 * @return string
 */
function cropx_get_archive_group_url( string $group_slug ): string {
	$page = cropx_get_archive_group_page( $group_slug );
	return $page ? get_permalink( $page ) : home_url( '/' . $group_slug . '/' );
}

/**
 * Build the "All / ..." filter pills for a group, given the category term
 * currently being viewed — pass null when rendering the group's own combined
 * page (e.g. /insights/, /news/), where "All" is always the active pill.
 *
 * @param  array         $group       Result of cropx_get_archive_group_context().
 * @param  WP_Term|null  $active_term The category currently being viewed, if any.
 * @return array<int, array{url: string, label: string, active: bool}>
 */
function cropx_build_archive_group_pills( array $group, ?WP_Term $active_term ): array {
	// A single-category group has no pill_terms (see cropx_get_archive_group_context())
	// — a lone "All" pill with nothing to contrast against isn't worth showing,
	// so return no pills at all and let the caller hide the row entirely.
	if ( empty( $group['pill_terms'] ) ) {
		return array();
	}

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
 * The tags actually used on a category archive group's posts, most-used
 * first — used by the Ag Insights page in place of the old cross-category
 * filter pills now that the group is down to a single category (Aug 2026,
 * "Research" folded in as just another Ag Insights post rather than its own
 * category). Rendered with the exact same agr-filter-pill markup the
 * category pills used, just linking to each tag's native /tag/{slug}/
 * archive instead — so no new CSS was needed for this.
 *
 * Note: /tag/ archives currently render via archive.php's generic blog
 * design (ba-* classes), not this file's agr-* Ag Insights design — same as
 * every other tag link on the site today. Worth an upgrade later if that
 * inconsistency bothers Lauren, but out of scope for this pass.
 *
 * @param  int[] $category_ids Scope to pull tags from (typically a group's all_ids).
 * @param  int   $limit        Max tags to return.
 * @return array<int, array{url: string, label: string}>
 */
function cropx_get_archive_group_tags( array $category_ids, int $limit = 20 ): array {
	if ( empty( $category_ids ) ) {
		return array();
	}

	$post_ids = get_posts( array(
		'post_type'      => 'post',
		'category__in'   => $category_ids,
		'posts_per_page' => -1,
		'fields'         => 'ids',
	) );
	if ( empty( $post_ids ) ) {
		return array();
	}

	$terms = get_terms( array(
		'taxonomy'   => 'post_tag',
		'object_ids' => $post_ids,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'hide_empty' => true,
	) );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	return array_map( function ( WP_Term $term ) {
		return array(
			'url'   => get_term_link( $term ),
			'label' => $term->name,
		);
	}, array_slice( $terms, 0, $limit ) );
}

/**
 * Render the archive hero — blocks from a group's own page content. Used by
 * category.php, which isn't itself that Page and so has no post content of
 * its own to draw on. The group's own page-{slug}.php template doesn't need
 * this — it IS that page, so it uses the normal have_posts()/the_content() loop.
 *
 * @param string $group_slug Also the group's page slug (e.g. 'insights', 'news').
 */
function cropx_render_archive_group_hero( string $group_slug ): void {
	$page = cropx_get_archive_group_page( $group_slug );
	if ( $page && ! empty( $page->post_content ) ) {
		echo do_blocks( $page->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_reset_postdata();
	}
}

/**
 * ── Flat URLs ────────────────────────────────────────────────────────────
 *
 * A group with 'flatten_url' => true (Ag Insights) gets posts permalinked
 * straight off the site root — /post-name/ — instead of WordPress's normal
 * nested category permalink. Three things make this work:
 *
 *   1. A catch-all rewrite rule, registered at 'bottom' priority, so a bare
 *      single-segment request like /post-name/ has SOMETHING to match.
 *      WordPress does NOT ship a generic "any unclaimed single segment might
 *      be a Page" fallback rule — it only auto-generates a rule per ACTUAL
 *      existing Page path (via get_page_uris()). Without this, an unclaimed
 *      single segment matches no rule at all and 404s before any filter
 *      below ever runs — this was the bug in the first version of this file
 *      (every Ag Insights post 404'd because 'pagename' was never being set
 *      for them in the first place). 'bottom' priority means every other,
 *      more specific rule — 2-segment category permalinks, the
 *      cropx_publication CPT's prefixed rule, and each real Page's own
 *      specific rule — gets first chance to match; this only ever fires for
 *      a genuinely unclaimed single segment. Requires a permalinks flush
 *      (Settings → Permalinks → Save Changes, no need to change anything)
 *      after this file changes, since rewrite rules are cached.
 *   2. post_link  — outputs the flat URL when building a link to one of
 *                    these posts (permalinks, card grids, the_permalink(),
 *                    etc.).
 *   3. request    — resolves an incoming flat URL back to the right post.
 *                    Reuses WordPress's own 'pagename' query var (same one
 *                    rule #1 sets, same one a real Page's own rule would set)
 *                    and steps in only when no Page claims the slug AND a
 *                    flat-url-group post's slug matches it — Pages always
 *                    keep first claim, same as WordPress's own slug-collision
 *                    behavior (a new post whose slug collides with an
 *                    existing Page already gets auto-suffixed by
 *                    wp_unique_post_slug() on save).
 */

add_action( 'init', function () {
	add_rewrite_rule( '^([^/]+)/?$', 'index.php?pagename=$matches[1]', 'bottom' );
} );

/**
 * Which registered archive group (if any) wants a flat, prefix-less
 * permalink for this specific post.
 *
 * @param  int $post_id
 * @return string Group slug if this post should get a flat URL, else ''.
 */
function cropx_get_flat_url_group_slug( int $post_id ): string {
	$cats = wp_get_post_categories( $post_id );
	if ( empty( $cats ) ) {
		return '';
	}
	foreach ( array_keys( cropx_get_archive_group_registry() ) as $group_slug ) {
		$group = cropx_get_archive_group_context( $group_slug );
		if ( empty( $group['flatten_url'] ) || empty( $group['all_ids'] ) ) {
			continue;
		}
		if ( array_intersect( $cats, $group['all_ids'] ) ) {
			return $group_slug;
		}
	}
	return '';
}

add_filter( 'post_link', function ( string $permalink, WP_Post $post ) {
	if ( 'post' !== $post->post_type || ! cropx_get_flat_url_group_slug( $post->ID ) ) {
		return $permalink;
	}
	return home_url( '/' . $post->post_name . '/' );
}, 10, 2 );

add_filter( 'request', function ( array $query_vars ) {
	if ( is_admin() || empty( $query_vars['pagename'] ) || false !== strpos( $query_vars['pagename'], '/' ) ) {
		return $query_vars;
	}

	$slug = $query_vars['pagename'];

	// A real Page always keeps first claim on a given slug.
	if ( get_page_by_path( $slug, OBJECT, 'page' ) ) {
		return $query_vars;
	}

	foreach ( array_keys( cropx_get_archive_group_registry() ) as $group_slug ) {
		$group = cropx_get_archive_group_context( $group_slug );
		if ( empty( $group['flatten_url'] ) || empty( $group['all_ids'] ) ) {
			continue;
		}

		$match = get_posts( array(
			'name'           => $slug,
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'category__in'   => $group['all_ids'],
			'posts_per_page' => 1,
			'fields'         => 'ids',
		) );

		if ( $match ) {
			return array(
				'name'      => $slug,
				'post_type' => 'post',
			);
		}
	}

	return $query_vars;
}, 20 );

/**
 * Category archive query rewrite — every /category/{slug}/ archive shows
 * posts from that category AND all of its child categories, not just posts
 * assigned directly to it. This is what lets /category/news/ include
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
 * @param string   $intro               Grid section intro paragraph. Pass ''
 *                                       to omit the paragraph entirely (Ag
 *                                       Insights does this — see $tags below).
 * @param array    $tags                Result of cropx_get_archive_group_tags(),
 *                                       or an empty array. When non-empty, the
 *                                       H2 + intro column is dropped and this
 *                                       renders as its own "Filter by topic"
 *                                       pill row in the freed space (left,
 *                                       spanning 2 of 3 grid columns) — same
 *                                       agr-filter-pill markup as $pills, just
 *                                       tag links, no "All"/active state.
 *                                       Originally (Ag Insights, Aug 2026) this
 *                                       fully replaced $pills since that group
 *                                       had nothing left to filter by; News
 *                                       (Aug 2026) renders both at once — tags
 *                                       on the left, its still-meaningful
 *                                       category-children $pills on the right
 *                                       — see the "dual pill" layout below.
 * @param bool     $show_badges         Whether each card shows its category
 *                                       badge. False for Ag Insights, whose
 *                                       badge would just say "Ag Insights" on
 *                                       every single card.
 * @param string   $filter_param        Which REST query param $query_category_ids
 *                                       gets sent as when Show More fetches
 *                                       more pages — 'categories' everywhere
 *                                       except tag.php, which passes 'tags'
 *                                       (Aug 2026 — tag archives moved to this
 *                                       shared agr-* design too, see tag.php).
 */
function cropx_render_insights_grid( WP_Query $query, array $query_category_ids, array $badge_category_ids, array $accent_ids, array $pills, string $heading, string $intro, array $tags = array(), bool $show_badges = true, string $filter_param = 'categories' ): void {
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

					<?php
					// Topic tag filter pills — hidden sitewide (Aug 2026) until the
					// underlying WP tags get organized into a cleaner taxonomy;
					// per-tag filtering is coming back once that's done. Flip
					// $show_topic_tags back to true to restore the "Filter by
					// topic" pill row everywhere it used to show — nothing else
					// here needs to change, the data and markup are untouched.
					$show_topic_tags = false;
					$show_tags_block = $show_topic_tags && ! empty( $tags );

					// Tags mode (Ag Insights, News) drops the H2 + intro column
					// entirely, freeing that space for the tag pill row. Ag Insights
					// has nothing else to show there since it has no category pills
					// left ($pills empty); News shows tags there AND keeps its
					// still-meaningful category-children $pills on the right —
					// "dual pill" layout, same technique as the Results & Research
					// grid header (see cropx_render_customer_stories_grid()). Also
					// true whenever real category/type $pills exist even with the
					// topic tags hidden, so that row still gets the compact
					// heading-dropped treatment instead of an empty tag slot.
					$is_tags_only = $show_tags_block || ! empty( $pills );
					?>
					<div class="agr-grid-header<?php echo $is_tags_only ? ' agr-grid-header--tags-only' : ''; ?>">

						<?php if ( ! $is_tags_only ) : ?>
						<div class="agr-grid-header-text">
							<h2 class="agr-grid-heading"><?php echo esc_html( $heading ); ?></h2>
							<?php if ( $intro ) : ?>
							<p class="agr-grid-intro"><?php echo esc_html( $intro ); ?></p>
							<?php endif; ?>
						</div>
						<?php endif; ?>

						<?php if ( $show_tags_block ) : ?>
						<div class="agr-grid-header-storytags">
							<h3 class="agr-grid-cat-label"><?php esc_html_e( 'Filter by topic', 'cropx' ); ?></h3>
							<div class="agr-grid-cat-pills">
								<?php foreach ( $tags as $pill ) : ?>
								<a href="<?php echo esc_url( $pill['url'] ); ?>"
								   class="agr-filter-pill <?php echo ! empty( $pill['active'] ) ? 'agr-filter-pill--active' : ''; ?>"
								   <?php echo ! empty( $pill['active'] ) ? 'aria-current="true"' : ''; ?>>
									<?php echo esc_html( $pill['label'] ); ?>
								</a>
								<?php endforeach; ?>
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! empty( $pills ) ) : ?>
						<div class="agr-grid-header-tags">
							<h3 class="agr-grid-cat-label"><?php echo esc_html( $is_tags_only ? __( 'Filter by category', 'cropx' ) : __( 'Filter by topic', 'cropx' ) ); ?></h3>
							<div class="agr-grid-cat-pills">
								<?php foreach ( $pills as $pill ) : ?>
								<a href="<?php echo esc_url( $pill['url'] ); ?>"
								   class="agr-filter-pill <?php echo ! empty( $pill['active'] ) ? 'agr-filter-pill--active' : ''; ?>"
								   <?php echo ! empty( $pill['active'] ) ? 'aria-current="true"' : ''; ?>>
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
					     data-category-ids="<?php echo esc_attr( implode( ',', $query_category_ids ) ); ?>"
					     data-show-badges="<?php echo $show_badges ? '1' : '0'; ?>"
					     data-filter-param="<?php echo esc_attr( $filter_param ); ?>">

						<?php
						while ( $query->have_posts() ) :
							$query->the_post();

							$badge        = cropx_get_archive_post_badge( get_the_ID(), $badge_category_ids, $accent_ids );
							$grid_thumb   = get_the_post_thumbnail_url( null, 'medium_large' );
							$grid_excerpt = cropx_get_card_excerpt( null, 20 );
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

								<?php if ( $show_badges && $badge['term'] ) : ?>
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
