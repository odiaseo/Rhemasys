<?php
/**
 * Class to adapt the Doctrine model to the ZF Grid
 * Functions include column-to-field generation
 * @author Pele
 *
 */
class Rhema_Grid_Adapter_DoctrineModel extends Rhema_Model_Service{
	
	const TYPE_SELECT = 'select';
	const TYPE_TEXT = 'text';
	const TYPE_CHECKBOX = 'checkbox';
	const TYPE_TEXTAREA = 'textarea';
	const TYPE_RADIO = 'radio';
	const TYPE_BUTTON = 'button';
	const TYPE_IMAGE = 'image';
	const TYPE_FILE = 'file';
	const BLANK_STRING = 'null: ---';
	public static $spinElements = array("level","sequence","cols","rows","sort_order","total");
	const QUERY_LIMIT = 15;
	
	private $_modelList = array();
	private $_fieldLabels = array();
	private $_tableTitle = '';
	protected static $_blankSelect = array(
			'null' => '---');
	
	public static $ignoreFields = array(
			'admin_subsite_id', 
			'deleted_at', 
			'root_id', 
			'category_mapping',
			'merchant_mapping',
			'field_mapping',
			//'slug',	
			//'index_status',
			'lft', 
			'rgt', 
			'level', 
			'layout', 
			'version');
	protected $_productFields = array(
		'title', 'deeplink', 'price', 'brand', 'affiliate_product_type_id', 'affiliate_network_id',
		'affiliate_retailer_id', 'affiliate_product_category_id', 'code', 'valid_from', 'valid_to'
	);
	public static $hiddenFields = array( 
				'label',
				'slug',
				'page_header_id',
				'page_footer_id' ,
				'start_at',
			    'end_at',
	            'ip_address',	  
				'recurring_rule',
				'address_book_id',
				'field_mapping',
				'category_mapping',
				'merchant_mapping',
			    'field_mapping',
	);
		
	protected static $_restrictedList = array();
	
	protected static $_bannedList = array(
			'admin_subsite_id', 
			'deleted_at',
			'created_at',
			'update_at',
			//'productid',
			'index_status'
			
	);
	
	public $typeList = array(
			self::TYPE_SELECT => 10, 
			self::TYPE_TEXT => 5, 
			self::TYPE_CHECKBOX => 15, 
			self::TYPE_TEXTAREA => 35, 
			self::TYPE_RADIO => 20, 
			self::TYPE_BUTTON => 40, 
			self::TYPE_IMAGE => 25, 
			self::TYPE_FILE => 30);
	
	public static $treeReaderList = array(
			'level', 
			'lft', 
			'rgt', 
			'isLeaf', 
			'expand');
	/**
	 * Which Zend_Form element types are associated with which doctrine type?
	 * @var array
	 */
	protected $_columnTypes = array(
			'integer' => self::TYPE_TEXT, 
			'decimal' => self::TYPE_TEXT, 
			'float' => self::TYPE_TEXT, 
			'string' => self::TYPE_TEXT, 
			'varchar' => self::TYPE_TEXT, 
			'boolean' => self::TYPE_CHECKBOX, 
			'timestamp' => self::TYPE_TEXT, 
			'time' => self::TYPE_TEXT, 
	        'datetime' => self::TYPE_TEXT, 
			'date' => self::TYPE_TEXT, 
			'enum' => self::TYPE_SELECT);
	/**
	 * Default validators for doctrine column types
	 * @var array
	 */
	protected $_columnValidators = array(
		//	'integer' => 'integer', 
			'float' => 'number', 
			'double' => 'number');
	
	protected $_fieldValidators = array(
			'email' => 'email', 
			'dob_at' => 'date');
	protected $_fieldTypes = array(
		'index_status' =>self::TYPE_SELECT 
	);
	/**
	 * @return the $_bannedList
	 */
	public static function getBannedList() {
		return Rhema_Grid_Adapter_DoctrineModel::$_bannedList;
	}

	/**
	 * @param field_type $_bannedList
	 */
	public static function setBannedList($_bannedList) {
		Rhema_Grid_Adapter_DoctrineModel::$_bannedList = $_bannedList;
	}

	public function __construct($options = array()){
		if(isset($options['model'])){
			$filter = new Rhema_Filter_FormatModelName();
			$this->_modelClass = $filter->filter($options['model']);
			$this->setTable($this->_modelClass);
			
			if(Zend_Registry::isRegistered(Rhema_Constant::DATA_DICTIONARY)){
				$labels = Zend_Registry::get(Rhema_Constant::DATA_DICTIONARY);
			}else{
				$dictionary = Rhema_Model_Service::factory('admin_dictionary');
				$adminTable = Rhema_Model_Service::factory('admin_table');
				
				$labels = $dictionary->getFieldlabels();
				$tableNames = $adminTable->getTableTitles();
				$data = array_merge($labels, $tableNames);
				
				Zend_Registry::set(Rhema_Constant::DATA_DICTIONARY, $data);
			}
			$this->_fieldLabels = $labels;
		}
	}
	
	public function getColumnModel(){
		$this->_columnsToFields()->_manyRelationsToFields();
		return $this->getModelList();
	}
	
	public static function getSpinElementRegex(){
		return '/(' . implode('|', self::$spinElements) . ')$/i';
	} 	
	/**
	 * Maps doctrine model column types to html elements based on
	 * datatype and preset criteria depending on relationships
	 * @return Rhema_Grid_Adapter_DoctrineModel
	 */
	

	
	protected function _columnsToFields(){
		$modelList 		= array();
		$preload 		= Rhema_SiteConfig::getConfig('settings.preload_grid_select');
		$view 			= Zend_Layout::getMvcInstance()->getView();
		$tableName 		= $this->getTable()->getTableName();
		$this->_tableTitle  = isset($this->_fieldLabels[$tableName]) ? $this->_fieldLabels[$tableName] : $tableName;
		$isTreeModel 		= Rhema_Model_Service::isMenuTable($this->_model);
		$spinRegex   		=  self::getSpinElementRegex();
		
		foreach($this->getColumns() as $name => $definition){
			$columnModel = new Rhema_Grid_Column_Model();
			$name        = trim($name);
			$columnModel->name = $name;
			$columnModel->index = $name;
			$columnModel->edittype = self::TYPE_TEXT;
			
			if(isset($this->_fieldTypes[$name])){
				$columnModel->edittype = $this->_fieldTypes[$name];
			}else if($definition['foreignKey'] and ! $definition['primary']){
				$columnModel->edittype = self::TYPE_SELECT;
			}elseif($definition['type']){			
				if(isset($this->_columnTypes[$definition['type']]))	{
					$columnModel->edittype = $this->_columnTypes[$definition['type']];
				}elseif(!preg_match('/(image|thumb)/i', $name) and isset($definition['length']) and ($definition['length'] > 150 or !$definition['length'])){
					$columnModel->edittype = self::TYPE_TEXTAREA ;
				} 
			}
			
			if(isset($this->_fieldLabels[$name])){
				$columnModel->label = $this->_fieldLabels[$name];
			}else{
				$columnModel->label = $name;
			}
			
			if(isset($this->_columnValidators[$definition['type']])){
				$validator = $this->_columnValidators[$definition['type']];
				$columnModel->editrules->$validator = true;
			}
			
			if(isset($this->_fieldValidators[$name])){
				$validator = $this->_fieldValidators[$name];
				$columnModel->editrules->$validator = true;
			}
			
			if((isset($definition['notnull']) && $definition['notnull'] == true) and $name != 'id'){
				$columnModel->editrules->required = true;
				$columnModel->formoptions->elmprefix = '<span class="field-reg">*</span>';
			}else{
				$columnModel->editrules->required = false;
				$columnModel->formoptions->elmprefix = '<span class="field-notreg">&nbsp;</span>';
			}
			
			if($definition['type'] == 'integer' and $definition['length'] == 1 and substr($name, 0, 3) == 'is_'){
				$options = array( 'No',  'Yes');
				$string = $this->_convertArrayOptionsToString($options);
				$columnModel->stype = self::TYPE_SELECT;
				$columnModel->edittype = self::TYPE_CHECKBOX;
				$columnModel->editoptions->value = "1:0";
				$columnModel->searchoptions->value = $string;
			}
			
			if($definition['default']){
				$columnModel->editoptions->defaultValue = $definition['default'];
			}
			
			if($this->_isIgnoredField($name, $definition, $tableName)){
				$columnModel->hidden = true;
				$columnModel->editrules->edithidden = false;
				$columnModel->editrules->required = false;
			}elseif(array_search($name, self::$hiddenFields) !== false){
				$columnModel->hidden = true;
				$columnModel->editrules->edithidden = true;				
			}
			
			if($columnModel->edittype == self::TYPE_SELECT && $definition['type'] == 'enum'){
				$enumOptions = array();
				foreach($definition['values'] as $text){
					$enumOptions[$text] = $text;
				}
				$columnModel->stype = self::TYPE_SELECT;
				$columnModel->editoptions->value = (object) $enumOptions;
				$columnModel->searchoptions->value = (object) $enumOptions;
			}else if($definition['foreignKey'] and $columnModel->edittype == self::TYPE_SELECT){
				$columnModel->stype = self::TYPE_SELECT;
				$columnModel->edittype = self::TYPE_SELECT;
				$mod = $definition['class'];
				if($preload and $mod != MODEL_PREFIX . 'AffiliateRetailer'){
					$options = array();
					$filter = new Rhema_Dao_Filter();
					$filter->setModel($mod)
						   ->setParentTable($this->getModel())
						   ->setHydrationMode(Doctrine_Core::HYDRATE_RECORD)	;
						   
					$records = Rhema_Model_Service::createEditOptionQuery($filter)->execute();

					if($records->count()){
						foreach($records as $record){
							$recordKey = $this->getRecordIdentifier($record);
							$options[$recordKey] = (string) $record;
						}
						asort($options);
					}
					$string = $this->_convertArrayOptionsToString($options); // self::BLANK_STRING . ';' . implode(';', $options);
					$columnModel->editoptions->value = $string;
					$columnModel->searchoptions->value = $string;
				}else{
					$url = $view->url(array('table'     => $tableName,
							                'gridmodel' => $definition['class']), 'grid-select-option');
					$columnModel->editoptions->dataUrl = $url;
					$columnModel->searchoptions->dataUrl = $url;
				}
				
				if(isset($definition['notnull']) and $definition['notnull']){
					$columnModel->editrules->integer= true;
				}
			}else if(preg_match('/(m_module|m_controller|m_action|widget)/i', $name)){
				$columnModel->stype = self::TYPE_SELECT;
				$columnModel->edittype = self::TYPE_SELECT;
				$columnModel->hidden = true;
				$columnModel->editrules->edithidden = true;
				
				if($name == Rhema_Constant::MENU_MODULE){
					//if($preload){
						$arr = Rhema_Util::getDir('/modules', true, false, false);
						$string = $this->_convertArrayOptionsToString($arr);
						
						$columnModel->editoptions->value = $string;
						$columnModel->searchoptions->value = $string;
					/*}else{
						$url = $view->url(array(
								'm' => $name, 
								'd' => ($name == Rhema_Constant::MENU_MODULE) ? true : false, 
								'c' => '',
								'a' => false), 'directory-listing-ajax');
						$columnModel->editoptions->dataUrl = $url;
						$columnModel->searchoptions->dataUrl = $url;
					}*/
				}elseif($name == Rhema_Constant::MENU_ACTION and preg_match('/(_menu)$/i', $this->_model) ){
					$columnModel->edittype = self::TYPE_TEXT;
				}elseif($name == 'widget'){
					$url = $view->url(array(
								'name' => $name, 
								'action' => 'widget'), 'ajax-route');
						$columnModel->editoptions->dataUrl = $url;
						$columnModel->searchoptions->dataUrl = $url;
						$columnModel->searchoptions->searchhiddem = true;	
						$columnModel->hidden = false;												 
			 
				}else{
					$columnModel->editoptions->value = self::BLANK_STRING;
				}
			}elseif(preg_match('/_at$/i', $name)){
				switch($name){
					case 'created_at' :
					case 'deleted_at' :
					case 'updated_at' :
						$columnModel->hidden = true;
						$columnModel->editable = false;
						break;
					default :
				}
			
			}elseif(preg_match('/(comment|content|note|description|keyword|params)/i', $name)){
				$columnModel->edittype = self::TYPE_TEXTAREA;
				$columnModel->hidden = true;
				$columnModel->editrules->edithidden = true;
			}elseif(preg_match($spinRegex, $name)){
				$columnModel->edittype = self::TYPE_TEXT;
				$columnModel->stype    = self::TYPE_SELECT;
				$options               = array();
				for($i = 1; $i <= 40; $i ++){
					$options[$i] = $i;
				}
				
				$string = $this->_convertArrayOptionsToString($options);
				$columnModel->searchoptions->value = $string;
			}elseif(substr($name, - 3) == '_by'){
				$columnModel->editable = false;
				$columnModel->hidden = true;
			}
			
			if($definition['primary']){
			//	$columnModel->editable = false;
				$columnModel->width = '80px';
				$columnModel->editrules->required = false; 
			}
			
			$modelList[$columnModel->edittype][] = $columnModel;
		}
		
		$flipList = array_flip($this->typeList);
		ksort($flipList, SORT_NUMERIC);
		
		$rowpos = 3;
		$colpos = 1;
		foreach($flipList as $type){
			if(isset($modelList[$type])){
				foreach($modelList[$type] as $model){
					if($model->name == 'id'){
						$model->formoptions->rowpos = 1;
					}elseif($model->name == 'title'){
						$model->formoptions->colpos = $colpos;
						$model->formoptions->rowpos = 2;
					}else{
						$model->formoptions->colpos = $colpos;
						$model->formoptions->rowpos = $rowpos;
						$rowpos ++;
					}
					$this->_modelList[] = $model;
				}
			}
		}
		//$this->_addTreeReaderFields();
		

		return $this;
	}
	
	protected function _addTreeReaderFields(){
		if(Rhema_Model_Service::isMenuTable($this->_model)){
			$count = count($this->_modelList);
			foreach(self::$treeReaderList as $field => $index){
				$node = new Rhema_Grid_Column_Model();
				$node->name = $field;
				$node->index = $field;
				$this->_modelList[] = $node;
				self::$treeReaderList[$field] = $count ++;
			}
		}
	}
	protected function _manyRelationsToFields(){
		$modelToField = new Rhema_Filter_FormatModelName();
		foreach($this->getManyRelations() as $alias => $relation){
			if($this->_isIgnoredField($alias, $relation)){
				continue;
			}
			$columnId = $modelToField->reverse($relation['model']);
			$columnModel = new Rhema_Grid_Column_Model();
			$columnModel->index = $columnId;
			$columnModel->name = $columnId;
			
			if(isset($this->_fieldLabels[$alias])){
				$columnModel->label = $this->_fieldLabels[$alias];
			}else{
				$columnModel->label = $relation['model'];
			}
			
			$options = self::$_blankSelect;
			foreach($this->getAllRecords($relation['model']) as $record){
				$recordValue = (string) $record;
				$options[$this->getRecordIdentifier($record)] = trim($recordValue);
			}
			$columnModel->editoptions->value = (object) $options;
		
		//TODO rms see how to implement  $this->addElement($columnModel);
		}
		return $this;
	}
	
	protected function _isIgnoredField($name, $definition, $table = ''){
		if(in_array($name, self::$ignoreFields)){
			return true;
		}else if(isset($definition['type']) && ! isset($this->_columnTypes[$definition['type']])){
			return true;
		}elseif($table == 'affiliate_product' and !in_array($name, $this->_productFields)){
			return true;
		}
		
		return false;
	}
	
	protected function _setFilters(Doctrine_Query $query, Rhema_Dao_Filter $filters = null){
		return $query;
	}
	/**
	 * @return the $_modelList
	 */
	public function getModelList(){
		return $this->_modelList;
	}
	
	/**
	 * @param field_type $_modelList
	 */
	public function setModelList($_modelList){
		$this->_modelList = $_modelList;
	}
	
	public function addElement($model){
		array_push($this->_modelList, $model);
	}
	
	public static function buildGridSelectOption($model){
		$options = array();
		foreach($this->getAllRecords($model) as $record){
			$options[$this->getRecordIdentifier($record)] = (string) $record;
		}
		$select = new Zend_Form_Element_Select('sample', array(
				'multiOptions' => $options, 
				'decorators' => array(
						'Viewhelper')));
		
		return $select;
	}
	/**
	 * @return the $_tableTitle
	 */
	public function getTableTitle(){
		return $this->_tableTitle;
	}
	
	protected function _convertArrayOptionsToString(array $list = null){
		$options = array();
		if(count($list)){
			foreach($list as $key => $value){
				$options[] = $key . ':' . trim($value);
			}
			
			$string = self::BLANK_STRING . ';' . implode(';', $options);
		}else{
			$string = self::BLANK_STRING ;
		}
		return $string;
	}
	/**
	 * Return all columns as an array
	 *
	 * Array must contain 'type' for column type, 'notnull' true/false
	 * for the column's nullability, and 'values' for enum values, 'primary'
	 * true/false for primary key. Key = column's name
	 *
	 * @return array
	 */
	public function getColumns($model = null){
		$foreignKeyColumns = array();
		$table = ($model) ? Doctrine_Core::getTable($model) : $this->_table;
		foreach($table->getRelations() as $alias => $relation){
			$localColumn = strtolower($relation['local']);
			$foreignKeyColumns[$localColumn] = $relation['class'];
		}
		
		$data = $table->getColumns();
		$cols = array();
		foreach($data as $name => $def){
			$isPrimary = (isset($def['primary'])) ? $def['primary'] : false;
			$isForeignKey = isset($foreignKeyColumns[strtolower($name)]);
			$length = isset($def['length']) ? (int) $def['length'] : 0;
			
			$columnName = $table->getColumnName($name);
			$fieldName = $table->getFieldName($columnName);
			
			$cols[$fieldName] = array(
					'type' => $def['type'], 
					'notnull' => (isset($def['notnull'])) ? $def['notnull'] : false, 
					'values' => (isset($def['values'])) ? $def['values'] : array(), 
					'primary' => $isPrimary, 
					'foreignKey' => $isForeignKey, 
					'class' => ($isForeignKey) ? $foreignKeyColumns[strtolower($name)] : null, 
					'default' => isset($def['default']) ? $def['default'] : null,
					'length' => $length);
		}
		
		return $cols;
	}
	/**
	 * @return the $_blankSelect
	 */
	public static function getBlankSelect(){
		return Rhema_Grid_Adapter_DoctrineModel::$_blankSelect;
	}
	
	/**
	 * @param field_type $_blankSelect
	 */
	public static function setBlankSelect($_blankSelect){
		Rhema_Grid_Adapter_DoctrineModel::$_blankSelect = $_blankSelect;
	}
	/**
	 * @return the $_treeReaderList
	 */
	public function getTreeReaderList(){
		return $this->_treeReaderList;
	}
	
	/**
	 * @param field_type $_treeReaderList
	 */
	public function setTreeReaderList($_treeReaderList){
		$this->_treeReaderList = $_treeReaderList;
	}

}
