<?php

class Cms_DesignController extends Zend_Controller_Action{

	public $page;
	public $sections;
	public $layout;
	public $template;
	
	protected $_editor;


    public function init(){

        /* Initialize action controller here */
        parent::init();
        $this->_editor = $this->_helper->layoutEditor();
    }


    /**
     * This action is called when the layout tab is clicked via ajax
     * It returns the layout items and a blank layout stage
     * ready for a page to be clicked
     *
     */
    public function indexAction(){
    	/*$request    = $this->getRequest();
		$items   	= $this->getCached()->getLayoutItems();

    	$this->view->layoutItems = $items['html'];

    	if($request->isXmlHttpRequest()){
    		$output = $this->view->render('design/index.phtml');
    		$this->_utility->setAjaxData($output);
    	}*/

    	//$grid   = new Admin_Service_Grid('Admin_Model_Page');
    	//$script = $grid->display();
    }

    /**
     * This action is called on first entry to CMS->Design->Pages
     * It list the sites pages in a grid
     *
     */
    public function pageAction(){
        $this->_request->setParam('table', 'Page');        
    	$this->view->gridIndexRoute = Rhema_Constant::ROUTE_GRID_INDEX ;    	 
    	$this->_helper->displayGrid();    	 
    }

    public function getTip($contentTypeId, $itemId){
    	$contentTypes = $this->_utility->getCached()->getContentTypes();
    	$tableName    = isset($contentTypes['raw'][$contentTypeId]) ? $contentTypes['raw'][$contentTypeId]['AdminTable']['name'] : null;
    	$model        = $this->_utility->table2Model($tableName);
    	return Rhema_Model_Abstract::getToolTip($model, $itemId);
    }
    /**
     * This method generates tool tip information on the page design screen
     * Clicking the icon next to a page, template, element or component calls this action
     * The information about the clicked item is displayed in a tool tip
     *
     */
    public function tooltipAction(){
    	 $request 			= $this->getRequest();
    	 $id	  			= $request->getParam('id', null);
    	 $contentTypeId   	= $request->getParam('content_type_id', null);

    	 if($contentTypeId and $id){
    	 	$this->view->toolTip = $this->getCached()->getTip($contentTypeId, $id);
    	 	$output = $this->view->render('design/tooltip.phtml');
    	 	$this->_utility->setAjaxData($output);

    	 }else{
    	 	$output = 'nothing found';
    	 }
    	 $this->_utility->setAjaxData($output);
    }

	/**
	 *This method cleans up the items posted via ajax when saving a page layout
	 * It also created an array of fields in the specified sequence to be saved
	 * in the pagelayout table
	 *
	 * @param  $arr array
	 * @return multi dimentional array of [fieldtype][field id][field sequence]
	 */
	public function processField($arr){
		$return = array();
		$parts  = explode(',', $arr);
		$seq    = 0;
		for($i=0; $i<count($parts); $i++){
			if('blank' == substr($parts[$i],0,5)) {
				continue;
			}
			list(,$list)            = explode('_', $parts[$i])	;
			//$list = str_replace('item_', '',$parts[$i]);
			list($type, $fieldId)       = explode('-',$list);
			$return[$type][$fieldId]    = 	intval($seq);
			$seq++;
		}

		return $return;
	}


	public function resetLayout(){
		$request	 = $this->getRequest();
		$template_id = $request->getParam('template_id', null);
		$page_id     = $request->getParam('id', null);
		return Admin_Model_PageLayout::resetLayout($page_id, $template_id);
	}

	/**
	 * Saves the layout to the database
	 * Also saves the section sequence for the current template
	 * This change in sequence would affect all pages currently
	 * using the template
	 *
	 */
	public function saveLayout(){
		$request	 = $this->getRequest();
		$sections    = $request->getParam('sections', array());
		$items       = $request->getParam('items', array());
		$template_id = $request->getParam('template_id', null);
		$page_id     = $request->getParam('page_id', null);
		$table       = $this->_getParam('table');

		$sections    = array_filter($sections);
		$items       = array_filter($items);

		$sectionAdd	 = array();
		$itemAdd     = array();

		foreach($sections as $sequence => $data){
			$sxnId = intval($this->_utility->getPiece($data));
			if(isset($items[$sxnId])){
				$itemAdd[$sxnId]    	= $this->processField($items[$sxnId]);
				$sectionAdd[$sxnId]     = intval($sequence);
			}
		}

		$itemAdd = array_filter($itemAdd);

		Admin_Model_TemplateSection::saveSectionOrder($template_id, $sectionAdd);
		Admin_Model_PageLayout::updateLayout($page_id, $template_id, $table, $itemAdd, $sectionAdd);
	}

	public function updatelayoutAction(){
		$request       	= $this->getRequest();
		$layoutSections = $request->getParam('sections');
		$page			= $this->_utility->getSessData('active-page');
		$sectionAdd		= array();
		$itemAdd		= array();
		$return			= array();
		$setup			= array();

		foreach($layoutSections as $suffix => $arr){

			$sectionAdd	= array();
			$table 		= MODEL_PREFIX . $suffix;

			if('Page' == $suffix){
				$page_id  		= $page['Page']['id'];
				$template_id 	= $page['Page']['template_id'];
			}else{
				$page_id  		= $page['Page'][$suffix]['id'];
				$template_id 	= $page['Page'][$suffix]['template_id'];
			}

			foreach($arr as $sxnSeq => $data){
				$itemAdd		= array();

				$str   			= key($data);
				$sxnId 			= intval($this->_utility->getPiece($str));

				$sectionAdd[$sxnId]     = intval($sxnSeq);

				foreach($data[$str] as $itemSeq => $v){
					list(,$list)            	= explode('_', $v)	;
					list($type, $itemId)        = explode('-',$list);
					$return[$type][$itemId]     = intval($itemSeq);
					$itemAdd[$sxnId]    		= $return ;
				}
			}

			$setup[$table]['id']		= $page_id;
			$setup[$table]['template'] 	= $template_id;
			$setup[$table]['item'] 		= $itemAdd;
			$setup[$table]['section'] 	= $sectionAdd;

			foreach($setup as $tab => $dd){
				Admin_Model_PageLayout::updateLayout($dd['id'], $dd['template'], $table, $dd['item'], $dd['section']);
			}
		}



	}

    /**
     * This is the layout work horse that builds the page layout
     * when a page is selected from the list of pages.
     * It displays existing items in their appropriate sections
     *
     */
    public function layoutAction(){
    	$far           = 100;
    	$sxnById       = array();
    	$request       = $this->getRequest();
    	$template_id   = $request->getParam('template_id');
    	$page_id       = $request->getParam('page_id');
    	$title         = $request->getParam('title');
    	$task          = $request->getParam('task', null);
    	$table         = $this->_getParam('table');

    	if($template_id){
	    	switch($task){
	    		case 'savelayout':{
	    			$this->saveLayout();
	    			break;
	    		}

	    		case 'resetlayout':{
	    			$this->resetLayout();
	    			break;
	    		}
	    	}
    	}
		 
		$pageLayout   = Rhema_Model_Service::factory('page_layout');		
    	$res          = $pageLayout->getPageLayout($page_id, $template_id, $table); 
    	
    	$items    	  = $this->_editor->getCached()->getItems();    	
    	$templateData = $this->_editor->getCached()->getTemplateDetails($template_id);
    	$items        = array_merge($items, $templateData);
    	 
    	$this->view->title      = $title;
    	//$this->view->sections 	= $templateData['sections'];
    	$this->view->layout   	= $res['layout']; 
    	 
    	$formUrl['action']		= 'layout';
    	$formUrl['controller']  = 'design';
    	$formUrl['module']      = 'cms';

    	$formUrl['page_id']     = $page_id ;
    	$formUrl['template_id'] = $template_id;
    	$formUrl['title']       = $title;
    	$formUrl['table']       = $table ;

    	$this->view->formAction = $this->view->url($formUrl, ADMIN_ROUTE); 
		$this->view->items      = $items;

    	$output['form']         = $this->view->render('design/layout.phtml');
    	
    	$this->_utility->setAjaxData(Zend_Json::encode($output));
    }

    public function templateAction(){

    }

    public function urlAction(){
    	$request = $this->getRequest();
    	$page_id  = $request->getParam('page_id');
    	$pageUrl = '';

    	if($page_id){
    		$pageMenu = Admin_Model_Menu::getPageMenu($page_id);
    	}

    	if(count($pageMenu)){
    		$curPage  = $pageMenu[0];
    		$parm = array('',
    					$curPage['module'],
    					$curPage['controller'],
    					$curPage['action'],
    					'admin',
    					0,
    					'mode',
    					0
    				);

    		$pageUrl  = $this->baseUrl . implode('/',$parm);
    	}
    	$output['url'] = $pageUrl;
    	$this->_utility->setAjaxData($this->view->json($output));
    }

    public function featureAction(){

    }


    public function formlayoutAction(){
    	$request			= $this->getRequest();
    	$formId 			= $request->getParam('web_form_id');
    	$tableId    		= $request->getParam('table_id');
    	$list       		= $this->_utility->getCached ('admin_table_id')->getLeftNavigation();
    	$table      		= $this->_utility->table2Model($list['raw']['name'][$tableId]);

    	$this->view->fields = Doctrine_Core::getTable($table)->getColumns();

    }

}