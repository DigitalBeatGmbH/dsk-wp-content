<?php
spl_autoload_register( function ( $class ) {
	//change this to your root namespace
	$prefix = 'TNP\\API\\';
	//make sure this is the directory with your classes
	$base_dir = __DIR__ . '/';
	$len      = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}
	$relative_class = substr( $class, $len );
	$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
	if ( file_exists( $file ) ) {
		require $file;
	}

} );
