/**
 * Zoho Form (Embed) — front-end interaction (view.js)
 *
 * The form's `target` attribute (set in render.php) points at a hidden
 * iframe, so submitting it never navigates the visible page — the browser
 * just POSTs into that iframe in the background. This replaces an earlier
 * version that let the browser navigate the whole page to Zoho: whenever
 * Zoho rejected a submission (or any client/server validation mismatch slipped
 * through), the visitor would see a flash of Zoho's own error response, then
 * get bounced back to a frozen, mid-"Sending…" snapshot of this page with no
 * way to recover short of a refresh. With a hidden-iframe target that
 * failure mode can't happen — the page never leaves.
 *
 * The tradeoff (unchanged from before, just made explicit): a cross-origin
 * response can't be read by this script, so we still don't get a real
 * success/failure signal from Zoho. Validation here is what stands between
 * a visitor and Zoho actually rejecting the submission, so it's done field
 * by field with visible inline errors (not just a native browser tooltip)
 * and cleared live as soon as each field is corrected.
 *
 * Sept 10, 2026 rewrite — geography-dependent fields: Zoho's live form
 * gates State/Province/Territory (Dropdown8) and City (SingleLine2) behind
 * field rules — confirmed directly in Zoho's own Field Rules admin panel
 * (see render.php's doc comment and PROGRESS.md), replacing an earlier,
 * incomplete version of this file that only handled US-only State+City
 * based on a since-corrected automated sweep:
 *   • State/Province/Territory shows + requires whenever Country is one of
 *     United States, Canada, Australia, or Mexico — with a DIFFERENT option
 *     list per country, rebuilt on the fly from the `data-state-options`
 *     JSON render.php put on the field (see rebuildStateOptions() below)
 *     rather than four option lists hardcoded here.
 *   • City shows + requires only for United States specifically (narrower
 *     than State).
 * (No County field: Zoho's admin panel also lists a "Show California
 * Counties" rule, but its condition value doesn't match the State field's
 * real value and can never fire on the live form — see render.php's doc
 * comment for how this was confirmed, straight out of the live form's own
 * zf_rule.ruleObjs.)
 * Because `required` on both fields now changes at runtime, the
 * required-field list can't be captured once at load — getRequiredEls()
 * re-queries `[required]` fresh every time it's needed instead of relying
 * on a stale cached array.
 *
 * Also wires up the shared scroll-reveal animation (see
 * src/shared/scrollReveal.js) for the header + form card.
 */

import { initScrollReveal } from '../../shared/scrollReveal';

initScrollReveal( '.zform-section' );

document.querySelectorAll( '.zform-form' ).forEach( ( form ) => {
	const submitBtn      = form.querySelector( '.zform-submit' );
	const statusEl       = form.querySelector( '.zform-status' );
	const countryField   = form.querySelector( '.zform-country-select' );
	const stateCityRow   = form.querySelector( '.zform-row--state-city' );
	const stateField     = form.querySelector( '.zform-state-select' );
	const stateFieldWrap = form.querySelector( '.zform-field--state' );
	const cityFieldWrap  = form.querySelector( '.zform-field--city' );
	const cityField      = cityFieldWrap ? cityFieldWrap.querySelector( 'input, select' ) : null;
	const geoFields       = [ stateField, cityField ].filter( Boolean );

	// Per-country State/Province/Territory option lists, read once from the
	// JSON render.php embedded on the field itself (see
	// cropx_zoho_state_options_by_country() in inc/helpers.php) — this is
	// the single source of truth for which countries get a State field at
	// all, so an empty/missing blob just means State never shows, which
	// fails safe.
	let stateOptionsByCountry = {};
	if ( stateField && stateField.dataset.stateOptions ) {
		try {
			stateOptionsByCountry = JSON.parse( stateField.dataset.stateOptions );
		} catch ( err ) {
			stateOptionsByCountry = {};
		}
	}

	const getRequiredEls = () => Array.from( form.querySelectorAll( '[required]' ) );

	// Rebuilds State/Province/Territory's <option> list for the given
	// country. Zoho doesn't just show/hide this field per country — the
	// options themselves are different every time (US states vs. Canadian
	// provinces vs. Australian states vs. Mexican states), so the dropdown
	// has to be repopulated, not just revealed. Keeps the current value
	// selected if it still exists in the new list (covers a visitor
	// bouncing back and forth between, say, two states of the same
	// country); otherwise resets to the placeholder so a stale value from a
	// different country's list can't silently ride along.
	function rebuildStateOptions( country ) {
		if ( ! stateField ) return;
		const list         = stateOptionsByCountry[ country ] || [];
		const currentValue = stateField.value;

		stateField.innerHTML = '';

		const placeholder = document.createElement( 'option' );
		placeholder.value = '-Select-';
		placeholder.textContent = '-Select-';
		placeholder.selected = true;
		stateField.appendChild( placeholder );

		list.forEach( ( optionLabel ) => {
			const opt = document.createElement( 'option' );
			opt.value = optionLabel;
			opt.textContent = optionLabel;
			stateField.appendChild( opt );
		} );

		stateField.value = list.includes( currentValue ) ? currentValue : '-Select-';
	}

	function toggleGeoField( wrap, field, show ) {
		if ( ! wrap || ! field ) return;
		wrap.hidden = ! show;
		field.required = show;
		if ( ! show ) {
			field.value = '';
			clearFieldError( field );
		}
	}

	// Show + require State/Province/Territory (with the right per-country
	// option list) and City, matching Zoho's own field rules for this form.
	function syncGeoFields() {
		if ( ! countryField ) return;
		const country    = countryField.value;
		const showState  = Object.prototype.hasOwnProperty.call( stateOptionsByCountry, country );
		const showCity   = country === 'United States';

		if ( showState ) {
			rebuildStateOptions( country );
		}

		toggleGeoField( stateFieldWrap, stateField, showState );
		toggleGeoField( cityFieldWrap, cityField, showCity );

		if ( stateCityRow ) {
			stateCityRow.hidden = ! ( showState || showCity );
			stateCityRow.classList.toggle( 'zform-row--single', showState && ! showCity );
		}

		if ( ! form.querySelector( '.has-error' ) ) {
			hideStatus();
		}
	}

	if ( countryField ) {
		countryField.addEventListener( 'change', syncGeoFields );
		syncGeoFields();
	}

	// Live-clear a field's error as soon as it becomes valid, so a visitor
	// fixing one field doesn't have to hit Submit again just to find out.
	// Attached to every field that's required at load PLUS the geography
	// fields (which may become required later, after a Country change) —
	// attaching this listener doesn't depend on a field's required state,
	// since getFieldError() below only flags fields that are currently
	// required.
	const listenerEls = new Set( [ ...getRequiredEls(), ...geoFields ] );
	listenerEls.forEach( ( field ) => {
		const eventName = field.tagName === 'SELECT' ? 'change' : 'input';
		field.addEventListener( eventName, () => {
			if ( ! getFieldError( field ) ) {
				clearFieldError( field );
			}
			// Once every flagged field has been corrected, the summary banner
			// pointing at them is stale — drop it instead of leaving a red
			// "please fill in the highlighted field" message with nothing
			// actually highlighted anymore.
			if ( ! form.querySelector( '.has-error' ) ) {
				hideStatus();
			}
		} );
	} );

	form.addEventListener( 'submit', ( e ) => {
		const requiredEls   = getRequiredEls();
		const invalidFields = requiredEls.filter( ( field ) => !! getFieldError( field ) );

		if ( invalidFields.length > 0 ) {
			e.preventDefault();
			requiredEls.forEach( clearFieldError );
			invalidFields.forEach( ( field ) => setFieldError( field, getFieldError( field ) ) );
			showStatus( 'Please fill in the highlighted field' + ( invalidFields.length > 1 ? 's' : '' ) + ' above.', 'error' );
			invalidFields[ 0 ].focus();
			invalidFields[ 0 ].scrollIntoView( { behavior: 'smooth', block: 'center' } );
			return;
		}

		// Validation passed — clear any stale error state and let the native
		// submission proceed into the hidden iframe (no preventDefault here).
		requiredEls.forEach( clearFieldError );
		hideStatus();

		if ( submitBtn ) {
			submitBtn.disabled = true;
			submitBtn.dataset.originalLabel = submitBtn.textContent;
			submitBtn.textContent = submitBtn.dataset.sendingLabel || 'Sending…';
		}

		// The POST into the hidden iframe fires synchronously on submit; this
		// delay is purely to give the "Sending…" state a moment to register
		// before we optimistically treat the submission as complete — we have
		// no way to confirm Zoho's side from here (see doc comment above).
		setTimeout( () => {
			const redirectInput = form.querySelector( 'input[name="zf_redirect_url"]' );
			const redirectUrl   = redirectInput ? redirectInput.value.trim() : '';

			if ( redirectUrl ) {
				window.location.href = redirectUrl;
				return;
			}

			showStatus( form.dataset.successMessage || "Thanks — we've received your message.", 'success' );
			form.reset();

			if ( submitBtn ) {
				submitBtn.disabled = false;
				submitBtn.textContent = submitBtn.dataset.originalLabel || submitBtn.textContent;
			}
		}, 900 );
	} );

	function getFieldError( field ) {
		if ( field.tagName === 'SELECT' ) {
			return ( field.value === '' || field.value === '-Select-' )
				? 'Please make a selection.'
				: '';
		}
		if ( field.value.trim() === '' ) {
			return 'This field is required.';
		}
		if ( field.type === 'email' && ! field.checkValidity() ) {
			return 'Please enter a valid email address.';
		}
		return '';
	}

	function setFieldError( field, message ) {
		const wrapper = field.closest( '.zform-field' );
		if ( ! wrapper ) return;
		wrapper.classList.add( 'has-error' );

		let errorEl = wrapper.querySelector( '.zform-field-error' );
		if ( ! errorEl ) {
			errorEl = document.createElement( 'span' );
			errorEl.className = 'zform-field-error';
			wrapper.appendChild( errorEl );
		}
		errorEl.textContent = message;

		const errorId = field.id ? `${ field.id }-error` : '';
		if ( errorId ) {
			errorEl.id = errorId;
			field.setAttribute( 'aria-describedby', errorId );
		}
		field.setAttribute( 'aria-invalid', 'true' );
	}

	function clearFieldError( field ) {
		const wrapper = field.closest( '.zform-field' );
		if ( ! wrapper ) return;
		wrapper.classList.remove( 'has-error' );
		const errorEl = wrapper.querySelector( '.zform-field-error' );
		if ( errorEl ) errorEl.remove();
		field.removeAttribute( 'aria-invalid' );
		field.removeAttribute( 'aria-describedby' );
	}

	function showStatus( message, type ) {
		if ( ! statusEl ) return;
		statusEl.textContent = message;
		statusEl.className   = `zform-status zform-status--${ type }`;
		statusEl.removeAttribute( 'hidden' );
		statusEl.scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
	}

	function hideStatus() {
		if ( ! statusEl ) return;
		statusEl.setAttribute( 'hidden', '' );
		statusEl.textContent = '';
		statusEl.className   = 'zform-status';
	}
} );
