<?php
/**
 * Asset enqueueing.
 *
 * The theme stylesheet (style.css) only carries metadata — the real
 * design tokens and front-end CSS live in styles/tokens.css. We enqueue
 * tokens.css on both front-end and editor so the published page and
 * the block editor's preview look the same.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Author web font via Fontshare's CDN. Loaded on enqueue_block_assets so
 * it's available BOTH on the front-end AND inside the block editor iframe.
 * Fontshare ships Author as a variable font with a free commercial license.
 * If we ever want to self-host for performance / vendor independence, swap
 * this for a local @font-face declaration in tokens.css.
 */
add_action( 'enqueue_block_assets', function () {
	wp_enqueue_style(
		'cropx-fonts-author',
		'https://api.fontshare.com/v2/css?f[]=author@1,2&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'cropx-tokens',
		CROPX_THEME_URI . 'styles/tokens.css',
		array( 'cropx-fonts-author' ),
		CROPX_THEME_VERSION
	);

	// Shared block utilities (.section-inner, .section-header, .section-eyebrow,
	// .section-heading, .section-body, .cta-link, .section-padded).
	// Loaded after tokens so token variables are already declared.
	wp_enqueue_style(
		'cropx-shared',
		CROPX_THEME_URI . 'styles/shared.css',
		array( 'cropx-tokens' ),
		CROPX_THEME_VERSION
	);

	// Core block styles — brands WordPress's built-in blocks (Paragraph, Heading,
	// Image, List, Quote, Table, Button, etc.) to match the CropX design system.
	// Loaded via enqueue_block_assets so it reaches both the front-end AND the
	// block editor iframe — WYSIWYG matches the published page.
	wp_enqueue_style(
		'cropx-content',
		CROPX_THEME_URI . 'styles/content.css',
		array( 'cropx-tokens' ),
		CROPX_THEME_VERSION
	);
} );

add_action( 'wp_enqueue_scripts', function () {
	// Theme metadata stylesheet (still required by WordPress even though
	// it only has the theme header comment).
	wp_enqueue_style(
		'cropx-theme',
		get_stylesheet_uri(),
		array( 'cropx-tokens' ),
		CROPX_THEME_VERSION
	);

	// Nav CSS + JS are shared infrastructure: every page uses a nav, either
	// via the cropx/nav block, the cropx/segment-hero block, or a PHP partial
	// rendered by a template (single.php, home.php, archive templates).
	//
	// CSS: WordPress auto-enqueues block styles only for the block that
	// declares them; since segment-hero doesn't list nav/style-index.css in
	// its block.json (parent-directory file: paths aren't supported), we load
	// it globally here.
	//
	// JS: WordPress auto-enqueues block viewScripts only when that block is
	// in the page content (the_content()). Template-rendered navs (single.php,
	// home.php, pub archives) and the nav embedded inside cropx/segment-hero
	// are not guaranteed to trigger viewScript auto-enqueue. Loading the nav JS
	// globally costs nothing (2KB deferred) and makes every nav work regardless
	// of how it's rendered. Per-template enqueues below use the same handle and
	// are silently deduped by WordPress.
	wp_enqueue_style(
		'cropx-nav-block-styles',
		CROPX_THEME_URI . 'build/blocks/nav/style-index.css',
		array(),
		CROPX_THEME_VERSION
	);

	$nav_view_asset = CROPX_THEME_DIR . 'build/blocks/nav/view.asset.php';
	if ( file_exists( $nav_view_asset ) ) {
		$nav_view = require $nav_view_asset;
		wp_enqueue_script(
			'cropx-nav-view',
			CROPX_THEME_URI . 'build/blocks/nav/view.js',
			$nav_view['dependencies'],
			$nav_view['version'],
			array( 'strategy' => 'defer', 'in_footer' => true )
		);
	}
} );

/**
 * Preconnect to Fontshare's CDN so the browser opens a TCP+TLS connection
 * before it needs the font CSS. Knocks ~100–300ms off first font paint.
 */
/**
 * Expose theme URI to all block editor scripts so edit.js files can
 * build absolute URLs to theme assets (logos, PNGs, SVGs) for previews.
 * Usage in any edit.js: window.cropxThemeData?.themeUri + 'assets/...'
 * This is the standard pattern for all CropX blocks.
 */
add_action( 'enqueue_block_editor_assets', function () {
	wp_add_inline_script(
		'wp-blocks',
		'var cropxThemeData = ' . wp_json_encode( array(
			'themeUri' => trailingslashit( get_template_directory_uri() ),
		) ) . ';',
		'before'
	);

	// Sidebar panels for CPT meta fields (Team Member, Dealer, Testimonial).
	$asset_file = CROPX_THEME_DIR . 'build/admin/editor-panels.asset.php';
	if ( file_exists( $asset_file ) ) {
		$asset = require $asset_file;
		wp_enqueue_script(
			'cropx-editor-panels',
			CROPX_THEME_URI . 'build/admin/editor-panels.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);
	}

	// Underline format type — adds the U button to the rich-text toolbar.
	// WordPress core omits underline; this registers it as <u> with Ctrl/Cmd+U.
	wp_enqueue_script(
		'cropx-editor-underline',
		CROPX_THEME_URI . 'assets/js/editor-underline.js',
		array( 'wp-rich-text', 'wp-block-editor', 'wp-element' ),
		CROPX_THEME_VERSION,
		true
	);
} );

/**
 * Blog single post chrome — single.php.
 * Only loaded on individual blog post pages to keep the main stylesheet lean.
 *
 * single.php renders the nav via cropx_render_nav() (PHP partial), so WordPress
 * never auto-enqueues the nav block's viewScript. We do it manually here,
 * exactly like the blog archive enqueue above.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( is_singular( 'post' ) ) {
		// Nav interactive JS — manual enqueue because the nav is PHP-rendered,
		// not a Gutenberg block (which would trigger auto-enqueue).
		$nav_view_asset = CROPX_THEME_DIR . 'build/blocks/nav/view.asset.php';
		if ( file_exists( $nav_view_asset ) ) {
			$nav_view = require $nav_view_asset;
			wp_enqueue_script(
				'cropx-nav-view',
				CROPX_THEME_URI . 'build/blocks/nav/view.js',
				$nav_view['dependencies'],
				$nav_view['version'],
				array( 'strategy' => 'defer', 'in_footer' => true )
			);
		}

		wp_enqueue_style(
			'cropx-single',
			CROPX_THEME_URI . 'styles/single.css',
			array( 'cropx-tokens' ),
			CROPX_THEME_VERSION
		);
		// Cards block CSS — needed for crd-* classes used in the related-posts section.
		wp_enqueue_style(
			'cropx-cards-block-styles',
			CROPX_THEME_URI . 'build/blocks/cards/style-index.css',
			array( 'cropx-tokens' ),
			CROPX_THEME_VERSION
		);
		// Reading progress bar + dynamic ToC builder + copy-link button.
		wp_enqueue_script(
			'cropx-blog-single',
			CROPX_THEME_URI . 'assets/js/blog-single.js',
			array(),
			CROPX_THEME_VERSION,
			array( 'strategy' => 'defer', 'in_footer' => true )
		);
	}
} );

/**
 * Blog archive chrome — home.php + archive.php.
 * Loaded on the blog posts page (is_home) AND date archives (is_date) since
 * archive.php uses the same ba-* layout. Category archives moved to
 * category.php + styles/ag-archive.css as of the Ag Insights & Research
 * archive; tag archives moved the same way to tag.php as of Aug 2026 (see
 * the enqueue block below for both) — neither is handled here anymore.
 * Keeps the global stylesheet lean.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( is_home() || is_date() ) {
		wp_enqueue_style(
			'cropx-blog-archive',
			CROPX_THEME_URI . 'styles/blog-archive.css',
			array( 'cropx-tokens' ),
			CROPX_THEME_VERSION
		);

		// Nav interactive JS — not auto-enqueued on this page because the nav is
		// rendered via cropx_render_nav() (a PHP partial), not through a Gutenberg
		// block. On other pages a cropx/nav or cropx/segment-hero block triggers
		// WordPress's viewScript auto-enqueue. Here we do it manually.
		$nav_view_asset = CROPX_THEME_DIR . 'build/blocks/nav/view.asset.php';
		if ( file_exists( $nav_view_asset ) ) {
			$nav_view = require $nav_view_asset;
			wp_enqueue_script(
				'cropx-nav-view',
				CROPX_THEME_URI . 'build/blocks/nav/view.js',
				$nav_view['dependencies'],
				$nav_view['version'],
				array( 'strategy' => 'defer', 'in_footer' => true )
			);
		}

		// Load-more + carousel JS.
		wp_enqueue_script(
			'cropx-blog-archive-js',
			CROPX_THEME_URI . 'assets/js/blog-archive.js',
			array(),
			CROPX_THEME_VERSION,
			array( 'strategy' => 'defer', 'in_footer' => true )
		);

		// Pass the REST API base URL so the script works in subdirectory installs.
		// categoryId is always 0 here now — this runs only on is_home()/is_date(),
		// neither of which is ever category-filtered.
		wp_localize_script(
			'cropx-blog-archive-js',
			'cropxBlogArchive',
			array(
				'restUrl'    => esc_url_raw( rest_url( 'wp/v2/posts' ) ),
				'categoryId' => 0,
			)
		);
	}
} );

/**
 * Category/tag archive chrome — every group's own page (page-insights.php,
 * page-press-room.php, ...), category.php, AND tag.php. Loaded on each
 * group's combined page, every /category/{slug}/ archive, and every
 * /tag/{slug}/ archive (Aug 2026 — tag.php joined category.php in sharing
 * this same agr-* design so a tag click doesn't land somewhere that looks
 * like a different site).
 */
add_action( 'wp_enqueue_scripts', function () {
	$archive_group_slugs = array_keys( cropx_get_archive_group_registry() );
	$on_group_page        = false;
	foreach ( $archive_group_slugs as $slug ) {
		if ( is_page( $slug ) ) {
			$on_group_page = $slug;
			break;
		}
	}

	if ( $on_group_page || is_category() || is_tag() ) {

		wp_enqueue_style(
			'cropx-ag-archive',
			CROPX_THEME_URI . 'styles/ag-archive.css',
			array( 'cropx-tokens' ),
			CROPX_THEME_VERSION
		);

		// Nav interactive JS — same manual enqueue as the other PHP-rendered-nav
		// archives, since the nav here is a cropx_render_nav() PHP partial, not
		// a Gutenberg block WordPress would auto-enqueue a viewScript for.
		$nav_view_asset = CROPX_THEME_DIR . 'build/blocks/nav/view.asset.php';
		if ( file_exists( $nav_view_asset ) ) {
			$nav_view = require $nav_view_asset;
			wp_enqueue_script(
				'cropx-nav-view',
				CROPX_THEME_URI . 'build/blocks/nav/view.js',
				$nav_view['dependencies'],
				$nav_view['version'],
				array( 'strategy' => 'defer', 'in_footer' => true )
			);
		}

		// Load-more JS.
		wp_enqueue_script(
			'cropx-ag-archive-js',
			CROPX_THEME_URI . 'assets/js/ag-archive.js',
			array(),
			CROPX_THEME_VERSION,
			array( 'strategy' => 'defer', 'in_footer' => true )
		);

		// categoryIds — the REST filter scope for Show More (must match what
		// was actually queried server-side). badgeIds — the wider scope used
		// to pick each fetched post's badge term (a group's full category
		// list, even when categoryIds is narrowed to one category within it,
		// so the badge always shows the post's most specific relevant term).
		// accentIds — category IDs whose badge uses the accent colour.
		if ( $on_group_page ) {
			$group        = cropx_get_archive_group_context( $on_group_page );
			$category_ids = ! empty( $group['all_ids'] ) ? $group['all_ids'] : array( 0 );
			$badge_ids    = $group['all_ids'];
			$accent_ids   = $group['accent_ids'];
		} elseif ( is_tag() ) {
			// tag.php — a tag can span posts from completely different
			// categories, so there's no group scope to hand out. Empty
			// badgeIds makes findBadgeCategory() in ag-archive.js fall back
			// to each fetched post's own first category, matching the PHP
			// side's cropx_get_archive_post_badge() default behaviour.
			$category_ids = array();
			$badge_ids    = array();
			$accent_ids   = array();
		} else {
			// is_category() — scope to the queried category + its own children.
			// If that category belongs to a registered group, badge/accent use
			// the group's full scope; otherwise they match categoryIds exactly.
			$queried      = get_queried_object();
			$category_ids = ( $queried instanceof WP_Term ) ? cropx_category_and_children_ids( $queried->term_id ) : array();
			$group_slug   = ( $queried instanceof WP_Term ) ? cropx_get_term_archive_group_slug( $queried->term_id ) : '';
			if ( $group_slug ) {
				$group      = cropx_get_archive_group_context( $group_slug );
				$badge_ids  = $group['all_ids'];
				$accent_ids = $group['accent_ids'];
			} else {
				$badge_ids  = $category_ids;
				$accent_ids = array();
			}
		}

		wp_localize_script(
			'cropx-ag-archive-js',
			'cropxAgArchive',
			array(
				'restUrl'     => esc_url_raw( rest_url( 'wp/v2/posts' ) ),
				'categoryIds' => implode( ',', $category_ids ),
				'badgeIds'    => implode( ',', $badge_ids ),
				'accentIds'   => implode( ',', $accent_ids ),
			)
		);
	}
} );

/**
 * Customer Story single chrome — single-cropx_publication.php.
 * Only loaded on individual customer story pages (case studies, video testimonials).
 * Reuses the same reading-progress JS as the blog single post.
 *
 * Like single.php, the nav is PHP-rendered here, so we must manually
 * enqueue the nav viewScript (same as blog archive / publication archive).
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( is_singular( 'cropx_publication' ) ) {
		// Nav interactive JS — manual enqueue (PHP-rendered nav, not a block).
		$nav_view_asset = CROPX_THEME_DIR . 'build/blocks/nav/view.asset.php';
		if ( file_exists( $nav_view_asset ) ) {
			$nav_view = require $nav_view_asset;
			wp_enqueue_script(
				'cropx-nav-view',
				CROPX_THEME_URI . 'build/blocks/nav/view.js',
				$nav_view['dependencies'],
				$nav_view['version'],
				array( 'strategy' => 'defer', 'in_footer' => true )
			);
		}

		wp_enqueue_style(
			'cropx-pub-single',
			CROPX_THEME_URI . 'styles/pub-single.css',
			array( 'cropx-tokens' ),
			CROPX_THEME_VERSION
		);
		// Cards block CSS — needed for crd-* classes used in the related publications section.
		wp_enqueue_style(
			'cropx-cards-block-styles',
			CROPX_THEME_URI . 'build/blocks/cards/style-index.css',
			array( 'cropx-tokens' ),
			CROPX_THEME_VERSION
		);
		wp_enqueue_script(
			'cropx-pub-single-js',
			CROPX_THEME_URI . 'assets/js/blog-single.js',
			array(),
			CROPX_THEME_VERSION,
			array( 'strategy' => 'defer', 'in_footer' => true )
		);
	}
} );

/**
 * Results & Research grid chrome — page-results.php, taxonomy-cropx_content_type.php,
 * and taxonomy-cropx_story_tag.php.
 * Loaded on the "results" Page (wherever it places the
 * [cropx_customer_stories_grid] shortcode) and on taxonomy term archives
 * (/content-type/case-study/, /content-type/video-testimonial/,
 * /story-tag/{term}/), which render that same Page's content. Also checks
 * has_shortcode() generally so the assets still load if the shortcode is
 * ever reused on some other page.
 */
add_action( 'wp_enqueue_scripts', function () {
	$queried_post   = is_singular() ? get_post() : null;
	$has_shortcode  = $queried_post && has_shortcode( $queried_post->post_content, 'cropx_customer_stories_grid' );

	if ( is_page( 'results' ) || is_tax( 'cropx_content_type' ) || is_tax( 'cropx_story_tag' ) || $has_shortcode ) {

		wp_enqueue_style(
			'cropx-pub-archive',
			CROPX_THEME_URI . 'styles/pub-archive.css',
			array( 'cropx-tokens' ),
			CROPX_THEME_VERSION
		);

		// Nav interactive JS — same manual enqueue as the blog archive because the
		// nav is rendered via cropx_render_nav() (PHP partial), not a Gutenberg block.
		$nav_view_asset = CROPX_THEME_DIR . 'build/blocks/nav/view.asset.php';
		if ( file_exists( $nav_view_asset ) ) {
			$nav_view = require $nav_view_asset;
			wp_enqueue_script(
				'cropx-nav-view',
				CROPX_THEME_URI . 'build/blocks/nav/view.js',
				$nav_view['dependencies'],
				$nav_view['version'],
				array( 'strategy' => 'defer', 'in_footer' => true )
			);
		}

		// Load-more JS.
		wp_enqueue_script(
			'cropx-pub-archive-js',
			CROPX_THEME_URI . 'assets/js/pub-archive.js',
			array(),
			CROPX_THEME_VERSION,
			array( 'strategy' => 'defer', 'in_footer' => true )
		);

		// Pass REST URL and (optionally) the active taxonomy + term ID to JS.
		// On the main archive termId is 0 (no filter) and filterTax is empty.
		// On a Content Type or Story Tag term archive, filterTax names which
		// taxonomy's REST query var to filter by so the Load More fetch stays
		// within that term — see pub-archive.js.
		$term_id    = 0;
		$filter_tax = '';
		if ( is_tax( 'cropx_content_type' ) ) {
			$queried = get_queried_object();
			if ( $queried instanceof WP_Term ) {
				$term_id    = (int) $queried->term_id;
				$filter_tax = 'cropx_content_type';
			}
		} elseif ( is_tax( 'cropx_story_tag' ) ) {
			$queried = get_queried_object();
			if ( $queried instanceof WP_Term ) {
				$term_id    = (int) $queried->term_id;
				$filter_tax = 'cropx_story_tag';
			}
		}

		wp_localize_script(
			'cropx-pub-archive-js',
			'cropxPubArchive',
			array(
				'restUrl'   => esc_url_raw( rest_url( 'wp/v2/cropx_publication' ) ),
				'termId'    => $term_id,
				'filterTax' => $filter_tax,
			)
		);
	}
} );

add_filter( 'wp_resource_hints', function ( $hints, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$hints[] = array(
			'href'        => 'https://api.fontshare.com',
			'crossorigin' => 'anonymous',
		);
		$hints[] = array(
			'href'        => 'https://cdn.fontshare.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $hints;
}, 10, 2 );
