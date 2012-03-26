<?php
class Rhema_Grid_Column_SearchOption extends Rhema_Grid_Column_EditOption{
	const EQUAL = 'eq';
	const NOT_EQUAL = 'ne';
	const LESS_THAN = 'lt';
	const LESS_OR_EQUAL = 'le';
	const GREATER_THAN = 'gt';
	const GREATER_OR_EQUAL = 'ge';
	const BEGINS_WITH = 'bw';
	const NOT_BEGIN_WITH = 'bn';
	const IS_IN = 'in';
	const NOT_IN = 'ni';
	const ENDS_WITH = 'ew';
	const NOT_END_WITH = 'en';
	const CONTAINS = 'cn';
	const NOT_CONTAIN = 'nc';
	const IS_NULL     = 'isnull';
	const NOT_NULL    = 'nnull';
	/**
	 * attr is object where we can set valid attributes to the created element. 
	 * By example:
	 * attr : { title: “Some title” }
	 * Will set a title of the searched element
	 * @var unknown_type
	 */
	public $attr = null;
	/**
	 * By default hidden elements in the grid are not searchable . 
	 * In order to enable searching when the field is hidden set this option to true
	 * @var unknown_type
	 */
	public $searchhidden = null;
	/**
	 * This option is used only in advanced single field searching and determines the 
	 * operation that is applied to the element. If not set all the available options 
	 * will be used. All available option are:
	 * ['eq','ne','lt','le','gt','ge','bw','bn','in','ni','ew','en','cn','nc']
	 * The corresponding texts are in language file and mean the following:
	 * ['equal','not equal', 'less', 'less or equal','greater','greater or equal', 'begins with',
	 * 'does not begin with','is in','is not in','ends with','does not end with','contains','does 
	 * not contain']
	 * Note that the elements in sopt array can be mixed in any order.
	 * @var unknown_type
	 */
	public $sopt = null;
	
	public $showQuery = true ;
	
	public $multipleSearch = true ;
	
	public $multipleGroup = true ;
 
}