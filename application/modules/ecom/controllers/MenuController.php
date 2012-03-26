<?php
class Ecom_MenuController extends Zend_Controller_Action{
	
	public function indexActon(){
	
	}
	
	public function productcrumbAction(){
		$option = array(
				'title' => 'Main');
		$nav = $this->_utility->getCached('tree')->getMainMenu($option, 'ecom_navigation_menu');
		
		if(isset($nav['container'])){
			$container = $nav['container'];
		}else{
			$container = new Zend_Navigation(array());
		}
		
		$this->view->menuContainer = $container;
	}
	
	public function treeAction(){
		$request = $this->getRequest();
		$title = $request->getParam('title', 'Main');
		$option = array(
				'title' => $title);
		$result = $this->_utility->getCached('tree')->getMainMenu($option, 'ecom_navigation_menu');
		$this->view->navData = $result;
		$this->view->treeLabel = 'Product Categories';
	}
	
	public function productNavigationAction(){
		$table = 'ecom_navigation_menu';
		$this->_helper->setupMenuTab($table);
	}
	
	public function ecomaccessAction(){
		$table = 'ecom_navigation_menu';
		if('updateAcl' == $this->_getParam('task')){
			$this->_helper->accessControl->updateAcl($table);
		}
		$this->_helper->accessControl->showTabs($table);
	}
	
	public function affiliateCategoryMenuAction(){
		$table = 'affiliate_product_category';
		$this->_request->setParam('minDepth', 1);
		$this->_helper->setupMenuTab($table);		
	}

}