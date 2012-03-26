<?php
class Admin_AjaxController extends Zend_Controller_Action{
	private $_config;
	protected $_schemaBlocks = array(
			'admin',
			'blog',
			'ecom',
			'help',
			'affiliate',
			'storefront'
	);
 
	public function init(){
		parent::init();
		$this->_config  = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();
		$this->_utility = Rhema_Util::getInstance();
		

		$manager = Doctrine_Manager::getInstance();
		$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_AGGRESSIVE);	
		
	}

	public function preDispatch(){
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	}
	
	public function dirListAction(){
		$path = '/';
		$parms = $this->_request->getPost();
		$dirOnly = $this->_request->getPost('d', false);
		$actions = $this->_request->getPost('a', false);
		$module = $this->_request->getPost('m', null);
		$contr = $this->_request->getPost('c', null);

		if($module){
			$path .= 'modules/' . $module . '/views/scripts/';
		}
		if($contr){
			$path .= $contr . '/';
		}

		$arr = $this->_utility->getCached()->getDir($path, $dirOnly, $actions, false);
		$this->_helper->json->sendJson($arr);

	}

	public function clearCacheAction(){		
		$type      = $this->_request->getParam('type');	
		$msg       = array();
		$count     = 0;	
		$cachePath = '';
		$filter    = new Zend_Filter_RealPath();
		switch($type){
			case 'backend-dynamic':{ 
				$cachePath = $filter->filter(APPLICATION_PATH . '/../sites/' . SITE_DIR .  '/backend');
				$count     = $this->_utility->cleanupDirectory($cachePath);
				//if(!file_exists($cachePath)){
					//mkdir($cachePath, 0755, true);
				//}
				$msg[]     = $cachePath ;
				$msg[]     = $count . ' files deleted';
								
				break;
			}
			case 'backend-static':{ 
				$cachePath = $filter->filter(SITE_PATH . '/../backend/merged');
				$count     = $this->_utility->cleanupDirectory($cachePath);
				//mkdir($cachePath, 0755, true);
				$msg[]     = $cachePath ;
				$msg[]     = $count . ' page cache files deleted';				
				break;
			}
			case 'frontend-dynamic':{
				$cachePath = $filter->filter(APPLICATION_PATH . '/../sites/' . SITE_DIR . '/cache');
				$count     = $this->_utility->cleanupDirectory($cachePath);
				//mkdir($cachePath, 0755, true);
				$msg[]     = $cachePath;
				$msg[]     = $count . ' class cache files deleted';				
				break;
			}
			
			case 'frontend-static':{
				$theme = Zend_Registry::get(Rhema_Constant::SITE_THEME_KEY) ;
				$cachePath = $filter->filter(SITE_PATH . '/' . $theme .  '/merged');
				$count     = $this->_utility->cleanupDirectory($cachePath);
				//mkdir($cachePath, 0755, true);
				$msg[]     = $cachePath;
				$msg[]     = $count . ' page cache files deleted';					
				break;
			}
					
			case 'page':{
				$cachePath = $filter->filter(APPLICATION_PATH . '/../sites/' . SITE_DIR . '/cache/page');
				$count     = $this->_utility->cleanupDirectory($cachePath);
				$msg[]     = $cachePath;
				$msg[]     = $count . ' page cache files deleted';				
				break;
			}
					
			case 'db':{
				$manager   = Doctrine_Manager::getInstance();
				$qCache    = $manager->getAttribute(Doctrine_Core::ATTR_QUERY_CACHE);
		
				if($qCache and method_exists($qCache, 'deleteAll')){
					$qCount  = $qCache->deleteAll();
					$msg[]   = $qCount . ' database cache entries deleted';
				}else{
					$msg[]   = 'Database cache not found';
				}	
				break ;			
			}

			case 'all':
			{
				$cachePath = $filter->filter(APPLICATION_PATH . '/../sites/' . SITE_DIR . '/cache') . '/';
				$count     = $this->_utility->cleanupDirectory($cachePath);
				//mkdir($cachePath, 0755, true);
				$msg[]     = $cachePath;
				$msg[]     = $count . ' class cache files deleted';
		 
				$manager   = Doctrine_Manager::getInstance();
				$qCache    = $manager->getAttribute(Doctrine_Core::ATTR_QUERY_CACHE);
		
				if($qCache and method_exists($qCache, 'deleteAll')){
					$qCount  = $qCache->deleteAll();
					$msg[]   = $qCount . ' database cache entries deleted';
				}				
				break;
			}
			case 'tmx':{
				$filename1 = Rhema_Util_TmxGenerator::getTmxFilename(false, true); 
				$filename2 = Rhema_Util_TmxGenerator::getTmxFilename(false, true, 'route');
				$filePath  = $filter->filter(APPLICATION_PATH . '/../sites/' . SITE_DIR . '/cache/translations');
				$count     = $this->_utility->cleanupDirectory($filePath);
				
				$msg[]     = $filename1 . ' refreshed';
				$msg[]     = $filename2 . ' refreshed';
				$msg[]     = $count . ' cache files deleted';
				break;
			}
			case 'logs':{
				$options = Rhema_SiteConfig::getConfig('settings');
				$logDir  = $options['log_dir'];
				$count     = $this->_utility->cleanupDirectory($logDir);
				foreach($options['log_files'] as $key => $desc){
					file_put_contents($logDir . $key . '.log', '');
				}	
				$msg[]     = 'Log directory : ' . $filter->filter($logDir);	
				$msg[]     = $count . ' log files refreshed';	
				$msg[]     = implode(', ', array_keys($options['log_files']));	
				break;
			}
			case 'config':
			default:{
				$filename  = APPLICATION_ENV . '-' . Rhema_SiteConfig::CACHED_CONFIG ;
				$filePath  = $filter->filter(APPLICATION_PATH . '/../sites/' . SITE_DIR . '/cache/' . $filename);
				$count     = @unlink($filePath);
				$msg[]     = $filePath;				
				break;
			}	
			
		}
		if($cachePath and !file_exists($cachePath)){
			mkdir($cachePath, 0755, true);
		}
		$this->_helper->sendAjaxMessage($msg, 'Clear Cache', Rhema_Dto_UserMessageDto::TYPE_SUCCESS);

	}

	public function imageSourceAction(){
		$src    = $this->_request->getParam('src');
		$util   = Rhema_Util_String::getInstance();
		$file   = $util->getImageSource($src);
		
		$this->_utility->setAjaxData($file); 
	}
	
	public function organiseAction(){
		$table      = $this->_request->getParam('table');
		$minDepth   = $this->_request->getParam('minDepth', 0);
		$menuObject = Rhema_Model_Service::factory($table);
		$table = $menuObject->getModelName($table);
		$menuObject->setModel($table);

		$this->view->rogueMenus = $menuObject->getRogueMenus($table);
		$this->view->menuObj 	= $menuObject;
		$this->view->roots 		= $menuObject->getRoots($table);
		$this->view->table 		= $table;
		$this->view->minimumDepth 	= $minDepth;
		
		$viewFilename = $minDepth ? 'organise-category' : 'organise';
		$output = $this->view->render("ajax/{$viewFilename}.phtml");
		$this->_utility->setAjaxData($output);
	}

	public function updateAclAction(){
		$table = $this->_request->getParam('table');
		if('updateAcl' == $this->_getParam('task')){
			$this->_helper->accessControl->updateAcl($table);
		}

		//$this->accessAction();
	//$this->_helper->accessControl->showTabs($table);
	}

	public function accessAction(){
		$scope = $this->_getParam(Admin_Model_AdminMenu::SCOPE_KEY, '');
		$table = $this->_request->getParam('table', 'menu');
		$rootId = $this->_getParam('root_id', '');

		$roleObject = Rhema_Model_Service::factory('role');
		$roles = $roleObject->getRoles();
		$option = $this->_getAccessGridOptions($table);
    	$option['preGenerateHooks'] = array(
    		'addRoleColumns' => array($roles),
    		'addHelpColumn'  => array()
    	);		
		$daoFilter = new Rhema_Dao_Filter();
		$daoFilter->addOrderBy('lft');
		$option['daoFilter']  = $daoFilter ;
		
		$gridService = new Rhema_Grid_Service($option);
		$grid = $gridService->generateGrid();

		$this->view->rootId = $rootId;
		$this->view->tableId = implode('-', array_filter(array(
				$scope,
				$rootId)));

		$html = $gridService->ajaxDespatch($grid, 'ajax/access');

		$this->_utility->setAjaxData($html);
	}

	public function getPageLayoutAction(){
		$params 		= $this->_request->getParams();
		$items 			= $this->_request->getParam('items');
		$templateId 	= $this->_request->getParam('template_id');
		$pageId 		= $this->_request->getParam('page_id');
		$table 			= $this->_request->getParam('table');
		$task 			= $this->_request->getParam('task');

		$layoutModel    = new Admin_Model_PageLayout() ; //Rhema_Model_Service::factory('page_layout');
		switch($task){
			case 'update' :
				{
					//$this->saveLayout($items);
					$itemAdd = array();
					foreach($items as $sxnId => $data){
						$itemAdd[$sxnId] = $this->processField($items[$sxnId]);
					}

					$sectionAdd = array_keys($items);

					//Admin_Model_TemplateSection::saveSectionOrder($templateId, $sectionAdd);
					$layoutModel->updateLayout($pageId, $templateId, $table, $itemAdd, $sectionAdd);

					break;
				}

			case 'resetlayout' :
				{
					$done = $this->resetLayout();
					break;
				}
		}

		$temp         = $this->_helper->layoutEditor()->getTemplateDetails($templateId);
		$res          = $layoutModel->getPageLayout($pageId, $templateId, $table);
		$res['menus'] = Rhema_Model_Service::factory('menu')->getMenuReferencingPage($pageId);
		/*		$this->view->items  = array_merge($res, $temp);
		$return['html'] 	= $this->_helper->layout()->render('partials/page-layout');*/
		$param['page_id']     = $pageId;
		$param['template_id'] = $templateId;
		$param['table']       = $table;

		$return = array('templateData' => $temp,
					    'pageData'	   => $res,
						'params'       => $param,
						'sectionOrd'   => $temp['sectionOrd'] //isset($sectionAdd) ? $sectionAdd :
		);
		$this->_helper->json->sendJson($return);
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
		$seq = 0;
		for($k = 0; $max = count($arr), $k <$max; $k += 1){
			if('blank' == substr($arr[$k], 0, 5)){
					continue;
			}

			list(, $list) = explode('_', $arr[$k]);
			//$list = str_replace('item_', '',$parts[$k]);
			list($type, $fieldId) = explode('-', $list);
			$return[$type][$fieldId] = $seq;

			$seq ++;
		}
		return $return;
	}



	public function getEventAction(){
		try{

			$defaultDate 	= date('d/m/Y', time());
			$viewType   	= $this->_request->getParam('viewtype', 'month');
			$timezone 		= $this->_request->getParam('timezone', 1);
			$showdate 		= $this->_request->getParam('showdate', $defaultDate);
			$method			= $this->_request->getParam('method');

			$event = Rhema_Model_Service::factory('event');

  			switch($method){
  				case 'update':{

  					break;
  				}
  				case 'remove':{
  					break;
  				}
  				case 'list';
  				default:  	{
					$ret = $event->getEventsByRange($showdate, $viewType);
  				}
  			}

		}catch(Exception $e){
			$ret['error'] = $e->getMessage();
		}

		$this->_helper->json->sendJson($ret);
	}

	public function resetLayout(){
		$request    = $this->getRequest();
		$templateId = $request->getParam('template_id', null);
		$pageId     = $request->getParam('id', null);
		if($pageId and !is_numeric($templateId)){
			$page = new Admin_Model_Page();
			$templateId = $page->getPageTemplate($pageId);
		}
		if($page and $templateId){
			return Admin_Model_PageLayout::resetLayout($pageId, $templateId);
		}else{
			return false ;
		}
	}


	protected function _getAccessGridOptions($model){
		$filter = new Rhema_Dao_Filter();

		$filter->setModel($model);
		$filter->setAttributes(array(
				'id',
				'title',
				'image_file',
				'rgt',
				'lft',
				'page_id',
				'description',
				'label',
				'level',
				'is_visible',
				'm_module',
				'm_controller',
				'm_action'));

		$removeList = array(
				'm_module',
				'm_controller',
				'm_action',
				'sequence',
				'image_file',
				'is_label',
				'label');
		$gridParam = array(
				'title' => 'Access Control Rules',
				//  'rownumbers'	=> false,
				'ExpandColumn' => 'description',  // FIX for grid not able to determine the correct column because of the rownumber set to true
				'tree_root_level' => 1,
				'toolbar' => array(
						true,
						'bottom'));

		$editParam = array(
				'add' => 'false',
				'edit' => 'false',
				'delete' => 'false',
				'search' => 'false');

		$option = array(
				'gridParam' => $gridParam,
				'editParam' => $editParam,
				'model' => $model,
				//'daoFilter' => $filter,
				'removeList' => $removeList);

		return $option;

	}
 
	public function widgetAction(){
		$options = $this->_utility->buildWidgetList(new DirectoryIterator(WIDGET_PATH . '/Controller'));
		$return  =  $this->view->formSelect('widget', '', null, $options);
		$this->_utility->setAjaxData($return);
	}

	public function schemaFromDbAction(){
		$doctrineConfig = $this->_config['doctrine']; //doctrine.db_schema_path.db
		//pd($doctrineConfig['db_schema_path']);
		unlink($doctrineConfig['db_schema_path']);
		$result = Doctrine_Core::generateYamlFromDb($doctrineConfig['db_schema_path'], array('admin'));
		if($result){
			$type = Rhema_Dto_UserMessageDto::TYPE_SUCCESS ;
			$msg  = 'DB Schema created successfully- ' . realpath($doctrineConfig['db_schema_path']) ;
		}else{
			$msg  = 'An error occurred';
			$type = Rhema_Dto_UserMessageDto::TYPE_SUCCESS ;
		}
		$this->_helper->sendAjaxMessage($msg, __FUNCTION__, $type);
		//$this->_helper->json->sendJson($result);
	}

	public function modelDbDifferenceAction(){
		$doctrineConfig = $this->_config['doctrine'];
		$from = $doctrineConfig['yaml_schema_path']['db'];
		$to = $doctrineConfig['yaml_schema_path']['merged'];

		if(file_exists($from)){
			$migrationsPath = $doctrineConfig['migrations_path'];
			$result = Doctrine_Core::generateMigrationsFromDiff($migrationsPath, $from, $to);
		}else{
			$result = $to . ' not found';
		}

		$this->_helper->json->sendJson($result);
	}

	public function generateModelFromSchemaAction(){
		clearstatcache();
		
		$rFilter 		= new Zend_Filter_RealPath();
		$doctrineConfig = $this->_config['doctrine'];
		$yamlParam      = $doctrineConfig['params'];
		$mergedSchema   = $rFilter->filter($doctrineConfig['merged_schema_path']);
		$title          = 'Model Generation From Schema';

	/*	$manager = Doctrine_Manager::getInstance();
		$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_AGGRESSIVE);

		$entry = array();
		foreach($this->_schemaBlocks as $c){
			$entry[] = file_get_contents($doctrineConfig['yaml_schema_path'][$c]);
		}

		file_put_contents($mergedSchema, implode(PHP_EOL, $entry));
*/
		if(!($mergedSchema and file_exists($mergedSchema))){
			$message = $mergedSchema . ' not found';
			$type    = Rhema_Dto_UserMessageDto::TYPE_WARNING ;
		}else{
			try{
				Doctrine_Core::generateModelsFromYaml($mergedSchema, $doctrineConfig['models_path']['admin'], $yamlParam);
				$message = 'Models created from schema successfully';
				$type = Rhema_Dto_UserMessageDto::TYPE_SUCCESS;
			}catch(Exception $e){
				$message = $e->getMessage();
				$type = Rhema_Dto_UserMessageDto::TYPE_ERROR;
			}
		}

		$this->_helper->sendAjaxMessage($message, $title, $type);
	}

	public function createTableFromModelAction(){
		$title     = 'Creating tables from models';
		
		try{
			$array = Rhema_Util_Db::listMissingTables();
			$filter = new Rhema_Filter_FormatModelName();
			$err = array();
			$count = 0;
			foreach((array) Rhema_Util_Db::listMissingTables() as $arr => $table){
				try{
					$m = $filter->filter($arr);
					Doctrine_Core::createTablesFromArray(array($m));
					$count ++;
				}catch(Exception $e){
					$err[] = $e->getMessage();
				}
			}
			$message = $count . ' database tables created successfully.' . PHP_EOL;
			$type = Rhema_Dto_UserMessageDto::TYPE_SUCCESS;

			if(count($err)){
				$message .= 'Notices shown include:' . PHP_EOL . implode(PHP_EOL, $err);
				$type = Rhema_Dto_UserMessageDto::TYPE_WARNING;
			}
		}catch(Exception $e){
			$message = $e->getMessage();
			$type = Rhema_Dto_UserMessageDto::TYPE_ERROR;
		}

		$this->_helper->sendAjaxMessage($message, $title, $type);
	}

	public function mergeSchemaAction(){
		$realFilter     = new Zend_Filter_RealPath();
		$doctrineConfig = $this->_config['doctrine'];
		$yamlParam      = $doctrineConfig['params'];
		$mergedSchema   = $doctrineConfig['merged_schema_path'];
		$title          = 'Merge Schema Files';
		$proceed        = $copied =  false;
				
		$backupFile = $doctrineConfig['merged_schema_path_old'];
		$backupDir  = dirname($backupFile);
		if(!file_exists($backupDir)){
			@mkdir($backupDir, 0777, true);
			$message[] = 'Backup directory created'; 
		}
		//$backupFile = $backupDir . DIRECTORY_SEPARATOR . basename(substr($mergedSchema,0,-4)) . '-' . date('Y-m-d-H-i') . '.yml';
		
		
		if(file_exists($backupFile)){
			$message[]  = 'Previous backup schema found - ' . $realFilter->filter($backupFile);
			$proceed = true;
		}elseif(file_exists($mergedSchema)){
			$copied     = copy("$mergedSchema", "$backupFile");
			$message[]  = 'Schema backup created ' . $realFilter->filter($backupFile) ; 
		}else{
			$copied = true ;
			$message[]  = 'No previous merged schema was found ' ;
		}
		 
		if($copied or $proceed){
			
			$manager = Doctrine_Manager::getInstance();
			$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_AGGRESSIVE);
	
			$entry = array();
			foreach($this->_schemaBlocks as $c){
				$entry[] = file_get_contents($doctrineConfig['yaml_schema_path'][$c]);
			}
	
			$done = file_put_contents($mergedSchema, implode(PHP_EOL, $entry));	
			if($done){
				$message[] = 'New merge schema created successfully - ' . $realFilter->filter($mergedSchema) ;
				$type      = Rhema_Dto_UserMessageDto::TYPE_SUCCESS ;
			}else{
				$message[] = 'Unable to create schema file - ' . $mergedSchema;
				$type      = Rhema_Dto_UserMessageDto::TYPE_WARNING;
			}
			
		}else{
			$type = Rhema_Dto_UserMessageDto::TYPE_WARNING;
			$message[] = "Unable to backup $mergedSchema to $backupFile";
		}
		$this->_helper->sendAjaxMessage($message, $title, $type);
	}
/*
 * 
 * 	public function createMigrationClassesAction(){
		$doctrineConfig = $this->_config['doctrine'];
		$dbSchema = $doctrineConfig['db_schema_path'] ;
		$backup   = $doctrineConfig['merged_schema_path_old'] ;
		
		$result = Doctrine_Core::generateMigrationsFromDiff($doctrineConfig['migrations_path'], 
					$dbSchema, $doctrineConfig['merged_schema_path']);
		
		$type   = isset($result['error']) ? Rhema_Dto_UserMessageDto::TYPE_WARNING : Rhema_Dto_UserMessageDto::TYPE_SUCCESS ;		
		$this->_helper->sendAjaxMessage($result, __FUNCTION__, $type);		
	}
	
 */	
	public function createMigrationClassesAction(){
		$doctrineConfig = $this->_config['doctrine'];
		$dbSchema  = $doctrineConfig['db_schema_path'] ;
		$backup    = $doctrineConfig['merged_schema_path_old'] ;
		$msg       = '';
		$type      = Rhema_Dto_UserMessageDto::TYPE_ERROR ;
		$autoclose = false;
		$rFilter   = new Zend_Filter_RealPath();
		
		if(!file_exists($doctrineConfig['merged_schema_path_old']) ){
			$msg[] = "Backup schema not found ({$doctrineConfig['merged_schema_path_old']}). Run Merge Schema " 	;
		}elseif(!file_exists($doctrineConfig['merged_schema_path'])){
			$msg[] = "Schema not found ({$doctrineConfig['merged_schema_path']}). Run Merge Schema " 	;
		}else{
		//pd($doctrineConfig);
			try{
				$temPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . getmypid() . DIRECTORY_SEPARATOR . 'fromprfx_doctrine_tmp_dirs';
				set_include_path(implode(PATH_SEPARATOR, array($temPath, get_include_path())));
				
				$result = Doctrine_Core::generateMigrationsFromDiff($doctrineConfig['migrations_path'], 
						$doctrineConfig['merged_schema_path_old'], $doctrineConfig['merged_schema_path']); 
						 
			    $msg[]  = 'Migraton classes created successfully @ ' . $rFilter->filter($doctrineConfig['migrations_path']) ;
				$msg[]  = $rFilter->filter($backup) . ' deleted';
				//$msg[]  = print_r($result, true) ;
				$type   = isset($result['error']) ? Rhema_Dto_UserMessageDto::TYPE_WARNING : Rhema_Dto_UserMessageDto::TYPE_SUCCESS ;
				foreach((array)$result as $key => $data){
					$msg[] = $key .': <pre>' .  print_r($data, true) . '</pre>';
				}
				//$autoclose = false ;
			}catch(Exception $e){
				$msg = $e->getMessage();
			}
		} 
 		
		$this->_helper->sendAjaxMessage($msg, __FUNCTION__, $type, $autoclose);		
	}
	public function migrateDbAction(){
		set_time_limit(0);
		$type           = Rhema_Dto_UserMessageDto::TYPE_WARNING ;
		$doctrineConfig = $this->_config['doctrine'];
		$oldName        = $doctrineConfig['merged_schema_path_old'];
		/*$version = $this->_request->getParam('version');
		$migration = new Doctrine_Migration($doctrineConfig['migrations_path'], 'admin');
		$migration->migrate();
		$result['version'] = $migration->getCurrentVersion();*/
		if(file_exists($oldName)){
			$result         = $this->_helper->migrateDb();	
			if(!isset($result['error'])){
				$result		    = array("Database successfully migrated from version {$result['oldVersion']} to {$result['newVersion']}");
				$type           = Rhema_Dto_UserMessageDto::TYPE_SUCCESS ;
				$time			= date('YmdHis');
				$oldName        = $doctrineConfig['merged_schema_path_old'];
				$newName	    = str_replace('_old.yml', '-' . $time . '.yml', $oldName);
				if(rename($oldName, $newName)){
					$result[] = 'Schema History backedup @ ' . $newName ;
					unset($doctrineConfig['merged_schema_path_old']);
				}	
					
			} 
		}else{
			$result[] = $oldName . ' not found. Run Merge Schema';
			$type     = Rhema_Dto_UserMessageDto::TYPE_ERROR ;
		}
				
		$this->_helper->sendAjaxMessage($result, __FUNCTION__, $type);
	}

	/**
	 * This method generates tool tip information on the page design screen
	 * Clicking the icon next to a page, template, element or component calls this action
	 * The information about the clicked item is displayed in a tool tip
	 *
	 */
	public function tooltipAction(){
		$id = $this->_request->getParam('id', null);
		$table = $this->_request->getParam('table', null);

		if($table and $id){
			$mFilter = new Rhema_Filter_FormatModelName();
			$model = $mFilter->filter($table);
			$this->view->labels = Rhema_Model_Service::factory('admin_dictionary')->getFieldLabels();
			$this->view->toolTip = Rhema_Model_Abstract::getToolTip($model, $id);
			$output = $this->_helper->layout()->render('partials/tooltip');
		}else{
			$output = 'nothing found';
		}
		$this->_response->setBody($output)->sendResponse();
		exit();
	}

	/**
	 * Save data submitted ajax from grid to the database
	 * @return unknown_type
	 */
	public function saveAction(){
		try{
			$output = Rhema_Model_Abstract::saveData($this->_request);
		}catch(Exception $e){
			$output['pass'] = 0;
			$output['message'] = $e->getMessage();
			$output['code'] = $e->getCode();
			$this->_response->clearBody();
		}
		$this->_helper->json->sendJson($output);
	}

	public function pageMetaAction(){
	    $pageId   = $this->_request->getParam('pageId', null);
	    $task     =  $this->_request->getParam('task');
	    $data     = '';
	    $message  = '';
	    $pageData = '';
	    $error    = 0;

	    $form     = new Rhema_Form_EditMeta();
	    $obj      = Rhema_Model_Service::factory('page');

	    if($pageId){
	        switch($task){
	            case 'update':{
	                    $param = $this->_request->getQuery();
	                    if($form->isValid($param)){
	                        $values = $form->getValues();
	                        $done   = $obj->updatePageMetaData($pageId, $values);

	                        $userMessage    = new Rhema_Dto_UserMessageDto('Page meta data updated successfully', 'Page Update',
	                                                Rhema_Dto_UserMessageDto::TYPE_SUCCESS );
	                        $message = $this->view->printUserMessage($userMessage);

	                    }else{
	                        $error  = 1;
	                        $userMessage    = new Rhema_Dto_UserMessageDto($form->getMessages(), 'Page Update',
	                                                Rhema_Dto_UserMessageDto::TYPE_ERROR );
	                        $message = $this->view->printUserMessage($userMessage);
	                    }
	                break;
	            }

	            case 'getmeta':
	            default: {

	                $pageData = $obj->getPageById($pageId);


	                $url      = $this->view->url(array('pageId' => $pageId, 'task' => 'update'), 'manage-page-meta' );
	                $form->setAction($url);
	                $form->populate($pageData);
	                $data     = $form->render();
	            }
	        }
	    }
	    $return['data']  = $data;
	    $return['msg']   = $message ;
	    $return['error'] = $error;
	    $return['page']  = $pageData;

	    $this->_helper->json->sendJson($return);
	}
	
	public function updateAdminTableAction(){
		$task        = $this->_request->getParam('task', null);
		 
		if($this->_request->isPost() and $task){
			$import   = array_keys($this->_request->getPost('import', array()));
			$truncate = array_keys($this->_request->getPost('truncate', array()));
			$source   = $this->_request->getPost('source', null);
			
			if(count($truncate)){ 
				foreach($import as $table){
					if(in_array($table, $truncate)){
						$tableName = Doctrine_Core::getTable($table)->getTableName();
						$done      = Rhema_Model_Abstract::rmsTruncateTable($tableName);
					}
				}
			}
			
			$message   = Rhema_Model_Abstract::getDefaultTableDetails($import, $source, true);			

			if(is_array($message)){ 
				$type    = 	Rhema_Dto_UserMessageDto::TYPE_SUCCESS;
			}else{ 
				$type            = 	Rhema_Dto_UserMessageDto::TYPE_ERROR;
				$return['error'] = 1;
			}
			
			$userMessage   = new Rhema_Dto_UserMessageDto($message, 'Update Admin Table', $type); 
			$data        = $this->view->printUserMessage($userMessage); 
			
		}else{			 
		
			$this->view->tableList      = Rhema_Model_Abstract::getTablesByRegex(REGEX_TABLE_ALL); 
			$this->view->sourceDomains  = (array) Rhema_SiteConfig::getConfig('settings.source_domains'); 
			$this->view->selectedSource = current($this->view->sourceDomains);
			 
			$data          				= $this->view->render('ajax/update-admin-table.phtml'); 
		}
			
		$this->_response->clearBody();
		$this->_response->setBody($data)
				 		->sendResponse();
		exit();		 
	}
}