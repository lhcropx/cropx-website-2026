/**
 * 2-Column with Zoho Contact Form — front-end interaction (view.js)
 *
 * Same approach as cropx/zoho-form: this form POSTs directly to Zoho's own
 * endpoint (a different origin), so we can't intercept the submit with
 * fetch() and read back a real success/failure the way cropx/contact-form
 * does against our own REST API. We only run the same required-field /
 * email checks Zoho's own hosted form would run, then get out of the way
 * and let the native form submission happen.
 */

document.querySelectorAll( '.zcf-form' ).forEach( ( form ) => {
	const submitBtn = form.querySelector( '.zcf-submit' );

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

		if ( submitBtn ) {
			submitBtn.disabled = true;
			submitBtn.dataset.originalLabel = submitBtn.textContent;
			submitBtn.textContent = submitBtn.dataset.sendingLabel || 'Sending…';
		}
	} );
} );
