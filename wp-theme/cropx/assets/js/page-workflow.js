/**
 * Page Workflow — Gutenberg sidebar panel.
 *
 * Adds a "Page Workflow" panel to the block editor's Document sidebar for
 * Pages. Provides two dropdowns: Status (the five editorial phases) and
 * Assigned To (Larissa, Lauren, Julia). Values are saved as post meta and
 * surfaced as columns in the Pages admin list view.
 *
 * No build step required — uses wp.* globals already available in the editor.
 */

( function () {

	// Gracefully support both WP 6.5 (editPost) and WP 6.6+ (editor) locations.
	var PluginDocumentSettingPanel =
		( wp.editor    && wp.editor.PluginDocumentSettingPanel ) ||
		( wp.editPost  && wp.editPost.PluginDocumentSettingPanel );

	if ( ! PluginDocumentSettingPanel ) {
		return; // Guard: editor packages not loaded yet — shouldn't happen.
	}

	var registerPlugin  = wp.plugins.registerPlugin;
	var SelectControl   = wp.components.SelectControl;
	var useSelect       = wp.data.useSelect;
	var useDispatch     = wp.data.useDispatch;
	var createElement   = wp.element.createElement;

	var STATUS_OPTIONS = [
		{ label: '— Select status —',        value: '' },
		{ label: 'I — Design Phase',          value: 'design' },
		{ label: 'II — Copy Phase',           value: 'copy' },
		{ label: 'III — Visual Polish Phase', value: 'visual-polish' },
		{ label: 'IV — Review Phase',         value: 'review' },
		{ label: 'V — Complete',              value: 'complete' },
	];

	var ASSIGNEE_OPTIONS = [
		{ label: '— Unassigned —', value: '' },
		{ label: 'Larissa',        value: 'larissa' },
		{ label: 'Lauren',         value: 'lauren' },
		{ label: 'Julia',          value: 'julia' },
	];

	function PageWorkflowPanel() {
		var meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );

		var dispatch = useDispatch( 'core/editor' );

		return createElement(
			PluginDocumentSettingPanel,
			{
				name:  'cropx-page-workflow',
				title: 'Page Workflow',
				icon:  'editor-ul',
			},
			createElement( SelectControl, {
				label:    'Status',
				value:    meta._cropx_page_status || '',
				options:  STATUS_OPTIONS,
				onChange: function ( value ) {
					dispatch.editPost( { meta: { _cropx_page_status: value } } );
				},
			} ),
			createElement( SelectControl, {
				label:    'Assigned to',
				value:    meta._cropx_page_assignee || '',
				options:  ASSIGNEE_OPTIONS,
				onChange: function ( value ) {
					dispatch.editPost( { meta: { _cropx_page_assignee: value } } );
				},
			} )
		);
	}

	wp.domReady( function () {
		registerPlugin( 'cropx-page-workflow', { render: PageWorkflowPanel } );
	} );

} )();
