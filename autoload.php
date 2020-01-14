<?php

spl_autoload_register( 'editorial_flow_autoload_modules' );
function editorial_flow_autoload_modules( $class_name ) {
	if ( false === strpos( $class_name, 'Editorial_Flow' ) ) {
		return;
	}

	// Split into parts using the namespaces, then match our file structure (hyphens and lowercase).
	$file_parts = array_reverse( array_map( 'strtolower', str_replace( '_', '-', explode( '\\', $class_name ) ) ) );
	$file_name  = "$file_parts[0].php";

	// Handle modules specially.
	if ( false !== strpos( $class_name, 'Modules' ) ) {
		$module_name = $file_parts[0];

		$directories_to_try = [ "includes/modules/$module_name", 'includes/modules' ];
		foreach ( $directories_to_try as $directory ) {
			$file_location = dirname( EDITORIAL_FLOW_ROOT_FILE ) . "/$directory/$file_name";

			if ( file_exists( $file_location ) ) {
				require_once $file_location;
				return;
			}
		}
	} else {
		$file_location = dirname( EDITORIAL_FLOW_ROOT_FILE ) . "/includes/$file_name";

		if ( file_exists( $file_location ) ) {
			require_once $file_location;
			return;
		}
	}
}
