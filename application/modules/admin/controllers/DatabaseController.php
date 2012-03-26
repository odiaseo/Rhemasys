<?php
class Admin_DatabaseController extends Zend_Controller_Action {

	
    public function init() {
        /* Initialize action controller here */
        parent::init();
		
    }

    public function indexAction() {
    	$this->_table					= ADMIN_PREFIX . 'AdminDatabase'; 
		$this->_helper->displayGrid(); 
    }
    
    public function backupAction(){
    	$task          = $this->_request->getParam('task', 'all');
    	$config        = Rhema_SiteConfig::getConfig('settings.db.local');
    	$filename      = $config ['dbname'] . '-task-' . date("Y-m-d-H-i-s") .'.sql.gz'; 
 		//$filename = $db_name . "_" . date("Y-m-d_H-i-s") . '.sql.gz';
		//$command = "/usr/local/mysql/bin/mysqldump  ;
		$options       = '--opt --skip-extended-insert --complete-insert -host="'
					    . $config['host']. '" -user="' .$config['username']. '" -password="' 
					    . $config['password'] .'"';
					    
    	$path = '';//H:\wamp\bin\mysql\mysql5.5.8\bin\\';
    	
    	switch($task){
    		case 'structure':{
    			$command = $path . "mysqldump $options –no-data {$config['dbname']} > $filename";
    			break;
    		}
    		case 'restore':	{
    			$command = $path . "mysql $options {$config['dbname']} < $filename";
    			break;
    		}
    		case 'data':{
    			$command = $path . "mysqldump $options –no-create-info {$config['dbname']} > $filename";
    			break;
    		}
    		default:
    		case 'data-structure':{
    			$command = $path . "mysqldump $options {$config['dbname']} > $filename";
    		}
    		
    	}
    	//$conn = mysql_connect($config['host'], $config['username'], $config['password']) or die ('unable to connet to mysql');                     ('Error connecting to mysql');
		//mysql_select_db($config ['dbname']);
    	$result = exec($command, $output); 
    	
    	return $output;
    }
    
    public function createAction(){
    	
    }
    
    public function tableAction(){
    	
    }
    
    public function manageTableAction(){
		$manager = Doctrine_Manager::getInstance();
		$manager->setAttribute ( Doctrine::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_AGGRESSIVE);		
		
		$this->view->oldSchema = Rhema_SiteConfig::getConfig('doctrine.merged_schema_path_old');
		$this->view->buttons   = Rhema_SiteConfig::getConfig('dbtools.button'); 
		$this->view->changes   = array();    	
    }
	
	public function schemaViewAction(){
		$config  = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getOption('doctrine'); 
		$this->view->schemaFiles = $config['yaml_schema_path'];
	}
	
	public function diffAction(){
		
	}
}