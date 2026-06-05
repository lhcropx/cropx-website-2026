/**
 * Shared nav initialiser — handles all .cnav-block navs on the page.
 *
 * Extracted from cropx/nav view.js and cropx/segment-hero view.js (Phase 3).
 * Both blocks' view.js files import this module and call initCropxNav().
 *
 * A data-cnav-init guard prevents double-setup when both blocks appear on the
 * same page (unlikely in practice, but safe by design).
 */
export function initCropxNav() {
	document.querySelectorAll( '.cnav-block:not([data-cnav-init])' ).forEach( ( nav ) => {
		nav.dataset.cnavInit = '1';

		const navItems    = Array.from( nav.querySelectorAll( '.cnav-item' ) );
		const hamburger   = nav.querySelector( '.cnav-hamburger' );
		const mobilePanel = nav.querySelector( '.cnav-mobile-panel' );
		const megaDropdown = nav.querySelector( '.cnav-dropdown--mega' );

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
				// Close all items and the mega dropdown.
				navItems.forEach( ( i ) => {
					i.classList.remove( 'is-open' );
					const btn = i.querySelector( '.cnav-btn' );
					if ( btn ) btn.setAttribute( 'aria-expanded', 'false' );
				} );
				if ( megaDropdown ) megaDropdown.classList.remove( 'is-open' );

				if ( ! isOpen ) {
					item.classList.add( 'is-open' );
					trigger.setAttribute( 'aria-expanded', 'true' );
					// Directly open the mega dropdown if this is the Platform trigger.
					if ( item.classList.contains( 'cnav-item--has-mega' ) && megaDropdown ) {
						megaDropdown.classList.add( 'is-open' );
					}
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
			if ( megaDropdown ) megaDropdown.classList.remove( 'is-open' );
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
			if ( megaDropdown ) megaDropdown.classList.remove( 'is-open' );
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
}
