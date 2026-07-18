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
		'supports'     => array( 'title', 'thumbnail' ),
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
		'supports'          => array( 'title', 'excerpt', 'thumbnail' ),
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
		'supports'          => array( 'title', 'thumbnail' ),
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
		'supports'     => array( 'title', 'thumbnail' ),
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
 * Seed default taxonomy terms on init (not just theme activation).
 * wp_insert_term() silently skips duplicates so this is safe to run on
 * every request — it only does real work the first time each term is missing.
 * Running on init ensures terms exist on staging and production without
 * any manual setup after deploying the theme.
 */
add_action( 'init', 'cropx_seed_content_type_terms', 20 );
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
// download_url           — General/fallback file URL (single-format resources).
// download_url_letter    — US Letter PDF URL.
// download_url_a4        — A4 PDF URL.
// download_attachment_id — Media Library attachment ID of the PDF.
//                          WordPress auto-generates a first-page thumbnail for
//                          any PDF uploaded through the Media Library when
//                          Imagick/Ghostscript is available. Storing this ID
//                          lets render.php retrieve that auto-thumbnail via
//                          wp_get_attachment_image( $id, 'cropx-doc-cover' ).
//
// Render priority for download buttons (handled in the block's render.php):
//   If the block has showLetterDownload / showA4Download enabled AND the
//   resource has the corresponding URL set → show format-specific buttons.
//   Otherwise fall back to download_url for a single generic "Download PDF" button.

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
				array( 'File URL ★',                    'Link to the PDF or file — use "Choose from Media Library" or paste an external URL',                                         'https://cropx.com/files/evato-datasheet.pdf' ),
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
			$url_letter    = get_post_meta( $post->ID, 'download_url_letter',    true );
			$url_a4        = get_post_meta( $post->ID, 'download_url_a4',        true );
			$attachment_id = (int) get_post_meta( $post->ID, 'download_attachment_id', true );
			wp_nonce_field( 'cropx_resource_download_save', 'cropx_resource_download_nonce' );
			?>
			<p style="margin:0 0 14px;color:#555;font-size:12px;line-height:1.5">
				<?php esc_html_e( 'Add format-specific URLs to enable the "US Letter / A4" download buttons on resource cards. If only one format exists, use the General URL — it shows a single "Download PDF" button.', 'cropx' ); ?>
			</p>

			<?php
			$fields = array(
				array(
					'label'       => __( 'US Letter PDF', 'cropx' ),
					'name'        => 'download_url_letter',
					'id'          => 'cropx_url_letter',
					'value'       => $url_letter,
					'placeholder' => 'https://example.com/file-letter.pdf',
					'attach_id'   => '',
				),
				array(
					'label'       => __( 'A4 PDF', 'cropx' ),
					'name'        => 'download_url_a4',
					'id'          => 'cropx_url_a4',
					'value'       => $url_a4,
					'placeholder' => 'https://example.com/file-a4.pdf',
					'attach_id'   => '',
				),
				array(
					'label'       => __( 'General URL (fallback / single-format)', 'cropx' ),
					'name'        => 'download_url',
					'id'          => 'cropx_download_url',
					'value'       => $url_general,
					'placeholder' => 'https://example.com/file.pdf',
					'attach_id'   => 'cropx_download_attachment_id',
					'note'        => __( 'Also used as the cover thumbnail source when chosen from the Media Library.', 'cropx' ),
				),
			);
			foreach ( $fields as $f ) :
			?>
			<div style="margin-bottom:14px">
				<label for="<?php echo esc_attr( $f['id'] ); ?>"
				       style="display:block;font-weight:600;margin-bottom:4px;font-size:12px">
					<?php echo esc_html( $f['label'] ); ?>
				</label>
				<div style="display:flex;gap:8px;align-items:center">
					<input type="url"
					       name="<?php echo esc_attr( $f['name'] ); ?>"
					       id="<?php echo esc_attr( $f['id'] ); ?>"
					       value="<?php echo esc_attr( $f['value'] ); ?>"
					       placeholder="<?php echo esc_attr( $f['placeholder'] ); ?>"
					       style="flex:1">
					<button type="button"
					        class="button cropx-media-pick-btn"
					        data-target="<?php echo esc_attr( $f['id'] ); ?>"
					        data-attach-target="<?php echo esc_attr( $f['attach_id'] ); ?>"
					        style="white-space:nowrap">
						<?php esc_html_e( 'Media Library', 'cropx' ); ?>
					</button>
				</div>
				<?php if ( ! empty( $f['note'] ) ) : ?>
					<p style="margin:4px 0 0;color:#757575;font-size:11px"><?php echo esc_html( $f['note'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>

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
	update_post_meta( $post_id, 'download_url',           esc_url_raw( $_POST['download_url']            ?? '' ) );
	update_post_meta( $post_id, 'download_url_letter',    esc_url_raw( $_POST['download_url_letter']     ?? '' ) );
	update_post_meta( $post_id, 'download_url_a4',        esc_url_raw( $_POST['download_url_a4']         ?? '' ) );
	update_post_meta( $post_id, 'download_attachment_id', absint(      $_POST['download_attachment_id']  ?? 0  ) );
} );

// ── Publication: meta fields + field guide ───────────────────────────────────
// Covers case studies and white papers. Body content lives in the block
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
	// right sidebar column on case study singles. Leave blank on white papers.
	foreach ( array( 'cs_company', 'cs_region', 'cs_scale' ) as $key ) {
		register_post_meta( 'cropx_publication', $key, array_merge( $meta_args, array( 'sanitize_callback' => 'sanitize_text_field' ) ) );
	}
	// Challenge and Solution can be a sentence or two — use textarea sanitizer.
	foreach ( array( 'cs_challenge', 'cs_solution' ) as $key ) {
		register_post_meta( 'cropx_publication', $key, array_merge( $meta_args, array( 'sanitize_callback' => 'sanitize_textarea_field' ) ) );
	}
} );

add_action( 'add_meta_boxes', function () {

	// ── Field Guide ──────────────────────────────────────────────────────────
	add_meta_box(
		'cropx_publication_guide',
		__( '📋 How to complete this publication', 'cropx' ),
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
				array( 'Content Type ★',       'Tag as Case Study or White Paper using the taxonomy panel',           'Case Study' ),
				array( 'Featured Image',        'Hero image at the top of the page. Landscape 16:9 or wider.',        '—' ),
				array( 'Location',              'Short location string shown in the meta row',                         'Arizona, USA' ),
				array( 'Key Findings 1–3',      'Stat + label pairs. Leave blank to hide the Key Findings card.',     '40% / Reduction in water consumption' ),
				array( 'Download URL',          'Link to a downloadable PDF. Leave blank to hide the Download button.', 'https://cropx.com/files/alfalfa-case-study.pdf' ),
				array( 'CS Details (company, region, scale, challenge, solution)', 'Case studies only — fills the "At a Glance" sidebar. Leave blank on white papers.', 'Sonoma Vineyards · California, USA · 1,200 ha' ),
			);
			foreach ( $rows as $row ) {
				echo '<tr style="border-top:1px solid #ddd">'
					. '<td style="padding:5px 12px 5px 0;font-weight:600;vertical-align:top">' . esc_html( $row[0] ) . '</td>'
					. '<td style="padding:5px 12px 5px 0;vertical-align:top">'                  . esc_html( $row[1] ) . '</td>'
					. '<td style="padding:5px 0;color:#757575;vertical-align:top">'             . esc_html( $row[2] ) . '</td>'
					. '</tr>';
			}
			echo '</tbody></table>';
			echo '<p style="margin:8px 0 0;font-size:12px;color:#757575">' . esc_html__( '★ Required  ·  Featured Image, Location, Key Findings, and Download URL are optional but strongly recommended for case studies.', 'cropx' ) . '</p>';
			echo '</div>';
		},
		'cropx_publication',
		'normal',
		'high'
	);

	// ── Publication Details ───────────────────────────────────────────────────
	add_meta_box(
		'cropx_publication_details',
		__( 'Publication Details', 'cropx' ),
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
	// singles. Leave all blank for white papers — they'll be ignored.
	add_meta_box(
		'cropx_cs_details',
		__( 'Case Study Details (sidebar panel)', 'cropx' ),
		function ( $post ) {
			$cs_company   = get_post_meta( $post->ID, 'cs_company',   true );
			$cs_region    = get_post_meta( $post->ID, 'cs_region',    true );
			$cs_challenge = get_post_meta( $post->ID, 'cs_challenge', true );
			$cs_solution  = get_post_meta( $post->ID, 'cs_solution',  true );
			$cs_scale     = get_post_meta( $post->ID, 'cs_scale',     true );
			// Nonce already output by the Publication Details meta box above.
			echo '<p style="margin:0 0 12px;color:#757575;font-size:12px">'
				. esc_html__( 'These fields appear in the "At a Glance" sidebar panel on case study pages. Leave blank for white papers — they will be ignored.', 'cropx' ) . '</p>';

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
} );

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
				array( "Post Title (Person's Full Name) ★", "First and last name of the person being quoted", 'Jane Smith' ),
				array( 'Quote ★',        'The spoken quote — no quotation marks, the design adds them', 'Our crop yields improved significantly...' ),
				array( 'Attribution ★',  'Role and company',                                           'VP of Agriculture, Reinke Manufacturing' ),
				array( 'Featured Image ★', 'Headshot or company logo. Must be a perfect square, at least 250×250 px.', '—' ),
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
				. 'style="width:100%" placeholder="' . esc_attr__( 'e.g. VP of Agriculture, Reinke Manufacturing', 'cropx' ) . '">';
			echo '<p style="margin:6px 0 0;color:#757575;font-size:12px">'
				. esc_html__( "The person's role and company, displayed below the quote.", 'cropx' ) . '</p>';
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
	update_post_meta( $post_id, 'quote_text',  sanitize_textarea_field( $_POST['quote_text']  ?? '' ) );
	update_post_meta( $post_id, 'attribution', sanitize_text_field( $_POST['attribution']     ?? '' ) );
} );
