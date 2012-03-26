<?php
/**
 *
 * @author Pele
 * @version 
 */
 
/**
 * SocialMediaIcons helper
 * Renders social media icons eg facebook etc as set in
 * the site config file
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_SocialMediaIcons   extends Zend_View_Helper_Abstract {	
 
	public function socialMediaIcons($useDir = true) {
		$path   =  $this->getIconPath();	
		if($useDir){
			$path   = '/' . SITE_DIR. $path ;
		}
		
		$config  = Rhema_SiteConfig::getConfig('settings.socialmedia');
		$dirPath = realpath(APPLICATION_PATH . '/../public' . $path);
		$arrow   = $dirPath . 'curved_arrow.png';
		$arrParm = getimagesize($arrow);
		
		$html = '<div id="social_media"><div class="curved_arrow">
				<img alt="" src="' . $path . 'curved_arrow.png"' . $arrParm[3]. ' /></div>
				<div class="icons">';
		foreach($config as $icon => $var){
			$fullPath = $dirPath . $var['icon'] ;
			$iconUrl  = $path . $var['icon'];
			if(file_exists($fullPath)){
				$imgParms = getimagesize($fullPath); 
				if($var['href']){
					$html .= '<a href="' . $var['href'] . '" title="' . $icon .'">' ;
				}  
				$html .= '<img alt="' . $icon .'" src="' . $iconUrl .'" ' .  $imgParms[3] . ' />' ; 
		 	    if($var['href']){
		 	    	$html .= '</a>'; 
		 	    }  
			} 
		}
		$html .= '<div class="text"><span class="title">we socialise</span> 
		          <span class="subtitle">Digg us, follow us, like us</span></div> </div>';
 
		return $html ;
	}
 
}
