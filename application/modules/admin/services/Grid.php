<?php
class Admin_Service_Grid extends Bvb_Grid_Deploy_JqGrid implements Rhema_Cache_Interface{
	
	protected $_instance;
	protected $_gridId = 'grid-';
	protected $_pagerId = 'pager-';
	protected $_table;
	protected $_filename;
	protected $_adp;
	protected $_columnModel;
	protected $_cacheType = 'class-file';
	protected $_util;
	protected $_cache;
	protected $_cacheOptions;
	protected $_pager;
	protected $_byPass = true;
	protected $_toolbarButtons = array();
	protected $_imageExtensions = array('png', 'gif', 'jpg');
	protected $_imageUrl;
	protected $_dateFormat;
	
	protected $gridParam = array('datatype' => 'json', 'autowidth' => false, 'sortname' => 'title', 'viewrecords' => true, 'sortorder' => 'asc', 'caption' => 'Table record', 'forceFit' => false, 'multiselect' => true, 'width' => 870, 'height' => 'auto', 'sortable' => true, 'loadui' => 'disable', 
			'altRows' => true, 'gridview' => true, 'toolbar' => array(true, 'bottom'), 'rowList' => array(5, 10, 15, 20, 30, 40, 50, 100), 'toppager' => true);
	
	public $editParam = array('editWidth' => 850, 'editPos' => '', 'editHeight' => 'auto', 'canadd' => true, 'canedit' => true, 'candel' => true, 'cansearch' => true, 'canview' => true);
	
	public $placeHolder = array();
	
	public function __construct($id, $table, $params = array(), $requestArray = array(), $edit = array()){
		$this->_util = Rhema_Util::getInstance();
		$this->_dateFormat = 'Y-m-d';
		if(isset($params['factory'])){
			$this->_adp = new Admin_Service_Grid_Adapter_Database($requestArray);
		
		//return $this;
		}else{
			$config = (array) Rhema_SiteConfig::getConfig('grid');
			parent::__construct($config);
			
			$this->editParam = array_merge($this->editParam, $edit);
			$requestArray = array_merge($requestArray, $this->getAllParams());
			if(! isset($requestArray['rows'])){
				$requestArray['rows'] = $this->_pagination;
			}
			$this->_adp = new Admin_Service_Grid_Adapter_Database($requestArray);
			$this->_table = $table;
			$this->bvbId = $id;
			$this->_gridId = $id;
			$this->setId($id);
			
			$params['caption'] = $this->_util->getlabel($table);
			$this->gridParam = array_merge($this->gridParam, $params);
			
			$this->configure($this, $table);
		}
	}
	
	public function configure($grid, $table){
		$this->_pager = $this->_adp->getPager($table);
		//$query             				= $this->_pager->getQuery();
		$router = Zend_Controller_Front::getInstance()->getRouter();
		$source = new Admin_Service_Grid_Source_Pager($this->_pager);
		$arr = array();
		
		$this->_imageUrl = '/backend/images/icons';
		
		$grid->setImageUrl($this->_imageUrl);
		$grid->setSource($source);
		
		$this->getColumnModel($table);
		
		$exportUrl = array('module' => 'admin', 'controller' => 'grid', 'action' => 'export', 'ajx' => 0, 'table' => $table);
		$expFormat = array('PDF', 'Word', 'CSV', 'Wordx', 'XML', 'Excel', 'Print');
		
		foreach($expFormat as $fmt){
			$x = strtolower($fmt);
			$exportUrl['_exportTo'] = $x;
			$arr[$x]['caption'] = $fmt;
			$arr[$x]['url'] = $router->assemble($exportUrl, ADMIN_ROUTE);
		}
		
		$grid->setExport($arr);
		
		foreach($this->gridParam as $k => $v){
			$grid->setJqgParam($k, $v);
		}
	
	}
	
	public function jqInit(){
		
		return $this;
	}
	
	public function renderPartData(){
		$data = new stdClass();
		$data->rows = array();
		foreach(parent::_buildGrid() as $row){
			$dataRow = new stdClass();
			$d = array();
			foreach($row as $val){
				$d[] = $val['value'];
			}
			$dataRow->cell = $d;
			$data->rows[] = $dataRow;
		}
		
		$data->page = $this->_pager->getPage();
		$data->total = $this->_pager->getLastPage();
		$data->records = $this->_pager->getNumResults();
		
		return Zend_Json::encode($data);
	}
	
	public function findIcon($url, $prefix){
		$return = '';
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$root = $request->getServer('DOCUMENT_ROOT');
		foreach($this->_imageExtensions as $ext){
			$filename = $url . '/' . $prefix . '.' . $ext;
			if(file_exists($root . '/' . $filename)){
				$return = $filename;
				break;
			}
		}
		return $return;
	}
	
	public function addToolbarButtons(){
		$gridId = $this->jqgGetIdTable();
		$toolbarId = 't_' . $gridId;
		$buttons = $this->getExports();
		
		foreach($buttons as $exp => $options){
			$imageUrl = $this->findIcon($this->_imageUrl, $exp);
			$href = $options['url'];
			$script = sprintf("jQuery('#%s').append(\"<a href='%s' class='export-icon' target='_blank'><img src='%s' border='0' width='16' /></a>\");", $toolbarId, $href, $imageUrl);
			$this->bvbSetOnInit($script);
		}
	}
	
	public function prepareOptions(){
		//parent::prepareOptions();
		

		$id = $this->getId();
		$url = isset($this->gridParams['url']) ? $this->gridParams['url'] : $this->getView()->serverUrl(true);
		$url .= "?q=$id";
		$this->_jqgParams += $this->_jqgDefaultParams;
		$this->_jqgParams['url'] = $url;
		$this->_jqgParams['pager'] = new Zend_Json_Expr(sprintf("'#%s'", $this->jqgGetIdPager()));
		$this->_jqgParams['rowNum'] = $this->_pagination;
		
		//$url         = $this->getJqgParam('url');
		$url = str_replace('/ajx/1', '', $url);
		$this->setJqgParam('url', $url);
		$this->addToolbarButtons();
		
		if(! $this->getInfo('noFilters', false)){
			$this->_postCommands[] = 'jqGrid("filterToolbar")';
			$this->jqgAddNavButton(array('caption' => '', 'title' => $this->__("Toggle Search Toolbar"), 'buttonicon' => 'ui-icon-pin-s', 'onClickButton' => new Zend_Json_Expr("function(){ jQuery(this)[0].toggleToolbar(); }")));
			
			$this->jqgAddNavButton(array('caption' => '', 'title' => "Clear Search", 'buttonicon' => 'ui-icon-refresh', 'onClickButton' => new Zend_Json_Expr("function(){ jQuery(this)[0].clearToolbar(); }")));
		}
		
		$this->jqgAddNavButton(array('caption' => '', 'title' => "Reorder Columns", 'buttonicon' => 'ui-icon-folder-open', 'onClickButton' => new Zend_Json_Expr("function(){ jQuery(this).jqGrid('columnChooser'); }")));
		
		$this->_postCommands[] = $this->getPagerScript();
		$toolbarScript = $this->getCached($this->_table)->getToolbarScript($this->_table, $id);
		$this->bvbSetOnInit($toolbarScript);
	
	}
	
	protected function getExportButtonJs($url, $newWindow, $exportTo){
		$data = $this->cmd("getGridParam", "postData");
		
		$getUrl = <<<JS
var url  = "$url";
var data = $data;
url      = url + "?_exportFrom=jqGrid&_exportTo=$exportTo";
JS;
		if($newWindow){
			return <<<JS
function() {
    $getUrl
    newwindow = window.open(url);
    if (window.focus) {
        newwindow.focus();
    }
    return false;
}
JS;
		}else{
			return <<<JS
function() {
    $getUrl
    location.href = url;
}
JS;
		}
	}
	
	public function getData($arr = array()){
		$this->setDbParam($arr);
		$pager = $this->_adp->getPager($this->_table);
		$result = $pager->execute();
		$this->returnData = (object) array();
		$this->returnData->page = $pager->getPage();
		$this->returnData->total = $pager->getLastPage();
		$this->returnData->records = $pager->getNumResults();
		
		$count = $result->count();
		$columns = $this->getColumns($this->_table);
		$isSetting = (substr($this->_table, - 7) == 'Setting' and $this->_adapter->getParam('setting')) ? true : false;
		
		if($isSetting){
			$local = Admin_Model_Setting::getAllSettings();
		}
		
		if($count){
			$i = 0;
			foreach($result as $row){
				//$row = $isSetting ? $item['AdminSetting'] : $item;
				$sId = $row['id'];
				$this->returnData->rows[$i]['id'] = $sId;
				$cell = array();
				foreach($columns as $field => $arr){
					if($isSetting and $field == 'param'){
						//$cell[] = isset($local[$sId]) ? $local[$sId] : $item[$field];
						$cell[] = isset($local[$sId]) ? $local[$sId] : '';
					}else{
						$cell[] = isset($row[$field]) ? $row[$field] : '';
					}
				}
				$this->returnData->rows[$i]['cell'] = array_values($cell);
				$i ++;
			}
		
		}
		return Zend_Json::encode($this->returnData);
	}
	
	public function display(){
		$this->setId($this->_gridId);
		Bvb_Grid::deploy();
		
		$this->jqInit();
		$this->prepareOptions();
		$this->_jqgParams['colModel'] = $this->jqgGetColumnModel();
		
		$script = 'jQuery(function () { ' . $this->renderPartJavascript() . ' })';
		$view = $this->getView();
		$view->inlineScript()->setScript($script);
		$script = $view->inlineScript();
		$return = '<div class="data-grid">' . $this->renderPartHtml() . $script . '</div>';
		
		return $return;
	
	}
	
	public function __2construct($table, $params = array(), $requestArray = array()){
		
		$this->_adapter = new Admin_Service_Grid_Adapter_Database();
		$this->_util = $this->_util->getInstance();
		$this->_gridId .= $table . '-' . rand(1, 300);
		$this->_pagerId .= $table . '-' . rand(1, 300);
		$this->_table = $table;
		//$tableName						 = isset($params['tableName']) ? $params['tableName'] : $table;
		

		$this->setDbParam($requestArray);
		$this->gridParam = array_merge($this->gridParam, $params);
		$this->gridParam['rowList'] = Zend_Json::encode(array(5, 10, 15, 20, 30, 40, 50, 100));
		$modelString = Zend_Json::encode($this->getColumnModel($table));
		$this->gridParam['colModel'] = preg_replace(array_keys($this->placeHolder), array_values($this->placeHolder), $modelString);
		$this->gridParam['caption'] = $this->_adapter->getCaption($this->gridParam['tableName']);
		
		return $this;
	}
	
	/*	private function __get($key){
    		return $this->$key;
    	}

    	private function __set($key, $value){
    		$this->$key = $value;
    	}
   */
	public function getColumns($table){
		return $this->_adp->getCached($table)->getColumns($table);
	}
	
	public function setDbParam($params = array()){
		$this->_adp->setParam($params);
		return $this;
	}
	
	public function getToolbarScript($table){
		$default 	= '';
		$button 	= array();
		include_once ('Rhema/Button.php');
		$type = array();
		$gridId = $this->jqgGetIdTable();
		$router = Zend_Controller_Front::getInstance()->getRouter();
		$return[] = "var lastsel; var noRow  = rms.initDialog('No Selection','Please select row');";
		
		switch($table){
			case MODEL_PREFIX . 'Template' :
			case ADMIN_PREFIX . 'AdminTemplate' :
				{
					$type = array('sections');
					break;
				}
			
			case ADMIN_PREFIX . 'AdminSubsite' :
				{
					$type = array('add-licence');
					break;
				}
			
			case ECOM_PREFIX . 'EcomNavigationMenu' :
			case MODEL_PREFIX . 'Menu' :
			case ADMIN_PREFIX . 'AdminMenu' :
				{
					$type = array('tree-setup');
					break;
				}
			case HELP_PREFIX . 'HelpTemplate' :
				{
					$type = array('fields', 'layout');
					//$type      = array('layout');
					break;
				}
			
			case MODEL_PREFIX . 'PageHeader' :
			case MODEL_PREFIX . 'PageFooter' :
			case MODEL_PREFIX . 'Page' :
				{
					$type = array('page-layout', 'preview');
					break;
				}
			case ECOM_PREFIX . 'EcomDisplayTemplate' :
				{
					$type = array('attributes');
					break;
				}
			case MODEL_PREFIX . 'WebForm' :
				{
					$type = array('manage-form');
					break;
				}
			case ECOM_PREFIX . 'EcomProduct' :
				{
					$type = array('product-category');
					break;
				}
		}
		
		//asort($type);
		

		foreach($type as $id){
			$url_1 = '';
			$url_2 = '';
			$title = '';
			$vars = array();
			
			switch($id){
				case 'sections' :
					{
						$url_1 = $router->assemble(array('module' => 'admin', 'controller' => 'grid', 'action' => 'section'), ADMIN_ROUTE);
						$url_2 = $router->assemble(array('module' => 'admin', 'controller' => 'grid', 'action' => 'section', 'task' => 'save'), ADMIN_ROUTE);
						$title = 'View Sections';
						break;
					}
				
				case 'add-licence' :
					{
						$url_1 = $router->assemble(array('module' => 'admin', 'controller' => 'grid', 'action' => 'licence'), ADMIN_ROUTE);
						$url_2 = $router->assemble(array('module' => 'admin', 'controller' => 'grid', 'action' => 'licence', 'task' => 'save'), ADMIN_ROUTE);
						$title = 'Add Licence';
						break;
					}
				
				case 'manage-form' :
					{
						$url_1 = $router->assemble(array('module' => 'cms', 'controller' => 'design', 'action' => 'formlayout'), ADMIN_ROUTE);
						$title = 'Manage Form';
						break;
					}
				
				case 'fields' :
					{
						$url_1 = $router->assemble(array('module' => 'help', 'controller' => 'design', 'action' => 'assign'), ADMIN_ROUTE);
						$title = 'View fields';
						break;
					}
				
				case 'layout' :
					{
						$url_1 = $router->assemble(array('module' => 'help', 'controller' => 'design', 'action' => 'layout', 'table' => $table), ADMIN_ROUTE);
						$title = 'Layout';
						break;
					}
				
				case 'page-layout' :
					{
						$url_1 = $router->assemble(array('module' => 'cms', 'controller' => 'design', 'action' => 'layout', 'table' => $table), ADMIN_ROUTE);
						$title = 'Page Layout';
						break;
					}
				
				case 'preview' :
					{
						$url_1 = $router->assemble(array('module' => 'cms', 'controller' => 'design', 'action' => 'url'), ADMIN_ROUTE);
						$title = 'Preview Page';
						break;
					}
				
				case 'attributes' :
					{
						$title = 'Attributes';
						$url_1 = $router->assemble(array('module' => 'ecom', 'controller' => 'index', 'action' => 'attribute', 'table' => $this->_table), ADMIN_ROUTE);
						break;
					}
				case 'product-category' :
					{
						$title = 'Add Category';
						$url_1 = $router->assemble(array('module' => 'ecom', 'controller' => 'index', 'action' => 'addcategory', 'table' => $this->_table), ADMIN_ROUTE);
						$url_2 = $router->assemble(array('module' => 'ecom', 'controller' => 'index', 'action' => 'addcategory', 'table' => $this->_table, 'task' => 'save'), ADMIN_ROUTE);
					}
			}
			
			$vars = array_merge($vars, array('/GRID_ID/' => $gridId, '/TOOLBAR_ID/' => 't_' . $gridId, '/BTN_ID/' => 'btn_' . $id . $gridId, '/MENU_ID/' => 'menu_' . $gridId, '/TITLE/' => $title, '/URL/' => $url_1, '/LINK/' => $url_2));
			
			$pattern = array_keys($vars);
			$replace = array_values($vars);
			
			if($title){
				$str = preg_replace('/SCRIPT/', $button[$id], $default);
				$return[] = preg_replace($pattern, $replace, $str);
			
			}elseif($id == 'tree-setup'){
				$return[] = preg_replace($pattern, $replace, $button[$id]);
			}
		}
		
		return implode(' ', $return);
	}
	
	public function getPagerScript(){
		$gridId = $this->jqgGetIdTable();
		$pagerId = $this->jqgGetIdPager();
		$param = $this->editParam;
		
		//$gridId  = str_replace('pager_','', $pagerId);
		$script = "jqGrid('navGrid','#$pagerId', {view:true, refresh:true, search:true, add:$param[canadd], edit:$param[canedit], del:$param[candel]},
						{	height:'auto',
							width:" . $param['editWidth'] . ",
							reloadAfterSubmit : true,
							jqModal:true,
							closeOnEscape:false,
							recreateForm: true,
							modal:false,
							bottominfo:'Fields marked with (*) are required',
							beforeShowForm: function(frmId){ rms.prepareForm(frmId,'#$gridId');},
							afterclickPgButtons:  function(a,b,c){rms.afterShowForm(a,b,c); },
							onClose: function(frmId){rms.cleanUpForm(frmId);}
						} ,
						{	height:'auto',
							width:" . $param['editWidth'] . ",
							reloadAfterSubmit : true,
							jqModal:true,
							closeOnEscape:false,
							recreateForm: true,
							modal:false,
							bottominfo:'Fields marked with (*) are required',
							closeAfterAdd: true,
							beforeShowForm: function(frmId){rms.prepareForm(frmId,'#$gridId'); },
							onClose: function(frmId){rms.cleanUpForm(frmId);}
						} ,
						{	reloadAfterSubmit : true,
							jqModal:false,
							closeOnEscape:false
						} ,
						{
							closeOnEscape : false,
							sFilter: 'filters',
							multipleSearch : true
						}  ,
						{	width:" . $param['editWidth'] . ",
							height:'auto',
							afterclickPgButtons:  function(a,b,c){rms.afterShowForm(a,b,c); },
							beforeShowForm: function(frmId){rms.prepareViewForm(frmId,'#$gridId'); }
						}
					 )";
		return $this->_util->filterText($script);
	}
	
	public function getOptions($model, $table, $key, $field, $show, $col){
		return $this->_adapter->getOptions($model, $table, $key, $field, $show, $col);
	}
	
	public function getColumnModel($table, $sortField = 'title'){
		$reqStr = '(*)' . str_repeat('&nbsp;', 2);
		$noReqStr = str_repeat('&nbsp;', 6);
		
		$row = 1;
		
		$columns = $this->getCached($table)->getColumns($table);
		$sortField = isset($columns[$sortField]) ? $sortField : 'id';
		
		foreach($columns as $name => $columnData){
			$colModel = $this->getProperties($name, $row, $table, $columnData);
			$required = (isset($columnData['notnull']) and $columnData['notnull']);
			$colModel['jqg']['required'] = $required;
			$formOptions = "<span class='field-req req-$name'>" . ($required ? $reqStr : $noReqStr) . '</span>';
			
			$colModel['jqg']['formoptions']['elmprefix'] = $formOptions;
			$headers[] = $name;
			$labels[] = $this->_util->getLabel($name);
			
			$this->updateColumn($name, $colModel);
			$row ++;
		
		}
		
		$this->setJqgParam('colNames', $labels);
		$this->setGridColumns($headers);
		
		$this->gridParam['sortname'] = $this->_adp->getSortField($table, $sortField);
	}
	
	public function getProperties($col, $order = 0, $model = null, $colData = array()){
		
		$show = true; // do not shot empty select as first option
		$gridParam = array();
		$jqg = array('index' => $col, 'name' => $col, //'width'  		=> 120,
				'editable' => true, 'edittype' => 'text', 'required' => true, 
				'sortable' => true, 'readonly' => false, 
				'editrules' => array('edithidden' => true), 'label' => $col);
		
		$url = array('module' => 'admin', 'controller' => 'grid', 'action' => 'option');
		$query = array('model' => $model, 'col' => $col, 'tm' => time());
		
		if(isset($colData['default'])){
			$jqg['editoptions']['defaultValue'] = $colData['default'];
		}
		if('id' == $col or 'admin_subsite_id' == $col){
			$jqg['editable'] = false;
			$jqg['readonly'] = true;
			$jqg['required'] = false;
			$jqg['key'] = ('id' == $col) ? true : false;
			$jqg['editable'] = false;
			$jqg['width'] = 50;
			$jqg['hidden'] = false;
		}elseif($this->_adp->isRestricted($col, $model)){
			$jqg['hidden'] = true;
			$jqg['editrules']['edithidden'] = false;
			$jqg['editable'] = false;
		}elseif('title' == $col){
			$jqg['width'] = 250;
		}elseif('version' == $col){
			$jqg['editable'] = false;
		}elseif(preg_match('/^(excerpt|information|content|description|summary|keyword|note|related_item|template|pretext|posttext)$/i', $col)){
			$jqg['edittype'] = 'textarea';
			$jqg['editrules']['edithidden'] = true;
			$jqg['editable'] = true;
			$jqg['width'] = 200;
			$jqg['class'] = 'rms-textarea';
			$jqg['hidden'] = true;
			$gridParam['escape'] = 'stripslashes';
			switch($col){
				case 'content' :
					{
						$order += 600;
						break;
					}
				case 'note' :
					{
						$order += 300;
					}
				default :
					{
						$order += 300;
						break;
					}
			}
		}elseif('content' == $col){
			$jqg['hidden'] = true;
		}elseif('_at' == substr($col, - 3)){
			$jqg['editoptions']['defaultValue'] = date(DB_DATE_FORMAT);
			$jqg['edittype'] = 'text';
			$jqg['formatter'] = 'date';
			$jqg['formatoptions']['srcformat'] = DB_DATE_FORMAT; //'ISO8601Long';
			$jqg['formatoptions']['newformat'] = $this->_dateFormat; //'SortableDateTime';
			

			$picker = new Zend_Json_Expr(" function(el){ gbl.doDate(el); } ");
			$jqg['editoptions']['dataInit'] = $picker;
			$jqg['searchoptions']['dataInit'] = $picker;
			if(preg_match('/^(created_at|updated_at|deleted_at)$/i', $col)){
				$jqg['editrules']['edithidden'] = false;
				$jqg['editable'] = false;
				$jqg['hidden'] = true;
			}
		}elseif('_id' == substr($col, - 3) or substr($col, - 3) == '_by'){
			$field = 'title id';
			$key = 'id';
			$refCol = $col;
			$jqg['number'] = true;
			$jqg['hidden'] = true;
			if('admin_subsite_id' == $col){
				$jqg['editable'] = false;
			}elseif('admin_table_id' == $col){
				//	$key    = 'name';
				$jqg['number'] = false;
			}elseif(preg_match('/^(header_id|footer_id)$/i', $col)){
				$refCol = 'page_id';
				$pageType = 'is_' . substr($col, 0, - 3);
				Zend_Registry::set('page_type', $pageType);
			}elseif(substr($col, - 3) == '_by' or 'user_id' == $col){
				$refCol = 'user_id';
				$key = 'id';
				$field = 'firstname lastname id';
				$jqg['editable'] = false;
				if($model == MODEL_PREFIX . 'Event' and 'user_id' == $col){
					$jqg['editable'] = true;
				}
			}
			
			$jqg['edittype'] = 'select';
			$jqg['stype'] = 'select';
			
			$query['refCol'] = $refCol;
			$query['show'] = true; //$show ? 1 : 0;
			$query['key'] = $key;
			$query['field'] = $field;
			
			//if(NO_CACHE){
			if(false or substr($col, - 7) == 'menu_id'){
				//if(false){
				$freshCall = $this->_util->assemble($url, ADMIN_ROUTE) . '?' . http_build_query($query);
				$jqg['editoptions']['dataUrl'] = $freshCall;
				$jqg['searchoptions']['dataUrl'] = $freshCall;
			}else{
			//	$tags = array($refCol, $this->_util->table2Model($refCol));
				$freshCall = $this->_adp->getCached()->getOptions($model, $refCol, $key, $field, $show);
				$jqg['editoptions']['value'] = $freshCall;
				$jqg['searchoptions']['value'] = $freshCall;
			}
			
			$jqg['width'] = 150;
			$order += 40;
		}elseif('widget' == $col){
			$url['action'] = 'widget';
			$freshCall = $this->_util->assemble($url, ADMIN_ROUTE);
			$jqg['editoptions']['dataUrl'] = $freshCall;
			
			$jqg['edittype'] = 'select';
			$select = true;
			$jqg['hidden'] = false;
			$jqg['stype'] = 'select';
			$jqg['searchoptions']['searchhidden'] = true;
		
		}elseif(preg_match('/^(module|controller|action|module_dir|country)$/i', $col)){
			$jqg['edittype'] = 'select';
			$options = array('' => 'select', 'index' => 'index');
			$select = true;
			$jqg['hidden'] = true;
			$jqg['stype'] = 'select';
			$jqg['searchoptions']['searchhidden'] = true;
			
			switch($col){
				case 'module_dir' :
				case 'module' :
					{
						$tags = array('grid', 'admin_menu_id', 'menu_id', 'ecom_navigation_menu_id');
						$options = $this->_util->getCached('menu')->getDir('/modules');
						$jqg['editoptions']['defaultValue'] = Zend_Controller_Front::getInstance()->getDefaultModule();
						break;
					}
				case 'controller' :
					{
						
						break;
					}
				
				case 'country' :
					{
						$options = $this->_util->getCountryList();
						break;
					}
				case 'action' :
					{
						if(MODEL_PREFIX . 'Menu' == $model){
							$jqg['edittype'] = 'text';
							$select = false;
						}
						break;
					}
			}
			
			if($select){
				$jqg['editoptions']['value'] = $options;
				$jqg['searchoptions']['value'] = $options;
			}
		
		}elseif('is_' == substr($col, 0, 3)){
			$jqg['formatter'] = 'checkbox';
			$jqg['edittype'] = 'checkbox';
			$jqg['hidden'] = true;
			$jqg['width'] = 50;
			$jqg['editoptions']['value'] = "1:0";
			$jqg['searchoptions']['value'] = "1:0";
			$jqg['align'] = 'center';
			$jqg['editoptions']['defaultValue'] = 1;
			$order += 20;
		}elseif('image_file' == $col or 'thumb' == $col){
			$jqg['width'] = 60;
			//$jqg['hidden']  = true;
			$order += 70;
		}elseif(preg_match('/email$/i', $col)){
			$jqg['editrules']['email'] = true;
		}elseif(substr($col, - 3) == '_by'){
			$jqg['editable'] = false;
			$jqg['editrules']['edithidden'] = false;
		}elseif(preg_match('/^(sequence|row|column)$/i', $col)){
			$jqg['editoptions']['defaultValue'] = 1;
			$jqg['editrules']['minValue'] = 0;
			$jqg['editrules']['integer'] = true;
			$jqg['editrules']['number'] = true;
			$jqg['sorttype'] = 'int';
			$order += 45;
		}elseif('question' == $col){
			$jqg['edittype'] = 'textarea';
			$order += 111;
		}elseif('answer' == $col){
			$jqg['edittype'] = 'textarea';
			$order += 152;
		}elseif(preg_match('/^(price|cost|rrp)$/i', $col)){
			$currency = new Zend_Currency();
			$symbol = $currency->getSymbol();
			$jqg['formatter'] = 'currency';
			$jqg['formatoptions']['thousandsSeparator'] = ',';
			$jqg['formatoptions']['prefix'] = $symbol;
			$jqg['editoptions']['defaultValue'] = 0;
			$jqg['sorttype'] = 'float';
		}elseif($colData['type'] == 'enum'){
			$jqg['edittype'] = 'select';
			$jqg['stype'] = 'select';
			$jqg['editoptions']['value'] = $colData['values'];
			$jqg['searchoptions']['value'] = $colData['values'];
			$jqg['width'] = 150;
		}
		
		if($this->_adp->isRestricted('admin_table_id', $model) and 'param' != $col){
			$jqg['editable'] = false;
		}
		
		$jqg['formoptions']['rowpos'] = $order;
		
		$gridParam['jqg'] = $jqg;
		
		return $gridParam;
	}
	
	//================== implement cache interface ==============================
	public function setCache(Rhema_Cache_Abstract $cache){
		$this->_cache = $cache;
	}
	
	public function setCacheOptions(array $options, $type = 'class-file'){
		$this->_cacheOptions[$type] = $options;
	}
	
	public function getCacheOptions(){
		if(empty($this->_cacheOptions)){
			$this->_cacheOptions = $this->_util->getCacheOptions($this->_cacheType, $this);
		}
		
		$cacheDir = APPLICATION_PATH . '/../data/cache/grid';
		if(! file_exists($cacheDir)){
			mkdir($cacheDir, 0777, true);
		}
		$this->_cacheOptions['backendOptions']['cache_dir'] = realpath($cacheDir);
		
		return $this->_cacheOptions;
	}
	
	public function getCached($tagged = null){
		if(defined('NO_CACHE') and NO_CACHE){
			return $this;
		}
		
		if(null == $this->_cache){
			$this->_cache = new Rhema_Cache($this, $this->getCacheOptions());
		}
		
		$this->_cache->setTagged($tagged);
		return $this->_cache;
	}

	//======================================================================================
}
