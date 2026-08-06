( function () {
	document.querySelectorAll( '.seg-section' ).forEach( ( section ) => {
		const tabBar = section.querySelector( '.seg-tab-bar' );
		if ( ! tabBar ) return;

		const tabs   = Array.from( tabBar.querySelectorAll( '[role="tab"]' ) );
		const panels = tabs.map( ( tab ) => document.getElementById( tab.getAttribute( 'aria-controls' ) ) );

		// switchTab: update state only (no focus move) — used for click/touch.
		function switchTab( idx ) {
			tabs.forEach( ( tab, i ) => {
				const isActive = i === idx;
				tab.classList.toggle( 'active', isActive );
				tab.setAttribute( 'aria-selected', String( isActive ) );
				tab.tabIndex = isActive ? 0 : -1;
			} );
			panels.forEach( ( panel, i ) => {
				if ( panel ) panel.classList.toggle( 'active', i === idx );
			} );
		}

		// activateTab: switchTab + move focus — used for keyboard navigation only.
		function activateTab( idx ) {
			switchTab( idx );
			tabs[ idx ].focus();
		}

		tabs.forEach( ( tab, idx ) => {
			// Click/touch: switch panel, keep focus on the clicked button naturally.
			tab.addEventListener( 'click', () => switchTab( idx ) );

			// Keyboard: WAI-ARIA automatic-activation pattern.
			tab.addEventListener( 'keydown', ( e ) => {
				let next;
				switch ( e.key ) {
					case 'ArrowRight':
						e.preventDefault();
						next = ( idx + 1 ) % tabs.length;
						break;
					case 'ArrowLeft':
						e.preventDefault();
						next = ( idx - 1 + tabs.length ) % tabs.length;
						break;
					case 'Home':
						e.preventDefault();
						next = 0;
						break;
					case 'End':
						e.preventDefault();
						next = tabs.length - 1;
						break;
				}
				if ( next !== undefined ) activateTab( next );
			} );
		} );
	} );
}() );
