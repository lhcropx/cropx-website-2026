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
 * │ cropx-knowledge-hub │ Blog, Case Studies, Resources, White Papers, etc │
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
		'cropx-solutions'     => __( 'Solutions Dropdown', 'cropx' ),
		'cropx-platform'      => __( 'Platform Mega Menu', 'cropx' ),
		'cropx-knowledge-hub' => __( 'Knowledge Hub Dropdown', 'cropx' ),
		'cropx-utility'       => __( 'Utility Links (Resources, Company)', 'cropx' ),
	) );
} );


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
		$item    = $data_object;
		$output .= '<a href="' . esc_url( cropx_url( $item->url ) ) . '">' . esc_html( $item->title );
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
			if ( $is_mobile ) {
				$output .= '<a href="' . esc_url( cropx_url( $item->url ) ) . '">' . esc_html( $item->title ) . '</a>';
			} else {
				$output .= '<a href="' . esc_url( cropx_url( $item->url ) ) . '">';
				$output .= '<span class="cnav-mega-link-title">' . esc_html( $item->title ) . '</span>';
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

		if ( $is_mobile ) {
			$output .= '<li class="cnav-mobile-item"><a href="' . esc_url( cropx_url( $item->url ) ) . '">' . esc_html( $item->title );
		} else {
			$output .= '<li class="cnav-item"><a href="' . esc_url( cropx_url( $item->url ) ) . '" class="cnav-link">' . esc_html( $item->title );
		}
	}

	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		$output .= '</a></li>';
	}
}
