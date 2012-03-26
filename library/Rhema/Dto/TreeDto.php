<?php
 class Rhema_Dto_TreeDto {
 	
 	protected $raw        = false; #doctrine tree object
 	protected $moduleData = false; #data of the current module
 	protected $roots      = false; #alt roots in the current tree
 	protected $menus      = false ; #processed menus for disply
 	protected $container  = false ; #Zend navigation container for menus
 	protected $modules    = false;
 	protected $tableList  = false;
 	
 	public function __construct(){
 		$moduleObject     = Rhema_Model_Service::factory('AdminModule');
 		$this->moduleData = new $moduleObject();
 	}
 
	/**
	 * @return the $raw
	 */
	public function getRaw() {
		return $this->raw;
	}

	/**
	 * @return the $moduleData
	 */
	public function getModuleData() {
		return $this->moduleData;
	}

	/**
	 * @return the $roots
	 */
	public function getRoots() {
		return $this->roots;
	}

	/**
	 * @return the $menus
	 */
	public function getMenus() {
		return $this->menus;
	}

	/**
	 * @return the $container
	 */
	public function getContainer() {
		return $this->container;
	}

	/**
	 * @param field_type $raw
	 */
	public function setRaw($raw) {
		$this->raw = $raw;
		return $this;
	}

	/**
	 * @param field_type $moduleData
	 */
	public function setModuleData($moduleData) {
		$this->moduleData = $moduleData;
		return $this;
	}

	/**
	 * @param field_type $roots
	 */
	public function setRoots($roots) {
		$this->roots = $roots;
		return $this;
	}

	/**
	 * @param field_type $menus
	 */
	public function setMenus($menus) {
		$this->menus = $menus;
		return $this;
	}

	/**
	 * @param field_type $container
	 */
	public function setContainer($container) {
		$this->container = $container;
		return $this;
	}
	/**
	 * @return the $modules
	 */
	public function getModules() {
		return $this->modules;
	}

	/**
	 * @param field_type $modules
	 */
	public function setModules($modules) {
		$this->modules = $modules;
	}
	/**
	 * @return the $tableList
	 */
	public function getTableList(){
		return $this->tableList;
	}

	/**
	 * @param field_type $tableList
	 */
	public function setTableList($tableList){
		$this->tableList = $tableList;
	}



 }