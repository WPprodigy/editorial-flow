<?php

namespace Editorial_Flow\Modules;

class General_Settings extends Module {
	public function __construct() {
		$this->name = 'general_settings';

		$this->information = [
			'title' => __( 'General Settings', 'editorial-flow' ),
			'description' => __( 'Editorial Flow redefines your WordPress publishing workflow.', 'editorial-flow' ),
		];
	}

	protected function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );
		// TODO: Add a global settings page that allows each module to be enabled and configured.
	}

	public function register_admin_scripts() {
		// Will only be enqueued if the sidebar is used by other modules.
		$dependencies = [ 'wp-edit-post', 'wp-element', 'wp-hooks', 'wp-plugins', 'wp-polyfill' ];
		wp_register_script( 'editorial-flow-sidebar', plugins_url( 'built-assets/sidebar.js', EDITORIAL_FLOW_ROOT_FILE ), $dependencies, EDITORIAL_FLOW_VERSION );
	}

	public function is_enabled() {
		// This module should always be enabled.
		return true;
	}
}
