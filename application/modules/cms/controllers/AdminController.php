<?php

class Cms_AdminController extends Zend_Controller_Action{
	
	public function init(){
		
		/* Initialize action controller here */
		parent::init();
	
	}
	
	public function indexAction(){

	}
	
	public function settingAction(){
		$util = Rhema_Util::getInstance();	
		$form = new Rhema_Form_AutoForm(array(
            'model' => 'Admin_Model_AdminSubsite',
            'action' => $this->view->url(),
            'method' => 'post',
			'generateManyFields' => false
        ));
        
        if($this->_request->isPost()){
			$siteConfig = $util->getSessData(Rhema_Constant::SITE_CONFIG_KEY);
			$subsiteId  = $siteConfig['subsite']['id'];
			$model      = MODEL_PREFIX . 'AdminSubsite' ; 
			$data       = Rhema_Model_Abstract::getRemoteData('getElement', array($model, $subsiteId));       	
        }else{
        	$siteConfig = $util->getSessData(Rhema_Constant::SITE_CONFIG_KEY);
        	$data       = $siteConfig['subsite'];
        }
 		        
		$form->populate($data);
        $this->view->form = $form;
	}
	
	public function helpAction(){
 		$this->_request->setParam('treeGrid', true);
		$option['model'] = 'role'; 
		
		$gridService = new Rhema_Grid_Service($option);		
		$grid        = $gridService->setType('jqGrid')
						           ->generateGrid() ;
		//$grid->addExtraColumns($gridService->getActionBarColumn());
		 
	 
 	 
		//$form = new Bvb_Grid_Form(new Rhema_Form_AutoForm());
		//$grid->setSource($source);
		
	//	$form->setAdd(true)->setEdit(true)->setDelete(true)->setAddButton(true);
		//$grid->setForm($form);
		 
		

        
		//$grid->updateColumn('title', array('title' => 'City name', 'width' => 260));
		//$grid->updateColumn('id', array('title' => '#ID', 'hide' => false));
		//$grid->setCache(array('use'=>array('form'=>'true|false', 'db'=>'true|false'), 'instance'=>Zend_Cache $cache,'tag'=>'my_tag'));
/*		 $grid->setJqgParams(array('caption' => 'jqGrid Example', 'forceFit' => true,		          
			'rowList' => array(10, 15, 50),  
			'altRows' => true) 
			);*/
		//	 $grid->setAjax('test');
		$this->view->grid = $grid->deploy();
	}
 
	public function g1ActionBar($id) {
        $helper = new Zend_View_Helper_Url();
        $actions = array(
            array('href'=>$helper->url(array('action'=>'do', 'what'=>'view', 'id'=>$id)), 'caption'=>'View', 'class'=>'{view}'),
            array('href'=>$helper->url(array('action'=>'do', 'what'=>'edit', 'id'=>$id)), 'caption'=>'Edit', 'class'=>'{edit} fixedClass'),
            array('href'=>$helper->url(array('action'=>'do', 'what'=>'delete', 'id'=>$id)), 'caption'=>'Delete', 'class'=>'{delete}'),
            array('onclick'=>new Zend_Json_Expr('alert("you clicked on ID: "+jQuery(this).closest("tr").attr("id"));'), 'caption'=>'Alert Me')
        );
        return Bvb_Grid_Deploy_JqGrid::formatterActionBar($actions);
    }

    
    public function translationAction(){ 
    	$options = Rhema_SiteConfig::getConfig('settings');
		$logDir  = $options['log_dir'];
		if(!file_exists($logDir)){
			mkdir($logDir, 0777, true);
		}
		 
		$this->view->logDir   = rtrim($logDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$this->view->files    = $options['log_files'];
		$this->view->routeTmxFilename   = Rhema_Util_TmxGenerator::getTmxFilename(false, false, 'route'); 
		$this->view->contentTmxFilename = Rhema_Util_TmxGenerator::getTmxFilename();
     	$this->_request->setParam('table', 'translation');
    	$this->_helper->displayGrid();    	
    }

 
}