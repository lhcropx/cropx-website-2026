<?php
/**
 * Navigation menu registration and custom walkers.
 *
 * Three menus power the CropX nav — all managed through Appearance → Menus
 * in the WP admin. Custom walkers output the exact cnav-* HTML the design
 * expects, so nothing about the published page changes.
 *
 * ┌─────────────────────┬──────────────────────────────────────────────────┐
 * │ Menu location       │ What goes in it                                  │
 * ├─────────────────────┼──────────────────────────────────────────────────┤
 * │ cropx-solutions     │ Enterprise, Service Providers, On-Farm           │
 * │ cropx-platform      │ Product links inside the Platform mega dropdown  │
 * │                     │ Use hierarchy: group headings as top-level items, │
 * │                     │ links as their children. Fill in the Description  │
 * │                     │ field (enable via Screen Options) for subtitles.  │
 * │ cropx-knowledge-hub │ Blog, Case Studies, Resources, Video Testimonials, etc │
 * │ cropx-utility       │ Resources, Company (the two simple nav links)     │
 * └─────────────────────┴──────────────────────────────────────────────────┘
 *
 * The login button URL stays as a block attribute — it's a styled CTA,
 * not a standard menu item.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', function () {
	register_nav_menus( array(
		// Header nav
		'cropx-solutions'     => __( 'Solutions Dropdown', 'cropx' ),
		'cropx-platform'      => __( 'Platform Mega Menu', 'cropx' ),
		'cropx-knowledge-hub' => __( 'Knowledge Hub Dropdown', 'cropx' ),
		'cropx-about'         => __( 'About Dropdown', 'cropx' ),
		'cropx-contact'       => __( 'Contact Dropdown', 'cropx' ),
		'cropx-utility'       => __( 'Utility Links (Resources, Company)', 'cropx' ),
		// Footer legal bar — standalone menu (all other footer columns reuse header nav menus)
		'footer-legal' => __( 'Footer: Legal', 'cropx' ),
	) );
} );


// ─────────────────────────────────────────────────────────────────────────────
// External-link icon helper
// ─────────────────────────────────────────────────────────────────────────────
//
// Returns a <span class="cnav-external-icon"> wrapping an inline SVG.
// Injected by each walker when $item->target === '_blank'.
//
// Trigger in WP Admin: Appearance → Menus → Screen Options → enable "Link Target"
// → check "Open link in a new tab" on the specific menu item.
//
// The SVG uses currentColor so the icon colour is set entirely in CSS
// (.cnav-external-icon { color: var(--cropx-blue); } — defined in shared.css).

function cropx_nav_external_icon() {
	return '<span class="cnav-external-icon" aria-hidden="true">'
		. '<svg width="12" height="12" viewBox="0 0 16 16" fill="none" focusable="false">'
		. '<path d="M7 3H4a2 2 0 00-2 2v7a2 2 0 002 2h7a2 2 0 002-2V9M10 2h4v4M14 2L8.5 7.5"'
		. ' stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'
		. '</svg>'
		. '</span>';
}


// ─────────────────────────────────────────────────────────────────────────────
// Walker: Solutions dropdown
// ─────────────────────────────────────────────────────────────────────────────
//
// Outputs plain <a> tags — no <li> wrappers — to match the
// cnav-dropdown--simple and cnav-mobile-sub containers in the nav partial.
//
// Desktop output (inside .cnav-dropdown--simple):
//   <a href="/enterprise">Enterprise</a>
//   <a href="/service-providers">Service Providers</a>
//   <a href="/on-farm">On-Farm</a>
//
// Mobile output (inside .cnav-mobile-sub) — identical structure.

class CropX_Solutions_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {}
	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item   = $data_object;
		$is_ext = '_blank' === $item->target;
		$target = $is_ext ? ' target="_blank" rel="noopener noreferrer"' : '';
		$output .= '<a href="' . esc_url( cropx_url( $item->url ) ) . '"' . $target . '>' . esc_html( $item->title );
		if ( $is_ext ) {
			$output .= cropx_nav_external_icon();
		}
	}

	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		$output .= '</a>';
	}
}


// ─────────────────────────────────────────────────────────────────────────────
// Walker: Platform mega menu
// ─────────────────────────────────────────────────────────────────────────────
//
// Menu structure in WP Admin (Appearance → Menus):
//   Sensing              ← top-level item, URL not required (use '#' or leave blank)
//     Soil Sensing       ← child item; put the subtitle in the Description field
//     Crop Monitoring    ← child item
//   Planning             ← top-level
//     Irrigation Planning
//     Nutrient Management
//   …etc.
//
// Enable the Description field via Screen Options (top-right of the Menus page).
//
// Desktop output (inside .cnav-mega-inner):
//   <div class="cnav-mega-group">
//     <p class="cnav-mega-heading">Sensing</p>
//     <a href="..."><span class="cnav-mega-link-title">Soil Sensing</span>
//                   <span class="cnav-mega-link-desc">Real-time moisture…</span></a>
//     …
//   </div>
//
// Mobile output (inside .cnav-mobile-sub) — pass 'cropx_context' => 'mobile'
// in the wp_nav_menu() args:
//   <p class="cnav-mobile-sub-heading">Sensing</p>
//   <a href="...">Soil Sensing</a>
//   …

class CropX_Platform_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		// Group div is closed in end_el instead — end_lvl only fires when an item
		// has children, but end_el fires for every item regardless. Closing in
		// end_el means flat menus (no children added yet) don't produce unclosed divs.
	}

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item      = $data_object;
		$is_mobile = isset( $args->cropx_context ) && 'mobile' === $args->cropx_context;

		if ( 0 === $depth ) {
			// Group heading.
			if ( $is_mobile ) {
				$output .= '<p class="cnav-mobile-sub-heading">' . esc_html( $item->title ) . '</p>';
			} else {
				$output .= '<div class="cnav-mega-group">';
				$output .= '<p class="cnav-mega-heading">' . esc_html( $item->title ) . '</p>';
			}
		} else {
			// Link item (child of a group heading).
			$is_ext = '_blank' === $item->target;
			$target = $is_ext ? ' target="_blank" rel="noopener noreferrer"' : '';
			if ( $is_mobile ) {
				$output .= '<a href="' . esc_url( cropx_url( $item->url ) ) . '"' . $target . '>' . esc_html( $item->title );
				if ( $is_ext ) {
					$output .= cropx_nav_external_icon();
				}
				$output .= '</a>';
			} else {
				$output .= '<a href="' . esc_url( cropx_url( $item->url ) ) . '"' . $target . '>';
				$output .= '<span class="cnav-mega-link-title">' . esc_html( $item->title );
				if ( $is_ext ) {
					$output .= cropx_nav_external_icon();
				}
				$output .= '</span>';
				if ( $item->description ) {
					$output .= '<span class="cnav-mega-link-desc">' . esc_html( $item->description ) . '</span>';
				}
				$output .= '</a>';
			}
		}
	}

	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		$is_mobile = isset( $args->cropx_context ) && 'mobile' === $args->cropx_context;
		// Close the cnav-mega-group div opened in start_el for every depth-0 item.
		// Works whether the item has children (end_lvl fires first, then this) or not.
		if ( 0 === $depth && ! $is_mobile ) {
			$output .= '</div>';
		}
		// Depth-1 link <a> tags are self-closed in start_el above.
	}
}


// ─────────────────────────────────────────────────────────────────────────────
// Walker: Utility links (Resources, Company)
// ─────────────────────────────────────────────────────────────────────────────
//
// Desktop output (items rendered inside .cnav-links <ul>):
//   <li class="cnav-item"><a class="cnav-link" href="...">Resources</a></li>
//
// Mobile output — pass 'cropx_context' => 'mobile':
//   <li class="cnav-mobile-item"><a href="...">Resources</a></li>

class CropX_Utility_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {}
	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item      = $data_object;
		$is_mobile = isset( $args->cropx_context ) && 'mobile' === $args->cropx_context;
		$is_ext    = '_blank' === $item->target;
		$target    = $is_ext ? ' target="_blank" rel="noopener noreferrer"' : '';

		if ( $is_mobile ) {
			$output .= '<li class="cnav-mobile-item"><a href="' . esc_url( cropx_url( $item->url ) ) . '"' . $target . '>' . esc_html( $item->title );
		} else {
			$output .= '<li class="cnav-item"><a href="' . esc_url( cropx_url( $item->url ) ) . '" class="cnav-link"' . $target . '>' . esc_html( $item->title );
		}
		if ( $is_ext ) {
			$output .= cropx_nav_external_icon();
		}
	}

	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		$output .= '</a></li>';
	}
}


// ─────────────────────────────────────────────────────────────────────────────
// Walker: Footer nav columns
// ─────────────────────────────────────────────────────────────────────────────
//
// Outputs plain <a> tags — no <ul>/<li> wrappers — to match the
// .footer-nav-group structure expected by the footer block's CSS.
//
// Used for all four footer menu locations:
//   footer-platform, footer-solutions, footer-company, footer-legal
//
// Output:
//   <a href="/soil-sensing">Soil Sensing</a>
//   <a href="/irrigation-planning">Irrigation Planning</a>
//   ...

class CropX_Footer_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {}
	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item   = $data_object;
		$is_ext = '_blank' === $item->target;
		$target = $is_ext ? ' target="_blank" rel="noopener noreferrer"' : '';
		$output .= '<a href="' . esc_url( $item->url ) . '"' . $target . '>' . esc_html( $item->title );
	}

	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		$output .= '</a>' . "\n";
	}
}


// ─────────────────────────────────────────────────────────────────────────────
// Footer: Platform mega menu group extractor
// ─────────────────────────────────────────────────────────────────────────────
//
// The Platform mega menu uses top-level items as group headings (Hardware,
// Software, etc.) with the actual nav links as their children. The main nav
// renders these as a mega menu panel. The footer wants to show Hardware and
// Software as separate columns — without a "Platform" parent heading — so
// we pull just the children of the matching group directly.
//
// Usage in render.php:
//   cropx_footer_platform_children( 'Hardware' );
//   cropx_footer_platform_children( 'Software' );

function cropx_footer_platform_children( string $group_title ): void {
	$locations = get_nav_menu_locations();
	if ( empty( $locations['cropx-platform'] ) ) {
		return;
	}
	$items = wp_get_nav_menu_items( $locations['cropx-platform'] );
	if ( ! $items ) {
		return;
	}

	// Find the depth-0 item whose title matches the requested group heading.
	$parent_id = null;
	foreach ( $items as $item ) {
		if ( 0 == $item->menu_item_parent && 0 === strcasecmp( $item->title, $group_title ) ) {
			$parent_id = $item->ID;
			break;
		}
	}
	if ( null === $parent_id ) {
		return;
	}

	// Output children of that parent as plain <a> tags.
	foreach ( $items as $item ) {
		if ( (int) $item->menu_item_parent === $parent_id ) {
			$is_ext = '_blank' === $item->target;
			$target = $is_ext ? ' target="_blank" rel="noopener noreferrer"' : '';
			echo '<a href="' . esc_url( $item->url ) . '"' . $target . '>' . esc_html( $item->title ) . '</a>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}
