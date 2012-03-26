<?php
/**
 * A representation of the jqGrid  
 * @author Pele
 *
 */
class Rhema_Grid_Column_Model extends Rhema_Grid_Option{
	
	public $align 			= null;
	public $classes 		= null;
	public $datefmt 		= null;
	public $defval  		= null ;
	public $editable 		= true;
	public $editoptions 	= null ;
	public $editrules   	= null;
	public $edittype 		= null;
	public $firstsortorder 	= null;
	public $fixed 			= null;
	public $formoptions     = null ;
	public $formatoptions   = null;
	public $formatter 		= null;
	public $hidden 		    = null;
	public $hidedlg 		= null;
	public $index 			= null;
	public $jsonmap 		= null;
	public $key 			= null;
	public $label 			= null;
	public $name			= null;
	public $resizable		= null;
	public $search			= null;
	public $searchoptions	= null;
	public $sortable 		= null;
	public $sorttype		= null;
	public $stype			= null;
	public $surl			= null;
	public $title			= null;
	public $width			= null;
	public $xmlmap			= null;
	public $unformat		= null;
	public $viewable		= null;
	
	public function __construct(){
		$this->editoptions   = (object) new Rhema_Grid_Column_EditOption();
		$this->searchoptions = (object) new Rhema_Grid_Column_SearchOption();
		$this->editrules     = (object) new Rhema_Grid_Column_EditRule();
		$this->formoptions   = (object) new Rhema_Grid_Column_FormOption();
	}
}