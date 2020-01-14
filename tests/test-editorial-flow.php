<?php

class WP_Test_Editorial_Flow extends WP_UnitTestCase {

	public function test_instance_is_returned_from_helper_function() {
		$this->assertInstanceOf( Editorial_Flow::class, Editorial_Flow() );
	}

}
