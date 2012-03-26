<?php
class Rhema_Adapter_Paginator implements Zend_Paginator_Adapter_Interface{
	 
	protected $_daoFilter; 
	protected $_count = null;
	
	public function __construct(Rhema_Dao_Filter $filter){ 
		$this->_daoFilter   = $filter ;  
	}
	
	public function count(){ 	
		if(null === $this->_count){
			$this->_count   = $this->_getCachedResult();
		}
		return $this->_count ;	 
	}
	
	public function getItems($offset, $itemCountPerPage){ 
		$filter = clone $this->_daoFilter ;
		$filter->setLimit($itemCountPerPage)
			   ->setOffset($offset); 	
		return Rhema_Model_Service::createQuery($filter)->execute(); 
	}
	
	protected function _getCachedResult(){
		$filter    = clone $this->_daoFilter ;		
		$cache     = Rhema_Cache::getDefaultCache();
		$cacheId   = md5(serialize($filter));	
		if($cache->test($cacheId))	{
			return $cache->load($cacheId);
		}else{
			$res =  Rhema_Model_Service::createQuery($filter)->count();
			$cache->save($res, $cacheId, array(__CLASS__));
			return $res ;
		}
	} 
}