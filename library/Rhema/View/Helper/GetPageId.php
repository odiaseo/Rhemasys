<?php
 
 
/**
 * Create unique Id for the current page
 * @author Pele
 *
 */
class Rhema_View_Helper_GetPageId extends Zend_View_Helper_Abstract {
  
	public function getPageId() { 
		$request         = Zend_Controller_Front::getInstance()->getRequest();
		$params[]        = $request->getActionName();
		$params[]        = $request->getParam(Rhema_Constant::MENU_FRONTEND_KEY, ''); 
		
		$params          = array_filter(array_unique($params));
		return strtolower(implode('-', $params)); 
	}
	 
}
