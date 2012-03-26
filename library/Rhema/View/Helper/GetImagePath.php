<?php
/**
 *
 * @author Pele
 * @version 
 */
require_once 'Zend/View/Interface.php';

/**
 * GetImagePath helper
 *
 * @uses viewHelper Rhema_View_Helper
 */
class Rhema_View_Helper_GetImagePath extends Zend_View_Helper_Abstract {  
	
	const GRAPHIC 		= '/graphics';
	const ICON     		= '/icons';
	const DEFAULT_THEME = 'default';
	const IMAGE_DIR     = 'images';
	
	private static $_theme;
	private  $_viewAdult = null;
	public static  $path;
	/**
	 * 
	 */
	public function getImagePath() {		
		self::$_theme = Zend_Registry::isRegistered(Rhema_Constant::SITE_THEME_KEY) 
						? Zend_Registry::get (Rhema_Constant::SITE_THEME_KEY)
						: self::DEFAULT_THEME;
		self::$path   = Rhema_SiteConfig::getSiteImagePath(self::$_theme);
		return $this; 
	}
	
	/**
	 * @return the $_viewAdult
	 */
	public  function getViewAdult() {
		if($this->_viewAdult === null){
			$this->_viewAdult = $this->view->viewAdult;
		}		
		return $this->_viewAdult;
	}

	public  function generic($filename = ''){
		$filename = $filename ? trim($filename, '/') : '';
		return self::$path . $filename; 
	}

	public function icon($filename = ''){
		$filename = $filename ? trim($filename, '/') : '';
		return  self::$path . ltrim(self::ICON, '/') . '/' . $filename; 
	}
	
	public function graphic($filename = ''){
		$filename = $filename ? trim($filename, '/') : '';
		return  self::$path .  self::GRAPHIC . '/' . $filename; 
	}
	
	public function backendIcon($filename = ''){
		$filename = $filename ? trim($filename, '/') : '';
		return  Rhema_SiteConfig::getBackendPath() . 'images/icons/' . $filename; 
	}
	
	public function flag($country, $size = 32){
		$country = strtolower($country);
		return  Rhema_SiteConfig::getDomainPath('admin', '', true, false) . "/media/image/flags/{$country}-{$size}.png";; 
	}
		
	public function backendGraphic($filename = ''){	
		$filename = $filename ? trim($filename, '/') : '';	
		return  Rhema_SiteConfig::getBackendPath() . 'images/graphics/' . $filename; 
	}
	
	public function store($data){
		
	}
	
	public function product($product){
		if($product['image_url'] and $product['AffiliateProductCategory']['is_adult'] and $this->getViewAdult()){
			$productImage  = $product['image_url'];
		}elseif($product['image_url'] and !$product['AffiliateProductCategory']['is_adult']){
			$productImage  = $product['image_url'];
		}else{
			$productImage  = $this->view->getRetailerLogo($product['AffiliateRetailer'], '');			
		}		
	 
		return $productImage ? $productImage : 'image-not-found.jpg';			 		
	}
	
	private function _stripDoubleSlashed($string){
		return str_replace('//', '/', $string);
	}

}
