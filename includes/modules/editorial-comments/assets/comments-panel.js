import { Panel, PanelBody, PanelRow } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { select, subscribe } from '@wordpress/data';
import { addFilter } from '@wordpress/hooks';
import apiFetch from '@wordpress/api-fetch';
import Comments from './components/Comments.js';

// Hook into the EF sidebar and add the comments panel.
addFilter( 'editorialFlowSidebarContent', 'efCommentsPanel', ( content ) => {
	content.push( <EF_Comments_Panel /> );
	return content;
}, 10 );

/**
 * The Root Component. Responsible for outputting the panel for editorial comments.
 */
function EF_Comments_Panel() {
	const [comments, fetchComments] = useComments();

	return (
		<Panel>
			<PanelBody title='Editorial Comments' icon='admin-comments' initialOpen={ false } >
				<PanelRow><Comments comments={comments} fetchComments={fetchComments} /></PanelRow>
			</PanelBody>
		</Panel>
	);
}

/**
 * Custom hook / cached wrapper for useState.
 * Keep comments cached locally in this closure to avoid multiple requests when remounting / changing sidebars.
 */
let cachedComments = null;
function useComments() {
	const [comments, setComments] = useState( cachedComments );
	const isNewPost = select('core/editor').isEditedPostNew();

	const fetchComments = () => {
		const postID = select('core/editor').getCurrentPostId();

		apiFetch( { path: `/editorial-flow/v1/comments/?post_id=${postID}`, method: 'GET' } ).then( newComments => {
			cachedComments = newComments;
			setComments( newComments );
		} ).catch( () => {
			const error = { error: "Unable to retrieve comments." };
			cachedComments = error;
			setComments( error );
		} );
	}

	// If it's a new post, set a listener for the first save.
	if ( isNewPost ) {
		const unsubscribe = subscribe( () => {
			if ( select('core/editor').isSavingPost() ) {
				cachedComments = [];
				setComments( [] );
				unsubscribe();
			}
		} );
	}

	// Go and fetch the comment data after the first mount, just once.
	useEffect( () => {
		if ( null === cachedComments && ! isNewPost ) {
			fetchComments();
		}
	}, [] );

	return [comments, fetchComments];
}
