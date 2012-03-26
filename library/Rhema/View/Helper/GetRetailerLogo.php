<?php 
class Rhema_View_Helper_GetRetailerLogo extends Zend_View_Helper_Abstract {
	
	public static  $imageDir =  '/media/image/logos/retailer/medium/';
	
	public function getRetailerLogo($data, $default = 'image-not-found.jpg'){
		$icon = '';
		if($data){
			$data      = (array) $data;
			$util      = Rhema_Util_String::getInstance();
			$logoTitle = isset($data['slug']) ? $data['slug'] : Doctrine_Inflector::urlize($data['title']);
			$icon      = (isset($data['logo']) and $data['logo']) ?   $util->getImageSource($data['logo']) : $default; 
			$root      = Rhema_Constant::getPublicRoot();  
			$imageDir  = rtrim($root, '/') . self::$imageDir;  
			$stat      = rtrim(Rhema_SiteConfig::getDomainPath('admin'), '/') . self::$imageDir;
			 
			foreach(Rhema_View_Helper_DisplayBrandLogo::$allowedExtension as $ext){
				$filename   = $logoTitle. '.' . $ext;	
				$actual     = $imageDir  . $filename	;			 			 
				if(file_exists($actual)){
					$icon    = $stat . $filename ; 
					break;
				}
			} 
		}
		
		return $icon ;
	}
}