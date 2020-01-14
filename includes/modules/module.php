<?php

namespace Editorial_Flow\Modules;

abstract class Module {
	protected $name;
	protected $information;
	private static $ui_enabled_modules;

	/**
	 * Shared template algorithm. Used to load each active module.
	 */
	public function load() {
		// Would add additional methods here as needed, such as:
		// $this->maybe_run_install();
		// $this->maybe_run_upgrade();

		$this->init();
	}

	/**
	 * The key template method. Must be overriden by specific modules.
	 */
	protected abstract function init();

	public function is_enabled() {
		if ( ! isset( self::$ui_enabled_modules ) ) {
			self::$ui_enabled_modules = get_option( 'editorial_flow_active_modules', [] );
		}

		return in_array( $this->name, apply_filters( 'editorial_flow_active_modules', self::$ui_enabled_modules ), true );
	}

	public function get_name() {
		return $this->name;
	}

	public function get_information() {
		return $this->information;
	}
}
