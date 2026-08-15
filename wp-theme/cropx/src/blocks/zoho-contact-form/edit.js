import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import './editor.css';

/**
 * 2-Column with Zoho Contact Form — block editor UI.
 *
 * Layout/Inspector structure mirrors cropx/contact-form (Section Styling,
 * Intro Content, Contact Details panels) with two additions specific to
 * the Zoho-backed form: a "Zoho Connection" panel (submit endpoint +
 * optional redirect) and a "Legal Links" panel, both carried over from
 * cropx/zoho-form. The field set in the canvas preview matches the Zoho
 * form this block was built from — see render.php.
 */
export default function Edit( { attributes, setAttributes } ) {
	const {
		bgColor,
		cardColor,
		showEyebrow,
		eyebrow,
		heading,
		introText,
		showContactDetails,
		contactEmail,
		contactPhone,
		contactAddress,
		submitLabel,
		formActionUrl,
		redirectUrl,
		referrerName,
		privacyUrl,
		termsUrl,
	} = attributes;

	const sectionClass = `zcf-section zcf-section--bg-${ bgColor }`;
	const cardClass    = `zcf-card zcf-card--${ cardColor }`;

	const blockProps = useBlockProps( {
		className: sectionClass,
		style: bgColor === 'deep-blue'
			? { '--zcf-pattern-url': `url(${ window.cropxThemeData?.themeUri ?? '' }assets/decorative/drift-pattern.svg)` }
			: undefined,
	} );

	const StaticInput = ( { placeholder } ) => (
		<input className="zcf-input" type="text" placeholder={ placeholder } readOnly tabIndex={ -1 } />
	);
	const StaticSelect = ( { placeholder } ) => (
		<select className="zcf-select" disabled>
			<option>{ placeholder }</option>
		</select>
	);

	return (
		<>
			<InspectorControls>

				{ /* Styling */ }
				<PanelBody title={ __( 'Section Styling', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background colour', 'cropx' ) }
						value={ bgColor }
						options={ [
							{ label: __( 'Deep Blue + Topo', 'cropx' ), value: 'deep-blue' },
							{ label: __( 'White', 'cropx' ),                         value: 'white' },
							{ label: __( 'Taupe', 'cropx' ),                         value: 'taupe' },
						] }
						onChange={ ( val ) => setAttributes( { bgColor: val } ) }
					/>
					<SelectControl
						label={ __( 'Card colour', 'cropx' ) }
						value={ cardColor }
						options={ [
							{ label: __( 'White', 'cropx' ),     value: 'white' },
							{ label: __( 'Deep Blue', 'cropx' ), value: 'deep-blue' },
						] }
						onChange={ ( val ) => setAttributes( { cardColor: val } ) }
					/>
				</PanelBody>

				{ /* Intro content */ }
				<PanelBody title={ __( 'Intro Content', 'cropx' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Show eyebrow', 'cropx' ) }
						checked={ showEyebrow }
						onChange={ ( val ) => setAttributes( { showEyebrow: val } ) }
					/>
					{ showEyebrow && (
						<TextControl
							label={ __( 'Eyebrow', 'cropx' ) }
							value={ eyebrow }
							onChange={ ( val ) => setAttributes( { eyebrow: val } ) }
						/>
					) }
					<TextControl
						label={ __( 'Heading', 'cropx' ) }
						value={ heading }
						onChange={ ( val ) => setAttributes( { heading: val } ) }
					/>
					<TextareaControl
						label={ __( 'Intro text', 'cropx' ) }
						help={ __( 'Supports basic HTML: <a>, <strong>, <em>, <br>.', 'cropx' ) }
						value={ introText }
						rows={ 5 }
						onChange={ ( val ) => setAttributes( { introText: val } ) }
					/>
				</PanelBody>

				{ /* Contact channels */ }
				<PanelBody title={ __( 'Contact Details (optional)', 'cropx' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Show contact details', 'cropx' ) }
						checked={ showContactDetails }
						onChange={ ( val ) => setAttributes( { showContactDetails: val } ) }
					/>
					{ showContactDetails && (
						<>
							<TextControl
								label={ __( 'Email', 'cropx' ) }
								type="email"
								value={ contactEmail }
								placeholder="info@cropx.com"
								onChange={ ( val ) => setAttributes( { contactEmail: val } ) }
							/>
							<TextControl
								label={ __( 'Phone', 'cropx' ) }
								type="tel"
								value={ contactPhone }
								placeholder="+1 (800) 000-0000"
								onChange={ ( val ) => setAttributes( { contactPhone: val } ) }
							/>
							<TextareaControl
								label={ __( 'Address', 'cropx' ) }
								help={ __( 'Each line renders on its own line.', 'cropx' ) }
								value={ contactAddress }
								rows={ 3 }
								onChange={ ( val ) => setAttributes( { contactAddress: val } ) }
							/>
						</>
					) }
				</PanelBody>

				{ /* Zoho connection */ }
				<PanelBody title={ __( 'Zoho Connection', 'cropx' ) } initialOpen={ false }>
					<p style={ { fontSize: '12px', color: '#6E6B66', marginTop: 0 } }>
						{ __( 'From Zoho Forms: Share → Embed → HTML & CSS → Generate Code. Copy the form action URL from the downloaded index.html into the field below.', 'cropx' ) }
					</p>
					<TextControl
						label={ __( 'Form action URL', 'cropx' ) }
						help={ __( 'The Zoho endpoint this form submits to. Changing forms in Zoho later? Paste the new URL here — the field names in render.php must still match.', 'cropx' ) }
						value={ formActionUrl }
						onChange={ ( val ) => setAttributes( { formActionUrl: val } ) }
					/>
					<TextControl
						label={ __( 'Redirect URL after submit', 'cropx' ) }
						help={ __( "Optional. Send visitors to a CropX thank-you page after a successful submission. Leave blank to land on Zoho's own default response page.", 'cropx' ) }
						value={ redirectUrl }
						onChange={ ( val ) => setAttributes( { redirectUrl: val } ) }
					/>
					<TextControl
						label={ __( 'Referrer name (optional)', 'cropx' ) }
						help={ __( 'Zoho referral-tracking field — usually left blank.', 'cropx' ) }
						value={ referrerName }
						onChange={ ( val ) => setAttributes( { referrerName: val } ) }
					/>
				</PanelBody>

				{ /* Legal links */ }
				<PanelBody title={ __( 'Legal Links', 'cropx' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Privacy Policy URL', 'cropx' ) }
						type="url"
						value={ privacyUrl }
						onChange={ ( val ) => setAttributes( { privacyUrl: val } ) }
					/>
					<TextControl
						label={ __( 'Terms & Conditions URL', 'cropx' ) }
						type="url"
						value={ termsUrl }
						onChange={ ( val ) => setAttributes( { termsUrl: val } ) }
					/>
				</PanelBody>

				{ /* Submit button */ }
				<PanelBody title={ __( 'Submit Button', 'cropx' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Button label', 'cropx' ) }
						value={ submitLabel }
						onChange={ ( val ) => setAttributes( { submitLabel: val } ) }
					/>
				</PanelBody>

			</InspectorControls>

			{ /* ── Canvas preview ────────────────────────────────────────────── */ }
			<section { ...blockProps }>
				<div className="section-inner zcf-inner">

					{ /* Intro column */ }
					<div className="zcf-intro">
						{ showEyebrow && eyebrow && (
							<span className="section-eyebrow zcf-eyebrow">{ eyebrow }</span>
						) }
						<h2 className="section-heading zcf-heading">{ heading }</h2>
						{ introText && (
							<div
								className="section-body zcf-intro-body"
								dangerouslySetInnerHTML={ { __html:
									introText
										.split( /\n\n+/ )
										.map( p => `<p>${ p.replace( /\n/g, '<br>' ) }</p>` )
										.join( '' )
								} }
							/>
						) }

						{ showContactDetails && ( contactEmail || contactPhone || contactAddress ) && (
							<div className="zcf-channels">
								{ contactEmail && (
									<div className="zcf-channel">
										<div className="zcf-ch-icon">
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
										</div>
										<div className="zcf-ch-text">
											<span className="zcf-ch-label">{ __( 'Email', 'cropx' ) }</span>
											<span className="zcf-ch-value">{ contactEmail }</span>
										</div>
									</div>
								) }
								{ contactPhone && (
									<div className="zcf-channel">
										<div className="zcf-ch-icon">
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8a19.79 19.79 0 01-3.07-8.68A2 2 0 012.18 1h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.91 8.1a16 16 0 006 6l1.46-1.46a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 14.92z"/></svg>
										</div>
										<div className="zcf-ch-text">
											<span className="zcf-ch-label">{ __( 'Phone', 'cropx' ) }</span>
											<span className="zcf-ch-value">{ contactPhone }</span>
										</div>
									</div>
								) }
								{ contactAddress && (
									<div className="zcf-channel">
										<div className="zcf-ch-icon">
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
										</div>
										<div className="zcf-ch-text">
											<span className="zcf-ch-label">{ __( 'Address', 'cropx' ) }</span>
											<span className="zcf-ch-value">{ contactAddress }</span>
										</div>
									</div>
								) }
							</div>
						) }
					</div>

					{ /* Form card */ }
					<div className={ cardClass }>
						<div className="zcf-form">
							<div className="zcf-row">
								<div className="zcf-field">
									<label className="zcf-label">{ __( 'First Name', 'cropx' ) }</label>
									<StaticInput placeholder="Jane" />
								</div>
								<div className="zcf-field">
									<label className="zcf-label">{ __( 'Last Name', 'cropx' ) }</label>
									<StaticInput placeholder="Smith" />
								</div>
							</div>
							<div className="zcf-row">
								<div className="zcf-field">
									<label className="zcf-label">{ __( 'Email', 'cropx' ) } <span className="zcf-req">*</span></label>
									<StaticInput placeholder="jane@example.com" />
								</div>
								<div className="zcf-field">
									<label className="zcf-label">{ __( 'Country', 'cropx' ) }</label>
									<StaticSelect placeholder={ __( '-Select-', 'cropx' ) } />
								</div>
							</div>
							<div className="zcf-row">
								<div className="zcf-field">
									<label className="zcf-label">{ __( 'What is your role?', 'cropx' ) } <span className="zcf-req">*</span></label>
									<StaticSelect placeholder={ __( '-Select-', 'cropx' ) } />
								</div>
								<div className="zcf-field">
									<label className="zcf-label">{ __( 'Phone', 'cropx' ) }</label>
									<StaticInput placeholder="+1 (555) 000-0000" />
								</div>
							</div>
							<div className="zcf-field">
								<label className="zcf-label">{ __( 'How can we help?', 'cropx' ) }</label>
								<textarea className="zcf-textarea" readOnly rows={ 4 } />
							</div>
							<div className="zcf-field">
								<label className="zcf-label">{ __( 'How did you hear about us?', 'cropx' ) }</label>
								<select className="zcf-select" disabled>
									<option>{ __( '-Select-', 'cropx' ) }</option>
								</select>
							</div>
							<div className="zcf-consent">
								<label className="zcf-checkbox-wrap">
									<input className="zcf-checkbox" type="checkbox" readOnly />
									<span className="zcf-checkbox-label">{ __( 'Agree to receive marketing messages', 'cropx' ) }</span>
								</label>
								<p className="zcf-legal">
									{ __( 'By checking above box, you agree to receive automated promotional marketing text messages from CropX about its services…', 'cropx' ) }
									{ ' ' }<a href={ privacyUrl }>{ __( 'Review our Privacy Policy', 'cropx' ) }</a>
									{ termsUrl && <>{ ' · ' }<a href={ termsUrl }>{ __( 'Review our Terms and Conditions', 'cropx' ) }</a></> }
								</p>
							</div>
							<div className="zcf-submit-row">
								<button className="zcf-submit btn-primary" type="button">{ submitLabel }</button>
							</div>
						</div>
					</div>

				</div>
			</section>
		</>
	);
}
