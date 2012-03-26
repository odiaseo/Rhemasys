<?php
class Rhema_Cache extends Rhema_Cache_Abstract{
	
	const CACHE_CLASS_FILE = 'class-file';
	const CACHE_PAGE_FILE = 'page-file';
	const CACHE_CLASS_MEMCACHED = 'class-memcache';
	
	private $_modelName;
	public  static $statCache = null ;
	
	public function removeCacheByTag($tags = array(), $mode = Zend_Cache::CLEANING_MODE_MATCHING_TAG){
		$tags = (array) $tags;
		if(array(
				$tags) and count($tags)){
			$this->_model = $this;
			$cache = $this->getCache();
			return $cache->clean($mode, (array)$tags);
		}
	}
	/**
	 * @return the $_modelName
	 */
	public function getModelName(){
		return $this->_modelName;
	}
	
	/**
	 * @param field_type $_modelName
	 */
	public function setModelName($_modelName){
		$this->_modelName = $_modelName;
	}
	
	public static function clearCache($template, $tags, $mode = Zend_Cache::CLEANING_MODE_MATCHING_TAG ){
		$manage = Zend_Registry::get(Rhema_Constant::CACHE_MANAGER);
		$cache = $manage->getCache($template);
		$cache->clean($mode, (array)$tags);		
	}

	public static function clearCacheOnUpdate($model, array $tags = null){
		$nameFilter  = new Rhema_Filter_FormatModelName();
		$tags[]      = $model;
			
		foreach($tags as $k => $m){
			$tags[$k] = $nameFilter->filter($m);
		}
		
		if(preg_match('/(menu)$/i', $model)){
			$tags[] = $nameFilter->filter('admin_module');
			$tags[] = 'getMenuTree';
		}elseif(preg_match('/(role|acl)$/i', $model)){ 
			Rhema_Util::unsetSessData("acl_site"); 
		}
 

		self::clearCache('class-file', $tags); 		
	}
	
    /**
     * Enter description here ...
     * @return Zend_Cache
     */
    public static function getStatCache($lifetime = 604800){    	
    	if(self::$statCache == null){      	 
	    	$cacheManager  = Zend_Registry::get('cache-manager');
	    	$cache         = $cacheManager->getCache('stat-cache'); 
	 
    		$dir           = current(Rhema_Util::createSiteDirectory('/stat'));
    		$cache->getBackend()->setCacheDir($dir);
  
    		$cache->setLifetime($lifetime);
    		$cache->setOption('automatic_cleaning_factor', 0);   
    		 			
    		self::$statCache = $cache ;
    	}
    	return self::$statCache ;
    }
    
    /**
     * Get default cache object
     * @return Zend_Cache
     */
    public static function getDefaultCache(){
    	$cacheManager  = Zend_Registry::get('cache-manager');
    	$cache         = $cacheManager->getCache('doctrine-cache');
	    return  $cache; 
    }
}