<?php
/**
 * "Duplicate" row action for Customer Stories (cropx_publication).
 *
 * Adds a Duplicate link next to Edit / Trash in the Customer Stories list
 * table (Admin → Customer Stories). Clicking it clones the post — title,
 * body content, excerpt, every custom field (case study stats, guide
 * links, location, download URL, etc.), the featured image, and every
 * assigned Customer Stories taxonomy term (Content Type, Story Tags) —
 * into a new draft, then opens that draft straight into the editor so it
 * can be tweaked before publishing.
 *
 * Custom fields are copied generically (loop over get_post_meta(), skip a
 * short blacklist of WordPress's own per-post bookkeeping keys) rather than
 * an explicit key-by-key list, so this keeps working automatically if more
 * meta fields get added to the Customer Story meta boxes later — no edit
 * needed here.
 *
 * Only wired up for cropx_publication right now. To extend Duplicate to
 * another post type later, just add its slug to the array returned by
 * cropx_duplicable_post_types() below — everything else (row action,
 * meta copy, taxonomy copy) is already generic.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post types that get the "Duplicate" row action.
 *
 * @return string[]
 */
function cropx_duplicable_post_types(): array {
	return array( 'cropx_publication' );
}

// ── Row action link ──────────────────────────────────────────────────────
add_filter( 'post_row_actions', function ( $actions, $post ) {
	if ( ! in_array( $post->post_type, cropx_duplicable_post_types(), true ) ) {
		return $actions;
	}
	if ( ! current_user_can( 'edit_post', $post->ID ) ) {
		return $actions;
	}

	$url = wp_nonce_url(
		admin_url( 'admin.php?action=cropx_duplicate_post&post=' . $post->ID ),
		'cropx_duplicate_post_' . $post->ID
	);

	$actions['cropx_duplicate'] = sprintf(
		'<a href="%s" aria-label="%s">%s</a>',
		esc_url( $url ),
		/* translators: %s: post title */
		esc_attr( sprintf( __( 'Duplicate &#8220;%s&#8221;', 'cropx' ), get_the_title( $post ) ) ),
		esc_html__( 'Duplicate', 'cropx' )
	);

	return $actions;
}, 10, 2 );

// ── Handler ───────────────────────────────────────────────────────────────
add_action( 'admin_action_cropx_duplicate_post', function () {

	$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;

	if ( ! $post_id ) {
		wp_die( esc_html__( 'Invalid duplicate request — no post specified.', 'cropx' ) );
	}

	// Dies on its own with the standard "Are you sure?" screen if the nonce
	// is missing, expired, or doesn't match this post ID.
	check_admin_referer( 'cropx_duplicate_post_' . $post_id );

	$original = get_post( $post_id );

	if ( ! $original || ! in_array( $original->post_type, cropx_duplicable_post_types(), true ) ) {
		wp_die( esc_html__( "This post can't be duplicated.", 'cropx' ) );
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( esc_html__( "You don't have permission to duplicate this.", 'cropx' ) );
	}

	// ── Clone the post itself ──────────────────────────────────────────
	$new_id = wp_insert_post( array(
		'post_type'      => $original->post_type,
		/* translators: %s: original post title */
		'post_title'     => sprintf( __( '%s (Copy)', 'cropx' ), $original->post_title ),
		'post_content'   => $original->post_content,
		'post_excerpt'   => $original->post_excerpt,
		'post_status'    => 'draft', // Always a draft, regardless of the original's status —
		                              // duplicating a published story should never publish
		                              // a second copy of it automatically.
		'post_author'    => get_current_user_id() ?: $original->post_author,
		'comment_status' => $original->comment_status,
		'ping_status'    => $original->ping_status,
		'menu_order'     => $original->menu_order,
	), true );

	if ( is_wp_error( $new_id ) || ! $new_id ) {
		wp_die( esc_html__( 'Duplicating this post failed — nothing was changed.', 'cropx' ) );
	}

	// ── Copy every taxonomy term assigned to this post type ───────────
	// For Customer Stories that's cropx_content_type (Case Study / Video
	// Testimonial) and cropx_story_tag — read from the taxonomy registry
	// rather than hardcoded so this stays correct if either taxonomy's
	// registration ever changes.
	foreach ( get_object_taxonomies( $original->post_type ) as $taxonomy ) {
		$term_ids = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
		if ( ! is_wp_error( $term_ids ) && $term_ids ) {
			wp_set_object_terms( $new_id, $term_ids, $taxonomy );
		}
	}

	// ── Copy all post meta — custom fields, featured image, everything ─
	// The featured image comes along for free here: it's stored as the
	// _thumbnail_id meta key, which this loop copies just like any other
	// field (both copies point at the same underlying media attachment —
	// duplicating the image file itself isn't necessary or expected).
	// Skipped keys are WordPress's own per-post bookkeeping — copying them
	// onto a brand-new draft would be meaningless or actively wrong (e.g.
	// _edit_lock references a specific user + timestamp on the original).
	$skip_keys = array( '_edit_lock', '_edit_last', '_wp_old_slug', '_wp_old_date' );
	$all_meta  = get_post_meta( $post_id );

	foreach ( $all_meta as $key => $values ) {
		if ( in_array( $key, $skip_keys, true ) ) {
			continue;
		}
		foreach ( $values as $value ) {
			add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
		}
	}

	wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
	exit;
} );
