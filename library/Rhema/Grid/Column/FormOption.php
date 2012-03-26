<?php
class Rhema_Grid_Column_FormOption extends Rhema_Grid_Option{
	/**
	 * if set, a text or html content appears before the input element
	 * @var unknown_type
	 */
	public $elmprefix = null;  
	/**
	 * if set, a text or html content appears after the input element
	 * @var unknown_type
	 */
	public $elmsuffix = null;
	/**
	 * if set, this replace the name from colNames array that appears as label in the
	 * form.
	 * @var unknown_type
	 */
	public $label = null;
	/**
	 * determines the row position of the element (again with the text-label) in the form; 
	 * the count begins from 1
	 * @var unknown_type
	 */
	public $rowpos = null;
	/**
	 * determines the column position of the element (again with thelabel) in the form
	 * beginning from 1 
	 * @var unknown_type
	 */
	public $colpos = null;
}
