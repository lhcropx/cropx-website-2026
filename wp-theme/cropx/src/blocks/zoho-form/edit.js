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
 * Zoho Form (Embed) — block editor UI.
 *
 * The field set (Name, Email, Role, Phone, Message, Source, marketing
 * consent) is fixed to match the Zoho form this block was built from — see
 * render.php's doc comment. Everything editable here is section chrome
 * (colors, heading) and the handful of values Zoho needs per-form
 * (the submit endpoint, an optional post-submit redirect).
 */
export default function Edit( { attributes, setAttributes } ) {
	const {
		bgColor,
		cardColor,
		showEyebrow,
		eyebrow,
		heading,
		introText,
		submitLabel,
		formActionUrl,
		redirectUrl,
		referrerName,
		privacyUrl,
		termsUrl,
	} = attributes;

	const sectionClass = `zform-section zform-section--bg-${ bgColor }`;
	const cardClass    = `zform-card zform-card--${ cardColor }`;

	const blockProps = useBlockProps( {
		className: sectionClass,
		style: bgColor === 'deep-blue'
			? { '--zform-pattern-url': `url(${ window.cropxThemeData?.themeUri ?? '' }assets/decorative/drift-pattern.svg)` }
			: undefined,
	} );

	const StaticInput = ( { placeholder } ) => (
		<input className="zform-input" type="text" placeholder={ placeholder } readOnly tabIndex={ -1 } />
	);
	const StaticSelect = ( { placeholder } ) => (
		<select className="zform-select" disabled>
			<option>{ placeholder }</option>
		</select>
	);

	return (
		<>
			<InspectorControls>

				<PanelBody title={ __( 'Section Styling', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background colour', 'cropx' ) }
						value={ bgColor }
						options={ [
							{ label: __( 'White', 'cropx' ),                         value: 'white' },
							{ label: __( 'Taupe', 'cropx' ),                         value: 'taupe' },
							{ label: __( 'Deep Blue + Topo', 'cropx' ), value: 'deep-blue' },
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

				<PanelBody title={ __( 'Header (optional)', 'cropx' ) } initialOpen={ true }>
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
						value={ introText }
						rows={ 3 }
						onChange={ ( val ) => setAttributes( { introText: val } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Zoho Connection', 'cropx' ) } initialOpen={ false }>
					<p style={ { fontSize: '12px', color: '#6E6B66', marginTop: 0 } }>
						{ __( 'From Zoho Forms: Share → Embed → HTML & CSS → Generate Code. Copy the form action URL from the downloaded index.html into the field below.', 'cropx' ) }
					</p>
					<TextControl
						label={ __( 'Form action URL', 'cropx' ) }
						help={ __( 'The Zoho endpoint this form submits to. Changing forms in Zoho later? Paste the new URL here — the field names below must still match.', 'cropx' ) }
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

				<PanelBody title={ __( 'Submit Button', 'cropx' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Button label', 'cropx' ) }
						value={ submitLabel }
						onChange={ ( val ) => setAttributes( { submitLabel: val } ) }
					/>
				</PanelBody>

			</InspectorControls>

			<section { ...blockProps }>
				<div className="section-inner zform-inner">

					{ ( ( showEyebrow && eyebrow ) || heading || introText ) && (
						<div className="zform-header">
							{ showEyebrow && eyebrow && (
								<span className="section-eyebrow zform-eyebrow">{ eyebrow }</span>
							) }
							{ heading && <h2 className="section-heading zform-heading">{ heading }</h2> }
							{ introText && <p className="section-body zform-intro">{ introText }</p> }
						</div>
					) }

					<div className={ cardClass }>
						<div className="zform-form">
							<div className="zform-row">
								<div className="zform-field">
									<label className="zform-label">{ __( 'First Name', 'cropx' ) }</label>
									<StaticInput placeholder="Jane" />
								</div>
								<div className="zform-field">
									<label className="zform-label">{ __( 'Last Name', 'cropx' ) }</label>
									<StaticInput placeholder="Smith" />
								</div>
							</div>
							<div className="zform-row">
								<div className="zform-field">
									<label className="zform-label">{ __( 'Email', 'cropx' ) } <span className="zform-req">*</span></label>
									<StaticInput placeholder="jane@example.com" />
								</div>
								<div className="zform-field">
									<label className="zform-label">{ __( 'Country', 'cropx' ) }</label>
									<StaticSelect placeholder={ __( '-Select-', 'cropx' ) } />
								</div>
							</div>
							<div className="zform-row">
								<div className="zform-field">
									<label className="zform-label">{ __( 'What is your role?', 'cropx' ) } <span className="zform-req">*</span></label>
									<StaticSelect placeholder={ __( '-Select-', 'cropx' ) } />
								</div>
								<div className="zform-field">
									<label className="zform-label">{ __( 'Phone', 'cropx' ) }</label>
									<StaticInput placeholder="+1 (555) 000-0000" />
								</div>
							</div>
							<div className="zform-field">
								<label className="zform-label">{ __( 'How can we help?', 'cropx' ) }</label>
								<textarea className="zform-textarea" readOnly rows={ 4 } />
							</div>
							<div className="zform-field">
								<label className="zform-label">{ __( 'How did you hear about us?', 'cropx' ) }</label>
								<select className="zform-select" disabled>
									<option>{ __( '-Select-', 'cropx' ) }</option>
								</select>
							</div>
							<div className="zform-consent">
								<label className="zform-checkbox-wrap">
									<input className="zform-checkbox" type="checkbox" readOnly />
									<span className="zform-checkbox-label">{ __( 'Agree to receive marketing messages', 'cropx' ) }</span>
								</label>
								<p className="zform-legal">
									{ __( 'By checking above box, you agree to receive automated promotional marketing text messages from CropX about its services…', 'cropx' ) }
								</p>
							</div>
							<div className="zform-submit-row">
								<button className="zform-submit btn-primary" type="button">{ submitLabel }</button>
							</div>
						</div>
					</div>

				</div>
			</section>
		</>
	);
}
