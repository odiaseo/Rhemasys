<?php

class SoapController extends Zend_Controller_Action{

	private $_WSDL_URI  = "http://rhemasys-dev/golive/soap?wsdl";
	private $_soapClass = 'Admin_Service_Server';
	
    public function init(){
 				
    }
    
   

    public function indexAction()    {  
    	$this->_helper->viewRenderer->setNoRender(); 
    	$this->view->layout()->setLayout('blank');
    	
        $request = $this->getRequest();
        $wsdl    = $request->getParam('wsdl', null) ;
        if($wsdl !== null) {
            //return the WSDL 
            $this->handleWSDL();
        } else {
            //handle SOAP request
            $this->handleSOAP();
        }
    }

    private function handleWSDL() {
        $autodiscover = new Zend_Soap_AutoDiscover();
        $autodiscover->setClass($this->_soapClass);
        $autodiscover->handle();
    }
    
    private function handleSOAP() {
        $soap = new Zend_Soap_Server($this->_WSDL_URI); 
        $soap->setClass($this->_soapClass);
        $soap->handle();
    }
    
    public function clientAction() {
        $client = new Zend_Soap_Client($this->_WSDL_URI);
        
        $this->view->add_result  = $client->math_add(11, 55);
        $this->view->not_result  = $client->logical_not(true);
        $this->view->sort_result = $client->simple_sort(
       		array("d" => "lemon", "a" => "orange",
             "b" => "banana", "c" => "apple"));
        
    }
}