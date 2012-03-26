<?php
	class Admin_Service_Grid_Adapter_Database implements Admin_Service_Grid_Adapter_Interface, Rhema_Cache_Interface {

		protected $_utility;
		protected $_params = array();
		protected $_cache;
		protected $_cacheOptions;

		public function __construct($arr = array()){
			$this->_utility = Rhema_Util::getInstance();
			$this->_params  = $arr;

		}

		public function setParam($param = array()){
			$this->_params  = $param;
		}

		public function getPager($table){
	    	$page 			= $this->getParam('page',1); // get the requested page
	    	$limit 			= $this->getParam('rows', null); // get how many rows we want to have into the grid
	    	$sidx 			= $this->getParam('sidx','title'); // get index row - i.e. user click to sort
	    	$sord 			= $this->getParam('sord','ASC'); // get the direction
	    	$root_id    	= $this->getParam('root_id',null);
	    	$doSearch   	= $this->getParam('_search', false);
	    	$type_id    	= $this->getParam('type_id', null);
	    	$page_type  	= $this->getParam('page_type', null);
	    	$isAdmin    	= $this->getParam('isAdmin', null);
	    	$display_type 	= $this->getParam('display_type');
	    	$multiSearch    = $this->getParam('filters', null);
	    	$searchField    = $this->getParam('searchField');

	    	$isTree     = Rhema_Util::isTreeTable($table);
    		$query 		= Doctrine_Query::create()
    						->from($table . ' t') 
    						->orderBy("t.$sidx $sord");

	    	if(substr($table,-4) == 'Menu' and $doSearch != 'true'){
	    		if(!$isAdmin and $root_id){
	    			$query->where('t.level <> ?', 0);
	    		}

	    		if($root_id){
	    			$query->andWhere('t.root_id =?', $root_id);
	    		}elseif(!$isAdmin){
	    			$query->andWhere('t.level IS NULL');
	    			$query->andWhere('t.root_id IS NULL');
	    		}

	    	}

	    	if(ADMIN_PREFIX . 'AdminTable' == $table){
	    		$query->andWhere('t.is_hidden = ?', 0);
	    	}

	    	if(HELP_PREFIX . 'Document' == $table and $type_id){
	    		$query->andWhere('t.type_id = ?', $type_id);
	    	}


	    /*	if(MODEL_PREFIX . 'Page' == $table){
	    		if($page_type){
	    			$query->andWhere("t.$page_type = ?", 1);
	    		}else{
	    			$query->andWhere('t.is_header = ?', 0);
	    			$query->andWhere('t.is_footer = ?', 0);
	    		}
	    	}
	     */

	    	if($doSearch     == 'true'){
	    		if($multiSearch){
	    			$multiSearch 	= urldecode($multiSearch);
	    			$filters 		= json_decode($multiSearch, true);

	    			if(isset($filters['groupOp'])){
	    				$grpOp   		= $filters['groupOp'] ;
		    			foreach($filters['rules'] as $data){
		    				$this->_utility->transformSearch($query, $data['field'], $data['data'], $data['op'], $grpOp);
		    			}
	    			}else{
	    				$cols = Doctrine_Core::getTable($table)->getColumnNames();
	    				foreach($cols as $field){
	    					if(isset($filters[$field])){
	    						$this->_utility->transformSearch($query, $field, $filters[$field], 'cn', 'and');
	    					}
	    				}
	    			}


	    		}elseif($searchField){
	    			$operator     = $this->getParam('searchOper');
	    			$searchString = $this->getParam('searchString');
	    			$args         = array_filter(explode(',', $searchString));

	    			for($i=0; $i<count($args); $i++){
	    				$this->_utility->transformSearch($query, $searchField, $args[$i], $operator);
	    			}

    			}else{
    				 $arr		  = array();
	    			 foreach($_POST as $k => $v){
	    			 	if(!preg_match('/^(_search|nd|page|rows|sidx|sord)$/i',$k)){
	    			 		$arr[$k] = $v;
	    			 	}
	    			 }

	    			 foreach($arr as $f => $d){
	    			 	$this->_utility->transformSearch($query, $f, $d, 'bw');
	    			 }
    			}

	    	}

	    	//$sidx       = $sidx  ? $sidx  : 'id';
	    	$pager      = new Doctrine_Pager($query, $page, $limit);
	    	$pager->setCountQuery($query);

	    	//$this->_result =  $pager->execute(array(),Doctrine::HYDRATE_ARRAY);
	    	return $pager;
		}



		public function getColumns($table, &$tableName = null){
			$typeId		= $this->getParam('type_id', null);
	    	$mandatory  = array();
	    	$hold       = array();
	    	$return     = array();
	    	$oTable     = Doctrine_Core::getTable($table);
	    	$allCols    = $oTable->getColumns();
	    	$tableName  = $oTable->getTableName();

	    	if($typeId){
	    		$res          = Help_Model_Field::getMandatoryColumns();
	    		$mandatory    = $res['title'];
		    	$row          = Help_Model_Type::getItem($typeId);
		    	$selectedCols = Help_Model_TemplateField::getTemplateFields($row['template_id']);
		    	$len          = count($selectedCols);

		    	if($len){
		    		for($i=0; $i<$len; $i++){
		    			$title         = $selectedCols[$i]['Field']['title'];
		    			$hold[$title ] = $allCols[$title];
		    		}
		    	}
	    	}

	    	if(is_array($mandatory)){
	    		$temp = array_flip($mandatory);
	    	}

	    	foreach($allCols as $tit => $item){
		    	if($typeId and (isset($temp[$tit]) or isset($hold[$tit]))){
		    		$return[$tit] = $item;
	    		}elseif(!$this->isRestricted($tit, $table)){
		    		$return[$tit] = $item;
	    		}
	    	}


	    	$return = count($return) ? $return : $allCols;

	    	return $return;
		}

		public function update(){

		}

		public function getParam($key, $default = null){
			return isset($this->_params[$key]) ? $this->_params[$key] : $default ;
		}
		public function getLabel($col){
			return Rhema_Util::getLabel($col);
		}
		public function isRestricted($col, $model){
			return Rhema_Util::dontEdit($col, $this->_params, $model);
		}

		public function getOptions($model,$table,$key, $field, $show, $col = ''){
			return Rhema_Model_Abstract::getEditOptions($model,$table,$key, $field, $show);
		}

		public function getSortField($table, $default = 'title'){
			return Rhema_Util::isTreeTable($table) ? 'lft' : $default;
		}

		public function getCaption($table){
    		 return Rhema_Util::getLabel($table);
    	}

   //===========================================================
   		public function setCache(Rhema_Cache_Abstract $cache){
			$this->_cache = $cache;
		}

		public function setCacheOptions(array $options){
			$this->_cacheOptions = $options;
		}

		public function getCacheOptions($type = 'class-file') {
			if (empty ( $this->_cacheOptions[$type] )) {
				$this->_cacheOptions[$type] = $this->_utility->getCacheOptions($type, $this);
			}

			$cacheDir = APPLICATION_PATH . '/../data/cache/database';
			if(!file_exists($cacheDir)){
				mkdir($cacheDir, 0777, true);
			}
			$this->_cacheOptions['backendOptions']['cache_dir']  = realpath($cacheDir);

			return $this->_cacheOptions[$type];
		}


		public function getCached($tagged = null){
			if(defined('NO_CACHE') and NO_CACHE){
				return $this;
			}

			if(null == $this->_cache){
				$this->_cache = new Rhema_Cache(
					$this, $this->getCacheOptions()
				);
			}

			$this->_cache->setTagged($tagged);
			return $this->_cache;
		}
//============================================================================
	}