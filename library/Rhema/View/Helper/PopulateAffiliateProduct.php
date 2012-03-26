<?php
 
class Rhema_View_Helper_PopulateAffiliateProduct extends Zend_View_Helper_Abstract {
	
	public static $brandList;
	public static $categoryList;
	public static $giftList; 
	public static $retailerList;
	public static $manufacturerList ;
	public static $typeList ;
	
	public static $statList = null;
	
	public function __construct(){
		$affData = self::getStatList();
		self::$categoryList = isset($affData['category'])   ? $affData['category']   : array();
		self::$brandList    = isset($affData['brands'])     ? $affData['brands']     : array();
		self::$retailerList = isset($affData['retailers'])  ? $affData['retailers']  : array();
		self::$typeList     = isset($affData['types'])      ? $affData['types']      : array();
		self::$giftList     = isset($affData['gifts'])      ? $affData['gifts']      : array();	
		self::$manufacturerList    = isset($affData['manufacturers'])  ? $affData['manufacturers']         : array();				
	}
	

	/**
	 * @return the $statList
	 */
	public static function getStatList() {
		if(!self::$statList){
			self::$statList = Admin_Model_AffiliateProduct::getProductStatList();
		}
		return Rhema_View_Helper_PopulateAffiliateProduct::$statList;
	}

	/**
	 * @param field_type $statList
	 */
	public static function setStatList($statList) {
		Rhema_View_Helper_PopulateAffiliateProduct::$statList = $statList;
	}

	public function populateAffiliateProduct($product){
		$data   = array();
		if(isset($product['product_data'])){
			$str    = trim(str_replace("'", '"', $product['product_data'])); 
			if($str){
				@eval('$data = ' . $str) ;	
			} 
			unset($product['product_data']);
		}
		
		if(isset($data['merchant_deep_link'])){
			$data['merchant_deep_link'] = Rhema_Util_FeedFilter::replaceAffiliateWindowIdAndClickref($data['merchant_deep_link']);
		}
		
		$product     = array_merge($product, $data); 
		$retailerObj = Rhema_Model_Service::factory('affiliate_retailer');
		 
		$retailerId = isset($product['affiliate_retailer_id'])         ? $product['affiliate_retailer_id'] : 1;
		$catId      = isset($product['affiliate_product_category_id']) ? $product['affiliate_product_category_id']: 1;;
		$brandId    = isset($product['affiliate_product_brand_id'])    ? $product['affiliate_product_brand_id']: 1;
		$typeId     = isset($product['affiliate_product_type_id'])     ? $product['affiliate_product_type_id'] : 0;
		$manId      = isset($product['affiliate_product_manufacturer_id']) ? $product['affiliate_product_manufacturer_id'] : 0 ;;
		$giftId     = isset($product['affiliate_promotion_id']) ? (int) $product['affiliate_promotion_id'] : 1;
		
		if($retailerId and !isset(self::$retailerList[$retailerId])){
			self::$retailerList[$retailerId] = $retailerObj->getRetailer($retailerId);
		}
		
		if($catId and !isset(self::$categoryList[$catId])){
			self::$categoryList[$catId] = Rhema_Model_Service::factory('affiliate_product_category')->getCategory($catId);
		}		
		$product['AffiliateRetailer']            = isset(self::$retailerList[$retailerId]) ? self::$retailerList[$retailerId] : array();
		$product['AffiliateProductType']         = isset(self::$typeList[$typeId])         ? self::$typeList[$typeId] : array();
		$product['AffiliateProductCategory']     = isset(self::$categoryList[$catId])      ? self::$categoryList[$catId] : array();
		$product['AffiliateProductBrand']        = isset(self::$brandList[$brandId])       ? self::$brandList[$brandId] : array();
		$product['AffiliatePromotion']           = isset(self::$giftList[$giftId])         ? self::$giftList[$giftId] : array();
		$product['AffiliateProductManufacturer'] = isset(self::$manufacturerList[$manId])  ? self::$manufacturerList[$manId] : array();

		if(isset($product['AffiliateRetailer']['deeplink'])){
			$product['AffiliateRetailer']['deeplink'] = Rhema_Util_FeedFilter::replaceAffiliateWindowIdAndClickref($product['AffiliateRetailer']['deeplink']);
		}
		return $product ;
	}
}