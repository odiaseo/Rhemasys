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
class Rhema_View_Helper_IncludeCss extends Zend_View_Helper_Abstract {
 
	protected static $_scriptPath = '';
	public static $mergeList      = array ();
	public static $mergeFilename  = false;
	public static $minFilename    = false;
	public static $currentFile    = '';
	public static $record = array(); 
	public static $util   = null ;


	public function __construct(){
 		$revision      = self::getRevisionNumber();
 		$mergeFilename = "style-{$revision}.css";
 		$minFilename   = "style-{$revision}.min.css";
 		
 		self::$_scriptPath = Rhema_SiteConfig::getBackendPath();
 		self::setMergeFilename($mergeFilename);
 		self::setMinFilename($minFilename);
	}
	
	public static function getRevisionNumber(){
	 	$filename  = SITE_PATH . '/../backend/.rvn';
 		if(file_exists($filename)){
 			$revision  = (int)file_get_contents($filename);
 		}else{
 			$dir       = realpath(getcwd() .  escapeshellarg("/../../"));
 			$version   = passthru("svnversion $dir",$return);
 			$revision  = (int) $return;
 		}	
 		
 		$number = str_pad($revision, 4, '0', STR_PAD_LEFT);
 		return $number ;	
	}
	/**
	 * @return the $mergeFilename
	 */
	public static function getMergeFilename() {
		if(!self::$mergeFilename){
 			$revision      = self::getRevisionNumber();
 			self::$mergeFilename = "style-{$revision}.css";			
		}
		return Rhema_View_Helper_IncludeCss::$mergeFilename;
	}

	/**
	 * @return the $minFilename
	 */
	public static function getMinFilename() {
		if(!self::$minFilename){
 			$revision          = self::getRevisionNumber();
 			self::$minFilename = "style-{$revision}.min.css";				
		}
		return Rhema_View_Helper_IncludeCss::$minFilename;
	}

	/**
	 * @param field_type $mergeFilename
	 */
	public static function setMergeFilename($mergeFilename) {
		Rhema_View_Helper_IncludeCss::$mergeFilename = $mergeFilename;
	}

	/**
	 * @param field_type $minFilename
	 */
	public static function setMinFilename($minFilename) {
		Rhema_View_Helper_IncludeCss::$minFilename = $minFilename;
	}

	/**
	 * @return the $util
	 */
	public static function getUtil() {
		if(!Rhema_View_Helper_IncludeCss::$util){
			Rhema_View_Helper_IncludeCss::$util = Rhema_Util_String::getInstance();
		}
		return Rhema_View_Helper_IncludeCss::$util;
	}	
	
	public function includeCss($list = '', $merge = true) {
		$list = (array) $list ;
		foreach($list as $file){ 
			if($merge){
				self::addToMergeList($file);
			}else{
				if(strpos($file, ':')  === false){
					$fullpath = $this->getScriptPath() .  ltrim($file, '/'); 
				}else{
					$fullpath = $file;
				}
				$this->view->headLink()->appendStylesheet($fullpath); 
			}
			//$fullpath = str_replace('//', '/', $this->getScriptPath() . $file);
			//$fullpath = $this->getScriptPath() . trim($file, '/');
			//$this->view->headLink()->appendStylesheet($fullpath); 
		}
		return null;
	}
 

	public function addToMergeList($file) {
		if (! in_array ( $file, self::$mergeList )) { 
			self::$mergeList [] = $file;  
		}
	}
	/**
	 * @return the $mergeList
	 */
	public static function getMergeList() {
		return Rhema_View_Helper_IncludeCss::$mergeList;
	}

	/**
	 * @param field_type $mergeList
	 */
	public static function setMergeList($mergeList) {
		Rhema_View_Helper_IncludeCss::$mergeList = $mergeList;
	}
	/**
	 * @return the $_scriptPath
	 */
	public static function getScriptPath() {
		return Rhema_View_Helper_IncludeCss::$_scriptPath;
	}

	/**
	 * @param field_type $_scriptPath
	 */
	public static function setScriptPath($_scriptPath) {
		Rhema_View_Helper_IncludeCss::$_scriptPath = $_scriptPath;
	}
	
	public static function printCss(){
		list($theme, $context) = Rhema_Util::getThemeContext(); 
		if($context == CONTEXT_ADMIN){
			$dir  = Rhema_Constant::getBackendPath() .  'merged/' ;
		}else{
			$dir  = Rhema_Constant::getSiteRoot() . $theme . '/merged/' ;			
		}
		
		    
		$file 		= $dir . "{$context}-" . self::getMergeFilename();
		$minFile    = $dir . "{$context}-" . self::getMinFilename();
		$view 		= Zend_Layout::getMvcInstance()->getView();
		$css        = array();
		$debug 		=  Rhema_SiteConfig::getConfig('settings.debug_merge');	
		$min        = new Rhema_Cssmin();
		//$debug      = 1;
		
		if($debug or !file_exists($minFile)){
			if(!file_exists($dir)){
				@mkdir($dir, 0766, true);
			}
 
			$contextCssList  = Rhema_Constant::getCssList($context, $theme); 
			$css             = self::getMergeList();
					
			if(CONTEXT_SITE == $context){ 
				$siteScriptPath = Rhema_Constant::getSiteRoot() . $theme . '/css'; 
				
				$iterator       = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($siteScriptPath), RecursiveIteratorIterator::CHILD_FIRST);
				foreach($iterator as $filename => $path){
					if(substr($filename, -4) == '.css' and file_exists($filename)){
						$css[] = $filename ; 
					}
				}
				asort($css);
			}		
			
			$appCss  = array_merge_recursive($css, $contextCssList)	; 
			$data    = self::mergeFiles($appCss) ;
				 	
			file_put_contents($file, $data);
			chmod($file, 0777);
			$minData = $min->minify($data);
			
			file_put_contents($minFile, $minData);
			chmod($minFile, 0777);
		}
		
		
		if($debug){ 
			//$minPath    = '/summer/merged' .  basename($minFile);
			$minPath    = Rhema_SiteConfig::getMinimizePath($theme, $context) . basename($file);
		}else{
			//$minPath    = "/{$theme}/merged/" .  basename($file);
			$minPath    = Rhema_SiteConfig::getMinimizePath($theme, $context) . basename($minFile);
		}		
		 
		$view->headLink()->prependStylesheet($minPath);		 		
	}

	public static function getAbsoluteUrl($match){		 
		$file   = str_replace(array("'", '"'), '', $match[1]);
		$dir    = dirname(self::$currentFile) ;		
		if(substr($file,0,1) == '/'){
			$path   = Rhema_Constant::getPublicRoot() . $file ;
		}elseif(!$path   = realpath($dir . '/' . $file)){			
			$path   = $dir . '/' . $file ; 		 
		}
		
		$path = str_replace(array("'", '"', '//'), array('','','/'), str_replace(DIRECTORY_SEPARATOR, '/', $path));
		$res  = self::getUtil()->directoryToUrl($path); //  str_replace(self::$pattern, self::$replace, $path); 
		self::$record[$file] = $path . ' | ' . $res ;  
		
		return 'url("' . $res . '")';
	}
	
	public static function mergeFiles(array $list = null){
		$repeat  = str_repeat('=', 20) ;
		$content = array();
		$content[] = '/*';
		$content[] = PHP_EOL . implode(PHP_EOL, $list) . PHP_EOL;
		$content[] = '*/'; 

		foreach($list as $file){			
			$str       = file_get_contents($file);
			self::$currentFile = $file ;
			if($str){
				$res       = preg_replace_callback('/url\(([^\(\)]+)\)/i', array(new self(), "getAbsoluteUrl"), $str);
				$content[] = '/*' . $repeat . $file . $repeat . '*/';
				$content[] = self::getUtil()->correctEncoding($res) ; 
				$content[] = PHP_EOL ;
			}
		}
		//pd(self::$record);
		return implode(PHP_EOL, $content);
	}
	
	public static function addMergeList(array $files, $filename){
		$backendPath = Rhema_SiteConfig::getBackendPath('merged/');
		$dirPath     = Rhema_Constant::getBackendPath() . 'merged/';
		$view 		 = Zend_Layout::getMvcInstance()->getView();
		
		if(!file_exists($dirPath. $filename)){			
			$str     = self::mergeFiles($files);
			$min     = new Rhema_Cssmin();
			$minData = $min->minify($str);
			file_put_contents($dirPath . $filename, $minData);
		}
		$view->headLink()->appendStylesheet($backendPath . $filename);
	}
}
