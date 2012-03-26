<?php
	class Rhema_Widget_Controller_Layout extends Rhema_Widget_Abstract{


		public function indexMethod(){

		}

		public function toolMethod(){
			$return   = array();
			$scope	  = 'admin';
			$resource = 'mvc:admin.layout';
			$userRole = Zend_Registry::get(Rhema_Constant::USER_ROLE_KEY);
			$priv	  = 'tool';

			$acl      = $this->_utility->getAcl($scope);
			$auth     = Zend_Auth::getInstance();
			$editMode = 0 ; //$this->_utility->getSessData('editmode', 0);
			$isAdmin  = 0;

			if($auth->hasIdentity() and $acl->has($resource) and $acl->isAllowed($userRole, $resource, $priv) ){
				$isAdmin 			= 1;
				$backendPath        = Rhema_SiteConfig::getBackendPath(); 
			    $url['module']			= 'cms';
			    $url['controller']		= 'design';
			    $url['action']			= 'updatelayout';

			    $updUrl['module']		= 'admin';
			    $updUrl['controller']	= 'layout';
			    $updUrl['action']		= 'update' ;


				$return['editMode']   = $editMode;
				$return['formAction'] = $this->_view->url($url, ADMIN_ROUTE); 
				$return['updateUrl']  = $this->_view->url($updUrl, ADMIN_ROUTE);
				$return['allow']      = true;
			
				$this->_view->headScript()->appendFile($backendPath . 'editors/ckeditor/ckeditor.js')
				 						  ->appendFile($backendPath . 'editors/ckeditor/adapters/jquery.js') 
				 						  ->appendFile($backendPath . 'scripts/tool.js');
    			$this->_view->headLink()->appendStylesheet($backendPath . 'css/tool.css'); 			

			}else{
				$isAdmin 			= 0;
				$return['allow']  = false;
				//$this->_utility->setSessData('editmode', 0);
			}

			$this->_utility->setSessData('editmode', $editMode);
			$this->_utility->setSessData('admin', $isAdmin);

			return $return ;
		}

	}
