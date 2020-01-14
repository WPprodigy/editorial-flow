import { registerPlugin } from '@wordpress/plugins';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import { Fragment } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';

registerPlugin( 'editorial-flow', { render: () => {
	return (
		<Fragment>
			<PluginSidebarMoreMenuItem target='editorial-flow-sidebar' icon='image-filter'>Editorial Flow</PluginSidebarMoreMenuItem>
			<PluginSidebar name='editorial-flow-sidebar' title='Editorial Flow' icon="image-filter">
				{ applyFilters( 'editorialFlowSidebarContent', [] ) }
			</PluginSidebar>
		</Fragment>
	);
} } );
