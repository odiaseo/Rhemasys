<?php
	class Rhema_Widget_Controller_Search extends Rhema_Widget_Abstract{

		const ALL = 'all';
		
	    public function indexMethod(){
	    	$search		= $this->_request->getParam('searchType');
	    	$return     = array();

	    	if($search){
	    		$search = strtolower($search);
	    		$name   = $search . 'Method';
	    		//$this->_helper->viewRenderer->setScriptAction($search);
	    		$return = $this->$name();
	    	}

	    	return $return;
	    }
 
		public function affiliateResultMethod(){ 
	    	$searchType   = $this->_request->getParam('searchType');
	    	$return       = array();
			$page 		  = $this->_request->getParam('page', 1);
			$perPage      = Rhema_SiteConfig::getConfig('settings.items_per_page');
			$prdObj       = Rhema_Model_Service::factory('affiliate_product');
			$pageData     = $this->_view->layout()->pageData;
			
			$return['searchType']    = $searchType ;
			$return['slugTitle']     = ''; 
 
	    	switch($searchType){
	    		case 'affiliate-brand':{
	    			$brandId    = 0;
	    			$desc       = '';
			    	$brandSlug  = $this->_request->getParam('brand');
 		
			    	if($brandSlug == self::ALL){
			    		$brand      = new Admin_Model_AffiliateProductBrand(); 
			    	}else{	    	
			    		$brdObj     = Rhema_Model_Service::factory('affiliate_product_brand');
			    		$brand      = $brdObj->getBrand($brandSlug, 'slug');
			    	}
			    	
	    			if($brand){
	    				$brandId     = $brand['id'] ;
	    				$brandTitle  = $brand['title'] ;
			    		Zend_Registry::set(Rhema_Constant::SEARCH_TERM_TITLE, $brandTitle . ' Brand'); 			    		
			    	}else{
			    		$brandTitle  = ucwords(str_replace('-', ' ', $brandSlug));
			    	}

			    	$pageDesc = "[site-name] brings you the best {$brandTitle} deals, discounts, offers and sales all in one place at a bargain - [page]!. 
			    	{$brand['description']}";
		 			$pageData['description'] = $pageDesc;
		 			if($brand['keywords']){
		 				$pageData['keyword']     = $brand['keywords'];
		 			}
					$this->_view->layout()->pageData = $pageData;						    	
			    	
			    	if($brandId){
			    		$return['brand']     = $brand;
			    		$return['paginator'] = $prdObj->getProductsByBrand($brandId, $perPage, $page); 
			    	}
			    	
			    	if(!$brandId or !count($return['paginator'])){
			    		$return['brand']     = array('title' => str_replace('-', ' ', $brandSlug));
			    		$return['similar']   = true;
			    		$return['paginator'] = $prdObj->findSimilarProducts($brandSlug, $perPage, $page);
			    	}   

			    	$return['slugTitle']     = $brand['title'];
			    	
	    			break;
	    		}
	    		
	    		case 'affiliate-category': {
	    			$catId = 0;
	    			$desc  = '';
			    	$categorySlug = $this->_request->getParam('category');
			    	$catModel     = Rhema_Model_Service::factory('affiliate_product_category');
			    	 
			    	if($categorySlug == self::ALL){
			    		$catObj   =  $catModel->getCategory(1); 
			    	}else{
			    		$catObj   = $catModel->getCategory($categorySlug, 'slug');
			    	}
			    	
			    	if($catObj){
			    		$catId    = $catObj['id'];
			    		$title    = $catObj['title'];			    		
			    		Zend_Registry::set(Rhema_Constant::SEARCH_TERM_TITLE, $catObj['title']); 
			    		$desc = $catObj['description'];
			    	} 			    	
			    	$desc = $desc ? $desc : $title = str_replace('-', ' ', $categorySlug);
			    	 	
			    	
			    	$pageData['meta_title'] = sprintf("%s discounts, deals and offers [page]", $title);			    			 		 
			    	$pageData['description'] = sprintf("Get the best %s discounts, %s deals and %s offers all in one place at a bargain! - [page]", $desc, $desc, $desc);
			    	
			    	if($catObj['keywords']){
		 				$pageData['keyword']     = $catObj['keywords'];
			    	}					
					$return['subList']       = array();		    	
			    	$return['category']      = $catObj;
			    	if($catId){ 
			    		$sourceList[]	     = $catId;    		
			    		list($subList, $listType)  = $catModel->getSubcategory($catId);
			    		$catsWithProduct     = (array) $prdObj->getProductStatList('categoryWithProducts');
 						$catList             = (array) $prdObj->getProductStatList('category');
 						
			    		foreach($subList as $k => $item){
			    			$subId = $item['id'];
			    			if(in_array($subId, $catsWithProduct)){
			    				$return['subList'][$subId]  = $catList[$subId];
			    				if($listType == Admin_Model_AffiliateProductCategory::DESCENDANTS){ 
			    					$sourceList[] = $subId;
			    				}
			    			}
			    		}
			    		if(count($return['subList'])){
				    		$container			     = $prdObj->getProductStatList('categoryMenu');
				    		if($container){
				    			$currentCrumb            = $container->findOneBy('title', $catObj['title']);
				    			$currentCrumb->setActive(true);				    		 
				    			$return['categoryMenu']  = $container ;
				    		}
			    		}
			    		$return['paginator']     = $prdObj->getProductsByCategory($sourceList, $perPage, $page);
			    	}
			    	
			    	if(!$catId or !count($return['paginator'])){ 
			    		$return['similar']     = true; 
			    		$return['category']    = array('title' => str_replace('-', ' ', $categorySlug));
			    		$return['paginator']   = $prdObj->findSimilarProducts($categorySlug, $perPage, $page);
			    	}
			    	
			    	$return['slugTitle']     = $catObj['title'];
			    	break;
	    		}
	    		    		
	    		case 'product-title-id':{
			    	$id	       = $this->_request->getParam('id'); 
			    	$title	   = $this->_request->getParam('title'); 

			    	$product   = Rhema_Model_Service::factory('affiliate_product')->getProductById($id);
			     
			    	if($product){
			    		$slug      = Doctrine_Inflector::urlize($product['title']);
			    		$slugTitle = Doctrine_Inflector::urlize($title);
			    		if($slug != $slugTitle){
							$broker = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
							$url    = $this->_view->url(array(
	    											'title'	=> $slug,
	    											'id'	=> $product['id']
	    											),'affiliate-product-detail', true, false);
							$broker->gotoUrlAndExit($url, array('code' => 301));			    			
			    		}
			    		$title = $product['title'];
			    		
			    		Zend_Registry::set(Rhema_Constant::SEARCH_TERM_TITLE, $title);
				    	if($product['description']){
				    		$pageData['description'] = $product['description'];
				    	}		 		 
				    	
				    	if($product['keywords']){
				    		$pageData['keyword']     = $product['keywords'];
				    	}
				    	$pageData['description']    .= ' [page]';			    		
			    	}
			    	
			    	Zend_Registry::set(Rhema_Constant::SEARCH_TERM_TITLE, $title);			    	    			
		 						
 					$catTitle				 = $product ? $product['brand'] . ' ' . $product['title'] : $title;
			    	$return['product']      = array('title' => $catTitle);
			    	
			    	if($product){
			    		$return['paginator']     = $prdObj->getProductsByTitle($product['title'], $perPage, $page);
			    	}
			    	
			    	if(!product or !count($return['paginator'])){
			    		$title = str_replace('-', ' ', $title);
			    		$return['similar']     = true;
			    		$return['paginator']   = $prdObj->findProductByKeyword($title, $perPage, $page);
			    	}
			        $pageData['meta_title'] = $return['slugTitle'] = sprintf("%s discounts and offers [page]", $title); 			
	    			break;
	    		}	    		    		
	    		case 'product-title':{
			    	$title	   = $this->_request->getParam('title');
			    	$title     = str_replace('-', ' ', $title);	 
			    	Zend_Registry::set(Rhema_Constant::SEARCH_TERM_TITLE, $title);	
			    			    	 
			    	$pageData['description'] = sprintf("Get the best %s discounts, deals and  offers at a bargain all in one place [page]!", $title);
			    	$pageData['meta_title']  = sprintf("%s discounts and deals [page]", $title); 
			 
			    	$return['slugTitle']     = $title;
			    	$return['product']       = array('title' => $title); 
			    	$return['paginator']     = $prdObj->findProduct($title, $perPage, $page);
			         			
	    			break;
	    		}

	    	
	    		case 'promotions':{
	    			$return['sales']         = true;
	    			$return['feedType']      = 'promotions';
	    			$return['slugTitle']     = '';
	    			$pageData['meta_title']  = "Latest [year] sales and promotions [page]";
			    	$pageData['description'] = "Get the latest [year] promotions and sale items all in one place! [page]"; 	
	    			$return['paginator']     = $prdObj->getPromotions($perPage, $page);
	    			break;
	    		}
	    		case 'promotion':{
			    	$id	       = $this->_request->getParam('id');
			    	$promo     = $prdObj->getOffer($id);
			    	 
			    	if($promo){
			    		Zend_Registry::set(Rhema_Constant::SEARCH_TERM_TITLE, $promo['title']); 
			    	}	
			    	if($promo['description']){
			    		$pageData['description'] = $promo['description'];
			    	}		 		 
			    	
			    	if($promo['keywords']){
			    		$pageData['keyword']     = $promo['keywords'];
			    	}		 						
 					$catTitle				 = $promo['title'];
			    	$return['category']      = array('title' => $catTitle);
			    	$return['paginator']     = $prdObj->getPromotions($perPage, $page);
			         			
	    			break;
	    		}	    		
 	
	    		    		
	    		case 'affiliate-manufacturer':{
	    			$id 		    = 0;
			    	$slug 		    = trim($this->_request->getParam('manufacturer'));
			    	$manufacturer   = Rhema_Model_Service::factory('affiliate_product_manufacturer')->getManufacturer($slug, 'slug');
			    	//$r = $prdObj->setProductStatList();
			    	//pd($r);
			    	if($manufacturer == self::ALL){
			    		$retailer = new Admin_Model_AffiliateProductManufacturer(); 
			    	}
			    	if($manufacturer){
			    		Zend_Registry::set(Rhema_Constant::SEARCH_TERM_TITLE, $manufacturer['title'] . ' Manufacturer');
			    		$id = $manufacturer['id'] ;
			    	}	
			    	if($manufacturer['description']){
			    		$pageData['description'] = $manufacturer['description'];
			    	}		 		 
			    	
			    	if($manufacturer['keywords']){
			    		$pageData['keyword']     = $manufacturer['keywords'];
			    	}		 						

			    	$pageData['description']    .= ' [page]';
			    	
			    	$return['manufacturer']  = $manufacturer;
			    	if($id){
			    		$return['paginator']     = $prdObj->getProductsByManufacturer($id, $perPage, $page);
			    	}
			    	if(!$id or !count($return['paginator'])){			    		
			    		$return['similar']      = true; 
			    		$return['manufacturer'] = array('title' => str_replace('-', ' ', $slug));
			    		$return['paginator']    = $prdObj->findSimilarProducts($slug, $perPage, $page);
			    	}

			    	$return['slugTitle']     = $manufacturer['title'];
	    			break;
	    		}	    		    			    		
	    		case 'affiliate-retailer':{
	    			$id 		    = 0;
			    	$slug 		    = trim($this->_request->getParam('retailer'));
			    	$term 		    = str_replace('-',' ', $slug);
			    	$retailerObj    = Rhema_Model_Service::factory('affiliate_retailer');
			    	$retailer       = $retailerObj->getRetailer($slug, 'slug');	
			    	
			    	if(!is_numeric($page)){
			    		$url = $this->_view->url(array('retailer' => $slug), 'affiliate-retailer', true);
			    		$broker = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
    					$broker->gotoUrlAndExit($url, array('code' => 301));	  
			    	}		    	
 

			    	if($retailer == self::ALL){
			    		$retailer = new Admin_Model_AffiliateRetailer(); 
			    	}
			    	if($retailer){
			    		Zend_Registry::set(Rhema_Constant::SEARCH_TERM_TITLE, $retailer['title'] . ' Retailer');
			    		$pageData['meta_title'] = sprintf("%s Discounts, deals, sales and offers [page]", $retailer['title']); 
			    		$id = $retailer['id'] ;
			    	}	
			    	if($retailer['description']){
			    		$pageData['description'] = $retailer['description'];
			    	}		 		 
			    	
			    	if($retailer['keywords']){
			    		$pageData['keyword']     = $retailer['keywords'];
			    	}		 		
			    					
					$pageData['description']    .= ' [page]';							    	
			    	$return['retailer']          = $retailer;
			    	
			    	if($id){
			    		$masterStoreId           = $retailer['affiliate_retailer_id'] ? $retailer['affiliate_retailer_id'] : false;
			    		if($masterStoreId){
			    			$dataSource				 = $masterStoreId ? array($masterStoreId, $id) : $id;
			    		}else{
			    			$clones = $retailerObj->getCloneRetailerIds($id);
			    			if(count($clones)){
			    			    array_push($clones, $id);
			    				$dataSource = $clones;
			    			}else{
			    				$dataSource	= $id;
			    			}
			    		}
			    		$return['paginator']     = $prdObj->getProductsByRetailer($dataSource, $perPage, $page);
			    	}else{
			    		
			    		$return['slugTitle']     = $term;
			    		$return['paginator']     = $prdObj->findProductByKeyword($term, $perPage, $page);			    		
			    	}
	    					    	
			    	if(!$id or !count($return['paginator'])){
			    		$return['similar']     = true; 
			    		$return['search']      = true;
			    		$return['slugTitle']   = $term;
			    		//$return['retailer']    = array('title' => str_replace('-', ' ', $slug));
			    		$return['paginator']   = $prdObj->findSimilarProducts($slug, $perPage, $page);
			    	}else{		
			    		$return['slugTitle']       = $retailer['title'];	
			    	}         			
	    			break;
	    		}
	    		case 'keyword':
	    		case 'keywordSearch':{
	    			$keywords    = $this->_request->getParam('keyword');
	    			$routeName   = Zend_Controller_Front::getInstance()->getRouter()->getCurrentRoutename();
	    			
	    			if($routeName == 'deal-search-text'){ // redirect improper search formats indexed by google
    					$url   = $this->_view->url(array('keyword' => $keywords), 'deal-search', true);
    					$broker = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
    					$broker->gotoUrlAndExit($url, array('code' => 301));	    				
	    			}
	    			
	    			$keywords = str_replace('-', ' ',$keywords);	
	    			$return['slugTitle']     = $keywords ;    
	    			//pd($return['slugTitle']);			
	    			Zend_Registry::set(Rhema_Constant::SEARCH_TERM_TITLE, $keywords);
	    			
	    			$pageData['meta_title']  = sprintf("%s discounts, deals and offers [page]", $keywords); 
	    			
	    			$return['search']		 = true ;
	    			$return['keywords']      = array('title' => $keywords);
	    			$return['page']			 = $page ;
	    			$return['paginator']     = $prdObj->findProductByKeyword($keywords, $perPage, $page);
	    			//$pageData['description'].= ' [page]' ;

	    			//if(true){
	    			if(!count($return['paginator'])){
			    		//$return['similar']     = true; 
			    		//$return['paginator']   = $prdObj->findProductByKeyword($keywords, $perPage, $page);
			    		$return['paginator']   = $prdObj->searchProductIndex($keywords, null, $perPage, $page);			    		
			    		if(!count($return['paginator'])){
			    			$return['similar']     = true;
			    			$return['paginator']   = $prdObj->getVouchers($perPage, $page);
			    		}
			    		 
	    			
			    	}	    			  
	    			break;
	    		}
	    		case 'tags':{
	    			$tag 					 = $this->_request->getParam('tag');
	    			$return['tag']           = array('title' =>$tag);
	    			$return['slugTitle']     = $tag ;
	    			Zend_Registry::set(Rhema_Constant::SEARCH_TERM_TITLE, $tag);
	    			$pageData['meta_title']  = sprintf("%s discounts, deals, %s sales & offers [page]", $tag, $tag); 
	    			$return['paginator']     = $prdObj->findProductByKeyword($tag, $perPage, $page);
	    			
	    			break;
	    		}
	    		case 'contract':{
	    			$return['paginator']     = $prdObj->listContractMobileDeals($perPage, $page); 
	    			break;
	    		}
	    		
	    		case 'payg':{
	    			$return['paginator']     = array();
	    			break;
	    		}
	    		case 'product-filter':{	 	   
	    			$data = $this->_request->getParams(); 						
	    			list($return['paginator'], $meta) = $prdObj->getFilteredSearch($data, $perPage, $page);
	    			$meta                    = (array) $meta ;
	    			$metaStr 			     = implode(', ', $meta);
	    			$return['meta']          = $metaStr;
	    			$return['category']      = false ;
	    			$pageData['description'] = sprintf("Best product deals, discounts and offers %s [page]", $metaStr);
	    			Zend_Registry::set(Rhema_Constant::SEARCH_TERM_TITLE, $metaStr);
	    			
	    			break;
	    		}
	    		default:{ 		    				    	
			    	$return['paginator']     = $prdObj->getProductsByCategory(null, $perPage, $page);	    			
	    		}
	    	}
 
	    	$this->_view->layout()->pageData = $pageData;
	    	return $return;			
		} 
 
    
	    public function affiliateMethod(){
	    	$form   = new Rhema_Form_Search_AffiliateFeed();
	    	$post = $this->_request->getPost();
	     
	    	if($form->isRhemaButtonSubmitted()){	    			    		
	    		if(!$form->isValid($post)){ 
					$msg = new Rhema_Dto_UserMessageDto($form->getMessages(), 'Search', Rhema_Dto_UserMessageDto::TYPE_ERROR);
					$flash = Zend_Controller_Action_HelperBroker::getStaticHelper('flashMessenger');
					$flash->addMessage($msg);	
					$gotoUrl = $this->_request->getServer('HTTP_REFERER');    			
	    		}else{	    
	    			$keyword     = Rhema_Util_String::filterSearchTerms($form->getValue('keyword'));
	    			$searchRoute = Rhema_SiteConfig::getConfig('settings.default.search_route');
	    			$params      = array('category' => 'search', 
		    							 'page'     => $this->_request->getParam('page'),
		    							 'keyword'  => preg_replace('/[\s]+/','-', $keyword)
	    						  );
	    			$gotoUrl  = $this->_view->url($params, $searchRoute);	    			 
	    		}		
	    				 			
	    		$red = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');  
		 		$red->gotoUrlAndExit($gotoUrl );	
	 						
	    	} 
	    	 
	    	//$form->getElement(get_class($form))->setLabel('');
	    	$keyword = 	  $this->_request->getParam('keyword');
	    	if($keyword){ 		    		    	
	    		$form->getElement('keyword')->setValue($keyword);
	    	}
	    			    	
	    	$return['searchForm'] = $form;
		    return $return;		    	
	    }
	    
	    public function standardMethod(){
	    	$form   = new Rhema_Form_Search_Simple();
	    	$post = $this->_request->getPost();
	     
	    	if($form->isRhemaButtonSubmitted()){	    			    		
	    		if(!$form->isValid($post)){ 
					$msg = new Rhema_Dto_UserMessageDto($form->getMessages(), 'Search', Rhema_Dto_UserMessageDto::TYPE_ERROR);
					$flash = Zend_Controller_Action_HelperBroker::getStaticHelper('flashMessenger');
					$flash->addMessage($msg);	
					$gotoUrl = $this->_request->getServer('HTTP_REFERER');    			
	    		}else{	    
	    			$keyword     = $form->getValue('keyword');
	    			$searchRoute = Rhema_SiteConfig::getConfig('settings.default.search_route');
	    			$params      = array('category' => 'search', 
		    							 'page'     => $this->_request->getParam('page'),
		    							 'keyword'  => Doctrine_Inflector::urlize($keyword)
	    						  );
	    			if($searchRoute){
	    				$gotoUrl  = $this->_view->url($params, $searchRoute);
	    			}else{
	 		    		$gotoUrl = $this->_view->url($params, 'portfolio-search');
	    			}
	    		}		
	    				 			
	    		$red = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');  
		 		$red->gotoUrlAndExit($gotoUrl );	
	 						
	    	} 
	    	 
	    	$form->getElement(get_class($form))->setLabel('');	   		    		    	
	    	$form->getElement('keyword')
	    		 ->setValue($this->_request->getParam('keyword'), '');
	    			    	
	    	$return['searchForm'] = $form;
		    return $return;		    	
	    }
	    
	    public function categoryMethod(){
	    	$slug 	 			= $this->_request->getParam(CATEGORY_MAP);
	    	$page    			= $this->_request->getParam('page', 1);
	    	$limit   			= $this->_utility->getSetting(1);
	    	//$search				= $this->_request->getParam('searchType');

	    	//$url				= $this->_utility->assemble(array(CATEGORY_MAP => $slug, 'page'=> $page), CATEGORY_ROUTE);
	    	$url				= '/category/' . $slug;

	    	$display_type       = $this->_request->getParam('display_type', 3);
	    	$templateId		    = $this->_utility->getSessData('display_template_id_' . $display_type);
	    	$displayTemplate    = $this->getCached('displaytemplate')->getDisplayTemplate($templateId, $display_type);

	    	$pageLayout   		= Ecom_Model_EcomProductCategory::getSearchLayout($slug, CATEGORY_MAP, $limit, $page, $url);

	    	$pager			 				= $pageLayout->getPager();
	    	$return['displayTemplate']    	= $displayTemplate;
	    	$return['products']				= $pager->execute();
	    	$return['nav'] 					= $pageLayout;

	    	return $return;

	    }

	    public function branchMethod(){
	    	$slug 					= $this->_request->getParam(CATEGORY_MAP);
	    	$page    				= $this->_request->getParam('page', 1);
	    	$display_type 			= 2;
	    	$children				= array();
	    	$url					= '/branch/' . $slug;

	    	$templateId		    	= $this->_utility->getSessData('display_template_id_' . $display_type);
	    	$displayTemplate    	= $this->getCached('displaytemplate')->getDisplayTemplate($templateId, $display_type);
    	    $pageLayout 	    	= Ecom_Model_EcomNavigationMenu::getSubCategoryLayout($slug, $page, $url);

	    	$pager			 		= $pageLayout->getPager();
	    	$res					= $pager->execute();
	    	$category 				= $res[0];

			if($category){
				$node     			= $category->getNode();
				$children 			= $node->getChildren();
			}

	    	$return['category']				= $children;
	    	$return['nav'] 					= $pageLayout;
	    	$return['displayTemplate']    	= $displayTemplate;

	    	return $return;
	    }

	    public function getDisplayTemplate($templateId, $displayType){
	    	//return Ecom_Model_EcomAttribute::getAttributeTemplates($templateId, $displayType);
	    	return Ecom_Model_EcomTemplateAttribute::getAttributes($templateId, $displayType);
	    }

	    public function productMethod(){
	    	$productId				= $this->_request->getParam('product_id');
	    	$model					= ECOM_PREFIX . 'EcomProduct';

	    	$display_type 			= 1;
	    	$templateId		    	= $this->_utility->getSessData('display_template_id_' . $display_type);
	    	$displayTemplate    	= $this->getCached('displaytemplate')->getDisplayTemplate($templateId, $display_type);
 
	    	$return['displayTemplate']    = $displayTemplate;
	    	$return['product'] 			  = Doctrine_Core::getTable($model)->find($productId);

			return $return;
	    }

	    /**
	     * Perform site search using google' search api
	     */

	    public function siteresultMethod(){
	    	$return            = array();
	    	$search            = $this->_request->getParam('search', null);

	    	if($search !== null){
		    	$siteParam		   = null ;
		    	$keyword           = $this->_request->getParam('keyword', null);
		    	$keyword           = strip_tags(html_entity_decode(urldecode($keyword)));
	    	}

	    	return $return ;
	    }

	    public function googleSearchMethod(){
	    	$return            = array();
	    	$search            = $this->_request->getParam('search', null);

	    	if($search !== null){
		    	$siteParam		   = null ;
		    	$keyword           = $this->_request->getParam('keyword', null);
		    	$keyword           = strip_tags(html_entity_decode(urldecode($keyword)));

		    	if($keyword){
		    		$url 			   = 'http://ajax.googleapis.com/ajax/services/search/local';
			    	$vars 			   = array('v' 	=> '1.0',
			    							'q'		    => $keyword ,
			    							'key'	    => $this->_apikey ,
			    							'userip'	=> $this->_utility->getClientIp()
				    				) ;
				    $url  			  .= '?' . http_build_query($vars);

		      		$config = array(
		          				'adapter'     => 'Zend_Http_Client_Adapter_Curl',
		          				'curloptions' => array(
		      											CURLOPT_RETURNTRANSFER  => 1,
		      											CURLOPT_REFERER			=> $this->_request->getServer('REQUEST_URI')
		      										)
		      				);

		      		$client = new Zend_Http_Client($url, $config);
		      		$resp	= $client->request('GET');
		      		$body   = $resp->getBody();
		      		$return = Zend_Json::decode($body, true);
		    	}
	    	}
			return $return;

	    }
 
	}