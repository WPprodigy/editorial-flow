<?php

namespace Editorial_Flow\Modules;

class REST_Comments_Controller extends \WP_REST_Controller {
	protected $namespace = 'editorial-flow/v1';
	protected $rest_base = 'comments';

	public function __construct( $comment_type ) {
		$this->comment_type = $comment_type;
	}

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, [
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'args'                => $this->get_comments_params(),
				'permission_callback' => array( $this, 'get_comments_permissions_check' ),
				'callback'            => array( $this, 'get_comments' ),
			),
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'args'                => $this->create_comment_params(),
				'permission_callback' => array( $this, 'create_comment_permissions_check' ),
				'callback'            => array( $this, 'create_comment' ),
			),
		] );
	}

	public function get_comments_params() {
		return [
			'post_id' => [
				'description' => __( 'Limit result set to comments assigned to specific post IDs.', 'editorial-flow' ),
				'type'        => 'integer',
				'required'    => true,
			],
			'page' => [
				'description' => __( 'Select a page of results.', 'editorial-flow' ),
				'type'        => 'integer',
				'default'     => 1,
				'required'    => false,
			]
		];
	}

	public function get_comments_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_post', $request['post_id'] ) ) {
			return new \WP_Error(
				'rest_cannot_get_comments',
				__( 'Invalid permissions. This endpoint requires authentication.', 'editorial-flow' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	public function get_comments( $request ) {
		$query         = new \WP_Comment_Query;
		$query_results = $query->query( [
			'post_id' => $request['post_id'],
			'type'    => $this->comment_type,
			'status'  => $this->comment_type,
			'order'   => 'asc',
			'number'  => 100,
			'paged'   => $request['page'],
			'no_found_rows' => 0,
			'hierarchical'  => 'threaded',
		] );

		$comments = [];
		foreach ( $query_results as $comment ) {
			$comments[] = $this->prepare_response_for_collection( $this->prepare_comment_for_response( $comment ) );
		}

		$response = rest_ensure_response( $comments );
		$response->header( 'X-WP-TotalPages', (int) $query->max_num_pages );
		return $response;
	}

	public function create_comment_params() {
		return [
			'post_id' => [
				'description' => __( 'Post ID the comment belongs too.', 'editorial-flow' ),
				'type'        => 'integer',
				'required'    => true,
			],
			'parent_id' => [
				'description' => __( 'Optional parent ID for nested comments.', 'editorial-flow' ),
				'type'        => 'integer',
				'default'     => 0,
				'required'    => false,
			],
			'content' => [
				'description' => __( 'The main comment content.', 'editorial-flow' ),
				'type'        => 'string',
				'required'    => true,
			]
		];
	}

	public function create_comment_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_post', $request['post_id'] ) ) {
			return new \WP_Error(
				'rest_cannot_create_comment',
				__( 'Invalid permissions. This endpoint requires authentication.', 'editorial-flow' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	public function create_comment( $request ) {
		$current_time = current_time( 'mysql', true );

		$prepared_comment = [
			'comment_post_ID'  => $request['post_id'],
			'comment_parent'   => $request['parent_id'],
			'comment_type'     => $this->comment_type,
			'comment_approved' => $this->comment_type, // legacy, should probably be -1.
			'comment_date'     => $current_time,
			'comment_date_gmt' => $current_time,
		];

		// User is guaranteed to be logged in at this point, per the permissions checks above.
		$user = wp_get_current_user();
		$prepared_comment['user_id']              = $user->ID;
		$prepared_comment['comment_author']       = $user->display_name;
		$prepared_comment['comment_author_email'] = $user->user_email;
		$prepared_comment['comment_author_url']   = $user->user_url;

		$prepared_comment['comment_author_IP'] = '127.0.0.1';
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) && rest_is_ip_address( $_SERVER['REMOTE_ADDR'] ) ) {
			$prepared_comment['comment_author_IP'] = $_SERVER['REMOTE_ADDR'];
		}

		$prepared_comment['comment_agent'] = '';
		if ( $request->get_header( 'user_agent' ) ) {
			$prepared_comment['comment_agent'] = $request->get_header( 'user_agent' );
		}

		// No markup support for now.
		$prepared_comment['comment_content'] = wp_kses( $request['content'], [] );

		$comment_id = wp_insert_comment( wp_filter_comment( wp_slash( $prepared_comment ) ) );

		return rest_ensure_response( $this->prepare_comment_for_response( get_comment( $comment_id ) ) );
	}

	public function prepare_comment_for_response( $comment ) {
		$response = [
			'id'          => (int) $comment->comment_ID,
			'parent'      => (int) $comment->comment_parent,
			'author_name' => $comment->comment_author,
			'date'        => mysql2date( get_option( 'date_format' ) . ' \a\t ' . get_option( 'time_format' ), $comment->comment_date ),
			'content'     => $comment->comment_content,
		];

		$response['children'] = [];
		foreach ( $comment->get_children() as $child ) {
			$response['children'][] = $this->prepare_response_for_collection( $this->prepare_comment_for_response( $child ) );
		}

		return $response;
	}
}
