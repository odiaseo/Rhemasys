<?php
 
/**
 * Render Outlink
 * @author odiaseo
 *
 */
class Rhema_View_Helper_AffiliateLink extends Zend_View_Helper_Abstract {
 
	private   static $_elementAttribute ;
	private   $_currentRoute     = null;	
	private   $_wgcampaignid     ;	 
 
 
	public function affiliateLink(){
		return $this;
	}
	/**
	 * Return the deeplink to be displayed
	 * @param unknown_type $product
	 * @param unknown_type $anchorText
	 * @param unknown_type $clickRef
	 * @return string
	 */
	public function getDeeplink($product, $anchorText = '', $clickRef = '', $returnTag = false){ 
		$urlTitle    = Doctrine_Inflector::urlize(html_entity_decode($product['title'])) ;
		$outLink     = $this->view->url(array('id' => $product['id'], 'title' => $urlTitle), 'product-outlink', true, false);		
		 
		if($product['deeplink']){ 
			$product['deeplink'] = urldecode($product['deeplink']);
			
			if(stripos($product['deeplink'], 'awin1.com') !== false or stripos($product['deeplink'], 'webgains.com')){
				//$deepLink = $product['deeplink'] ; //$this->addClickRef($product, '', $clickRef );
				$campaignId = Rhema_SiteConfig::getConfig('settings.affiliate.wgcampaignid');
				$affId      = Rhema_SiteConfig::getConfig('settings.affiliate.awinaffid');
				
				$arr    = array();
				$query  = parse_url($product['deeplink'], PHP_URL_QUERY);
				if($query) {
					parse_str($query, $arr);	
				}
				unset($arr['clickref']);
				$newRef = array('clickref' => $this->_getClickRef( $clickRef));
				$arr    = $newRef + $arr ;
	 
				if(isset($arr['wgcampaignid']) and $campaignId){
					$arr['wgcampaignid'] = $campaignId ;
				}elseif(isset($arr['awinaffid']) and $affId){
					$arr['awinaffid'] = $affId;
				}
				$queryParms      = http_build_query($arr);
				 
				if(stripos($product['deeplink'], '?') !== false){
					$deepLink = strstr($product['deeplink'], '?', true) . '?' . $queryParms; 
				}else{
					$deepLink = $product['deeplink'] . '?' . $queryParms ;
				}	
			}else{
				$deepLink = $product['deeplink'];
			}		
		}else{
			
			$deepLink = $outLink ;
		}

					 
	 	if($returnTag){
	 		$anchorText  = $anchorText ? $anchorText : $product['title'];
  			$link        = sprintf("<a id='prod_%d' href='%s' %s='%s' class='outlink' target='_blank' title='%s'>%s</a>", 
 						$product['id'], $outLink, $this->view->linkAttribute, $deepLink, $product['title'], $anchorText);	

 			return $link ;	 		
	 	}else{
			return $deepLink ;
	 	} 
	}
	
 
	public function addClickRef($product, $link, $reference = ''){	
		$camdId     = false ;	
		$affLink    = $product['deeplink'] ? $product['deeplink'] : $link;
/*		if($product){
			$affLink    = false ;				 
			$affNetwork = $product['AffiliateRetailer']['affiliate_network_id'];
			$awinmid    = $product['AffiliateRetailer']['programid'];
			 
			$prodLink   = $product['deeplink'];
	 
			switch($prodLink){
				case (stripos($prodLink, 'awin1.com') !== false): { // affiliate windows 	
					//pd($prodLink);
					if(stripos($link, 'awin1.com') === false){
						$link     = isset($product['merchant_deep_link']) ? $product['merchant_deep_link'] : $link;			
						$affLink  = $this->getAffiliateWindowMerchantLink($awinmid, $link);
					}else{
						$affLink  = $link ;
					}
					break;
				}
				
				case (stripos($prodLink, 'webgains.com') !== false) :{ // web gains
					if($link and stripos($link, 'webgains.com') === false){ 
						$affLink  = $this->getWebgainsMerchantLink($awinmid, $link);
					}else{
						$camdId  = $this->getWgcampaignid();
						$affLink = $product['deeplink'];
					}
					break;
				}			
				default:{
					$affLink = $product['deeplink'];
					break ;
				}
			}
		}else{
			$affLink  = $newLink  = $link ;
		}*/
		 
		if($affLink){
			$arr    = array();
			$query  = parse_url($affLink, PHP_URL_QUERY);
			if($query) {
				parse_str($query, $arr);	
			}
			unset($arr['clickref']);
			$newRef = array('clickref' => $this->_getClickRef($reference));
			$arr    = $newRef + $arr ;
 
			$queryParms      = http_build_query($arr);
			 
			if(stripos($affLink, '?') !== false){
				$newLink = strstr($affLink, '?', true) . '?' . $queryParms; 
			}else{
				$newLink = $affLink . '?' . $queryParms ;
			}
		} else{
			$newLink  = $link; 
		}
	 
		return $newLink ;		 
	}
	
	public function getAffiliateWindowMerchantLink($awinmid, $targetLink = ''){
		$clickref   = $this->_getClickRef();
		$url        = $targetLink ? '&p=' . $targetLink : '';
		$affId      = Rhema_SiteConfig::getConfig('settings.affiliate.awinaffid'); //"http://www.awin1.com/cread.php?awinmid=530&awinaffid=73184"	
		$link		= sprintf("http://www.awin1.com/cread.php?clickref=%s&awinmid=%d&awinaffid=%d%s",$clickref,$awinmid, $affId, $url);

		return $link;
	}
	
	public function getWebgainsMerchantLink($merchantId, $targetLink = ''){
		$clickref   = $this->_getClickRef();
		$url        = $targetLink ? "&wgtarget={$targetLink}" : '';
		$campaignId = Rhema_SiteConfig::getConfig('settings.affiliate.wgcampaignid'); //http://track.webgains.com/click.html?wgcampaignid=101254&wgprogramid=5355&clickref=myre
		$link       = sprintf("http://track.webgains.com/click.html?wgcampaignid=%d&wgprogramid=%d&clickref=%s%s", $campaignId, $merchantId, $clickref,$url);
		
		return $link ;
	}
	
	/**
	 * Return affiliate futures tracking codes
	 * http://scripts.affiliatefuture.com/AFClick.asp?affiliateID=269935&merchantID=4142&programmeID=10382&mediaID=0&tracking=&url=
	 * @param unknown_type $data 
	 */
	public static function getAffiliateFutureRetailerLink($data){
		$affId      = Rhema_SiteConfig::getConfig('settings.affiliate.affiliatefutureid');
		return sprintf("http://scripts.affiliatefuture.com/AFClick.asp?affiliateID=%s&merchantID=%s&programmeID=%s&mediaID=%s&tracking=&url=",
				$affId, $data['MerchantID'], $data['ProgrammeID'], 0);
	}
		
	protected function _getClickRef($ref = ''){
		$request = Zend_Controller_Front::getInstance()->getRequest();
		if(!$ref){
			if(isset($this->view->clickref) and $this->view->clickref){
				$ref  =  $this->view->clickref;
			}else{
				$ref  = $this->getCurrentRoute();
			}				
		}
		$siteCode = Rhema_SiteConfig::getConfig('settings.affiliate.site_code');
		$page     = $request->getParam('page', 1);
		$clickRef = "$siteCode.$ref.$page" ;  
		
		return $clickRef;
	}
	
	/**
	 * @return the $_currentRoute
	 */
	public function getCurrentRoute() {
		if(!$this->_currentRoute){
			$this->_currentRoute = Zend_Controller_Front::getInstance()->getRouter()->getCurrentRoutename();
		}
		return $this->_currentRoute;
	}

	/**
	 * @param field_type $_currentRoute
	 */
	public function setCurrentRoute($_currentRoute) {
		$this->_currentRoute = $_currentRoute;
		return $this;
	}
	/**
	 * @return the $_elementAttribute
	 */
	public static function getElementAttribute() {
		if(!self::$_elementAttribute){
			$elmAttrib = Rhema_SiteConfig::getConfig('settings.affiliate.outlink_attribute');
			self::$_elementAttribute = $elmAttrib ? $elmAttrib : 'rms';
		}
		return Rhema_View_Helper_AffiliateLink::$_elementAttribute;
	}

	/**
	 * @param field_type $_elementAttribute
	 */
	public static function setElementAttribute($_elementAttribute) {
		Rhema_View_Helper_AffiliateLink::$_elementAttribute = $_elementAttribute;
	}
	/**
	 * @return the $_wgcampaignid
	 */
	public function getWgcampaignid() {
		if(!$this->_wgcampaignid){
			$this->_wgcampaignid = Rhema_SiteConfig::getConfig('settings.affiliate.wgcampaignid');
		}
		return $this->_wgcampaignid;
	}

	/**
	 * @param field_type $_wgcampaignid
	 */
	public function setWgcampaignid($_wgcampaignid) {
		$this->_wgcampaignid = $_wgcampaignid;
	}


}