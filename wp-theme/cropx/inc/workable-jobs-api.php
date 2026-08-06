<?php
/**
 * Workable Jobs API — server-side fetch helper.
 *
 * Used by the cropx/job-openings block. Data is sourced through the
 * "Workable API" plugin (jlvanhulst/Workable-for-Wordpress, a mirror of
 * kanopi/wp-workable) rather than a direct API call from theme code — that
 * plugin owns the credentials (wp-admin → Settings → Workable API) and its
 * own Workable_Api_Wrapper class, which already handles the HTTP request,
 * auth header, and its own 2-hour transient cache.
 *
 * This function's return shape is unchanged from the original direct-fetch
 * version, so cropx/job-openings/render.php never needed to change:
 * { configured, error, jobs: [ { title, department, location, url }, … ] }.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fetch published Workable jobs via the Workable API plugin.
 *
 * @return array {
 *     @type bool  $configured Whether the plugin is active AND a subdomain +
 *                             access token are both set in Settings → Workable API.
 *     @type bool  $error      Whether a live fetch was attempted and failed.
 *     @type array $jobs       Normalized job list — each item:
 *                             { title, department, location, url }.
 * }
 */
function cropx_get_workable_jobs() {
	$options   = get_option( 'workable_api_options' );
	$subdomain = trim( (string) ( $options['field_workable_subdomain'] ?? '' ) );
	$api_key   = trim( (string) ( $options['field_api_key'] ?? '' ) );

	if ( ! class_exists( 'Workable_Api_Wrapper' ) || '' === $subdomain || '' === $api_key ) {
		return array(
			'configured' => false,
			'error'      => false,
			'jobs'       => array(),
		);
	}

	// Plugin name/version passed here are just stored as instance properties
	// by the plugin's constructor — not used for anything else — so any
	// string is fine.
	$workable = new Workable_Api_Wrapper( 'cropx-job-openings', '1.0.0' );
	$result   = $workable->get_jobs( array( 'state' => 'published' ) );

	if ( ! is_object( $result ) || ! isset( $result->jobs ) || ! is_array( $result->jobs ) ) {
		// The plugin caches failed lookups (bad credentials, Workable down,
		// a raw cURL error string in place of the decoded response, etc.)
		// for 2 hours in its own transient. Clear that specific key so a
		// credentials fix takes effect on the very next page load instead
		// of silently serving the cached failure for up to 2 hours.
		//
		// IMPORTANT: the plugin builds its transient key as
		// 'workable_all_jobs' . implode( ',', $params ) — implode() only
		// joins array VALUES, not keys, so this only matches our key above
		// because both use the exact same single-item params array. If the
		// $workable->get_jobs() call above ever gains more params (limit,
		// since_id, etc.), this line must be updated to build the identical
		// params array or the cache-clear will silently stop working.
		delete_transient( 'workable_all_jobs' . implode( ',', array( 'state' => 'published' ) ) );

		return array(
			'configured' => true,
			'error'      => true,
			'jobs'       => array(),
		);
	}

	$jobs = array();
	foreach ( $result->jobs as $job ) {
		$job_location = is_object( $job->location ?? null ) ? $job->location : null;

		$location_parts = array();
		if ( $job_location ) {
			if ( ! empty( $job_location->city ) ) {
				$location_parts[] = $job_location->city;
			}
			if ( ! empty( $job_location->country ) ) {
				$location_parts[] = $job_location->country;
			}
			if ( empty( $location_parts ) && ! empty( $job_location->location_str ) ) {
				$location_parts[] = $job_location->location_str;
			}
			if ( empty( $location_parts ) && ! empty( $job_location->telecommuting ) ) {
				$location_parts[] = __( 'Remote', 'cropx' );
			}
		}

		$jobs[] = array(
			'title'      => (string) ( $job->title ?? '' ),
			'department' => (string) ( $job->department ?? '' ),
			'location'   => implode( ', ', $location_parts ),
			'url'        => (string) ( $job->application_url ?? $job->url ?? $job->shortlink ?? '' ),
		);
	}

	return array(
		'configured' => true,
		'error'      => false,
		'jobs'       => $jobs,
	);
}

/**
 * Sample placeholder listings shown when Workable isn't configured yet, or
 * a live fetch fails — lets the block's styling be reviewed before the real
 * API key is in hand. Never shown to regular site visitors once Workable is
 * successfully configured and returning real (even zero) results.
 *
 * @return array Same shape as cropx_get_workable_jobs()['jobs'].
 */
function cropx_get_sample_workable_jobs() {
	return array(
		array(
			'title'      => __( 'Backend Developer (Java / Python)', 'cropx' ),
			'department' => __( 'R&D', 'cropx' ),
			'location'   => __( 'Plovdiv, Bulgaria', 'cropx' ),
			'url'        => '#',
		),
		array(
			'title'      => __( 'Fullstack Software Engineer (Python/GIS)', 'cropx' ),
			'department' => __( 'R&D', 'cropx' ),
			'location'   => __( 'Groningen, Netherlands', 'cropx' ),
			'url'        => '#',
		),
		array(
			'title'      => __( 'Territory Sales Manager', 'cropx' ),
			'department' => __( 'Sales', 'cropx' ),
			'location'   => __( 'United States', 'cropx' ),
			'url'        => '#',
		),
		array(
			'title'      => __( 'Controller', 'cropx' ),
			'department' => __( 'Finance', 'cropx' ),
			'location'   => __( 'Hod Hasharon, Israel', 'cropx' ),
			'url'        => '#',
		),
		array(
			'title'      => __( 'Junior Developer (Customer Solutions)', 'cropx' ),
			'department' => __( 'R&D', 'cropx' ),
			'location'   => __( 'Plovdiv, Bulgaria', 'cropx' ),
			'url'        => '#',
		),
	);
}
