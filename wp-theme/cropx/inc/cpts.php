<?php
/**
 * Custom Post Types and Taxonomies for CropX.
 *
 * Post types:
 *   cropx_publication — Results & Research: case studies, research pieces, and video
 *                       testimonials (evergreen)
 *                       (internal post_type slug kept as cropx_publication — renamed
 *                       "Publications" → "Customer Stories" → "Results & Research" in
 *                       labels/admin only (Aug 2026, the CPT's scope grew beyond just
 *                       customer case studies to include research content too), so
 *                       existing content and single-story URLs are untouched. The
 *                       /results/ archive itself is now a real WP Page — see
 *                       page-results.php and inc/customer-stories.php — instead of
 *                       an automatic CPT archive; has_archive is false below.)
 *   cropx_resource    — downloadable assets: brochures, datasheets, reports, white papers
 *   cropx_dealer      — dealer directory (Phase 2)
 *   cropx_team_member — team / about page
 *   cropx_testimonial — admin label "Quotes" (internal post_type slug kept as
 *                       cropx_testimonial, same labels-only rename approach as
 *                       cropx_publication above); quotes used by testimonial
 *                       blocks (no public pages)
 *   (standard post)   — blog articles and press releases (time-stamped, by category)
 *
 * Taxonomies:
 *   cropx_content_type  — applied to Results & Research (Case Study, Research Results,
 *                         Video Testimonial)
 *   cropx_story_tag     — free-tagging taxonomy on Results & Research; public front-end
 *                         archives at /story-tag/{term}/ (see taxonomy-cropx_story_tag.php)
 *   cropx_resource_type — applied to resources (Brochure, Datasheet, Report)
 *
 * Image sizes:
 *   cropx-doc-cover         — scales document cover images to fit within 841×841px (no crop),
 *                             preserving portrait (~595×841) or landscape (~841×595) proportions.
 *   cropx-testimonial-avatar — 250×250px hard-cropped square for testimonial headshots/logos.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'cropx_register_post_types' );
function cropx_register_post_types() {

	// ── Results & Research (case studies, research pieces, video testimonials) ──
	// Press releases live in standard Posts (category: Press Release) since they
	// are time-stamped news items, not evergreen documents. White papers live
	// under Resources (cropx_resource) — they moved out of this CPT so it could
	// be renamed and refocused, first on Customer Stories, and now on the wider
	// Results & Research scope (case studies + research + video testimonials).
	register_post_type( 'cropx_publication', array(
		'labels' => array(
			'name'               => __( 'Results & Research',                       'cropx' ),
			'singular_name'      => __( 'Results',                                   'cropx' ),
			'add_new'            => __( 'Add New',                                   'cropx' ),
			'add_new_item'       => __( 'Add New Results',                          'cropx' ),
			'edit_item'          => __( 'Edit Results',                             'cropx' ),
			'new_item'           => __( 'New Results',                              'cropx' ),
			'view_item'          => __( 'View Results',                             'cropx' ),
			'search_items'       => __( 'Search Results',                           'cropx' ),
			'not_found'          => __( 'No Results & Research entries found.',     'cropx' ),
			'not_found_in_trash' => __( 'No Results & Research entries in trash.',  'cropx' ),
			'all_items'          => __( 'All Results & Research',                   'cropx' ),
			'menu_name'          => __( 'Results & Research',                       'cropx' ),
		),
		'public'            => true,
		'show_in_rest'      => true,
		// No automatic archive — /results/ is now a real WP Page (slug "results",
		// template page-results.php) so an editor can build that page freely with
		// blocks, including the [cropx_customer_stories_grid] shortcode wherever
		// they want it. Turning has_archive off is what frees up the "results"
		// slug for a Page — WordPress otherwise auto-suffixes ("results-2") any
		// Page slug that collides with a post type's has_archive value. This is
		// independent of the single-post permalink structure below, which still
		// nests singles under /results/{content-type}/{post-name}/.
		'has_archive'       => false,
		'supports'          => array( 'title', 'excerpt', 'thumbnail', 'editor', 'custom-fields' ),
		'menu_icon'         => 'dashicons-portfolio',
		// Single customer story permalinks are flat — /post-name/ — as of Aug
		// 2026 (SEO request, same as Ag Insights). This 'rewrite' slug is no
		// longer what actually ends up in a live permalink — the flat URL is
		// generated entirely by cropx_publication_permalink() below — but it's
		// left in place because cropx_publication_rewrite_rules() still uses it
		// to keep any old-style /results/{content-type}/{post-name}/ link alive
		// as a fallback, and removing it isn't necessary for the flat URL to work.
		'rewrite'           => array( 'slug' => 'results/%cropx_content_type%', 'with_front' => false ),
		'show_in_nav_menus' => true,
	) );

	// ── Team Member ─────────────────────────────────────────────────────────
	register_post_type( 'cropx_team_member', array(
		'labels' => array(
			'name'               => __( 'Team Members',              'cropx' ),
			'singular_name'      => __( 'Team Member',               'cropx' ),
			'add_new'            => __( 'Add New',                   'cropx' ),
			'add_new_item'       => __( 'Add New Team Member',       'cropx' ),
			'edit_item'          => __( 'Edit Team Member',          'cropx' ),
			'not_found'          => __( 'No team members found.',    'cropx' ),
			'not_found_in_trash' => __( 'No team members in trash.', 'cropx' ),
			'all_items'          => __( 'All Team Members',          'cropx' ),
			'menu_name'          => __( 'Team',                      'cropx' ),
		),
		'public'       => true,
		'show_in_rest' => true,
		// has_archive flipped true Aug 2026 so a Team archive page routes to
		// the new archive-cropx_team_member.php template (Lauren's basic Team
		// archive page request). The rewrite slug is 'team-archive', not
		// 'team' (Lauren, Aug 2026) — the plain /team/ URL was already
		// 404ing before permalinks were re-saved after the first has_archive
		// flip, and she wants /team/ kept free for a possible future WP Page
		// rather than ever being claimed by this CPT archive again. /team/
		// now 301-redirects to /team-archive/ — see inc/legacy-redirects.php.
		// IMPORTANT: after deploying a rewrite slug change, Settings →
		// Permalinks must be re-saved once on that environment to flush the
		// new rule, or /team-archive/ will 404 until then (same gotcha noted
		// on cropx_story_tag below).
		'has_archive'  => true,
		'supports'     => array( 'title', 'thumbnail' ),
		'menu_icon'    => 'dashicons-groups',
		'rewrite'      => array( 'slug' => 'team-archive' ),
	) );

	// ── Resource (brochures, datasheets, white papers, reports) ────────────
	register_post_type( 'cropx_resource', array(
		'labels' => array(
			'name'               => __( 'Resources',               'cropx' ),
			'singular_name'      => __( 'Resource',                'cropx' ),
			'add_new'            => __( 'Add New',                 'cropx' ),
			'add_new_item'       => __( 'Add New Resource',        'cropx' ),
			'edit_item'          => __( 'Edit Resource',           'cropx' ),
			'new_item'           => __( 'New Resource',            'cropx' ),
			'view_item'          => __( 'View Resource',           'cropx' ),
			'search_items'       => __( 'Search Resources',        'cropx' ),
			'not_found'          => __( 'No resources found.',     'cropx' ),
			'not_found_in_trash' => __( 'No resources in trash.',  'cropx' ),
			'all_items'          => __( 'All Resources',           'cropx' ),
			'menu_name'          => __( 'Resources',               'cropx' ),
		),
		'public'            => true,
		'show_in_rest'      => true,
		'has_archive'       => true,
		'supports'          => array( 'title', 'excerpt', 'thumbnail' ),
		'menu_icon'         => 'dashicons-media-document',
		// Rewrite slug is 'resources-archive', not 'resources' (Lauren, Aug
		// 2026) — she wants /resources/ kept free for a possible future WP
		// Page rather than ever being claimed by this CPT archive. /resources/
		// now 301-redirects to /resources-archive/ — see inc/legacy-redirects.php.
		// IMPORTANT: after deploying a rewrite slug change, Settings →
		// Permalinks must be re-saved once on that environment to flush the
		// new rule, or /resources-archive/ will 404 until then.
		'rewrite'           => array( 'slug' => 'resources-archive' ),
		'show_in_nav_menus' => true,
	) );

	// ── Dealer ───────────────────────────────────────────────────────────────
	// has_archive is true so this archive-cropx_dealer.php template routes
	// (Lauren's basic Dealers archive page request, Aug 2026). The rewrite
	// slug is 'dealer-archive', not 'dealers' — /dealers/ is a real, separate
	// WordPress Page slug reserved for the interactive dealer-finder map
	// (currently at /solutions/dealers/); a CPT archive at /dealers/ would
	// intercept that Page's content before it could render, same failure
	// mode this comment used to warn about before the slug moved. /dealers/
	// now 301-redirects to /solutions/dealers/, NOT to this archive — a
	// deliberately different redirect target than Resources/Team (Lauren
	// confirmed this Aug 2026) — see inc/legacy-redirects.php. The
	// dealer-finder block itself is unaffected either way since it always
	// queries dealers via REST (/wp-json/wp/v2/cropx_dealer), never via this
	// archive route. Individual dealer posts live at /dealer-archive/{slug}/.
	// IMPORTANT: after deploying a rewrite slug change, Settings →
	// Permalinks must be re-saved once on that environment to flush the new
	// rule, or /dealer-archive/ will 404 until then.
	register_post_type( 'cropx_dealer', array(
		'labels' => array(
			'name'               => __( 'Dealers',               'cropx' ),
			'singular_name'      => __( 'Dealer',                'cropx' ),
			'add_new'            => __( 'Add New',               'cropx' ),
			'add_new_item'       => __( 'Add New Dealer',        'cropx' ),
			'edit_item'          => __( 'Edit Dealer',           'cropx' ),
			'new_item'           => __( 'New Dealer',            'cropx' ),
			'view_item'          => __( 'View Dealer',           'cropx' ),
			'search_items'       => __( 'Search Dealers',        'cropx' ),
			'not_found'          => __( 'No dealers found.',     'cropx' ),
			'not_found_in_trash' => __( 'No dealers in trash.',  'cropx' ),
			'all_items'          => __( 'All Dealers',           'cropx' ),
			'menu_name'          => __( 'Dealers',               'cropx' ),
		),
		'public'            => true,
		'show_in_rest'      => true,
		'has_archive'       => true,
		'supports'          => array( 'title', 'thumbnail' ),
		'menu_icon'         => 'dashicons-store',
		'rewrite'           => array( 'slug' => 'dealer-archive' ),
		'show_in_nav_menus' => true,
	) );

	// ── Testimonial (admin label: "Quotes") ──────────────────────────────────
	// No public pages — used only as a data source for blocks. Internal
	// post_type slug (cropx_testimonial) and all field/meta names are
	// unchanged — this is a labels-only rename, same approach as the
	// Publications → Customer Stories rename.
	register_post_type( 'cropx_testimonial', array(
		'labels' => array(
			'name'               => __( 'Quotes',              'cropx' ),
			'singular_name'      => __( 'Quote',                'cropx' ),
			'add_new'            => __( 'Add New',              'cropx' ),
			'add_new_item'       => __( 'Add New Quote',        'cropx' ),
			'edit_item'          => __( 'Edit Quote',           'cropx' ),
			'not_found'          => __( 'No quotes found.',     'cropx' ),
			'not_found_in_trash' => __( 'No quotes in trash.',  'cropx' ),
			'all_items'          => __( 'All Quotes',           'cropx' ),
			'menu_name'          => __( 'Quotes',               'cropx' ),
		),
		'public'       => false,
		'show_ui'      => true,
		'show_in_rest' => true,
		'has_archive'  => false,
		'supports'     => array( 'title', 'thumbnail' ),
		'menu_icon'    => 'dashicons-format-quote',
	) );
}

add_action( 'init', 'cropx_register_taxonomies' );
function cropx_register_taxonomies() {

	// ── Content Type (applied to Results & Research) ─────────────────────────
	// Hierarchical so editors see checkboxes instead of a tag text field.
	// Default terms seeded on theme activation below.
	register_taxonomy( 'cropx_content_type', array( 'cropx_publication' ), array(
		'labels' => array(
			'name'              => __( 'Content Types',          'cropx' ),
			'singular_name'     => __( 'Content Type',           'cropx' ),
			'add_new_item'      => __( 'Add New Content Type',   'cropx' ),
			'edit_item'         => __( 'Edit Content Type',      'cropx' ),
			'search_items'      => __( 'Search Content Types',   'cropx' ),
			'all_items'         => __( 'All Content Types',      'cropx' ),
			'menu_name'         => __( 'Content Types',          'cropx' ),
		),
		'public'            => true,
		'show_in_rest'      => true,
		'hierarchical'      => true,  // shows as checkboxes in the editor
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'content-type' ),
	) );

	// ── Story Tags (applied to Results & Research) ────────────────────────────
	// Free-tagging taxonomy — separate from Content Type (Case Study / Research
	// Results / Video Testimonial), which stays a fixed, small classification.
	// Story Tags are open-ended keywords an editor can add freely (e.g.
	// "irrigation", "corn", "California"), the same flat comma-box UI as
	// WordPress's built-in Tags.
	//
	// Kept separate from the site's built-in Tags (post_tag) on purpose so a
	// blog tag archive never mixes in Results & Research content, and vice versa.
	//
	// Made public Aug 2026 as part of the Results & Research rework — front-end
	// archives now live at /story-tag/{term}/ (see taxonomy-cropx_story_tag.php)
	// and a "Filter by topic" pill row sourced from these terms was added to
	// cropx_render_customer_stories_grid() in inc/customer-stories.php, in the
	// spot the old H2 + intro copy used to occupy. IMPORTANT: after deploying
	// this change, Settings → Permalinks must be re-saved once on that
	// environment to flush the new /story-tag/ rewrite rule, or its URLs will
	// 404 until then.
	register_taxonomy( 'cropx_story_tag', array( 'cropx_publication' ), array(
		'labels' => array(
			'name'                       => __( 'Story Tags',                          'cropx' ),
			'singular_name'              => __( 'Story Tag',                           'cropx' ),
			'search_items'               => __( 'Search Story Tags',                   'cropx' ),
			'popular_items'              => __( 'Popular Story Tags',                  'cropx' ),
			'all_items'                  => __( 'All Story Tags',                      'cropx' ),
			'edit_item'                  => __( 'Edit Story Tag',                      'cropx' ),
			'update_item'                => __( 'Update Story Tag',                    'cropx' ),
			'add_new_item'               => __( 'Add New Story Tag',                   'cropx' ),
			'new_item_name'              => __( 'New Story Tag Name',                  'cropx' ),
			'separate_items_with_commas' => __( 'Separate story tags with commas',     'cropx' ),
			'add_or_remove_items'        => __( 'Add or remove story tags',            'cropx' ),
			'choose_from_most_used'      => __( 'Choose from the most used story tags', 'cropx' ),
			'not_found'                  => __( 'No story tags found.',                'cropx' ),
			'menu_name'                  => __( 'Story Tags',                          'cropx' ),
		),
		'hierarchical'      => false, // free-tagging comma-box UI, like core Tags
		'public'            => true,  // front-end archives live now — see note above
		'show_ui'           => true,
		'show_in_rest'      => true,  // keeps the editor tagging UI + REST access working
		'show_admin_column' => true,
		'show_tagcloud'     => false,
		'rewrite'           => array( 'slug' => 'story-tag' ),
	) );

	// ── Resource Type (applied to Resources) ─────────────────────────────────
	// Hierarchical so editors see checkboxes. Default terms seeded below.
	register_taxonomy( 'cropx_resource_type', array( 'cropx_resource' ), array(
		'labels' => array(
			'name'              => __( 'Resource Types',         'cropx' ),
			'singular_name'     => __( 'Resource Type',          'cropx' ),
			'add_new_item'      => __( 'Add New Resource Type',  'cropx' ),
			'edit_item'         => __( 'Edit Resource Type',     'cropx' ),
			'search_items'      => __( 'Search Resource Types',  'cropx' ),
			'all_items'         => __( 'All Resource Types',     'cropx' ),
			'menu_name'         => __( 'Resource Types',         'cropx' ),
		),
		'public'            => true,
		'show_in_rest'      => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'resource-type' ),
	) );

	// ── Quotes taxonomies ─────────────────────────────────────────────────────
	// Backend/editorial classification only — none of these are meant to show
	// on the published blocks yet. All are 'public' => false (no front-end
	// archive routes, no rewrite slugs) but 'show_in_rest' => true so the term
	// picker works normally in the block editor sidebar. This is prep for a
	// later geotargeting feature (Region) and for the "Auto (by category)" and
	// "Pick from existing quotes" content-source modes on the testimonial
	// blocks, plus the two admin list-table columns Lauren asked for
	// (Product, Business Name).

	// Category — powers "Auto (by category)" in Single Testimonial / Carousel.
	// Hierarchical checkboxes since editors will pick from a small, curated
	// set of buckets (e.g. "Homepage", "Enterprise Page") rather than free-tag.
	register_taxonomy( 'cropx_testimonial_category', array( 'cropx_testimonial' ), array(
		'labels' => array(
			'name'          => __( 'Categories',            'cropx' ),
			'singular_name' => __( 'Category',              'cropx' ),
			'add_new_item'  => __( 'Add New Category',      'cropx' ),
			'edit_item'     => __( 'Edit Category',         'cropx' ),
			'search_items'  => __( 'Search Categories',     'cropx' ),
			'all_items'     => __( 'All Categories',        'cropx' ),
			'menu_name'     => __( 'Categories',            'cropx' ),
		),
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'hierarchical'      => true,
		'show_admin_column' => false,
	) );

	// Region — required tag. World region parent terms are seeded below;
	// editors can add an optional state/sub-region as a child term of the
	// relevant world region (e.g. "North America" > "California").
	register_taxonomy( 'cropx_testimonial_region', array( 'cropx_testimonial' ), array(
		'labels' => array(
			'name'          => __( 'Regions',               'cropx' ),
			'singular_name' => __( 'Region',                'cropx' ),
			'add_new_item'  => __( 'Add New Region',        'cropx' ),
			'edit_item'     => __( 'Edit Region',           'cropx' ),
			'search_items'  => __( 'Search Regions',        'cropx' ),
			'all_items'     => __( 'All Regions',           'cropx' ),
			'menu_name'     => __( 'Regions',                'cropx' ),
			'parent_item'   => __( 'Parent Region',         'cropx' ),
			'parent_item_colon' => __( 'Parent Region:',    'cropx' ),
		),
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'hierarchical'      => true,
		'show_admin_column' => false,
	) );

	// Product — required tag. Free-tagging (not hierarchical) since the
	// product lineup changes over time and this list-table column is meant
	// to be scannable at a glance.
	register_taxonomy( 'cropx_testimonial_product', array( 'cropx_testimonial' ), array(
		'labels' => array(
			'name'                       => __( 'Products',                       'cropx' ),
			'singular_name'              => __( 'Product',                        'cropx' ),
			'search_items'               => __( 'Search Products',                'cropx' ),
			'all_items'                  => __( 'All Products',                   'cropx' ),
			'edit_item'                  => __( 'Edit Product',                   'cropx' ),
			'update_item'                => __( 'Update Product',                 'cropx' ),
			'add_new_item'               => __( 'Add New Product',                'cropx' ),
			'new_item_name'              => __( 'New Product Name',               'cropx' ),
			'separate_items_with_commas' => __( 'Separate products with commas',  'cropx' ),
			'add_or_remove_items'        => __( 'Add or remove products',         'cropx' ),
			'choose_from_most_used'      => __( 'Choose from the most used products', 'cropx' ),
			'not_found'                  => __( 'No products found.',            'cropx' ),
			'menu_name'                  => __( 'Products',                       'cropx' ),
		),
		'hierarchical'      => false,
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'show_admin_column' => true, // shows as its own column on the Quotes list page
		'show_tagcloud'     => false,
	) );

	// Business Name — required tag. Free-tagging, same reasoning as Product.
	register_taxonomy( 'cropx_testimonial_business', array( 'cropx_testimonial' ), array(
		'labels' => array(
			'name'                       => __( 'Business Names',                      'cropx' ),
			'singular_name'              => __( 'Business Name',                       'cropx' ),
			'search_items'               => __( 'Search Business Names',               'cropx' ),
			'all_items'                  => __( 'All Business Names',                  'cropx' ),
			'edit_item'                  => __( 'Edit Business Name',                  'cropx' ),
			'update_item'                => __( 'Update Business Name',                'cropx' ),
			'add_new_item'               => __( 'Add New Business Name',               'cropx' ),
			'new_item_name'              => __( 'New Business Name',                   'cropx' ),
			'separate_items_with_commas' => __( 'Separate business names with commas', 'cropx' ),
			'add_or_remove_items'        => __( 'Add or remove business names',        'cropx' ),
			'choose_from_most_used'      => __( 'Choose from the most used business names', 'cropx' ),
			'not_found'                  => __( 'No business names found.',           'cropx' ),
			'menu_name'                  => __( 'Business Names',                      'cropx' ),
		),
		'hierarchical'      => false,
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'show_admin_column' => true, // shows as its own column on the Quotes list page
		'show_tagcloud'     => false,
	) );

	// Segment — required tag. Hierarchical checkboxes over a small fixed set
	// mirroring the sitewide segment accent system (General/CropX, Enterprise,
	// Service Provider, On-Farm). Defaults seeded below.
	register_taxonomy( 'cropx_testimonial_segment', array( 'cropx_testimonial' ), array(
		'labels' => array(
			'name'          => __( 'Segments',              'cropx' ),
			'singular_name' => __( 'Segment',               'cropx' ),
			'add_new_item'  => __( 'Add New Segment',       'cropx' ),
			'edit_item'     => __( 'Edit Segment',          'cropx' ),
			'search_items'  => __( 'Search Segments',       'cropx' ),
			'all_items'     => __( 'All Segments',          'cropx' ),
			'menu_name'     => __( 'Segments',               'cropx' ),
		),
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'hierarchical'      => true,
		'show_admin_column' => false,
	) );

	// Crop Type — optional tag. Free-tagging, open-ended.
	register_taxonomy( 'cropx_testimonial_crop_type', array( 'cropx_testimonial' ), array(
		'labels' => array(
			'name'                       => __( 'Crop Types',                      'cropx' ),
			'singular_name'              => __( 'Crop Type',                       'cropx' ),
			'search_items'               => __( 'Search Crop Types',               'cropx' ),
			'all_items'                  => __( 'All Crop Types',                  'cropx' ),
			'edit_item'                  => __( 'Edit Crop Type',                  'cropx' ),
			'update_item'                => __( 'Update Crop Type',                'cropx' ),
			'add_new_item'               => __( 'Add New Crop Type',               'cropx' ),
			'new_item_name'              => __( 'New Crop Type Name',              'cropx' ),
			'separate_items_with_commas' => __( 'Separate crop types with commas', 'cropx' ),
			'add_or_remove_items'        => __( 'Add or remove crop types',        'cropx' ),
			'choose_from_most_used'      => __( 'Choose from the most used crop types', 'cropx' ),
			'not_found'                  => __( 'No crop types found.',           'cropx' ),
			'menu_name'                  => __( 'Crop Types',                      'cropx' ),
		),
		'hierarchical'      => false,
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'show_admin_column' => false,
		'show_tagcloud'     => false,
	) );
}

/**
 * Seed default taxonomy terms on init (not just theme activation).
 * wp_insert_term() silently skips duplicates so this is safe to run on
 * every request — it only does real work the first time each term is missing.
 * Running on init ensures terms exist on staging and production without
 * any manual setup after deploying the theme.
 */
add_action( 'init', 'cropx_seed_content_type_terms', 20 );
function cropx_seed_content_type_terms() {
	// Customer Story content types — case studies and video testimonials only.
	// White papers moved to Resources; press releases use standard Posts with
	// a "Press Release" category. Any pre-existing "white-paper" term is left
	// in place (not force-deleted) in case older content still references it —
	// it will simply stop appearing as a filter pill once no posts use it.
	$defaults = array(
		array( 'name' => 'Case Study',        'slug' => 'case-study'        ),
		array( 'name' => 'Video Testimonial', 'slug' => 'video-testimonial' ),
	);
	foreach ( $defaults as $term ) {
		if ( ! term_exists( $term['slug'], 'cropx_content_type' ) ) {
			wp_insert_term( $term['name'], 'cropx_content_type', array( 'slug' => $term['slug'] ) );
		}
	}

	// Resource types — brochures, datasheets, reports, white papers.
	// White papers moved here from Customer Stories (cropx_publication).
	$resource_defaults = array(
		array( 'name' => 'Brochure',    'slug' => 'brochure'    ),
		array( 'name' => 'Datasheet',   'slug' => 'datasheet'   ),
		array( 'name' => 'Report',      'slug' => 'report'      ),
		array( 'name' => 'White Paper', 'slug' => 'white-paper' ),
	);
	foreach ( $resource_defaults as $term ) {
		if ( ! term_exists( $term['slug'], 'cropx_resource_type' ) ) {
			wp_insert_term( $term['name'], 'cropx_resource_type', array( 'slug' => $term['slug'] ) );
		}
	}

	// Quotes — world-region parent terms. Editors can add an optional
	// state/sub-region as a child term of whichever region applies.
	$region_defaults = array(
		array( 'name' => 'North America',          'slug' => 'north-america' ),
		array( 'name' => 'Latin America',          'slug' => 'latin-america' ),
		array( 'name' => 'Europe',                 'slug' => 'europe' ),
		array( 'name' => 'Africa',                 'slug' => 'africa' ),
		array( 'name' => 'Middle East',             'slug' => 'middle-east' ),
		array( 'name' => 'Asia-Pacific',            'slug' => 'asia-pacific' ),
		array( 'name' => 'Australia & New Zealand', 'slug' => 'australia-new-zealand' ),
		array( 'name' => 'Global / Other',          'slug' => 'global-other' ),
	);
	foreach ( $region_defaults as $term ) {
		if ( ! term_exists( $term['slug'], 'cropx_testimonial_region' ) ) {
			wp_insert_term( $term['name'], 'cropx_testimonial_region', array( 'slug' => $term['slug'] ) );
		}
	}

	// Quotes — segment terms, matching the sitewide segment accent system.
	$segment_defaults = array(
		array( 'name' => 'General / CropX',    'slug' => 'general-cropx'     ),
		array( 'name' => 'Enterprise',         'slug' => 'enterprise'        ),
		array( 'name' => 'Service Provider',   'slug' => 'service-provider'  ),
		array( 'name' => 'On-Farm',            'slug' => 'on-farm'           ),
	);
	foreach ( $segment_defaults as $term ) {
		if ( ! term_exists( $term['slug'], 'cropx_testimonial_segment' ) ) {
			wp_insert_term( $term['name'], 'cropx_testimonial_segment', array( 'slug' => $term['slug'] ) );
		}
	}
}

// ── Publication permalinks: /results/{content-type-slug}/{post-name}/ ─────────
//
// Same idea as WordPress core's built-in %category%/%postname% structure for
// blog posts — except core only wires that up automatically for the 'post'
// type. For a CPT paired with a custom taxonomy, two pieces are needed:
//
//   1. Swap the %cropx_content_type% placeholder (set as part of the rewrite
//      slug in register_post_type() above) for the publication's real term
//      slug whenever WordPress builds a permalink.
//   2. Add an explicit rewrite rule so an incoming request to, say,
//      /results/case-study/my-annual-report/ is recognized and routed back
//      to that cropx_publication post.
//
// The CPT archive (/results/) and taxonomy archive (/content-type/case-study/)
// are untouched — this only changes single publication permalinks.

// Flat URL (Aug 2026 — same "drop the folder" SEO request Lauren made for Ag
// Insights, see inc/insights-archive.php's "Flat URLs" section for the fuller
// write-up of the shared mechanics). Single customer story permalinks now sit
// straight off the site root — /post-name/ — instead of nested under
// /results/{content-type}/. Deliberately does NOT touch the breadcrumb on
// single-cropx_publication.php — that still shows Results & Research > content
// type > title, since it's built from get_the_terms( ..., 'cropx_content_type' )
// and a link to the Results archive, neither of which comes from the post's
// own permalink.
add_filter( 'post_type_link', 'cropx_publication_permalink', 10, 2 );
function cropx_publication_permalink( $link, $post ) {
	if ( 'cropx_publication' !== $post->post_type ) {
		return $link;
	}
	return home_url( '/' . $post->post_name . '/' );
}

// Resolves an incoming flat URL back to the right customer story.
//
// This site's actual saved permalink structure resolves any bare single
// segment (e.g. /post-name/) through WordPress's own native rule —
// '([^/]+)(?:/([0-9]+))?/?$' — which sets the 'name' query var, not
// 'pagename'. That native rule always matches before our bottom-priority
// catch-all in insights-archive.php ever gets a chance to, so 'pagename' is
// never actually set for these requests (confirmed via matched_rule/
// matched_query debugging, Aug 2026). WordPress then defaults an unqualified
// 'name' lookup to post_type 'post' only — which is exactly why Ag Insights
// posts (real 'post' type) resolve on their own with no help needed here,
// while cropx_publication posts 404 before this filter had a real chance to
// step in. Checking 'name' as well as 'pagename' (belt-and-suspenders, in
// case a future permalink structure change ever does route through the
// pagename-based catch-all instead) fixes it. A real Page always keeps first
// claim on a slug, same rule the Ag Insights flat-URL resolver follows.
add_filter( 'request', 'cropx_publication_flat_url_request', 20 );
function cropx_publication_flat_url_request( $query_vars ) {
	$slug_key = '';
	if ( ! empty( $query_vars['name'] ) && false === strpos( $query_vars['name'], '/' ) ) {
		$slug_key = 'name';
	} elseif ( ! empty( $query_vars['pagename'] ) && false === strpos( $query_vars['pagename'], '/' ) ) {
		$slug_key = 'pagename';
	}

	if ( is_admin() || '' === $slug_key ) {
		return $query_vars;
	}

	$slug = $query_vars[ $slug_key ];

	if ( get_page_by_path( $slug, OBJECT, 'page' ) ) {
		return $query_vars;
	}

	// get_page_by_path() works for ANY post type despite its name — a direct
	// post_name + post_type lookup, bypassing WP_Query's broader query-building
	// pipeline entirely.
	$found = get_page_by_path( $slug, OBJECT, 'cropx_publication' );

	if ( ! $found || 'publish' !== $found->post_status ) {
		return $query_vars;
	}

	// 'name' + 'post_type' — NOT just 'cropx_publication' => $slug. WP::parse_request()
	// only promotes a bare post-type-named query var (e.g. 'cropx_publication') into an
	// actual 'name' lookup when 'name' is ALREADY non-empty at that point; since we're
	// replacing the query vars array wholesale here, 'name' would otherwise stay unset,
	// and WP_Query silently falls back to the post type's ARCHIVE query (every single
	// customer-story URL rendering the same Results & Research listing page — exactly
	// the bug this caused the first time around). Setting 'name' ourselves, the same
	// way the working Ag Insights resolver in insights-archive.php does for 'post',
	// sidesteps that entirely.
	return array(
		'name'      => $slug,
		'post_type' => 'cropx_publication',
	);
}

// Legacy rewrite rule — kept registered so any already-shared/bookmarked
// nested-style link (/results/{content-type}/{post-name}/) still resolves
// rather than 404ing, even though cropx_publication_permalink() above no
// longer generates that form. Harmless to leave in place: 'top' priority only
// matters relative to rules it could otherwise shadow, and a 3-segment path
// like this never overlaps with the single-segment flat-URL catch-all.
add_action( 'init', 'cropx_publication_rewrite_rules', 20 );
function cropx_publication_rewrite_rules() {
	// Pull the live list of content-type slugs (Case Study, Video Testimonial,
	// and any added later) rather than hardcoding them, so a new term added via
	// cropx_seed_content_type_terms() — or added by hand in wp-admin — is
	// automatically picked up the next time permalinks are flushed.
	$terms = get_terms( array(
		'taxonomy'   => 'cropx_content_type',
		'fields'     => 'slugs',
		'hide_empty' => false,
	) );
	if ( is_wp_error( $terms ) ) {
		$terms = array();
	}
	$terms[] = 'publication'; // matches the fallback slug used above

	$pattern = implode( '|', array_map( 'preg_quote', array_unique( $terms ) ) );

	add_rewrite_rule(
		'^results/(' . $pattern . ')/([^/]+)/?$',
		'index.php?cropx_publication=$matches[2]',
		'top'
	);
}

// ── Image sizes ───────────────────────────────────────────────────────────────
add_action( 'after_setup_theme', function () {
	// Resource document cover: scales to fit within 841×841px, no crop.
	// Preserves portrait (~595×841) or landscape (~841×595) proportions.
	add_image_size( 'cropx-doc-cover', 841, 841, false );

	// Testimonial avatar: hard-cropped to a perfect 250×250px square.
	// Used for headshots and company logos in testimonial blocks.
	add_image_size( 'cropx-testimonial-avatar', 250, 250, true );
} );

// ── Team Member: meta fields + field guide ───────────────────────────────────

add_action( 'init', function () {
	$meta_args = array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'default'       => '',
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	);
	register_post_meta( 'cropx_team_member', 'job_title',    array_merge( $meta_args, array( 'sanitize_callback' => 'sanitize_text_field' ) ) );
	register_post_meta( 'cropx_team_member', 'bio',          array_merge( $meta_args, array( 'sanitize_callback' => 'sanitize_textarea_field' ) ) );
	register_post_meta( 'cropx_team_member', 'linkedin_url', array_merge( $meta_args, array( 'sanitize_callback' => 'esc_url_raw' ) ) );
} );

add_action( 'add_meta_boxes', function () {

	// ── Field Guide ──────────────────────────────────────────────────────────
	add_meta_box(
		'cropx_team_member_guide',
		__( '📋 How to complete this entry', 'cropx' ),
		function () {
			echo '<div style="background:#f0f6fc;border-left:4px solid #0ca8c0;padding:12px 14px;font-size:13px;line-height:1.6">';
			echo '<table style="width:100%;border-collapse:collapse">';
			echo '<thead><tr style="text-align:left">'
				. '<th style="padding:4px 12px 4px 0;width:28%;color:#243565">' . esc_html__( 'Field', 'cropx' ) . '</th>'
				. '<th style="padding:4px 12px 4px 0;width:32%;color:#243565">' . esc_html__( 'What to enter', 'cropx' ) . '</th>'
				. '<th style="padding:4px 0;color:#243565">'                    . esc_html__( 'Example', 'cropx' )        . '</th>'
				. '</tr></thead><tbody>';
			$rows = array(
				array( "Post Title (Employee's Full Name) ★", 'First and last name',                                  'Lauren Hostetter' ),
				array( 'Job Title',                            'Role or position — optional',                         'VP of Marketing' ),
				array( 'Bio',                                  '2–3 sentence biography — optional, plain text',       'Lauren leads marketing strategy at CropX...' ),
				array( 'LinkedIn URL',                         'Full LinkedIn profile URL — optional',                'https://linkedin.com/in/laurenhostetter' ),
				array( 'Featured Image ★',                     'Professional headshot. Square crop recommended.',     '—' ),
			);
			foreach ( $rows as $row ) {
				echo '<tr style="border-top:1px solid #ddd">'
					. '<td style="padding:5px 12px 5px 0;font-weight:600;vertical-align:top">' . esc_html( $row[0] ) . '</td>'
					. '<td style="padding:5px 12px 5px 0;vertical-align:top">'                  . esc_html( $row[1] ) . '</td>'
					. '<td style="padding:5px 0;color:#757575;vertical-align:top">'             . esc_html( $row[2] ) . '</td>'
					. '</tr>';
			}
			echo '</tbody></table>';
			echo '<p style="margin:8px 0 0;font-size:12px;color:#757575">' . esc_html__( '★ Required  ·  Job Title and Bio are optional.', 'cropx' ) . '</p>';
			echo '</div>';
		},
		'cropx_team_member',
		'normal',
		'high'
	);

	// ── Job Title ────────────────────────────────────────────────────────────
	add_meta_box(
		'cropx_job_title',
		__( 'Job Title', 'cropx' ),
		function ( $post ) {
			$job_title = get_post_meta( $post->ID, 'job_title', true );
			wp_nonce_field( 'cropx_team_member_save', 'cropx_team_member_nonce' );
			echo '<input type="text" name="job_title" value="' . esc_attr( $job_title ) . '" '
				. 'style="width:100%" placeholder="' . esc_attr__( 'e.g. VP of Marketing', 'cropx' ) . '">';
			echo '<p style="margin:6px 0 0;color:#757575;font-size:12px">'
				. esc_html__( 'Displayed below the name on the Team page.', 'cropx' ) . '</p>';
		},
		'cropx_team_member',
		'normal',
		'high'
	);

	// ── Bio ──────────────────────────────────────────────────────────────────
	add_meta_box(
		'cropx_bio',
		__( 'Bio', 'cropx' ),
		function ( $post ) {
			$bio = get_post_meta( $post->ID, 'bio', true );
			echo '<textarea name="bio" rows="3" style="width:100%;resize:vertical" '
				. 'placeholder="' . esc_attr__( 'e.g. Lauren leads marketing strategy and brand development at CropX. She brings 10 years of experience in B2B SaaS and precision agriculture.', 'cropx' ) . '">'
				. esc_textarea( $bio ) . '</textarea>';
			echo '<p style="margin:6px 0 0;color:#757575;font-size:12px">'
				. esc_html__( '2–3 sentences. Plain text only — no formatting.', 'cropx' ) . '</p>';
		},
		'cropx_team_member',
		'normal',
		'high'
	);

	// ── LinkedIn URL ─────────────────────────────────────────────────────────
	add_meta_box(
		'cropx_linkedin_url',
		__( 'LinkedIn URL', 'cropx' ),
		function ( $post ) {
			$linkedin_url = get_post_meta( $post->ID, 'linkedin_url', true );
			echo '<input type="url" name="linkedin_url" value="' . esc_attr( $linkedin_url ) . '" '
				. 'style="width:100%" placeholder="' . esc_attr__( 'https://linkedin.com/in/…', 'cropx' ) . '">';
			echo '<p style="margin:6px 0 0;color:#757575;font-size:12px">'
				. esc_html__( 'Optional. When set, a LinkedIn badge appears on the person\'s card.', 'cropx' ) . '</p>';
		},
		'cropx_team_member',
		'normal',
		'high'
	);
} );

add_action( 'save_post_cropx_team_member', function ( $post_id ) {
	if ( ! isset( $_POST['cropx_team_member_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['cropx_team_member_nonce'], 'cropx_team_member_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	update_post_meta( $post_id, 'job_title',    sanitize_text_field( $_POST['job_title']       ?? '' ) );
	update_post_meta( $post_id, 'bio',          sanitize_textarea_field( $_POST['bio']         ?? '' ) );
	update_post_meta( $post_id, 'linkedin_url', esc_url_raw( $_POST['linkedin_url']            ?? '' ) );
} );

// ── Team Member: dedicated REST field for block editor ───────────────────────
// Registers cropx_team_data on the cropx_team_member REST response.
// This is more reliable than relying on the meta object (which can be empty
// depending on request context/auth) because register_rest_field always runs
// its get_callback and always appears in the response.
add_action( 'rest_api_init', function () {
	register_rest_field( 'cropx_team_member', 'cropx_team_data', array(
		'get_callback' => function ( $post_arr ) {
			$id = absint( $post_arr['id'] );
			return array(
				'job_title'    => (string) get_post_meta( $id, 'job_title',    true ),
				'bio'          => (string) get_post_meta( $id, 'bio',          true ),
				'linkedin_url' => (string) get_post_meta( $id, 'linkedin_url', true ),
			);
		},
		'update_callback' => null,
		'schema' => array(
			'type'       => 'object',
			'properties' => array(
				'job_title'    => array( 'type' => 'string' ),
				'bio'          => array( 'type' => 'string' ),
				'linkedin_url' => array( 'type' => 'string' ),
			),
		),
	) );
} );

// ── Resource: download meta fields ───────────────────────────────────────────
// download_url           — General/fallback file URL (single-format resources,
//                          or resources predating the language picker below).
// download_url_letter    — English, US Letter PDF URL.
// download_url_a4        — English, A4 PDF URL. This is the version treated
//                          as the default/fallback everywhere a default is
//                          needed (front-end picker's pre-selected option,
//                          cover thumbnail source) — see
//                          cropx_resource_brochure_versions() in helpers.php.
// download_url_{lang}_a4 — Same idea, one per additional language (es, pt,
//                          fr, de, nl, ro, ru — all A4/metric; there's no
//                          non-English "Letter" variant in practice). All
//                          optional — a brochure with only English files
//                          simply never shows the others.
// download_attachment_id — Media Library attachment ID of the PDF used for
//                          the card's cover thumbnail.
//                          WordPress auto-generates a first-page thumbnail for
//                          any PDF uploaded through the Media Library when
//                          Imagick/Ghostscript is available. Storing this ID
//                          lets render.php retrieve that auto-thumbnail via
//                          wp_get_attachment_image( $id, 'cropx-doc-cover' ).
//                          There's only ever one of these per resource (not
//                          one per language) — when a visitor switches
//                          versions in the front-end picker, the cover is
//                          re-rendered client-side from that version's own
//                          PDF via pdf.js instead (see view.js), rather than
//                          needing a separate attachment/thumbnail per
//                          language on the backend.
//
// Render logic for download buttons (handled in the block's render.php via
// cropx_resource_get_available_versions()):
//   0 versions found → fall back to download_url, then to the resource's own
//     permalink ("View resource"), exactly as before this feature existed.
//   1 version found  → a single plain "Download PDF" button — visually and
//     functionally identical to today, regardless of which version it is.
//   2+ versions found → a <select> of every available version (English A4
//     pre-selected when present) plus one "Download PDF" button that always
//     points at whichever version is currently selected.

add_action( 'init', function () {
	$url_args = array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'auth_callback'     => function () { return current_user_can( 'edit_posts' ); },
	);

	register_post_meta( 'cropx_resource', 'download_url',        $url_args );
	register_post_meta( 'cropx_resource', 'download_url_letter', $url_args );
	register_post_meta( 'cropx_resource', 'download_url_a4',     $url_args );
	register_post_meta( 'cropx_resource', 'download_url_es_a4',  $url_args );
	register_post_meta( 'cropx_resource', 'download_url_pt_a4',  $url_args );
	register_post_meta( 'cropx_resource', 'download_url_fr_a4',  $url_args );
	register_post_meta( 'cropx_resource', 'download_url_de_a4',  $url_args );
	register_post_meta( 'cropx_resource', 'download_url_nl_a4',  $url_args );
	register_post_meta( 'cropx_resource', 'download_url_ro_a4',  $url_args );
	register_post_meta( 'cropx_resource', 'download_url_ru_a4',  $url_args );

	register_post_meta( 'cropx_resource', 'download_attachment_id', array(
		'type'              => 'integer',
		'single'            => true,
		'show_in_rest'      => true,
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'auth_callback'     => function () { return current_user_can( 'edit_posts' ); },
	) );
} );

// Enqueue WP media scripts on the cropx_resource edit screen so the
// "Choose from Media Library" button can open the media frame.
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	global $post_type;
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) return;
	if ( 'cropx_resource' !== $post_type ) return;
	wp_enqueue_media();
} );

add_action( 'add_meta_boxes', function () {

	// ── Field Guide ──────────────────────────────────────────────────────────
	add_meta_box(
		'cropx_resource_guide',
		__( '📋 How to complete this entry', 'cropx' ),
		function () {
			echo '<div style="background:#f0f6fc;border-left:4px solid #0ca8c0;padding:12px 14px;font-size:13px;line-height:1.6">';
			echo '<table style="width:100%;border-collapse:collapse">';
			echo '<thead><tr style="text-align:left">'
				. '<th style="padding:4px 12px 4px 0;width:34%;color:#243565">' . esc_html__( 'Field', 'cropx' )        . '</th>'
				. '<th style="padding:4px 12px 4px 0;width:34%;color:#243565">' . esc_html__( 'What to enter', 'cropx' ) . '</th>'
				. '<th style="padding:4px 0;color:#243565">'                    . esc_html__( 'Example', 'cropx' )       . '</th>'
				. '</tr></thead><tbody>';
			$rows = array(
				array( 'Post Title (Document Title) ★', 'Full title of the document. Appears as the card title in resource listings and as the page heading at /resources/[slug]/',  'CropX Evato Sensor Datasheet' ),
				array( 'Excerpt',                        'Short description shown below the title in cards and listings',                                                               'How CropX helps almond growers reduce water usage by 28%' ),
				array( 'File URL(s) ★',                  'Link(s) to the PDF — fill in the English (A4) row in the Download Files box below at minimum; add more language/format rows if this brochure has them',  'https://cropx.com/files/evato-datasheet.pdf' ),
				array( 'Resource Type',                  'Tag the format: Brochure, Datasheet, or Report',                                                                             'Datasheet' ),
				array( 'Featured Image ★',              'Document cover image. Portrait ~595×841 px or landscape ~841×595 px.',                                                       '—' ),
			);
			foreach ( $rows as $row ) {
				echo '<tr style="border-top:1px solid #ddd">'
					. '<td style="padding:5px 12px 5px 0;font-weight:600;vertical-align:top">' . esc_html( $row[0] ) . '</td>'
					. '<td style="padding:5px 12px 5px 0;vertical-align:top">'                  . esc_html( $row[1] ) . '</td>'
					. '<td style="padding:5px 0;color:#757575;vertical-align:top">'             . esc_html( $row[2] ) . '</td>'
					. '</tr>';
			}
			echo '</tbody></table>';
			echo '<p style="margin:8px 0 0;font-size:12px;color:#757575">' . esc_html__( '★ Required  ·  Excerpt and Resource Type are optional.', 'cropx' ) . '</p>';
			echo '</div>';
		},
		'cropx_resource',
		'normal',
		'high'
	);

	// ── Download files ────────────────────────────────────────────────────────
	add_meta_box(
		'cropx_resource_download',
		__( 'Download Files ★', 'cropx' ),
		function ( $post ) {
			$url_general   = get_post_meta( $post->ID, 'download_url',           true );
			$attachment_id = (int) get_post_meta( $post->ID, 'download_attachment_id', true );
			wp_nonce_field( 'cropx_resource_download_save', 'cropx_resource_download_nonce' );
			?>
			<p style="margin:0 0 14px;color:#555;font-size:12px;line-height:1.5">
				<?php esc_html_e( 'Add a file for every language/format this brochure is available in. English (A4) is the fallback shown by default — if a resource has 2 or more files filled in below, visitors get a dropdown to pick a language/format before downloading. Leave any row blank if that version doesn\'t exist yet.', 'cropx' ); ?>
			</p>

			<?php
			$versions = cropx_resource_brochure_versions();
			$english  = array_filter( $versions, fn( $v ) => 'english' === $v['group'] );
			$other    = array_filter( $versions, fn( $v ) => 'other' === $v['group'] );

			$render_field = function ( $version ) use ( $post ) {
				$value = get_post_meta( $post->ID, $version['meta_key'], true );
				$id    = 'cropx_url_' . $version['code'];
				?>
				<div style="margin-bottom:12px">
					<label for="<?php echo esc_attr( $id ); ?>"
					       style="display:block;font-weight:600;margin-bottom:4px;font-size:12px">
						<?php echo esc_html( $version['label'] ); ?>
						<?php if ( 'en_a4' === $version['code'] ) : ?>
							<span style="font-weight:400;color:#0ca8c0;text-transform:uppercase;letter-spacing:0.04em;font-size:10px;margin-left:4px">
								<?php esc_html_e( 'default / fallback', 'cropx' ); ?>
							</span>
						<?php endif; ?>
					</label>
					<div style="display:flex;gap:8px;align-items:center">
						<input type="url"
						       name="<?php echo esc_attr( $version['meta_key'] ); ?>"
						       id="<?php echo esc_attr( $id ); ?>"
						       value="<?php echo esc_attr( $value ); ?>"
						       placeholder="https://example.com/file.pdf"
						       style="flex:1">
						<button type="button"
						        class="button cropx-media-pick-btn"
						        data-target="<?php echo esc_attr( $id ); ?>"
						        data-attach-target=""
						        style="white-space:nowrap">
							<?php esc_html_e( 'Media Library', 'cropx' ); ?>
						</button>
					</div>
				</div>
				<?php
			};
			?>

			<p style="margin:0 0 8px;font-weight:600;font-size:12px;color:#243565;text-transform:uppercase;letter-spacing:0.04em">
				<?php esc_html_e( 'English', 'cropx' ); ?>
			</p>
			<?php foreach ( $english as $version ) : $render_field( $version ); endforeach; ?>

			<p style="margin:18px 0 8px;font-weight:600;font-size:12px;color:#243565;text-transform:uppercase;letter-spacing:0.04em">
				<?php esc_html_e( 'Additional languages (optional)', 'cropx' ); ?>
			</p>
			<?php foreach ( $other as $version ) : $render_field( $version ); endforeach; ?>

			<div style="margin-top:18px;padding-top:14px;border-top:1px solid #ddd">
				<label for="cropx_download_url"
				       style="display:block;font-weight:600;margin-bottom:4px;font-size:12px">
					<?php esc_html_e( 'General URL (legacy fallback)', 'cropx' ); ?>
				</label>
				<div style="display:flex;gap:8px;align-items:center">
					<input type="url"
					       name="download_url"
					       id="cropx_download_url"
					       value="<?php echo esc_attr( $url_general ); ?>"
					       placeholder="https://example.com/file.pdf"
					       style="flex:1">
					<button type="button"
					        class="button cropx-media-pick-btn"
					        data-target="cropx_download_url"
					        data-attach-target="cropx_download_attachment_id"
					        style="white-space:nowrap">
						<?php esc_html_e( 'Media Library', 'cropx' ); ?>
					</button>
				</div>
				<p style="margin:4px 0 0;color:#757575;font-size:11px">
					<?php esc_html_e( 'Only used if none of the language fields above are filled in. Also sets the cover thumbnail source (Media Library PDFs only).', 'cropx' ); ?>
				</p>
			</div>

			<input type="hidden" name="download_attachment_id" id="cropx_download_attachment_id" value="<?php echo esc_attr( $attachment_id ); ?>">

			<script>
			(function($){
				$('.cropx-media-pick-btn').on('click', function(){
					var targetId = $(this).data('target');
					var attachId = $(this).data('attach-target');
					var frame = wp.media({
						title:  <?php echo wp_json_encode( __( 'Select or Upload PDF', 'cropx' ) ); ?>,
						button: { text: <?php echo wp_json_encode( __( 'Use this file', 'cropx' ) ); ?> },
						multiple: false,
					});
					frame.on('select', function(){
						var a = frame.state().get('selection').first().toJSON();
						$('#' + targetId).val(a.url);
						if (attachId) { $('#' + attachId).val(a.id); }
					});
					frame.open();
				});
			}(jQuery));
			</script>
			<?php
		},
		'cropx_resource',
		'normal',
		'high'
	);
} );

add_action( 'save_post_cropx_resource', function ( $post_id ) {
	if ( ! isset( $_POST['cropx_resource_download_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['cropx_resource_download_nonce'], 'cropx_resource_download_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	update_post_meta( $post_id, 'download_url', esc_url_raw( $_POST['download_url'] ?? '' ) );
	foreach ( cropx_resource_brochure_versions() as $version ) {
		update_post_meta(
			$post_id,
			$version['meta_key'],
			esc_url_raw( $_POST[ $version['meta_key'] ] ?? '' )
		);
	}
	update_post_meta( $post_id, 'download_attachment_id', absint( $_POST['download_attachment_id'] ?? 0 ) );
} );

// ── Results & Research: meta fields + field guide ────────────────────────────
// Covers case studies, research pieces, and video testimonials. Body content lives in the block
// editor. These meta fields capture structured data that renders outside
// the editable content area: download link, location, and up to three
// key findings stats shown in the hero band above the article.

add_action( 'init', function () {
	$meta_args = array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'default'       => '',
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	);
	// Download URL — PDF or external link. Shows a Download CTA box below the
	// article when set. Leave blank for web-only publications.
	register_post_meta( 'cropx_publication', 'pub_download_url', array_merge( $meta_args, array( 'sanitize_callback' => 'esc_url_raw' ) ) );
	// Location — short geographic string shown in the meta bar (e.g. "Arizona, USA").
	register_post_meta( 'cropx_publication', 'pub_location',     array_merge( $meta_args, array( 'sanitize_callback' => 'sanitize_text_field' ) ) );
	// Key findings — up to three stat/label pairs. Rendered in the "Key findings"
	// card between the hero image and the article body.
	foreach ( array( 'pub_stat_1_value', 'pub_stat_1_label',
	                 'pub_stat_2_value', 'pub_stat_2_label' ) as $key ) {
		register_post_meta( 'cropx_publication', $key, array_merge( $meta_args, array( 'sanitize_callback' => 'sanitize_text_field' ) ) );
	}
	// Case study sidebar details — structured "At a Glance" panel rendered in the
	// right sidebar column on case study singles. Leave blank on video testimonials.
	foreach ( array( 'cs_company', 'cs_region', 'cs_scale' ) as $key ) {
		register_post_meta( 'cropx_publication', $key, array_merge( $meta_args, array( 'sanitize_callback' => 'sanitize_text_field' ) ) );
	}
	// Challenge and Solution can be a sentence or two — use textarea sanitizer.
	foreach ( array( 'cs_challenge', 'cs_solution' ) as $key ) {
		register_post_meta( 'cropx_publication', $key, array_merge( $meta_args, array( 'sanitize_callback' => 'sanitize_textarea_field' ) ) );
	}
	// Hide "At a Glance" — editor toggle (Document Settings sidebar) to omit the
	// right-sidebar "At a Glance" card (cs_company/cs_region/cs_scale/
	// cs_challenge/cs_solution above) from the published page even when that
	// data is filled in. Defaults to false (card shown) so existing published
	// stories are unaffected. See single-cropx_publication.php for the render
	// check and pub-single.css's .pub-body-layout--no-sidebar for the
	// collapse-to-one-column layout used when the card is hidden.
	register_post_meta( 'cropx_publication', 'cropx_hide_at_a_glance', array(
		'type'              => 'boolean',
		'single'            => true,
		'show_in_rest'      => true,
		'default'           => false,
		'sanitize_callback' => 'rest_sanitize_boolean',
		'auth_callback'     => function () { return current_user_can( 'edit_posts' ); },
	) );
} );

// ── Blog posts: table of contents toggle ─────────────────────────────────────
// Editor toggle (Document Settings sidebar) to hide the auto-generated
// right-side table of contents on a single blog post. Defaults to false (ToC
// shown) so existing posts are unaffected. See single.php — the two-column
// grid is left as-is when hidden (article column width doesn't change);
// only the <aside> markup is skipped, leaving that space blank.
add_action( 'init', function () {
	register_post_meta( 'post', 'cropx_hide_toc', array(
		'type'              => 'boolean',
		'single'            => true,
		'show_in_rest'      => true,
		'default'           => false,
		'sanitize_callback' => 'rest_sanitize_boolean',
		'auth_callback'     => function () { return current_user_can( 'edit_posts' ); },
	) );
} );

add_action( 'add_meta_boxes', function () {

	// ── Field Guide ──────────────────────────────────────────────────────────
	add_meta_box(
		'cropx_publication_guide',
		__( '📋 How to complete this Results & Research entry', 'cropx' ),
		function () {
			echo '<div style="background:#f0f6fc;border-left:4px solid #0ca8c0;padding:12px 14px;font-size:13px;line-height:1.6">';
			echo '<table style="width:100%;border-collapse:collapse">';
			echo '<thead><tr style="text-align:left">'
				. '<th style="padding:4px 12px 4px 0;width:28%;color:#243565">' . esc_html__( 'Field', 'cropx' )        . '</th>'
				. '<th style="padding:4px 12px 4px 0;width:34%;color:#243565">' . esc_html__( 'What to enter', 'cropx' ) . '</th>'
				. '<th style="padding:4px 0;color:#243565">'                    . esc_html__( 'Example', 'cropx' )       . '</th>'
				. '</tr></thead><tbody>';
			$rows = array(
				array( 'Post Title ★',         'The full headline as it appears on the page',                         '40% Water Reduction on a 125-Acre Arizona Alfalfa Pivot' ),
				array( 'Excerpt ★',            'Lead paragraph shown below the title — 2–4 sentences, plain text',   'Center-pivot irrigation is the backbone of large-scale alfalfa...' ),
				array( 'Content (body) ★',     'Full article composed in the block editor',                           '—' ),
				array( 'Content Type ★',       'Tag as Case Study, Research Results, or Video Testimonial using the taxonomy panel', 'Case Study' ),
				array( 'Featured Image',        'Hero image at the top of the page. Landscape 16:9 or wider.',        '—' ),
				array( 'Location',              'Short location string shown in the meta row',                         'Arizona, USA' ),
				array( 'Key Findings 1–3',      'Stat + label pairs. Leave blank to hide the Key Findings card.',     '40% / Reduction in water consumption' ),
				array( 'Download URL',          'Link to a downloadable PDF. Leave blank to hide the Download button.', 'https://cropx.com/files/alfalfa-case-study.pdf' ),
				array( 'CS Details (company, region, scale, challenge, solution)', 'Case studies only — fills the "At a Glance" sidebar. Leave blank on video testimonials.', 'Sonoma Vineyards · California, USA · 1,200 ha' ),
			);
			foreach ( $rows as $row ) {
				echo '<tr style="border-top:1px solid #ddd">'
					. '<td style="padding:5px 12px 5px 0;font-weight:600;vertical-align:top">' . esc_html( $row[0] ) . '</td>'
					. '<td style="padding:5px 12px 5px 0;vertical-align:top">'                  . esc_html( $row[1] ) . '</td>'
					. '<td style="padding:5px 0;color:#757575;vertical-align:top">'             . esc_html( $row[2] ) . '</td>'
					. '</tr>';
			}
			echo '</tbody></table>';
			echo '<p style="margin:8px 0 0;font-size:12px;color:#757575">' . esc_html__( '★ Required  ·  Featured Image, Location, Key Findings, and Download URL are optional but strongly recommended for case studies and research pieces.', 'cropx' ) . '</p>';
			echo '</div>';
		},
		'cropx_publication',
		'normal',
		'high'
	);

	// ── Publication Details ───────────────────────────────────────────────────
	add_meta_box(
		'cropx_publication_details',
		__( 'Results & Research Details', 'cropx' ),
		function ( $post ) {
			$location     = get_post_meta( $post->ID, 'pub_location',     true );
			$download_url = get_post_meta( $post->ID, 'pub_download_url', true );
			wp_nonce_field( 'cropx_publication_save', 'cropx_publication_nonce' );

			echo '<p><label style="display:block;font-weight:600;margin-bottom:3px">'
				. esc_html__( 'Location', 'cropx' ) . '</label>';
			echo '<input type="text" name="pub_location" value="' . esc_attr( $location ) . '" '
				. 'style="width:100%" placeholder="' . esc_attr__( 'e.g. Arizona, USA', 'cropx' ) . '"></p>';
			echo '<p style="margin:-8px 0 12px;color:#757575;font-size:12px">'
				. esc_html__( 'Short geographic location shown in the meta row below the title.', 'cropx' ) . '</p>';

			echo '<p><label style="display:block;font-weight:600;margin-bottom:3px">'
				. esc_html__( 'Download URL', 'cropx' ) . '</label>';
			echo '<input type="url" name="pub_download_url" value="' . esc_attr( $download_url ) . '" '
				. 'style="width:100%" placeholder="https://example.com/file.pdf"></p>';
			echo '<p style="margin:-8px 0 0;color:#757575;font-size:12px">'
				. esc_html__( 'Link to a PDF download. When set, a "Download PDF" button appears below the article. Leave blank to omit it.', 'cropx' ) . '</p>';
		},
		'cropx_publication',
		'normal',
		'high'
	);

	// ── Key Findings ─────────────────────────────────────────────────────────
	add_meta_box(
		'cropx_publication_stats',
		__( 'Key Findings (up to 2)', 'cropx' ),
		function ( $post ) {
			$stats = array();
			for ( $i = 1; $i <= 2; $i++ ) {
				$stats[ $i ] = array(
					'value' => get_post_meta( $post->ID, "pub_stat_{$i}_value", true ),
					'label' => get_post_meta( $post->ID, "pub_stat_{$i}_label", true ),
				);
			}
			echo '<p style="margin:0 0 12px;color:#757575;font-size:12px">'
				. esc_html__( 'Fill in either or both stat/label pairs. Pairs with a value are displayed in a "Key Findings" card between the hero image and the article. Leave all blank to hide the card.', 'cropx' ) . '</p>';
			for ( $i = 1; $i <= 2; $i++ ) {
				echo '<div style="display:grid;grid-template-columns:1fr 2fr;gap:8px;margin-bottom:10px">';
				echo '<div><label style="display:block;font-weight:600;margin-bottom:3px;font-size:12px">'
					. sprintf( esc_html__( 'Stat %d — Value', 'cropx' ), $i ) . '</label>'
					. '<input type="text" name="pub_stat_' . $i . '_value" value="' . esc_attr( $stats[ $i ]['value'] ) . '" '
					. 'style="width:100%" placeholder="' . esc_attr__( 'e.g. 40%', 'cropx' ) . '"></div>';
				echo '<div><label style="display:block;font-weight:600;margin-bottom:3px;font-size:12px">'
					. sprintf( esc_html__( 'Stat %d — Label', 'cropx' ), $i ) . '</label>'
					. '<input type="text" name="pub_stat_' . $i . '_label" value="' . esc_attr( $stats[ $i ]['label'] ) . '" '
					. 'style="width:100%" placeholder="' . esc_attr__( 'e.g. Reduction in water consumption', 'cropx' ) . '"></div>';
				echo '</div>';
			}
		},
		'cropx_publication',
		'normal',
		'high'
	);

	// ── Case Study Details ────────────────────────────────────────────────────
	// These fields populate the "At a Glance" sidebar panel on case study
	// singles. Leave all blank for video testimonials — they'll be ignored.
	add_meta_box(
		'cropx_cs_details',
		__( 'Case Study Details (sidebar panel)', 'cropx' ),
		function ( $post ) {
			$cs_company   = get_post_meta( $post->ID, 'cs_company',   true );
			$cs_region    = get_post_meta( $post->ID, 'cs_region',    true );
			$cs_challenge = get_post_meta( $post->ID, 'cs_challenge', true );
			$cs_solution  = get_post_meta( $post->ID, 'cs_solution',  true );
			$cs_scale     = get_post_meta( $post->ID, 'cs_scale',     true );
			// Nonce already output by the Results & Research Details meta box above.
			echo '<p style="margin:0 0 12px;color:#757575;font-size:12px">'
				. esc_html__( 'These fields appear in the "At a Glance" sidebar panel on case study pages. Leave blank for video testimonials — they will be ignored.', 'cropx' ) . '</p>';

			$fields = array(
				'cs_company'   => array( 'Company / operation name', 'e.g. Sonoma Vineyards' ),
				'cs_region'    => array( 'Region / country',          'e.g. California, USA' ),
				'cs_scale'     => array( 'Scale',                     'e.g. 1,200 ha across 4 farms' ),
				'cs_challenge' => array( 'Challenge',                 'One or two sentences describing the main problem.' ),
				'cs_solution'  => array( 'Solution deployed',         'One or two sentences describing what CropX provided.' ),
			);
			$values = compact( 'cs_company', 'cs_region', 'cs_scale', 'cs_challenge', 'cs_solution' );

			foreach ( $fields as $key => $meta ) {
				$is_textarea = in_array( $key, array( 'cs_challenge', 'cs_solution' ), true );
				echo '<p><label style="display:block;font-weight:600;margin-bottom:3px">'
					. esc_html( $meta[0] ) . '</label>';
				if ( $is_textarea ) {
					echo '<textarea name="' . esc_attr( $key ) . '" rows="3" '
						. 'style="width:100%" placeholder="' . esc_attr( $meta[1] ) . '">'
						. esc_textarea( $values[ $key ] ?? '' ) . '</textarea>';
				} else {
					echo '<input type="text" name="' . esc_attr( $key ) . '" '
						. 'value="' . esc_attr( $values[ $key ] ?? '' ) . '" '
						. 'style="width:100%" placeholder="' . esc_attr( $meta[1] ) . '">';
				}
				echo '</p>';
			}
		},
		'cropx_publication',
		'normal',
		'high'
	);
} );

add_action( 'save_post_cropx_publication', function ( $post_id ) {
	if ( ! isset( $_POST['cropx_publication_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['cropx_publication_nonce'], 'cropx_publication_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	update_post_meta( $post_id, 'pub_download_url', esc_url_raw( $_POST['pub_download_url']      ?? '' ) );
	update_post_meta( $post_id, 'pub_location',     sanitize_text_field( $_POST['pub_location']  ?? '' ) );
	for ( $i = 1; $i <= 2; $i++ ) {
		update_post_meta( $post_id, "pub_stat_{$i}_value", sanitize_text_field( $_POST["pub_stat_{$i}_value"] ?? '' ) );
		update_post_meta( $post_id, "pub_stat_{$i}_label", sanitize_text_field( $_POST["pub_stat_{$i}_label"] ?? '' ) );
	}
	// Case study sidebar details.
	update_post_meta( $post_id, 'cs_company', sanitize_text_field( $_POST['cs_company'] ?? '' ) );
	update_post_meta( $post_id, 'cs_region',  sanitize_text_field( $_POST['cs_region']  ?? '' ) );
	update_post_meta( $post_id, 'cs_scale',   sanitize_text_field( $_POST['cs_scale']   ?? '' ) );
	update_post_meta( $post_id, 'cs_challenge', sanitize_textarea_field( $_POST['cs_challenge'] ?? '' ) );
	update_post_meta( $post_id, 'cs_solution',  sanitize_textarea_field( $_POST['cs_solution']  ?? '' ) );
} );

// ── Enforce single content-type term (server-side safety net) ─────────────────
//
// The Gutenberg JS enforcer (below) handles this in real time in the editor.
// This hook is the server-side fallback: if somehow two terms are saved
// (e.g. via a direct REST call or bulk-edit), keep only the first one.
// Priority 99 — runs after WordPress has already persisted the taxonomy.

add_action( 'save_post_cropx_publication', function ( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	$terms = wp_get_post_terms( $post_id, 'cropx_content_type', array( 'fields' => 'ids' ) );
	if ( is_wp_error( $terms ) || count( $terms ) <= 1 ) return;
	// More than one term — keep only the first (oldest) one.
	wp_set_post_terms( $post_id, array( $terms[0] ), 'cropx_content_type' );
}, 99 );

// ── Enforce single content-type term (Gutenberg block editor) ─────────────────
//
// Registers a tiny inline script on the publication edit screen that watches
// the editor data store. When an editor checks a second taxonomy term the
// script immediately removes the previously selected one, making the taxonomy
// behave like a radio group in the Gutenberg sidebar panel.

add_action( 'enqueue_block_editor_assets', function () {
	$screen = get_current_screen();
	if ( ! $screen || 'cropx_publication' !== $screen->post_type ) return;

	// Register a handle with no src so we can attach an inline script to it.
	wp_register_script(
		'cropx-pub-type-enforcer',
		false,
		array( 'wp-data', 'wp-edit-post' ),
		false,
		true // load in footer — after wp-data and core editor scripts
	);
	wp_enqueue_script( 'cropx-pub-type-enforcer' );

	wp_add_inline_script( 'cropx-pub-type-enforcer', '
(function () {
	"use strict";
	var prevTerms = [];

	wp.data.subscribe( function () {
		var editor = wp.data.select( "core/editor" );
		if ( ! editor ) return;

		var terms = editor.getEditedPostAttribute( "cropx_content_type" );
		if ( ! Array.isArray( terms ) ) return;

		if ( terms.length <= 1 ) {
			prevTerms = terms.slice();
			return;
		}

		// Two or more terms detected — find the newly added one and keep only it.
		var newTerm = terms.find( function ( t ) {
			return prevTerms.indexOf( t ) === -1;
		} );
		var keep = ( newTerm !== undefined ) ? newTerm : terms[ terms.length - 1 ];

		wp.data.dispatch( "core/editor" ).editPost( { cropx_content_type: [ keep ] } );
		prevTerms = [ keep ];
	} );
}());
	' );
} );

// ── Dealer: meta fields + field guide ────────────────────────────────────────

add_action( 'init', function () {
	foreach ( array( 'dealer_website', 'dealer_phone', 'dealer_email', 'dealer_address', 'dealer_region', 'dealer_image_type' ) as $key ) {
		register_post_meta( 'cropx_dealer', $key, array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'default'           => '',
			'sanitize_callback' => $key === 'dealer_website' ? 'esc_url_raw' : 'sanitize_text_field',
			'auth_callback'     => function () { return current_user_can( 'edit_posts' ); },
		) );
	}
	// Lat/lng stored as strings (floats serialized to text) for simplicity.
	foreach ( array( 'dealer_lat', 'dealer_lng' ) as $key ) {
		register_post_meta( 'cropx_dealer', $key, array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => function () { return current_user_can( 'edit_posts' ); },
		) );
	}
	// Active flag — '1' = show on map, '0' = hidden. Defaults to '1' so all
	// existing dealers without this meta set are treated as active.
	register_post_meta( 'cropx_dealer', 'dealer_active', array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'default'           => '1',
		'sanitize_callback' => 'sanitize_text_field',
		'auth_callback'     => function () { return current_user_can( 'edit_posts' ); },
	) );
} );

/**
 * Restrict the Dealers archive (archive-cropx_dealer.php, /dealer-archive/)
 * to active dealers only — a dealer an editor has hidden from the
 * interactive dealer-finder map (dealer_active = '0') shouldn't reappear in
 * this plain list either. dealer_active defaults to '1' and is always
 * explicitly saved as '1' or '0' on every save (see the dealer_active
 * checkbox handling further down this file), so "meta doesn't exist" is only
 * ever a legacy-row safety net, not the normal case.
 *
 * IMPORTANT: this must live here — at file scope, hooked directly into
 * pre_get_posts — and NOT inside archive-cropx_dealer.php itself. Template
 * files are only `include`d by template-loader.php after WordPress has
 * already built and run the main query, so a pre_get_posts callback
 * registered inside a template file never fires for that page's own main
 * query; it silently only affects a page's own main query on some
 * *following* request, which never actually helps this one. This file is
 * `require_once`'d at theme bootstrap (functions.php), long before the main
 * query runs, so registering it here is what actually works. (Also uses
 * $query->is_post_type_archive() rather than the global is_post_type_archive()
 * — same reasoning already documented on the near-identical category-widening
 * filter in inc/insights-archive.php: the global conditional tag functions
 * aren't reliably populated yet this early for the main query.)
 */
add_action( 'pre_get_posts', function ( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'cropx_dealer' ) ) {
		return;
	}

	$query->set( 'meta_query', array(
		'relation' => 'OR',
		array(
			'key'     => 'dealer_active',
			'value'   => '0',
			'compare' => '!=',
		),
		array(
			'key'     => 'dealer_active',
			'compare' => 'NOT EXISTS',
		),
	) );
} );

add_action( 'add_meta_boxes', function () {

	// ── Field Guide ──────────────────────────────────────────────────────────
	add_meta_box(
		'cropx_dealer_guide',
		__( '📋 How to complete this entry', 'cropx' ),
		function () {
			echo '<div style="background:#f0f6fc;border-left:4px solid #0ca8c0;padding:12px 14px;font-size:13px;line-height:1.6">';
			echo '<table style="width:100%;border-collapse:collapse">';
			echo '<thead><tr style="text-align:left">'
				. '<th style="padding:4px 12px 4px 0;width:28%;color:#243565">' . esc_html__( 'Field', 'cropx' )        . '</th>'
				. '<th style="padding:4px 12px 4px 0;width:32%;color:#243565">' . esc_html__( 'What to enter', 'cropx' ) . '</th>'
				. '<th style="padding:4px 0;color:#243565">'                    . esc_html__( 'Example', 'cropx' )       . '</th>'
				. '</tr></thead><tbody>';
			$rows = array(
				array( 'Post Title (Company or Individual Name) ★', 'Dealer company or individual name', 'Agri Partners Inc.' ),
				array( 'Region ★',       'Geographic region for directory sorting',             'North America' ),
				array( 'Website',        'Full URL including https://',                          'https://agripartners.com' ),
				array( 'Phone',          'Include country code',                                '+1 (555) 000-0000' ),
				array( 'Email',          'Primary contact email',                               'contact@agripartners.com' ),
				array( 'Address',        'Street address',                                      '123 Main St, Fresno, CA' ),
				array( 'Featured Image', 'Company logo or dealer headshot — optional. Use the image type toggle to flag which it is.', '—' ),
			);
			foreach ( $rows as $row ) {
				echo '<tr style="border-top:1px solid #ddd">'
					. '<td style="padding:5px 12px 5px 0;font-weight:600;vertical-align:top">' . esc_html( $row[0] ) . '</td>'
					. '<td style="padding:5px 12px 5px 0;vertical-align:top">'                  . esc_html( $row[1] ) . '</td>'
					. '<td style="padding:5px 0;color:#757575;vertical-align:top">'             . esc_html( $row[2] ) . '</td>'
					. '</tr>';
			}
			echo '</tbody></table>';
			echo '<p style="margin:8px 0 0;font-size:12px;color:#757575">' . esc_html__( '★ Required  ·  All other fields are optional.', 'cropx' ) . '</p>';
			echo '</div>';
		},
		'cropx_dealer',
		'normal',
		'high'
	);

	// ── Dealer Details ───────────────────────────────────────────────────────
	add_meta_box(
		'cropx_dealer_details',
		__( 'Dealer Details', 'cropx' ),
		function ( $post ) {
			$website    = get_post_meta( $post->ID, 'dealer_website',    true );
			$phone      = get_post_meta( $post->ID, 'dealer_phone',      true );
			$email      = get_post_meta( $post->ID, 'dealer_email',      true );
			$address    = get_post_meta( $post->ID, 'dealer_address',    true );
			$region     = get_post_meta( $post->ID, 'dealer_region',     true );
			$image_type = get_post_meta( $post->ID, 'dealer_image_type', true ) ?: 'logo';
			$lat        = get_post_meta( $post->ID, 'dealer_lat',        true );
			$lng        = get_post_meta( $post->ID, 'dealer_lng',        true );
			// '0' = inactive; anything else (including empty = not yet set) = active.
			$active     = get_post_meta( $post->ID, 'dealer_active', true );
			$is_active  = $active !== '0';
			wp_nonce_field( 'cropx_dealer_details_save', 'cropx_dealer_details_nonce' );

			// ── Active / Inactive toggle ─────────────────────────────────────────
			$badge_bg     = $is_active ? '#edfaef' : '#fff5f5';
			$badge_border = $is_active ? '#4caf50' : '#e57373';
			$badge_color  = $is_active ? '#2e7d32' : '#c62828';
			$badge_icon   = $is_active ? '✓' : '✗';
			$badge_label  = $is_active
				? esc_html__( 'Active — showing on map', 'cropx' )
				: esc_html__( 'Inactive — hidden from map', 'cropx' );
			echo '<div style="margin-bottom:14px;padding:10px 12px;border-radius:4px;background:'
				. esc_attr( $badge_bg ) . ';border:1px solid ' . esc_attr( $badge_border ) . '">';
			echo '<label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:600">';
			echo '<input type="checkbox" name="dealer_active" value="1"' . checked( $is_active, true, false ) . '>';
			echo '<span style="color:' . esc_attr( $badge_color ) . '">'
				. esc_html( $badge_icon ) . ' ' . $badge_label . '</span>';
			echo '</label>';
			echo '<p style="margin:4px 0 0 24px;font-size:12px;color:#757575">'
				. esc_html__( 'Uncheck to hide this dealer from the map without deleting any data.', 'cropx' )
				. '</p>';
			echo '</div>';

			$field = function ( $label, $name, $value, $type = 'text', $placeholder = '', $required = false ) {
				$req = $required ? ' <span style="color:#d63638" title="Required">★</span>' : '';
				echo '<p><label style="display:block;font-weight:600;margin-bottom:3px">'
					. esc_html( $label ) . $req . '</label>';
				echo '<input type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" '
					. 'style="width:100%" placeholder="' . esc_attr( $placeholder ) . '"></p>';
			};

			$field( __( 'Region',  'cropx' ), 'dealer_region',  $region,  'text',  'e.g. North America, Europe', true );
			$field( __( 'Website', 'cropx' ), 'dealer_website', $website, 'url',   'https://example.com' );
			$field( __( 'Phone',   'cropx' ), 'dealer_phone',   $phone,   'tel',   '+1 (555) 000-0000 (include country code)' );
			$field( __( 'Email',   'cropx' ), 'dealer_email',   $email,   'email', 'contact@example.com' );
			$field( __( 'Address', 'cropx' ), 'dealer_address', $address, 'text',  '123 Main St, City, State' );

			// ── Coordinates ──────────────────────────────────────────────────
			echo '<div style="border-top:1px solid #ddd;margin-top:12px;padding-top:12px">';
			echo '<p style="margin:0 0 6px;font-weight:600">' . esc_html__( 'Map Coordinates', 'cropx' ) . '</p>';
			echo '<p style="margin:0 0 10px;color:#757575;font-size:12px">'
				. esc_html__( 'To find coordinates: search for the dealer in ', 'cropx' )
				. '<a href="https://maps.google.com" target="_blank" rel="noopener">Google Maps</a>'
				. esc_html__( ', right-click the exact location, and click the coordinates shown at the top of the menu. Paste the two numbers into the fields below.', 'cropx' )
				. '</p>';
			echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">';
			echo '<p style="margin:0"><label style="display:block;font-weight:600;margin-bottom:3px">'
				. esc_html__( 'Latitude', 'cropx' ) . ' <span style="color:#d63638" title="Required for map pin">★</span></label>'
				. '<input type="text" name="dealer_lat" value="' . esc_attr( $lat ) . '" style="width:100%" placeholder="e.g. 41.8781" inputmode="decimal"></p>';
			echo '<p style="margin:0"><label style="display:block;font-weight:600;margin-bottom:3px">'
				. esc_html__( 'Longitude', 'cropx' ) . ' <span style="color:#d63638" title="Required for map pin">★</span></label>'
				. '<input type="text" name="dealer_lng" value="' . esc_attr( $lng ) . '" style="width:100%" placeholder="e.g. -87.6298" inputmode="decimal"></p>';
			echo '</div>';
			if ( $lat && $lng ) {
				$verify_url = 'https://www.google.com/maps?q=' . urlencode( $lat ) . ',' . urlencode( $lng );
				echo '<p style="margin:8px 0 0;font-size:12px">'
					. '<a href="' . esc_url( $verify_url ) . '" target="_blank" rel="noopener">'
					. esc_html__( '✓ Verify pin on Google Maps →', 'cropx' ) . '</a></p>';
			}
			echo '</div>';

			echo '<p style="margin-top:8px"><label style="display:block;font-weight:600;margin-bottom:6px">'
				. esc_html__( 'Featured image type (optional)', 'cropx' ) . '</label>';
			foreach ( array( 'logo' => __( 'Company logo', 'cropx' ), 'headshot' => __( 'Dealer headshot', 'cropx' ) ) as $val => $lbl ) {
				echo '<label style="display:inline-flex;align-items:center;gap:6px;margin-right:16px">';
				echo '<input type="radio" name="dealer_image_type" value="' . esc_attr( $val ) . '"'
					. checked( $image_type, $val, false ) . '> ' . esc_html( $lbl ) . '</label>';
			}
			echo '<p style="margin:6px 0 0;color:#757575;font-size:12px">'
				. esc_html__( 'Only set this if you have added a Featured Image above.', 'cropx' ) . '</p>';
			echo '</p>';
		},
		'cropx_dealer',
		'normal',
		'high'
	);
} );

add_action( 'save_post_cropx_dealer', function ( $post_id ) {
	if ( ! isset( $_POST['cropx_dealer_details_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['cropx_dealer_details_nonce'], 'cropx_dealer_details_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	update_post_meta( $post_id, 'dealer_website',    esc_url_raw( $_POST['dealer_website']    ?? '' ) );
	update_post_meta( $post_id, 'dealer_phone',      sanitize_text_field( $_POST['dealer_phone']      ?? '' ) );
	update_post_meta( $post_id, 'dealer_email',      sanitize_email( $_POST['dealer_email']            ?? '' ) );
	update_post_meta( $post_id, 'dealer_address',    sanitize_text_field( $_POST['dealer_address']    ?? '' ) );
	update_post_meta( $post_id, 'dealer_region',     sanitize_text_field( $_POST['dealer_region']     ?? '' ) );
	update_post_meta( $post_id, 'dealer_image_type', sanitize_text_field( $_POST['dealer_image_type'] ?? 'logo' ) );
	// Checkbox: present = '1' (active), absent = '0' (inactive).
	update_post_meta( $post_id, 'dealer_active', isset( $_POST['dealer_active'] ) ? '1' : '0' );
	// Store lat/lng as sanitized decimal strings.
	$lat = (string) (float) sanitize_text_field( $_POST['dealer_lat'] ?? '' );
	$lng = (string) (float) sanitize_text_field( $_POST['dealer_lng'] ?? '' );
	update_post_meta( $post_id, 'dealer_lat', $lat !== '0' ? $lat : '' );
	update_post_meta( $post_id, 'dealer_lng', $lng !== '0' ? $lng : '' );
} );

// ── Testimonial: meta fields + field guide ───────────────────────────────────

add_action( 'init', function () {
	$meta_args = array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'default'       => '',
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	);
	register_post_meta( 'cropx_testimonial', 'quote_text',   array_merge( $meta_args, array( 'sanitize_callback' => 'sanitize_textarea_field' ) ) );
	register_post_meta( 'cropx_testimonial', 'attribution',  array_merge( $meta_args, array( 'sanitize_callback' => 'sanitize_text_field' ) ) );
	// person_name holds the quoted person's full name. The post title no
	// longer doubles as the name field — see the title-placeholder filter
	// and guide box below for the new "[Business Name] - [Product]" title
	// convention this CPT now requires for backend searchability.
	register_post_meta( 'cropx_testimonial', 'person_name',  array_merge( $meta_args, array( 'sanitize_callback' => 'sanitize_text_field' ) ) );
} );

// Title-field placeholder — titles must now follow "[Business Name] -
// [CropX Product Name]" (e.g. "Reinke Manufacturing - Vertex Soil Sensor")
// so Quotes are searchable by business/product from the title alone. This is
// placeholder text only, not auto-fill: the Business Name and Product tags
// below are the source of truth for filtering/columns, and an editor may
// reference multiple businesses or products in one quote, so forcing the
// title to auto-derive from tag picks would need arbitrary tie-breaking and
// go stale if tags are edited later. The guide box spells out the
// convention explicitly for editors.
add_filter( 'enter_title_here', function ( $title, $post ) {
	if ( isset( $post->post_type ) && 'cropx_testimonial' === $post->post_type ) {
		return __( '[Business Name] - [CropX Product Name]', 'cropx' );
	}
	return $title;
}, 10, 2 );

add_action( 'add_meta_boxes', function () {

	// ── Field Guide ──────────────────────────────────────────────────────────
	add_meta_box(
		'cropx_testimonial_guide',
		__( '📋 How to complete this entry', 'cropx' ),
		function () {
			echo '<div style="background:#f0f6fc;border-left:4px solid #0ca8c0;padding:12px 14px;font-size:13px;line-height:1.6">';
			echo '<table style="width:100%;border-collapse:collapse">';
			echo '<thead><tr style="text-align:left">'
				. '<th style="padding:4px 12px 4px 0;width:28%;color:#243565">' . esc_html__( 'Field', 'cropx' )        . '</th>'
				. '<th style="padding:4px 12px 4px 0;width:32%;color:#243565">' . esc_html__( 'What to enter', 'cropx' ) . '</th>'
				. '<th style="padding:4px 0;color:#243565">'                    . esc_html__( 'Example', 'cropx' )       . '</th>'
				. '</tr></thead><tbody>';
			$rows = array(
				array( 'Post Title ★',   'Suggest naming in [Product Name] - [Quote Topic] format. This makes quotes easier to choose from when adding them to a block.', 'Reinke Manufacturing - Vertex Soil Sensor' ),
				array( "Person's Name ★", "First and last name of the person being quoted", 'Jane Smith' ),
				array( 'Quote ★',        'The spoken quote — no quotation marks, the design adds them', 'Our crop yields improved significantly...' ),
				array( 'Attribution ★',  'Role/title only — company now comes from the Business Name tag below', 'VP of Agriculture' ),
				array( 'Featured Image ★', 'Headshot or company logo. Must be a perfect square, at least 250×250 px.', '—' ),
				array( 'Region tag ★',   'World region (+ optional state/sub-region as a child term)', 'North America > California' ),
				array( 'Product tag ★', 'The CropX product the customer is discussing',              'Vertex Soil Sensor' ),
				array( 'Business Name tag ★', 'The business named in the quote',                       'Reinke Manufacturing' ),
				array( 'Segment tag ★', 'Which segment(s) this quote will appeal to',                  'Enterprise' ),
				array( 'Crop Type tag',  '(Optional) crop type discussed in the quote',                'Almonds' ),
			);
			foreach ( $rows as $row ) {
				echo '<tr style="border-top:1px solid #ddd">'
					. '<td style="padding:5px 12px 5px 0;font-weight:600;vertical-align:top">' . esc_html( $row[0] ) . '</td>'
					. '<td style="padding:5px 12px 5px 0;vertical-align:top">'                  . esc_html( $row[1] ) . '</td>'
					. '<td style="padding:5px 0;color:#757575;vertical-align:top">'             . esc_html( $row[2] ) . '</td>'
					. '</tr>';
			}
			echo '</tbody></table>';
			echo '<p style="margin:8px 0 0;font-size:12px;color:#757575">' . esc_html__( '★ All fields required  ·  Non-square images will be auto-cropped to a square.', 'cropx' ) . '</p>';
			echo '</div>';
		},
		'cropx_testimonial',
		'normal',
		'high'
	);

	// ── Person's Name ────────────────────────────────────────────────────────
	add_meta_box(
		'cropx_testimonial_person_name',
		__( "Person's Name ★", 'cropx' ),
		function ( $post ) {
			$person_name = get_post_meta( $post->ID, 'person_name', true );
			wp_nonce_field( 'cropx_testimonial_save', 'cropx_testimonial_nonce' );
			echo '<input type="text" name="person_name" value="' . esc_attr( $person_name ) . '" '
				. 'style="width:100%" placeholder="' . esc_attr__( 'e.g. Jane Smith', 'cropx' ) . '">';
			echo '<p style="margin:6px 0 0;color:#757575;font-size:12px">'
				. esc_html__( "First and last name of the person being quoted. The post title no longer holds this — it now follows the \"[Business Name] - [Product]\" convention below.", 'cropx' ) . '</p>';
		},
		'cropx_testimonial',
		'normal',
		'high'
	);

	// ── Quote ────────────────────────────────────────────────────────────────
	add_meta_box(
		'cropx_testimonial_quote',
		__( 'Quote ★', 'cropx' ),
		function ( $post ) {
			$quote = get_post_meta( $post->ID, 'quote_text', true );
			wp_nonce_field( 'cropx_testimonial_save', 'cropx_testimonial_nonce' );
			echo '<textarea name="quote_text" rows="4" style="width:100%;resize:vertical" '
				. 'placeholder="' . esc_attr__( 'e.g. Our crop yields improved by 18% in the first season after adopting CropX.', 'cropx' ) . '">'
				. esc_textarea( $quote ) . '</textarea>';
			echo '<p style="margin:6px 0 0;color:#757575;font-size:12px">'
				. esc_html__( 'The spoken quote. Do not add quotation marks — the design adds them automatically.', 'cropx' ) . '</p>';
		},
		'cropx_testimonial',
		'normal',
		'high'
	);

	// ── Attribution ──────────────────────────────────────────────────────────
	add_meta_box(
		'cropx_testimonial_attribution',
		__( 'Attribution ★', 'cropx' ),
		function ( $post ) {
			$attribution = get_post_meta( $post->ID, 'attribution', true );
			echo '<input type="text" name="attribution" value="' . esc_attr( $attribution ) . '" '
				. 'style="width:100%" placeholder="' . esc_attr__( 'e.g. VP of Agriculture', 'cropx' ) . '">';
			echo '<p style="margin:6px 0 0;color:#757575;font-size:12px">'
				. esc_html__( "The person's role/title, displayed below the quote. Company is no longer entered here — it comes from the Business Name tag.", 'cropx' ) . '</p>';
		},
		'cropx_testimonial',
		'normal',
		'high'
	);
} );

add_action( 'save_post_cropx_testimonial', function ( $post_id ) {
	if ( ! isset( $_POST['cropx_testimonial_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['cropx_testimonial_nonce'], 'cropx_testimonial_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	update_post_meta( $post_id, 'quote_text',   sanitize_textarea_field( $_POST['quote_text']   ?? '' ) );
	update_post_meta( $post_id, 'attribution',  sanitize_text_field( $_POST['attribution']      ?? '' ) );
	update_post_meta( $post_id, 'person_name',  sanitize_text_field( $_POST['person_name']      ?? '' ) );
} );
