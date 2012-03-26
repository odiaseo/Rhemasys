<?php
/**
 * Use to filter feed data when importing from affiliate networks 
 * @author Pele
 *
 */
class Rhema_Util_FeedFilter  {
	
	/**
	 * Checks if the link is valid merchant deeplink
	 * @param unknown_type $link
	 * @return unknown|string
	 */
	public static $_affiliateWindowId = 73184;
	
	public function checkMerchantDeepLink($link){
		 if(preg_match('/(http:\/\/)/i', $link)){
		 	return $link ;
		 }else{
		 	return '';
		 }
	}
	
	public static function decodeHtmlEntity($data){
		return html_entity_decode($data); 
	}	
	
	/**
	 * Replace merchant placeholders for affiliate windonw ID and clickref
	 * @param unknown_type $data
	 * @return mixed
	 */
	public static function replaceAffiliateWindowIdAndClickref($data){
		//clickid=!!!clickref!!!&affid=!!!affid!!!
		$domain = Zend_Controller_Front::getInstance()->getRequest()->getHttpHost();
		$data   =  str_replace(array('!!!affid!!!', '!!!clickref!!!'), array(self::$_affiliateWindowId, $domain), $data );
		return $data;
	}
	
	/**
	 * Convert date format to DB datetime particularly for web gains date
	 * input format is dd/mm/yy
	 * @param unknown_type $data
	 * @param unknown_type $col
	 * @return Ambigous <unknown, string>
	 */
	public static function formatProductDate($data, $col = ''){
		$dbDate = $data ;
		if($data){
			list($day, $month, $year) = explode('/', $data);
			$time    = mktime(0, 0, 0, $month, $day, $year);
			if($col == 'valid_to'){
				$time += (24*60*60) -  1; 
			}else{
				$time += 1 ;
			}
			$dbDate  = date(DB_DATE_FORMAT, $time);
		}
		return $dbDate ;
	} 
	
	public static function convertoUtf8($data){
		return Rhema_Util_String::correctEncoding($data);
	}
	
	public static function convertStringToTime($data, $col = ''){
		$time = strtotime($data);	
		$date = date('d/m/y', $time);	
		return self::formatProductDate($date, $col);
	}
	
	public static function stringToInteger($data, $col){
		return strtolower($data) == 'no' ? 0 : 1 ;
	}
	
	
	public static function convertToUtf8($data, $col){
		return Rhema_Util_String::correctEncoding($data);
	} 
}