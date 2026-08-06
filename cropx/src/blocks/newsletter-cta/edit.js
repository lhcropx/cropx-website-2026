import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, TextareaControl } from '@wordpress/components';
import './editor.css';

export default function Edit( { attributes, setAttributes } ) {
	const { bgColor, heading, body, formShortcode, inputPlaceholder, buttonLabel, privacyText } = attributes;

	const sectionClass = `ncta-section ncta--bg-${ bgColor }`;
	const blockProps = useBlockProps( { className: sectionClass } );

	return (
		<>
			<InspectorControls>
				{/* ── Section Settings ── */}
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background colour', 'cropx' ) }
						value={ bgColor }
						options={ [
							{ label: __( 'White', 'cropx' ),                          value: 'white' },
							{ label: __( 'Taupe', 'cropx' ),                          value: 'taupe' },
							{ label: __( 'Deep Blue (animated topo overlay)', 'cropx' ), value: 'deep-blue' },
						] }
						onChange={ ( val ) => setAttributes( { bgColor: val } ) }
					/>
				</PanelBody>

				{/* ── Form ── */}
				<PanelBody title={ __( 'Form', 'cropx' ) } initialOpen={ true }>
					<TextareaControl
						label={ __( 'Form shortcode', 'cropx' ) }
						help={ __( 'Paste your HubSpot, Mailchimp, or other provider shortcode here. Leave blank to show the placeholder form.', 'cropx' ) }
						value={ formShortcode }
						onChange={ ( val ) => setAttributes( { formShortcode: val } ) }
						rows={ 3 }
					/>
					<TextControl
						label={ __( 'Input placeholder text', 'cropx' ) }
						value={ inputPlaceholder }
						onChange={ ( val ) => setAttributes( { inputPlaceholder: val } ) }
					/>
					<TextControl
						label={ __( 'Button label', 'cropx' ) }
						value={ buttonLabel }
						onChange={ ( val ) => setAttributes( { buttonLabel: val } ) }
					/>
				</PanelBody>

				{/* ── Privacy ── */}
				<PanelBody title={ __( 'Privacy', 'cropx' ) } initialOpen={ false }>
					<TextareaControl
						label={ __( 'Privacy note', 'cropx' ) }
						help={ __( 'Short text shown below the form. A link to your privacy policy page is appended automatically if one is set in Settings → Privacy.', 'cropx' ) }
						value={ privacyText }
						onChange={ ( val ) => setAttributes( { privacyText: val } ) }
						rows={ 2 }
					/>
				</PanelBody>
			</InspectorControls>

			{/* ── Canvas preview ── */}
			<section { ...blockProps }>
				<div className="ncta-inner">

					{/* Envelope icon box */}
					<div className="ncta-icon-box" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" width="24" height="24">
							<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
							<polyline points="22,6 12,13 2,6"/>
						</svg>
					</div>

					<RichText
						tagName="h2"
						className="section-heading ncta-heading"
						value={ heading }
						onChange={ ( val ) => setAttributes( { heading: val } ) }
						placeholder={ __( 'Newsletter heading…', 'cropx' ) }
					/>

					<RichText
						tagName="p"
						className="section-body ncta-desc"
						value={ body }
						onChange={ ( val ) => setAttributes( { body: val } ) }
						placeholder={ __( 'Newsletter description…', 'cropx' ) }
					/>

					{ formShortcode ? (
						<p className="ncta-shortcode-note">
							{ __( '📋 Form shortcode set — renders on the front end.', 'cropx' ) }
						</p>
					) : (
						<div className="ncta-form ncta-form--preview">
							<input
								type="email"
								className="ncta-input"
								placeholder={ inputPlaceholder }
								disabled
							/>
							<button type="button" className={ `ncta-btn-subscribe btn-primary` } disabled>
								{ buttonLabel }
							</button>
						</div>
					) }

					<p className="ncta-privacy">{ privacyText }</p>

				</div>
			</section>
		</>
	);
}
