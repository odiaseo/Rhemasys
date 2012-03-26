<?php

define ( 'DS', DIRECTORY_SEPARATOR ); // I always use this short form in my code.

/**
 * dumps the argument using the Zend_Debug::dump function
 * Available in development environment only
 * @param unknown_type $var
 */

function vd($var) {
	
	if (APPLICATION_ENV == 'development' or isCli ()) {
		
		$loc = whereCalled ();
		
		$label = "Dump: $loc";
		
		$result = Zend_Debug::dump ( $var, $label );
		
		die ();
	
	}

}

/**
 * displays the args in <pre> tags using the print_r function
 * Available only in development enviroment
 */

function pd() {
	
	if (APPLICATION_ENV == 'development' or isCli ()) {
		
		$loc = whereCalled ();
		
		$label = "Dump: $loc \n";
		
		echo $label;
		
		foreach ( func_get_args () as $var ) {
			
			echo '<pre>';
			
			print_r ( $var );
			
			echo '</pre>';
		
		}
		
		die ();
	
	}

}

/**
 * Shows the backtrace and highlights where the call was made
 * @param unknown_type $level
 * @return string
 */

function whereCalled($level = 1) {
	
	$trace = debug_backtrace ();
	
	$file = $trace [$level] ['file'];
	
	$line = $trace [$level] ['line'];
	
	$object = isset ( $trace [$level] ['object'] ) ? $trace [$level] ['object'] : null;
	
	if (is_object ( $object )) {
		
		$object = get_class ( $object );
	
	}
	
	return "Where called: line $line of $object \n(in $file)";

}

/**
 * Output message if constant CLI_VERBOSE is set
 * Used in cli mode
 * @param unknown_type $msg
 * @param unknown_type $newLine
 */

function div($msg, $newLine = PHP_EOL, $str = ' ... ') {
	if (isVerbose ()) {
		$msg = ( array ) $msg;
		echo implode ( $str . $newLine, $msg ) . $str . $newLine;
	}
}

function mu($return = false) {
	if (isVerbose ()) {
		$size = memory_get_peak_usage ( true );
		$unit = array ('b', 'kb', 'mb', 'gb', 'tb', 'pb' );
		$val = @round ( $size / pow ( 1024, ($i = floor ( log ( $size, 1024 ) )) ), 2 ) . ' ' . $unit [$i];
		if ($return) {
			return $val;
		}
		echo $val;
	}
}

function isVerbose() {
	
	return (defined ( 'CLI_VERBOSE' ) and CLI_VERBOSE);

}

function copy_r($path, $dest) {
	
	if (is_dir ( $path )) {
		
		@mkdir ( $dest, null, true );
		
		$objects = scandir ( $path );
		
		if (sizeof ( $objects ) > 0) {
			
			foreach ( $objects as $file ) {
				
				if ($file == "." || $file == "..")
					
					continue;
				
				if (is_dir ( $path . DS . $file )) {
					
					copy_r ( $path . DS . $file, $dest . DS . $file );
				
				} else {
					
					div ( 'copying ' . $file . ' -> ' . $dest . DS . $file, '' );
					
					copy ( $path . DS . $file, $dest . DS . $file );
					
					div ( 'done', "\n", '' );
				
				}
			
			}
		
		}
		
		return true;
	
	} elseif (is_file ( $path )) {
		
		div ( 'copying ' . $path . ' -> ' . $dest, '' );
		
		$done = copy ( $path, $dest );
		
		div ( 'done', "\n", '' );
		
		return $done;
	
	} else {
		
		return false;
	
	}

}

function isCli() {
	
	if (! defined ( 'STDIN' ) && isCgi ()) {
		
		if (getenv ( 'TERM' )) {
			
			return true;
		
		}
		
		return false;
	
	}
	
	return defined ( 'STDIN' );

}

function isCgi() {
	
	if (substr ( PHP_SAPI, 0, 3 ) == 'cgi') {
		
		return true;
	
	} else {
		
		return false;
	
	}

}

 /**
  * Execute Command
  * @param unknown_type $cmd
  * @param unknown_type $output
  * @param unknown_type $return
  */
 function ec($cmd, &$output = null, &$return = null){
        echo "executing [$cmd]\n";
        exec($cmd, $output, $return);
 }