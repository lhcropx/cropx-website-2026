<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$login_url = $attributes['loginUrl'] ?? '#';

// Pass the full wrapper-attribute string so WP block supports (spacing, colour, etc.)
// land on the <nav> element. get_block_wrapper_attributes() already includes
// class="cnav-block wp-block-cropx-nav …" plus any user-set inline styles.
$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'cnav-block' ) );

cropx_render_nav( array(
	'wrapper_attrs_str' => $wrapper_attrs,
	'login_url'         => $login_url,
) );
