<?php
try{
/*$path = "C:/wamp/rms-dev/application/modules/admin/models/";
$topath = "C:/dump/";
$ite=new RecursiveDirectoryIterator($path);

$bytestotal=0;
$nbfiles=0;
foreach (new RecursiveIteratorIterator($ite) as $filename=>$cur) {
	if(is_file($filename) and preg_match('/^.+\.php$/i', $filename)){
    	$filesize=$cur->getSize();
    	$str  = file_get_contents($filename);
    	$repl = array(BLOG_PREFIX, ECOM_PREFIX, MODEL_PREFIX, HELP_PREFIX);
    	$mods = str_replace($repl, ADMIN_PREFIX, $str);
    	$done = file_put_contents($filename,  $mods);
    	$bytestotal+=$filesize;
    	$nbfiles++;
    	echo "$filename => $filesize\n";
	}
}*/

		//==============================================================
/* 	 	$conList   = array (
 	 	    'admin'      => ADMIN_PREFIX, 
 			'storefront' => ADMIN_PREFIX, 	 		 	 		 
 	 		'blog'       => ADMIN_PREFIX, 
 	 		'ecom'       => ADMIN_PREFIX, 
 	 		'help'       => HELP_PREFIX 
 	 		
 	 	);
 	 	 
	   $manager->setAttribute ( Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_AGGRESSIVE);
	   
	   $entry = array();
	   foreach($conList as $c => $prefix){	    
	   		$entry  []= file_get_contents($doctrineConfig ['yaml_schema_path'][$c]);
	   } 
	   $mergedSchema = dirname($doctrineConfig ['yaml_schema_path']['merged']) . '/mergedSchema.yml';	   
	   file_put_contents($mergedSchema, implode("\n", $entry));
	   
	    $yamlParam = array ('generateTableClasses' 	=> false, 
						    'baseClassPrefix' 		=> 'Base_', 
						    'baseClassesDirectory' 	=> '', 
						    'classPrefixFiles'		=> false, 
						    'classPrefix' 			=>  ADMIN_PREFIX, 
						    'pearStyle' 			=> true, 
						    'phpDocEmail' 			=> 'info@rhema-webesign.com', 
						    'phpDocName' 			=> 'Pele Odiase', 
						    'phpDocSubpackage' 		=> 'RhemaSys', 
						    'baseClassName' 		=> 'Rhema_Model_Abstract' 
						    ); */

		
			//Doctrine_Core::dropDatabases();
			//Doctrine_Core::createDatabases('con1');
			//Doctrine_Core::createDatabases('help_center');
			//Doctrine_Core::compile();
			 
			//Doctrine_Core::createTablesFromModels($doctrineConfig ['models_path'][$c]);
			//Doctrine_Core::generateYamlFromDb($doctrineConfig ['yaml_schema_path'],array('rhemasys_dev'));
			//Doctrine_Core::generateModelsFromDb($doctrineConfig ['models_path'], array('rhemasys_dev'),$yamlParam);
			//Doctrine_Core::generateYamlFromModels($doctrineConfig ['yaml_schema_path'],$doctrineConfig ['models_path']);
			//Doctrine_Core::generateModelsFromYaml($mergedSchema,$doctrineConfig ['models_path']['admin'],$yamlParam);
 

		//Rhema_Util::adminTables();
 }catch(Exception $e){
	pd($e->getMessage());
    	
    }

 
/*    	$schemaBlocks = array_keys($doctrineConfig['yaml_schema_path']);
		$yamlParam    = $doctrineConfig['params'];
		$mergedSchema = $doctrineConfig['merged_schema_path'];
		
		$title = 'Model Generation From Schema';

		$manager = Doctrine_Manager::getInstance();
		$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_AGGRESSIVE);

		$entry = array();
		foreach($schemaBlocks as $c){
			$entry[] = file_get_contents($doctrineConfig['yaml_schema_path'][$c]);
		}

		file_put_contents($mergedSchema, implode(PHP_EOL, $entry));

		try{
			Doctrine_Core::generateModelsFromYaml($mergedSchema, $doctrineConfig['models_path']['admin'], $yamlParam);
			$message = 'Models created from schema successfully';
			$type = Rhema_Dto_UserMessageDto::TYPE_SUCCESS;
		}catch(Zend_Controller_Action_Exception $e){
			$message = $e->getMessage();
			$type = Rhema_Dto_UserMessageDto::TYPE_ERROR;
		}*/