/**
 * Product Tabs block — front-end tab switching.
 *
 * Uses aria roles (tablist / tab / tabpanel) so the switching is
 * screen-reader friendly: aria-selected toggles on the buttons,
 * and the inactive panel uses the HTML `hidden` attribute so it is
 * truly removed from the accessibility tree rather than just visually
 * hidden with CSS.
 */

document.querySelectorAll( '.ptabs-block' ).forEach( ( block ) => {
	const tabs   = Array.from( block.querySelectorAll( '.ptabs-tab' ) );
	const panels = Array.from( block.querySelectorAll( '.ptabs-panel' ) );

	tabs.forEach( ( tab ) => {
		tab.addEventListener( 'click', () => {
			// Deactivate all
			tabs.forEach( ( t ) => {
				t.classList.remove( 'is-active' );
				t.setAttribute( 'aria-selected', 'false' );
			} );
			panels.forEach( ( p ) => {
				p.classList.remove( 'is-active' );
				p.hidden = true;
			} );

			// Activate clicked tab + its panel
			tab.classList.add( 'is-active' );
			tab.setAttribute( 'aria-selected', 'true' );

			const targetId    = tab.getAttribute( 'aria-controls' );
			const targetPanel = block.querySelector( '#' + targetId );
			if ( targetPanel ) {
				targetPanel.classList.add( 'is-active' );
				targetPanel.hidden = false;
			}
		} );
	} );
} );
