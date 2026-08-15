import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
	Button,
} from '@wordpress/components';

import { iconSrc, IconPicker } from '../../shared/IconPicker';
import './editor.css';

const SEGMENT_OPTIONS = [
	{ label: __( 'General (CropX Blue)',      'cropx' ), value: 'general'          },
	{ label: __( 'Enterprise (Gold)',          'cropx' ), value: 'enterprise'       },
	{ label: __( 'Service Provider (Terra)',   'cropx' ), value: 'service-provider' },
	{ label: __( 'On-Farm (New Leaf)',         'cropx' ), value: 'on-farm'          },
];

const MIN_STATS = 2;
const MAX_STATS = 8;

const FONT_SIZE_OPTIONS = [
	{ label: __( '3rem', 'cropx' ), value: '3rem' },
	{ label: __( '4rem', 'cropx' ), value: '4rem' },
	{ label: __( '5rem (default)', 'cropx' ), value: '5rem' },
	{ label: __( '6rem', 'cropx' ), value: '6rem' },
];

const clampStatCount = ( n ) => Math.min( MAX_STATS, Math.max( MIN_STATS, n ) );

const ARROW = (
	<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
		<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
	</svg>
);

export default function Edit( { attributes, setAttributes } ) {
	const {
		eyebrow, heading, body, ctaLabel, ctaUrl, segmentAccent,
		stat1Number, stat1Descriptor, stat1FontSize, stat1Icon,
		stat2Number, stat2Descriptor, stat2FontSize, stat2Icon,
		stat3Number, stat3Descriptor, stat3FontSize, stat3Icon,
		stat4Number, stat4Descriptor, stat4FontSize, stat4Icon,
		stat5Number, stat5Descriptor, stat5FontSize, stat5Icon,
		stat6Number, stat6Descriptor, stat6FontSize, stat6Icon,
		stat7Number, stat7Descriptor, stat7FontSize, stat7Icon,
		stat8Number, stat8Descriptor, stat8FontSize, stat8Icon,
		statCount, showIcons, showBorder,
		eyebrowColor, ctaStyle, showEyebrow, showCta,
		bgColor = 'taupe',
	} = attributes;

	const blockProps = useBlockProps( {
		className:
			'sg-section' +
			( segmentAccent !== 'general' ? ` sg-segment-${ segmentAccent }` : '' ) +
			` sg-section--bg-${ bgColor }`,
	} );

	const allStats = [
		{ n: 1, number: stat1Number, descriptor: stat1Descriptor, fontSize: stat1FontSize ?? '5rem', icon: stat1Icon ?? 'chart' },
		{ n: 2, number: stat2Number, descriptor: stat2Descriptor, fontSize: stat2FontSize ?? '5rem', icon: stat2Icon ?? 'chart' },
		{ n: 3, number: stat3Number, descriptor: stat3Descriptor, fontSize: stat3FontSize ?? '5rem', icon: stat3Icon ?? 'chart' },
		{ n: 4, number: stat4Number, descriptor: stat4Descriptor, fontSize: stat4FontSize ?? '5rem', icon: stat4Icon ?? 'chart' },
		{ n: 5, number: stat5Number, descriptor: stat5Descriptor, fontSize: stat5FontSize ?? '5rem', icon: stat5Icon ?? 'chart' },
		{ n: 6, number: stat6Number, descriptor: stat6Descriptor, fontSize: stat6FontSize ?? '5rem', icon: stat6Icon ?? 'chart' },
		{ n: 7, number: stat7Number, descriptor: stat7Descriptor, fontSize: stat7FontSize ?? '5rem', icon: stat7Icon ?? 'chart' },
		{ n: 8, number: stat8Number, descriptor: stat8Descriptor, fontSize: stat8FontSize ?? '5rem', icon: stat8Icon ?? 'chart' },
	];

	// How many of the 8 fixed slots are actually shown right now (2-8).
	// Content beyond this point stays stored in its attribute — shrinking
	// and then re-growing the count brings it right back.
	const visibleCount = clampStatCount( statCount ?? 4 );
	const stats = allStats.slice( 0, visibleCount );

	const setStatCount = ( next ) => setAttributes( { statCount: clampStatCount( next ) } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Section Settings', 'cropx' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Background', 'cropx' ) }
						value={ bgColor }
						options={ [
							{ label: __( 'Taupe 50 (default)', 'cropx' ), value: 'taupe'     },
							{ label: __( 'White',               'cropx' ), value: 'white'     },
							{ label: __( 'Deep Blue + Topo',    'cropx' ), value: 'deep-blue' },
						] }
						onChange={ ( v ) => setAttributes( { bgColor: v } ) }
					/>
					<SelectControl
						label={ __( 'Segment accent', 'cropx' ) }
						help={ __( 'Tints the 6px left border on each stat card.', 'cropx' ) }
						value={ segmentAccent }
						options={ SEGMENT_OPTIONS }
						onChange={ ( v ) => setAttributes( { segmentAccent: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show eyebrow', 'cropx' ) }
						checked={ showEyebrow !== false }
						onChange={ ( v ) => setAttributes( { showEyebrow: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show CTA Link', 'cropx' ) }
						checked={ showCta !== false }
						onChange={ ( v ) => setAttributes( { showCta: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show icons', 'cropx' ) }
						help={ __( 'Adds a standard icon box above each stat number, tinted with the segment accent color above.', 'cropx' ) }
						checked={ showIcons === true }
						onChange={ ( v ) => setAttributes( { showIcons: v } ) }
					/>
					<ToggleControl
						label={ __( 'Show left border', 'cropx' ) }
						help={ __( 'The segment-accent-colored border on each stat card. When off, the card\'s left padding is removed too.', 'cropx' ) }
						checked={ showBorder !== false }
						onChange={ ( v ) => setAttributes( { showBorder: v } ) }
					/>
					<SelectControl
						label={ __( 'Eyebrow color', 'cropx' ) }
						value={ eyebrowColor ?? 'cropx-blue' }
						options={ [
							{ label: __( 'CropX Blue (default)', 'cropx' ), value: 'cropx-blue' },
							{ label: __( 'Deep Blue',            'cropx' ), value: 'deep-blue'  },
							{ label: __( 'White',                'cropx' ), value: 'white'      },
						] }
						onChange={ ( val ) => setAttributes( { eyebrowColor: val } ) }
					/>
				</PanelBody>
				<PanelBody title={ __( 'CTA Link', 'cropx' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'CTA style', 'cropx' ) }
						value={ ctaStyle ?? 'link' }
						options={ [
							{ label: __( 'Text link with arrow', 'cropx' ), value: 'link'   },
							{ label: __( 'Button',               'cropx' ), value: 'button' },
						] }
						onChange={ ( val ) => setAttributes( { ctaStyle: val } ) }
					/>
					<TextControl
						label={ __( 'CTA label', 'cropx' ) }
						value={ ctaLabel }
						onChange={ ( v ) => setAttributes( { ctaLabel: v } ) }
					/>
					<TextControl
						label={ __( 'CTA URL', 'cropx' ) }
						value={ ctaUrl }
						onChange={ ( v ) => setAttributes( { ctaUrl: v } ) }
					/>
				</PanelBody>

				{/* ── Per-stat panels — number size + icon together, one panel per stat ── */}
				{ stats.map( ( { n, fontSize, icon } ) => (
					<PanelBody
						key={ n }
						title={ `${ __( 'Stat', 'cropx' ) } ${ n }` }
						initialOpen={ false }
					>
						<SelectControl
							label={ __( 'Number size', 'cropx' ) }
							help={ __( 'Desktop size of this stat\'s large number — it scales down proportionally on narrower screens.', 'cropx' ) }
							value={ fontSize }
							options={ FONT_SIZE_OPTIONS }
							onChange={ ( v ) => setAttributes( { [ `stat${ n }FontSize` ]: v } ) }
						/>
						{ showIcons === true && (
							<div style={ { marginTop: '12px' } }>
								<p style={ { fontSize: '11px', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.04em', color: '#1e1e1e', marginBottom: '8px' } }>
									{ __( 'Icon', 'cropx' ) }
								</p>
								<IconPicker
									value={ icon }
									onChange={ ( v ) => setAttributes( { [ `stat${ n }Icon` ]: v } ) }
								/>
							</div>
						) }
						<Button
							variant="link"
							isDestructive
							disabled={ n !== visibleCount || visibleCount <= MIN_STATS }
							onClick={ () => setStatCount( visibleCount - 1 ) }
							style={ { marginTop: '12px' } }
						>
							{ __( 'Remove stat', 'cropx' ) }
						</Button>
					</PanelBody>
				) ) }

				<div style={ { padding: '8px 16px 16px' } }>
					<Button
						variant="secondary"
						style={ { width: '100%', justifyContent: 'center' } }
						disabled={ visibleCount >= MAX_STATS }
						onClick={ () => setStatCount( visibleCount + 1 ) }
					>
						{ visibleCount >= MAX_STATS
							? __( 'Maximum 8 stats reached', 'cropx' )
							: __( '+ Add stat', 'cropx' ) }
					</Button>
				</div>

			</InspectorControls>

			<section { ...blockProps }>
				<div className="sg-inner">

					<div className="sg-content">
						{ showEyebrow !== false && (
							<RichText
								tagName="span"
								className="section-eyebrow"
								placeholder={ __( 'Eyebrow…', 'cropx' ) }
								value={ eyebrow }
								onChange={ ( v ) => setAttributes( { eyebrow: v } ) }
								allowedFormats={ [] }
								style={ { color: `var(--${ eyebrowColor ?? 'cropx-blue' })` } }
							/>
						) }
						<RichText
							tagName="h2"
							className="section-heading"
							placeholder={ __( 'Section heading…', 'cropx' ) }
							value={ heading }
							onChange={ ( v ) => setAttributes( { heading: v } ) }
							allowedFormats={ [ 'core/bold', 'core/italic' ] }
						/>
						<RichText
							tagName="div"
							className="section-body"
							placeholder={ __( 'Body text…', 'cropx' ) }
							value={ body }
							onChange={ ( v ) => setAttributes( { body: v } ) }
							allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
						/>
						{ showCta !== false && ctaLabel && (
							ctaStyle === 'button'
								? <span className="sg-btn" aria-hidden="true">{ ctaLabel }</span>
								: <span className="sg-cta sg-cta-preview" aria-hidden="true">{ ctaLabel }{ ARROW }</span>
						) }
					</div>

					<div className="sg-cards">
						{ stats.map( ( { n, number, descriptor, fontSize, icon } ) => (
							<div key={ n } className={ `sg-stat-card${ showBorder === false ? ' sg-stat-card--no-border' : '' }` }>
								{ showIcons === true && (
									<div className="sg-stat-icon" aria-hidden="true">
										<img src={ iconSrc( icon ) } alt="" width="24" height="24" />
									</div>
								) }
								<RichText
									tagName="span"
									className="sg-stat-number"
									placeholder="0%"
									value={ number }
									onChange={ ( v ) => setAttributes( { [ `stat${ n }Number` ]: v } ) }
									allowedFormats={ [] }
									style={ { '--sg-num-max': parseFloat( fontSize ) || 5 } }
								/>
								<RichText
									tagName="p"
									className="sg-stat-descriptor"
									placeholder={ __( 'Stat descriptor…', 'cropx' ) }
									value={ descriptor }
									onChange={ ( v ) => setAttributes( { [ `stat${ n }Descriptor` ]: v } ) }
									allowedFormats={ [ 'core/bold', 'core/italic' ] }
								/>
							</div>
						) ) }
					</div>

				</div>
			</section>
		</>
	);
}
