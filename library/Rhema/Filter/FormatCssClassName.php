<?php
/**
 * Removes illegal characters 
 * for dislay Css classnames and IDs
 * @author Pele
 *
 */
class Rhema_Filter_FormatCssClassName implements Zend_Filter_Interface{
	public function filter($value){
		$class = preg_replace('/(\s+|_|[^a-z0-9])/i','-', $value);
		return strtolower($class); 
	}
}