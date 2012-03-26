<?php
class Rhema_Grid_NavOption{
	protected $control = array(
			'view' 			=> true, 
			'refresh' 		=> true, 
			'search' 		=> true, 
			'edit' 			=> true, 
			'del' 			=> true, 
			'add' 			=> true,
			'cloneToTop' 	=> true,		
	);
	protected $edit = array(
			'reloadAfterSubmit' => true, 
			'jqModal' 			=> true, 
			'closeOnEscape' 	=> false, 
			'recreateForm' 		=> true, 
			//'modal' => true, 
			//'checkOnSubmit' => true, 
			'bottominfo ' 		=> 'Fields marked with (*) are required');
	
	protected $add = array(
			'reloadAfterSubmit' => true, 
			'jqModal' 			=> true, 
			'closeOnEscape' 	=> false, 
			//'modal' => true,  // bug with using ckeditor if set true
			'recreateForm' 		=> true, 
			'checkOnSubmit' 	=> true, 
			'closeAfterAdd' 	=> true,
			'bottominfo '		=> 'Fields marked with (*) are required');
	
	protected $search = array(
			'closeOnEscape' 	=> false, 
			'sFilter' 			=> 'filters', 
			'multipleSearch' 	=> true);
	
	protected $view = array(
			'reloadAfterSubmit' => true, 
			'jqModal' 			=>true, 
			'modal' 			=> true, 
			'jqModal' 			=> true,
			'closeOnEscape' 	=> false);
	
	protected $del = array(
			'height' => 'auto');
	
	public function __construct($options = array()){
		$vars = get_class_vars(__CLASS__);
		foreach($vars as $name => $value){
			if(isset($options[$name])){
				$this->$name = array_merge($value, (array) $options[$name]);
			}
		}
	}
	
	/**
	 * These functions are performed by the Grid as triggered by the events
	 * @param unknown_type $gridId
	 */
	private function _mergeJsonExpressions($gridId){ 
		$str   = Zend_Json::encode(Rhema_Grid_Adapter_DoctrineModel::$spinElements);
		//$str   = Zend_Json::encode(array());
		$array = array(
				'errorTextFormat' 		=> new Zend_Json_Expr("function(e){return rms.formatErrorMsg(e);}"), 
				'beforeShowForm' 		=> new Zend_Json_Expr("function(f){ rms.prepareForm(f,'#{$gridId}', '$str');}"), 
				'afterclickPgButtons' 	=> new Zend_Json_Expr("function(b,f,r){ rms.doAfterButtonClick(b,f,r,'#{$gridId}');}"),
			 	'afterSubmit' 			=> new Zend_Json_Expr("function(r,d){ return rms.showTaskStatus(r,d); }"),
				'onClose'           	=> new Zend_Json_Expr("function(f){rms.cleanUpForm(f);}")
		);
		
		$this->edit = array_merge($this->edit, $array);
		$this->add  = array_merge($this->add, $array);
	}
	
	public function getParams($gridId){
		$order = array(
				'control', 
				'edit', 
				'add', 
		        'del',  
				'search', 
				'view');
		$this->_mergeJsonExpressions($gridId);
		$options['enableJsonExprFinder'] = true;
		foreach($order as $key){
			if($key == 'control'){
				foreach($this->$key as $k => $v){
					$v = ($v == "true" or $v) ? true : false ;
					$items[$k] = $v;
				}
			}else{
				$items = $this->$key ;
			}
			$items    = (object) $items ; 
			$return[] = Zend_Json::encode($items, false, $options);
		}
		return $return;
	}

}