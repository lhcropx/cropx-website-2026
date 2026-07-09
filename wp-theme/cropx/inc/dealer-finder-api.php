<?php
/**
 * Dealer Finder REST API endpoint + front-end asset enqueueing.
 *
 * Registers GET /wp-json/cropx/v1/dealers — a public read-only endpoint
 * that returns all published dealers which have lat/lng coordinates set.
 *
 * Also enqueues Mapbox GL CSS + JS from the CDN via wp_enqueue_scripts so
 * the CSS lands in <head> (where it must be for the map canvas to size itself)
 * and the JS lands in the footer.  Using has_block() keeps the CDN assets off
 * pages that don't include this block.
 *
 * Response shape (array of objects):
 *   id         int     WordPress post ID
 *   title      string  Dealer name
 *   lat        float   Latitude
 *   lng        float   Longitude
 *   address    string
 *   phone      string
 *   email      string
 *   website    string  (full URL)
 *   region     string
 *   thumbnail  string  Featured image URL (thumbnail size) or empty string
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', function () {
	register_rest_route(
		'cropx/v1',
		'/dealers',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'cropx_rest_get_dealers',
			'permission_callback' => '__return_true', // Public — read-only data, no auth required.
			'args'                => array(
				'all' => array(
					'description'       => __( 'Pass ?all=1 to include dealers without coordinates (for admin use).', 'cropx' ),
					'type'              => 'boolean',
					'default'           => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
			),
		)
	);
} );

/**
 * Return all published dealers, optionally filtered to those with coordinates.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function cropx_rest_get_dealers( WP_REST_Request $request ) {
	$include_all = (bool) $request->get_param( 'all' );

	$posts = get_posts( array(
		'post_type'      => 'cropx_dealer',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );

	$data = array();

	foreach ( $posts as $post ) {
		// Skip inactive dealers unless ?all=1 is passed.
		// Treat empty (never set) as active — this keeps all existing dealers visible.
		$active = get_post_meta( $post->ID, 'dealer_active', true );
		if ( ! $include_all && $active === '0' ) {
			continue;
		}

		$lat = (float) get_post_meta( $post->ID, 'dealer_lat', true );
		$lng = (float) get_post_meta( $post->ID, 'dealer_lng', true );

		// Skip dealers without valid coordinates unless ?all=1 is passed.
		if ( ! $include_all && ( ! $lat || ! $lng ) ) {
			continue;
		}

		$thumbnail_id  = get_post_thumbnail_id( $post->ID );
		$thumbnail_url = $thumbnail_id
			? wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' )
			: '';

		$data[] = array(
			'id'        => $post->ID,
			'title'     => get_the_title( $post ),
			'lat'       => $lat ?: null,
			'lng'       => $lng ?: null,
			'address'   => (string) get_post_meta( $post->ID, 'dealer_address',    true ),
			'phone'     => (string) get_post_meta( $post->ID, 'dealer_phone',      true ),
			'email'     => (string) get_post_meta( $post->ID, 'dealer_email',      true ),
			'website'   => (string) get_post_meta( $post->ID, 'dealer_website',    true ),
			'region'    => (string) get_post_meta( $post->ID, 'dealer_region',     true ),
			'thumbnail' => $thumbnail_url ?: '',
		);
	}

	$response = rest_ensure_response( $data );

	// Cache for 5 minutes on public caches; revalidate after.
	$response->header( 'Cache-Control', 'public, max-age=300, stale-while-revalidate=60' );

	return $response;
}

// ── Enqueue Mapbox GL assets ──────────────────────────────────────────────────
//
// wp_enqueue_scripts fires before wp_head, so styles enqueued here land in
// <head> — essential for Mapbox GL whose CSS sizes the map canvas.
// Calling wp_enqueue_style from render.php (which runs during the_content,
// after wp_head) means the CSS is printed too late and the map stays blank.
//
// has_block() limits the CDN hit to pages that actually contain this block.

add_action( 'wp_enqueue_scripts', function () {
	if ( ! has_block( 'cropx/dealer-finder' ) ) {
		return;
	}

	$v = '3.4.0';

	wp_enqueue_style(
		'mapbox-gl',
		'https://api.mapbox.com/mapbox-gl-js/v' . $v . '/mapbox-gl.css',
		array(),
		$v
	);

	wp_enqueue_script(
		'mapbox-gl',
		'https://api.mapbox.com/mapbox-gl-js/v' . $v . '/mapbox-gl.js',
		array(),
		$v,
		true // in footer
	);
} );
