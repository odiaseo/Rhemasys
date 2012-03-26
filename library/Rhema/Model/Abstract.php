<?php
class Rhema_Model_Abstract extends Doctrine_Record implements Rhema_Cache_Interface{

	public static $pageTemplate = '<a href="{%url}/{%page_number}">{%page}</a>';
	public static $activePageTemplate = '<a href="{%url}/{%page_number}" class="ui-state-active">{%page}</a>';
	public static $memManager;

	protected $_cacheType = 'Class';
	protected $_productImageDir = 'product_images/';
	protected $_imageExtension = '.png';
	public $imageSizes = array(
			'small' => 32,
			'thumbnail' => 120,
			'medium' => 240,
			'large' => 360,
			'xlarge' => 600,
			'zoom' => 1280);

	public static function _getAllRecords($tableClassName, $parm = array()){
		$mFilter  = new Rhema_Filter_FormatModelName();
		$tableClassName = $mFilter->filter($tableClassName);
		
		$filter = new Rhema_Dao_Filter();
		$filter->setModel($tableClassName)
			   ->addOrderBy('title');
			   
/*		$query = Doctrine_Query::create()
					->from("$tableClassName t INDEXBY t.id")
					->orderBy('t.title')
					->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);*/

		foreach($parm as $k => $v){
			//$query->addWhere("t.$k =?", $v);
			$filter->addCondition($k, $v);
		}
		$data = Rhema_Model_Service::createQuery($filter)->execute();
		//$data = $query->execute();



		return $data;
	}

	public static function findAll($filter = null, $model = null){
		if(!$filter){
			$filter = new Rhema_Dao_Filter();
			//$model = get_called_class();
		}

		if(!$filter->getModel() and $model){ 
			$filter->setModel($model);
		}
		return Rhema_Model_Service::createQuery($filter)->execute();

	}

	public function countAll($daoFilter){
		$filter = clone($daoFilter);
		$filter->setLimit(null)
				->setOffset(0)
				->setPage(1);

		return Rhema_Model_Service::createQuery($filter)->count();
	}

	public static function buildGridSelectOption($class, $gridTable){
		$mFilter  = new Rhema_Filter_FormatModelName();
		$gridTable = $mFilter->filter($gridTable);

		$tableObj = Doctrine_Core::getTable($class);
		$options = Rhema_Grid_Adapter_DoctrineModel::getBlankSelect();
		$filter = new Rhema_Dao_Filter();
		$filter->setModel($class)
			   ->setParentTable($gridTable)
			   ->setHydrationMode(Doctrine_Core::HYDRATE_RECORD);

		$data = Rhema_Model_Service::createEditOptionQuery($filter)->execute();

		//$data = Doctrine_Core::getTable($class)->findAll(Doctrine_Core::HYDRATE_RECORD);
		foreach($data as $record){
			$col = $record->getTable()->getIdentifier();
			$key = $record[$col];
			$options[$key] = (string) $record;
		}
		$select = new Zend_Form_Element_Select($class, array(
				'multiOptions' => $options,
				'decorators' => array('ViewHelper')));

		return $select;
	}

	public function setCache(Rhema_Cache_Abstract $cache){
		$this->_cache = $cache;
	}

	public function setCacheOptions(array $options){
		$this->_cacheOptions = $options;
	}

	public static function getTableId($table){
		$filter = new Rhema_Dao_Filter();
		$filter->setModel('Admin_Model_AdminTable')
		       ->addCondition('name', $table)
		       ->addField('id')
		       ->setLimit(1);
		$item = Rhema_Model_Service::createQuery($filter)->fetchOne();
		return $item ? $item['id'] : false ;
	}

	public function getCacheOptions(){
		if(empty($this->_cacheOptions)){
			$frontendOptions = array(
					'lifetime' => 1800,
					'automatic_serialization' => true);

			$backendOptions = array(
					'cache_dir' => APPLICATION_PATH . '/../data/cache/db');

			$this->_cacheOptions = array(
					'frontend' => 'Class',
					'backend' => 'File',
					'frontendOptions' => $frontendOptions,
					'backendOptions' => $backendOptions);
		}

		return $this->_cacheOptions;
	}

	public function getCached($tagged = null){
		if(Rhema_SiteConfig::getConfig('settings.use_cache')){
			if(null == $this->_cache){
				$this->_cache = new Rhema_Cache($this, $this->getCacheOptions());
			}

			$this->_cache->setTagged($tagged);
			return $this->_cache;
		}else{
			return $this;
		}
	}

	public static function saveData($request){
		$posted = $request->getPost();
		$curTable = $request->getParam('table', null);
		$operation = $request->getParam('oper', null);
		$rowid = $request->getParam('id', null);

		$message = '';
		$tags = array();
		$row = null;

		if($curTable){
			$nameFilter = new Rhema_Filter_FormatModelName();
			$model = $nameFilter->filter($curTable);

			$filter = new Rhema_Dao_Filter();
			$filter->setModel($model);

			if('del' == $operation){

				$filter->setQueryType(Rhema_Dao_Filter::QUERY_TYPE_DELETE);
				$filter->setCondition('id', $rowid, Rhema_Dao_Filter::OP_EQ);
				$query = Rhema_Model_Service::createQuery($filter);
				$result = $query->execute();
				$message = 'Record Deleted';

			}elseif('add' == $operation){

				$filter->setQueryType(Rhema_Dao_Filter::QUERY_TYPE_CREATE);
				$filter->setUpdateList($posted);
				$row = Rhema_Model_Service::createQuery($filter);


				if($request->getParam(Admin_Model_AdminMenu::TYPE_ROOTS_ONLY)){
					$row->root_id  = 2; 
					$row->level    = 0 ;
					$row->slug     = Doctrine_Inflector::urlize($row->title);
					$row->sequence = 1;
					
					
					$row->save();
 
					$row->root_id = $row->id;
					$row->save();
					
					$row->getTable()->getTree()->createRoot($row);
					$childData   = $row->toArray() ;
					unset($childData['id']);
					
					$childData['title'] = 'first child';
					$childData['label'] = 'first child label';
					$childData['level'] = 0 ; 
					$childData['slug']  = Doctrine_Inflector::urlize($childData['title']);
 		
					$firstChild = Admin_Model_AdminMenu::getDefaultRow($childData,$model);					 
					$firstChild->getNode()->insertAsFirstChildOf($row);

				}else{
					$row->save();
				}

			}else{
				$filter->setQueryType(Rhema_Dao_Filter::QUERY_TYPE_UPDATE);
				$filter->setUpdateList($posted);
				$filter->setCondition('id', $rowid, Rhema_Dao_Filter::OP_EQ);
				$query = Rhema_Model_Service::createQuery($filter);
				$query->execute();
			}

			Rhema_Cache::clearCacheOnUpdate($model);
		}

		$return['message'] = $message;
		$return['rowId'] = $rowid;
		$return['object'] = $row;
		$return['pass']   = 1;

		return $return;
	}

	public static function processColumnData($col, $data){
		$val = $data[$col];
		if(substr($col, - 3) == '_id'){
			//$val = intval($value) ;
		}elseif(substr($col, - 3) == '_at'){
			list($date, $time) = explode(' ', $val);
			$time = $time ? $time : date('H:i:s');
			$val = implode(' ', array(
					$date,
					$time));
		}elseif($col == 'latitude' or $col == 'longitude'){
			$helper = Zend_Controller_Action_HelperBroker::getStaticHelper('Geocoder');
			$address = Rhema_Util_String::addressArrayToString($data);
			$latLong = $helper->direct($address);
			$val = $latLong[$col];
		}

		return $val;
	}

	public static function clearRelatedCacheFiles($tags){
		$cache = new Rhema_Cache();
		$cache->removeCacheByTag($tags);
	}
/*
	public static function getEditOptions($model, $refColumn, $key = 'id', $opt = 'title', $show = false, $html = false){

		$refTable = Rhema_Util::table2Model($refColumn);

		if(Zend_Registry::isRegistered($refTable) and ! NO_CACHE){
			$options = Zend_Registry::get($refTable);
		}else{
			$explode = array_filter(explode(' ', $opt));
			$explode[] = $key;
			foreach($explode as $f){
				$sel[] = "t.$f";
				$ord[] = "$f asc";
			}

			$select = implode(', ', $sel);
			$order = implode(', ', $ord);

			$query = Doctrine_Query::create()->select($select)->from("$refTable t")->orderBy($order);
			if(MODEL_PREFIX . 'Template' == $refTable){
				$pageType = Zend_Controller_Front::getInstance()->getRequest()->getParam('pageType', null);
				if($pageType){
					$query->where("t.is_{$pageType} =?", 1);
				}else{
					$query->where("t.is_header =?", 0);
					$query->andWhere("t.is_footer =?", 0);
				}
			}

			if($refColumn == 'admin_menu_id' and $model == ADMIN_PREFIX . 'AdminModule'){
				$query->andWhere("t.level =?", 0);
			}elseif(strpos($refTable, 'Menu') !== false){
				$query->where('(t.rgt - t.lft) =?', 1);
			}

			$result = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			//;


			$options = Rhema_Util::generateOptionArray($result, $key, $opt, $show, $html);
			if(count($result)){
				Zend_Registry::set($refTable, $options);
			}
		}
		return $options;
	}*/

	public function getImages($includeDefault = false){

	}

	public function getZoomImage(){
		$parms = '';
		$out['src'] = $this->getImage('zoom', 'product', $parms);
		$out['attr'] = $parms;

		return $out;
	}

	public static function getImage(array $data, $type = 'thumbnail', $subDirectory = 'image', &$imgParm = ''){
		$filePath = '';
		if(count($data)){
			$me       = new self();
			$root = realpath(SITE_PATH . '/../'); //realpath(APPLICATION_PATH . '/../public/' );
			$original = (isset($data['image_file'])and  $data['image_file']) ? $data['image_file'] : (isset($data['thumb']) ? $data['thumb'] : null);
			$original = $root . $original;
			$imgParm = getimagesize($original);
			if(file_exists($original) and is_file($original)){
				$id   = is_numeric($data['id']) ? sprintf("%05d", $data['id']) : $data['id'];
				$type = isset($me->imageSizes[$type]) ? $type : 'thumbnail';
				$fileParts[] = $root;
				$fileParts[] = BASE_URL;
				$fileParts[] = 'userfiles';
				$fileParts[] = $subDirectory;
				$fileParts[] = $type;

				$directory = implode('/', array_filter($fileParts));
				$filename = '/' . $id . $me->_imageExtension;

				if(! file_exists($directory)){
					mkdir($directory, 0777, true);
				}

				$fullPath = $directory . $filename;

				if(! file_exists($fullPath)){
					if($type == 'zoom'){
						copy($original, $fullPath);
					}else{
						$width = $me->imageSizes[$type];
						$oImage = new Admin_Service_Image();

						$oImage->load($original);
						$oImage->resizeToWidth($width);
						$oImage->save($fullPath, IMAGETYPE_PNG);
					}
				}

				$prefix = $root . '/';
				$filePath = str_replace($prefix, '', $fullPath);
			}
		}
		return $filePath;
	}

	public static function getToolTip($model, $itemId){
		$tip = '';
		/*$query = Doctrine_Query::create()
	 					->select('c.title,t.name')
	 					->from('Admin_Model_AdminContentType c')
	 					->leftJoin('c.AdminTable t')
	 					->where('c.id =?', $contentTypeId)
	 					->limit(1)
	 					->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
	 		$res   = $query->execute();
	 		*/
		//if(count($res)){
		//	$name  = $res[0]['AdminTable']['name'];
		//	$model = Rhema_Util::table2Model($name);
		$tip = Doctrine_Core::getTable($model)->find($itemId, Doctrine_Core::HYDRATE_ARRAY);
		//}
		return $tip;

	}

	public function getDefaultImage(){

	}

	public function formatImageName($str){
		$str = strtolower($str);
		$str = preg_replace('/\s+/', '-', $str);
		$str = preg_replace('/[^a-zA-Z0-9-]/', '', $str);
		return $str;
	}

	public static function bypassSoftDelete(&$query, $alias){
		$query->andWhere("$alias.deleted_at IS NULL OR $alias.deleted_at IS NOT NULL");
	}
	
	public static function getRestResult($resp, $function){
		$return = array();

		if($resp->isSuccess()){
			//$xml =  $resp->getIterator();
			$data = (array) $resp->{$function};
			foreach($data as $k => $v){
				if('status' != $k){
					$k = str_replace('key_', '', $k);
					$return[$k] = self::stringify($v);
				}
			}
		}

		return $return;
	}

	public static function stringify($data){

		if(is_array($data) or is_object($data)){
			foreach($data as $k => $val){
				$k = str_replace('key_', '', $k);
				$return[$k] = self::stringify($val);
			}
			if(! isset($return)){
				$return = (string) $data;
			}
		}else{
			$return = (string) $data;
		}

		return $return;
	}

	public static function getTablesByRegex($regex, $exempt = array()){
		$tables    = array();
	    $filter    = new Rhema_Filter_FormatModelName();
		$con       = Doctrine_Manager::getInstance()->getCurrentConnection();
		$dbTables  = $con->import->listTables(); 
		$exempt    = array_merge($exempt, array(MODEL_PREFIX . 'AdminAcl',
				   								//MODEL_PREFIX . 'AdminDictionary',
				   								//MODEL_PREFIX . 'AdminSubsite',
				   								//MODEL_PREFIX . 'AdminLicence',
				   								MODEL_PREFIX . 'MigrationVersion',
				   								//MODEL_PREFIX . 'AdminSubsiteLicence',
				   								MODEL_PREFIX . 'AdminDatabase',
				   								//MODEL_PREFIX . 'AdminTable',
				   								MODEL_PREFIX . 'AffiliateProduct',
				   								MODEL_PREFIX . 'AffiliateProductBrand',				   								
				   								//MODEL_PREFIX . 'AdminModule'
							));
		foreach($dbTables as $t){
			$name = $filter->filter($t);
			if(!in_array($name, $exempt) and preg_match($regex, $name)){
				$tables[] = $name;
			}
		}	

		return $tables;
	}
	
	public static function reLoadAdminTables($loadType = false){
		set_time_limit(0);

		$data   = array();
		$blank  = array();
		$tables = array();
 
		if($loadType){		
			if($loadType == 1){
				$tables = array(
					MODEL_PREFIX . 'Template',
					MODEL_PREFIX . 'TemplateSection',
					MODEL_PREFIX . 'PageHeader',
					MODEL_PREFIX . 'PageFooter',
					MODEL_PREFIX . 'Category',
					MODEL_PREFIX . 'User'
				); 
			}else{
				switch($loadType){
					case 'admin':{
						$pattern   = REGEX_TABLE_ADMIN;
						break;
					}
					
					case 'site':{
						$pattern   = REGEX_TABLE_SITE;
						break;
					}
					
					case 'page-setup':{
						$pattern   = REGEX_TABLE_PAGE;
						break;
					}
					case 'all':
					default: {
						$pattern   = REGEX_TABLE_ALL;
						break;
					}				 
				}
												
				$tables    = self::getTablesByRegex($pattern); 
			}
 
			$data = self::getDefaultTableDetails($tables);
			
			if(in_array(MODEL_PREFIX . 'AdminMenu', $tables)){
				Admin_Model_AdminAcl::initAcl();
			}
	
			$message = '<h2>' . count($data) . ' tables updated successfully</h2>
					   <ol><li>' . implode('</li><li>', $data) . '</li></ol>';
			 
			$flashMsg      = Zend_Controller_Action_HelperBroker::getStaticHelper('flashMessenger');
			$userMessage   = new Rhema_Dto_UserMessageDto($message, 'DB Update', Rhema_Dto_UserMessageDto::TYPE_SUCCESS);;
			 
			$flashMsg->addMessage($userMessage); 
		}	
		
		return true;
	}

	public static function getDefaultTableDetails($model, $domain = null, $ajax = false){
		$front  = Zend_Controller_Front::getInstance();
		$error  = false;
		$result = array();

		try{
			$client = Rhema_Util::getRemoteClient($domain);
			$resp = $client->getDefaultTableDetails($model)->post();

			if($resp->isError()){
				$message = (string) $resp->message;
				if(Zend_Registry::isRegistered('logger')){
					Zend_Registry::get('logger')->info($message);
				}
				return $message;
			}else{
				$return = self::getRestResult($resp, __FUNCTION__);
				if(is_array($model)){
					foreach($return as $m => $item){
						$result[] = $m . ' (' . self::populateTable($item, $m) . ')';
					}
				}else{
					$result[] = $model . ' (' . self::populateTable($return, $model) . ')';
				}
				return $result;
			}

		}catch(Exception $e){
			if($ajax){
				return $e->getMessage();
			}else{
				$front->getResponse()->setException($e);
				$error = true;
			}
		}

		if($error){
			$helper = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
			$helper->gotoRoute(array(
					'module' => $front->getRequest()->getModuleName(),
					'controller' => 'error',
					'action' => 'error'), 'default');
		}
	}

	protected static function populateTable($data, $model){
		$cols         = Doctrine_Core::getTable($model)->getColumnNames();
		$subsiteId    = (in_array('admin_subsite_id', $cols)) ? Zend_Registry::get('namespace')->subsiteId : null;
		$isAdmin      = (!$subsiteId and substr($model, 0, 6) == 'Admin_');
		$rowsDeleted  = self::deleteSiteDetails($model, $subsiteId, $isAdmin);
		$data         = is_array($data) ? $data : array();
		$count        = 0;
		
		foreach($data as $item){
			$obj = new $model();
			$toAdd = array();
			foreach($cols as $column){
				if(! preg_match('/(_at|_by|admin_subsite_id)$/i', $column) and $column != 'id' and isset($item[$column])){
					if(preg_match('/(^is_|_id$)/i', $column)){
						$toAdd[$column] = (int) $item[$column];
					}else{
						$toAdd[$column] =  $item[$column];
					}
				}
			}

			$obj->fromArray($toAdd);

			$obj->save();
			$obj->free();
			
			$count++;
		}
		
		return $count;
	}

	protected static function deleteSiteDetails($model, $subsiteId = null, $admin = false){
		$tableName = Doctrine_Core::getTable($model)->getTableName();
		if($admin){
			return self::rmsTruncateTable($tableName);
		}else{
			$query = Doctrine_Query::create()->delete("$model m");
			$query->where('m.admin_subsite_id =?', $subsiteId);
			return $query->execute();
		}
	}

	public static function rmsTruncateTable($table){ 
		$doctrine = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh(); 
		$stmt     =  $doctrine->prepare("SET foreign_key_checks = 0; ALTER TABLE $table DISABLE KEYS ; TRUNCATE TABLE $table ; ALTER TABLE $table ENABLE KEYS ; SET foreign_key_checks = 1;");
		$done     =  $stmt->execute();	

		return $done ;
	}

	public static function isUniqueEmail($email){

	}
	/*
	 * new design starts here methods reviewd would be added below
	 */
	public function getModelName(){
		return get_class($this);
	}

	public function __toString(){
		try{
			return $this->title;
		}catch(Exception $e){
			return $this->id;
		}
	}


	/**
	 * Sets the model filter and returns the query object
	 * @param Rhema_Dao_Filter $filter
	 * @return Doctrine_Query
	 */
	protected function _setFilters(Rhema_Dao_Filter $filter = null){
		$service = new Rhema_Model_Service();
		return $service->createQuery($filter);
	}

	/**
	 * Data required from man source
	 * @param unknown_type $functionName
	 * @param unknown_type $args
	 */
	public static function getRemoteData($functionName, $args){ 
		$cacheLifetime = ($functionName == 'validate') ? 43200 : 10080 ; // 1 week
		$cacheId       = 'remote_cache_' . md5($functionName . '_' .implode('',$args)) ;		
		$cache         = Rhema_Util::getDefaultCacheObject($cacheLifetime); 
		$data          = array();
		
		if(!$data = $cache->load($cacheId)){
			
			if(Rhema_Util::isHomeDomain() or !Rhema_Util::isOnline()){
	  			$server = new Admin_Service_Server();
	  			$data = call_user_func_array(array($server, $functionName), $args);
			}else{
				$client = Rhema_Util::getRemoteClient();
				
				$client->$functionName();
				
				foreach($args as $index => $arg){
					$prefix = ($index == 0) ? 'arg' : 'arg' . $index;					 
					$client->{$prefix}($arg);
				}
				try{
					$resp   = $client->post(); 
					$data   = self::getRestResult($resp, $functionName); 
				}catch(Exception $e){
					if(Rhema_SiteConfig::isDev()){
						pd($e->getMessage(), array($functionName => $args));
					}
				}
				
			}
			
			if($data){
				$cache->save($data, $cacheId, array(Rhema_Constant::REMOTE_DATA_CACHE));
			}
		}	
		
		return $data ;
	}
}