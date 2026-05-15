/* Nav dropdown/scroll logic is duplicated in cropx/segment-hero view.js — extract to a shared module in Phase 3.
   See: wp-theme/cropx/src/blocks/segment-hero/view.js */
( function () {
	document.querySelectorAll( '.cnav-block' ).forEach( ( nav ) => {
		const navItems   = Array.from( nav.querySelectorAll( '.cnav-item' ) );
		const hamburger  = nav.querySelector( '.cnav-hamburger' );
		const mobilePanel = nav.querySelector( '.cnav-mobile-panel' );

		// ── Scroll shadow ──────────────────────────────────────
		function updateSolid() {
			nav.classList.toggle( 'is-solid', window.scrollY > 40 );
		}
		window.addEventListener( 'scroll', updateSolid, { passive: true } );
		updateSolid();

		// ── Mobile: close helper ──────────────────────────────
		function closeMobile() {
			nav.classList.remove( 'is-mobile-open' );
			if ( hamburger ) hamburger.setAttribute( 'aria-expanded', 'false' );
			if ( mobilePanel ) mobilePanel.setAttribute( 'aria-hidden', 'true' );
		}

		// ── Desktop dropdown toggle ────────────────────────────
		navItems.forEach( ( item ) => {
			const trigger = item.querySelector( '.cnav-btn' );
			if ( ! trigger ) return;

			trigger.addEventListener( 'click', ( e ) => {
				e.stopPropagation();
				const isOpen = item.classList.contains( 'is-open' );
				navItems.forEach( ( i ) => {
					i.classList.remove( 'is-open' );
					const btn = i.querySelector( '.cnav-btn' );
					if ( btn ) btn.setAttribute( 'aria-expanded', 'false' );
				} );
				if ( ! isOpen ) {
					item.classList.add( 'is-open' );
					trigger.setAttribute( 'aria-expanded', 'true' );
				}
			} );
		} );

		// ── Mobile hamburger toggle ────────────────────────────
		if ( hamburger && mobilePanel ) {
			hamburger.addEventListener( 'click', ( e ) => {
				e.stopPropagation();
				const isOpen = nav.classList.contains( 'is-mobile-open' );
				if ( isOpen ) {
					closeMobile();
				} else {
					nav.classList.add( 'is-mobile-open' );
					hamburger.setAttribute( 'aria-expanded', 'true' );
					mobilePanel.setAttribute( 'aria-hidden', 'false' );
				}
			} );

			// Prevent clicks inside panel from bubbling to outside-click handler
			mobilePanel.addEventListener( 'click', ( e ) => e.stopPropagation() );

			// Close on any link click within the mobile panel
			mobilePanel.querySelectorAll( 'a' ).forEach( ( link ) => {
				link.addEventListener( 'click', closeMobile );
			} );

			// Expandable sections within the mobile panel
			mobilePanel.querySelectorAll( '.cnav-mobile-btn' ).forEach( ( btn ) => {
				btn.addEventListener( 'click', () => {
					const item = btn.closest( '.cnav-mobile-item' );
					if ( ! item ) return;
					const isOpen = item.classList.contains( 'is-open' );
					item.classList.toggle( 'is-open', ! isOpen );
					btn.setAttribute( 'aria-expanded', String( ! isOpen ) );
				} );
			} );
		}

		// ── Close all on outside click ─────────────────────────
		document.addEventListener( 'click', () => {
			navItems.forEach( ( i ) => {
				i.classList.remove( 'is-open' );
				const btn = i.querySelector( '.cnav-btn' );
				if ( btn ) btn.setAttribute( 'aria-expanded', 'false' );
			} );
			closeMobile();
		} );

		// ── Close on Escape ────────────────────────────────────
		document.addEventListener( 'keydown', ( e ) => {
			if ( e.key !== 'Escape' ) return;
			navItems.forEach( ( i ) => {
				i.classList.remove( 'is-open' );
				const btn = i.querySelector( '.cnav-btn' );
				if ( btn ) btn.setAttribute( 'aria-expanded', 'false' );
			} );
			if ( nav.classList.contains( 'is-mobile-open' ) ) {
				closeMobile();
				if ( hamburger ) hamburger.focus();
			}
		} );

		// Prevent clicks inside desktop dropdowns from closing them
		nav.querySelectorAll( '.cnav-dropdown' ).forEach( ( d ) => {
			d.addEventListener( 'click', ( e ) => e.stopPropagation() );
		} );
	} );
}() );
