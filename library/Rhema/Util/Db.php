<?php
	class Rhema_Util_Db {
		
		public static function generateSiteConfig($dirname){

/*			doctrine.connection_string.con1 = "mysql://root@localhost/rhemasys_dev"
			;doctrine.connection_string.con1 = "mysql://odiaseo:myfamily34@localhost/rhemasys_dev"
			doctrine.yaml_schema_path.con1  = APPLICATION_PATH "/../doctrine/schema/schema.yml"
			doctrine.models_path.con1       = APPLICATION_PATH "/modules/admin/models"
			
			doctrine.connection_string.con2 = "mysql://root@localhost/rhemasys_dev"
			doctrine.yaml_schema_path.con2  = APPLICATION_PATH "/../doctrine/schema/blog.yml"
			doctrine.models_path.con2       = APPLICATION_PATH "/modules/blog/models"
			
			doctrine.connection_string.con3 = "mysql://root@localhost/rhemasys_dev"
			doctrine.yaml_schema_path.con3  = APPLICATION_PATH "/../doctrine/schema/ecom.yml"
			doctrine.models_path.con3       = APPLICATION_PATH "/modules/ecom/models"
			
			doctrine.connection_string.conz  = "mysql://root@localhost/rhemasys_dev"
			;doctrine.connection_string.conz  = "mysql://odiaseo:myfamily34@localhost/rhemasys_dev"
			;doctrine.connection_string.conz = "mysql://rhemaweb_user:user2010@213.171.200.65/rhemaweb"
			doctrine.yaml_schema_path.conz   = APPLICATION_PATH "/../doctrine/schema/schema.yml"
			doctrine.models_path.conz        = APPLICATION_PATH "/modules/admin/models"	*/		
		}
		
		public static function listMissingTables($conName = 'admin', $dbName = 'admin'){
			$array          = array();
			$filter 		= new Zend_Filter_Word_CamelCaseToUnderscore();
			$doctrineConfig = Rhema_SiteConfig::getConfig('doctrine'); 
			 
			$manager  = Doctrine_Manager::getInstance();
			$con      = $manager->getConnection($conName);
			$dbTables = $con->import->listTables($dbName);		
			$flipList = array_flip($dbTables);
			
			 
			$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_AGGRESSIVE);	
			$baseDir = $doctrineConfig ['models_path']['admin'] . DIRECTORY_SEPARATOR . 'Base';
			$ite     = new DirectoryIterator($baseDir);
			
			foreach($ite as $fname=> $finfo){
				if(!$finfo->isDot()){
					$modelName = substr(basename($finfo->getFilename()),0, -4);
					$tableName = trim(strtolower($filter->filter($modelName)));
					if($tableName and !isset($flipList[$tableName])){
						$array[$modelName] =   $tableName;
					}
				}
			}	
			return array_filter($array);		
		}
	}