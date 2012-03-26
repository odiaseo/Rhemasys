<?php
/**
 * Get latitude and longitue information
 * @author Pele
 *
 */
class Rhema_Controller_Action_Helper_Geocoder  extends Zend_Controller_Action_Helper_Abstract{
	
	private   $_yahooApiKey = 'dj0yJmk9aGFIcG15ZnRVb2ZnJmQ9WVdrOVpFUmhZM2hoTkdVbWNHbzlNQS0tJnM9Y29uc3VtZXJzZWNyZXQmeD1lYQ--';
	private	  $_apiUrl      = 'http://where.yahooapis.com/geocode';
	protected $_result ;
	
	/**
	 * @return the $_yahooApiKey
	 */
	public function getYahooApiKey() {
		return $this->_yahooApiKey;
	}

	/**
	 * @return the $_apiUrl
	 */
	public function getApiUrl() {
		return $this->_apiUrl;
	}

	/**
	 * @return the $_result
	 */
	public function getResult() {
		return $this->_result;
	}

	/**
	 * @param field_type $_yahooApiKey
	 */
	public function setYahooApiKey($_yahooApiKey) {
		$this->_yahooApiKey = $_yahooApiKey;
	}

	/**
	 * @param field_type $_apiUrl
	 */
	public function setApiUrl($_apiUrl) {
		$this->_apiUrl = $_apiUrl;
	}

	/**
	 * @param field_type $_result
	 */
	public function setResult($_result) {
		$this->_result = $_result;
	}

	public function direct($address, $locale = null){
		$data    = array('latitude' => 0, 'longitude' => 0);
		
		$param   = array(
			'appid'		=> $this->_yahooApiKey,
		    'location'	=> $address,
		    'flags'		=> 'JGSRXT',
		    'locale'	=> $locale ? $locale : Zend_Registry::isRegistered('Zend_Locale') ? Zend_Registry::get('Zend_Locale') : new Zend_Locale('auto')
		);
		
		
		$qString = http_build_query($param);
		$url     = $this->_apiUrl . '?' . $qString;
		$result  = $this->getCached(__CLASS__)->getLatLong($url);
		
		$this->_result   = $result['ResultSet'];	
		
		if($result and !$result->ResultSet->Error){
			$data['latitude']   =  floatval($this->_result['Results'][0]['latitude']);
			$data['longitude']  =  floatval($this->_result['Results'][0]['longitude']);
		}
		
		return $data;		
	}
	
	public function getLatLong($url){
		return Zend_Json::decode(file_get_contents($url));
	}
}