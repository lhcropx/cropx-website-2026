import { initScrollReveal } from '../../shared/scrollReveal';

initScrollReveal( '.seg-section' );

( function () {
	// ── LAZY-LOAD BACKGROUND IMAGES (Sep 2026) ──────────────────────────────
	// .seg-panel-photo can't use native <img loading="lazy"> because it's a
	// CSS background-image (needed for the object-fit-style cover/position
	// behavior). This IntersectionObserver swaps each panel's data-bg
	// attribute (set in render.php) into a real background-image once the
	// panel is within ~400px of the viewport, so all 3 photos on this block
	// don't compete with earlier, higher-priority requests on page load.
	// rootMargin is generous on purpose — this section usually sits fairly
	// high on segment landing pages, so we want the photo ready well before
	// the visitor actually scrolls to it, not popping in at the last second.
	const lazyPhotos = document.querySelectorAll( '.seg-panel-photo[data-bg]' );
	if ( lazyPhotos.length ) {
		if ( 'IntersectionObserver' in window ) {
			const applyBg = ( el ) => {
				el.style.backgroundImage = 'url(' + el.dataset.bg + ')';
				el.removeAttribute( 'data-bg' );
			};
			const observer = new IntersectionObserver(
				( entries, obs ) => {
					entries.forEach( ( entry ) => {
						if ( entry.isIntersecting ) {
							applyBg( entry.target );
							obs.unobserve( entry.target );
						}
					} );
				},
				{ rootMargin: '400px 0px' }
			);
			lazyPhotos.forEach( ( el ) => observer.observe( el ) );
		} else {
			// No IntersectionObserver support (very old browser) — load
			// immediately rather than never loading at all.
			lazyPhotos.forEach( ( el ) => {
				el.style.backgroundImage = 'url(' + el.dataset.bg + ')';
				el.removeAttribute( 'data-bg' );
			} );
		}
	}

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
