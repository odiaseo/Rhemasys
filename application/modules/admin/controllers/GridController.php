<?php

class Admin_GridController extends Zend_Controller_Action{
	
	protected $_baseUrl;
	protected $_gridUrl;
	protected $_table;
	protected $_config;
	
	public function init(){
		
		/* Initialize action controller here */
		parent::init();
		$this->_urlParams = array();
		$this->_table = $this->_request->getParam('table');
		$rootOnly = $this->_request->getParam(Admin_Model_AdminMenu::TYPE_ROOTS_ONLY);
		$ophanOnly = $this->_request->getParam(Admin_Model_AdminMenu::TYPE_OPHANS);
		
		if($rootOnly){
			$this->_urlParams[Admin_Model_AdminMenu::TYPE_ROOTS_ONLY] = true;
		}elseif($ophanOnly){
			$this->_urlParams[Admin_Model_AdminMenu::TYPE_OPHANS] = true;
		}
 
	}
	
	public function indexAction(){
		$urlTables = array('affiliate_feed');
		$request = $this->getRequest();
		if(in_array($this->_table, $urlTables)){
			$options['preGenerateHooks'] = array(
    			'addFeedColumns' => array()
    		);
		}
		if($request->isXmlHttpRequest()){
			$options['urlParams'] = $this->_urlParams ;
			$this->_helper->displayGrid($options);
		}
	}
	
	public function tableAction(){
		$gridMargin   = 360;
		$options      = array();
		$util		  = Rhema_Util::getInstance();
		$options['gridMargin']  = $gridMargin;
		$this->view->gridMargin = $gridMargin; 
		$this->_request->setParam('table',$this->_request->getParam('table', 'admin_element'));
		
		if(! $this->getRequest()->isXmlHttpRequest()){
			$list = $util->getCached('admin_table_id')->getLeftNavigation();
			$this->view->adminTables = $list['bycat'];
		}
		
		//$options['gridParam']['width'] = 650; //TODO move to config file
		$this->_helper->displayGrid($options);
	}
	
	public function exportAction(){
		// construct JqGrid and let it configure
		$request = $this->getRequest();
		$table = $this->_getParam('table');
		$grid = Bvb_Grid::factory('Bvb_Grid_Deploy_JqGrid', Zend_Registry::get('grid-config'), '', array());
		$parm['factory'] = true;
		$con = new Admin_Service_Grid('', $table, $parm, $request->getParams());
		
		$con->configure($grid, $table);
		$con->ajax(get_class($grid));
		$this->view->g1 = $grid->deploy();
		
		$this->render('index');
	}
	
	public function licenceAction(){
		$request = $this->getRequest();
		$task    = $request->getParam('task', null);
		$ssid    = $request->getParam('ssid', null);
		$params  = $request->getParams();
		$html    = '';
		 
		switch($task){
			case 'save' :  
				if($ssid){						
					$return = Rhema_Model_Service::factory('admin_subsite_licence')->saveSiteLicence($params, $ssid); 
					$this->_helper->sendAjaxMessage($return['message'], 'Update Licence', $return['type']);
				}
				break;				 
			default :		
				$licObject = Rhema_Model_Service::factory('admin_licence');		
				$siteLics  = $licObject->getSiteLicences($ssid);
				$this->view->selected = Rhema_Util::generateOptionArray($siteLics, 'id', 'id');
				
				$allLics = $licObject->findAll(null, $licObject->getModelName());
				$this->view->multiOpt = Rhema_Util::generateOptionArray($allLics, 'id', 'title');
				$html = $this->view->layout()->render('grid/licence');
		}
		
		$this->_response->setBody($html)
					    ->sendResponse();
		exit(); 
	}
	
	public function sectionAction(){
		$request = $this->getRequest();
		$task = $request->getPost('task', null);
		$templateId = $request->getPost('template_id', null);
		$params = $request->getParams();
		
		if($templateId){
			switch($task){
				case 'save' :
					{
						$toUpdate = $this->_request->getParam('section_id');
						$done     = Admin_Model_TemplateSection::updateTemplateSection($templateId, $toUpdate);
						 
						$this->_helper->sendAjaxMessage($done['message'], 'Update Template Sections', $done['type']);
						
						break;
					}
				case  'list': 
				default :
					{
						$adminSection = Rhema_Model_Service::factory('admin_section');
						$tempObject   = Rhema_Model_Service::factory('template');
						$selectedList = array();
						$sortedList   = array();
						
						$result       = $tempObject->getActiveSections($templateId);
						
						if(count($result)){
							foreach($result[$templateId]['TemplateSections'] as $item){
								$sectionId = $item['AdminSection']['id'];
								$tempSxns[$sectionId]     = $sectionId;
								$selectedList[$sectionId] = $item['sequence'];
							}
						}else{
							$tempSxns = array();
						}
						$allSections  = $adminSection->getAllSections();
						
						$this->view->selected = $tempSxns ;
						$res = $this->_utility->getCached()->generateOptionArray($allSections, 'id', 'title');
						
						foreach($res as $key => $item){
							if(isset($selectedList[$key])){
								$selectedList[$key] = $item . ' (' . $selectedList[$key] . ')'; 
							}else{
								$sortedList[$key]   = $item;
							}
						}
						 
						$this->view->multiOpt    = $selectedList + $sortedList;
						$this->view->template_id = $templateId;
						
						$html = $this->view->render('grid/section.phtml');						 
						$this->_response->setBody($html)->sendResponse();
						exit();					
					}
			}
		
		}
	
	}
	
	public function optionAction(){
		$model = $this->_getParam('gridmodel');
		$table = $this->_request->getParam('table');
		$object = Rhema_Model_Service::factory($model);
		$select = $object->buildGridSelectOption($model, $table);
		
		$this->_response->setBody($select)
					    ->sendResponse();
		exit();
 
	}
	
	public function widgetAction(){
		$return = $this->getCached('widget')->getAllWidgets();
		$this->_utility->setAjaxData($return);
	}
	
	public function getAllWidgets(){
		$options = $this->_utility->buildWidgetList(new DirectoryIterator(WIDGET_PATH . '/Controller'));
		return $this->view->formSelect('widget', '', null, $options);
	}
}