<?php
class Rhema_Controller_Action_Helper_GetModuleData extends Zend_Controller_Action_Helper_Abstract {
	
	public function getModuleData(){
		return $this->direct();
	}
	public function direct(){
		$module = $this->getRequest()->getModuleName();
		$moduleObject = Rhema_Model_Service::factory('admin_module');
		return $moduleObject->getModuleContent($module);
	}
}