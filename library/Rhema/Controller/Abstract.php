<?php
abstract class Rhema_Controller_Abstract extends Zend_Controller_Action implements Rhema_Cache_Interface{
	protected $_classMethods;
	protected $_cache;
	protected $_frontend;
	protected $_backend;
	protected $_cacheType = 'Class';
	protected $_frontendOptions = array();
	protected $_backendOptions = array();
	protected $_model;
	protected $_tagged;
	protected $_urlParams = array(
			'module' => 'admin',
			'controller' => 'grid',
			'action' => 'table',
			'table' => '');
	protected $_gridParam;

	public $baseHref;
	public $baseUrl;
	public $headerTags = array(
			'page_header_id',
			'page_footer_id',
			'page_body_id',
			'page_id');

/*	public function init(){
		$request = $this->getRequest();
		$config = Zend_Registry::get('config');
		$this->setDefaults($config['auth']);

		$this->_utility = Rhema_Util::getInstance();
		//$this->baseUrl 			= BASE_URL;
		//$this->baseHref			= "http://" . $this->_request->getHeader ( 'host' ) . BASE_URL;
		$this->view->rhemaLink = $this->getBaseLink();
		//
		$request = $this->getRequest();
		$this->view->rhemaLink = $this->_request->getScheme() . '://' . $this->_request->getHttpHost() . $this->_request->getBasePath() . '/';

		$this->router = Zend_Controller_Front::getInstance()->getRouter();
		$search = $this->_request->getParam('_search', null);
		//vd($this->view->rhemaLink);
		if($search === null and $this->_request->getParam('isControlPanel')){
			$this->_request->setParam('ajx', 1);
		}

		Zend_Registry::set('curModule', $this->_request->getModuleName());
	}*/

	/*public function preDispatch(){
		$position = $this->getRequest()->getParam('element-position', false);
		if($position){
			$this->_helper->viewRenderer->setResponseSegment($position);
		}
	}
 

	public function setDefaults($options = array()){
		foreach($options as $index => $val){
			$this->$index = $val;
		}
	}

	public function displayGrid($options = array(), $set = true){
		$ajax   = $this->_request->isXmlHttpRequest();
		$margin = $this->_request->getParam('gridMargin');

		if(! isset($options['model'])){
			$options['model'] = $this->_table;
		}

		if(preg_match('/(page|page_header|page_footer)/i', $this->_table)){
			$this->view->formAction     = $this->view->url(array('table' => $this->_table), 'layout-manager');
			$this->view->layoutItems    = $this->_helper->getHelper('layoutEditor')->getPageItems();
		}

		if(! isset($options['editurl'])){
			$urlParams = array_merge($this->_urlParams, array(
										'rootType' => $this->_request->getparam('rootType', ''),
										'table' => $this->_table)
						);
			$editUrl = $this->view->url($urlParams, 'grid-model-save');
			$options['gridParam']['editurl'] = $editUrl;
		}

		if($margin){
			$options['gridMargin'] = $margin ;
			//$options['gridParam']['width'] = $width;
		}
		//$options['gridParam']['shrinkToFit'] = true;
		//$options['gridParam']['forceFit'] = true;
		//$options['gridParam']['autowidth'] = true;
		
		$gridService = new Rhema_Grid_Service($options);
		$grid = $gridService->setType('jqGrid')->generateGrid();

		$displayAjax = $this->_getParam('ajx', null);

		$return = '';

		if($ajax){
			if($displayAjax){
				$script = $gridService->ajaxDespatch($grid);
				if($set){
					$this->_utility->setAjaxData($script);

				}else{
					$return = $script;
				}
			}else{
				$grid->deploy();
			}
		}else{
			$this->view->caption = $grid->getJqgParam('caption', 'Grid Table');
			$this->view->gridData = $grid->deploy();
			$this->view->gridId = $grid->makeGridId();
		}

		return $return;
	}*/

	public function getGrid($table, array $params = array(), $edit = array()){
		$modelFilter = new Rhema_Filter_FormatModelName();
		$table = $modelFilter->filter($table);
		$request = $this->getRequest();
		$params = count($params) ? $params : $this->_request->getParams();
		$tableGrid = $this->_getGridId($table);

		if(! isset($this->_gridParam['editurl'])){
			$this->_gridParam['editurl'] = $this->view->url(array(
					'table' => $table), Rhema_Constant::ROUTE_GRID_SAVE);
		}

		$grid = new Admin_Service_Grid($tableGrid, $table, $this->_gridParam, $params, $edit);
		$this->view->caption = $grid->getJqgParam('caption', 'Grid Table');

		return $grid;
	}

	public function getAuthService(){
		if(Zend_Registry::isRegistered('auth_server')){
			return Zend_Registry::get('auth_server');
		}else{
			$server = new Admin_Service_Authenticate($this->_model, $this->_identityColumn, $this->_credentialColumn);
			Zend_Registry::set('auth_server', $server);
			return $server;
		}
	}

	public function getTypes(){
		return Help_Model_HelpType::listAllTypes();
	}

/*	//================== implement cache interface ==============================
	public function setCache(Rhema_Cache_Abstract $cache){
		$this->_cache = $cache;
	}

	public function setCacheOptions(array $options){
		$this->_cacheOptions = $options;
	}

	public function getCacheOptions($type = 'class-file'){
		if(empty($this->_cacheOptions[$type])){
			$this->_cacheOptions[$type] = $this->_utility->getCacheOptions($type, $this);
		}

		return $this->_cacheOptions[$type];
	}

	public function getCached($tagged = null){
		if(!Rhema_SiteConfig::getConfig('settings.use_cache')){
			return $this;
		}

		if(null == $this->_cache){
			$this->_cache = new Rhema_Cache($this, $this->getCacheOptions());
		}

		$this->_cache->setTagged($tagged);
		return $this->_cache;
	}

	//======================================================================================*/
	/**
	 * This is the tree work horse. It is called via ajax when a menu is modified
	 * It ensures that menu items are stored in the correct hierarchy.
	 *
	 */

	/*public function updateTree(){
		$request 	= $this->getRequest();
		$task 		= $this->_request->getParam('task', null);
		if($task){
			$filter 	= new Rhema_Filter_FormatModelName();
			$node 		= $this->_request->getParam('node', null);
			$refNode 	= $this->_request->getParam('refNode', null);
			$rootType 	= $this->_request->getParam('rootType', null);
			$type 		= $this->_request->getParam('type', null);
			$model 		= $this->_request->getParam('table', null);

			$model = $filter->filter($model);
			$table = Doctrine_Core::getTable($model);

			if($node){
				$node_id = Rhema_Util::getIdFromNode($node);
				$nodeMenu = $table->find($node_id);
			}

			if($refNode){
				$ref_id = Rhema_Util::getIdFromNode($refNode);
				$refMenu = $table->find($ref_id);
			}

			switch($task){
				case 'create' :
					{
						$label = $this->_request->getParam('nodeText');
						$row = new $model();
						$option = array(
							'title'	=> $label,
							'label'	=> $label  . rand(0,50),
						);
						$nodeMenu 			    = Admin_Model_AdminMenu::getDefaultRow($option, $model);
						$nodeMenu->m_controller = Zend_Controller_Front::getInstance()->getDefaultControllerName();
						$nodeMenu->state('TDIRTY');
						$nodeMenu->save();

						//$rootMenu   = $table->find($root_id);
						$node_id = $nodeMenu->id;
						if('after' == $type){
							$nodeMenu->getNode()->insertAsNextSiblingOf($refMenu);
						}elseif('before' == $type){
							$nodeMenu->getNode()->insertAsPrevSiblingOf($refMenu);
						}elseif('inside' == $type){
							$nodeMenu->getNode()->insertAsLastChildOf($refMenu);
						}
						break;
					}
				case 'move' :
					{
						if('after' == $type){
							$nodeMenu->getNode()->moveAsNextSiblingOf($refMenu);
						}elseif('before' == $type){
							$nodeMenu->getNode()->moveAsPrevSiblingOf($refMenu);
						}elseif('inside' == $type){
							$nodeMenu->getNode()->moveAsLastChildOf($refMenu);
						}
						break;
					}
				case 'rename' :
					{
						$label = $this->_request->getParam('nodeText');
						$nodeMenu->title = $label;
						$nodeMenu->label = $label;
						$nodeMenu->save();
						break;
					}

				case 'delete' :
					{
						$nodeMenu->getNode()->delete();
						break;
					}
				default :
			}

			Rhema_Cache::clearCacheOnUpdate($model);
			$return = array('node_id' => $node_id);
			$this->_helper->json->sendJson($return);
			exit();
		}
	}*/

	public function getSuffix($type){
		switch($type){
			case 'admin' :
				$suffix = 'admin_menu';
				;
				break;
			case 'stock' :
				$suffix = 'ecom_navigation_menu';
				break;
			default :
				$suffix = 'menu';
		}

		return $suffix;
	}

/*	public function getModuleData(){
		$module = $this->getRequest()->getModuleName();
		$moduleObject = Rhema_Model_Service::factory('admin_module');
		return $moduleObject->getModuleContent($module);
	}*/

	public function removeCacheFiles(){
		if(! NO_CACHE){
			$cache = $this->getInvokeArg('pageCache');
			$cache->clean();
		}
		/*
		$tags      = $this->headerTags;
		$tags[]    = MODEL_PREFIX . 'Menu';
		$siteCache = new Rhema_Cache();
		$siteCache->removeCacheByTag($tags);
 */
	}

	protected function showDefaultTableGrid(){
		$request = $this->getRequest();
		$urlParm = $this->_urlParams;
		$urlParm['table'] = $this->_table;
		$this->_gridParam['url'] = $this->router->assemble($urlParm, ADMIN_ROUTE);

		$grid = $this->getGrid($this->_table);
		$this->displayGrid($grid);
	}

	private function _getGridId($table){
		$cssFilter = new Rhema_Filter_FormatCssClassName();
		$params = array(
				$table,
				Rhema_Constant::GRID_NODE,
				$this->_request->getParam('type_id', ''),
				$this->_request->getParam('page_type', ''),
				$this->_request->getParam('rootType', ''),
				$this->_request->getParam('root_id', ''));

		$params = array_filter($params);
		$id = implode('-', $params);
		return $cssFilter->filter($id);
	}

/*	protected function _sendAjaxMessage($message, $title = null, $type = Rhema_Dto_UserMessageDto::TYPE_WARNING, $autoclose = null){
		$autoclose     = ($autoclose === null)						 
						 ? (($type == Rhema_Dto_UserMessageDto::TYPE_SUCCESS)? true : false)
						 :  $autoclose ;
		$userMessage   = new Rhema_Dto_UserMessageDto($message, $title, $type, $autoclose);
		$return        = $this->view->printUserMessage($userMessage);

		$this->_response->setBody($return)
						->sendResponse();
		exit();
	}*/
}