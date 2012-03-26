<?php 
class Rhema_View_Helper_DisplayBrandLogo extends Zend_View_Helper_Abstract {
	
	protected $_defaultFields = array(
		'brand', 'mobile_network', 'service_provider'
	);
	
	public static $allowedExtension = array(
		'png', 'gif', 'jpg', 'jpeg', 'bmp'
	);
	
	public function displayBrandLogo($data = array(), array $fields = null){
		$items    = array();
		$html     = '';
		$done     = array();
		//pd($data);
		if(count($fields)){
			$toUse = $fields;
		}else{
			$toUse = $this->_defaultFields;
		}		

		$root   = Rhema_Constant::getSiteRoot();      
		
		foreach($toUse as $f){
			if($f == 'brand' and isset($data ['AffiliateProductBrand']['slug'])){
				$key = $data ['AffiliateProductBrand']['slug'];
			}elseif(isset($data[$f]) and $data[$f]){
				$key      = Doctrine_Inflector::urlize($data[$f]);
			}else{
				continue;	
			}
			if(!in_array($key, $done)){								 
				$stat = Rhema_SiteConfig::getStaticPath();			 
				$pre  = 'media/image/icons/logo-' . $key;	  
				foreach(self::$allowedExtension as $ext){
					$filename   = $pre. '.' . $ext;	
					$actual     = $root . '/'  . $filename	;			 			 
					if(file_exists($actual) and isset($data[$f])){
						$icon    = $stat . $filename ;
						$done[]  = $key;
						$url     = $this->view->url(array($f => $data[$f]), 'product-search-filters', true);
						$items[] = "<a href='{$url}'><img width='34' height='34' class='logo {$key}' src='{$icon}' alt='{$data[$f]}' title='{$f} - {$data[$f]}' /></a>";
						break;
					}
				}				 
			}
		}
		
		return $items;
	}
}