import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, URLInput } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import './editor.css';

export default function Edit( { attributes, setAttributes } ) {
	const {
		loginUrl, resourcesUrl, companyUrl,
		enterpriseUrl, serviceProviderUrl, onFarmUrl,
	} = attributes;

	const blockProps = useBlockProps( { className: 'cnav-block' } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Navigation URLs', 'cropx' ) } initialOpen={ true }>
					<URLInput
						label={ __( 'Log in URL', 'cropx' ) }
						value={ loginUrl }
						onChange={ ( v ) => setAttributes( { loginUrl: v } ) }
					/>
					<URLInput
						label={ __( 'Resources URL', 'cropx' ) }
						value={ resourcesUrl }
						onChange={ ( v ) => setAttributes( { resourcesUrl: v } ) }
					/>
					<URLInput
						label={ __( 'Company URL', 'cropx' ) }
						value={ companyUrl }
						onChange={ ( v ) => setAttributes( { companyUrl: v } ) }
					/>
				</PanelBody>
				<PanelBody title={ __( 'Solutions URLs', 'cropx' ) }>
					<URLInput
						label={ __( 'Enterprise URL', 'cropx' ) }
						value={ enterpriseUrl }
						onChange={ ( v ) => setAttributes( { enterpriseUrl: v } ) }
					/>
					<URLInput
						label={ __( 'Service Providers URL', 'cropx' ) }
						value={ serviceProviderUrl }
						onChange={ ( v ) => setAttributes( { serviceProviderUrl: v } ) }
					/>
					<URLInput
						label={ __( 'On-Farm URL', 'cropx' ) }
						value={ onFarmUrl }
						onChange={ ( v ) => setAttributes( { onFarmUrl: v } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<nav { ...blockProps } aria-label={ __( 'Main navigation (editor preview)', 'cropx' ) }>
				<div className="cnav-inner">
					{ /* Wordmark shown as text — SVG URL is a PHP runtime value; see editor.css comment */ }
					<span
						style={ {
							fontWeight: 700,
							fontSize: '1.125rem',
							color: '#243565',
							letterSpacing: '-0.02em',
							flexShrink: 0,
						} }
						aria-hidden="true"
					>
						CropX
					</span>

					<ul className="cnav-links" style={ { listStyle: 'none', padding: 0 } } aria-hidden="true">
						<li className="cnav-item">
							<span className="cnav-btn">
								{ __( 'Platform', 'cropx' ) }
								<svg className="cnav-chevron" viewBox="0 0 12 12" fill="none" stroke="currentColor" strokeWidth="1.5" aria-hidden="true"><path d="M2 4l4 4 4-4" /></svg>
							</span>
						</li>
						<li className="cnav-item">
							<span className="cnav-btn">
								{ __( 'Solutions', 'cropx' ) }
								<svg className="cnav-chevron" viewBox="0 0 12 12" fill="none" stroke="currentColor" strokeWidth="1.5" aria-hidden="true"><path d="M2 4l4 4 4-4" /></svg>
							</span>
						</li>
						<li className="cnav-item"><span className="cnav-btn">{ __( 'Resources', 'cropx' ) }</span></li>
						<li className="cnav-item"><span className="cnav-btn">{ __( 'Company', 'cropx' ) }</span></li>
					</ul>

					<span className="cnav-login" aria-hidden="true">{ __( 'Log in', 'cropx' ) }</span>
				</div>
			</nav>
		</>
	);
}
