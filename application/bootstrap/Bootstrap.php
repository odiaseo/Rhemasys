<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
    private $_siteCache;
    protected $_logger;
    protected $_resourceLoader;
    public $frontController;
    public $base;
    public $cpanel;

     protected function _initLog(){
        $this->bootstrap('frontController' );  
        $option = $this->getOption('settings');  
            
        if(Rhema_Constant::PRD_ENV == APPLICATION_ENV){
            $logDir = $option['log_dir'] . DIRECTORY_SEPARATOR . 'syslog.log'; 
            $writer = new Zend_Log_Writer_Stream($logDir );
        }else{
            $writer   = new Zend_Log_Writer_Firebug();     
        }
       
        $logger = new Zend_Log();
        $logger->addWriter($writer);
        Zend_Registry::set('logger', $logger ); 
        
        //$this->testLog();
        //Rhema_Util::setMemoryLimit();
        //$mem = ini_get('memory_limit' );
       // $val = intVal(substr($mem, 0, - 1 ) );
        //if($val < 2048){
          //  ini_set('memory_limit', '2048M' );
       // }
         
        Zend_Registry::set('logger', $logger);
        Zend_Registry::set(Rhema_Constant::PRE_QUERY_KEY, array());
        
        return $logger;
    } 

    protected function _initAutoload(){
        $autoloader = new Zend_Application_Module_Autoloader(array('namespace' => 'Admin', 'basePath' => realpath(APPLICATION_PATH . '/modules/admin' )) );
        $autoloader = new Zend_Application_Module_Autoloader(array('namespace' => 'Ecom', 'basePath' => realpath(APPLICATION_PATH . '/modules/ecom' )) );
        $autoloader = new Zend_Application_Module_Autoloader(array('namespace' => 'Blog', 'basePath' => realpath(APPLICATION_PATH . '/modules/blog' )) );
        $autoloader = new Zend_Application_Module_Autoloader(array('namespace' => 'Help', 'basePath' => realpath(APPLICATION_PATH . '/modules/help' )) );
        $autoloader = new Zend_Application_Module_Autoloader(array('namespace' => 'Storefront', 'basePath' => APPLICATION_PATH . '/modules/storefront') );
        return $autoloader;
    }
    
    protected function _initCache(){
        $options   = $this->getOptions();
        $uri       = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : false ;
        $isAdmin   = Rhema_Util::isAdminRoute($uri);
        $isDev     = Rhema_SiteConfig::isDev();
        
        if($isAdmin){
        	$cDir = array('backend/functions', 'backend/page', 'cache/page');
        }else{
        	$cDir = array('cache/functions', 'cache/page', 'cache/doctrine', 'tags', 'stat');
        }
        $cacheDirs = Rhema_Util::createSiteDirectory($cDir, 0777);
    		
        if($isAdmin or $isDev or !$options['settings']['use_cache']){
			$noCache = true;
		}else{
			$noCache = false;
		}
		
        define('NO_CACHE', $noCache);
        $cacheManager = $this->getPluginResource('cacheManager' )->getCacheManager();
        $memCache     =  $cacheManager->getCache(Rhema_Cache::CACHE_CLASS_FILE);
 
        $cacheOptions['backend']['name'] = 'file';
        $cacheOptions['backend']['options']['hashed_directory_level'] = 1;        
        $cacheOptions['backend']['options']['cache_dir']              = $cacheDirs[0] ; 
        $cacheOptions['backend']['options']['cache_file_umask']       = 0777;  
        $cacheOptions['backend']['options']['file_name_prefix']       = SITE_DIR ;
        $cacheOptions['backend']['options']['hashed_directory_umask'] = 0777;  
        
        //$temp = $cacheManager->getCacheTemplate(Rhema_Cache::CACHE_CLASS_FILE);
        //$temp = array_merge($temp, $cacheOptions);
        
       // $cacheManager->setCacheTemplate(Rhema_Cache::CACHE_CLASS_FILE, $temp);   
            
        	
        $default = $cacheManager->getCacheTemplate('default');
		$default = array_merge($default, $cacheOptions);
		
        $cacheManager->setCacheTemplate('default', $default);   
        $defaultCache = $cacheManager->getCache('default' );   
        $cacheManager->setCache('default' , $defaultCache);
        $defaultCache->setOption('automatic_cleaning_factor', 0);
        $defaultCache->setOption('cache_id_prefix',  SITE_DIR);
 
        Zend_Registry::set('cache-manager', $cacheManager );
 
     
        return $defaultCache;
    }
    
    protected function _initPluginLoaderCache(){
/*        if('development' != $this->getEnvironment() and php_sapi_name() != 'cli'){
            $path = realpath(APPLICATION_PATH . '/../sites' ) . '/' . SITE_DIR . '/cache/';
            if(! file_exists($path )){
                @mkdir($path, 0775, true );
            }
            $classFileIncCache = $path . 'pluginLoaderCache.php';
            if(file_exists($classFileIncCache )){
                include_once $classFileIncCache;
            }
            Zend_Loader_PluginLoader::setIncludeFileCache($classFileIncCache );
        }*/
    }
    protected function _initLocale(){
    	$locale = $this->getOption('locale');
        try{
            $locale = new Zend_Locale($locale);
        }catch(Zend_Locale_Exception $e){
            $locale = new Zend_Locale('en_GB' );
        }
        $cache = $this->getSiteCache('locale', 'default', '' );
        $locale->setCache($cache );
        Zend_Registry::set('Zend_Locale', $locale );
        
        return $locale;
    }

    
    protected function _initDoctrine(){
        //require_once 'Doctrine.compiled.php';
        require_once 'Doctrine.php';
        $this->bootstrap('session' );
        $namespace = Zend_Registry::get('namespace' );
        $loader = Zend_Loader_Autoloader::getInstance();
        $config = $this->getOptions();
        
        $dbParams = $config['settings']['db'];
        $schemas  = $config['doctrine']['yaml_schema_path'];
        
        $cacheDriver        = null;
        $doctineAttributess = array(Doctrine_Core::ATTR_AUTO_ACCESSOR_OVERRIDE => true, 
						        	Doctrine_Core::ATTR_USE_NATIVE_ENUM => true, 
						        	Doctrine_Core::ATTR_MODEL_LOADING => Doctrine_Core::MODEL_LOADING_PEAR, 
						        	Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES => false, 
						        	Doctrine_Core::ATTR_USE_DQL_CALLBACKS => true,
						        	Doctrine_Core::ATTR_AUTO_FREE_QUERY_OBJECTS => true
						        );
        $loader->pushAutoloader(array('Doctrine', 'autoload') );
        $manager = Doctrine_Manager::getInstance();
        
        foreach($doctineAttributess as $param => $attr){
            $manager->setAttribute($param, $attr );
        }
        
        $manager->connection($this->_getConnectionString($dbParams['local']), 'admin');
        $manager->connection($this->_getConnectionString($dbParams['remote']), 'remote'); 
        $manager->connection($config['settings']['db']['sqlite'], 'sqlite' );
        
        $manager->setCurrentConnection('admin' );
        //===================== Register Plugins =======================================
        $manager->registerExtension('Blameable' );
        $manager->registerExtension('Subsite', $config['doctrine']['extension_path'] );
        
       if(extension_loaded('memcache')){
       		$server                = $config['resources']['cachemanager']['class-memcache']['backend']['options'];
       		$server['compression'] = false ;
        	$cacheDriver 		   = new Doctrine_Cache_Memcache($server);        	
        }elseif(false and extension_loaded('apc')){
            $cacheDriver = new Doctrine_Cache_Apc();
            ini_set('apc.enabled', true );
        }elseif(false and extension_loaded('pdo_sqlite' ) and extension_loaded('sqlite' )){
            $tableName = SITE_DIR . 'QueryCache';
            $cacheConn = $manager->getConnection('sqlite' );
            $cacheDriver = new Doctrine_Cache_Db(array('connection' => $cacheConn, 'tableName' => $tableName) );
            if(! $namespace->sqliteTable){
                $sql = "SELECT name FROM sqlite_master WHERE name = ?";
                $has = $cacheConn->exec($sql, array($tableName) );
                if($has == 0){
                    try{
                        $cacheDriver->createTable();
                    }catch(Exception $e){
                        //throw new Exception('Unable to create sqlite cache table');
                    }
                    $namespace->sqliteTable = true;
                }
            }
        }else{
        	$cacheManager       = Zend_Registry::get(Rhema_Constant::CACHE_MANAGER);
            $doctrineCache 		= $cacheManager->getCache('doctrine-cache');  //$this->getResource('cache'); // Rhema_Util::getDefaultCacheObject(86400 );
            $cacheDriver   		= new Rhema_Cache_DoctrineAdapter($doctrineCache, 'doctrine' );
        }
        
        if($cacheDriver){
            $manager->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE, $cacheDriver );
            $manager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE, $cacheDriver );
            $manager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE_LIFESPAN, 3600 );
            $manager->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE_LIFESPAN, 3600 );
        }
        
        $doctrineConfig = $config['doctrine'];
        include APPLICATION_PATH . '/../library/debug/doctrine.php';
        Zend_Registry::set('doctrine', $manager );
        
        return $manager;
    }

    protected function _initSession(){
    	$sessionDir   = Rhema_Util::createSiteDirectory('session/sys_app' );
    	$sessionOptions = (array) $this->getOption('session' );
    	/*if(extension_loaded('memcache')){
    		$cacheManager = $this->getPluginResource('cacheManager' )->getCacheManager();
    		$cache        = $cacheManager->getCache('memcache');
    	}else{    		
    		$this->bootstrap('cache');
    		$cache        = clone $this->getResource('cache');
    		$backend      = clone $cache->getBackend();
    		$backend->setCacheDir($sessionDir[0]);
    		$cache->setBackend($backend);    		   		
    	}
  		   		
    	$handler      = new Rhema_Session_SaveHandler_Cache(); 
    	$handler->setCache($cache);
 
    	Zend_Session::setSaveHandler($handler); */
        Zend_Session::setOptions($sessionOptions );
        
        $defaultNamespace = new Zend_Session_Namespace(SESS_NAMESPACE );
        if(! isset($defaultNamespace->initialized )){
            Zend_Session::regenerateId();
            $defaultNamespace->initialized = true;
        }
        $config = $this->getOptions();
        Zend_Registry::set('config', $config );
        Zend_Registry::set('namespace', $defaultNamespace );
    }
 
    protected function _initViewSettings(){
        $this->bootstrap('view');
        $this->bootstrap('cache');
        $cache       = $this->getResource('cache');
        $view        = $this->getResource('view' );
        $options     = $this->getOption('settings');
        
        //$view->setEncoding('utf-8');
        
        //$view        = new Rhema_View();          
       // $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
       // $viewRenderer->setView($view);
        
        $this->_view = $view  ; 
        $this->_view->headTitle()->setSeparator(' | ' );
        $this->_view->addHelperPath('Rhema/View/Helper', 'Rhema_View_Helper' );
        $this->_view->addScriptPath(realpath(APPLICATION_PATH . '/../library/Rhema/Widget/View' ) );
 
        ZendX_JQuery::enableView($this->_view );
        ZendX_JQuery_View_Helper_JQuery::enableNoConflictMode();
 

        //initialise helpers
        Zend_Controller_Action_HelperBroker::addPrefix('Rhema_Controller_Action_Helper' );
        Zend_Controller_Action_HelperBroker::addPath('ZFDoctrine/Controller/Helper', 'ZFDoctrine_Controller_Helper' );

        
        // Initialises application pagination. 
        Zend_Paginator::setDefaultScrollingStyle('Sliding' );
        Zend_Paginator::setDefaultPageRange(7);
        Zend_Paginator::setCache($cache);
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('paginators/default.phtml' );
         
        //==== lucene configuration 
        Zend_Search_Lucene::setResultSetLimit(500);
        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');
		Zend_Search_Lucene_Analysis_Analyzer::setDefault(
		    new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive ()
		);
        
        //Setup flashmessenger          
        $fm 		   = Zend_Controller_Action_HelperBroker::getStaticHelper('flashMessenger' );
        $userMessage   = '';
        $messages      = $fm->getMessages();
        $totMessages   = count($messages );
        
        if($totMessages > 0){
            $lastMessage = $messages[$totMessages - 1];
            if($lastMessage instanceof Rhema_Dto_UserMessageDto){
                $userMessage = clone ($lastMessage);
            }else if(is_string($lastMessage )){
                $userMessage = new Rhema_Dto_UserMessageDto($lastMessage );
            }
            $fm->clearCurrentMessages();
        }
        
        $locale = Zend_Registry::get('Zend_Locale');  
        //$this->_view->setEncoding('utf-8');
        $this->view->dateFormat    = Zend_Locale_Format::getDateFormat($locale);
 		$this->view->logger        = $this->getResource('log');
        $this->view->linkAttribute = $options['affiliate']['outlink_attribute'];
        $this->_view->userMessage  = $userMessage; 
		$this->_view->addScriptPath(APPLICATION_PATH . '/layouts/scripts');
		$this->_view->headScript()->setAllowArbitraryAttributes(true);
		
        return $this->_view;
    }
 
    
    public function _initZFDebug(){
    	if(!Rhema_Util::isCli()){
	        $config = $this->getOption('zfdebug' );
	        $uAGent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
	        $zfdebugConfig = ($config['enabled'] or strpos($uAGent,'zfdebug') !== false);
	        
	        if($zfdebugConfig){
	            $autoloader = Zend_Loader_Autoloader::getInstance();
	            $autoloader->registerNamespace('ZFDebug' );
	            $list = array();
	            $front = $this->bootstrap('frontController' );
	            $cacheManager = Zend_Registry::get('cache-manager' );
	            
	            $list['function']   = $cacheManager->getCache('default')->getBackend();	            
	            $list['page']       = $cacheManager->getCache(Rhema_Cache::CACHE_PAGE_FILE)->getBackend(); 
	            if(extension_loaded('memcache')){
	            	$list['memcache']   = $cacheManager->getCache(Rhema_Cache::CACHE_CLASS_MEMCACHED)->getBackend();
	            }
 
	            $options = array('plugins' => array('Variables', 
											        'Rhema_Plugin_ZFDebug_Doctrine', 
	            								    'Rhema_Plugin_ZFDebug_PageInfo',
											        //'File' => array('base_path' => APPLICATION_PATH . '/../'), 
											        'Memory', 
											        'Time', 
											        'Registry', 
											        'Exception', 
											        'Rhema_Plugin_ZFDebug_Cache' => array('backend' => $list)
											            ),
								 'jquery_path' => ''
						);
	            $debug = new ZFDebug_Controller_Plugin_Debug($options );
	            Zend_Controller_Front::getInstance()->registerPlugin($debug );
	            return $debug;
	        }
    	}
    }
    public static function autoload($path){
        include str_replace('_', '/', $path ) . '.php';
        return $path;
    }
 
 
    public function byPass(){
        $url = isset($_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
        if(strstr($url, 'bypass' ) !== false){
            $ajax = new Rhema_Ajax_Responce($url, $this );
            $output = $ajax->process($url );
            die($output );
        }
    }
    public function run(){
        $this->frontController = $this->getResource('FrontController' );  
        $this->byPass();
        parent::run();
    }
    
    public function getSiteCache($cacheType = 'page', $front = 'page', $back = 'file'){
        $option = array();
        /*		if (extension_loaded ( 'apc' ) and $front != 'page') {

			$back  = 'apc';

		}*/
        $secs[] = $front;
        $secs[] = $back;
        $template = implode('-', array_filter($secs ) );
        $cacheDir = realpath(APPLICATION_PATH . '/../sites/' ) . '/' . SITE_DIR . '/cache/' . $cacheType;
        if(! file_exists($cacheDir )){
            mkdir($cacheDir, 0777, true );
        }
        $option['frontend']['options']['cache_id_prefix'] = 'static';
        $option['frontend']['options']['cached_entity'] = $this;
        $option['backend']['options']['cache_dir'] = $cacheDir;
        $siteCache = Rhema_Cache::getCacheByTemplate($template, $option );
        $this->frontController->setParam('pageCache', $siteCache );
        Zend_Registry::set('pageCache', $siteCache );
        return $siteCache;
    }
    
    public function updateElements(){
        $res = Doctrine_Query::create()->from('Admin_Model_AdminElement e' )->execute();
        foreach($res as $arr){
            $cont = $arr->controller;
            $act = $arr->action;
            $arr->widget = ucfirst($cont ) . '~' . $act;
        }
        $res->save();
    }
    
    /**
     * Enter description here ...
     * @param unknown_type $dbParams
     * @return string
     */
    private function _getConnectionString($dbParams){
       $conString  = $dbParams['scheme'] 
                	. '://' 
			        . $dbParams['username'] 
			        . ($dbParams['password'] ? ':' . $dbParams['password'] : '') 
			        . '@' 
			        . $dbParams['host'] 
			        . '/' 
			        . $dbParams['dbname'];   
		return $conString;	
    }
    
    public function _initCli(){ 
    	
		if (Rhema_Util::isCli()) { 			
			$start = time();
			echo "\n =========== running in command line mode on " . date('j F Y H:i:s') . " =========\n";
			include 'cli-config.php';
		        
	        //initialise helpers
	        Zend_Controller_Action_HelperBroker::addPrefix('Rhema_Controller_Action_Helper' );
	        Zend_Controller_Action_HelperBroker::addPath('ZFDoctrine/Controller/Helper', 'ZFDoctrine_Controller_Helper' );        		 	 
            // launch index controller of cron module 
            $this->bootstrap ('frontcontroller');
            $front = $this->getResource('frontcontroller');            
        	//$front  = Zend_Controller_Front::getInstance();
        	$params = array(
        				'bootstrap'  =>  $this,
        				'action'     =>  $console->action,
        				'verbose'    =>  $console->verbose,
        				'servername' =>  $console->servername,
        				'params'     =>  $console->params 
        		);
        	
        	$front->setParams($params)
        		  ->setDefaultModule('cron') 
        	      ->setDefaultControllerName('index') 
        	      ->setDefaultAction($console->action)
   			      ->setParam('disableOutputBuffering', true );
   			      
 			$this->bootstrap(array('autoload','doctrine', 'viewSettings'));
 			$front->addControllerDirectory(APPLICATION_PATH.'/modules/cron/controllers') ;      	
 			//$front->setRequest (new Zend_Controller_Request_Simple ($console->action, 'index', 'cron',$params));
 			
 			try{        		
        		$front->dispatch();
 			}catch(Exception $e){
 				echo $e->getMessage();
 			}	
 			$end  = time();
 			$diff = $end - $start ;
 			echo "\n\n===================== completed in {$diff} seconds! ============================ \n\n";		
			exit();
    	}
    }
    
    public function testLog(){
        $log  = Zend_Registry::get('logger');      
        if($log){
       		$log->debug("testing log"); 
        }    	
    }
}