<?php 

class Rhema_View_Helper_GetProductFilters extends Zend_View_Helper_Abstract {
	public function getProductFilters(){
	    $form		  = new Rhema_Form_Search_ProductFilter(); 
	    $request      = Zend_Controller_Front::getInstance()->getRequest();
	    
    	if($form->isRhemaButtonSubmitted()){
    		if($form->isValid($request->getPost())){
    			$data              = array(); 
    			foreach($form->getValues() as $k => $v){
    				if($v){
    					$data[$k] = $v ;
    				}
    			}
			    $redirector        = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');	    			
    			$url  		       = $this->view->url($data, 'product-search-filters', true);
    			$redirector->gotoUrlAndExit($url);
    		}
    	}else{    		
    		$form->isValid($request->getParams()); 
    	}
    	return $form;		
	}
}