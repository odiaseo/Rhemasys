<?php

class ErrorController extends Zend_Controller_Action
{
	public function init(){	
		if(!$this->_request->getParam('isControlPanel')){
    		$this->_helper->layout()->setLayout('error-display'); 
		}
		Rhema_Util::cancelPageCaching();
    	$this->view->headTitle('An Error Occurred', Zend_View_Helper_Placeholder_Container_Abstract::APPEND);
	}
//
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        $this->getResponse()->clearBody();
        
        switch ($errors->type) { 
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                break;
            default:
                // application error 
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                break;
        }
        
        
        $this->view->request   = $errors->request;        

        $exception = $errors->exception;   
        $log       = $this->getLog();  
        
        if($log){
        	$info[] = "\n\nRequest Uri: " . $this->_request->getServer('REQUEST_URI');
        	$info[] = "\nReferer: " . $this->_request->getServer('HTTP_REFERER');
        	$info[] = $exception->getMessage()  ;
        	$info[] = $errors->exception->getTraceAsString() ;
        	
       		$log->debug(implode("\n" , $info)); 
        } 
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
     
		if (Rhema_SiteConfig::isDev()) {     		 
        	pd($exception->getMessage(), $errors->exception->getTraceAsString()); 
		}elseif(Rhema_SiteConfig::isProd()){
			$this->_redirect('/', array('code' => 302));
		}


    }

    public function getLog()
    {
    	if(Zend_Registry::isRegistered('logger')){
    		return Zend_Registry::get('logger');
    	}else{
    		return false ;
    	}
    }
    
    public function denyAction(){
    	
    }

}