<?php
/**
 * CropX theme settings page.
 *
 * Adds Admin → Settings → CropX for storing third-party API keys that must
 * not be hard-coded in source files.
 *
 * Current settings:
 *   cropx_mapbox_token  — public Mapbox token (pk.…) used by the Dealer Finder block.
 *                         Must be domain-restricted in the Mapbox dashboard.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Register settings & add menu page ────────────────────────────────────────

add_action( 'admin_init', function () {
	register_setting(
		'cropx_settings_group',
		'cropx_mapbox_token',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		)
	);

	add_settings_section(
		'cropx_map_section',
		__( 'Map Settings', 'cropx' ),
		function () {
			echo '<p>' . esc_html__( 'Settings for the Dealer Finder map block.', 'cropx' ) . '</p>';
		},
		'cropx-settings'
	);

	add_settings_field(
		'cropx_mapbox_token',
		__( 'Mapbox Public Token', 'cropx' ),
		'cropx_render_mapbox_token_field',
		'cropx-settings',
		'cropx_map_section'
	);
} );

add_action( 'admin_menu', function () {
	add_options_page(
		__( 'CropX Settings', 'cropx' ),
		__( 'CropX', 'cropx' ),
		'manage_options',
		'cropx-settings',
		'cropx_settings_page_html'
	);
} );

// ── Field renderer ───────────────────────────────────────────────────────────

function cropx_render_mapbox_token_field() {
	$token = get_option( 'cropx_mapbox_token', '' );
	echo '<input
		type="text"
		id="cropx_mapbox_token"
		name="cropx_mapbox_token"
		value="' . esc_attr( $token ) . '"
		class="regular-text"
		placeholder="pk.eyJ1Ijoi…"
		autocomplete="off"
		spellcheck="false"
	>';
	echo '<p class="description">';
	echo wp_kses(
		sprintf(
			/* translators: 1: opening <a> tag, 2: closing </a> tag */
			__( 'Your Mapbox public token (starts with %1$s). Get one at %2$s. Restrict it to your domain(s) in the Mapbox dashboard for security.', 'cropx' ),
			'<code>pk.</code>',
			'<a href="https://account.mapbox.com/access-tokens/" target="_blank" rel="noopener">account.mapbox.com</a>'
		),
		array(
			'a'    => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
			'code' => array(),
		)
	);
	echo '</p>';

	if ( $token ) {
		echo '<p class="description" style="color:#2a9d5c;margin-top:4px">';
		echo '<span aria-hidden="true">✓</span> ' . esc_html__( 'Token saved. Ensure it is restricted to your domain(s) in the Mapbox dashboard.', 'cropx' );
		echo '</p>';
	}
}

// ── Page HTML ────────────────────────────────────────────────────────────────

function cropx_settings_page_html() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'cropx_settings_group' );
			do_settings_sections( 'cropx-settings' );
			submit_button( __( 'Save Settings', 'cropx' ) );
			?>
		</form>
	</div>
	<?php
}
