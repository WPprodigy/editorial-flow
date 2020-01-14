<?php
/**
 * Class SampleTest
 *
 * @package Editorial_Flow
 */

use Editorial_Flow\Modules\REST_Comments_Controller;

class Test_REST_Comments_Controller extends WP_UnitTestCase {
	private $server;
	private $namespaced_route = '/editorial-flow/v1/comments';

	public function setUp() {
		parent::setUp();

		$this->editor      = $this->factory->user->create( [ 'role' => 'editor' ] );
		$this->contributor = $this->factory->user->create( [ 'role' => 'contributor' ] );

		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server;
		do_action( 'rest_api_init' );

		$this->response_codes = [
			'missing_params' => 400,
			'logged_out_unauthorized' => 401,
			'logged_in_unauthorized' => 403,
			'success' => 200,
		];
	}

	public function test_route_is_registered() {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( $this->namespaced_route, $routes );
	}

	public function test_endpoint_is_active() {
		$routes = $this->server->get_routes();

		$route_config = $routes[ $this->namespaced_route ];
		$this->assertTrue( count( $route_config ) === 2 );

		// One for GET and one for POST
		foreach( $route_config as $endpoint ) {
			$this->assertArrayHasKey( 'callback', $endpoint );
			$this->assertTrue( is_callable( array( $endpoint[ 'callback' ][0], $endpoint[ 'callback' ][1] ) ) );
		}
	}

	public function test_route_requires_params() {
		// No post_id param for the get request.
		$response = $this->make_GET_request();
		$this->assertEquals( $this->response_codes['missing_params'], $response->get_status() );

		// No content param for the post request.
		$response = $this->make_POST_request( [ 'id' => 1 ] );
		$this->assertEquals( $this->response_codes['missing_params'], $response->get_status() );
	}

	public function test_route_is_protected() {
		wp_set_current_user( 0 );

		$response = $this->make_GET_request( [ 'post_id' => 1 ] );
		$this->assertEquals( $this->response_codes['logged_out_unauthorized'], $response->get_status() );

		$response = $this->make_POST_request( [ 'post_id' => 1, 'content' => 'some content' ] );
		$this->assertEquals( $this->response_codes['logged_out_unauthorized'], $response->get_status() );

		// Even a logged in contributor can't request comments from a post they don't have access too.
		wp_set_current_user( $this->contributor );
		$editors_post = $this->factory->post->create( [ 'post_author' => $this->editor ] );

		$response = $this->make_GET_request( [ 'post_id' => $editors_post ] );
		$this->assertEquals( $this->response_codes['logged_in_unauthorized'], $response->get_status() );

		$response = $this->make_POST_request( [ 'post_id' => $editors_post, 'content' => 'Contributor Comment Attempt' ] );
		$this->assertEquals( $this->response_codes['logged_in_unauthorized'], $response->get_status() );
	}

	public function test_route_authentication() {
		wp_set_current_user( $this->editor );
		$editors_post = $this->factory->post->create( [ 'post_author' => $this->editor ] );

		$response = $this->make_GET_request( [ 'post_id' => $editors_post ] );
		$this->assertEquals( $this->response_codes['success'], $response->get_status() );

		$response = $this->make_POST_request( [ 'post_id' => $editors_post, 'content' => 'Editor Comment' ] );
		$this->assertEquals( $this->response_codes['success'], $response->get_status() );
	}

	public function test_route_returns_empty_set_for_new_post() {
		wp_set_current_user( $this->contributor );
		$contributors_post = $this->factory->post->create( [ 'post_author' => $this->contributor, 'post_status' => 'draft' ] );

		$response = $this->make_GET_request( [ 'post_id' => $contributors_post ] );
		$this->assertEquals( $this->response_codes['success'], $response->get_status() );
		$this->assertEquals( [], $response->get_data() );
	}

	public function test_create_comment_response() {
		wp_set_current_user( $this->contributor );
		$contributors_post = $this->factory->post->create( [ 'post_author' => $this->contributor, 'post_status' => 'draft' ] );

		$response = $this->make_POST_request( [ 'post_id' => $contributors_post, 'content' => 'Contributors first comment!' ] );
		$data = $response->get_data();
		$this->assertEquals( 'Contributors first comment!', $data['content'] );
		$this->assertEquals( 0, $data['parent'] );
		$this->assertEquals( [], $data['children'] );
	}

	public function test_get_comments_response() {
		wp_set_current_user( $this->editor );
		$contributors_post = $this->factory->post->create( [ 'post_author' => $this->contributor, 'post_status' => 'draft' ] );

		// Add two comments real quick.
		$this->make_POST_request( [ 'post_id' => $contributors_post, 'content' => 'Editor comment #1' ] );
		$this->make_POST_request( [ 'post_id' => $contributors_post, 'content' => 'Editor comment #2' ] );

		$response = $this->make_GET_request( [ 'post_id' => $contributors_post ] );
		$data = $response->get_data();
		$this->assertTrue( count( $data ) === 2 );
		$this->assertEquals( 'Editor comment #1', $data[0]['content'] );
		$this->assertEquals( 'Editor comment #2', $data[1]['content'] );
	}

	private function make_GET_request( $params = [] ) {
		$request = new WP_REST_Request( 'GET', $this->namespaced_route );
		$request->set_query_params( $params );
		return $this->server->dispatch( $request );
	}

	private function make_POST_request( $params = [] ) {
		$request = new WP_REST_Request( 'POST', $this->namespaced_route );
		$request->set_body_params( $params );
		return $this->server->dispatch( $request );
	}

	public function tearDown() {
		parent::tearDown();

		// Should clean up posts as well.
		self::delete_user( $this->editor );
		self::delete_user( $this->contributor );

		global $wp_rest_server;
		$wp_rest_server = null;
	}
}
