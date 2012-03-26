<?php
class Rhema_Adapter_Smarty_View extends Zend_View_Abstract
{
	protected $_smarty;

	public function __construct($config){	

		require_once (realpath($config->smarty_dir.'Smarty.class.php'));
		
		$this->_smarty  = new Smarty();
		
		$this->_smarty->template_dir 		= $config->template_dir;
		$this->_smarty->compile_dir 		= $config->compile_dir;
        $this->_smarty->caching 			= $config->caching;
        $this->_smarty->compile_check 		= $config->compile_check;
        $this->_smarty->plugins_dir 		= array(realpath($config->smarty_dir . 'plugins'),
        											realpath($config->plugins_dir));		
	}

	public function getEngine()
	{
		return $this->_smarty;
	}

	public function __set($key, $val)
	{
		$this->_smarty->assign($key, $val);
	}
	
	public function __get($key)
	{
		return $this->_smarty->get_template_vars($key);
	}

	public function __isset($key)
	{
		return $this->_smarty->get_template_vars($key) != null;
	}

	public function __unset($key)
	{
		$this->_smarty->clear_assign($key);
	}

	public function assign($spec, $value=null)
	{
		if (is_array($spec)) {
			$this->_smarty->assign($spec);
			return;
		}
		$this->_smarty->assign($spec, $value);
	}

	public function clearVars()
	{
		$this->_smarty->clear_all_assign();
	}

	public function render($name)
	{
		return $this->_smarty->fetch(strtolower($name));
	}

	public function _run() {}
}