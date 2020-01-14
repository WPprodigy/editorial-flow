<?php

use Editorial_Flow\Module_Registry;
use Editorial_Flow\Modules\Module;
use Editorial_Flow\Modules\General_Settings;

class WP_Test_Module_Registry extends WP_UnitTestCase {
	public function test_can_register_modules() {
		$registry = new Module_Registry();
		$registry->register( new Disabled_Module() );
		$this->assertArrayHasKey( 'disabled_module', $registry->get_modules() );
	}

	public function test_empty_array_returned_if_none_registered() {
		$registry = new Module_Registry();
		$this->assertTrue( [] === $registry->get_modules() );
	}

	public function test_empty_array_returned_if_none_enabled() {
		$registry = new Module_Registry();
		$registry->register( new Disabled_Module() );
		$this->assertTrue( [] === $registry->get_enabled_modules() );
	}

	public function test_enabled_module_returned() {
		$registry = new Module_Registry();
		$registry->register( new Enabled_Module() );
		$this->assertArrayHasKey( 'enabled_module', $registry->get_modules() );
	}
}

class Enabled_Module extends Module {
	public function __construct() {
		$this->name = 'enabled_module';
	}

	public function init() {}

	public function is_enabled() {
		return true;
	}
}

class Disabled_Module extends Module {
	public function __construct() {
		$this->name = 'disabled_module';
	}

	public function init() {}

	public function is_enabled() {
		return false;
	}
}
