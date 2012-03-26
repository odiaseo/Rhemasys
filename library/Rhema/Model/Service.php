<?php
class Rhema_Model_Service extends ZFDoctrine_Form_Model_Adapter_Doctrine{
	
	const SUBSITE_KEY = 'admin_subsite_id';
	
	protected static $_instance;
	protected static $_cacheInstance;
	private static $_cache ;
	
	protected $_ignoreFields = array();
	protected $_modelNameFilter;
	
	private function __clone(){
	}
	
	public static function getInstance(){
		if(null === self::$_instance){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public static function getCacheInstance($type = 'class-file'){
		if(null === self::$_cacheInstance){ 
			$util = Rhema_Util::getInstance();
			$type = extension_loaded('memcache') ? Rhema_Cache::CACHE_CLASS_MEMCACHED : $type ;
			self::$_cacheInstance = new Rhema_Cache(new self(), $util->getCacheOptions($type)); 
		}
		return self::$_cacheInstance;
	}	
	/**
	 * Returns an instance of the doctrine model. If cache is enabled in the config file
	 * an instance of the cache object is returned instead
	 * when caching is on, all function calls would be proxied through the cache
	 * @param unknown_type $entity
	 * @throws Zend_Controller_Action_Exception
	 * @return Rhema_Cache|Ambiguous
	 */
	public static function factory($entity, $dbModel = true, $useCache = null ){
		
		if($dbModel){
			$filter = new Rhema_Filter_FormatModelName();
			$model = $filter->filter($entity);			
		}else{
			$model = $entity;
		}
		
		try{
			$object = new $model();
		}catch(Exception $e){
			throw new Zend_Controller_Action_Exception($e->getMessage());
		}
 
		if($useCache === false or NO_CACHE){
			return $object;
		}else{
			$util  = Rhema_Util::getInstance();
			$type  = extension_loaded('memcache') ? Rhema_Cache::CACHE_CLASS_MEMCACHED : Rhema_Cache::CACHE_CLASS_FILE ;
			$cache = new Rhema_Cache($object, $util->getCacheOptions($type), $model);
			$cache->setModelName($model); 
			return $cache;
		} 
	}
	
	public static function isMenuTable($model){
		$grid = Zend_Controller_Front::getInstance()->getRequest()->getParam('treeGrid', false);
		if(MODEL_PREFIX . 'AffiliateProductCategory' == $model and $grid){
			return true;
		}
		return (substr(strtolower($model), - 4) == 'menu') and $grid;
	
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
	}
	
	public function getTable(){
		if(! $this->_table){
			$this->_table = Doctrine_Core::getTable($this->getModel());
		}
		return $this->_table;
	}
	
	/**
	 * Parsing the DAO filter and created the DQL object to query the database
	 * @param Rhema_Dao_Filter $filter
	 * @param unknown_type $addRelations
	 * @throws Doctrine_Query_Exception
	 * @return Doctrine_Query_Abstract
	 */
	public static function createQuery(Rhema_Dao_Filter $filter, $addRelations = false){
		$me = new self();
		$alias = Rhema_Dao_Filter::ENTITY_ALIAS;
		$cnt = ord($alias);
		
		$model = $filter->getModel();
		$queryType = $filter->getQueryType();
		$updateList = $filter->getUpdateList();
		
		if($model and $queryType){
			$manager = Doctrine_Manager::getInstance(); 
			$useQueryCache = $manager->getAttribute(Doctrine_Core::ATTR_QUERY_CACHE) ? true : false;
			if(! $me->_table){
				$me->setTable($model);
			}
			try{
				if($queryType == Rhema_Dao_Filter::QUERY_TYPE_CREATE){
					$row = new $model();
					//$row->state('TDIRTY');
					$ignoreList = array_flip(Rhema_Grid_Adapter_DoctrineModel::$ignoreFields);
					
					foreach($row as $col => $val){
						if(isset($ignoreList[$col]) or 'id' == $col){
							continue;
						}elseif(isset($updateList[$col])){
							$row->$col = self::processColumnData($col, $updateList);
						}
					}
					return $row;
				}else{
					$indexField = $filter->getIndexBy();
					$indexString = $alias . '.' . $indexField;
					$limit = $filter->getLimit();
					
					if($queryType == Rhema_Dao_Filter::QUERY_TYPE_DELETE){
						$query = Doctrine_Query::create()
								     ->delete("$model $alias")
								     ->useQueryCache($useQueryCache);
					}elseif($queryType == Rhema_Dao_Filter::QUERY_TYPE_UPDATE){
					  
						$query = Doctrine_Query::create()
										->update("$model $alias") 
										->useQueryCache($useQueryCache);
				 
						foreach($me->getColumns() as $column => $colData){							
							if(isset($updateList[$column]) 
								and (array_search($column, Rhema_Grid_Adapter_DoctrineModel::$ignoreFields) === false)){								
								$value = self::processColumnData($column, $updateList, $model);
								if($value !== 'null'){
									$column = $me->_table->getColumnName($column);
									$query->set("$column", '?', "$value");
								}
							}
						}
					}else{ // SELECT Query
						if(!$offset = $filter->getOffset()){							 
							$offset = ($filter->getPage() - 1) * $filter->getLimit();
						}
						$query = Doctrine_Query::create()
									->select($me->_getSelectColumns($filter))
									->from("$model $alias INDEXBY $indexString")																		
									->useQueryCache($useQueryCache)
									->setHydrationMode($filter->getHydrationMode());
					
						// use result cache			
						$request = Zend_Controller_Front::getInstance()->getRequest();	
						$uri     = $request->getRequestUri();		
						if(Rhema_SiteConfig::getConfig('settings.use_cache')
							and $manager->getAttribute(Doctrine_Core::ATTR_RESULT_CACHE) 
							and !$request->getParam('isControlPanel') 
							and !Rhema_Util::isAdminRoute($uri)
							and !$request->getParam('rest') 
							and !Rhema_Util::isCli() )
							{ 
								$query->useResultCache(true);
						}						
					}
					
					//set query limit
					if($limit){
						$query->limit($limit)->offset($offset);
					}
 
					// add join tables and record alias
					$joinList = $filter->getJoinList();
					if(count($joinList)){ 
						$aliasRecord = array();
						$manyRelations = $me->getTable()->getRelations();
						
						foreach($joinList as $item => $itemData){
							$joinType = $itemData['type'];
							$joinAlias = chr(++ $cnt);
							if(isset($manyRelations[$item])){
								$className = $manyRelations[$item]['class'];
								$joinClass = $manyRelations[$item]->getTable();
								$joinClause = "{$alias}.{$item} as $joinAlias";
							}elseif(preg_match('/([a-z]+)\.([a-z]+)/i', $item, $arr)){
								 
 								$item      = $arr[2]; 
								$className = $me->getModelNameFilter()->filter($item);
								$joinClass = Doctrine_Core::getTable($className);
								$parentAlias = $aliasRecord[$arr[1]];
								$joinClause = "{$parentAlias}.{$item} as $joinAlias"; 
							}else{
								continue;
							}
							
							$aliasRecord[$item] = $joinAlias;
							$refFilter = new Rhema_Dao_Filter();
							$fields = count($itemData['columns']) ? $itemData['columns'] : $joinClass->getColumnNames();
							$refFilter->addFields($fields);
							
							$refSelectCols = $me->_getSelectColumns($refFilter, $className, $joinAlias);
							$query->$joinType($joinClause);
							$query->addSelect($refSelectCols);						
						}
						
					}elseif($addRelations){
						foreach($me->getManyRelations() as $joinAlias => $relation){
							$as = chr(++ $cnt);
							$refFilter = new Rhema_Dao_Filter();
							$refSelectCols = $me->_getSelectColumns($filter, $relation['model'], $as);
							$query->innerJoin("{$alias}.{$joinAlias} as $as");
							$query->addSelect($refSelectCols);
						}
					}
									
					// add where clauses
					foreach($filter->getConditions() as $groupOperator => $conditions){
						$method = $groupOperator . 'Where';
						 					
						foreach($conditions as $condition){
							$field    = $condition->getField();
							$operator = $condition->getOperator();
							$value    = $condition->getValue(); 
							if($operator == Rhema_Dao_Filter::OP_RAW_SQL){ //$value should be an array with placeholder values specified in $rawSql
								$rawSql = preg_replace('/(\{alias\})/i', $alias, $field);
								$query->$method($rawSql, $value);
							}elseif(preg_match('/(like)$/i',$operator)){
								switch($operator){
									case Rhema_Dao_Filter::OP_LLIKE:{
										$value = "$value%";
										break;
									}
									case Rhema_Dao_Filter::OP_RLIKE:{
										$value = "%$value";
										break;
									}
									case Rhema_Dao_Filter::OP_LRLIKE:{
										$value = "%$value%";
										break;
									}																		
								}
								$query->$method("{$alias}.{$field} LIKE ?", $value);
							}elseif($operator == Rhema_Dao_Filter::OP_IS_NULL or $operator == Rhema_Dao_Filter::OP_NOT_NULL){
								$query->$method("{$alias}.{$field} $operator");
							}elseif($operator == Rhema_Dao_Filter::OP_IN){
								$inMethod = $groupOperator . 'WhereIn';
								$query->{$inMethod}("{$alias}.{$field}", $value);
							}elseif($operator == Rhema_Dao_Filter::OP_NOT_IN){
								$inMethod = $groupOperator . 'WhereNotIn';
								$query->$inMethod("{$alias}.{$field}", $value);
							}elseif(preg_match('/([a-z]+)\.([a-z]+)\.([a-z]+)/i', $field , $arr)){
								//$parentAlias = $aliasRecord[$arr[1]];
								//$curField    =  $arr[1] . '.'.$arr[2] ;
								$calias      =  $aliasRecord[$arr[2]];
								$query->$method("{$calias}.{$arr[3]} $operator ?", $value);
							}elseif($operator == Rhema_Dao_Filter::WITH_PLACEHOLDERS){
								
								$pattern = array( '/\{([^\.\{\}]+)\}/i' );								
								$replace = array( $alias . '.$1' );
								
								foreach($aliasRecord as $parent => $parAlias){
									$pattern[] = "/\{($parent)\.([a-z_]+)\}/i" ;
									$replace[] = $parAlias . '.$2';
								}
								
								$fieldString = preg_replace($pattern, $replace, $field); 								
								$query->$method($fieldString, $value);		
															
							}else{
								$query->$method("{$alias}.{$field} $operator ?", $value);
							}
						}
						
						 
					}
										
					//add sort order
					foreach(array_reverse($filter->getOrderByList()) as $col => $order){
						if(preg_match('/([a-z_]+)\.([a-z_]+)/i', $col, $arr)){
							$parentAlias = $aliasRecord[$arr[1]];
							$query->addOrderBy("{$parentAlias}.{$arr[2]} $order");
						}elseif('rand' == $col){
							$query->addSelect("RANDOM() as $col")
							      ->orderBy($col);
						}elseif($me->_table->hasColumn($col)){							
							$query->addOrderBy("{$alias}.{$col} $order");
						}elseif('title' != $col){
						 	$query->addOrderBy("$col $order");
						}
					}

					// add group by
					foreach ($filter->getGroupByList() as $field){
						$query->addGroupBy($field);
					}
					
					// add aggregate fields e.g SUM etc					
					foreach($filter->getAggregateFieldList() as $func => $data){
						foreach($data as $field){ 
							if(preg_match('/([a-z_]+)\.([a-z_]+)/i', $field, $arr)){
								$parentAlias = $aliasRecord[$arr[1]];
								$aggAlias    = strtolower(implode('', array($func, ucfirst($arr[2]))));
								$query->addSelect("$func({$parentAlias}.{$arr[2]}) $aggAlias");
							}elseif($me->_table->hasColumn($field)){	
								$aggAlias    = strtolower(implode('', array($func, ucfirst($field))));						
								$query->addSelect("$func({$alias}.{$field}) $aggAlias");
							} 
						}
					}
					// bypass soft delete 
					if($filter->getBypassSoftDelete()){
						$query->andWhere("$alias.deleted_at IS NULL OR $alias.deleted_at IS NOT NULL");
					}	

					if($queryType == Rhema_Dao_Filter::QUERY_TYPE_SELECT and !count($query->getDqlPart('where'))){
						// $query->where("1=1"); 
					} 
										
					if($filter->getDebug() and (APPLICATION_ENV != 'production' or Rhema_Util::isCli())){
						$params = array(
								'DQL' => wordwrap($query->getDql(), 100), 
								'SQL' => wordwrap($query->getSqlQuery(), 100), 
								'ARGS' => $query->getParams());
						return $params;
					}
					return $query;
				}
			
			}catch(Exception $e){
				throw new Doctrine_Query_Exception('An error occurred: ' . $e->getmessage());
			}
		}else{
			throw new Doctrine_Query_Exception('No model set. Unable to create query object');
		}
	}
	
	public static function processColumnData($col, $data, $model = null){
		$val = $data[$col];
		if(substr($col, - 3) == '_id'){			 
			//$val = intval($value) ;
		}elseif(substr($col, - 3) == '_at'){
			$timestamp = $data[$col] ? strtotime($data[$col]) : time();
			$date = new Zend_Date($timestamp); 
			$val = $date->toString('YYYY-MM-dd HH:mm:ss');
		}elseif($col == 'latitude' or $col == 'longitude'){
			$helper = Zend_Controller_Action_HelperBroker::getStaticHelper('Geocoder');
			$address = Rhema_Util_String::addressArrayToString($data);
			$latLong = $helper->direct($address);
			$val = $latLong[$col];
		} 
		
		if(preg_match('/(page|eventType)$/i', $model) and $col == Rhema_Constant::MENU_FRONTEND_KEY){
			$action = trim($data[$col]) ? $data[$col] : $data['title'];
			$val = Doctrine_Inflector::urlize($action);
		}
		
		return $val;
	}
	/**
	 * Builds a list of colums to be  used in the select options of the DQL
	 * @param Rhema_Dao_Filter $filter
	 * @param unknown_type $model
	 * @param unknown_type $alias
	 * @return string
	 */
	protected function _getSelectColumns(Rhema_Dao_Filter $filter, $model = null, $alias = null){
		$alias 		= $alias ? $alias : Rhema_Dao_Filter::ENTITY_ALIAS;
		$cols      	= $filter->getFields();
		$allCols 	= $this->getColumns($model);
		$restricted = false;
		
		if(! count($cols)){
			$cols = $allCols;
		}else{
			//$cols = array_keys($cols); //TODO  need to check
			array_unshift($cols, 'id');
			$cols = array_flip(array_unique($cols));
			$restricted = true;
		}
		$selectArray = array();
		
		foreach($cols as $col => $colData){
			if(array_search($col, $this->_ignoreFields) === false){
				if(! $restricted or ($restricted and isset($allCols[$col]))){
					$selectArray[] = $alias . '.' . $col;
				}
			}
		}
		
		if(isset($allCols[self::SUBSITE_KEY]) and !isset($cols[self::SUBSITE_KEY])){
			$selectArray[] = $alias . '.' . self::SUBSITE_KEY ;
		}
		return implode(', ', $selectArray);
	}
	
	/**
	 * Function to create string representation of a model
	 * Ideally similar to a call to __toString() 
	 * However this is used the the zfGrid to display meaningful text
	 * for one-to-many relationships instead of just the id
	 * @param unknown_type $foreighKey
	 * @param unknown_type $id
	 * @return string
	 */
	public static function getStringRepresentation($foreighKey, $id){
		$filter = new Rhema_Filter_FormatModelName();
		$model = $filter->filter($foreighKey);
		$row = Doctrine_Core::getTable($model)->find($id);
		$hidden = new Zend_Form_Element_Hidden(array(
				'name' => 'hidden_' . $foreighKey, 
				'value' => $id, 
				'decorators' => array(
						'ViewHelper')));
		
		$display = (string) $row;
		
		return $display . $hidden;
	}
	
/*	public function getAllRecords($class){
		$filter = new Rhema_Dao_Filter();
		$filter->setModel($class)->setHydrationMode(Doctrine_Core::HYDRATE_RECORD);
		$query = $this->createQuery($filter);
		//Rhema_Util::getEditOptions($model, $column, $key = 'id', $opt = 'title', $show = false)		
		return $query->execute();
	}*/
	/**
	 * @return the $_modelNameFilter
	 */
	public function getModelNameFilter(){
		if(! $this->_modelNameFilter){
			$this->_modelNameFilter = new Rhema_Filter_FormatModelName();
		}
		return $this->_modelNameFilter;
	}

	public static function createEditOptionQuery(Rhema_Dao_Filter $filter){ 
		$parentTable = $filter->getParentTable();
		$key    = $filter->getSelectKey();
		$opt    = $filter->getSelectDisplayField();
		
		$filter->addOrderBy($opt, Rhema_Dao_Filter::ORDER_ASC);
			  // ->addFields(array($key,  $opt));
		
		if($filter->getModel() == MODEL_PREFIX . 'Template'){			
			if(MODEL_PREFIX . 'Page' == $parentTable){
				$filter->addCondition('is_header', 0)
					   ->addCondition('is_footer', 0);
			}elseif(MODEL_PREFIX . 'PageHeader' == $parentTable){
				$filter->addCondition('is_header', 1) ;
			}elseif(MODEL_PREFIX . 'PageFooter' == $parentTable){
				$filter->addCondition('is_footer', 1);
			}
		}

		return self::createQuery($filter);
	}
	
	/**
	 * Enter description here ...
	 * @param Rhema_Dao_Filter $filter
	 * @param unknown_type $page
	 * @param unknown_type $useCache
	 * @return Zend_Paginator
	 */
	public static function getPaginator(Rhema_Dao_Filter $filter, $page = 1, $useCache = true){	 
		$paginatorAdapter = new Rhema_Adapter_Paginator($filter); 
		$paginator        = new Zend_Paginator($paginatorAdapter);
			
		if($useCache){
			$cache        = self::getCache(); 
 			$paginator->setCache($cache);
		}		 
		$paginator->setItemCountPerPage($filter->getLimit())   
				  ->setCurrentPageNumber($page); 

		return $paginator;
	
	}
	
	/**
	 * Create and instance of Zend Paginator with an Array Adapter Interface
	 * @param array $list
	 * @param unknown_type $itemsPerPage
	 * @param unknown_type $page
	 * @return Zend_Paginator
	 */
	public static function getArrayPaginator(array $list, $itemsPerPage, $page = 1){ 
    	$cache            = self::getCache();    	
		$paginatorAdapter = new Zend_Paginator_Adapter_Array($list) ;
		$paginator        = new Zend_Paginator($paginatorAdapter);
		 
		$paginator->setItemCountPerPage($itemsPerPage)  
				  ->setCurrentPageNumber($page); 
 		$paginator->setCache($cache);
		return $paginator;		
	}
	
	/**
	 * @return the $_cache
	 */
	public static function getCache() {
		if(!self::$_cache){
			$cacheManager  = Zend_Registry::get(Rhema_Constant::CACHE_MANAGER); 			
			if(extension_loaded('memcache')){
				$cache = $cacheManager->getCache('memcache');
			}else{
				$cache = $cacheManager->getCache('doctrine-cache');
			}  
			self::$_cache = $cache ;
		}
		
		return self::$_cache ;
		
	}
 

}