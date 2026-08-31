<?php
/**
 * Theme helper functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All valid icon slugs, used by every block's IconPicker to validate the
 * saved `icon` attribute before building the front-end <img src="...">.
 *
 * This reads assets/icons/*.svg directly off disk instead of a hardcoded
 * list, so it can never drift out of sync with the actual icon library —
 * add or remove an SVG in assets/icons/ and every block picks it up on the
 * next page load automatically, no render.php edits required.
 *
 * (Several blocks previously hardcoded their own copy of this list at the
 * point the shared IconPicker component was wired in. A couple of those
 * copies were never updated to the full icon set, so choosing most icons
 * in the editor would silently fall back to the block's default icon on
 * the published page — the attribute saved fine, it just failed this
 * allowlist check. Centralizing it here removes that whole class of bug.)
 *
 * @return string[] Icon slugs (filenames without the .svg extension), sorted.
 */
function cropx_allowed_icon_slugs(): array {
	static $slugs = null;

	if ( null !== $slugs ) {
		return $slugs;
	}

	$slugs = array();
	$files = glob( CROPX_THEME_DIR . 'assets/icons/*.svg' );

	if ( $files ) {
		foreach ( $files as $file ) {
			$slugs[] = basename( $file, '.svg' );
		}
		sort( $slugs );
	}

	return $slugs;
}

/**
 * Maps a retired icon slug to the icon that replaced it.
 *
 * When an icon SVG is deleted from assets/icons/ because it turned out to be
 * a duplicate of another icon (e.g. "speed-2" duplicating "speed"), any
 * already-published block still has the OLD slug saved in its attributes.
 * Without this, cropx_allowed_icon_slugs() would reject that now-missing
 * slug and every block's render.php would silently fall back to its own
 * hardcoded default icon — a visible, confusing regression on pages nobody
 * touched. Resolving the alias here means the front end keeps showing the
 * (correct, still-present) icon with zero editor action required.
 *
 * Every block's render.php should call this on the raw `icon` attribute
 * value BEFORE checking it against cropx_allowed_icon_slugs().
 * src/shared/IconPicker.js keeps a matching ICON_SLUG_ALIASES map so the
 * editor canvas preview and "selected" swatch stay in sync with this.
 *
 * @param string $slug Icon slug as stored in the block attribute.
 * @return string The resolved slug — unchanged if it isn't a retired alias.
 */
function cropx_resolve_icon_slug( string $slug ): string {
	$aliases = array(
		'fields-2' => 'fields',
	);

	return $aliases[ $slug ] ?? $slug;
}

/**
 * Convert a same-domain absolute URL to a root-relative path.
 *
 * Use this for every link href output in render.php files. It strips the
 * site's own domain so links work correctly across local → staging → production
 * without needing database search-replace on deployment.
 *
 * Rules:
 *   - Empty string or '#'  → returned as-is
 *   - Same-domain URL      → domain stripped, relative path returned
 *   - External URL         → returned as-is
 *
 * Examples (on cropx.com):
 *   cropx_url('https://cropx.com/enterprise/')   → '/enterprise/'
 *   cropx_url('http://cropx-2026-2.local/about') → '/about'
 *   cropx_url('/already-relative')               → '/already-relative'
 *   cropx_url('https://example.com/page')        → 'https://example.com/page'
 *   cropx_url('#')                               → '#'
 *
 * DO NOT use for image src attributes — only for link hrefs and navigation URLs.
 *
 * @param  string $url The URL to normalise.
 * @return string      Root-relative path for same-domain URLs; original for all others.
 */
/**
 * Estimate reading time for a post.
 *
 * Strips HTML, counts words, and divides by a 200 wpm reading speed.
 * Returns a localised string like "5 min read".
 *
 * @param  int|null $post_id Post ID, or null to use the current global post.
 * @return string            Localised reading-time label.
 */
/**
 * Get the post ID of a synced WordPress pattern (wp_block CPT) by slug.
 *
 * Synced patterns are stored as wp_block posts. Their numeric IDs differ
 * between environments (local / staging / production), so we look them up
 * by slug which is consistent. Used by page pattern PHP files so they stay
 * portable across environments — no hardcoded IDs needed.
 *
 * Usage in a pattern file:
 *   $ref = cropx_get_synced_block_ref( 'segment-navigation' );
 *   if ( $ref ) echo '<!-- wp:block {"ref":' . $ref . '} /-->';
 *
 * @param  string $slug The wp_block post slug (e.g. 'segment-navigation').
 * @return int          The post ID, or 0 if the pattern doesn't exist yet.
 */
function cropx_get_synced_block_ref( string $slug ): int {
	$post = get_page_by_path( $slug, OBJECT, 'wp_block' );
	return $post ? (int) $post->ID : 0;
}

/**
 * [cropx_year] shortcode — outputs the current 4-digit year.
 *
 * Added for the footer's legal/copyright line (Aug 2026 footer rebuild,
 * task #305): the old monolithic cropx/footer block computed the copyright
 * year in PHP ($copyright_year ?: date('Y')). The new footer is a Synced
 * Pattern made of plain core/paragraph blocks, so there's no PHP to do that
 * automatically — this shortcode is the one small dynamic piece kept, so
 * Lauren never has to manually bump the year on the footer copyright line.
 *
 * Usage inside any block's text content: © [cropx_year] CropX Technologies Ltd.
 *
 * Note: shortcodes only expand automatically inside the_content() (WordPress
 * hooks do_shortcode() onto that filter, not onto block rendering generally).
 * Anywhere block content is rendered directly via do_blocks() outside the
 * main loop — the footer pattern included — wrap the output in do_shortcode()
 * as well, e.g.: echo do_shortcode( do_blocks( '<!-- wp:block ... /-->' ) );
 */
add_shortcode( 'cropx_year', function () {
	return esc_html( date( 'Y' ) );
} );

/**
 * Get a card-safe excerpt for a post.
 *
 * Behaves exactly like get_the_excerpt() when the post has a manual excerpt
 * (author-written excerpts are always plain text, so there's nothing to
 * strip there). When there's no manual excerpt, this auto-generates one
 * from the post content with any heading blocks removed FIRST — otherwise
 * WordPress's own excerpt generator just strips HTML tags and runs the
 * heading text straight into the paragraph that follows it, so heading
 * copy ends up bleeding into the excerpt shown on cards (e.g. "Our Mission
 * CropX helps growers..." instead of just "CropX helps growers...").
 *
 * Use this everywhere a post is rendered as a card — the Cards block,
 * Resource Grid, related-posts partials, and every archive/grid template —
 * instead of calling get_the_excerpt() or wp_trim_words( get_the_content() )
 * directly.
 *
 * @param  WP_Post|int|null $post       Post object, ID, or null for the current global post.
 * @param  int              $word_count Words to trim to when auto-generating. Ignored when a
 *                                       manual excerpt exists (get_the_excerpt() never re-trims
 *                                       a manual excerpt either, so this matches that behaviour).
 * @return string           Plain-text excerpt. Same escaping contract as get_the_excerpt() —
 *                           callers still run it through esc_html() themselves.
 */
function cropx_get_card_excerpt( $post = null, int $word_count = 55 ): string {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}

	if ( has_excerpt( $post ) ) {
		return get_the_excerpt( $post );
	}

	// Strip whole heading blocks — tag *and* inner text — before stripping
	// the remaining tags. This is the step get_the_excerpt() and
	// wp_trim_words() skip, which is why heading text normally leaks into
	// auto-generated excerpts.
	$content = strip_shortcodes( $post->post_content );
	$content = preg_replace( '#<h[1-6][^>]*>.*?</h[1-6]>#is', ' ', $content );

	return wp_trim_words( $content, $word_count );
}

function cropx_reading_time( ?int $post_id = null ): string {
	$content    = get_post_field( 'post_content', $post_id );
	$word_count = str_word_count( wp_strip_all_tags( $content ) );
	$minutes    = max( 1, (int) round( $word_count / 200 ) );
	/* translators: %d: number of minutes */
	return sprintf( _n( '%d min read', '%d min read', $minutes, 'cropx' ), $minutes );
}

/**
 * Resolve the fill colour for the hero curved-swoop bottom-right corner.
 *
 * The swoop SVG path must match the background of the section directly below
 * the hero. When the page author places a CropX block next, we read that
 * block's bgColor attribute and return the matching hex value. When native
 * WordPress content follows (paragraphs, headings, etc.) there is no bgColor
 * to read, so we fall back to white — which matches WordPress's default body
 * background for pages that use the base theme styles.
 *
 * A manual override (the swoopFill block attribute) takes priority over
 * auto-detection; pass an empty string to trigger auto-detection.
 *
 * @param  string $block_name  Full block name of the calling hero, e.g. 'cropx/hero-curved'.
 * @param  string $manual      Manual override value ('white', 'taupe', 'deep-blue', …) or ''
 *                             for auto-detect.
 * @return string              Hex colour string, e.g. '#ffffff'.
 */
function cropx_get_swoop_fill( string $block_name, string $manual = '' ): string {
	// Map of bgColor attribute token values → hex colours.
	$color_map = array(
		'white'          => '#ffffff',
		'taupe'          => '#f3f1f1',
		'deep-blue'      => '#243565',
		'dark'           => '#243565',
		'blue'           => '#0CA8C0',
		'cropx-blue'     => '#0CA8C0',
		'gold'           => '#E9C242',
		'terra'          => '#E48C4D',
		'new-leaf'       => '#96C05A',
	);

	// Manual override takes priority.
	if ( $manual !== '' && isset( $color_map[ $manual ] ) ) {
		return $color_map[ $manual ];
	}

	// Auto-detect: parse the current post's block list and find the first
	// non-empty block that immediately follows our hero block.
	$post = get_post();
	if ( ! $post || empty( $post->post_content ) ) {
		return '#fbfaf9';
	}

	$blocks = parse_blocks( $post->post_content );
	$found  = false;

	foreach ( $blocks as $block ) {
		if ( $found ) {
			// Skip null/whitespace-only blocks that WordPress inserts between blocks.
			if ( null === $block['blockName'] ) {
				continue;
			}
			$bg = $block['attrs']['bgColor'] ?? '';
			return isset( $color_map[ $bg ] ) ? $color_map[ $bg ] : '#fbfaf9';
		}
		if ( $block['blockName'] === $block_name ) {
			$found = true;
		}
	}

	// Hero is last block, or only native content follows → match the site's
	// body background (#fbfaf9, taupe-50) set in theme.json. This is the correct
	// default for privacy/terms/legal pages with native WordPress content.
	return '#fbfaf9';
}

/**
 * Build the <img>/<picture> markup for one image (device or phone) within one
 * pair of the "Animated Device Hero — Curved Edge" block's `pairs` list, plus
 * the inline style attribute carrying its position/scale custom properties
 * and the `animation` shorthand that drives its slot in the pair slideshow.
 *
 * This lives here (rather than as a function declared inside render.php)
 * because block render.php templates are `include`d fresh on every render —
 * if the block appears more than once on a page, a function declared inside
 * render.php would be declared twice and fatal with "Cannot redeclare
 * function". inc/helpers.php is required exactly once per request, so this
 * is safe to call from any number of block instances.
 *
 * NOTE: deliberately no hardcoded 'style' => 'height: 100%;' passed to
 * wp_get_attachment_image() — see hero-curved-standard's render.php for why
 * that silently breaks the Size (%) control (inline styles beat CSS).
 *
 * @param  string $kind          'device' or 'phone'.
 * @param  int    $id            Attachment ID, or 0 if using $url or the
 *                                 fallback illustration.
 * @param  string $url           External image URL, ignored if $id is set.
 * @param  int    $scale         Size (%), same meaning as hero-curved-standard's
 *                                 deviceScale/phoneScale.
 * @param  int    $offset_x      Horizontal offset in px.
 * @param  int    $offset_y      Vertical offset in px.
 * @param  string $animation_css Full `animation: ...` shorthand value already
 *                                 assembled by render.php (keyframe name,
 *                                 duration, delay — identical for the device
 *                                 and phone in a pair so they move together).
 * @param  string $theme_uri     CROPX_THEME_URI, for the fallback illustration.
 * @return string Markup for one <img> or <picture>, or '' if this is a phone
 *                 slot with nothing selected (phones are optional per pair;
 *                 devices always fall back to the default illustration).
 */
function cropx_hca_render_image( string $kind, int $id, string $url, int $scale, int $offset_x, int $offset_y, string $animation_css, string $theme_uri ): string {
	$is_phone = 'phone' === $kind;

	// A blank phone slot means "no app image for this pair" — skip it
	// entirely rather than falling back to a default, since that's the
	// whole point of making it optional per pair.
	if ( $is_phone && ! $id && ! $url ) {
		return '';
	}

	$item_css_vars = sprintf(
		'--i-scale:%d;--i-x:%dpx;--i-y:%dpx;animation:%s',
		$scale, $offset_x, $offset_y, $animation_css
	);

	$css_class = $is_phone ? 'shca-item--phone' : 'shca-item--device';
	$alt       = $is_phone
		? esc_attr__( 'CropX app on iPhone', 'cropx' )
		: esc_attr__( 'CropX soil sensor', 'cropx' );

	if ( $id ) {
		return wp_get_attachment_image( $id, 'full', false, array(
			'class' => $css_class,
			'alt'   => $alt,
			'style' => esc_attr( $item_css_vars ),
		) );
	}

	if ( $url ) {
		return '<img class="' . esc_attr( $css_class ) . '" style="' . esc_attr( $item_css_vars ) . '" src="' . esc_url( $url ) . '" alt="' . $alt . '" loading="eager">';
	}

	// Device with nothing selected — fall back to the same default
	// illustration hero-curved-standard uses, so a freshly inserted block
	// still looks intentional out of the box. (Phones never reach here —
	// handled by the early return above.)
	$default_base = 'vertex-partial-a';
	$markup  = '<picture>';
	$markup .= '<source type="image/webp" srcset="' . esc_url( $theme_uri . 'assets/images/illustrations/' . $default_base . '.webp' ) . '">';
	$markup .= '<img class="' . esc_attr( $css_class ) . '" style="' . esc_attr( $item_css_vars ) . '" src="' . esc_url( $theme_uri . 'assets/images/illustrations/' . $default_base . '.png' ) . '" alt="' . $alt . '" loading="eager">';
	$markup .= '</picture>';
	return $markup;
}

/**
 * Country list for the "Country" dropdown on the Zoho-backed form blocks
 * (cropx/zoho-form, cropx/zoho-contact-form).
 *
 * Copied verbatim from the Zoho Forms "Export as HTML & CSS" download for
 * the Country field (Dropdown1) — including a couple of oddities that are
 * Zoho's own, not typos on our end: "Saint BarthÃ©lemy" is mojibake already
 * present in Zoho's exported option value, and a few entries are disputed
 * territories (Kosovo, Palestine, Western Sahara, etc.) that Zoho includes
 * in its own default list. These are left exactly as exported because the
 * <option> value is literally what gets submitted to Zoho — "fixing" the
 * text would send a value that no longer matches whatever Zoho has on its
 * side for that field.
 *
 * If Zoho's own list ever changes, re-export the form and diff the
 * Dropdown1 <option> values against this array rather than hand-editing.
 *
 * @return string[] Country names, in the exact order Zoho exports them.
 */
function cropx_zoho_country_list(): array {
	return array(
		'Åland Islands', 'Afghanistan', 'Akrotiri', 'Albania', 'Algeria', 'American Samoa',
		'Andorra', 'Angola', 'Anguilla', 'Antarctica', 'Antigua and Barbuda', 'Argentina',
		'Armenia', 'Aruba', 'Ashmore and Cartier Islands', 'Australia', 'Austria', 'Azerbaijan',
		'Bahrain', 'Bangladesh', 'Barbados', 'Bassas Da India', 'Belarus', 'Belgium', 'Belize',
		'Benin', 'Bermuda', 'Bhutan', 'Bolivia', 'Bosnia and Herzegovina', 'Botswana',
		'Bouvet Island', 'Brazil', 'British Indian Ocean Territory', 'British Virgin Islands',
		'Brunei', 'Bulgaria', 'Burkina Faso', 'Burma', 'Burundi', 'Cambodia', 'Cameroon',
		'Canada', 'Cape Verde', 'Caribbean Netherlands', 'Cayman Islands',
		'Central African Republic', 'Chad', 'Chile', 'China', 'Christmas Island',
		'Clipperton Island', 'Cocos (Keeling) Islands', 'Colombia', 'Comoros', 'Cook Islands',
		'Coral Sea Islands', 'Costa Rica', 'Cote D\'Ivoire', 'Croatia', 'Cuba', 'Curaçao',
		'Cyprus', 'Czech Republic', 'Democratic Republic of the Congo', 'Denmark', 'Dhekelia',
		'Djibouti', 'Dominica', 'Dominican Republic', 'Ecuador', 'Egypt', 'El Salvador',
		'Equatorial Guinea', 'Eritrea', 'Estonia', 'Ethiopia', 'Europa Island',
		'Falkland Islands (Islas Malvinas)', 'Faroe Islands', 'Federated States of Micronesia',
		'Fiji', 'Finland', 'France', 'French Guiana', 'French Polynesia',
		'French Southern and Antarctic Lands', 'Gabon', 'Gaza Strip', 'Georgia', 'Germany',
		'Ghana', 'Gibraltar', 'Glorioso Islands', 'Greece', 'Greenland', 'Grenada',
		'Guadeloupe', 'Guam', 'Guatemala', 'Guernsey', 'Guinea', 'Guinea-bissau', 'Guyana',
		'Haiti', 'Heard Island and Mcdonald Islands', 'Holy See (Vatican City)', 'Honduras',
		'Hong Kong', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland',
		'Isle of Man', 'Israel', 'Italy', 'Jamaica', 'Jan Mayen', 'Japan', 'Jersey', 'Jordan',
		'Juan De Nova Island', 'Kazakhstan', 'Kenya', 'Kiribati', 'Kosovo', 'Kuwait',
		'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libya',
		'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macau', 'Macedonia', 'Madagascar',
		'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Martinique',
		'Mauritania', 'Mauritius', 'Mayotte', 'Mexico', 'Moldova', 'Monaco', 'Mongolia',
		'Montenegro', 'Montserrat', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia', 'Nauru',
		'Navassa Island', 'Nepal', 'Netherlands', 'Netherlands Antilles', 'New Caledonia',
		'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Niue', 'Norfolk Island',
		'North Korea', 'Northern Mariana Islands', 'Norway', 'Oman', 'Pakistan', 'Palau',
		'Palestine', 'Panama', 'Papua New Guinea', 'Paracel Islands', 'Paraguay', 'Peru',
		'Philippines', 'Pitcairn Islands', 'Poland', 'Portugal', 'Puerto Rico', 'Qatar',
		'Republic of the Congo', 'Reunion', 'Romania', 'Russia', 'Rwanda', 'Saint BarthÃ©lemy',
		'Saint Helena', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Martin',
		'Saint Pierre and Miquelon', 'Saint Vincent and the Grenadines', 'Samoa', 'San Marino',
		'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia', 'Seychelles',
		'Sierra Leone', 'Singapore', 'Sint Maarten', 'Slovakia', 'Slovenia', 'Solomon Islands',
		'Somalia', 'South Africa', 'South Georgia and the South Sandwich Islands',
		'South Korea', 'South Sudan', 'Spain', 'Spratly Islands', 'Sri Lanka', 'Sudan',
		'Suriname', 'Svalbard', 'Swaziland', 'Sweden', 'Switzerland', 'Syria', 'Taiwan',
		'Tajikistan', 'Tanzania', 'Thailand', 'The Bahamas', 'The Gambia', 'Timor-leste',
		'Togo', 'Tokelau', 'Tonga', 'Trinidad and Tobago', 'Tromelin Island', 'Tunisia',
		'Turkey', 'Turkmenistan', 'Turks and Caicos Islands', 'Tuvalu', 'Uganda', 'Ukraine',
		'United Arab Emirates', 'United Kingdom', 'United States', 'Uruguay', 'Uzbekistan',
		'Vanuatu', 'Venezuela', 'Vietnam', 'Virgin Islands', 'Wake Island', 'Wallis and Futuna',
		'West Bank', 'Western Sahara', 'Yemen', 'Zambia', 'Zimbabwe',
	);
}

/**
 * Canonical list of brochure language/format versions for the
 * cropx_resource CPT — single source of truth for both the "Download
 * Files" admin meta box (inc/cpts.php) and the resource-downloads block's
 * front-end version picker (src/blocks/resource-downloads/render.php).
 *
 * Order matters: it's the order versions appear in the admin meta box and
 * in the front-end <select>, and it's also the fallback-selection order —
 * when English (A4) has no file, the first entry below that DOES have one
 * becomes the default. English (A4) is deliberately second (not first) so
 * it can be visually grouped with English (US Letter) as a pair in the
 * admin UI, while still being the intended default in practice.
 *
 * Each entry's `meta_key` is the cropx_resource post meta key holding that
 * version's file URL. The English Letter/A4 keys (download_url_letter,
 * download_url_a4) predate this multi-language picker and are kept as-is
 * rather than renamed, so every already-published resource keeps working
 * with zero data migration — this list is purely additive on top of them.
 *
 * @return array<int, array{code: string, meta_key: string, label: string, group: string}>
 */
function cropx_resource_brochure_versions(): array {
	return array(
		array(
			'code'     => 'en_letter',
			'meta_key' => 'download_url_letter',
			'label'    => __( 'English (US Letter / Imperial Units)', 'cropx' ),
			'group'    => 'english',
		),
		array(
			'code'     => 'en_a4',
			'meta_key' => 'download_url_a4',
			'label'    => __( 'English (A4 / Metric Units)', 'cropx' ),
			'group'    => 'english',
		),
		array(
			'code'     => 'es_a4',
			'meta_key' => 'download_url_es_a4',
			'label'    => __( 'Español (A4 / Unidades métricas)', 'cropx' ),
			'group'    => 'other',
		),
		array(
			'code'     => 'pt_a4',
			'meta_key' => 'download_url_pt_a4',
			'label'    => __( 'Português (A4 / Unidades métricas)', 'cropx' ),
			'group'    => 'other',
		),
		array(
			'code'     => 'fr_a4',
			'meta_key' => 'download_url_fr_a4',
			'label'    => __( 'Français (A4 / Unités métriques)', 'cropx' ),
			'group'    => 'other',
		),
		array(
			'code'     => 'de_a4',
			'meta_key' => 'download_url_de_a4',
			'label'    => __( 'Deutsch (A4 / Metrische Einheiten)', 'cropx' ),
			'group'    => 'other',
		),
		array(
			'code'     => 'nl_a4',
			'meta_key' => 'download_url_nl_a4',
			'label'    => __( 'Nederlands (A4 / Metrische eenheden)', 'cropx' ),
			'group'    => 'other',
		),
		array(
			'code'     => 'ro_a4',
			'meta_key' => 'download_url_ro_a4',
			'label'    => __( 'Română (A4 / Unități metrice)', 'cropx' ),
			'group'    => 'other',
		),
		array(
			'code'     => 'ru_a4',
			'meta_key' => 'download_url_ru_a4',
			'label'    => __( 'Русский (A4 / Метрические единицы)', 'cropx' ),
			'group'    => 'other',
		),
	);
}

/**
 * Resolve the brochure versions that actually have a file for one
 * cropx_resource post — i.e. cropx_resource_brochure_versions() filtered
 * down to entries whose meta value is non-empty, each carrying its
 * resolved URL. Used by render.php to decide between the three
 * download-button states (none / single / picker) without duplicating
 * the meta-lookup-and-filter logic there.
 *
 * @param int $post_id
 * @return array<int, array{code: string, label: string, url: string}>
 */
function cropx_resource_get_available_versions( int $post_id ): array {
	$available = array();

	foreach ( cropx_resource_brochure_versions() as $version ) {
		$url = get_post_meta( $post_id, $version['meta_key'], true );
		if ( empty( $url ) ) {
			continue;
		}
		$available[] = array(
			'code'  => $version['code'],
			'label' => $version['label'],
			'url'   => $url,
		);
	}

	return $available;
}

/**
 * The current site visitor's language, as a brochure-version code prefix
 * ('en', 'es', 'fr', ...). Single source of truth for "which language is
 * this visitor viewing the site in" — everything that needs to make a
 * language-aware fallback decision (right now, just the resource card
 * thumbnail below) should call this rather than hardcoding 'en' itself.
 *
 * The site is English-only today, so this always returns 'en'. Once a
 * language switcher exists (Polylang or similar — see PROGRESS.md's i18n
 * plan), replace the body with a real lookup, e.g.:
 *   return function_exists( 'pll_current_language' ) ? pll_current_language() : 'en';
 * No caller needs to change when that happens.
 *
 * @return string
 */
function cropx_resource_get_current_lang_code(): string {
	return 'en';
}

/**
 * Which brochure version's file should supply a resource card's cover
 * thumbnail — deliberately independent of whatever version a visitor has
 * picked in the download dropdown. Lauren's call (Aug 2026): the thumbnail
 * is a stable representation of "this resource" on the page, not a live
 * preview of the currently-selected download, so it never changes when the
 * dropdown selection changes (see render.php/view.js — the <select> only
 * ever updates the download link, never the cover).
 *
 * Resolution order:
 *   1. The current site language's A4 version (e.g. 'fr_a4' on a future
 *      French site) — the version a visitor in that language would expect.
 *   2. Any other version in that same language (covers 'en_letter' if
 *      'en_a4' isn't uploaded, so English visitors still get an English
 *      thumbnail rather than skipping straight to another language).
 *   3. The English A4 version — the sitewide fallback today, and the
 *      fallback for every other language until that language's own file
 *      is uploaded.
 *   4. Whatever version happens to be first, if even English A4 is missing.
 *
 * On today's English-only site this always resolves to step 1 (lang is
 * always 'en', so it's functionally "English A4, else whatever's first") —
 * the extra steps exist so this function doesn't need to change shape when
 * cropx_resource_get_current_lang_code() starts returning other languages.
 *
 * @param int $post_id
 * @return array{code: string, label: string, url: string}|null
 */
function cropx_resource_get_thumbnail_version( int $post_id ): ?array {
	$versions = cropx_resource_get_available_versions( $post_id );
	if ( empty( $versions ) ) {
		return null;
	}

	$lang = cropx_resource_get_current_lang_code();

	foreach ( $versions as $v ) {
		if ( $lang . '_a4' === $v['code'] ) {
			return $v;
		}
	}
	foreach ( $versions as $v ) {
		if ( 0 === strpos( $v['code'], $lang . '_' ) ) {
			return $v;
		}
	}
	foreach ( $versions as $v ) {
		if ( 'en_a4' === $v['code'] ) {
			return $v;
		}
	}

	return $versions[0];
}

function cropx_url( string $url ): string {
	if ( empty( $url ) || $url === '#' ) {
		return $url;
	}

	// Already relative — nothing to do.
	if ( strncmp( $url, '/', 1 ) === 0 && strncmp( $url, '//', 2 ) !== 0 ) {
		return $url;
	}

	$home = untrailingslashit( home_url() );

	// Case-insensitive check: does this URL start with our home URL?
	if ( strncasecmp( $url, $home, strlen( $home ) ) === 0 ) {
		$path = substr( $url, strlen( $home ) );
		return $path !== '' ? $path : '/';
	}

	return $url;
}

