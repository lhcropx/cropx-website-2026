<?php
/**
 * Popular Posts — meta field + editor meta box
 *
 * Registers _cropx_is_popular post meta and adds a "Blog Archive"
 * meta box to the post editor so editors can flag up to 3 posts as
 * "popular". Those posts appear in the carousel at the top of home.php.
 *
 * Usage:
 *   1. Open any post in the WP editor.
 *   2. Look for the "Blog Archive" meta box in the right sidebar.
 *   3. Tick "Show in Popular Posts carousel" and save/publish.
 *   4. Flag up to 3 posts for the best carousel experience.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Meta field registration ─────────────────────────────────────────────────────

add_action( 'init', function () {
	register_post_meta( 'post', '_cropx_is_popular', array(
		'show_in_rest'  => true,  // enables Gutenberg sidebar panel access if desired later
		'single'        => true,
		'type'          => 'string',
		'default'       => '0',
		'auth_callback' => function () {
			return current_user_can( 'edit_posts' );
		},
	) );
} );


// ── Meta box ────────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'cropx_popular_post',
		__( 'Blog Archive', 'cropx' ),
		'cropx_popular_post_meta_box_cb',
		'post',
		'side',
		'high'
	);
} );

/**
 * Render the meta box markup.
 *
 * @param WP_Post $post The current post object.
 */
function cropx_popular_post_meta_box_cb( $post ) {
	wp_nonce_field( 'cropx_save_popular_post', 'cropx_popular_post_nonce' );
	$is_popular = ( '1' === get_post_meta( $post->ID, '_cropx_is_popular', true ) );
	?>
	<label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; margin: 4px 0 0;">
		<input type="checkbox" name="cropx_is_popular" value="1"
		       style="margin-top: 3px; flex-shrink: 0;"
		       <?php checked( $is_popular ); ?>>
		<span><?php esc_html_e( 'Show in Popular Posts carousel', 'cropx' ); ?></span>
	</label>
	<p class="description" style="margin-top: 8px; line-height: 1.5;">
		<?php esc_html_e( 'Appears in the carousel above the post grid on the blog archive page. Up to 3 posts at a time — flag your best-performing content.', 'cropx' ); ?>
	</p>
	<?php
}


// ── Save handler ────────────────────────────────────────────────────────────────

add_action( 'save_post_post', function ( $post_id ) {
	// Bail early on autosave, ajax, or missing nonce.
	if ( ! isset( $_POST['cropx_popular_post_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce(
		sanitize_text_field( wp_unslash( $_POST['cropx_popular_post_nonce'] ) ),
		'cropx_save_popular_post'
	) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	update_post_meta(
		$post_id,
		'_cropx_is_popular',
		isset( $_POST['cropx_is_popular'] ) ? '1' : '0'
	);
} );
