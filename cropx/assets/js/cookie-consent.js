/**
 * Cookie Consent — IIFE
 * assets/js/cookie-consent.js
 *
 * Shows the compact Option A bar to first-time visitors. Slides up the full
 * preferences panel when "Manage Preferences" is clicked. Writes all consent
 * state as first-party cookies so server-side code can gate scripts accordingly.
 *
 * Cookie: cropx_consent
 *   'all'        user accepted all non-essential cookies
 *   'essential'  user accepted essential cookies only
 *   (not set)    no choice yet — show the banner
 *
 * Granular per-category cookies (set alongside cropx_consent):
 *   cropx_consent_analytics   '1' | '0'
 *   cropx_consent_marketing   '1' | '0'
 *   cropx_consent_functional  '1' | '0'
 *
 * All cookies expire in 365 days, SameSite=Lax, path=/.
 */
( function () {
	'use strict';

	const COOKIE  = 'cropx_consent';
	const EXPIRY  = 365; // days

	// ── Cookie helpers ─────────────────────────────────────────────────────────

	function getCookie( name ) {
		const m = document.cookie.match( '(?:^|;)\\s*' + name + '=([^;]*)' );
		return m ? decodeURIComponent( m[ 1 ] ) : null;
	}

	function setCookie( name, value, days ) {
		const expires = new Date( Date.now() + days * 864e5 ).toUTCString();
		document.cookie = name + '=' + encodeURIComponent( value )
			+ '; expires=' + expires + '; path=/; SameSite=Lax';
	}

	// ── Elements ───────────────────────────────────────────────────────────────

	const banner    = document.getElementById( 'cc-banner' );
	const prefs     = document.getElementById( 'cc-prefs' );
	if ( ! banner ) return;

	const btnAccept      = document.getElementById( 'cc-accept' );
	const btnReject      = document.getElementById( 'cc-reject' );
	const btnManage      = document.getElementById( 'cc-manage' );
	const btnClose       = document.getElementById( 'cc-prefs-close' );
	const btnPrefsSave   = document.getElementById( 'cc-prefs-save' );
	const btnPrefsReject = document.getElementById( 'cc-prefs-reject' );

	const togAnalytics  = document.getElementById( 'cc-toggle-analytics' );
	const togMarketing  = document.getElementById( 'cc-toggle-marketing' );
	const togFunctional = document.getElementById( 'cc-toggle-functional' );

	// ── Body padding — prevents fixed banner from covering footer content ────────
	// The banner and prefs panel are position:fixed, so they don't affect layout.
	// We measure whichever panel is active and push padding-bottom onto <body>
	// so the footer scrolls clear of the banner. ResizeObserver re-fires on
	// viewport resize (e.g., banner text wrapping on mobile).

	var _padRAF;
	function updateBodyPad() {
		cancelAnimationFrame( _padRAF );
		_padRAF = requestAnimationFrame( function () {
			var prefsVisible  = prefs   && ! prefs.hasAttribute( 'hidden' );
			var bannerVisible = banner  && ! banner.hasAttribute( 'hidden' );
			var active = prefsVisible ? prefs : ( bannerVisible ? banner : null );
			document.body.style.paddingBottom = active ? active.offsetHeight + 'px' : '';
		} );
	}

	var ro = new ResizeObserver( updateBodyPad );
	ro.observe( banner );
	if ( prefs ) ro.observe( prefs );

	// ── Show / hide (slide animation via CSS class) ────────────────────────────
	// Double-RAF pattern: first frame removes `hidden` (display:none → block),
	// second frame adds the visible class so the CSS transition fires.

	function show( el, cls ) {
		el.removeAttribute( 'hidden' );
		requestAnimationFrame( function () {
			requestAnimationFrame( function () {
				el.classList.add( cls );
				updateBodyPad();
			} );
		} );
	}

	function hide( el, cls ) {
		el.classList.remove( cls );
		el.addEventListener( 'transitionend', function done() {
			el.setAttribute( 'hidden', '' );
			el.removeEventListener( 'transitionend', done );
			updateBodyPad();
		}, { once: true } );
	}

	// ── Consent actions ────────────────────────────────────────────────────────

	function acceptAll() {
		setCookie( COOKIE,                     'all', EXPIRY );
		setCookie( 'cropx_consent_analytics',  '1',   EXPIRY );
		setCookie( 'cropx_consent_marketing',  '1',   EXPIRY );
		setCookie( 'cropx_consent_functional', '1',   EXPIRY );
		hide( banner, 'cc-banner--visible' );
		if ( prefs ) hide( prefs, 'cc-prefs--visible' );
	}

	function rejectAll() {
		setCookie( COOKIE,                     'essential', EXPIRY );
		setCookie( 'cropx_consent_analytics',  '0',         EXPIRY );
		setCookie( 'cropx_consent_marketing',  '0',         EXPIRY );
		setCookie( 'cropx_consent_functional', '0',         EXPIRY );
		hide( banner, 'cc-banner--visible' );
		if ( prefs ) hide( prefs, 'cc-prefs--visible' );
	}

	function savePrefs() {
		const a = togAnalytics  ? togAnalytics.checked  : false;
		const m = togMarketing  ? togMarketing.checked  : false;
		const f = togFunctional ? togFunctional.checked : false;
		setCookie( COOKIE,                     ( a && m && f ) ? 'all' : 'essential', EXPIRY );
		setCookie( 'cropx_consent_analytics',  a ? '1' : '0', EXPIRY );
		setCookie( 'cropx_consent_marketing',  m ? '1' : '0', EXPIRY );
		setCookie( 'cropx_consent_functional', f ? '1' : '0', EXPIRY );
		if ( prefs ) hide( prefs, 'cc-prefs--visible' );
	}

	function openPrefs() {
		// Pre-populate toggles from existing granular cookies (re-visit scenario)
		if ( togAnalytics )  togAnalytics.checked  = getCookie( 'cropx_consent_analytics' )  === '1';
		if ( togMarketing )  togMarketing.checked  = getCookie( 'cropx_consent_marketing' )   === '1';
		if ( togFunctional ) togFunctional.checked = getCookie( 'cropx_consent_functional' )  === '1';

		hide( banner, 'cc-banner--visible' );
		if ( prefs ) show( prefs, 'cc-prefs--visible' );
	}

	function closePrefs() {
		if ( prefs ) hide( prefs, 'cc-prefs--visible' );
		// Only re-show the banner if consent hasn't been saved yet
		if ( ! getCookie( COOKIE ) ) show( banner, 'cc-banner--visible' );
	}

	// ── Event listeners ────────────────────────────────────────────────────────

	if ( btnAccept )      btnAccept.addEventListener(      'click', acceptAll );
	if ( btnReject )      btnReject.addEventListener(      'click', rejectAll );
	if ( btnManage )      btnManage.addEventListener(      'click', openPrefs );
	if ( btnClose )       btnClose.addEventListener(       'click', closePrefs );
	if ( btnPrefsSave )   btnPrefsSave.addEventListener(   'click', savePrefs );
	if ( btnPrefsReject ) btnPrefsReject.addEventListener( 'click', rejectAll );

	// ── Init — show banner only if no consent has been recorded ───────────────
	// Short delay so the banner doesn't flash before the page finishes painting.

	if ( ! getCookie( COOKIE ) ) {
		setTimeout( function () { show( banner, 'cc-banner--visible' ); }, 400 );
	}

} )();
