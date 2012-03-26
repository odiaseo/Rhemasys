<?php 
class Rhema_Controller_Action_Helper_SuggestList extends Zend_Controller_Action_Helper_Abstract{
	
	public static $cachedData = false;
	public $filters = array('product'  => 'Products',
										 'voucher'  => 'Vouchers',
									     'retailer'	=> 'Retailers',
										 'brand'    => 'Brand',
										 'category' => 'Category'	
									);
	
	public function __construct(){
		//self::$cachedData = Admin_Model_AffiliateProduct::getProductStatList();
	}
	
	public function direct($filter){
		return $this->getProductList($filter);
	}
	
	public function suggestList($filter){
		return $this;
	}
	
	public function getProductList($filter){
		$list       = array();
		$cachedData = self::getCachedData();
		if($filter == 'voucher'){
			$list    =  Rhema_Model_Service::factory('affiliate_product')->getVouchers(null); 
		}elseif($filter == 'retailer'){			
			$list    = (isset($cachedData['retailers']) and $cachedData['retailers']) 
					   ? $cachedData['retailers'] 
					   : Rhema_Model_Service::factory('affiliate_retailer')->listRetailers();   
		}elseif($filter == 'brand'){	
			$list    = (isset($cachedData['brands']) and $cachedData['brands'] )
					   ? $cachedData['brands'] 
					   : Rhema_Model_Service::factory('affiliate_product_brand')->listBrands(); 
					  
		}elseif($filter == 'category'){
			$list    = (isset($cachedData['category']) and  $cachedData['category'])
					   ? $cachedData['category']  
					   : Rhema_Model_Service::factory('affiliate_product_category')->listCategory();				
		}
		$products    = Rhema_Util_String::buildAutocompleteObject($list, '', $filter );
		
		return $products ;		
	}
 
	public function listAll(array $items = null){
		//$items   = $items ? $items : array_keys($this->filters);
		 
		foreach($this->filters as $filter => $title){
			if(in_array($filter, $items)){
				$data[$filter] = $this->getProductList($filter);
			}else{
				$data[$filter] = array();
			}
		}	
	 
		return $data;
	}
	/**
	 * @return the $filters
	 */
	public function getFilters() {
		return $this->filters;
	}
	/**
	 * @return the $cachedData
	 */
	public static function getCachedData() {
		if(!self::$cachedData){
			self::$cachedData = Admin_Model_AffiliateProduct::getProductStatList();
		}
		return Rhema_Controller_Action_Helper_SuggestList::$cachedData;
		 
	}


}