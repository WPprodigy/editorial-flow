<?php
/**
 * Plugin Name: Editorial Flow
 * Description: Edit Flow V2 😀
 * Version:     1.0
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.1
 * Requires PHP:      7.1
 */

// Future TODO: Bail early if using unsupported PHP/WP version.

define( 'EDITORIAL_FLOW_VERSION', '1.0.0' );
define( 'EDITORIAL_FLOW_ROOT_FILE', __FILE__ );

require_once dirname( EDITORIAL_FLOW_ROOT_FILE ) . '/autoload.php';

/**
 * Main class, used to setup and initiate the plugin.
 */
final class Editorial_Flow {
	private static $instance;
	private $module_registry;

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Editorial_Flow();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->module_registry = new Editorial_Flow\Module_Registry();

		// The WP entry point.
		add_action( 'init', array( $this, 'load_modules' ) );
	}

	public function load_modules() {
		// Register core modules.
		$this->register_module( new Editorial_Flow\Modules\General_Settings() );
		$this->register_module( new Editorial_Flow\Modules\Editorial_Comments() );

		$enabled_modules = $this->module_registry->get_enabled_modules();
		foreach ( $enabled_modules as $module ) {
			$module->load();
		}
	}

	public function register_module( $module ) {
		$this->module_registry->register( $module );
	}
}

// Start things up!
function Editorial_Flow() {
	return Editorial_Flow::instance();
}
Editorial_Flow();
