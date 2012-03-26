<?php		
	if(extension_loaded('memcache')){
		$cache['provider'] 	= 'memcache';	
		$cache['params'] = 'serverIp=127.0.0.1';
	}else{
		$cache['provider'] 				= null;
	}
				
	$resourcesDir 					= APPLICATION_PATH . '/../data/wurfl/';						
	$wurfl['main-file'] 			= 'wurfl-latest.zip';	
	//$wurfl['main-file'] 			= 'wurfl/wurfl-2.0.27.zip';						
	$wurfl['patches'] 				= array ('web_browsers_patch.xml' );						
	$persistence['provider'] 		= 'file';						
	$persistence['dir'] 			= APPLICATION_PATH . '/../data/cache/';											
	$configuration['wurfl'] 		= $wurfl;						
	$configuration['persistence'] 	= $persistence;						
	$configuration['cache'] 		= $cache;
	
