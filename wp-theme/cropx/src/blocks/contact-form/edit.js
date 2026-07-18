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
 * Contact Form — block editor UI.
 *
 * All text editing happens in the Inspector sidebar to keep the canvas
 * preview clean and unambiguous. The canvas shows the full two-column layout
 * with a static (non-interactive) form preview so the selected bg/card
 * combination is immediately visible.
 */
export default function Edit( { attributes, setAttributes } ) {
	const {
		bgColor,
		cardColor,
		eyebrow,
		heading,
		introText,
		showContactDetails,
		contactEmail,
		contactPhone,
		contactAddress,
		privacyUrl,
		termsUrl,
		submitLabel,
		notifyEmail,
	} = attributes;

	const sectionClass = `cf-section cf-section--bg-${ bgColor }`;
	const cardClass    = `cf-card cf-card--${ cardColor }`;

	const blockProps = useBlockProps( { className: sectionClass } );

	// ── Reusable static form preview ──────────────────────────────────────────
	const StaticInput  = ( { placeholder } ) => (
		<input className="cf-input" type="text" placeholder={ placeholder } readOnly tabIndex={ -1 } />
	);
	const StaticSelect = ( { placeholder } ) => (
		<select className="cf-select" disabled>
			<option>{ placeholder }</option>
		</select>
	);

	return (
		<>
			{ /* ── Inspector panels ─────────────────────────────────────────── */ }
			<InspectorControls>

				{ /* Styling */ }
				<PanelBody title={ __( 'Section Styling', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background colour', 'cropx' ) }
						value={ bgColor }
						options={ [
							{ label: __( 'Deep Blue (with topo overlay)', 'cropx' ), value: 'deep-blue' },
							{ label: __( 'White', 'cropx' ),                         value: 'white' },
							{ label: __( 'Taupe', 'cropx' ),                         value: 'taupe' },
						] }
						onChange={ ( val ) => setAttributes( { bgColor: val } ) }
					/>
					<SelectControl
						label={ __( 'Card colour', 'cropx' ) }
						value={ cardColor }
						options={ [
							{ label: __( 'White', 'cropx' ),      value: 'white' },
							{ label: __( 'Deep Blue', 'cropx' ),  value: 'deep-blue' },
						] }
						onChange={ ( val ) => setAttributes( { cardColor: val } ) }
					/>
				</PanelBody>

				{ /* Intro content */ }
				<PanelBody title={ __( 'Intro Content', 'cropx' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Eyebrow', 'cropx' ) }
						value={ eyebrow }
						onChange={ ( val ) => setAttributes( { eyebrow: val } ) }
					/>
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

				{ /* Form settings */ }
				<PanelBody title={ __( 'Form Settings', 'cropx' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Submit button label', 'cropx' ) }
						value={ submitLabel }
						onChange={ ( val ) => setAttributes( { submitLabel: val } ) }
					/>
					<TextControl
						label={ __( 'Notification email', 'cropx' ) }
						help={ __( 'Where to send form submissions. Leave blank to use the site admin email.', 'cropx' ) }
						type="email"
						value={ notifyEmail }
						placeholder={ __( 'admin@example.com', 'cropx' ) }
						onChange={ ( val ) => setAttributes( { notifyEmail: val } ) }
					/>
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

			</InspectorControls>

			{ /* ── Canvas preview ────────────────────────────────────────────── */ }
			<section { ...blockProps }>
				<div className="section-inner cf-inner">

					{ /* Intro column */ }
					<div className="cf-intro">
						{ eyebrow && (
							<span className="section-eyebrow cf-eyebrow">{ eyebrow }</span>
						) }
						<h2 className="section-heading cf-heading">{ heading }</h2>
						{ introText && (
							<div
								className="section-body cf-intro-body"
								dangerouslySetInnerHTML={ { __html:
									// Mirror wpautop(): split on double newlines → <p> tags,
									// single newlines within a paragraph → <br>.
									introText
										.split( /\n\n+/ )
										.map( p => `<p>${ p.replace( /\n/g, '<br>' ) }</p>` )
										.join( '' )
								} }
							/>
						) }

						{ /* Contact channels preview */ }
						{ showContactDetails && ( contactEmail || contactPhone || contactAddress ) && (
							<div className="cf-channels">
								{ contactEmail && (
									<div className="cf-channel">
										<div className="cf-ch-icon">
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
										</div>
										<div className="cf-ch-text">
											<span className="cf-ch-label">{ __( 'Email', 'cropx' ) }</span>
											<span className="cf-ch-value">{ contactEmail }</span>
										</div>
									</div>
								) }
								{ contactPhone && (
									<div className="cf-channel">
										<div className="cf-ch-icon">
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8a19.79 19.79 0 01-3.07-8.68A2 2 0 012.18 1h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.91 8.1a16 16 0 006 6l1.46-1.46a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 14.92z"/></svg>
										</div>
										<div className="cf-ch-text">
											<span className="cf-ch-label">{ __( 'Phone', 'cropx' ) }</span>
											<span className="cf-ch-value">{ contactPhone }</span>
										</div>
									</div>
								) }
								{ contactAddress && (
									<div className="cf-channel">
										<div className="cf-ch-icon">
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
										</div>
										<div className="cf-ch-text">
											<span className="cf-ch-label">{ __( 'Address', 'cropx' ) }</span>
											<span className="cf-ch-value">{ contactAddress }</span>
										</div>
									</div>
								) }
							</div>
						) }
					</div>

					{ /* Form card */ }
					<div className={ cardClass }>
						<div className="cf-form">
							<div className="cf-row">
								<div className="cf-field">
									<label className="cf-label">{ __( 'First Name', 'cropx' ) } <span className="cf-req">*</span></label>
									<StaticInput placeholder="Jane" />
								</div>
								<div className="cf-field">
									<label className="cf-label">{ __( 'Last Name', 'cropx' ) } <span className="cf-req">*</span></label>
									<StaticInput placeholder="Smith" />
								</div>
							</div>
							<div className="cf-row">
								<div className="cf-field">
									<label className="cf-label">{ __( 'Email', 'cropx' ) } <span className="cf-req">*</span></label>
									<StaticInput placeholder="jane@example.com" />
								</div>
								<div className="cf-field">
									<label className="cf-label">{ __( 'Country', 'cropx' ) } <span className="cf-req">*</span></label>
									<StaticSelect placeholder={ __( 'Select country…', 'cropx' ) } />
								</div>
							</div>
							<div className="cf-row">
								<div className="cf-field">
									<label className="cf-label">{ __( 'Your role', 'cropx' ) } <span className="cf-req">*</span></label>
									<StaticSelect placeholder={ __( 'I am a…', 'cropx' ) } />
								</div>
								<div className="cf-field">
									<label className="cf-label">{ __( 'Phone', 'cropx' ) }</label>
									<StaticInput placeholder="+1 (555) 000-0000" />
								</div>
							</div>
							<div className="cf-field">
								<label className="cf-label">{ __( 'How can we help?', 'cropx' ) }</label>
								<textarea className="cf-textarea" placeholder={ __( 'Tell us what you\'re looking for…', 'cropx' ) } readOnly rows={ 4 } />
							</div>
							<div className="cf-field">
								<label className="cf-label">{ __( 'How did you hear about us?', 'cropx' ) }</label>
								<StaticSelect placeholder={ __( 'Select…', 'cropx' ) } />
							</div>
							<div className="cf-consent">
								<label className="cf-checkbox-wrap">
									<input className="cf-checkbox" type="checkbox" readOnly />
									<span className="cf-checkbox-label">{ __( 'Agree to receive marketing messages', 'cropx' ) }</span>
								</label>
								<p className="cf-legal">
									{ __( 'By checking the above box, you agree to receive automated promotional marketing messages from CropX.', 'cropx' ) }
									{ ' ' }<a href={ privacyUrl }>{ __( 'Privacy Policy', 'cropx' ) }</a>
									{ ' ' }<a href={ termsUrl }>{ __( 'Terms and Conditions', 'cropx' ) }</a>
								</p>
							</div>
							<div className="cf-submit-row">
								<button className="cf-submit btn-primary" type="button">{ submitLabel }</button>
							</div>
						</div>
					</div>

				</div>
			</section>
		</>
	);
}
