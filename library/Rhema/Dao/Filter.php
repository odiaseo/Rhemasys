<?php
class Rhema_Dao_Filter{
	
	const ORDER_DESC = 'desc';
	const ORDER_ASC = 'asc';
	const INNER_JOIN = 'innerJoin';
	const LEFT_JOIN = 'leftJoin';
	const ENTITY_ALIAS = 'a';
	const QUERY_LIMIT = 25 ;//null;
	const WITH_PLACEHOLDERS = 'WITH_PLACEHOLDERS';
	
	
	const OP_AND = 'and';
	const OP_IN = 'in';
	const OP_NOT_IN = 'not_in';
	const OP_OR = 'or';
	const OP_EQ = '=';
	const OP_NE = '<>';
	const OP_GT = '>';
	const OP_GTE = '>=';
	const OP_LT = '<';
	const OP_LTE = '<=';
	const OP_LIKE = 'LIKE';
	const OP_LLIKE = 'LLIKE';
	const OP_RLIKE = 'RLIKE';
	const OP_LRLIKE = 'LRLIKE';
	const OP_NOT_LIKE = 'NOT LIKE';
	const OP_IS_NULL = 'IS NULL';
	const OP_NOT_NULL = 'IS NOT NULL'; 
	const OP_RAW_SQL = 'RAW_SQL'; // (sql, nameparameters, operator)
	
	const QUERY_TYPE_CREATE = 'QUERY_TYPE_CREATE';
	const QUERY_TYPE_SELECT = 'QUERY_TYPE_SELECT';
	const QUERY_TYPE_UPDATE = 'QUERY_TYPE_UPDATE';
	const QUERY_TYPE_DELETE = 'QUERY_TYPE_DELETE';
	
	/**
	 * Stores join lists
	 * @var unknown_type
	 */
	protected $_joinList = array();
	protected $_groupByList = array();
	protected $_orderByList = array();
	protected $_fields = array();
	protected $_aggregateFieldList = array();
	protected $_conditions = array();
	protected $_limit = null;
	protected $_page = 1;
	protected $_attributes = array();
	protected $_indexBy = 'id';
	protected $_hydrationMode = Doctrine_Core::HYDRATE_ARRAY;
	protected $_model = null;
	protected $_updateList = array();
	protected $_selectKey  = 'id';
	protected $_selectDisplayField = 'title';
	protected $_parentTable ;
	protected $_offset = null ;
	protected $_unique = false;
	protected $_queryParams = array();
	
	private $_debug = false;
	private $_bypassSoftDelete = false;
	private $_queryType = self::QUERY_TYPE_SELECT;
	
	/**
	 * @return the $_unique
	 */
	public function getUnique() {
		return $this->_unique;
	}

	/**
	 * @param field_type $_unique
	 */
	public function setUnique($_unique = true) {
		$this->_unique = $_unique;
		return $this;
	}

	public function __construct($limit = null, $page = 1){
		if($limit){
			$this->setLimit($limit); 
		}
		
		if($page){
			$this->setPage($page); 
		}
	}
	 
	/**
	 * @return the $_joinList
	 */
	public function getJoinList(){ 
		return $this->_joinList;
	}
	
	/**
	 * @return the $_groupByList
	 */
	public function getGroupByList(){
		return $this->_groupByList;
	}
	
	/**
	 * @return the $_orderByList
	 */
	public function getOrderByList(){
		return $this->_orderByList;
	}
	/**
	 * @return the $_bypassSoftDelete
	 */
	public function getBypassSoftDelete(){
		return $this->_bypassSoftDelete;
	}

	/**
	 * @param field_type $_bypassSoftDelete
	 */
	public function setBypassSoftDelete($_bypassSoftDelete){
		$this->_bypassSoftDelete = $_bypassSoftDelete;
		return $this;
	}	
	/**
	 * @return the $_fields
	 */
	public function getFields(){
		return $this->_fields;
	}
	
	/**
	 * @return the $_conditions
	 */
	public function getConditions(){
		return $this->_conditions;
	}
	
	/**
	 * @param field_type $_joinList
	 */
	public function addJoin($model, $joinType = self::INNER_JOIN, $columns = array()){
		$this->_joinList[$model] = array('type' => $joinType, 'columns' => $columns);
		return $this;
	}
	
	public function removeJoin($model){
		unset($this->_joinList[$model]);
		return $this;
	}
	/**
	 * @param field_type $_orderByList
	 */
	public function setOrderBy($field, $order = self::ORDER_ASC){
		$this->_orderByList = array();
		$this->addOrderBy($field, $order);
		return $this;
	}
	
	public function addOrderBy($field, $order = self::ORDER_ASC){
		$this->_orderByList[$field] = $order;
		return $this;
	}
	/**
	 * @param field_type $_fields
	 */
	public function setFields($_fields){
		$this->_fields = array();
		$this->addFields($_fields);
		return $this;
	}
	
	public function addFields($_fields){
		$this->_fields = array_merge($this->_fields, (array) $_fields);
		return $this;
	}
	
	public function addField($_field){
		if(is_array($_field)){
			$this->addFields($_fields);
		}else{
			$this->_fields[] = $_field;
		}
		return $this;
	}
	
	public function removeField($field){
		$key = array_search($field, $this->_fields);
		if($key !== false){
			unset($this->_fields[$key]);
		}
		return $this;
	}
	public function addClause(array $clause = null){
		foreach($clause as $grpOp => $data){
			if(count($data) > 2){					
				list($field, $value, $operator) = $data;
			}else{
				list($field, $value) = $data;
				$operator = self::OP_AND ;
			}
			$this->addCondition($field, $value, $operator);			  
		}
	}
	
	public function addCondition($field, $value, $operator = self::OP_EQ, $grpOp = self::OP_AND){
		$this->_conditions[$grpOp][] = new Rhema_Dao_FilterCondition($field, $value, $operator);
		return $this;
	}
	public function setCondition($field, $value, $operator = null, $grpOp = self::OP_AND){
		$this->_conditions = array();
		$this->addCondition($field, $value, $operator, $grpOp);
		return $this;
	}
	/**
	 * @param field_type $_orderByList
	 */
	public function addGroupBy($field){  
		$this->_groupByList[] = $field;
		return $this;
	}
	public function setGroupBy($field){
		$this->_groupByList = array(); 
		$this->addGroupBy($field);
		return $this;
	}
	/**
	 * @return the $_limit
	 */
	public function getLimit(){
		return $this->_limit;
	}
	
	/**
	 * @return the $_page
	 */
	public function getPage(){
		return $this->_page;
	}
	
	public function getOffset(){
		return ($this->getPage() - 1) * $this->getLimit();
	}
	/**
	 * @param field_type $_limit
	 */
	public function setLimit($_limit){
		$this->_limit = $_limit;
		return $this;
	}
	
	/**
	 * @param field_type $_page
	 */
	public function setPage($_page){
		$this->_page = $_page;
		return $this;
	}
	/**
	 * @return the $_attributes
	 */
	public function getAttributes(){
		return $this->_attributes;
	}
	
	/**
	 * @param field_type $_attributes
	 */
	public function setAttributes($_attributes){
		$this->_attributes = (array) $_attributes;
		return $this;
	}
	
	public function addAttribute($field){
		$fields = (array) $field;
		foreach($fields as $f){
			$this->_attributes[] = $f;
		}
		return $this;
	}
	/**
	 * @return the $_indexBy
	 */
	public function getIndexBy(){
		return $this->_indexBy;
	}
	
	/**
	 * @return the $_debug
	 */
	public function getDebug(){
		return $this->_debug;
	}
	
	/**
	 * @param field_type $_indexBy
	 */
	public function setIndexBy($_indexBy){
		$this->_indexBy = $_indexBy;
		return $this;
	}
	
	/**
	 * @param field_type $_debug
	 */
	public function setDebug($_debug = true){
		$this->_debug = $_debug;
		return $this;
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
		$this->_model = $_model;
		return $this;
	}
	/**
	 * @return the $_hydrationMode
	 */
	public function getHydrationMode(){
		return $this->_hydrationMode;
	}
	
	/**
	 * @param field_type $_hydrationMode
	 */
	public function setHydrationMode($_hydrationMode){
		$this->_hydrationMode = $_hydrationMode;
		return $this;
	}
	
	/**
	 * Adds search criteria to query object
	 * @columns array Database table columns 
	 * @return array
	 */
	
	public function setSearchCriteria(array $options = null, $columns = array()){
		if(empty($this->_conditions) and $options){
			$jqgPrm = new Rhema_Grid_JqgParam();
			$prmNames = $jqgPrm->getPrmNames();
			
			$request = Zend_Controller_Front::getInstance()->getRequest(); // new Zend_Controller_Request_Http(); 
			$request->setParams($options);
			$root_id = $request->getParam('root_id', null);
			$doSearch = $request->getParam($prmNames[Rhema_Grid_JqgParam::SEARCH_ID], 'false');
			$multiSearch = $request->getParam('filters', null);
			$searchField = $request->getParam('searchField');
			$ophans = $request->getParam(Admin_Model_AdminMenu::TYPE_OPHANS, false);
			$rootsOnly = $request->getParam(Admin_Model_AdminMenu::TYPE_ROOTS_ONLY , false);
			
			$userRole = Zend_Registry::isRegistered(Rhema_Constant::USER_ROLE_KEY) ? Zend_Registry::get(Rhema_Constant::USER_ROLE_KEY) : false;
			$isAdmin = ($userRole == Admin_Model_Role::ROLE_SUPER);
			$conditionArray = array();
			
			//Doctrine_Core::getTable($this->_model)->isTree() 
			if(Doctrine_Core::getTable($this->_model)->isTree()){ // and $doSearch == 'false'
				if($root_id){
					$conditionArray[self::OP_AND][] = $this->getConditionObject('root_id', $root_id, Rhema_Grid_Column_SearchOption::EQUAL);
					if(! $isAdmin){
						$conditionArray[self::OP_AND][] = $this->getConditionObject('level', 0, Rhema_Grid_Column_SearchOption::NOT_EQUAL);
					}
				}elseif($ophans){
					//searchiing for ophan menus i.e. menus without a parent root
					$ops = Rhema_Grid_Column_SearchOption::IS_NULL;
					$conditionArray[self::OP_AND][] = $this->getConditionObject('root_id', '', $ops);
					$conditionArray[self::OP_AND][] = $this->getConditionObject('level', '', $ops);
				
				}elseif($rootsOnly){
					$ops = Rhema_Grid_Column_SearchOption::NOT_NULL;
					$conditionArray[self::OP_AND][] = $this->getConditionObject('root_id', '', $ops);
					$conditionArray[self::OP_AND][] = $this->getConditionObject('level', 0, Rhema_Grid_Column_SearchOption::EQUAL);
				}else{
					$conditionArray[self::OP_AND][] = $this->getConditionObject('level', 0, Rhema_Grid_Column_SearchOption::EQUAL);
				
				}
			}
			
			if('AdminTable' == $this->_model and ! $isAdmin){
				$conditionArray[self::OP_AND][] = $this->getConditionObject('is_hidden', 0, Rhema_Grid_Column_SearchOption::EQUAL);
			}
			
			if($doSearch == 'true'){
				if($multiSearch){
					$multiSearch = urldecode($multiSearch);
					$filters = Zend_Json::decode($multiSearch);
					
					if(isset($filters['groupOp'])){
						$grpOp = strtolower($filters['groupOp']);
						foreach($filters['rules'] as $data){
							$conditionArray[$grpOp][] = $this->getConditionObject($data['field'], $data['data'], $data['op']);
						}
					}else{
						foreach($columns as $field){
							if(isset($filters[$field])){
								$conditionArray[self::OP_AND][] = $this->getConditionObject($field, $filters[$field], Rhema_Grid_Column_SearchOption::CONTAINS);
							}
						}
					}
				
				}elseif($searchField){
					$operator = $request->getParam('searchOper');
					$searchString = $request->getParam('searchString');
					$args = array_filter(explode(',', $searchString));
					
					for($i = 0; $i < count($args); $i ++){
						$conditionArray[self::OP_AND][] = $this->getConditionObject($searchField, $args[$i], $operator);
					}
				}else{ // doing toolbar search					 
				/*					foreach($request->getParams() as $field => $data){
						$data = trim($data);
						if(array_search($field, $columns) !== false and 'null' != $data){
							if(preg_match('/^is_/i', $field)){
								$operator  = Rhema_Grid_Column_SearchOption::EQUAL ; 
							}else{
								$operator = Rhema_Grid_Column_SearchOption::CONTAINS ;
							}
						//	$conditionArray[self::OP_AND][] = $this->getConditionObject($field, $data, $operator);  
						}
					}*/
				}
			}
			
			$this->_conditions = $conditionArray;
		}
		
		return $this;
	}
	
	public static function getConditionObject($field, $string, $oper, $type = self::OP_AND){
		switch($oper){
			case Rhema_Grid_Column_SearchOption::NOT_NULL :
				$operator = self::OP_NOT_NULL;
				break;
			case Rhema_Grid_Column_SearchOption::IS_NULL :
				$operator = self::OP_IS_NULL;
				break;
			case Rhema_Grid_Column_SearchOption::NOT_EQUAL :
				$operator = self::OP_NE;
				break;
			case Rhema_Grid_Column_SearchOption::LESS_THAN :
				$operator = self::OP_LT;
				break;
			case Rhema_Grid_Column_SearchOption::LESS_OR_EQUAL :
				$operator = self::OP_LTE;
				break;
			case Rhema_Grid_Column_SearchOption::GREATER_THAN :
				$operator = self::OP_GT;
				break;
			case Rhema_Grid_Column_SearchOption::GREATER_OR_EQUAL :
				$operator = self::OP_GTE;
				break;
			case Rhema_Grid_Column_SearchOption::BEGINS_WITH :
				$operator = self::OP_LIKE;
				$string = "$string%";
				break;
			case Rhema_Grid_Column_SearchOption::ENDS_WITH :
				$operator = self::OP_LIKE;
				$string = "%$string";
				break;
			case Rhema_Grid_Column_SearchOption::CONTAINS :
			case Rhema_Grid_Column_SearchOption::IS_IN :
				$operator = self::OP_LIKE;
				$string = "%$string%";
				break;
			case Rhema_Grid_Column_SearchOption::NOT_CONTAIN :
			case Rhema_Grid_Column_SearchOption::NOT_IN :
				$operator = self::OP_NOT_LIKE;
				$string = "%$string%";
				break;
			case Rhema_Grid_Column_SearchOption::NOT_BEGIN_WITH :
				$operator = self::OP_NOT_LIKE;
				$string = "$string%";
				break;
			case Rhema_Grid_Column_SearchOption::NOT_END_WITH :
				$operator = self::OP_NOT_LIKE;
				$string = "%$string";
				break;
			case Rhema_Grid_Column_SearchOption::EQUAL :
			default :
				$operator = self::OP_EQ;
				break;
		}
		
		$condition = new Rhema_Dao_FilterCondition($field, $string, $operator);
		
		return $condition;
	}
	/**
	 * @return the $_queryType
	 */
	public function getQueryType(){
		return $this->_queryType;
	}
	
	/**
	 * @param field_type $_queryType
	 */
	public function setQueryType($_queryType){
		$this->_queryType = $_queryType;
		return $this;
	}
	/**
	 * @return the $_updateList
	 */
	public function getUpdateList(){
		return $this->_updateList;
	}
	
	/**
	 * @param field_type $_updateList
	 */
	public function setUpdateList(array $_updateList = null){
		$this->_updateList = $_updateList;
		return $this;
	}
	
	public function addToUpdateList(array $toAdd = null){
		foreach($toAdd as $field => $value){
			$this->_updateList[$field] = $value;
		}
		return $this;
	}
	/**
	 * @return the $_selectKey
	 */
	public function getSelectKey(){
		return $this->_selectKey;
	}

	/**
	 * @return the $_selectDisplayField
	 */
	public function getSelectDisplayField(){
		return $this->_selectDisplayField;
	}

	/**
	 * @param field_type $_selectKey
	 */
	public function setSelectKey($_selectKey){
		$this->_selectKey = $_selectKey;
		return $this;
	}

	/**
	 * @param field_type $_selectDisplayField
	 */
	public function setSelectDisplayField($_selectDisplayField){
		$this->_selectDisplayField = $_selectDisplayField;
		return $this;
	}
	/**
	 * @return the $_parentTable
	 */
	public function getParentTable(){
		return $this->_parentTable;
	}

	/**
	 * @param field_type $_parentTable
	 */
	public function setParentTable($_parentTable){
		$this->_parentTable = $_parentTable;
		return $this;
	}
	/**
	 * @param field_type $_offset
	 */
	public function setOffset($_offset){
		$this->_offset = $_offset;
		return $this;
	}
	/**
	 * @return the $_aggregateFieldList
	 */
	public function getAggregateFieldList(){
		return $this->_aggregateFieldList;
	}

	/**
	 * @param field_type $_aggregateFieldList
	 */
	public function addAggregateFieldList($field, $function = 'SUM'){
		$function = strtoupper($function);
		$this->_aggregateFieldList[$function][] = $field;
		return $this;
	}

	/**
	 * @param field_type $_aggregateFieldList
	 */
	public function setAggregateFieldList($field, $function){
		$this->_aggregateFieldList = array();
		$this->addAggregateFieldList($field, $function); 
		return $this;
	}
	/**
	 * @return the $_queryParams
	 */
	public function getQueryParams() {
		return $this->_queryParams;
	}
	
	public function addQueryParam($placeholder, $value) {
		return $this->_queryParams[$placeholder] = $value;
	}
	
	/**
	 * @param field_type $_queryParams
	 */
	public function setQueryParams($_queryParams) {
		$this->_queryParams = array();
		$this->_queryParams = $_queryParams;
	}




}