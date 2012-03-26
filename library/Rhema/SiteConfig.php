<?php

class Rhema_SiteConfig
{
    private static $_config;
    private static $_locale;
    protected static $_tempConfigFilename;
    protected static $_cachedFile ;
    protected static $_hostName ;
    protected static $_scheme; 

    const CACHE_DIR			 = 'cache';
    const CONFIG_DIR		 = 'config';
    const SITE_CONFIG_FILE   = 'site.ini';
    const MERGED_CONFIG_FILE = 'mergedconfig.ini';
    const CACHED_CONFIG      = 'config-array.txt';

    /**
     *
     * Returns site ALL configs, specific subtree of settings or only one setting
     * @param unknown_type $optionDotSeparated e.g. phpSettings.display_startup_errors
     * return array|string|int
     */
	protected static $_instance;
 

	protected function __construct() {
		$cacheDir = realpath(APPLICATION_PATH . '/../sites/' . SITE_DIR)
							 . DIRECTORY_SEPARATOR .  self::CACHE_DIR
    	   					 . DIRECTORY_SEPARATOR ;
		self::$_tempConfigFilename  = $cacheDir . APPLICATION_ENV . '-' . self::MERGED_CONFIG_FILE ;
		self::$_cachedFile          = $cacheDir . APPLICATION_ENV . '-' . self::CACHED_CONFIG ;
	}

	private function __clone() {
	}

	public static function getInstance() {
		if (null === self::$_instance) {
			self::$_instance            = new self ( );
		}

		return self::$_instance;
	}


	public static function getConfig($optionDotSeparated = null)
    {
    	$optionDotSeparated = trim($optionDotSeparated);
    	
        if (null === self::$_config) {
            self::$_config = Zend_Controller_Front::getInstance()
                            ->getParam('bootstrap')
                            ->getOptions();
        }

        if ($optionDotSeparated !== null) {
            $parts = explode('.', (string)$optionDotSeparated);
            return self::_getOptionFromEntries(self::$_config, $parts);
        }

        return self::$_config;
    }

    private static function _getOptionFromEntries($config, $arrEntries)
    {
        $value = null;
        $hasConfigEntry = isset($arrEntries[0]) && isset($config[$arrEntries[0]]);

        if ($hasConfigEntry) {
            $value = isset($arrEntries[1]) ?
                self::_getOptionFromEntries($config[$arrEntries[0]], array_slice($arrEntries, 1))
                : $config[$arrEntries[0]];
        }
        return $value;
    }

    public static function processConfigFiles($section, $files = array()){

    	 //if(!file_exists(self::$_cachedFile) or APPLICATION_ENV == 'development'){
    	 if(!file_exists(self::$_cachedFile)){
    	 	require_once 'Zend/Config.php';
    		require_once 'Zend/Config/Ini.php';
    		require_once 'Zend/Config/Writer/Ini.php';

    		$configDir =  APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' ;
			$recurDir  = new RecursiveDirectoryIterator($configDir);

			foreach(new RecursiveIteratorIterator($recurDir)  as $filename => $cur){
				if(substr($filename,-4) == '.ini' and basename($filename) != 'application.ini'){
					$configPath[] = $filename;
				}
			}

			$siteConfigPath = realpath(APPLICATION_PATH . '/../sites/' . SITE_DIR)
								 . DIRECTORY_SEPARATOR . self::CONFIG_DIR
								 . DIRECTORY_SEPARATOR . self::SITE_CONFIG_FILE;
			$siteConDir     = dirname($siteConfigPath);

			if(!file_exists($siteConDir)){
				mkdir($siteConDir,0755, true);
			}

			if(!file_exists(dirname(self::$_tempConfigFilename))){
				mkdir(dirname(self::$_tempConfigFilename),0755, true);
			}

			if(!file_exists($siteConfigPath)){
			    $defaultSiteConfig = '[production]' . "\r\n\n". '[development : production]';
				file_put_contents($siteConfigPath,  $defaultSiteConfig);
			}

			asort($configPath);

			$configPath[] = $siteConfigPath ;
			$appConfig    = $configDir . DIRECTORY_SEPARATOR . 'application.ini';
			array_unshift($configPath, $appConfig);


    		$config = new Zend_Config(array(), true);

	    	foreach($configPath as $filename){
	    		$dataConfig = new Zend_Config_Ini($filename, APPLICATION_ENV);
	    		$config->merge($dataConfig);
	    	}

	    	$writer    = new Zend_Config_Writer_Ini();
	    	$writer->write(self::$_tempConfigFilename, $config);

	    	$config    = $config->toArray();
	    	$string    = json_encode($config);
	    	$done      = file_put_contents(self::$_cachedFile, $string);

    	}else{
    		$data = file_get_contents(self::$_cachedFile);
    		$config = json_decode($data, true);
    	}

    	if(Rhema_Constant::PRD_ENV != APPLICATION_ENV){
    	    $config['settings']['use_page_cache']  = 0 ;
    	    $config['settings']['use_cache']     = 0;
    	}
    	return $config;
    }
    
    public static function isDev(){
    	return (APPLICATION_ENV == 'development') ? true : false;
    }
    
    public static function isProd(){
    	return (APPLICATION_ENV == 'production') ? true : false;
    }
    
    public static function getSiteJsPath($theme = null){
    	$theme  = ($theme !== null) ? $theme : Zend_Registry::get(Rhema_Constant::SITE_THEME_KEY);
    	$suffix = $theme . '/scripts';
    	return self::getDomainPath('static', $suffix); 
    }
    
    public static function getSiteCssPath($theme = null){
    	$theme  = ($theme !== null) ? $theme : Zend_Registry::get(Rhema_Constant::SITE_THEME_KEY);
    	$suffix = $theme . '/css';
    	return self::getDomainPath('static', $suffix); 
    }
    
    public static function getSiteImagePath($theme = null){
    	$theme  = ($theme !== null) ? $theme : Zend_Registry::get(Rhema_Constant::SITE_THEME_KEY);
    	$suffix = $theme . '/images';
    	return self::getDomainPath('static', $suffix); 
    }
    
    public static function getStaticPath($suffix = ''){
    	return self::getDomainPath('static', $suffix); 
    } 
      
    public static function getMinimizePath($theme, $context = CONTEXT_SITE){
    	if($context == CONTEXT_SITE){
    		return self::getStaticPath("{$theme}/merged");
    	}else{
    		return self::getBackendPath('merged');
    	}
    }
    public static function getBackendScriptsPath(){
    	return self::getBackendPath() . 'scripts/'; 
    }
    
    public static function getBackendPath($dir = ''){
    	return self::getDomainPath('admin',"backend/{$dir}"); 
    } 
       
    public static function getDomainPath($type, $suffix = '', $addScheme = true, $rotate = true){
    	$hostname  = self::getHostName();
    	$subdomain = false;
    	$rotate    = Rhema_Util::isAdminRoute() ? false : $rotate ;
    	
    	if($rotate and 'admin' == $type and !Rhema_Util::isAdminRoute()){
    		$imageStore = self::getConfig('settings.images_subdomain');
    		$hostname   = $imageStore ? rtrim($imageStore, '/') . '/' : $hostname;
    		$options    = array(
    			"images1.{$hostname}",
    			"images2.{$hostname}",
    			"images3.{$hostname}",
    			"images4.{$hostname}",
    		    "admin.{$hostname}"
    		);
    		
/*    		if(self::isProd()){
    			$options = array_merge($options, array(
    			    "admin.{$hostname}",
    				"admin.mealcentre.com",
    				"admin.ourmobiledeals.com",
    				"admin.rhemastudio.com",
    			));
    		}*/
    		$key 	    = array_rand($options, 1); 
    		$subdomain       = $options[$key] ;
    	}elseif($rotate and 'static' == $type){
    		if(rand(0,1)){
    			$type = 'www';
    		}
    	}
    	
    	$subdomain = $subdomain ? $subdomain : $type . '.' . $hostname ;
    	$subdomain = rtrim($subdomain, '/') . '/';
    	
    	if(!self::$_scheme){
    		self::$_scheme   =  Zend_Controller_Front::getInstance()->getRequest()->getScheme(); 
    	}
    	$suffix = $suffix ? trim($suffix, "/") . '/' : '';
    	if($addScheme){
    		return self::$_scheme . '://' . $subdomain . $suffix;
    	}else{
    		return $type . '.' . $hostname . $suffix;
    	}
    	 
    }
	/**
	 * @return the $_hostName
	 */
	public static function getHostName() {
		if(!self::$_hostName){
			$httpHost = Zend_Controller_Front::getInstance()->getRequest()->getHttpHost();
			$host     = current(explode(':', $httpHost)); 
			self::$_hostName =  str_replace('www.', '', $host) . '/';
		}
		return Rhema_SiteConfig::$_hostName;
	}
 

 }