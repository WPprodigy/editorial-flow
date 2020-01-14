<?php

namespace Editorial_Flow\Modules;

class Editorial_Comments extends Module {
	public const comment_type = 'editorial-comment';

	public function __construct() {
		$this->name = 'editorial_comments';

		$this->information = [
			'title' => __( 'Editorial Comments', 'editorial-flow' ),
			'description' => __( 'Share internal notes with your team while editing posts.', 'editorial-flow' ),
		];
	}

	protected function init() {
		require_once dirname( __FILE__ ) . '/rest-comments-controller.php';

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'rest_api_init', array( $this, 'add_rest_api_endpoints' ) );
	}

	public function enqueue_admin_scripts() {
		global $current_screen;

		if ( $current_screen->is_block_editor() ) {
			$script_dependencies = [ 'editorial-flow-sidebar', 'wp-components', 'wp-data', 'wp-element', 'wp-hooks', 'wp-polyfill' ];
			wp_enqueue_script( 'ef-editorial-comments-gb', plugins_url( 'built-assets/comments-panel.js', EDITORIAL_FLOW_ROOT_FILE ), $script_dependencies, EDITORIAL_FLOW_VERSION );
			wp_enqueue_style( 'ef-editorial-comments-gb', plugins_url( 'assets/comments-panel.css', __FILE__ ), [], EDITORIAL_FLOW_VERSION );
		}
	}

	public function add_rest_api_endpoints() {
		( new REST_Comments_Controller( self::comment_type ) )->register_routes();
	}

	public function is_enabled() {
		// Force enabled for the sake of the demo. There would otherwise be a settings page to control if it's enabled or not.
		return true;
	}
}
