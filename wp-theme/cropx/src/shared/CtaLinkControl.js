/**
 * CtaLinkControl — reusable "Link to" control for CTA buttons.
 *
 * Lets an editor choose whether a CTA points to a URL (the existing
 * behaviour) or downloads a file from the media library. The file option
 * never requires typing a URL by hand — a MediaUpload button picks the
 * attachment and its id/url are stored on the block's own attributes.
 *
 * Used by every block with primary/secondary CTA buttons: the hero-curved
 * family (hero-curved, hero-curved-standard, hero-curved-animated),
 * mid-page-cta, and pre-footer-cta. Each block wires its own attribute names
 * through as props — this component has no attribute-naming opinions of its
 * own, so it drops into any of them unchanged.
 */

import { __ } from '@wordpress/i18n';
import { MediaUpload, MediaUploadCheck, URLInput } from '@wordpress/block-editor';
import { BaseControl, Button, RadioControl } from '@wordpress/components';

const LINK_TYPE_OPTIONS = [
	{ label: __( 'URL', 'cropx' ), value: 'url' },
	{ label: __( 'Media file (download)', 'cropx' ), value: 'file' },
];

export default function CtaLinkControl( {
	label,
	linkType,
	onLinkTypeChange,
	url,
	onUrlChange,
	fileId,
	fileUrl,
	onFileSelect,
	onFileRemove,
} ) {
	const isFile = linkType === 'file';
	const fileName = fileUrl ? fileUrl.split( '/' ).pop() : '';

	return (
		<BaseControl label={ label } __nextHasNoMarginBottom>
			<RadioControl
				selected={ linkType || 'url' }
				options={ LINK_TYPE_OPTIONS }
				onChange={ onLinkTypeChange }
			/>
			{ isFile ? (
				<MediaUploadCheck>
					<MediaUpload
						onSelect={ onFileSelect }
						value={ fileId }
						render={ ( { open } ) => (
							<div style={ { display: 'flex', flexDirection: 'column', gap: '6px', marginTop: '8px' } }>
								{ fileName && (
									<p style={ { margin: 0, fontSize: '11px', color: '#757575', wordBreak: 'break-all' } }>
										{ fileName }
									</p>
								) }
								<Button onClick={ open } variant="secondary">
									{ fileId ? __( 'Replace file', 'cropx' ) : __( 'Select file from media library', 'cropx' ) }
								</Button>
								{ !! fileId && (
									<Button onClick={ onFileRemove } variant="link" isDestructive>
										{ __( 'Remove file', 'cropx' ) }
									</Button>
								) }
							</div>
						) }
					/>
				</MediaUploadCheck>
			) : (
				<div style={ { marginTop: '8px' } }>
					<URLInput value={ url } onChange={ onUrlChange } />
				</div>
			) }
		</BaseControl>
	);
}
