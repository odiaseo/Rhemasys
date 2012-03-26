<?php
class Rhema_Grid extends Bvb_Grid_Deploy_JqGrid{
	
	protected $_navBarOptions = array();
	protected $_leftKey = null;
	protected $_rightKey = null;
	protected $_levelKey = null;
	
	public function __construct($defaultClass, $options = array(), $id = '', $classCallbacks = array(), $requestParams = false){
		$options['grid']['requestParams'] = $requestParams;
		parent::__construct($options);
		if(is_string($id)){
			$this->setGridId($id);
		}
		
		if(isset($classCallbacks['Bvb_Grid_Deploy_JqGrid'])){
			$this->_configCallbacks = $classCallbacks['Bvb_Grid_Deploy_JqGrid'];
		}
	}
	
	/**
	 * Sets the grid with and dataheight when the table requires
	 * a WYSIWYG (ckEditor for field content)
	 */
	public function setContentGridParameters(){
		if($this->getField('content')){
			$config = Rhema_SiteConfig::getConfig('grid.nav_options.wysiwyg-tables');
			pd('pele');
		}
	}
	/**
	 * Generates the script to show CRUD buttons on the grid toolbar
	 * This is called in jqGrid class as a temporary fix
	 * not a good practise as updated would overwrite this change
	 * @param unknown_type $tableName
	 * @return string
	 */
	public function getExtendedNavbarConfiguration(){
		//pd($this);
		$gridId           = $this->jqgGetIdTable(); 
		$configOptions    = Rhema_SiteConfig::getConfig('grid.nav_options');
		$tableName        = current($this->getSource()->getMainTable());
		 
		$options         = $configOptions['default'];		
		if($tableName and isset($configOptions[$tableName])){
			$options = array_merge($options, $configOptions[$tableName]);
		}
		$isRootOnly  = Zend_Controller_Front::getInstance()
								->getRequest()
								->getParam(Admin_Model_AdminMenu::TYPE_ROOTS_ONLY);
		 
		$options = array_merge($options, $this->getNavBarOptions());
		if($isRootOnly){
			$options['control']['add']  = true;
			$options['control']['edit'] = true;
		}
		
		$navOptions       = new Rhema_Grid_NavOption($options);
		$params           = $navOptions->getParams($gridId); 
		
		$barConfig = sprintf( "jqGrid('navGrid','#%s', %s )",  
					$this->jqgGetIdPager(),  implode(',' . PHP_EOL ,$params));
		return $barConfig;
	}
	/**
	 * @return the $_navBarOptions
	 */
	public function getNavBarOptions(){
		return $this->_navBarOptions;
	}
	
	/**
	 * @param field_type $_navBarOptions
	 */
	public function setNavBarOptions($_navBarOptions){
		$this->_navBarOptions = $_navBarOptions;
	}
	
	public function addNavBarOptions($_navBarOptions){
		$this->_navBarOptions = array_merge($this->_navBarOptions, $_navBarOptions);
	}	
	
	public function setPostCommands($script){
		$this->_postCommands[] = $script;
	}
	
	public function renderPartData(){
		$model = $this->getSource()->getMainTable();
		if(Rhema_Model_Service::isMenuTable($model['table'])){
			return $this->buildTreeGridData();
		}else{
			return parent::renderPartData();
		}
	}
	public function buildTreeGridData(){
		// clarify the values
		$page = $this->getParam('page'); // get the requested page
		$limit = $this->getResultsPerPage(); // get how many rows we want to have into the grid
		$count = $this->_totalRecords;
		// decide if we should pass PK as ID to each row
		$passPk = false;
		if(isset($this->_bvbParams['id']) && count($this->_result) > 0){
			$pkName = $this->_bvbParams['id'];
			if(isset($this->_result[0][$pkName])){
				// only if that field exists
				$passPk = true;
			}else{
				$this->log("field '$pkName' defined as jqg>reader>id option does not exists in result set", Zend_Log::WARN);
			}
		}
		// build rows
		$data = new stdClass();
		$data->rows = array();
		foreach(parent::_buildGrid() as $i => $row){
			$dataRow = new stdClass();
			// collect data for cells
			$d = array();
			foreach($row as $key => $val){
				$d[] = $val['value'];
			}
			$this->addTreeReaderData($d, $i);
			
			if($passPk){
				// set PK to row
				// TODO works only if _buildGrid() results are in same order as $this->_result
				$dataRow->id = $this->_result[$i][$pkName];
			}
			$dataRow->cell = $d;
			$data->rows[] = $dataRow;
		}
		// set some other information
		if($count > 0){
			$totalPages = ceil($count / $limit);
		}else{
			$totalPages = 0;
		}
		$data->page = $page;
		$data->total = $totalPages;
		$data->records = $count;
		
		return Zend_Json::encode($data);
	}
	
	public function addTreeReaderData(&$arr, $key){		
		$lft = $this->_result[$key]['lft'];
		$rgt = $this->_result[$key]['rgt'];
		$level = $this->_result[$key]['level'];
		$isLeaf = (($rgt - $lft) == 1);
		 
		
		foreach(Rhema_Grid_Adapter_DoctrineModel::$treeReaderList as $item){
			switch($item){
				case 'level' :
					$arr[] = $level;
					break;
				case 'lft' :
					$arr[] = $lft;
					break;
				case 'rgt' :
					$arr[] = $rgt;
					break;
				case 'isLeaf' :
					$arr[] = $isLeaf;
					break;
				
				case 'expand' :
					$arr[] =  true ;//false ;//!$isLeaf  ? true : false;
					break;
			}
		}
	}
	/**
	 * @return the $_leftKey
	 */
	public function getLeftKey(){
		return $this->_leftKey;
	}
	
	/**
	 * @return the $_rightKey
	 */
	public function getRightKey(){
		return $this->_rightKey;
	}
	
	/**
	 * @return the $_levelKey
	 */
	public function getLevelKey(){
		return $this->_levelKey;
	}
	
	/**
	 * @param field_type $_leftKey
	 */
	public function setLeftKey($_leftKey){
		$this->_leftKey = $_leftKey;
	}
	
	/**
	 * @param field_type $_rightKey
	 */
	public function setRightKey($_rightKey){
		$this->_rightKey = $_rightKey;
	}
	
	/**
	 * @param field_type $_levelKey
	 */
	public function setLevelKey($_levelKey){
		$this->_levelKey = $_levelKey;
	}

}