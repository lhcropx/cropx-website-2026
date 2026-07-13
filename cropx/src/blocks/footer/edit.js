import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

import './editor.css';

const themeUri = window.cropxThemeData?.themeUri ?? '';

export default function Edit( { attributes, setAttributes } ) {
	const {
		tagline,
		copyrightYear,
		linkedinUrl,
		xUrl,
		youtubeUrl,
		facebookUrl,
		instagramUrl,
		showLocations,
		locationsText,
	} = attributes;

	const blockProps = useBlockProps( { className: 'footer ftr-c' } );

	const year = copyrightYear || new Date().getFullYear();
	const logoSrc = themeUri + 'assets/logos/cropx-wordmark.svg';

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Brand', 'cropx' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Tagline', 'cropx' ) }
						value={ tagline }
						onChange={ ( v ) => setAttributes( { tagline: v } ) }
					/>
					<TextControl
						label={ __( 'Copyright year', 'cropx' ) }
						value={ copyrightYear }
						placeholder={ String( new Date().getFullYear() ) }
						help={ __( 'Leave blank to use the current year automatically.', 'cropx' ) }
						onChange={ ( v ) => setAttributes( { copyrightYear: v } ) }
					/>
				</PanelBody>
				<PanelBody title={ __( 'Locations', 'cropx' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Show locations', 'cropx' ) }
						checked={ showLocations !== false }
						onChange={ ( v ) => setAttributes( { showLocations: v } ) }
					/>
					{ showLocations !== false && (
						<TextControl
							label={ __( 'Locations text', 'cropx' ) }
							help={ __( 'Edit city names as a single line, separated by your preferred character.', 'cropx' ) }
							value={ locationsText ?? 'Anaheim · Melbourne · Wellington · Haren · Netanya' }
							onChange={ ( v ) => setAttributes( { locationsText: v } ) }
						/>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Social links', 'cropx' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'LinkedIn URL', 'cropx' ) }
						value={ linkedinUrl }
						onChange={ ( v ) => setAttributes( { linkedinUrl: v } ) }
					/>
					<TextControl
						label={ __( 'X (Twitter) URL', 'cropx' ) }
						value={ xUrl }
						onChange={ ( v ) => setAttributes( { xUrl: v } ) }
					/>
					<TextControl
						label={ __( 'YouTube URL', 'cropx' ) }
						value={ youtubeUrl }
						onChange={ ( v ) => setAttributes( { youtubeUrl: v } ) }
					/>
					<TextControl
						label={ __( 'Facebook URL', 'cropx' ) }
						value={ facebookUrl }
						onChange={ ( v ) => setAttributes( { facebookUrl: v } ) }
					/>
					<TextControl
						label={ __( 'Instagram URL', 'cropx' ) }
						value={ instagramUrl }
						onChange={ ( v ) => setAttributes( { instagramUrl: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<footer { ...blockProps }>
				<div className="footer-main">
					<div className="footer-brand">
						{ logoSrc
							? <img src={ logoSrc } alt="CropX" className="footer-logo" />
							: <span className="footer-logo-placeholder">CropX</span>
						}
						<p className="footer-tagline">{ tagline }</p>
						<div className="footer-contact">
							<a href="mailto:sales@cropx.com">sales@cropx.com</a>
							{ showLocations !== false && (
						<a href="#">{ locationsText ?? 'Anaheim · Melbourne · Wellington · Haren · Netanya' }</a>
					) }
						</div>
						<div className="footer-social">
							<a href={ linkedinUrl } aria-label="LinkedIn">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2zm2-5a2 2 0 110 4 2 2 0 010-4z"/></svg>
							</a>
							<a href={ xUrl } aria-label="X">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
							</a>
							<a href={ youtubeUrl } aria-label="YouTube">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23 7s-.3-1.9-1.1-2.7c-1.1-1.1-2.3-1.1-2.8-1.2C16.6 3 12 3 12 3s-4.6 0-7.1.1c-.6.1-1.8.1-2.8 1.2C1.3 5.1 1 7 1 7S.7 9.1.7 11.3v2c0 2.1.3 4.3.3 4.3s.3 1.9 1.1 2.7c1.1 1.1 2.5 1 3.1 1.1C7.2 21.6 12 21.6 12 21.6s4.6 0 7.1-.2c.6-.1 1.8-.1 2.8-1.2.8-.8 1.1-2.7 1.1-2.7s.3-2.1.3-4.3v-2C23.3 9.1 23 7 23 7zM9.7 15.5V8.4l7.6 3.6-7.6 3.5z"/></svg>
							</a>
							<a href={ facebookUrl } aria-label="Facebook">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
							</a>
							<a href={ instagramUrl } aria-label="Instagram">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2c-2.716 0-3.056.013-4.123.06-1.064.049-1.791.218-2.427.465a4.902 4.902 0 00-1.772 1.153A4.902 4.902 0 002.525 5.45c-.247.636-.416 1.363-.465 2.427C2.013 8.944 2 9.284 2 12c0 2.716.013 3.056.06 4.123.049 1.064.218 1.791.465 2.427a4.902 4.902 0 001.153 1.772 4.902 4.902 0 001.772 1.153c.636.247 1.363.416 2.427.465C8.944 21.987 9.284 22 12 22c2.716 0 3.056-.013 4.123-.06 1.064-.049 1.791-.218 2.427-.465a4.902 4.902 0 001.772-1.153 4.902 4.902 0 001.153-1.772c.247-.636.416-1.363.465-2.427.047-1.067.06-1.407.06-4.123 0-2.716-.013-3.056-.06-4.123-.049-1.064-.218-1.791-.465-2.427a4.902 4.902 0 00-1.153-1.772A4.902 4.902 0 0018.55 2.525c-.636-.247-1.363-.416-2.427-.465C15.056 2.013 14.716 2 12 2zm0 2c2.67 0 2.986.01 4.04.058.976.045 1.505.207 1.858.344.466.182.8.398 1.15.748.35.35.566.683.748 1.15.137.353.3.882.344 1.857.048 1.055.058 1.37.058 4.041 0 2.67-.01 2.986-.058 4.04-.045.976-.207 1.505-.344 1.858a3.097 3.097 0 01-.748 1.15 3.098 3.098 0 01-1.15.748c-.353.137-.882.3-1.857.344-1.054.048-1.37.058-4.041.058-2.67 0-2.987-.01-4.04-.058-.976-.045-1.505-.207-1.858-.344a3.098 3.098 0 01-1.15-.748 3.098 3.098 0 01-.748-1.15c-.137-.353-.3-.882-.344-1.857-.048-1.055-.058-1.37-.058-4.041 0-2.67.01-2.986.058-4.04.045-.976.207-1.505.344-1.858.182-.466.398-.8.748-1.15.35-.35.683-.566 1.15-.748.353-.137.882-.3 1.857-.344C9.014 4.01 9.33 4 12 4zm0 3a5 5 0 100 10A5 5 0 0012 7zm0 2a3 3 0 110 6 3 3 0 010-6zm5.25-3.5a1.25 1.25 0 100 2.5 1.25 1.25 0 000-2.5z"/></svg>
							</a>
						</div>
					</div>

					<div className="footer-nav-group">
						<p className="footer-nav-heading">{ __( 'Platform', 'cropx' ) }</p>
						<a href="#">Soil Sensing</a>
						<a href="#">Irrigation Planning</a>
						<a href="#">Crop Monitoring</a>
						<a href="#">Sustainability Reporting</a>
						<a href="#">Integrations</a>
					</div>
					<div className="footer-nav-group">
						<p className="footer-nav-heading">{ __( 'Solutions', 'cropx' ) }</p>
						<a href="#">Enterprise</a>
						<a href="#">Service Providers</a>
						<a href="#">On-Farm Solutions</a>
					</div>
					<div className="footer-nav-group">
						<p className="footer-nav-heading">{ __( 'Company', 'cropx' ) }</p>
						<a href="#">About CropX</a>
						<a href="#">Careers</a>
						<a href="#">News</a>
						<a href="#">Partners</a>
						<a href="#">Contact</a>
					</div>
				</div>

				<div className="footer-legal">
					<div className="footer-legal-inner">
						<p className="footer-copyright">
							© { year } CropX Technologies Ltd. All rights reserved.
						</p>
						<div className="footer-legal-links">
							<a href="#">Privacy Policy</a>
							<a href="#">Terms of Use</a>
							<a href="#">Cookie Settings</a>
						</div>
					</div>
				</div>
			</footer>
		</>
	);
}
