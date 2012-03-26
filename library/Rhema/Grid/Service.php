<?php
/**
 * Services to generate grid object based on the database model
 * It requires
 * 1. The Bvb_Grid library
 * 2. The ZFDoctrine library
 * @author Pele
 *
 */
class Rhema_Grid_Service{

	private $_adapter = null;
	private $_type = self::TYPE_JQGRID;
	private $_source = null;
	private $_grid = null;
	private $_daoFilter = null;
	private $_gridId = null;
	private $_model = null;
	private $_proxy = null;
	private $_columnOptions = array();
	private $_additionalScripts = array();
	private $_gridParam = array();
	private $_preGenerateHooks = array();
	private $_editParam = array();
	private $_gridMargin = 80 ;
	private $_isSearch   = false;

	private $_removeFromGrid = array(
	);

	protected $_jqg = null;
	protected static $_acl;
	protected $_isTreeGrid = false;

	const TYPE_JQGRID = 'jqGrid';
	const POS_LEFT = 'left';
	const POS_RIGHT = 'right';
	const DEFAULT_GRID_LAYOUT = 'partials/gridview';

	public function __construct($options = array()){
		if(is_string($options) and $options){
			$this->setModel($options);
		}elseif(is_array($options)){
			if(isset($options['model'])){
				$this->setModel($options['model']);
			}else{
				throw new Rhema_Grid_Exception('A database model is required to instantiate a grid');
			}

			if(isset($options['adapter'])){
				$this->adapter = $options['adapter'];
			}

			if(isset($options['source'])){
				$this->_source = $options['source'];
			}

			if(isset($options['daoFilter'])){
				$this->_daoFilter = $options['daoFilter'];
			}
					
			if(isset($options['gridMargin'])){
				$this->_gridMargin = $options['gridMargin'];
			}
			
			if(isset($options['type']) and $options['type']){
				$this->_type = $options['type'];
			}else{
				$this->_type = self::TYPE_JQGRID;
			}

			if(array_key_exists('gridParam', $options)){
				$this->_gridParam = $options['gridParam'];
			}

			if(array_key_exists('editParam', $options)){
				$this->_editParam = array_merge($this->_editParam, $options['editParam']);
			}

			if(isset($options['removeList'])){
				$this->_removeFromGrid = array_merge($this->_removeFromGrid, $options['removeList']);
			}
			
			if(isset($options['preGenerateHooks'])){
				$this->_preGenerateHooks = $options['preGenerateHooks'];
			}
			
			$this->_gridParam['i18n'] = Zend_Registry::get('Zend_Locale')->getLanguage();
		}
 

		try{
			$options = array_merge(Rhema_SiteConfig::getConfig('grid.options'), $this->_columnOptions);
			$callback['Bvb_Grid_Deploy_JqGrid'] = array(
					$this,
					'preDeploy');
			$ctlParams = Zend_Controller_Front::getInstance()->getRequest()->getPost();

			//$grid = Bvb_Grid::factory($this->_type, $this->_columnOptions, $this->getGridId(), $callback, $ctlParams);
			$tempId = $this->makeGridId() ;
			$grid = new Rhema_Grid($this->_type, $this->_columnOptions, $tempId, $callback, $ctlParams);
			$grid->setNavBarOptions($this->_editParam);
			$grid->setView(Zend_Layout::getMvcInstance()->getView());
			$grid->setId($tempId);

		}catch(Exception $e){
			throw new Rhema_Grid_Exception('Unable to create grid instance: ' . $e->getMessage());
		}

		$cacheArray = array(
				'use' => array(
						'form' => 'true',
						'db' => 'true'),
				'instance' => Zend_Registry::get('cache-manager')->getCache('default'),
				'tag' => get_class($grid));

		$this->setGrid($grid);
		$this->_jqg = new Rhema_Grid_JqgParam();
		$this->_isTreeGrid = Rhema_Model_Service::isMenuTable($this->_model);
		$grid->setCache($cacheArray);
	}

	public function getDefaultTreegridParams(){
		$param['treeGrid'] = true;
		$param['gridview'] = false;
		$param['ExpandColumn'] = 'title';
		$param['treeReader']['left_field'] = 'lft';
		$param['treeReader']['right_field'] = 'rgt';
		$param['treeReader']['leaf_field'] = 'isLeaf';
		$param['treeReader']['level_field'] = 'level';
		$param['treeReader']['expanded_field'] = 'expand';
		$param['treedatatype'] = 'json';

		return $param;
	}

	/**
	 * Process pregenetate tasks e.g. adding columns
	 */
	protected function _preGenerate(){
		foreach((array) $this->_preGenerateHooks as $callback => $args){
			if(method_exists($this, $callback)){
				call_user_func_array(array($this, $callback), (array) $args);
			}
			unset($this->_preGenerateHooks[$callback]);
		}
	}
	
	public function generateGrid(){		
		$this->_preGenerate();
		
		$grid = $this->getGrid();
		$grid->setSource($this->getSource());
		$grid->setExport(array('print'));
		$util = Rhema_Util_String::getInstance();
		//	$grid->addExtraColumns($this->getActionBarColumn());
		$grid->addFormatterDir('Rhema/Grid/Formatter','Rhema_Grid_Formatter');

		if($this->getType() == self::TYPE_JQGRID){
			if($this->_isTreeGrid){
				$this->_gridParam = array_merge($this->getDefaultTreegridParams(), $this->_gridParam);
				$treeGridConfig   = (array) Rhema_SiteConfig::getConfig('grid.nav_options.tree-grid');
				$grid->addNavBarOptions($treeGridConfig);
			}elseif($grid->getField('content') or $grid->getField('comment')){
				$config = Rhema_SiteConfig::getConfig('grid.nav_options.wysiwyg-tables'); // tables requring WYSIWYG editor
				$grid->addNavBarOptions($config);
			}

			$modelparams = $this->_jqg->getCached()->getColModel($this->_model);
			$gridParams = $this->_jqg->getCached()->getParams($this->_gridParam);

			foreach($modelparams as $item){
				$updateValue = array('jqg' => (array) $item);

				if(substr($item->name, - 3) == '_at'){										
					$updateValue['class']  = 'datepicker';

					if($item->name == 'dob_at'){
						$picker = new Zend_Json_Expr(" function(el){ gbl.doDate(el); } ");
						$updateValue['format'] = array('date', array( 'date_format' => 'YYYY-MM-dd'));
					}else{
						$picker = new Zend_Json_Expr(" function(el){ gbl.doDateTime(el); } ");
						$updateValue['format'] = array('date', array( 'locale' => Zend_Registry::get('Zend_Locale')));
					}
					
					if(isset($updateValue['jqg']['editoptions'])){
						$updateValue['jqg']['editoptions'] = (array) $updateValue['jqg']['editoptions'];
					}
					if(isset($updateValue['jqg']['searchoptions'])){
						$updateValue['jqg']['searchoptions'] = (array) $updateValue['jqg']['searchoptions'];
					}
					$updateValue['jqg']['editoptions']['dataInit']   = $picker;
					$updateValue['jqg']['searchoptions']['dataInit'] = $picker;

				}elseif(substr($item->name, - 3) == '_id' and ($item->name != 'root_id')  or 'author' == $item->name){
					$updateValue['searchType'] = Rhema_Dao_Filter::OP_EQ;
					$param = ('author' == $item->name) ? 'user_id' : $item->name;
					$updateValue['callback'] = array(
							'function' => array( 'Rhema_Model_Service',  'getStringRepresentation'),
							'params' => array( $param,  '{{' . $item->name . '}}'));
				}elseif(substr($item->name, - 3) == '_by'){
					$updateValue['searchType'] = Rhema_Dao_Filter::OP_EQ;
					$updateValue['callback'] = array(
							'function' => array( 'Rhema_Model_Service',  'getStringRepresentation'),
							'params' => array( 'user_id',  '{{id}}'));
				}elseif(substr($item->name, 0, 3) == 'is_'){
					$updateValue['searchType'] = Rhema_Dao_Filter::OP_EQ;
					$updateValue['callback'] = array( 'function' => array(
									$this,
									'formatBoolean'),
									'params' => array( '{{' . $item->name . '}}'));
				}elseif(preg_match('/^(?:image_)|(?:thumb)$|^(?:logo)$/i', $item->name)){ 
					$updateValue['format'] = array( 'imagePreview',  array('src' => '{{' . $item->name. '}}'));
					$updateValue['search'] = false;
					$updateValue['class'] = 'grid-thumb';
					$updateValue['align'] = 'center';
				}elseif(preg_match('/(price|cost|rrp)/i', $item->name)){
					$updateValue['format'] = array( 'currency', array( 'locale' => Zend_Registry::get('Zend_Locale')));
					$updateValue['align'] = 'right';
				}elseif(preg_match(Rhema_Grid_Adapter_DoctrineModel::getSpinElementRegex(), $item->name)){
					$updateValue['class'] = 'spinner';
				}

				if($this->_isTreeGrid){
					if(array_search($item->name, Rhema_Grid_Adapter_DoctrineModel::$treeReaderList) !== false){
						$updateValue['remove'] = true;
					}
					if($item->name == 'table'){
						$updateValue['width'] = '180px';
					}elseif($item->name == 'title'){
						$updateValue['width'] = '450px';
					}elseif($item->name == 'page_id'){
						$updateValue['width'] = '200px';
					}elseif($item->name == 'id'){
						$updateValue['width'] = '180px';
					}

					if(array_search($item->name,$this->_removeFromGrid) !== false){
						$updateValue['remove'] = true;
					}
				}else{
					if($item->name == 'title'){
						$updateValue['width'] = '350px';
					}
				}

				$grid->updateColumn($item->name, $updateValue);
			}

			if(!$gridParams['editurl']){
				$gridParams['editurl'] = $grid->getView()
											->url(array('table' => $this->_model), 'grid-model-save');
			}

			if(!$gridParams['url']){
				//$gridParams['url'] = $grid->getView()->url(array('table' => $this->_model));
			}
			$pagerId = $grid->jqgGetIdPager();
			$grid->setJqgParams($gridParams);

			$grid->setJqgParam('pager', new Zend_Json_Expr(sprintf("'#%s'", $pagerId)));
			$grid->setJqgParam('caption', $this->_jqg->getCaption());
			$this->_additionalScripts[] = $this->_jqg->getCached()
										->getToolbarButtons($this->_model,  $grid->jqgGetIdTable(),
														    $grid->getId(), $this->_isTreeGrid);
			$gId    = $grid->jqgGetIdTable();
			//set the grid width based on screen size
			$js     = sprintf("var gw = jQuery('#admin-content').parent('div:first').width() - %d ;  jQuery('#%s').jqGrid('setGridWidth',gw)",  $this->_gridMargin, $gId);
			//$this->_additionalScripts[] = $js;
			$grid->bvbSetOnInit($js);
			//Zend_Layout::getMvcInstance()->getView()->collateScripts($js);			
			$grid->debug = ('development' == APPLICATION_ENV);
			
			//$doSearch 		= $request->getParam($prmNames[Rhema_Grid_JqgParam::SEARCH_ID], 'false');
 
			$request = Zend_Controller_Front::getInstance()->getRequest();
			$gridId  = $this->makeGridId();
			if($request->getPost('_search') and $request->isXmlHttpRequest() and !isset($_GET['q'])){
				$_GET['q'] = $gridId;
			}
			$grid->setAjax($gridId);
		}

		return $this; //$grid ;
	}

	public function addAccessControlCheckBoxRow(){
		$rows = new Bvb_Grid_Extra_Rows();
		$rows->addRow('afterTitles', array(
				'',
				array(
						'colspan' => 4,
						'class' => 'top-row',
						'content' => 'here you do')));

		$this->_grid->addExtraRows($rows);
		return $this;
	}

	public function displayFeedExist($feedUrl){
		$filename = Rhema_Util::getFeedCacheFilename($feedUrl);
		$var      = file_exists($filename);
		return self::formatBoolean($var);
	}
	public function formatBoolean($val){
		$file = $val ? 'ok.png' : 'no.png';
		$src  = Zend_Layout::getMvcInstance()->getView()->getImagePath()->backendIcon($file);
		$html = "<img src='{$src}' width='10' height='10' class='atip'/>";
		return $html ;
	}

	public function preDeploy($grid){
		$param = Zend_Controller_Front::getInstance()->getRequest()->isXmlHttpRequest();
		$this->setAdditionalScripts($grid, $param);
	}

	public function postDeploy(){

	}

	public function aJaxDespatch($grid, $layoutScript = self::DEFAULT_GRID_LAYOUT){
		try{
			$layout = Zend_Layout::getMvcInstance();
			$view = $layout->getView();
			$html = $grid->deploy();
			//$script = $view()->jQuery()->getOnLoadActions();// 'jQuery(function () { ' . $grid->renderPartJavascript() . ' })';
			//$view->inlineScript()->setScript($script);

			if($layoutScript){
				$view->gridData = $html;
				$return = $layout->render($layoutScript);
			}else{
				$scrip  = $view->jQuery()->setRenderMode(ZendX_jQuery::RENDER_JQUERY_ON_LOAD);
				$return = '<datagrid class="data-grid">' . $html . '</datagrid>' . $scrip;
			}

			return $return;
		}catch(Zend_Controller_Action_Exception $e){
			//echo $e->getMessage();
		}

	}
	public function setAdditionalScripts($grid, $ajax = false){
		$scripts = array_filter($this->_additionalScripts);
		$view = $grid->getView();
		if(count($scripts) > 0){
			$gridId = $grid->getId();
			$js = implode(PHP_EOL, $scripts); /*
			$js = " var grid = jQuery(\"#$gridId\").jqGrid();
		    		$cmds
				   ";*/
			$grid->bvbSetOnInit($js);
		}

		$grid->jqgAddNavButton(array(
				'caption' => '',
				'title' => "Reorder Columns",
				'buttonicon' => 'ui-icon-folder-open',
				'onClickButton' => new Zend_Json_Expr("function(){ jQuery(this).jqGrid('columnChooser'); }")));
		return $this;
	}

	/**
	 * This proxy is a class that extends the ZFdoctrine Model adapter
	 * Required to provide column information, model relationships and
	 * to build the Doctrine Query Object required to instantiate the
	 * grid source attribute
	 *
	 * @return Rhema_Grid_Adapter_Doctrine
	 */
	public function getAdapter(){
		if(! $this->_adapter){
			$option['model'] = $this->_model;
			$this->_adapter = new Rhema_Grid_Adapter_DoctrineModel($option);
		}
		return $this->_adapter;
	}

	/**
	 * @param field_type $adapter
	 */
	public function setAdapter($adapter){
		$this->_adapter = $adapter;
		return $this;
	}

	/**
	 * @return the $_type
	 */
	public function getType(){
		return $this->_type;
	}

	/**
	 * @return the $_source
	 */
	public function getSource(){
		if(! $this->_source){
			$filters = $this->getDaoFilter();
			$query = $this->getAdapter()->createQuery($filters);
			$this->_source = new Bvb_Grid_Source_Doctrine($query);
		}
		return $this->_source;
	}

	/**
	 * @return the $_grid
	 */
	public function getGrid(){
		return $this->_grid;
	}

	/**
	 * @return the $_daoFilter
	 */
	public function getDaoFilter(){
		if(! $this->_daoFilter){
			$this->_daoFilter = new Rhema_Dao_Filter();
		}
		$this->_daoFilter->setModel($this->_model);


		$prmNames 		= $this->_jqg->getPrmNames();
		$request 		= Zend_Controller_Front::getInstance()->getRequest();
		$doSearch 		= $request->getParam($prmNames[Rhema_Grid_JqgParam::SEARCH_ID], 'false');
		$postVars 		= $request->getParams(); //$request->getPost() + $request->getQuery();
		$sortColumnKey  = $prmNames[Rhema_Grid_JqgParam::SORT_ID];
		$sortOrderKey 	= $prmNames[Rhema_Grid_JqgParam::SORT_ORDER_ID];
		$limitIndex 	= $prmNames[Rhema_Grid_JqgParam::RECORD_LIMIT];
		$gridId 		= $this->_grid->getGridId();

		$filterChain = new Zend_Filter();
		$filterChain->addFilter(new Zend_Filter_StringTrim())->addFilter(new Zend_Filter_StripTags());
 		$this->_isSearch = ($doSearch == 'true') ? true : false ;
		 
		foreach($postVars as $field => $data){
			if('null' == $data and $doSearch == 'true'){
				$this->_grid->removeParam($field);
				unset($postVars[$field]);
			}else{
				$postVars[$field] = $filterChain->filter($data);
			}
		}

		foreach($prmNames as $val){ //prefix to cope with multiple grid/subgrid per request
			$zfkey = $val . $gridId;
			if(isset($postVars[$val])){
				$this->_grid->setParam($zfkey, $postVars[$val]);
			}
		}

		$page = $request->getPost($prmNames[Rhema_Grid_JqgParam::CURRENT_PAGE], 1);
		$zfPageKey = 'page' . $gridId;
		$this->_grid->setParam($zfPageKey, $page);

		$columns = $this->getAdapter()->getColumns();
		/*
		 * Determines data returned based on request and table time
		 * e.g. table is a tree table
		 */
		$this->_daoFilter->setSearchCriteria($postVars, array_keys($columns));

		if(isset($postVars[$sortColumnKey])){
			$sortField = $postVars[$sortColumnKey];
			$sortOrder = $postVars[$sortOrderKey];
			$this->_daoFilter->addOrderBy($sortField, $sortOrder);
		}else{
			$this->_daoFilter->addOrderBy(Rhema_Grid_JqgParam::SORT_COLUMN);
		}
			
		if($this->_isTreeGrid){
			$this->_daoFilter->setOrderBy('lft');
		}
		
		$limit = isset($postVars[$limitIndex]) ? $postVars[$limitIndex] : Rhema_Dao_Filter::QUERY_LIMIT ;
		if($limit){
			$this->getGrid()->setRecordsPerPage($limit);
			$this->_daoFilter->setLimit($limit);

			$zfLimitKey = 'perPage' . $gridId;
			if(! isset($postVars[$zfLimitKey])){
				$this->_grid->setParam($zfLimitKey, $limit);
			}

			$zfStartKey = 'start' . $gridId;
			if(! isset($postVars[$zfPageKey])){
				$start = ($page - 1) * $limit;
				$this->_grid->setParam($zfStartKey, $start);
			}
		}


		return $this->_daoFilter;
	}

	/**
	 * @param field_type $_type
	 */
	public function setType($_type){
		$this->_type = $_type;
		return $this;
	}

	/**
	 * @param field_type $_source
	 */
	public function setSource($_source){
		$this->_source = $_source;
		return $this;
	}

	/**
	 * @param field_type $_daoFilter
	 */
	public function setDaoFilter($_daoFilter){
		$this->_daoFilter = $_daoFilter;
		return $this;
	}
	/**
	 * @return the $_gridId
	 */
	public function makeGridId(){
		if(! $this->_gridId){
			$request = Zend_Controller_Front::getInstance()->getRequest();
			$params = array(
					$this->_model,
					Rhema_Constant::GRID_NODE,
					$request->getParam('type_id', ''),
					$request->getParam('page_type', ''),
					$request->getParam('rootType', ''),
					$request->getParam('file_type', ''),
					$request->getParam('root_id', ''),
					$request->getParam(Admin_Model_AdminMenu::TYPE_OPHANS, null) ? 'ops' : '',
					$request->getParam(Admin_Model_AdminMenu::TYPE_ROOTS_ONLY, null) ? 'roots' : ''
					);

			$params = array_filter($params);
			$id = implode('-', $params);
			$filter = new Rhema_Filter_FormatCssClassName();
			$this->_gridId = strtolower($filter->filter($id));

		}
		return $this->_gridId;
	}

	/**
	 * @param field_type $_gridId
	 */
	public function setGridId($_gridId){
		$this->_gridId = $_gridId;
		return $this;
	}

	public function getActionBarColumn($position = self::POS_RIGHT){
		$actionCol = new Bvb_Grid_Extra_Column();
		$actionCol->position($position)
					->title('Action')
					->name('_action')
					->callback(array('function' => array( __CLASS__,
														'actionBarCallback'),
														'params' => array('{{id}}') )
					);

		return $actionCol;
	}

	public static function formatRoleCheckbox($role, $model, $id, $mod, $cont, $action){
		$util     = Rhema_Util::getInstance();
		$aclModel = new Admin_Model_AdminAcl();
		$scope    = ($model == ADMIN_PREFIX . 'Menu')
					? Admin_Model_AdminAcl::FRONTEND_ACCESS_LIST
					: Admin_Model_AdminAcl::BACKEND_ACCESS_LIST;
		$role     = strtolower($role);

		if(Zend_Registry::isRegistered('role-acl')){
			$acl = Zend_Registry::get('role-acl');
		}else{
			$acl      = $aclModel->getAcl($scope);
			Zend_Registry::set('role-acl', $acl);
		}
		$disabled = false ;

		$data['m_module'] = $mod;
		$data['m_controller'] = $cont ;
		$data['m_action'] = $action;

		$resource = $util->getMenuResource($data);
		$priv     = $util->getMenuPrivilege($data);
		$allowed  = ($acl->has($resource) and $acl->isAllowed($role, $resource, $priv)) ? true : false;

		foreach($data as $val){
			if(!trim($val)){
				$disabled = true;
				break;
			}
		}
		$input = new Zend_Form_Element_Checkbox(array(
		    'decorators' => array('ViewHelper',),
			'name'     => "{$id}_{$role}",
		    'value'    => 1,
		    'checked'  => $allowed
		));

		if($disabled){
			$input->setAttrib('disabled', true);
		}

		return (string) $input;
	}

	public function addFeedColumns(){
		$column = new Bvb_Grid_Extra_Column();
		$column->position('right')
				->name('_hasFeed_')
				->title('Feed')
				->style('width:40px;')
				->callback(array('function' => array( __CLASS__,
													'displayFeedExist'),
													'params' => array('{{feed_url}}')
				));
		$this->_grid->addExtraColumns($column);
				
		$column = new Bvb_Grid_Extra_Column();
		$column->position('right')
				->name('_updFeed_')
				->title('Update')
				->style('width:60px;')
				->callback(array('function' => array( __CLASS__,
													'formatUpdateButton'),
													'params' => array('{{id}}','{{affiliate_feed_type_id}}')
				));
		$this->_grid->addExtraColumns($column); 
		return $this; 
	}
	
	public function addRoleColumns($roles){
		foreach($roles as $data){
			$column = new Bvb_Grid_Extra_Column();
			$label  = isset($data['label']) ? $data['label'] : $data['title'];
			$column->position('right')
					->name($data['title'])
					->title($label)
					->class('acl-box')
					->callback(array('function' => array( __CLASS__,
														'formatRoleCheckbox'),
														'params' => array($data['title'],
																		  $this->_model,
																		  '{{id}}',
																		  '{{m_module}}',
																		  '{{m_controller}}',
																		  '{{m_action}}',
													)
					));
			$this->_grid->addExtraColumns($column);
		}
		return $this;
	}

	public function addHelpColumn(){

		$column = new Bvb_Grid_Extra_Column();
		$column->position('right')
				->name('_info_')
				->title('Help')
				->style('width:40px;')
				->callback(array('function' => array( __CLASS__,
													'formatHelpColumn'),
													'params' => array('{{label}}', '{{description}}')
				));
		$this->_grid->addExtraColumns($column);
		$this->_grid->updateColumn('_info_', array('class' => 'grid-thumb'));
		return $this;
	}

	public static function formatUpdateButton($id, $feedTypeId){
		if(true or strpos(strtolower($feedTypeId), 'plain') !== false){
			$typeName = Doctrine_Inflector::urlize($feedTypeId);
			$view = Zend_Layout::getMvcInstance()->getView();
			$url  = $view->url(array('task' => 'update', 'id' => $id, 'table' => 'affiliate_feed'), 'preview-mapping');
			return "<a href='{$url}' class='fm-button green feed_update' id='feed_{$id}'>Update</a>";	
		}	else{
			return '' ;
		}
	}
	
	public static function formatHelpColumn($title, $tip){
		$html = '' ;
		if(trim($tip)){
			$view = Zend_Layout::getMvcInstance()->getView();
			$src  = $view->getImagePath()->backendIcon('help.png');
			$html = "<img src='{$src}' width='10' class='atip' title='$title|$tip'/>";
		}
		return $html ;
	}



	public static function actionBarCallback($id){
		$helper = new Zend_View_Helper_Url();
		$actions = array(
				array(
						'href' => $helper->url(array(
								'action' => 'do',
								'what' => 'view',
								'id' => $id)),
						'caption' => 'View',
						'class' => '{view}'),
				array(
						'href' => $helper->url(array(
								'action' => 'do',
								'what' => 'edit',
								'id' => $id)),
						'caption' => 'Edit',
						'class' => '{edit} fixedClass'),
				array(
						'href' => $helper->url(array(
								'action' => 'do',
								'what' => 'delete',
								'id' => $id)),
						'caption' => 'Delete',
						'class' => '{delete}'),
				array(
						'onclick' => new Zend_Json_Expr('alert("you clicked on ID: "+jQuery(this).closest("tr").attr("id"));'),
						'caption' => 'Alert Me'));
		return Bvb_Grid_Deploy_JqGrid::formatterActionBar($actions);
	}
	/**
	 * @return the $_model
	 */
	public function getModel(){
		return $this->_model;
	}

	/**
	 * @param field_type $_model
	 */
	public function setModel($_model){
		$filter = new Rhema_Filter_FormatModelName();
		$this->_model = $filter->filter(trim($_model));
		return $this;
	}

	public function __call($method, $args = array()){
		$callback = array(
				$this->_grid,
				$method);
		return call_user_func_array($callback, $args);
	}
	/**
	 * @param field_type $_grid
	 */
	public function setGrid($_grid){
		$this->_grid = $_grid;
	}
	/**
	 * @return the $_acl
	 */
	public function getAcl($scope){
		if(!$this->_acl){
			$this->_acl = Rhema_Util::getAcl($scope);
		}
		return $this->_acl;
	}

	/**
	 * @param field_type $_acl
	 */
	public function setAcl($_acl){
		$this->_acl = $_acl;
		return $this;
	}


}