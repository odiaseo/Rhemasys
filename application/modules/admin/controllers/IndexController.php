<?php
class Admin_IndexController extends Zend_Controller_Action{
	
	/**
	 * Authetication service
	 *
	 * @var unknown_type
	 */
	protected $_authService;
	protected $_utility;
	
	public function init(){
		/* Initialize action controller here */
		parent::init();
	}
	
	public function indexAction(){
		 
	}
	
	public function pageAction(){
		$gridHelper 				 = $this->_helper->getHelper('DisplayGrid');
		$this->_table 				 = MODEL_PREFIX . 'Template';
		$urlParms 					 = $gridHelper->getUrlParams();
		$urlParms['action'] 		 = 'save';
		$urlParms['table'] 			 = $this->_table;		
		$this->_gridParam['editurl'] = $this->view->url($urlParms, ADMIN_ROUTE); // baseUrl . '/admin/grid/save/table/' . $this->_table;
		 
		$this->_request->setParam('table', ADMIN_PREFIX . 'Template');
		$gridHelper->displayGrid($this->_gridParam);
	}
	
	public function adminaccessAction(){  
		$table      = 'admin_menu';		 
		if('updateAcl' == $this->_getParam('task')){
			$this->_helper->accessControl->updateAcl($table); 
		}
		$this->_helper->accessControl->showTabs($table);
	}
		
	public function registryAction(){
		$this->_request->setParam('table', ADMIN_PREFIX . 'AdminModule');
		$this->_helper->displayGrid();
	}
	
	public function settingAction(){
		$this->_request->setParam('table', ADMIN_PREFIX . 'AdminSetting');
		$this->_helper->displayGrid();
	}
	
	public function menuAction(){
		    
    //  $bootstrap = $this->getInvokeArg('bootstrap');  
   //   $userAgent = $bootstrap->getResource('useragent');
    // pd($userAgent->getDevice()); 
		$table = 'admin_menu';
		$this->_helper->setupMenuTab($table);
	}

	
	public function formAction(){
		$this->_request->setParam('table', MODEL_PREFIX . 'WebForm');
		$this->_helper->displayGrid();
	}
	
	public function sysInfoAction(){ 

		$info = array(
				'RhemaSys Version' => 'test', 
				'Home Directory' => SITE_DIR, 
				'Application Env' => APPLICATION_ENV, 
				'Application Path' => APPLICATION_PATH, 
				'ServerName' => $this->_request->getServer('SERVER_NAME'), 
				'Zend_Version::VERSION' => Zend_Version::VERSION, 
				'jQuery Version' => ZendX_jQuery::DEFAULT_JQUERY_VERSION, 
				'jQuery UI Version' => ZendX_jQuery::DEFAULT_UI_VERSION, 
				'BvbGrid Version' => Bvb_Grid::VERSION);
	 
		$this->view->configs    = Zend_Registry::get('config');
		$this->view->serverInfo = $info; 
	}
	
	public function phpinfoAction(){
 		$this->view->apcInfoPath = realpath(SITE_PATH . '/../') . '/apc.php';
	}
 
}