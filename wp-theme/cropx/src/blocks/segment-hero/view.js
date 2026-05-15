( function () {
	document.querySelectorAll( '.sgh-block' ).forEach( ( block ) => {
		const nav      = block.querySelector( '.sgh-nav' );
		const navItems = Array.from( block.querySelectorAll( '.sgh-nav-item' ) );

		if ( ! nav ) return;

		// ── Scroll shadow ──────────────────────────────────────
		function updateSolid() {
			nav.classList.toggle( 'is-solid', window.scrollY > 40 );
		}
		window.addEventListener( 'scroll', updateSolid, { passive: true } );
		updateSolid();

		// ── Dropdown toggle ────────────────────────────────────
		navItems.forEach( ( item ) => {
			const trigger = item.querySelector( '.sgh-nav-btn' );
			if ( ! trigger ) return;

			trigger.addEventListener( 'click', ( e ) => {
				e.stopPropagation();
				const isOpen = item.classList.contains( 'is-open' );
				navItems.forEach( ( i ) => {
					i.classList.remove( 'is-open' );
					const btn = i.querySelector( '.sgh-nav-btn' );
					if ( btn ) btn.setAttribute( 'aria-expanded', 'false' );
				} );
				if ( ! isOpen ) {
					item.classList.add( 'is-open' );
					trigger.setAttribute( 'aria-expanded', 'true' );
				}
			} );
		} );

		// Close on outside click
		document.addEventListener( 'click', () => {
			navItems.forEach( ( i ) => {
				i.classList.remove( 'is-open' );
				const btn = i.querySelector( '.sgh-nav-btn' );
				if ( btn ) btn.setAttribute( 'aria-expanded', 'false' );
			} );
		} );

		// Close on Escape
		document.addEventListener( 'keydown', ( e ) => {
			if ( e.key !== 'Escape' ) return;
			navItems.forEach( ( i ) => {
				i.classList.remove( 'is-open' );
				const btn = i.querySelector( '.sgh-nav-btn' );
				if ( btn ) btn.setAttribute( 'aria-expanded', 'false' );
			} );
		} );

		// Prevent clicks inside a dropdown from bubbling up and closing it
		block.querySelectorAll( '.sgh-dropdown' ).forEach( ( d ) => {
			d.addEventListener( 'click', ( e ) => e.stopPropagation() );
		} );
	} );
}() );
