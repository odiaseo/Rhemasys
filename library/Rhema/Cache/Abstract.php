<?php
abstract class Rhema_Cache_Abstract{
	protected $_classMethods;
	protected $_cache;
	protected $_frontend;
	protected $_backend;
	protected $_frontendOptions = array();
	protected $_backendOptions = array();
	protected $_model;
	protected $_tagged;
	protected $_entity;
	protected $_useCache     = true ;

	public function init(){

	}
	public function __construct($model = null, $options = array(), $tagged = null){
		$this->init();

		$this->_model = $model;
		if($options instanceof Zend_Config){
			$options = $options->toArray();
		}

		if(is_array($options)){
			$this->setOptions($options);
		}

		$this->setTagged($tagged);

	}
	public function disableCache(){
		$this->_useCache = false;
		return $this;
	}

	public function setOptions(array $options){
		if(null === $this->_classMethods){
			$this->_classMethods = get_class_methods($this);
		}

		foreach($options as $key => $value){
			$method = 'set' . ucfirst($key);
			if(in_array($method, $this->_classMethods)){
				$this->$method($value);
			}
		}

		return $this;
	}

	public function setCache(Zend_Cache $cache){
		$this->_cache = $cache;
	}

	public function getCache($model = null){
		if(null === $this->_cache){
			$util = Rhema_Util::getInstance();
			$opts = $util->getCacheOptions();
			$this->setOptions($util->getCacheOptions());
			if(! isset($this->_frontendOptions['cached_entity']) or ! $this->_frontendOptions['cached_entity']){
				$this->_frontendOptions['cached_entity'] = is_object($model) ? $model : $this->_model;
			}
			 
			$this->_cache = Zend_Cache::factory($this->_frontend, $this->_backend, $this->_frontendOptions, $this->_backendOptions);
		}
		return $this->_cache;
	}

	public function setFrontendOptions(array $frontend){
		$this->_frontendOptions = $frontend;
		$this->_frontendOptions['cached_entity'] = $this->_model;
	}

	public function setBackendOptions(array $backend){
		$this->_backendOptions = $backend;
	}

	public function setBackend($backend){
		$this->_backend = $backend;
	}

	public function setFrontend($frontend){
		if('Class' != $frontend){
			throw new Rhema_Model_Exception('Frontend type must be Class');
		}
		$this->_frontend = $frontend;
	}

	public function setTagged($tagged = null){
		$this->_tagged = $tagged;
		if(null == $tagged){
			$this->_tagged = 'default';
		}
	}

	public function __call($method, $param){
		if(! is_callable(array($this->_model, $method))){
			throw new Rhema_Model_Exception('Method ' . $method . ' does not exist in
					class ' . get_class($this->_model));
		}

		$model  = get_class($this->_model) ;
		if($this->_useCache){
			$cache = $this->getCache();
			$tags = array($model, $method);
			$tags = array_filter(array_merge($tags, (array) $this->_tagged));
			$cache->setTagsArray($tags);
			$callback = array($cache, $method);
		}else{
			$callback = array(new $model(), $method);
		}
		return call_user_func_array($callback, $param);
	}

	public static function getCacheByTemplate($template = Rhema_Cache::CACHE_CLASS_FILE, $option = array()){
		$manager = Zend_Registry::get('cache-manager');
		if(!empty($option)){
			$manager->setTemplateOptions($template, $option);
		}
		return $manager->getCache($template);
	}

	public function getCached($tagged = null){
		if(defined('NO_CACHE') and NO_CACHE){
			return $this;
		}

		if(null == $this->_cache){
			$util = Rhema_Util::getInstance();
			$this->_cache = new Rhema_Cache($this, $util->getCacheOptions());
		}

		$this->_cache->setTagged($tagged);
		return $this->_cache;
	}
}