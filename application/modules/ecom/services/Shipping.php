<?php
/**
 * Storefront_Model_Shipping
 *
 */
class Ecom_Service_Shipping extends Ecom_Service_Cart_Abstract  {
    /**
     * @var array
     */
    protected $_deliveryMethod = array(); 

    /**
     * Get the shipping options
     * 
     * @return array
     */
    public function init(){
    	$ns = new Zend_Session_Namespace(__CLASS__);
    	if($ns->deliveryMethod){
    		$this->_deliveryMethod  = $ns->deliveryMethod;
    	}else{
    		$methodObj			    = Rhema_Model_Service::factory('ecom_delivery_method');
    		$this->_deliverymethod  = $methodObj->getDeliveryMethods();
    		$ns->deliveryMethod     = $this->_deliverymethod;
    	} 
    }
    
    public function getShippingOptions()    {
        return $this->_deliveryMethod ;
    }
   
}