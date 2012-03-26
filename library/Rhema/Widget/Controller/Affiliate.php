<?php
class Rhema_Widget_Controller_Affiliate extends Rhema_Widget_Abstract {
     
	protected $_prodStat ;
	
 
	public function rotatingBannerMethod($width = 300, $height = 250){	
		$return['pubId']     = Rhema_SiteConfig::getConfig('settings.google_publisher_id');
		$return['channelId'] = Rhema_SiteConfig::getConfig('settings.google_adsense_channel');
		$return['banners']   = Rhema_Model_Service::factory('affiliate_banner')->getBanners($width, $height);
		//pd($return['banners'] );
		return $return;
	}
    /**
	 * @return the $_prodStat
	 */
	public function getProdStat() {
		if(!$this->_prodStat){
    		$this->_prodStat   = Admin_Model_AffiliateProduct::getProductStatList();			
		}
		return $this->_prodStat;
	}

	/**
	 * @param field_type $_prodStat
	 */
	public function setProdStat($_prodStat) {
		$this->_prodStat = $_prodStat;
	}

	public function categoryListMethod(){    	
    	if(!isset($this->_view->prodCache)){
    		$return['stat']   = $this->getProdStat(); 
    	}else{
    		$return   = array();
    	}
    	$return      += $this->rotatingBannerMethod();
    	
/*    	if(!$return['categoryMenu']){
    		$menuModel      = 'Admin_Model_AffiliateProductCategory';
    		$category = $return['category'];
			$return['categoryMenu']  = Admin_Model_AdminMenu::getTreeByRootId(1, $menuModel, $category);
    	}*/
		
    	return $return ;
    }
    
    public function offerDetailMethod(){
    	$offer 			    = array();
    	$productId          = $this->_request->getParam('id');
    	$model			    = Rhema_Model_Service::factory('affiliate_product');
    	$prdObj 		    = $model->getOffer($productId); 
    	$routeName          = Zend_Controller_Front::getInstance()->getRouter()->getCurrentRoutename();
		$pageData           = $this->_view->layout()->pageData;
		$title              = $title = $this->_request->getParam('title', ''); 
    		
		$page 		  = $this->_request->getParam('page', 1);			
    	$perPage      = Rhema_SiteConfig::getConfig('settings.items_per_page');	
    	 	
    	if(!$prdObj and $title){ 
    		$prdObj   = $model->findProductByTitle($title,$page, $perPage, true);    		 
    	}	
  	
    	if($prdObj){    		
    		$slug      = Doctrine_Inflector::urlize($prdObj['title']);
			$slugTitle = Doctrine_Inflector::urlize($this->_request->getParam('title'));
			if($slug != $slugTitle){
				$broker = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
				$url    = $this->_view->url(array(
    											'title'	=> $slug,
    											'id'	=> $prdObj['id']
    											),'affiliate-product-detail', true, false);
				 $broker->gotoUrlAndExit($url, array('code' => 301));			    			
		    }
			    		
    		$offer          		 = $prdObj;    		   		
			$pageData['description'] = $offer['description']; 
			$pageData['keyword']     = $offer['keywords']; 		
			$pageData['title']       = $offer['title'] ;		
						 
    	}else{  
    		$url   = $this->_view->url(array('keyword' => $title), 'deal-search', true);
    		$broker = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
    		$broker->gotoUrlAndExit($url, array('code' => 301));
    		exit;
    	}
    	
    	if($this->_request->getParam('searchType') == 'promotion'){
    		$return['paginator']     = $model->getPromotions($perPage, $page);
    	}else{
    		$return['paginator']     = $model->getProductsByCategory($offer['affiliate_product_category_id'], $perPage, $page, false, $prdObj['id']);
    	}
    	//$return['paginator']     = $model->findProductByTitle($offer['title'], $page, $perPage);
    	$return['offer']         = $offer; 
    	//$return['similar']       = true ;
    	$this->_view->layout()->pageData = $pageData;
    	
    	return $return ;    	
    }
    
    /**
     * List offers with promotions
     */
    public function promotionMethod(){  
    	$limit              =  Rhema_SiteConfig::getConfig('settings.items_per_page');
    	//$return['featured'] = Rhema_Model_Service::factory('affiliate_product')->getTopProducts(40);
    	$return['featured'] = Rhema_Model_Service::factory('affiliate_product')->getFeaturedOffersWithPromotion($limit);     
    	//$return['form']     = $this->_view->getProductFilters() ; 
    	return $return ;    		
    }
    
    
    public function tagCloudMethod(){
    	$page     = $this->_request->getParam('page',1);
		$product  = Rhema_Model_Service::factory('affiliate_product');
		$return['paginator']      = $product->getTagListPaginator($page);    
		return $return;	
    }
    
    public function retailerListingMethod(){
    	$return['stat']   = $this->getProdStat(); 
    	$letter   = $this->_request->getparam('letter','a');
    	$letter   = ($letter == 'all') ? '' : $letter ;
    	
    	$return['currentLetter'] = $letter ;    	 
    	$return['byLetter']      = Rhema_Model_Service::factory('affiliate_retailer')->getRetailerStartingWith($letter);
//pd($return['byLetter']);
    	return $return;
    }
    
    public function productCategoryMethod(){ 	
    	return $this->_getAlphaList('category', 'affiliate_product_category');
    }
    
    public function productBrandMethod(){
    	return $this->_getAlphaList('brands', 'affiliate_product_brand');    	
    } 
      
    public function offerCountMethod(){
    	$total           = (int) Admin_Model_AffiliateProduct::getProductStatList('activeCount'); 
    	if(!$total){
    		$total       = Rhema_Model_Service::factory('affiliate_product')->getTotalActiveProducts();
    		Admin_Model_AffiliateProduct::saveStatOption(array('activeCount' => $total));
    	}
    	$return['total'] = Zend_Locale_Format::getNumber($total);
    	return $return;
    }
        
    protected function _getAlphaList($section, $model){
    	//Rhema_Model_Service::factory('affiliate_product')->countProductsByBrand();
    	$return['stat'] = $this->getProdStat();
    	$letter         = $this->_request->getparam('letter','a');
    	$return['currentLetter'] = $letter ;
    	$method = 'list' . ucfirst($section);
    	$letter = ($letter == 'all') ? '' : $letter ;
    	$route  = Zend_Controller_Front::getInstance()->getRouter()->getCurrentRoutename();

 		    		
    	$list   = Rhema_Model_Service::factory($model)->{$method}($letter);   		  		
 
    	    		
    	$num      = floor(count($list)/30);
    	$moreCols = $num - ($num%3) ;
    	$return['columns'] = 3 + $moreCols;	
    		
		$return[$method] = $list ;
    	return $return   	;
    }
    
    public function filterFormMethod(){
    	$return['form']     = $this->_view->getProductFilters() ; 
    	return $return ;     	
    }

}