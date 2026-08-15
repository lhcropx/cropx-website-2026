/**
 * Zoho Form (Embed) — front-end interaction (view.js)
 *
 * This form POSTs directly to Zoho's own endpoint (a different origin), so
 * unlike cropx/contact-form we can't intercept the submit with fetch() and
 * read back a real success/failure response — a cross-origin POST body is
 * opaque to JS, and any "it worked!" message we showed after an opaque
 * response would just be a guess dressed up as feedback. So this script
 * does exactly one job: run the same required-field / email checks Zoho's
 * own hosted form would run, and only let the browser actually navigate to
 * Zoho once those pass. If everything's valid, we get out of the way and
 * let the native form submission happen — the browser will land wherever
 * Zoho's response takes it (or the redirectUrl, if one is set).
 */

document.querySelectorAll( '.zform-form' ).forEach( ( form ) => {
	const submitBtn = form.querySelector( '.zform-submit' );

	form.addEventListener( 'submit', ( e ) => {
		// Zoho's own "-Select-" placeholder option has a real, non-empty
		// value, so the native `required` attribute alone won't catch a
		// required dropdown left on its placeholder (e.g. Role/Dropdown2).
		// Flag it explicitly before running the normal validity check —
		// this mirrors what Zoho's own hosted form does server-side.
		form.querySelectorAll( 'select[required]' ).forEach( ( select ) => {
			select.setCustomValidity( select.value === '-Select-' ? 'Please make a selection.' : '' );
		} );

		if ( ! form.checkValidity() ) {
			e.preventDefault();
			form.reportValidity();
			return;
		}

		// Let the native POST to Zoho proceed. Just guard against a double
		// submit while the browser is busy navigating away.
		if ( submitBtn ) {
			submitBtn.disabled = true;
			submitBtn.dataset.originalLabel = submitBtn.textContent;
			submitBtn.textContent = submitBtn.dataset.sendingLabel || 'Sending…';
		}
	} );
} );
