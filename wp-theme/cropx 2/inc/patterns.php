<?php
/**
 * Register block pattern category and page patterns.
 * Patterns are stored as PHP files in /patterns/ and registered explicitly here.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {

	// Register the category first.
	register_block_pattern_category( 'cropx-pages', [
		'label' => __( 'CropX Pages', 'cropx' ),
	] );

	$patterns_dir = get_template_directory() . '/patterns/';

	$patterns = [
		'cropx/page-homepage' => [
			'title' => 'Homepage',
			'file'  => 'page-homepage.php',
		],
		'cropx/page-segment-on-farm' => [
			'title' => 'Segment — On-Farm',
			'file'  => 'page-segment-on-farm.php',
		],
		'cropx/page-segment-enterprise' => [
			'title' => 'Segment — Enterprise',
			'file'  => 'page-segment-enterprise.php',
		],
		'cropx/page-segment-service-provider' => [
			'title' => 'Segment — Service Provider',
			'file'  => 'page-segment-service-provider.php',
		],
	];

	foreach ( $patterns as $slug => $pattern ) {
		ob_start();
		include $patterns_dir . $pattern['file'];
		$content = ob_get_clean();

		register_block_pattern( $slug, [
			'title'      => $pattern['title'],
			'categories' => [ 'cropx-pages' ],
			'content'    => $content,
			'inserter'   => true,
		] );
	}

} );
