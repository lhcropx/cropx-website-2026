/**
 * Contact Form — front-end interaction (view.js)
 *
 * Intercepts the form submit event, sends data to the REST endpoint
 * /wp-json/cropx/v1/contact, and shows an inline success or error message.
 * All form validation uses the native HTML5 Constraint Validation API
 * (required, type="email") — no external library needed.
 */

document.querySelectorAll( '.cf-form' ).forEach( ( form ) => {
	const statusEl    = form.querySelector( '.cf-status' );
	const submitBtn   = form.querySelector( '.cf-submit' );
	const nonce       = form.dataset.nonce   || '';
	const notifyEmail = form.dataset.notify  || '';

	form.addEventListener( 'submit', async ( e ) => {
		e.preventDefault();

		// Native HTML5 validation — browser shows field-level messages.
		if ( ! form.checkValidity() ) {
			form.reportValidity();
			return;
		}

		// Disable the submit button while the request is in flight.
		submitBtn.disabled = true;
		const originalLabel = submitBtn.textContent;
		submitBtn.textContent = submitBtn.dataset.sendingLabel || 'Sending…';

		hideStatus();

		try {
			const body = new FormData( form );
			body.append( 'notify_email', notifyEmail );

			const response = await fetch(
				`${ window.location.origin }/wp-json/cropx/v1/contact`,
				{
					method: 'POST',
					headers: {
						'X-WP-Nonce': nonce,
					},
					body,
				}
			);

			const data = await response.json();

			if ( response.ok && data.success ) {
				showStatus(
					data.message || 'Thank you — we\'ll be in touch shortly.',
					'success'
				);
				form.reset();
			} else {
				showStatus(
					data.message || 'Something went wrong. Please try again.',
					'error'
				);
			}
		} catch {
			showStatus( 'Unable to send your message. Please check your connection and try again.', 'error' );
		} finally {
			submitBtn.disabled   = false;
			submitBtn.textContent = originalLabel;
		}
	} );

	function showStatus( message, type ) {
		if ( ! statusEl ) return;
		statusEl.textContent = message;
		statusEl.className   = `cf-status cf-status--${ type }`;
		statusEl.removeAttribute( 'hidden' );
		statusEl.scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
	}

	function hideStatus() {
		if ( ! statusEl ) return;
		statusEl.setAttribute( 'hidden', '' );
		statusEl.textContent = '';
		statusEl.className   = 'cf-status';
	}
} );
