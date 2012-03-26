<?php
 
class Rhema_View_Helper_AddGooglePlusOne extends Zend_View_Helper_Abstract {
 
	public function addGooglePlusOne($name = '', $desc = ''){ 
		$this->view->headScript()->appendFile('https://apis.google.com/js/plusone.js');  
		$lang = Zend_Registry::get('Zend_Locale')->toString();
		$lang = str_replace('_', '-', $lang) ;
		$this->view->collateScripts(
			"window.___gcfg = {lang: '{$lang}'};"
		);		 
		return "<g:plusone></g:plusone>";
 	}
}