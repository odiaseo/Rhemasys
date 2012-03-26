<?php
/**
 *
 * @author Pele
 * @version
 */

/**
 * IncludeJs helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Rhema_View_Helper_IncludeJs extends Zend_View_Helper_Abstract {
	
	protected static $_scriptPath = '';
	public static $mergeFilename  = false;
	public static $minFilename    = false;	
	public static $docRoot        = false;
	public static $mergeList      = array ();
	
	public function __construct() {
		self::$_scriptPath  = Rhema_Constant::getBackendPath() . 'scripts/'; 		
		$revision      		= Rhema_View_Helper_IncludeCss::getRevisionNumber();
 		$mergeFilename 		= "script-{$revision}.js";
 		$minFilename   		= "script-{$revision}.min.js"; 
 		
 		self::setMergeFilename($mergeFilename);
 		self::setMinFilename($minFilename);
 		self::$docRoot    = Zend_Controller_Front::getInstance()->getRequest()->getServer('DOCUMENT_ROOT');
	}
	
	/**
	 * @return the $mergeFilename
	 */
	public static function getMergeFilename() {
		if(!self::$mergeFilename){
			$revision      			= Rhema_View_Helper_IncludeCss::getRevisionNumber();
 			self::$mergeFilename 	= "script-{$revision}.js"; 
		}
		return Rhema_View_Helper_IncludeJs::$mergeFilename;
	}

	/**
	 * @return the $minFilename
	 */
	public static function getMinFilename() {
		if(!self::$minFilename){
			$revision      			= Rhema_View_Helper_IncludeCss::getRevisionNumber(); 
 			self::$minFilename   	= "script-{$revision}.min.js"; ; 
		}
		return Rhema_View_Helper_IncludeJs::$minFilename;
	}

	/**
	 * @param field_type $mergeFilename
	 */
	public static function setMergeFilename($mergeFilename) {
		Rhema_View_Helper_IncludeJs::$mergeFilename = $mergeFilename;
	}

	/**
	 * @param field_type $minFilename
	 */
	public static function setMinFilename($minFilename) {
		Rhema_View_Helper_IncludeJs::$minFilename = $minFilename;
	}

	public function includeJs($list = '', $pos = Rhema_Constant::APPEND, $merge = true) {
		$list  = ( array ) $list;
		$debug = Rhema_SiteConfig::getConfig('settings.debug_merge');		
		foreach ( $list as $file ) { 
			if(strpos($file, ':')  === false){
				$file     = str_replace(self::$_scriptPath, '', $file);
				$fullpath = self::$_scriptPath . $file;
			}else{
				$fullpath = $file ;
			}
			if($merge){
				self::addToMergeList($fullpath); 
			}else{
				$path = Rhema_SiteConfig::getBackendScriptsPath() . $file;
				$this->view->headScript()->appendFile($path);
			}
		}
		
		return null;
	}
	/**
	 * @return the $_scriptPath
	 */
	public function getScriptPath() {
		return self::$_scriptPath;
	}
	
	/**
	 * @param field_type $_scriptPath
	 */
	public function setScriptPath($_scriptPath) {
		self::$_scriptPath = $_scriptPath;
	}
	
	public function addToMergeList($file, $pos = Rhema_Constant::APPEND) {
		if (! in_array ( $file, self::$mergeList )) {
			if ($pos == Rhema_Constant::APPEND) {
				self::$mergeList [] = $file;
			} else {
				array_unshift ( self::$mergeList, $file );
			}
		}
	}
	/**
	 * @return the $mergeList
	 */
	public static function getMergeList() {
		return Rhema_View_Helper_IncludeJs::$mergeList;
	}
	
	/**
	 * @param field_type $mergeList
	 */
	public static function setMergeList($mergeList) {
		Rhema_View_Helper_IncludeJs::$mergeList = $mergeList;
	}
	
	public static function mergeFiles(array $list = null, &$minStr = null){
		$content = array();
		$content[] = '/*** last modified ' . date('r');
		$content[] = PHP_EOL . implode(PHP_EOL, $list) . PHP_EOL;
		$content[] = ' ***/'; 
		 
		$minData   = array();
		foreach($list as $file){			
			$str       = file_get_contents($file);
			if($str){
				$content[] = '/*** ' . $file . ' ***/';
				$content[] =  trim("$str") ; 
				$content[] = "\n" ;
				//$minData[] = Rhema_Jsmin::minify($str);
			}
		}
		//$minStr = implode(' ',$minData);
		return implode(PHP_EOL, $content);
	}
	
	public static function printJs(){    
		list($theme, $context) = Rhema_Util::getThemeContext();	
		if($context == CONTEXT_SITE){
			$dir  = Rhema_Constant::getSiteRoot() . $theme . '/merged/' ;
		}else{
			$dir  = Rhema_Constant::getBackendPath() .  'merged/' ;
		}
		
		$file 		= $dir . "{$context}-" . self::getMergeFilename();
		$minFile    = $dir . "{$context}-" . self::getMinFilename();
		$view 		= Zend_Layout::getMvcInstance()->getView();
		$minData    = '';
		$debug 		= Rhema_SiteConfig::getConfig('settings.debug_merge');
		//$debug = 1;
		
		if($debug or !file_exists($file)){
			if(!file_exists($dir)){
				@mkdir($dir, 0777, true);
			}
 
			$contextJsList  = Rhema_Constant::getJssList($context, $theme);   
			 
			if(CONTEXT_ADMIN == $context){
				$path = Rhema_Constant::getBackendPath() ;
 
				self::includeJs(array(
					$path . 'scripts/jpicker/jpicker-1.1.6.min.js',
					$path . 'scripts/global.js',
					$path . 'scripts/rhemasys.js',
					$path . 'scripts/tool.js'
				));
			}else{			
				$siteScriptPath = Rhema_Constant::getSiteRoot() . $theme . '/scripts'; 
				$iterator       = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($siteScriptPath), RecursiveIteratorIterator::CHILD_FIRST);
				foreach($iterator as $filename => $path){
					if(substr($filename, -3) == '.js'){
						$contextJsList[] = $filename ; 
					}
				}
			}

			$appJsList  = array_merge_recursive($contextJsList, Rhema_View_Helper_IncludeJs::getMergeList());			 
			$data       = self::mergeFiles($appJsList, $minData) ;
			
			file_put_contents($file, $data);
			chmod($file, 0777);
						
			file_put_contents($minFile, $minData);
			chmod($minFile, 0777);
		}
		
		if(CONTEXT_ADMIN == $context){
			$backendPath = Rhema_SiteConfig::getBackendPath();
			$view->headScript()->appendFile($backendPath . 'editors/ckeditor/ckeditor.js');
			$view->headScript()->appendFile($backendPath . 'editors/ckeditor/adapters/jquery.js');			
		}
 		  
	    $minPath    = Rhema_SiteConfig::getMinimizePath($theme, $context) . basename($file); 		
		$view->headScript()->prependFile($minPath); 
		
	}
	
	public static function addMergeList(array $files, $filename, $placement = 'APPEND'){
		$backendPath = Rhema_SiteConfig::getBackendPath('merged/');
		$dirPath     = Rhema_Constant::getBackendPath() . 'merged/';
		$view 		 = Zend_Layout::getMvcInstance()->getView();
		
		if(!file_exists($dirPath. $filename)){			
			$str = self::mergeFiles($files);
			file_put_contents($dirPath . $filename, $str);
		}
/*		$list = (array) Zend_Registry::get(Rhema_Constant::PRE_QUERY_KEY);
		$list[] = $backendPath . $filename;
		Zend_Registry::set(Rhema_Constant::PRE_QUERY_KEY, $list);
		*/
		$view->jQuery()->addJavascriptFile($backendPath . $filename);
/*		if($placement == 'APPEND'){
			$view->headScript()->appendFile($backendPath . $filename);
		}else{
			$view->headScript()->prependFile($backendPath . $filename);
		}*/
	}
}
