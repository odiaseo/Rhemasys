<?php
/**
 *
 * @author Pele
 * @version 
 */
 
/**
 * IncludeJs helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Rhema_View_Helper_CollateScripts extends Zend_View_Helper_Abstract {
 
	
	public function collateScripts($script = '', $pos = Rhema_Constant::APPEND) {
		
		if(Zend_Registry::isRegistered(Rhema_Constant::SCRIPT_INDEX)){
			$scriptArray = Zend_Registry::get(Rhema_Constant::SCRIPT_INDEX);
		}else{
			$scriptArray = array();
		}
		
		if($pos == Rhema_Constant::PREPEND){
			array_unshift($scriptArray, $script);
		}else{
			array_push($scriptArray, $script);
		}
 
		Zend_Registry::set(Rhema_Constant::SCRIPT_INDEX, $scriptArray);
		
		return null;
	}
}
