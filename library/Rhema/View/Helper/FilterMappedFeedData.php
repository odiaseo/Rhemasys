<?php 

/**
 * Filter feed data based on mapped data to database and set filters
 * @author odiaseo
 *
 */
class Rhema_View_Helper_FilterMappedFeedData extends Zend_View_Helper_Abstract {
	protected $_filter;
	
	public function __construct(){		
		$this->_filter = new Rhema_Util_FeedFilter();
	}
	
	public function filterMappedFeedData($col, $data, $mapping, $values = array()){
		 $data	= html_entity_decode($data);
		 //$data  = Rhema_Util_String::correctEncoding($data)	 ;
	     if($data and isset($mapping[$col]['filters']) and $mapping[$col]['filters']){
	        	foreach((array)$mapping[$col]['filters'] as $method){
	        		$data = $this->_filter->{$method}($data, $col, $values);
	        	}
	     }
	     return $data ;
	}
}