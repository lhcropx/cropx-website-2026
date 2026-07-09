( function () {
	/*
	 * Field Photos Gallery — front-end controller.
	 *
	 * Two responsibilities per block instance:
	 *
	 * 1. SCROLL STRIP NAV
	 *    Same pattern as testimonials-carousel/view.js.
	 *    Arrows step one item; dots jump to a snap position.
	 *    Native scroll-snap handles touch / trackpad swipe.
	 *    Dots are generated from the actual item count.
	 *
	 * 2. LIGHTBOX
	 *    Click any .fph-photo-wrap to open at that index.
	 *    Full-res URLs are stored in section.dataset.fphPhotos
	 *    (set by the inline <script> in render.php).
	 *    Prev / next arrows cycle through all photos.
	 *    Close via: close button, Escape key, or clicking the backdrop.
	 */

	document.querySelectorAll( '.fph-section' ).forEach( ( section ) => {

		/* ── DOM refs ── */
		const track    = section.querySelector( '.fph-track' );
		const prevBtn  = section.querySelector( '.fph-prev' );
		const nextBtn  = section.querySelector( '.fph-next' );
		const dotsWrap = section.querySelector( '.fph-dots' );

		const lightbox   = section.querySelector( '.fph-lightbox' );
		const lbImg      = section.querySelector( '.fph-lightbox-img' );
		const lbCaption  = section.querySelector( '.fph-lightbox-caption' );
		const lbClose    = section.querySelector( '.fph-lightbox-close' );
		const lbPrev     = section.querySelector( '.fph-lightbox-prev' );
		const lbNext     = section.querySelector( '.fph-lightbox-next' );

		if ( ! track ) return;

		/* ── Photo data (full-res URLs for lightbox) ── */
		let photos = [];
		try {
			photos = JSON.parse( section.dataset.fphPhotos || '[]' );
		} catch ( e ) {
			// eslint-disable-next-line no-console
			console.warn( 'fph: could not parse photo data', e );
		}

		const items = Array.from( track.children );
		const count = items.length;
		let dots = [];

		/* ════════════════════════════════════════
		   SCROLL STRIP NAV
		   ════════════════════════════════════════ */

		function visibleCount() {
			if ( ! items.length ) return 1;
			const vw    = track.clientWidth;
			const cardW = items[ 0 ].getBoundingClientRect().width;
			const gap   = parseFloat( getComputedStyle( track ).gap ) || 0;
			return Math.max( 1, Math.round( ( vw + gap ) / ( cardW + gap ) ) );
		}

		function maxIndex() {
			return Math.max( 0, count - visibleCount() );
		}

		function renderDots() {
			if ( ! dotsWrap ) return;
			dotsWrap.innerHTML = '';
			const total = maxIndex() + 1;
			for ( let i = 0; i < total; i++ ) {
				const d = document.createElement( 'button' );
				d.className = 'fph-dot' + ( i === 0 ? ' is-active' : '' );
				d.type      = 'button';
				d.setAttribute( 'role', 'tab' );
				d.setAttribute( 'aria-label', 'Go to photo ' + ( i + 1 ) );
				d.addEventListener( 'click', () => scrollToItem( i ) );
				dotsWrap.appendChild( d );
			}
			dots = Array.from( dotsWrap.querySelectorAll( '.fph-dot' ) );
		}

		function getActiveIndex() {
			const trackLeft = track.getBoundingClientRect().left;
			let closest = 0;
			let minDist  = Infinity;
			items.forEach( ( el, i ) => {
				const dist = Math.abs( el.getBoundingClientRect().left - trackLeft );
				if ( dist < minDist ) { minDist = dist; closest = i; }
			} );
			return Math.min( closest, maxIndex() );
		}

		function syncDots() {
			const a = getActiveIndex();
			dots.forEach( ( d, i ) => d.classList.toggle( 'is-active', i === a ) );
			if ( prevBtn ) prevBtn.disabled = a === 0;
			if ( nextBtn ) nextBtn.disabled = a === maxIndex();
		}

		function scrollToItem( i ) {
			if ( items[ i ] ) {
				items[ i ].scrollIntoView( { behavior: 'smooth', block: 'nearest', inline: 'start' } );
			}
			setTimeout( syncDots, 400 );
		}

		if ( prevBtn ) {
			prevBtn.addEventListener( 'click', () => {
				const a = getActiveIndex();
				if ( a > 0 ) scrollToItem( a - 1 );
			} );
		}
		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', () => {
				const a = getActiveIndex();
				if ( a < maxIndex() ) scrollToItem( a + 1 );
			} );
		}

		track.addEventListener( 'scroll', () => requestAnimationFrame( syncDots ), { passive: true } );
		window.addEventListener( 'resize', () => { renderDots(); syncDots(); } );

		renderDots();
		syncDots();

		/* ════════════════════════════════════════
		   LIGHTBOX
		   ════════════════════════════════════════ */

		if ( ! lightbox || ! lbImg ) return;

		let currentLbIndex = 0;
		// Keep a reference to the element that triggered the lightbox so we
		// can return focus to it when the lightbox closes.
		let triggerEl = null;

		function updateLightbox() {
			const p = photos[ currentLbIndex ];
			if ( ! p ) return;
			lbImg.src = p.src;
			lbImg.alt = p.alt || '';
			if ( lbCaption ) {
				lbCaption.textContent = p.caption || '';
			}
			if ( lbPrev ) lbPrev.disabled = currentLbIndex === 0;
			if ( lbNext ) lbNext.disabled = currentLbIndex === photos.length - 1;
		}

		function openLightbox( index, originEl ) {
			currentLbIndex = index;
			triggerEl = originEl || null;
			updateLightbox();
			lightbox.hidden = false;
			document.body.style.overflow = 'hidden';
			// Shift focus into the lightbox
			if ( lbClose ) lbClose.focus();
		}

		function closeLightbox() {
			lightbox.hidden = true;
			document.body.style.overflow = '';
			// Return focus to the card that opened the lightbox
			if ( triggerEl ) triggerEl.focus();
		}

		/* ── Open on photo click ── */
		items.forEach( ( item ) => {
			const wrap = item.querySelector( '.fph-photo-wrap' );
			if ( ! wrap ) return;

			// Make the wrap keyboard-focusable so keyboard users can also open the lightbox
			wrap.setAttribute( 'role', 'button' );
			wrap.setAttribute( 'tabindex', '0' );

			const idx = parseInt( item.dataset.index, 10 );

			wrap.addEventListener( 'click', () => openLightbox( idx, wrap ) );
			wrap.addEventListener( 'keydown', ( e ) => {
				if ( e.key === 'Enter' || e.key === ' ' ) {
					e.preventDefault();
					openLightbox( idx, wrap );
				}
			} );
		} );

		/* ── Close button ── */
		if ( lbClose ) {
			lbClose.addEventListener( 'click', closeLightbox );
		}

		/* ── Prev / next inside lightbox ── */
		if ( lbPrev ) {
			lbPrev.addEventListener( 'click', () => {
				if ( currentLbIndex > 0 ) {
					currentLbIndex--;
					updateLightbox();
				}
			} );
		}
		if ( lbNext ) {
			lbNext.addEventListener( 'click', () => {
				if ( currentLbIndex < photos.length - 1 ) {
					currentLbIndex++;
					updateLightbox();
				}
			} );
		}

		/* ── Backdrop click (click directly on the overlay, not its children) ── */
		lightbox.addEventListener( 'click', ( e ) => {
			if ( e.target === lightbox ) closeLightbox();
		} );

		/* ── Keyboard: Escape / arrow keys ── */
		document.addEventListener( 'keydown', ( e ) => {
			if ( lightbox.hidden ) return; // this lightbox is not open

			if ( e.key === 'Escape' ) {
				closeLightbox();
				return;
			}

			if ( e.key === 'ArrowLeft' && currentLbIndex > 0 ) {
				currentLbIndex--;
				updateLightbox();
				return;
			}

			if ( e.key === 'ArrowRight' && currentLbIndex < photos.length - 1 ) {
				currentLbIndex++;
				updateLightbox();
			}
		} );

	} );
}() );
