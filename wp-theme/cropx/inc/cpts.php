<?php
/**
 * Custom Post Types and Taxonomies for CropX.
 *
 * Post types:
 *   cropx_case_study  — case studies, white papers, press releases
 *   cropx_team_member — team / about page
 *   cropx_testimonial — quotes used by testimonial blocks (no public pages)
 *
 * Taxonomy:
 *   cropx_content_type — applied to case studies (e.g. Case Study, White Paper, Press Release)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'cropx_register_post_types' );
function cropx_register_post_types() {

	// ── Case Study ──────────────────────────────────────────────────────────
	register_post_type( 'cropx_case_study', array(
		'labels' => array(
			'name'               => __( 'Case Studies',              'cropx' ),
			'singular_name'      => __( 'Case Study',                'cropx' ),
			'add_new'            => __( 'Add New',                   'cropx' ),
			'add_new_item'       => __( 'Add New Case Study',        'cropx' ),
			'edit_item'          => __( 'Edit Case Study',           'cropx' ),
			'new_item'           => __( 'New Case Study',            'cropx' ),
			'view_item'          => __( 'View Case Study',           'cropx' ),
			'search_items'       => __( 'Search Case Studies',       'cropx' ),
			'not_found'          => __( 'No case studies found.',    'cropx' ),
			'not_found_in_trash' => __( 'No case studies in trash.', 'cropx' ),
			'all_items'          => __( 'All Case Studies',          'cropx' ),
			'menu_name'          => __( 'Case Studies',              'cropx' ),
		),
		'public'            => true,
		'show_in_rest'      => true,
		'has_archive'       => true,
		'supports'          => array( 'title', 'excerpt', 'thumbnail', 'editor', 'custom-fields' ),
		'menu_icon'         => 'dashicons-portfolio',
		'rewrite'           => array( 'slug' => 'case-studies' ),
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
		'has_archive'  => false,
		'supports'     => array( 'title', 'excerpt', 'thumbnail', 'editor', 'custom-fields' ),
		'menu_icon'    => 'dashicons-groups',
		'rewrite'      => array( 'slug' => 'team' ),
	) );

	// ── Testimonial ─────────────────────────────────────────────────────────
	// No public pages — used only as a data source for blocks.
	register_post_type( 'cropx_testimonial', array(
		'labels' => array(
			'name'               => __( 'Testimonials',              'cropx' ),
			'singular_name'      => __( 'Testimonial',               'cropx' ),
			'add_new'            => __( 'Add New',                   'cropx' ),
			'add_new_item'       => __( 'Add New Testimonial',       'cropx' ),
			'edit_item'          => __( 'Edit Testimonial',          'cropx' ),
			'not_found'          => __( 'No testimonials found.',    'cropx' ),
			'not_found_in_trash' => __( 'No testimonials in trash.', 'cropx' ),
			'all_items'          => __( 'All Testimonials',          'cropx' ),
			'menu_name'          => __( 'Testimonials',              'cropx' ),
		),
		'public'       => false,
		'show_ui'      => true,
		'show_in_rest' => true,
		'has_archive'  => false,
		'supports'     => array( 'title', 'excerpt', 'thumbnail', 'editor', 'custom-fields' ),
		'menu_icon'    => 'dashicons-format-quote',
	) );
}

add_action( 'init', 'cropx_register_taxonomies' );
function cropx_register_taxonomies() {

	// ── Content Type (applied to Case Studies) ───────────────────────────────
	// Non-hierarchical (like tags). Default terms are seeded on activation;
	// editors can add more via Case Studies → Content Types in wp-admin.
	register_taxonomy( 'cropx_content_type', array( 'cropx_case_study' ), array(
		'labels' => array(
			'name'              => __( 'Content Types',        'cropx' ),
			'singular_name'     => __( 'Content Type',         'cropx' ),
			'add_new_item'      => __( 'Add New Content Type', 'cropx' ),
			'edit_item'         => __( 'Edit Content Type',    'cropx' ),
			'search_items'      => __( 'Search Content Types', 'cropx' ),
			'all_items'         => __( 'All Content Types',    'cropx' ),
			'menu_name'         => __( 'Content Types',        'cropx' ),
		),
		'public'            => true,
		'show_in_rest'      => true,
		'hierarchical'      => false,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'content-type' ),
	) );
}

/**
 * Seed default Content Type terms on theme activation.
 * Runs once — safe to call repeatedly (wp_insert_term ignores duplicates).
 */
add_action( 'after_switch_theme', 'cropx_seed_content_type_terms' );
function cropx_seed_content_type_terms() {
	$defaults = array(
		array( 'name' => 'Case Study',    'slug' => 'case-study'    ),
		array( 'name' => 'White Paper',   'slug' => 'white-paper'   ),
		array( 'name' => 'Press Release', 'slug' => 'press-release' ),
	);
	foreach ( $defaults as $term ) {
		if ( ! term_exists( $term['slug'], 'cropx_content_type' ) ) {
			wp_insert_term( $term['name'], 'cropx_content_type', array( 'slug' => $term['slug'] ) );
		}
	}
}
