<?php
class Rhema_Filter_FormatModelName implements Zend_Filter_Interface{
	public function filter($value){
		$filter = new Zend_Filter_Word_UnderscoreToCamelCase();
		$model  = preg_replace ('/(^(' . ADMIN_PREFIX . '))|(_id|crumb|_crumb)$/i', '', $value);
		return ADMIN_PREFIX . $filter->filter($model);	 
	}
	
	public function reverse($value){ 
		$model  = preg_replace ('/(^(' . ADMIN_PREFIX . '))|(_id|crumb)$/i', '', $value);
		$field  = strtolower($model) . '_id';
		return $field; 
	}	
}