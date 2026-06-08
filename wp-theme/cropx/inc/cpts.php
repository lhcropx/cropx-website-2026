<?php
/**
 * Custom Post Types and Taxonomies for CropX.
 *
 * Post types:
 *   cropx_publication — publications: case studies and white papers (evergreen)
 *   cropx_resource    — downloadable assets: brochures, datasheets, reports
 *   cropx_dealer      — dealer directory (Phase 2)
 *   cropx_team_member — team / about page
 *   cropx_testimonial — quotes used by testimonial blocks (no public pages)
 *   (standard post)   — blog articles and press releases (time-stamped, by category)
 *
 * Taxonomies:
 *   cropx_content_type  — applied to publications (Case Study, White Paper)
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

	// ── Publications (case studies, white papers) ────────────────────────────
	// Press releases live in standard Posts (category: Press Release) since they
	// are time-stamped news items, not evergreen documents.
	register_post_type( 'cropx_publication', array(
		'labels' => array(
			'name'               => __( 'Publications',               'cropx' ),
			'singular_name'      => __( 'Publication',                'cropx' ),
			'add_new'            => __( 'Add New',                    'cropx' ),
			'add_new_item'       => __( 'Add New Publication',        'cropx' ),
			'edit_item'          => __( 'Edit Publication',           'cropx' ),
			'new_item'           => __( 'New Publication',            'cropx' ),
			'view_item'          => __( 'View Publication',           'cropx' ),
			'search_items'       => __( 'Search Publications',        'cropx' ),
			'not_found'          => __( 'No publications found.',     'cropx' ),
			'not_found_in_trash' => __( 'No publications in trash.',  'cropx' ),
			'all_items'          => __( 'All Publications',           'cropx' ),
			'menu_name'          => __( 'Publications',               'cropx' ),
		),
		'public'            => true,
		'show_in_rest'      => true,
		'has_archive'       => true,
		'supports'          => array( 'title', 'excerpt', 'thumbnail', 'editor', 'custom-fields' ),
		'menu_icon'         => 'dashicons-portfolio',
		'rewrite'           => array( 'slug' => 'publications' ),
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
		'supports'          => array( 'title', 'excerpt', 'thumbnail', 'custom-fields' ),
		'menu_icon'         => 'dashicons-media-document',
		'rewrite'           => array( 'slug' => 'resources' ),
		'show_in_nav_menus' => true,
	) );

	// ── Dealer ───────────────────────────────────────────────────────────────
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
		'supports'          => array( 'title', 'thumbnail', 'editor', 'custom-fields' ),
		'menu_icon'         => 'dashicons-store',
		'rewrite'           => array( 'slug' => 'dealers' ),
		'show_in_nav_menus' => true,
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

	// ── Content Type (applied to Publications) ──────────────────────────────
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
}

/**
 * Seed default Content Type terms on theme activation.
 * Runs once — safe to call repeatedly (wp_insert_term ignores duplicates).
 */
add_action( 'after_switch_theme', 'cropx_seed_content_type_terms' );
function cropx_seed_content_type_terms() {
	// Publication content types — case studies and white papers only.
	// Press releases use standard Posts with a "Press Release" category.
	$defaults = array(
		array( 'name' => 'Case Study',  'slug' => 'case-study'  ),
		array( 'name' => 'White Paper', 'slug' => 'white-paper' ),
	);
	foreach ( $defaults as $term ) {
		if ( ! term_exists( $term['slug'], 'cropx_content_type' ) ) {
			wp_insert_term( $term['name'], 'cropx_content_type', array( 'slug' => $term['slug'] ) );
		}
	}

	// Resource types — brochures, datasheets, reports.
	// White papers live in Publications, not Resources.
	$resource_defaults = array(
		array( 'name' => 'Brochure',  'slug' => 'brochure'  ),
		array( 'name' => 'Datasheet', 'slug' => 'datasheet' ),
		array( 'name' => 'Report',    'slug' => 'report'    ),
	);
	foreach ( $resource_defaults as $term ) {
		if ( ! term_exists( $term['slug'], 'cropx_resource_type' ) ) {
			wp_insert_term( $term['name'], 'cropx_resource_type', array( 'slug' => $term['slug'] ) );
		}
	}
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

// ── Team Member: job_title meta field ────────────────────────────────────────
//
// Registers a job_title custom field for team members. Appears as a labeled
// text box in the post editor (visible in both Classic and Gutenberg editors).
// Also exposed via the REST API so it can be read by blocks or the front end.

add_action( 'init', function () {
	register_post_meta( 'cropx_team_member', 'job_title', array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
		'auth_callback'     => function () {
			return current_user_can( 'edit_posts' );
		},
	) );
} );

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'cropx_job_title',
		__( 'Job Title', 'cropx' ),
		function ( $post ) {
			$job_title = get_post_meta( $post->ID, 'job_title', true );
			wp_nonce_field( 'cropx_job_title_save', 'cropx_job_title_nonce' );
			echo '<input type="text" name="job_title" value="' . esc_attr( $job_title ) . '" '
				. 'style="width:100%" placeholder="' . esc_attr__( 'e.g. VP of Marketing', 'cropx' ) . '">';
			echo '<p style="margin:6px 0 0;color:#757575;font-size:12px">'
				. esc_html__( 'Displayed below the name on the Team page.', 'cropx' )
				. '</p>';
		},
		'cropx_team_member',
		'normal',
		'high'
	);
} );

add_action( 'save_post_cropx_team_member', function ( $post_id ) {
	if ( ! isset( $_POST['cropx_job_title_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( $_POST['cropx_job_title_nonce'], 'cropx_job_title_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, 'job_title', sanitize_text_field( $_POST['job_title'] ?? '' ) );
} );

// ── Resource: download_url meta field ────────────────────────────────────────
// URL to the downloadable file (PDF, etc.). Shown as a text field in the editor.

add_action( 'init', function () {
	register_post_meta( 'cropx_resource', 'download_url', array(
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'auth_callback'     => function () {
			return current_user_can( 'edit_posts' );
		},
	) );
} );

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'cropx_resource_download',
		__( 'Download', 'cropx' ),
		function ( $post ) {
			$url = get_post_meta( $post->ID, 'download_url', true );
			wp_nonce_field( 'cropx_resource_download_save', 'cropx_resource_download_nonce' );
			echo '<label style="display:block;margin-bottom:4px;font-weight:600">'
				. esc_html__( 'File URL', 'cropx' ) . '</label>';
			echo '<input type="url" name="download_url" value="' . esc_attr( $url ) . '" '
				. 'style="width:100%" placeholder="https://example.com/file.pdf">';
			echo '<p style="margin:6px 0 0;color:#757575;font-size:12px">'
				. esc_html__( 'Paste the URL to the PDF or file. Can be a media library URL or an external link.', 'cropx' )
				. '</p>';
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
} );

// ── Dealer: contact details + image type ─────────────────────────────────────
// Groups all dealer-specific fields into one meta box for a clean editor UI.
// image_type ('logo' or 'headshot') flags the featured image so templates
// can display it differently when needed.

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
} );

add_action( 'add_meta_boxes', function () {
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
			wp_nonce_field( 'cropx_dealer_details_save', 'cropx_dealer_details_nonce' );

			$field = function( $label, $name, $value, $type = 'text', $placeholder = '' ) {
				echo '<p><label style="display:block;font-weight:600;margin-bottom:3px">' . esc_html( $label ) . '</label>';
				echo '<input type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" '
					. 'style="width:100%" placeholder="' . esc_attr( $placeholder ) . '"></p>';
			};

			$field( __( 'Website', 'cropx' ),  'dealer_website', $website, 'url',   'https://example.com' );
			$field( __( 'Phone',   'cropx' ),  'dealer_phone',   $phone,   'tel',   '+1 (555) 000-0000' );
			$field( __( 'Email',   'cropx' ),  'dealer_email',   $email,   'email', 'dealer@example.com' );
			$field( __( 'Address', 'cropx' ),  'dealer_address', $address, 'text',  '123 Main St, City, State' );
			$field( __( 'Region',  'cropx' ),  'dealer_region',  $region,  'text',  'e.g. North America, Europe' );

			echo '<p><label style="display:block;font-weight:600;margin-bottom:6px">' . esc_html__( 'Featured image is a…', 'cropx' ) . '</label>';
			foreach ( array( 'logo' => __( 'Company logo', 'cropx' ), 'headshot' => __( 'Dealer headshot', 'cropx' ) ) as $val => $lbl ) {
				echo '<label style="display:inline-flex;align-items:center;gap:6px;margin-right:16px">';
				echo '<input type="radio" name="dealer_image_type" value="' . esc_attr( $val ) . '"'
					. checked( $image_type, $val, false ) . '> ' . esc_html( $lbl ) . '</label>';
			}
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
	update_post_meta( $post_id, 'dealer_email',      sanitize_email( $_POST['dealer_email']      ?? '' ) );
	update_post_meta( $post_id, 'dealer_address',    sanitize_text_field( $_POST['dealer_address']    ?? '' ) );
	update_post_meta( $post_id, 'dealer_region',     sanitize_text_field( $_POST['dealer_region']     ?? '' ) );
	update_post_meta( $post_id, 'dealer_image_type', sanitize_text_field( $_POST['dealer_image_type'] ?? 'logo' ) );
} );
