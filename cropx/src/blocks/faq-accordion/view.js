( function () {
	function init() {
		document.querySelectorAll( '.faq-list' ).forEach( ( list ) => {
			const items = Array.from( list.querySelectorAll( '.faq-item' ) );

			items.forEach( ( item ) => {
				const toggle = item.querySelector( '.faq-toggle' );
				if ( ! toggle ) { return; }

				toggle.addEventListener( 'click', () => {
					const wasOpen = item.classList.contains( 'is-open' );

					// Close every item in this list
					items.forEach( ( i ) => {
						i.classList.remove( 'is-open' );
						const btn = i.querySelector( '.faq-toggle' );
						if ( btn ) { btn.setAttribute( 'aria-expanded', 'false' ); }
					} );

					// Open the clicked item only if it was previously closed
					if ( ! wasOpen ) {
						item.classList.add( 'is-open' );
						toggle.setAttribute( 'aria-expanded', 'true' );
					}
				} );
			} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
