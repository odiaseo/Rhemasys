<?php
class Rhema_Widget_Controller_User extends Rhema_Widget_Abstract{
	
	private     $_user ;
	protected  $_regData = array();
	
	
	public function __construct(){
		parent::__construct();
		$this->_regData = new Rhema_OpenId_Extension_Ax();			
		$this->_user    = Zend_Auth::getInstance()->getIdentity();
	}
	
	public function changePasswordMethod(){
		$form 		= new Rhema_Form_User_ChangeLoginDetails();	
		return $this->_processUserData($form);
	}
	
	public function changePersonalDetailsMethod(){
		$form 		= new Rhema_Form_User_ChangePersonalDetails();
		return $this->_processUserData($form);			
	}
	
	protected function _processUserData(Zend_Form $form = null){
		$success    = false; 	
		if($this->_request->isPost()){
			$data = $this->_request->getPost();
			$param['table'] = MODEL_PREFIX . 'User';
			$param['id']	= $this->_user['id'];
			if($form->isValid($data)){				
				$msg     = Rhema_Model_Abstract::saveData($param,$data);	
				$success = true ;
			}
		}else{			 
			$form->populate($this->_user);
		}	
		
		$return = array(
			'form'		=> $form ,
			'success'	=> $success
		);

		return $return;
	}
	
	public function myDetailsMethod(){
		$return['params'] = $this->_request->getParams();
		$googleEndpoint   = Rhema_SiteConfig::getConfig('settings.openid.google');
		 
		$consumer 		  = new Rhema_OpenId_Consumer(); 
		$status   		  = $consumer->verify($return['params'], $id, $this->_regData);
		
        if ($status ) { 
           $return['status'] = $status ;
           $return['data']	 = $this->_regData->getTrustData();
        }else{
        	$return['error'] = $consumer->getError();
        }
        
		return $return;
	}
	/**
	 * @return the $_regData
	 */
	public function getRegData(){
		return $this->_regData;
	}

}