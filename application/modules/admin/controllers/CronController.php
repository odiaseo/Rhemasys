<?php 
class Admin_CronController extends Zend_Controller_Action{
	/**
	 * Display text on the console if verbose mode is On
	 * @param string $msg
	 */
	public $verbose;
	
	/**
	 * @return the $verbose
	 */
	public function init(){
		
	}
	
	public function getVerbose() {
		return $this->verbose;
	}

	/**
	 * @param field_type $verbose
	 */
	public function setVerbose($verbose) {
		$this->verbose = $verbose;
	}

	private function _displayIfVerbose($msg, $newLine = "\n")
	{
		if ($this->verboseMode) {
			echo $msg . $newLine;
		}
	}
}
