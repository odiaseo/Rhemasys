<?php

class IndexController extends Zend_Controller_Action
{
	
    public function init(){
    	/* Initialize action controller here */
		parent::init();
		if(Rhema_SiteConfig::isProd()){		 
		 	$this->_helper->cache(array('affiliate-feed', 'feed'), array('feeds'), '.xml');
		 	$this->_helper->cache(array('index'), array('indexAction'));
		}
     }
 
    public function indexAction(){
        $request     = $this->getRequest();
     	$action      = $request->getActionName();
 		$actCode     = $this->_getParam('action',$action);
    }


    public function searchAction(){
    	Zend_Registry::set('page-params',$this->getRequest()->getParams());
    	$this->_helper->viewRenderer->setScriptAction('index');
    }


    public function __call($method, $arg){
    	if('Action' == substr($method, -6)){
    		$this->_helper->viewRenderer->setScriptAction('index');
    		call_user_func_array(array($this, 'indexAction'), $arg);
    	}
    }

 		
	public function outlinkAction(){
		$prodId     = $this->_request->getParam('id');
		$title      = $this->_request->getParam('title', ''); 
		$prodObject = Rhema_Model_Service::factory('affiliate_product'); 
		$prod       = $prodObject->getProductDeepLink($prodId);
		
		if($prod){
			$testTitle = Doctrine_Inflector::urlize($prod['title']);
			if($testTitle == $title){
				if($prod['deeplink']){
					$deeplink = $this->view->affiliateLink()->getDeepLink($prod);
					$this->_helper->redirector->gotoUrlAndExit($deeplink);
				} 
			}elseif(false){
				$reverseTitle = str_replace('-', ' ', $title);
				//$product = $prodObject->getAlternativeProductsByTitle($reverseTitle);
				$product = $prodObject->findProductByTitle($title, 1, 1, true); 
				if($product and $product['deeplink']){ 
					$deeplink = $this->view->affiliateLink()->getDeepLink($prod, '', "out{$product['id']}");
					$this->_helper->redirector->gotoUrlAndExit($deeplink); 
				}
			} 
		}
						
		$url   = $this->view->url(array('keyword' => $title), 'deal-search', true);
		$this->_helper->redirector->gotoUrlAndExit($url, array('code' => 301));			
	}
    
	public function affiliateFeedAction(){
		$type = $this->_request->getParam('type'); 		
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$config   = Rhema_Util::getSessData(Rhema_Constant::SITE_CONFIG_KEY);
		$siteData = $config['subsite'];
		$siteLink = 'http://' . $this->_request->getHttpHost();		
		
		$dto = new Rhema_Dto_FeedDto();
		
		switch($type){

			
			case 'category':{
				$title = 'Discounts, Deals and Offers - Product Categories';
				$items = Rhema_Model_Service::factory('affiliate_product_category')->listCategory();
				break ;
			}
			
			case 'retailer':{
				$title = 'Discounts, Deals and Offers - Merchants';
				$items = Rhema_Model_Service::factory('affiliate_retailer')->listRetailers();				
				break;
			}
			
			case 'brand':{
				//$title = 'Brands';
				$items = Rhema_Model_Service::factory('affiliate_product_brand')->listBrands();				
				break;
			}
			
			
			case 'manufacturer':{
				$title = 'Discounts, Deals and Offers - Manufacturers';
				$items = Rhema_Model_Service::factory('affiliate_product_manufacturer')->listManufacturers();				
				break;
			}
					
			case 'promotions':{
				$title = 'Promotional sales and Discounts';
				$res   = Rhema_Model_Service::factory('affiliate_product')->getPromotions(500);	
				$items = $res->getCurrentItems();			
				break;
			}	
			case 'top50':{			
				$title = 'Top Selling Products';
				$items = Rhema_Model_Service::factory('affiliate_product')->getRandomProducts(250);
				break;
			}	
 		
			case 'vouchers':{						 			 
 				$slug 				= $this->_request->getParam('slug');
 				$productObject		= Rhema_Model_Service::factory('affiliate_product'); 				
 				$types				= array(Admin_Model_AffiliateProductType::TYPE_DEAL, Admin_Model_AffiliateProductType::TYPE_VOUCHER);
 				$product   			= $productObject->getVouchers(200, 1, $types, $slug);
 		 
 				$items 				= $product->getCurrentItems();	 	 			
 				if(!count($items)){
 					$title	  = ucwords(str_replace('-', ' ', $slug));
 					$items    = $productObject->getTopProducts(200); 		
				}else{
					$retailer = Rhema_Model_Service::factory('affiliate_retailer')->getRetailer($slug, 'slug');
					$title    = $retailer['title'];
					if($retailer['description']){
						$siteData['description'] = $retailer['description'] ;
					}
				}
				break;
			}
			case 'search':
			default:{
				$slug  = $this->_request->getParam('slug');
				$slug  = $slug ? $slug : $this->_request->getParam('type');
				$title = 'Discount, Deals, Sales & Offers Matching: ' . ucwords(str_replace('-', ' ', $slug));
				$res   = Rhema_Model_Service::factory('affiliate_product')->findSimilarProducts($slug, 200, 1);	
				$items = $res->getCurrentItems();		
				break;
			}								
		}
		
		$feedTitle = $title ? $title : 'Best Discounts, Deals and Offers';
		$dto->setItems($items)
			->setTitle($feedTitle)
			->setDescription($siteData['description'])
			->setKeywords($siteData['keyword'])
			->setLink($siteLink);

		$feed = $this->_helper->getHelper('generateFeed')->affiliate($dto, $type);
		$this->_response
				->setHeader('Content-Type', 'text/xml; charset=utf-8')
				->setHeader('Pragma', 'public')
				->setHeader('Expires', '-1')
				->setBody($feed)
				->sendResponse();
		exit();		
	}
	
    public function feedAction(){
 		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$posts = Rhema_Model_Service::factory('blog_post')->getBlogPosts(100, null);
		$config = Rhema_Util::getSessData(Rhema_Constant::SITE_CONFIG_KEY);
		$siteData = $config['subsite'];
		$siteLink = 'http://' . $this->_request->getHttpHost();

		$dto = new Rhema_Dto_FeedDto();
		$feedTitle = $siteData['title'] . ' - Blog Feed';
		$dto->setItems($posts)
			->setTitle($feedTitle)
			->setDescription($siteData['description'])
			->setKeywords($siteData['keyword'])
			->setLink($siteLink);

		$feed = $this->_helper->getHelper('generateFeed')->rss($dto);
		$this->_response
				->setHeader('Content-Type', 'text/xml; charset=utf-8')
				->setHeader('Pragma', 'public')
				->setHeader('Expires', '-1')
				->setBody($feed)
				->sendResponse();
		exit();
    }

	public function pdfAction(){
		$filename = '';
		$options  = '';
		$html     = '';
		$svc = new Admin_Service_Pdf(); $svc->toPdf($filename, $html);
	}


	public function buyPhotoAction(){

	}

	public function logoutAction(){
		$auth = Admin_Service_Authenticate::getInstance();
		$auth->clear();
		
		$namespace = new Zend_Session_Namespace(SESS_NAMESPACE);
		$namespace->unsetAll();

		//$this->removeCacheFiles();
		$msg = new Rhema_Dto_UserMessageDto('You have logged out successfully', null, Rhema_Dto_UserMessageDto::TYPE_SUCCESS);
		$this->_helper->FlashMessenger($msg);

		$redirectUrl = $this->view->url(array('slug' =>'index' ), 'site-default-route'); 
 
		$this->_helper->redirector->gotoUrlAndExit($redirectUrl);
	}


	/**
	 * List all images in the specified image directory for album slideshow
	 * in frontend dialog box when an album thumbnail is clicked
	 */
	public function photobookPreviewAction(){
		$album       = $this->_request->getParam('album');
		$useThumb    = $this->_request->getParam('useThumb', true);
		$list        = array();
		$commentForm = '';
		$albumUrl    = $this->view->url();

		if($album){
			$folio          = Rhema_Model_Service::factory('portfolio');
			$albumDetails   = $folio->getAlbumDetails($album)	;
			$list           = $folio->listAlbumImages($albumDetails, $useThumb);
			$commentForm    = new Rhema_Form_AddComment();
			$commentForm->getElement('id')->setValue($albumDetails['id']);
		}


		$return['list']       = $list ;
		$return['album']      = $albumDetails ;
		$return['imagePath']  = SCRIPT_PATH . '/jquery-raty/img';
		$return['form']       = $commentForm->render();
		$return['albumUrl']   = $albumUrl ;

    	if($this->_request->isXmlHttpRequest()){
    		$this->_helper->json->sendJson($return);
		}else{
		    $this->view->assign($return);
		    $this->view->headTitle($albumDetails['title']);
		}
	}

	public function downloadFileAction(){

	}
}