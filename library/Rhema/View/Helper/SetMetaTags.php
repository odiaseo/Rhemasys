<?php
/**
 *
 * @author Pele
 * @version
 */
require_once 'Zend/View/Interface.php';

/**
 * SetMetaTags helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Rhema_View_Helper_SetMetaTags extends Zend_View_Helper_Abstract{

	public $view;

	public $productFeedTypes = array(
		'top50', 'category', 'brand', 'manufacturer', 'promotion', 'retailer'
	);
	
	public function setView(Zend_View_Interface $view){
		$this->view = $view;
	}
	/**
	 *
	 */
	public function setMetaTags($titleStr, $description = '', $keyword = '') {
		$titleStr       = (array) $titleStr ;
	    $titleStr       = array_unique($titleStr)	;
	     
	    $request        = Zend_Controller_Front::getInstance()->getRequest();
	    
	    if($request->getParam('page')){
	    	$route   		= Zend_Controller_Front::getInstance()->getRouter()->getCurrentRoutename();
	    	$params         = $request->getParams();
	    	unset($params['page']);
	    	$canon          = $this->view->url($params, $route, true);
	    	$this->view->headLink()->headLink(array('rel' => 'canonical', 'href' => $canon), 'PREPEND' ); 
	    	
		    if($this->view->paginator and $this->view->paginator instanceof Zend_Paginator){
		    	$pageCount = $this->view->paginator->count();
		    	$curPage   = $this->view->paginator->getCurrentPageNumber();
		    
		    	if($curPage < $pageCount){
		    		$params['page'] = $curPage + 1;
	    			$href           = $this->view->url($params, $route, true);
	    			$this->view->headLink()->headLink(array('rel' => 'next', 'href' => $href), 'PREPEND' );
		    	}
		    	
		    	if($curPage > 1){
		    		$params['page'] = $curPage - 1;
	    			$href           = $this->view->url($params, $route, true);
	    			$this->view->headLink()->headLink(array('rel' => 'previous', 'href' => $href), 'PREPEND' );
		    	}
		    }	    	
	    }	    
	    
	    foreach($titleStr as $t){
	    	$title[] = $this->view->replaceMergeField($t);
	    }
	    $description = substr($this->view->replaceMergeField($description),0,160);
	    $description = Rhema_Util_String::correctEncoding($description);
    
	    $keyword     = $this->view->replaceMergeField($keyword);
	    $alexaVerifyID      = Rhema_SiteConfig::getConfig('settings.alexa_site_verification');
	    $googleVerification = Rhema_SiteConfig::getConfig('settings.google_site_verification'); 
	    $bindId             = Rhema_SiteConfig::getConfig('settings.bing.id'); 
	    
 
		foreach((array)$title as $item){
			if($item){
			    $title  = $this->view->replaceMergeField($item);
			    $title  = Rhema_Util_String::correctEncoding($title);
				$this->view->headTitle(ucwords($title));
			}
		}

		$this->view->headMeta()->appendName('description', (string)$description)
							   ->appendName('google-site-verification', (string) $googleVerification)							   
		                       ->appendName('keywords', (string)ucwords($keyword));
		if($alexaVerifyID){
			$this->view->headMeta()->appendName('alexaVerifyID', (string)$alexaVerifyID);
		}
		$this->view->headMeta()->appendHttpEquiv("accept-charset", "UTF-8,ISO-8859-1"); 
		if($bindId){
			$this->view->headMeta()->appendName('msvalidate.01', (string)$bindId);
		}
        $siteConfig   = Rhema_Util::getSessData('site-config');	
        //$prefixPath   = rtrim(Rhema_SiteConfig::getStaticPath(), '/');
        $prefixPath   = rtrim('http://' . Zend_Controller_Front::getInstance()->getRequest()->getHttpHost(), '/');
       
		$rssUrl       = $prefixPath . $this->view->url(array(),'blog-rss-feed') ;
		$rssTitle     = $siteConfig['subsite']['title'] . ' - Blog Feed' ;
		
        $this->view->headLink()->appendAlternate($rssUrl, 'application/rss+xml', $rssTitle);
		
        foreach($this->productFeedTypes as $type){
			$rssUrl       = $this->view->url(array('type' => $type),'affiliate-feed') ;
			$rssTitle     = 'Product Feeds - ' . ucwords($type);   
			$this->view->headLink()->appendAlternate($rssUrl, 'application/rss+xml', $rssTitle);    	
        }

		$this->setOpenGraphMeta($title, $description, $keyword);
		
		return null;
	}
	
	protected function setOpenGraphMeta($title, $desc, $keywords){
		$util    = Rhema_SiteConfig::getInstance();
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$host    = $request->getHttpHost();
		$scheme  = $request->getScheme();
		$desc    = $desc ? $desc : $title ;
		
		$url     = $scheme . '://' . $host . $this->view->url();
		$name    = $util->getConfig('settings.site_name');
		$appId   = $util->getConfig('settings.socialmedia.facebook.appid');
		$image   = $util->getDomainPath('static', 'media/image/graphics/medium/synergy.jpg');
		$userId  = $util->getConfig('settings.socialmedia.facebook.userid');
		
		$this->view->doctype(Zend_View_Helper_Doctype::XHTML1_RDFA);
		
		$this->view->headMeta()->setProperty('og:title', $title);
		$this->view->headMeta()->setProperty('og:type', 'product');
		$this->view->headMeta()->setProperty('og:url', $url);
		$this->view->headMeta()->setProperty('og:site_name', $name);		
		$this->view->headMeta()->setProperty('og:description', $desc);
		$this->view->headMeta()->setProperty('og:image', $image);
		
		$this->view->headMeta()->setProperty('fb:app_id', $appId);
		$this->view->headMeta()->setProperty('fb:admins', $userId);
	}
	
	 
}
