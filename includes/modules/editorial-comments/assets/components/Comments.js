import { useState } from '@wordpress/element'
import { select } from '@wordpress/data';
import Comment from './Comment.js';
import CommentReply from './CommentReply.js';

/**
 * Output the list of comments if available, along with a top-level reply form.
 */
export default function Comments( { comments, fetchComments } ) {
	// Declaring this state a higher level so it's not lost when switching between comments.
	const [parentID, setParentID]   = useState(0);
	const [replyText, setReplyText] = useState('');

	if ( select('core/editor').isEditedPostNew() ) {
		return <div>You can add editorial comments to a post once you've saved it for the first time.</div>;
	}

	if ( comments && comments.error ) {
		return <div>{comments.error}</div>;
	}

	const commentReplyData = { parentID, setParentID, replyText, setReplyText, fetchComments };
	if ( comments ) {
		// Map over comments and render them. Will also just display the form if there are no comments yet.
		return (
			<ul id='ef-comments-list'>
				{ comments.map( comment => <Comment comment={comment} commentReplyData={commentReplyData} /> ) }
				{ commentReplyData.parentID === 0 && <CommentReply commentReplyData={commentReplyData} /> }
			</ul>
		);
	}

	return <div>Loading...</div>;
}
