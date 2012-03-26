<?php
class Rhema_Controller_Action_Helper_AccessControl extends Zend_Controller_Action_Helper_Abstract {
	
	protected $_view = null;
	protected $_request = null;
	
	public function __construct(){
		$this->_request = $this->getRequest();
		$this->_view = Zend_Layout::getMvcInstance()->getView();
	}
	
	public function showTabs($table){		
		$menuObject = Rhema_Model_Service::factory($table);
		$aclObject = Rhema_Model_Service::factory('admin_acl');		
		$task = $this->_request->getParam('task', null);		
		$return = $this->_request->getParam('returnto', null);		
		$this->_view->roots = $menuObject->getRoots($menuObject->getModel());
		$this->_view->table = $table;
	}
	
	public function updateAcl($table){		
		$rules = array();
		$data = $this->_request->getParam('rule'); 
		$rootId = $this->_request->getParam('root_id', null);
		$util = Rhema_Util::getInstance();
		$modFilter = new Rhema_Filter_FormatModelName();
		$svc = new Rhema_Model_Service();
		
		$menuTable = $modFilter->filter($table);
						
		$menuFilter = new Rhema_Dao_Filter();
		$menuFilter->setModel($menuTable)
				   ->addCondition('root_id', $rootId);	
		$menuList = $svc->createQuery($menuFilter)->execute();
		
		for($i=0; $max=count($data), $i<$max; $i+= 1){
			list($menuId, $role) = explode('_', $data[$i]['name']);
			$res = $util->getMenuResource($menuList[$menuId]);
			$priv = $util->getMenuPrivilege($menuList[$menuId]);
			
			if($res and $priv){
				$rules[$role][$res][$priv] = array('allow' => $data[$i]['value'], 'root_id' => $rootId);
			}
		}
		$aclObject = Rhema_Model_Service::factory('admin_acl');
		$aclObject->updateAcl($rules, $table, $rootId);	
	}
}