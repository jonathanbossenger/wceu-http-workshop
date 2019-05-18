<?php

// For plugins
function wceu_error_log( $message, $data = '' ) {
	$file = trailingslashit( plugin_dir_path( __FILE__ ) ) . 'log/' . date( 'Y-m-d' ) . '.log';
	if ( ! is_file( $file ) ) {
		file_put_contents( $file, '' );
	}
	if ( ! empty( $data ) ) {
		$message = array( $message => $data );
	}
	$data_string = print_r( $message, true ) . "\n";
	file_put_contents( $file, $data_string, FILE_APPEND );
}
