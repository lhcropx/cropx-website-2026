<?php
/**
 * WordPress admin UI customisations.
 *
 * - Reorders the admin sidebar menu
 * - Hides irrelevant menu items (Media, Comments)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Media library picker for Resource download URL ───────────────────────────
// Loads the WP media scripts and wires the "Choose from Media Library" button
// on the Resource edit screen. Clicking the button opens the media browser;
// selecting a file populates the download URL field automatically.
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	global $post;
	if ( ( $hook === 'post-new.php' || $hook === 'post.php' )
		&& isset( $post ) && $post->post_type === 'cropx_resource' ) {

		wp_enqueue_media();

		wp_add_inline_script( 'jquery-core', "
			jQuery( function( $ ) {
				$( '#cropx_download_url_btn' ).on( 'click', function( e ) {
					e.preventDefault();
					var frame = wp.media( {
						title:    'Select or Upload File',
						button:   { text: 'Use this file' },
						multiple: false,
					} );
					frame.on( 'select', function() {
						var attachment = frame.state().get( 'selection' ).first().toJSON();
						$( '#cropx_download_url' ).val( attachment.url ).trigger( 'change' );
					} );
					frame.open();
				} );
			} );
		" );
	}
} );

// ── Title field placeholder text per CPT ─────────────────────────────────────
// Replaces the generic "Add title" placeholder with a descriptive prompt so
// editors immediately know what the Title field expects for each content type.
add_filter( 'enter_title_here', function ( $placeholder, $post ) {
	switch ( $post->post_type ) {
		case 'cropx_publication':
			return __( 'Customer story title (e.g. How Reinke Reduced Water Usage by 30%)', 'cropx' );
		case 'cropx_resource':
			return __( 'Document title (e.g. CropX Evato Sensor Datasheet)', 'cropx' );
		case 'cropx_team_member':
			return __( "Employee's full name (e.g. Jane Smith)", 'cropx' );
		case 'cropx_dealer':
			return __( 'Dealer or company name (e.g. Agri Partners Inc.)', 'cropx' );
		default:
			return $placeholder;
	}
}, 10, 2 );

// ── Reorder the admin sidebar ─────────────────────────────────────────────────
// Items not listed here will appear after the listed items in their default order.
// Plugin slugs: All-in-One WP Migration = 'ai1wm_export', Meow Apps = 'meow-apps'.
// If either plugin item doesn't move to the right position, inspect the exact slug
// by hovering the menu item in WP Admin and checking the URL in the status bar.
add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', function ( $menu_order ) {
	return array(
		'index.php',                              // Dashboard
		'edit.php?post_type=page',                // Pages
		'edit.php?post_type=cropx_publication',   // Customer Stories
		'edit.php',                               // Posts
		'edit.php?post_type=cropx_resource',      // Resources
		'edit.php?post_type=cropx_testimonial',   // Quotes
		'edit.php?post_type=cropx_dealer',        // Dealers
		'edit.php?post_type=cropx_team_member',   // Team
		'upload.php',                             // Media
		'edit-comments.php',                      // Comments
		'separator1',
		'themes.php',                             // Appearance
		'plugins.php',                            // Plugins
		'users.php',                              // Users
		'tools.php',                              // Tools
		'ai1wm_export',                           // All-in-One WP Migration
		'separator2',
		'options-general.php',                    // Settings
		'meow-apps',                              // Meow Apps
	);
} );

// ── Block allow-list — hide unused core blocks from the inserter ──────────────
//
// WordPress ships ~100+ blocks, most of which are irrelevant to a custom-theme
// marketing site (FSE/site-editor blocks, widget-era relics, blocks replaced by
// our own CropX blocks). This filter returns an explicit whitelist so editors
// only see what they should actually use.
//
// All cropx/* blocks are included automatically by querying the block registry,
// so new CropX blocks are allowed the moment they're registered — no manual
// updates needed here.
//
add_filter( 'allowed_block_types_all', function ( $allowed_blocks, $editor_context ) {

	// ── Core blocks we actively use or style ──────────────────────────────────
	$allowed_core = array(

		// Text content —————————————————————————————————————————————————————————
		'core/paragraph',
		'core/heading',
		'core/list',
		'core/list-item',
		'core/quote',
		'core/pullquote',
		'core/table',
		'core/code',
		'core/preformatted',
		'core/details',       // Native <details>/<summary> accordion — useful in blog posts
		'core/footnotes',     // Inline footnotes — useful for sourced technical content

		// Media ————————————————————————————————————————————————————————————————
		'core/image',
		'core/gallery',
		'core/video',
		'core/audio',
		'core/embed',
		'core/file',          // PDF / document download link

		// Layout ———————————————————————————————————————————————————————————————
		'core/columns',
		'core/column',
		'core/group',
		'core/buttons',
		'core/button',
		'core/separator',
		'core/spacer',

		// Utility ——————————————————————————————————————————————————————————————
		'core/html',          // Custom HTML — third-party embeds without a core/embed provider
		'core/shortcode',     // Retained for legacy content migration
		'core/social-links',  // Social icon row (usable inside page body, not just footer)
		'core/social-link',
		'core/block',         // Synced Patterns — MUST stay; our global patterns rely on this
	);

	// ── Auto-include every registered cropx/* block ───────────────────────────
	// Querying the live registry means newly added CropX blocks are always
	// allowed without touching this file.
	$registry     = WP_Block_Type_Registry::get_instance();
	$all_blocks   = array_keys( $registry->get_all_registered() );
	$cropx_blocks = array_values( array_filter(
		$all_blocks,
		fn( $name ) => str_starts_with( $name, 'cropx/' )
	) );

	return array_merge( $allowed_core, $cropx_blocks );

}, 10, 2 );

// ── Page Workflow — status + assignee metadata ────────────────────────────────
//
// Two post-meta fields stored on Pages:
//   _cropx_page_status   — editorial phase (design → copy → polish → review → complete)
//   _cropx_page_assignee — person responsible (larissa | lauren | julia)
//
// Both are exposed to the REST API (required for the block editor to read/write
// them) and rendered in a custom "Page Workflow" panel in the Document sidebar
// via assets/js/page-workflow.js (no build step — pure wp.* globals).
// A pair of admin columns on the Pages list makes the values visible at a glance.

add_action( 'init', function () {
	$shared = array(
		'show_in_rest'  => true,
		'single'        => true,
		'type'          => 'string',
		'default'       => '',
		'auth_callback' => fn() => current_user_can( 'edit_posts' ),
	);
	register_post_meta( 'page', '_cropx_page_status',   $shared );
	register_post_meta( 'page', '_cropx_page_assignee', $shared );
} );

// Enqueue the sidebar plugin — pages only, block editor only.
add_action( 'enqueue_block_editor_assets', function () {
	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'page' ) {
		return;
	}
	wp_enqueue_script(
		'cropx-page-workflow',
		CROPX_THEME_URI . 'assets/js/page-workflow.js',
		array( 'wp-plugins', 'wp-edit-post', 'wp-editor', 'wp-element', 'wp-components', 'wp-data', 'wp-dom-ready' ),
		filemtime( CROPX_THEME_DIR . 'assets/js/page-workflow.js' ),
		true
	);
} );

// ── Pages list — Status and Assigned To columns ───────────────────────────────

add_filter( 'manage_pages_columns', function ( $columns ) {
	$columns['cropx_status']   = 'Status';
	$columns['cropx_assignee'] = 'Assigned To';
	return $columns;
} );

add_action( 'manage_pages_custom_column', function ( $column_name, $post_id ) {

	if ( $column_name === 'cropx_status' ) {
		$value  = get_post_meta( $post_id, '_cropx_page_status', true );
		$labels = array(
			'design'        => 'I — Design Phase',
			'copy'          => 'II — Copy Phase',
			'visual-polish' => 'III — Visual Polish',
			'review'        => 'IV — Review Phase',
			'complete'      => 'V — Complete',
		);
		$bg     = array(
			'design'        => '#e8f0fe',
			'copy'          => '#fef9c3',
			'visual-polish' => '#f3e8fd',
			'review'        => '#fff3e0',
			'complete'      => '#dcfce7',
		);
		$fg     = array(
			'design'        => '#1a56db',
			'copy'          => '#92400e',
			'visual-polish' => '#7e22ce',
			'review'        => '#c2410c',
			'complete'      => '#166534',
		);
		if ( $value && isset( $labels[ $value ] ) ) {
			printf(
				'<span style="display:inline-block;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:600;background:%s;color:%s;">%s</span>',
				esc_attr( $bg[ $value ] ),
				esc_attr( $fg[ $value ] ),
				esc_html( $labels[ $value ] )
			);
		} else {
			echo '<span style="color:#aaa;font-size:12px;">—</span>';
		}
	}

	if ( $column_name === 'cropx_assignee' ) {
		$value = get_post_meta( $post_id, '_cropx_page_assignee', true );
		echo $value ? esc_html( ucfirst( $value ) ) : '<span style="color:#aaa;font-size:12px;">—</span>';
	}

}, 10, 2 );

// ── Pages list — sortable columns ─────────────────────────────────────────────
// Registers Status and Assigned To as clickable column headers. WordPress uses
// the filter key (e.g. 'cropx_status') as the ?orderby= GET parameter value
// when the user clicks the header; pre_get_posts below intercepts it.

add_filter( 'manage_edit-page_sortable_columns', function ( $columns ) {
	$columns['cropx_status']   = 'cropx_status';
	$columns['cropx_assignee'] = 'cropx_assignee';
	return $columns;
} );

// ── Pages list — filter dropdowns ─────────────────────────────────────────────
// Adds "Status" and "Assigned To" <select> dropdowns to the filter bar above
// the Pages list. Submitting the bar's built-in Filter button passes the chosen
// values as GET params; pre_get_posts below applies them to the query.

add_action( 'restrict_manage_posts', function ( $post_type ) {
	if ( $post_type !== 'page' ) {
		return;
	}

	$status_options = array(
		'design'        => 'I — Design Phase',
		'copy'          => 'II — Copy Phase',
		'visual-polish' => 'III — Visual Polish Phase',
		'review'        => 'IV — Review Phase',
		'complete'      => 'V — Complete',
	);

	$assignee_options = array(
		'larissa' => 'Larissa',
		'lauren'  => 'Lauren',
		'julia'   => 'Julia',
	);

	$cur_status   = isset( $_GET['cropx_status_filter'] )   ? sanitize_text_field( $_GET['cropx_status_filter'] )   : '';
	$cur_assignee = isset( $_GET['cropx_assignee_filter'] ) ? sanitize_text_field( $_GET['cropx_assignee_filter'] ) : '';

	// Status dropdown
	echo '<select name="cropx_status_filter">';
	echo '<option value="">All Statuses</option>';
	foreach ( $status_options as $value => $label ) {
		printf(
			'<option value="%s"%s>%s</option>',
			esc_attr( $value ),
			selected( $cur_status, $value, false ),
			esc_html( $label )
		);
	}
	echo '</select>';

	// Assignee dropdown
	echo '<select name="cropx_assignee_filter">';
	echo '<option value="">All Assignees</option>';
	foreach ( $assignee_options as $value => $label ) {
		printf(
			'<option value="%s"%s>%s</option>',
			esc_attr( $value ),
			selected( $cur_assignee, $value, false ),
			esc_html( $label )
		);
	}
	echo '</select>';
} );

// ── Pages list — apply filters and column-header sorting ──────────────────────
// Handles both the filter dropdowns (meta_query) and the sortable column clicks
// (meta_key + orderby). When sorting by Status, a separate posts_orderby filter
// below replaces the default SQL with FIELD() so the sort follows phase order
// (I → V) rather than alphabetical order.

add_action( 'pre_get_posts', function ( $query ) {
	global $pagenow;

	if ( ! is_admin() || $pagenow !== 'edit.php' || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->get( 'post_type' ) !== 'page' ) {
		return;
	}

	// ── Filtering ──────────────────────────────────────────────────────────────
	$meta_query = array();

	if ( ! empty( $_GET['cropx_status_filter'] ) ) {
		$meta_query[] = array(
			'key'     => '_cropx_page_status',
			'value'   => sanitize_text_field( $_GET['cropx_status_filter'] ),
			'compare' => '=',
		);
	}

	if ( ! empty( $_GET['cropx_assignee_filter'] ) ) {
		$meta_query[] = array(
			'key'     => '_cropx_page_assignee',
			'value'   => sanitize_text_field( $_GET['cropx_assignee_filter'] ),
			'compare' => '=',
		);
	}

	if ( ! empty( $meta_query ) ) {
		$query->set( 'meta_query', $meta_query );
	}

	// ── Sorting ────────────────────────────────────────────────────────────────
	// Setting meta_key tells WordPress to JOIN postmeta, which is required for
	// any meta-based ORDER BY (including the FIELD() override below for status).
	$orderby = $query->get( 'orderby' );

	if ( $orderby === 'cropx_status' ) {
		$query->set( 'meta_key', '_cropx_page_status' );
		$query->set( 'orderby', 'meta_value' ); // overridden by posts_orderby below
	}

	if ( $orderby === 'cropx_assignee' ) {
		$query->set( 'meta_key', '_cropx_page_assignee' );
		$query->set( 'orderby', 'meta_value' ); // alphabetical is fine for names
	}
} );

// ── Status sort — phase order (I→V), not alphabetical ─────────────────────────
// meta_value sort would give: complete, copy, design, review, visual-polish —
// completely wrong. FIELD() maps each slug to its position in the defined array
// so clicking the Status header always sorts by editorial phase.
// Pages with no status value get FIELD() = 0, so they sort before "design" (ASC)
// or after "complete" (DESC) — i.e., unset pages always appear at the fringe.

add_filter( 'posts_orderby', function ( $sql, $query ) {
	global $wpdb, $pagenow;

	if ( ! is_admin() || $pagenow !== 'edit.php' ) {
		return $sql;
	}

	// Check the raw GET param because pre_get_posts already rewrote $query->orderby.
	if ( empty( $_GET['orderby'] ) || $_GET['orderby'] !== 'cropx_status' ) {
		return $sql;
	}

	$order = ( ! empty( $_GET['order'] ) && strtolower( $_GET['order'] ) === 'desc' ) ? 'DESC' : 'ASC';

	return "FIELD( {$wpdb->postmeta}.meta_value, 'design', 'copy', 'visual-polish', 'review', 'complete' ) {$order}";
}, 10, 2 );

// ── Hide irrelevant core/embed provider variations ────────────────────────────
// The core/embed block itself stays allowed (YouTube, Vimeo, LinkedIn, etc. are
// useful), but these ~20 provider variations have zero relevance to a B2B agtech
// site. Block variations must be removed via JS — PHP has no hook for it.
// wp.domReady() ensures the variations are already registered before we try to
// unregister them. Priority 20 fires after the main enqueue_block_editor_assets
// at priority 10 so the 'wp-blocks' handle definitely exists.
add_action( 'enqueue_block_editor_assets', function () {

	$hide_embed_providers = array(
		'amazon-kindle',
		'animoto',
		'bluesky',
		'cloudup',
		'crowdsignal',
		'dailymotion',
		'imgur',
		'kickstarter',
		'mixcloud',
		'pinterest',
		'pocket-casts',
		'reddit',
		'reverbnation',
		'smugmug',
		'soundcloud',
		'spotify',
		'tumblr',
		'videopress',
		'wolfram-alpha',
		'wordpress-tv',
	);

	$calls = implode( '', array_map(
		fn( $slug ) => "wp.blocks.unregisterBlockVariation('core/embed','" . esc_js( $slug ) . "');",
		$hide_embed_providers
	) );

	wp_add_inline_script( 'wp-blocks', 'wp.domReady(function(){' . $calls . '});' );

}, 20 );
