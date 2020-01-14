import { TextareaControl, Button } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { select } from '@wordpress/data';

/**
 * Display a comment reply form, and process submissions.
 */
export default function CommentReply( { commentReplyData } ) {
	const { parentID, setParentID, replyText, setReplyText, fetchComments } = commentReplyData;

	const submitComment = () => {
		if ( replyText === '' ) { return; }

		const commentReplyData = {
			post_id: select('core/editor').getCurrentPostId(),
			parent_id: parentID,
			content: replyText
		};

		// Reset the form.
		setParentID(0);
		setReplyText('');

		apiFetch( { path: `/editorial-flow/v1/comments/`, method: 'POST', data: commentReplyData } ).then( response => {
			// Rehydrate with new comments.
			fetchComments();
		} ).catch( ( error ) => {
			// TODO: Revert back to previous message state and show an error message.
		} );
	}

	const itemClass  = parentID === 0 ? 'top-level-reply' : '';
	const buttonText = parentID === 0 ? 'Add New Comment' : 'Reply';
	return (
		<li className={`ef-comment-reply ${itemClass}`}>
			<TextareaControl value={ replyText } onChange={ ( content ) => setReplyText( content ) } />
			<div className='ef-comment-reply-buttons'>
				{ parentID !== 0 && <Button isTertiary='true' onClick={ () => setParentID( 0 ) }>Cancel</Button> }
				<Button isPrimary='true' onClick={ () => submitComment() }>{ buttonText }</Button>
			</div>
		</li>
	);
}
