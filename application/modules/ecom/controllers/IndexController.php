<?php

class Ecom_IndexController extends Zend_Controller_Action{

    public function init(){

        /* Initialize action controller here */
        parent::init();

    }

    public function indexAction(){
 		 
    }

    public function settingAction(){
    	$this->_table     				= ADMIN_PREFIX . 'AdminSetting';
        $url			 				= $this->_urlParams;
        $url['table']	 				= $this->_table;
        $url['setting']					= $this->_getParam('module');
        $this->_setParam('setting', $url['setting']);

    	$this->_gridParam['url']  		= $this->_utility->assemble($url, ADMIN_ROUTE);
    	$url['action']   				= 'save';
    	$this->_gridParam['editurl']  	= $this->_utility->assemble($url, ADMIN_ROUTE);
    	$this->_gridParam['canadd']		= 'false';
    	$this->_gridParam['candel']		= 'false';
    	$this->_gridParam['width']      = 850;

    	$grid							= $this->getGrid($this->_table);
    	$this->displayGrid($grid);
    }

    public function attributeAction(){
    	$tempId     	= $this->_getParam('template_id', null);
    	$table      	= $this->_getParam('table');
    	$url	    	= array('model'=>'ecom','controller'=>'index','action'=>'update','table'=>$table,'template_id'=>$tempId);
    	$output	    	= '';
    	$displayType 	= 0;

    	if($tempId){
    		$attrObject = Rhema_Model_Service::factory('ecom_template_attribute');
    		$this->view->formAction     = $this->router->assemble($url, ADMIN_ROUTE);
    		$this->view->saved      = $attrObject->getAttributes($tempId);
    		$this->view->attr       = $this->getCached()->getDisplayAttributes($displayType);
    		$this->view->table      = $table;
    		$this->view->templateId = $tempId;
    		$this->view->title      = $this->_getParam('title');
   			$output['form']			= $this->view->render('index/attribute.phtml');
    	}
   		$this->_utility->setAjaxData(Zend_Json::encode($output));

    }

    public function getDisplayAttributes($displayType = 0){
    	$parm	= $displayType ? array('is_product' => $displayType) : array();
    	return Rhema_Model_Abstract::_getAllRecords('Ecom_Model_EcomAttribute', $parm);
    }

    public function updateAction(){
    	$table = $this->_getParam('table');
    	$str   = $this->_getParam('str');
    	$labs  = $this->_getParam('labels','');
    	$id    = $this->_getParam('template_id');

    	parse_str($str, $attr);
    	parse_str($labs,$labels);

    	$attrObject = Rhema_Model_Service::factory('ecom_template_attribute');

    	if(isset($attr['li_attr']) and $id){
    		$attrObject->updateAttributes($id, $attr['li_attr'],$labels);
    	}
    	$this->_utility->setAjaxData('saved');
    }

    public function templateAction(){
		$this->_table		= 'ecom_display_template';
		$options['table']       = $this->_table;
		//$options['url']  		= $this->_utility->assemble($this->_urlParams, ADMIN_ROUTE);
		$this->displayGrid($options);
    }

    public function addcategoryAction(){    	$productId      = $this->_getParam('product_id');
    	$output			= array();
    	$data           = array();
    	$task           = $this->_getParam('task', null);

    	$prodCat		= Rhema_Model_Service::factory('ecom_proc=duct_category');
    	$navObject      = Rhema_Model_Service::factory('ecom_proc=duct_category');

    	if($productId){
	    	if('save' == $task){
	    		$cats =  $this->_getParam('category_id', array());
	    		$output['message']      = $prodCat->updateProductCategory($productId, $cats);
	    	}else{
		    	$this->view->allCat     = $navObject->getLeafRecords();
		    	$selected               = $prodCat->getProductCategory($productId);

		    	foreach($selected as $arr){
		    		$data[$arr['ecom_category_id']] = 1;
		    	}
		    	$this->view->selected   = $data;
		    	$output['form']         = $this->view->render('index/addcategory.phtml');
	    	}
    	}
    	$this->_utility->setAjaxData(Zend_Json::encode($output));
    }

    public function  updatecategoryAction(){

    }

}