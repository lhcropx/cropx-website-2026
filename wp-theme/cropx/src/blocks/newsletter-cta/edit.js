import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl } from '@wordpress/components';
import './editor.css';

export default function Edit( { attributes, setAttributes } ) {
	const { heading, body, formShortcode, inputPlaceholder, buttonLabel, privacyText } = attributes;

	return (
		<>
			<InspectorControls>
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

			<section { ...useBlockProps( { className: 'cropx-newsletter' } ) }>
				<div className="cropx-newsletter-inner">

					<div className="cropx-newsletter-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
							<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
							<polyline points="22,6 12,13 2,6"/>
						</svg>
					</div>

					<RichText
						tagName="h2"
						className="cropx-newsletter-title"
						value={ heading }
						onChange={ ( val ) => setAttributes( { heading: val } ) }
						placeholder={ __( 'Newsletter heading…', 'cropx' ) }
					/>

					<RichText
						tagName="p"
						className="cropx-newsletter-desc"
						value={ body }
						onChange={ ( val ) => setAttributes( { body: val } ) }
						placeholder={ __( 'Newsletter description…', 'cropx' ) }
					/>

					{ formShortcode ? (
						<p className="cropx-newsletter-shortcode-note">
							{ __( '📋 Form shortcode set — renders on the front end.', 'cropx' ) }
						</p>
					) : (
						<div className="cropx-newsletter-form cropx-newsletter-form--preview">
							<div className="cropx-input-wrap">
								<span className="cropx-input-icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
										<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
										<circle cx="12" cy="7" r="4"/>
									</svg>
								</span>
								<input
									type="email"
									className="cropx-newsletter-input"
									placeholder={ inputPlaceholder }
									disabled
								/>
							</div>
							<button type="button" className="cropx-btn-subscribe" disabled>
								{ buttonLabel }
							</button>
						</div>
					) }

					<p className="cropx-newsletter-privacy">{ privacyText }</p>

				</div>
			</section>
		</>
	);
}
