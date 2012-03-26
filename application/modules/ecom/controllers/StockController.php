<?php

class Ecom_StockController extends Zend_Controller_Action{

    public function init(){

        /* Initialize action controller here */
        parent::init();
		$this->_gridParam['width']      = 870;
    }

    public function indexAction(){

    }

    public function navigationAction(){
   		$request    		= $this->getRequest();
   		$request->setParam('rootType', 'stock');
   		$this->_helper->displayGrid();
    }

    public function deliveryMethodAction(){
     	$this->_table = 'ecom_delivery_method';
    	$this->_helper->displayGrid();
    }


    public function categoryAction(){
    	$this->_table = 'ecom_category';
    	$this->_helper->displayGrid();
    }

    public function productAction(){
    	$this->_table 				=  'ecom_product';
    	$this->_helper->displayGrid();
    }

    public function imageAction(){

    }

    public function ordersAction(){

    }

    public function persAction(){
    	$task   = $this->_getParam('task', 'list');
    	$data   = '';
    	if($task){
    		$data = $this->view->layout()->render('per' . $task);
    	}

    	$this->_utility->setAjaxData($data);
    }
}