<?php
/**
 * This plugin switches the page layout if the flag isAdmin is set
 * It hooks in in the pre-despatch process of the front controller
 */
class Rhema_Plugin_AdminContext extends Zend_Controller_Plugin_Abstract{

	protected $_context = 'site';
	protected $_loggedIn;

	public function routeShutdown(Zend_Controller_Request_Abstract $request){
		$controllerName = $request->getControllerName();
		$layout = Zend_Layout::getMvcInstance();
		 		
		if($controllerName == 'setup'){
			$layout->setLayout('setup');
		}elseif(!(preg_match('/(rest|soap)/i', $controllerName) or Rhema_Util::isCli())){
			$path = array();
			

			$baseUrl = '/' . $request->getServer('RMSDIR'); // Zend_Controller_Front::getInstance()->getBaseUrl();
			$util = Rhema_Util::getInstance();

			//============== force login for new sites
			$util->forceLogin();

			$context = CONTEXT_SITE;
			$auth = Zend_Auth::getInstance();

			$user = $auth->getIdentity();
			$userRole = (is_array($user) and ($user['Role'])) ? strtolower($user['Role']['title']) : 'guest';
			Zend_Registry::set(Rhema_Constant::USER_ROLE_KEY, $userRole);

			if(! defined('BASE_URL')){
				define('BASE_URL', $baseUrl);
			}

			$editorConfigFile = SITE_PATH . '/fckpath.txt';
			

			if(! file_exists($editorConfigFile)){
				$filter = new Zend_Filter_RealPath();
				$path['site']['rel'] = '/media/';
				$path['site']['abs'] =  $filter->filter(Rhema_Constant::getSiteRoot(). $path['site']['rel']) . DIRECTORY_SEPARATOR;

				$path['admin']['rel'] = '/';
				$path['admin']['abs'] = Rhema_Constant::getPublicRoot();

				file_put_contents($editorConfigFile, Zend_Json::encode($path));
			}

			Zend_Registry::set('baseUrl', $baseUrl);
			$priv = $request->getActionName();
			
			if(! $request->isXmlHttpRequest() and array_search($priv, Rhema_Constant::$exemptActions)=== false){
				$this->_loggedIn = $auth->hasIdentity();
				$view = $layout->getView();
				if($request->getParam('isControlPanel')){
					
					if($this->_loggedIn){
						$context = CONTEXT_ADMIN;
					}else{
						Admin_Service_Authenticate::pleaseLogin();
					}
					
					if($request->getActionName() == 'index'){
						$view->moduleData  = Zend_Controller_Action_HelperBroker::getStaticHelper('getModuleData')->getModuleData();
					}
				}

				$acl  = $util->getAcl($context);
				$m    = $request->getModuleName();
				$c    = $request->getControllerName();
				 
				$resource = "mvc:$m.$c";
				$view->navigation()->setAcl($acl)->setRole($userRole);
 
				if($acl->has($resource) and ! $acl->isAllowed($userRole, $resource, $priv)){
					$broker = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
					$url = $view->url(array(), 'frontend-access-denied', true);
					$util->cancelPageCaching();
					$broker->gotoUrlAndExit($url); 
				}
				

				$this->_setGridParams();
			}else{
				//Zend_Controller_Front::getInstance()->throwExceptions(true);
			}
			
			Zend_Registry::set('sys-layout-context', $context);
		}
	}
 
	
	public function postDispatch(Zend_Controller_Request_Abstract $request){
		if(!Rhema_Util::isCli()){
			$util      = Rhema_Util::getInstance();
			$front     = Zend_Controller_Front::getInstance();
			$hasError  = $front->getResponse()->isException();
			$isControl = $request->getParam('isControlPanel');
			$layout    = Zend_Layout::getMvcInstance();
			 
			if($request->isXmlHttpRequest()){
				$layout->setLayout('blank');
				$data = $util->getAjaxData();
				//die($data);
				if($data){
					$front->getResponse()->setBody($data);
				}
				$layout->enableLayout();
			}elseif(! $hasError){
				$layout->getView()->addScriptPath(APPLICATION_PATH . '/layouts/scripts');
				$editMode       = (Zend_Registry::isRegistered('edit_mode')) ? Zend_Registry::get('edit_mode') : 0;
				$theme          = trim(Zend_Registry::get('site-theme'));				
				$view->theme    = $theme ? $theme : 'default';	
							
				if($isControl and $this->_loggedIn){					
					$modObject = Rhema_Model_Service::factory('AdminModule');
					$navData = $modObject->moduleNavigation($request->getModuleName(), $this->_loggedIn);
					Zend_Registry::set(Rhema_Constant::NAVDATA_KEY, $navData);
					$layout->setLayout('admin');
				}elseif(!$isControl){				 
			 		$bootstrap      = $front->getParam('bootstrap');
			 		$userAgent      = $bootstrap->getResource('useragent');
			 		
			 		try{
			        	$width = $userAgent->getDevice()->getPhysicalScreenWidth();
			 		}catch(Exception $e){
			 			$width = '' ;
			 			pd($e->getMessage());
			 		}	

			 		if($width){
						switch (true) {
						    case ($width <= 64):
						        //$layout->setLayout('layout-mobile');
						        break;						
/*						    case ($width <= 256):
						        $layout->setLayout('layout-small');
						        break;
						    case ($width <= 600):
						        $layout->setLayout('layout-medium');
						        break;*/
						    default:
						        // use default
						        break;
						}
			 		}
				}
			}
		}
	}
	
	protected function _setGridParams(){
		Bvb_Grid_Deploy_JqGrid::$debug = (APPLICATION_ENV == 'development') ? true : false;
        Bvb_Grid_Deploy_JqGrid::$defaultJqGridLibPath = Rhema_SiteConfig::getBackendScriptsPath() . 'grid';
        Bvb_Grid_Deploy_JqGrid::$defaultJqgI18n = Zend_Registry::get('Zend_Locale' );
	}
}