/**
 * Product Tabs block — front-end tab switching.
 *
 * Implements the WAI-ARIA Tabs pattern:
 *   https://www.w3.org/WAI/ARIA/apg/patterns/tabs/
 *
 * - Click or Enter/Space activates a tab and reveals its panel.
 * - ArrowLeft / ArrowRight move focus and auto-activate the adjacent tab.
 * - Home / End jump to first / last tab.
 * - Tab moves focus into the active panel (tabindex="0" on panel).
 *
 * Panels use the HTML `hidden` attribute so inactive content is removed
 * from the accessibility tree entirely (not just visually hidden).
 */

document.querySelectorAll( '.ptabs-block' ).forEach( ( block ) => {
	const tabBar = block.querySelector( '[role="tablist"]' );
	const tabs   = Array.from( block.querySelectorAll( '.ptabs-tab[role="tab"]' ) );
	const panels = tabs.map( ( t ) => document.getElementById( t.getAttribute( 'aria-controls' ) ) );

	function activate( tab ) {
		// Deactivate all
		tabs.forEach( ( t ) => {
			t.setAttribute( 'aria-selected', 'false' );
			t.setAttribute( 'tabindex', '-1' );
		} );
		panels.forEach( ( p ) => {
			if ( p ) p.hidden = true;
		} );

		// Activate selected tab + panel
		tab.setAttribute( 'aria-selected', 'true' );
		tab.setAttribute( 'tabindex', '0' );
		const panel = document.getElementById( tab.getAttribute( 'aria-controls' ) );
		if ( panel ) panel.hidden = false;
	}

	// Click
	tabs.forEach( ( tab ) => {
		tab.addEventListener( 'click', () => {
			activate( tab );
			tab.focus();
		} );
	} );

	// Keyboard navigation within the tab bar
	if ( tabBar ) {
		tabBar.addEventListener( 'keydown', ( e ) => {
			const idx = tabs.indexOf( document.activeElement );
			if ( idx === -1 ) return;

			let next;
			if      ( e.key === 'ArrowRight' ) next = ( idx + 1 ) % tabs.length;
			else if ( e.key === 'ArrowLeft'  ) next = ( idx - 1 + tabs.length ) % tabs.length;
			else if ( e.key === 'Home'        ) next = 0;
			else if ( e.key === 'End'         ) next = tabs.length - 1;
			else return;

			e.preventDefault();
			activate( tabs[ next ] );
			tabs[ next ].focus();
		} );
	}
} );
