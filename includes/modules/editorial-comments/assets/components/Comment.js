import { Fragment } from '@wordpress/element'
import CommentReply from './CommentReply.js';

/**
 * Output an individual comment, along with it's nested children.
 */
export default function Comment( { comment, commentReplyData } ) {
	const { parentID, setParentID } = commentReplyData;

	// Recursively add the comment's children.
	let children = null;
	if ( comment.children.length ) {
		children = <ul>{ comment.children.map( comment => <Comment comment={ comment } commentReplyData={ commentReplyData } /> ) }</ul>;
	}

	return (
		<Fragment>
			<li key={ comment.id } onClick={ () => setParentID( comment.id ) }>
				<span className="comment-author-name">{ comment.author_name }</span>
				<span>{ comment.content }</span>
				<span className="comment-date">{ comment.date }</span>
			</li>
			{ parentID === comment.id && <CommentReply commentReplyData={ commentReplyData } /> }
			{children}
		</Fragment>
	);
}
