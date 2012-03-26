<?php
class Rhema_Grid_JqgParam extends Rhema_Cache_Abstract{
	
	const SORT_ID       = 'sidx';
	const SORT_ORDER_ID = 'order';
	const SEARCH_ID     = 'search';
	const ND_ID		    = 'nd';
	const OPERATOR_KEY  = 'oper';
	const OPER_EDIT     = 'editoper';
	const OPER_ADD      = 'addoper';
	const OPER_DELETE   = 'deloper';
	const SUBGRID_ID    = 'subgridid';
	const TOTALROWS_ID  = 'totalrows';
	const SORT_COLUMN   = 'title';
	const TOP			= 'top';
	const BOTTOM        = 'bottom';
	const RECORD_LIMIT  = 'rows';
	const CURRENT_PAGE  = 'page';
	const CURRENT_ROW   = 'id';
	const ROW_TOTAL     = 'totalrows';
	
	const GRID_PREFIX           = 'jqg_';
	const TOOLBAR_PREFIX_BUTTOM = 'tb_';
	const TOOLBAR_PREFIX_TOP    = 't_';
	const TOOLBAR_PREFIX_MENU   = 'menu_';
	const TOOLBAR_PREFIX_BUTTON = 'btn_';
		
	public $caption  	= 'Test Title';
	public $forceFit 	= 0;
	public $rowList  	= array(2, 5, 10, 15, 25, 50, 100, 250, 500);
	public $altRows 	= true;	
	public $autowidth   = 1;
	public $editurl  	= '';	
	public $footerrow 	= false;
	public $gridview    = true;
	public $mtype       = 'post';
	public $multiselect = true; 
	public $rownumbers  = true;
	public $sortname    = self::SORT_COLUMN ;
	public $rowNum      = Rhema_Dao_Filter::QUERY_LIMIT ;
	public $toolbar     = array(true, 'both');
	public $toppager    = true ;
	public $url         = '' ; 
	//public $rowTotal    = 2000;
	//public $loadonce    = 1 ;
	//public $scroll      = 0;
	public $colModel    = array();
	//public $width       = 870;
	public $height      = 'auto';
	public $viewrecords = true;
	public $i18n        = '';
	public $userData    = array();
	public $viewsortcols = array(false, 'vertical', true);
	 
	private $prmNames   = array(
			self::SORT_ID          => 'sidx',
			self::SORT_ORDER_ID    => 'sord',
			self::SEARCH_ID        => '_search',
			self::ND_ID		       => 'nd',
			self::OPERATOR_KEY     => 'oper',
			self::OPER_EDIT        => 'edit',
			self::OPER_ADD         => 'add',
			self::OPER_DELETE      => 'del',
			self::SUBGRID_ID       => 'subgridid',
			self::TOTALROWS_ID     => 'totalrows',
			self::RECORD_LIMIT     => 'rows',
			self::CURRENT_PAGE     => 'page',
			self::CURRENT_ROW      => '_id_'
	);
 	
	public function __construct(){ 		
		$this->view	= Zend_Layout::getMvcInstance()->getView();
	}
	/**
	 * @return the $_params
	 */
	public function getParams($options = array()){ 
		$params                     = array_merge(get_class_vars(__CLASS__), (array) $options);			
		$params['prmNames'] = (object) $params['prmNames'] ;
		return $params ;
	}
 
	/**
	 * @return the $prmNames
	 */
	public function getPrmNames() {
		return $this->prmNames;
	}

	/**
	 * @param field_type $prmNames
	 */
	public function setPrmNames($prmNames) {
		$this->prmNames = $prmNames;
		return $this;
	}
	/**
	 * @return the $colModel
	 */
	public function getColModel($model) {
		if(empty($this->colModel)){
			$option['model'] = $model ;
			$adapter         = new Rhema_Grid_Adapter_DoctrineModel($option);
			$params          = $adapter->getColumnModel();
			foreach($params as $key => $val){ 
				$result[$key] =  (object) $this->filterParams((array)$val);
			}
			$this->caption   = $adapter->getTableTitle();
			$this->colModel  = $result ;
		}
		return $this->colModel;
	}

	/**
	 * @param field_type $colModel
	 */
	public function setColModel($colModel) {
		$this->colModel = $colModel;
		return $this;
	}

	public function filterParams($item){	
		$result = array();	
		foreach($item as $key => $value){
			if(is_array($value)){
				$return = $this->filterParams($value);
				if($return){
					$result[$key] = $return ;
				}
			}elseif(is_object($value)){
				$return = $this->filterParams((array) $value);
				if($return){
					$result[$key] = (object) $return ;
				}
			}elseif($value !== null){
				$result[$key] = $value;
			}
		}
		return $result ;
	}
	
	/**
	 * Generates buttons to be added to the grid toolbars
	 * Settings are set in grid.ini
	 * @param string $model
	 * @param string $gridId
	 * @return Ambigous <multitype:, string>
	 */
	public function getToolbarButtons($model, $tableId, $gridId, $treeGrid = false){
		//$toolbarButtons[] = "var grid = jQuery('#{$gridId}').jqGrid()[0];";	
		$toolbarButtons   = array();
		$table            = Doctrine_Core::getTable($model)->getTableName();
		$this->table	  = $table;
		$buttons          = (array) Rhema_SiteConfig::getConfig('grid.toolbar_buttons');
		$tableButtons     = (array) Rhema_SiteConfig::getConfig("grid.{$this->table}.buttons"); 
		
		if($treeGrid){
			$tableButtons[] = 'update-acl';
		}
		
		if(count($tableButtons)){
		//	$toolbarButtons[] = 'jQuery(document).ready(function() {';	 
			$toolbarButtons[] = Rhema_Grid_ButtonScript::getErrorDialogScript(); 
			$object 		  = new Rhema_Grid_ButtonScript();	
					
			$options          = array(
				'gridId'		=> $gridId, 
			    'tableId'		=> $tableId,
			    'menuId'    	=> self::TOOLBAR_PREFIX_MENU . $tableId , 
			    'buttonId'      => self::TOOLBAR_PREFIX_BUTTON . $tableId 
			);
			
			foreach($tableButtons as $btn){
				$prefix  = (isset($buttons[$btn]['position']) and $buttons[$btn]['position'] == self::TOP) 
							? self::TOOLBAR_PREFIX_TOP 
							: self::TOOLBAR_PREFIX_BUTTOM ;
				$options['alone']       = (isset($buttons[$btn]['stand_alone']) and $buttons[$btn]['stand_alone']);
				$options['toolbarId']   = $prefix . $tableId ;	
				$options['buttonTitle'] = $buttons[$btn]['title'] ;			
				$options['postUrl'] 	= $this->_getUrl($buttons[$btn]['post_url']);  
				$options['link'] 		= $this->_getUrl($buttons[$btn]['link_1']);
				$options['buttonId']    = self::TOOLBAR_PREFIX_BUTTON . $tableId  . '_' . $btn;
							
				$toolbarButtons[]    	= $object->getScript($btn, $options);
			}
		//	$toolbarButtons[] = '});';
		}
		return implode(PHP_EOL, $toolbarButtons) ;
	}
	
	private function _getUrl($routeName){
		if($routeName and Zend_Controller_Front::getInstance()->getRouter()->hasRoute($routeName)){
			$url = $this->view->url(array('table' => $this->table), $routeName);
		}else{
			$url = '#';
		}
		
		return $url ;
	}
	/**
	 * @return the $caption
	 */
	public function getCaption() {
		return $this->caption;
	}

}