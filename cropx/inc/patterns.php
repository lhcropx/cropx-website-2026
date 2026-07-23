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

	register_block_pattern_category( 'cropx-blocks', [
		'label' => __( 'CropX Universal Blocks', 'cropx' ),
	] );

	$patterns_dir = get_template_directory() . '/patterns/';

	$patterns = [
		'cropx/page-homepage' => [
			'title' => 'Homepage',
			'file'  => 'page-homepage.php',
		],
		'cropx/page-about' => [
			'title' => 'About CropX',
			'file'  => 'page-about.php',
		],
		'cropx/page-products-hub' => [
			'title' => 'Products Hub',
			'file'  => 'page-products-hub.php',
		],
		'cropx/page-hardware-product' => [
			'title' => 'Hardware Product Page',
			'file'  => 'page-hardware-product.php',
		],
		'cropx/page-software-product' => [
			'title' => 'Software Product Page',
			'file'  => 'page-software-product.php',
		],
		'cropx/page-contact' => [
			'title' => 'Contact',
			'file'  => 'page-contact.php',
		],
		'cropx/page-blog-archive' => [
			'title' => 'Blog Archive',
			'file'  => 'page-blog-archive.php',
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
