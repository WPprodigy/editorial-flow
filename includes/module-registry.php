<?php

namespace Editorial_Flow;

/**
 * Keeps track of modules.
 */
class Module_Registry {
	private $modules = [];

	public function register( Modules\Module $module ) {
		$this->modules[ $module->get_name() ] = $module;
	}

	public function get_modules() {
		return $this->modules;
	}

	public function get_enabled_modules() {
		$active_modules = array_filter( $this->modules, function( $module ) {
			return $module->is_enabled();
		} );

		return empty( $active_modules ) ? [] : $active_modules;
	}
}
