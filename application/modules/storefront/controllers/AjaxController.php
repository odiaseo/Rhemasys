<?php
class AjaxController extends Zend_Controller_Action{
	private $_config;
 
 
	public function init(){
		parent::init();
		$this->_config  = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();
		$this->_utility = Rhema_Util::getInstance();
		

		$manager = Doctrine_Manager::getInstance();
		$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_AGGRESSIVE);	
		
		$this->_helper->cache(array('get-autocomplete-list','vouchers-by-retailer'));	 
	}

	public function preDispatch(){
		//$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	}

	public function enableAdultAction(){
		$namespace        		= $namespace = Zend_Registry::get('namespace' );
		$namespace->viewAdult   = true;
 		$this->_helper->json->sendJson(1);	
	}
	
	public function dirListAction(){
		$path = '/';
		$parms = $this->_request->getPost();
		$dirOnly = $this->_request->getPost('d', false);
		$actions = $this->_request->getPost('a', false);
		$module = $this->_request->getPost('m', null);
		$contr = $this->_request->getPost('c', null);

		if($module){
			$path .= 'modules/' . $module . '/views/scripts/';
		}
		if($contr){
			$path .= $contr . '/';
		}

		$arr = $this->_utility->getCached()->getDir($path, $dirOnly, $actions, false);
		$this->_helper->json->sendJson($arr);

	}
  
 
	public function widgetAction(){
		$options = $this->_utility->buildWidgetList(new DirectoryIterator(WIDGET_PATH . '/Controller'));
		$return  =  $this->view->formSelect('widget', '', null, $options);
		$this->_utility->setAjaxData($return);
	}
 
	public function pageMetaAction(){
	    $pageId   = $this->_request->getParam('pageId', null);
	    $task     =  $this->_request->getParam('task');
	    $data     = '';
	    $message  = '';
	    $pageData = '';
	    $error    = 0;

	    $form     = new Rhema_Form_EditMeta();
	    $obj      = Rhema_Model_Service::factory('page');

	    if($pageId){
	        switch($task){
	            case 'update':{
	                    $param = $this->_request->getQuery();
	                    if($form->isValid($param)){
	                        $values = $form->getValues();
	                        $done   = $obj->updatePageMetaData($pageId, $values);

	                        $userMessage    = new Rhema_Dto_UserMessageDto('Page meta data updated successfully', 'Page Update',
	                                                Rhema_Dto_UserMessageDto::TYPE_SUCCESS );
	                        $message = $this->view->printUserMessage($userMessage);

	                    }else{
	                        $error  = 1;
	                        $userMessage    = new Rhema_Dto_UserMessageDto($form->getMessages(), 'Page Update',
	                                                Rhema_Dto_UserMessageDto::TYPE_ERROR );
	                        $message = $this->view->printUserMessage($userMessage);
	                    }
	                break;
	            }

	            case 'getmeta':
	            default: {

	                $pageData = $obj->getPageById($pageId);


	                $url      = $this->view->url(array('pageId' => $pageId, 'task' => 'update'), 'manage-page-meta' );
	                $form->setAction($url);
	                $form->populate($pageData);
	                $data     = $form->render();
	            }
	        }
	    }
	    $return['data']  = $data;
	    $return['msg']   = $message ;
	    $return['error'] = $error;
	    $return['page']  = $pageData;

	    $this->_helper->json->sendJson($return);
	}
	
 
	
	/**
	 * build autosuggest lit for autocomplete search
	 */
	public function getAutocompleteListAction(){
		$filter  = $this->_request->getParam('type', 'product');
		$cacheId = 'ajax_' . $filter . '_' . md5('autocomplete' . $filter);
		$cache   = Rhema_Cache::getStatCache();
		
		if(!$cache->test($cacheId)){ 
			if($filter == 'all'){
				$products = $this->_helper->getHelper('suggestList')->listAll();;
			}else{
				$products = $this->_helper->suggestList($filter);	
			}		
	    	$cache->save($products, $cacheId, array('autocompletecached'), 144000);
		}else{
			$products = $cache->load($cacheId);
		}
 
		//$data = Zend_Json::encode($products);
		//Rhema_Util::setAjaxData($data);		
		$this->_helper->json->sendJson($products); 
	}

 
	public function suggestAffiliateProductAction(){ 
		$searchTerm = $this->_request->getParam('term');
		$perPage    = Rhema_SiteConfig::getConfig('settings.items_per_page');
		$prd        = Rhema_Model_Service::factory('affiliate_product');
		$filter     = $this->_request->getParam('filter', 'product');
		$searchTerm = trim(strip_tags($searchTerm));
		$list       = array(); 
 
		switch($filter){
			case 'retailer' :{
				$retailer    = Rhema_Model_Service::factory('affiliate_retailer');
			    $list        = $retailer->searchRetailerIndex($searchTerm);				
				break;
			}
			
			case 'category':{
				$category    = Rhema_Model_Service::factory('affiliate_product_category');
			    $list        = $category->searchCategoryIndex($searchTerm);				
				break ;
			}
			case 'voucher':{
			    $list  = $prd->searchProductIndex($searchTerm, Admin_Model_AffiliateProductType::TYPE_VOUCHER); 		
				break;
			}
			case 'product':
			default:{	 
				try{
					$list   = $prd->findProduct($searchTerm, $perPage, 1, true);
				}catch(Exception $e){
					if(Rhema_SiteConfig::isDev()){
						pd($e->getMessage());
					}elseif(Zend_Registry::isRegistered('logger')){
						$logger = Zend_Registry::get('logger');
						$log->debug($e->getMessage());
					}
				}
 
				if(!$list or !count($list)){
					$list  = $prd->searchProductIndex($searchTerm); 
					//pd($list);
					//$list   = $prd->getProductsByTitle($searchTerm, $perPage, 1);
				}			
			}
		}
		
		$products   = Rhema_Util_String::buildAutocompleteObject($list, $searchTerm, $filter);  
		$this->_helper->json->sendJson($products);
 
	}
	
 	public function vouchersByRetailerAction(){
 		$this->_helper->viewRenderer->setNoRender(false);
 		$limit  			    = 10 ;
 		$retailerId 			= $this->_request->getParam('retailerId');
 		$productObject			= Rhema_Model_Service::factory('affiliate_product');
 		$product    			= $productObject->getVouchers($limit, 1, null, $retailerId);

 		if(!count($product)){
 			$product = $productObject->getVouchersGroupedByRetailer($limit);
 			$found = false ;
 		}else{
 			$found = true;
 		}
 		
 		$this->view->found		= $found ;
 		$this->view->product    = $product ;
 		$this->view->retailer   = Rhema_Model_Service::factory('affiliate_retailer')->getRetailer($retailerId);								
 		
 		
 		if(!$this->_request->isXmlHttpRequest()){ 
 			$retailerId = $this->_request->getParam('slug');
 			$feedUrl    = $this->view->url(array('type' => 'vouchers', 'slug' => $retailerId), 'affiliate-feed', false, true);
 			$this->_redirect($feedUrl); 
 		} 
 	}

}