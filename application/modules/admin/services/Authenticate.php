<?php
class Admin_Service_Authenticate {
	protected $_authAdapter;
	protected $_userModel;
	protected $_auth;
	protected $_column;
	protected $_credentialColumn;
	protected static $_instance;

	protected function __construct($userModel = null, $column = null, $credentialColumn = null){
		$this->_userModel = (null === $userModel) ? MODEL_PREFIX . 'User' : $userModel;
		$this->_column    = (null === $column)    ? 'username'         : $column;
		$this->_credentialColumn = (null == $credentialColumn) ? 'password' : $credentialColumn;
	}
 
    
    private function __clone(){
    }
    
    public static function getInstance(){
        if(null === self::$_instance){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
	 
	public function setAdapter(Rhema_Auth_Adapter_Model $adapter){
		$this->_authAdapter = $adapter;
	}

	public function getAuthAdapter($values){
		if(null == $this->_authAdapter){
			$authAdapter = new Rhema_Auth_Adapter_Model($this->_userModel,$this->_column, $this->_credentialColumn);
			$authAdapter->setIdentity($values[$this->_column]);
			$authAdapter->setCredential($values[$this->_credentialColumn]);
			$this->setAdapter($authAdapter);
		}
			
		return $this->_authAdapter;
	}

	public function authenticate(array $credentials){
		$success   = false;
			
		$adapter   = $this->getAuthAdapter($credentials);
		$result    = $this->getAuth()->authenticate($adapter);
		 
		if($result->isValid()){
			Zend_Session::rememberMe();
			$success  = true;
		}
			
		return $result;
	}

	public function getAuth(){
		if(null == $this->_auth){
			$this->_auth = Zend_Auth::getInstance();
		}
			
		return $this->_auth;
	}

	public function getIdentity(){
		$auth = $this->getAuth();
		if($auth->hasIdentity()){
			return $auth->getIdentity();
		}
			
		return false;
	}

	public function clear(){
		$this->getAuth()->clearIdentity();
	}

	public static function isLogin(){
		$auth = Zend_Auth::getInstance();
		return $auth->hasIdentity();
	}

	public static function pleaseLogin(){
		$request   = Zend_Controller_Front::getInstance()->getRequest();
		$action    = $request->getActionName();
		if('login' != $action){

			Rhema_Util::setSessData('returnto', $request->getRequestUri());
			$broker = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
			$broker->gotoRouteAndExit(array(), 'site-login-page');
		}
	}

}