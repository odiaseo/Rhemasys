<?php
set_time_limit(0);
/**
 * Admin_Model_AdminAcl
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
class Admin_Model_AdminAcl extends Admin_Model_Base_AdminAcl{

	const FRONTEND_ACCESS_LIST = 'site';
	const BACKEND_ACCESS_LIST  = 'admin';
	const TASK_UPDATE		   = 'updateAcl';
	
	private $_aclNamespace = array(
		self::FRONTEND_ACCESS_LIST => 'frontend-acl-namespace',
		self::BACKEND_ACCESS_LIST  => 'backend-acl-namespace'
	);
	
	private $_acl ;
	
	protected static $generic = array('admin'       => array('index' => array('deny','logout')),
									  'storefront'  => array('index' => array('login','logout','auth','deny','index','feed','affiliate-feed', 'search'),
															 'error' => array('deny'))
								);

	public function getAclByScope($scope = self::FRONTEND_ACCESS_LIST){
		if(array_key_exists($scope, $this->_aclNamespace)){	
			$key          = $this->_aclNamespace[$scope];		
			$namespace    = new Zend_Session_Namespace($key);
			if($namespace->$key){
				$this->_acl = $namespace->$key ;
			}else{
				$this->_acl      = $this->getAcl($scope);
				$namespace->$key = $this->_acl;
			}
		}else{
			$this->_acl = new Zend_Acl();
		}		
		return $this->_acl;
	}
	
	public static function updateAcl($rules, $table, $rootId = null){
		$defMod = Zend_Controller_Front::getInstance()->getDefaultModule();
		$modFilter = new Rhema_Filter_FormatModelName();
		$svc = new Rhema_Model_Service();
		$util = Rhema_Util::getInstance(); 
		
		//$menuTable = $modFilter->filter($table);
		$model  = __CLASS__;
		$scope  = ($table == 'admin_menu') ? 'admin' : 'site';
		 
		$updFilter = new Rhema_Dao_Filter();
		$updFilter->setModel($model)
				  ->addCondition('title', $table)
				  ->addCondition('scope', $scope)
				  // ->addCondition('root_id', $rootId);
				  ->setHydrationMode(Doctrine_Core::HYDRATE_RECORD);
				 
		$result = $svc->createQuery($updFilter)->execute();
					  
		

		foreach($result as $arr){
			if($arr->resource and $arr->privilege and $arr->role){
				$res   = $arr->resource;
				$priv  = $arr->privilege;
				$role  = $arr->role;

				if(isset($rules[$role][$res][$priv])){ 
					$arr->allow   = 1;
					unset($rules[$role][$res][$priv]);
				}elseif(isset(self::$generic[$scope][$priv])){
					$arr->allow   = 1; 
				}elseif($role != 'super' and $arr->root_id == $rootId){
					$arr->allow   = 0;
				}
			}
		}

		$result->save();

		self::addNewRules($rules, $table, $scope);
	}

	public static function addNewRules($rules, $table, $scope, $default = 1){
		$model = __CLASS__ ;
		foreach($rules as $role => $data){
			foreach($data as $resource => $arr){
				foreach($arr as $priv => $arg){
					if($priv and $resource and $role){
						$arr 				= array();
						$obj                = new $model();

						$arr['resource'] 	= $resource;
						$arr['privilege'] 	= $priv;
						$arr['role'] 		= $role;
						$arr['title']		= $table;
						$arr['scope']		= $scope;
						$arr['allow']	    = $arg['allow'];
						$arr['root_id']     = $arg['root_id'];

						$obj->fromArray($arr);
						$obj->save();
						$obj->free();
					}
				}
			}
		}

		//$acl = self::getAcl($scope);
		Rhema_Util::unsetSessData("acl_$scope");
	}

	public static function getAcl($scope = self::FRONTEND_ACCESS_LIST, $rootId = ''){
		$svc    = new Rhema_Model_Service(); 
		$filter = new Rhema_Dao_Filter();
		$filter->setModel(__CLASS__)
			   ->addCondition('scope', $scope);		 		 
		$result = $svc->createQuery($filter)->execute();
  	
		$roles  	= $svc->factory('role')->getRoles(); 
		$acl        = new Zend_Acl();
		$prev       = null;

		foreach($roles as $items){
			$str = strtolower($items['title']);
			if(!$acl->hasRole($str)){
				if($prev){
					$acl->addRole(new Zend_Acl_Role($str),$prev);
				}else{
					$acl->addRole(new Zend_Acl_Role($str));
				}
				$prev  = $str;
			}
		}

		$acl->deny();

		foreach($result as $arr){
			if(!$acl->has($arr['resource'])){
				$acl->addResource(new Zend_Acl_Resource($arr['resource']));
			}

			if($arr['allow']){
				$acl->allow($arr['role'], $arr['resource'], $arr['privilege']);
			}else{
				$acl->deny($arr['role'], $arr['resource'], $arr['privilege']);
			}
		}

		foreach(self::$generic as $module => $data){
			foreach($data as $controller => $contData){
				foreach($contData as $action){
					$op[Rhema_Constant::MENU_MODULE]     = $module;
					$op[Rhema_Constant::MENU_CONTROLLER] = $controller;
					$op[Rhema_Constant::MENU_ACTION]     = $action	;	

					$resource = Rhema_Util::getMenuResource($op);
					$priv     = Rhema_Util::getMenuPrivilege($op);
								
					if(!$acl->has($resource)){
						$acl->addResource(new Zend_Acl_Resource($resource));
					}	
					
					if($module != 'admin'){
						$acl->allow(Admin_Model_Role::ROLE_GUEST, $resource, $priv);
					}else{
						$acl->allow(Admin_Model_Role::ROLE_MEMBER, $resource, $priv);
					}
				}
			}
		}

	//	TODO add generic pages to menu by default
/*   		foreach(self::$generic as $module => $data){
			foreach($data as $controller => $arr){
				foreach($arr as $acts){
					$op[Rhema_Constant::MENU_MODULE] = $module;
					$op[Rhema_Constant::MENU_CONTROLLER] = $controller;
					$op[Rhema_Constant::MENU_ACTION] = $acts;
					
					$resource = Rhema_Util::getMenuResource($op);
					$priv     = Rhema_Util::getMenuPrivilege($op);
							
					if(!$acl->has($resource)){
						$acl->addResource(new Zend_Acl_Resource($resource));
					}	

					if($module != 'admin'){
						$acl->allow(Admin_Model_Role::ROLE_GUEST, $resource, $priv);
					}
				}
			}			 
		}*/

		$acl->allow(Admin_Model_Role::ROLE_SUPER);
		
		return $acl;
	}

	public static function initAcl(){
		$tableName = Doctrine_Core::getTable(__CLASS__)->getTableName();
		self::rmsTruncateTable($tableName);
		$mFilter = new Rhema_Filter_FormatModelName();
		
		$arr = array('menu', 'admin_menu', 'ecom_navigation_menu');
		$filter = new Rhema_Dao_Filter();
		$util = Rhema_Util::getInstance();
		
		foreach($arr as $table){
			$rule  = array();
			$roles = array();
			$scope = ($table == 'admin_menu') ? 'admin' : 'site';
			$model = $mFilter->filter($table);
			$filter->setModel($model);
			$result = Rhema_Model_Service::createQuery($filter)->execute();
  
			$roles    = array(1 =>array('title' => 'super')); // Admin_Model_Role::getRoles();

			foreach($roles as $c){
				$r    = strtolower($c['title']);
				foreach($result as $b){
					$res = $util->getMenuResource($b);
					$priv = $util->getMenuPrivilege($b);					
					if($res and $priv){ 
						$rule[$r][$res][$priv] = array('root_id' => $b['root_id'], 'allow' => 1);
					}
				}
			}

			self::addNewRules($rule, $table, $scope);
		}
	}


}