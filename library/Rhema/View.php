<?php
class Rhema_View extends Zend_View {
	
	public function url(array $urlOptions = array(), $name = null, $reset = false, $encode = false) {
		if(isset($urlOptions['text']) and strtolower($urlOptions['text']) == 'javascript'){
			unset($urlOptions['text']);
		}
		$router = Zend_Controller_Front::getInstance ()->getRouter ();
		return $router->assemble ( $urlOptions, $name, $reset, $encode );
	}
}