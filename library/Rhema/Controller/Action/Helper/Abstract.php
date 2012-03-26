<?php
/*class Zend_Controller_Action_Helper_Abstract extends Zend_Controller_Action_Helper_Abstract 
		implements Rhema_Cache_Interface {
	
	public function setCache(Rhema_Cache_Abstract $cache) {
		$this->_cache = $cache;
	}

	public function setCacheOptions(array $options) {
		$this->_cacheOptions = $options;
	}

	public function getCacheOptions($type = 'class-file', $obj = null, $lifetime = 31104000) {
		if (empty ( $this->_cacheOptions [$type] )) {
			$util = Rhema_Util::getInstance();
			$this->_cacheOptions [$type] = $util->getCacheOptions($type, $obj, $lifetime); 
		}
		return $this->_cacheOptions [$type];
	}

	public function getCached($tagged = null) {
		if (defined ( 'NO_CACHE' ) and NO_CACHE) {
			return $this;
		}

		if (null == $this->_cache) {
			$this->_cache = new Rhema_Cache ( $this, $this->getCacheOptions () );
		}

		$this->_cache->setTagged ( $tagged );
		return $this->_cache;
	}
}*/