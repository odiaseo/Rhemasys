<?php
 
	
	class Rhema_Ajax_Responce { 
		
		public $params = array();
		protected $_bootstrap;
		public $url;
		
		
		public function __construct($url, $bootstrap){ 
			$parms            = array();
			$this->url	      = $url;
			$this->_bootstrap = $bootstrap;
			
			$parts		= parse_url($url);	
			if(isset($parts['query'])){		
				parse_str($parts['query'], $parms);	
			} 
			$this->params = array_merge($parms, $_REQUEST);
		}
		
		 
		public function process(){
			$output		= '';

			
			$ajaxMethod = $this->get('task');
			$table      = $this->get('table');
			 
			switch($ajaxMethod){ 
				
				case 'grid-editurl':{
					
					break;
				}
				
				case 'template-sections':{
					
					break;
				}
				
				case 'template-fields':{
					
					break;
				}
				
				case 'help-layout':{
					
					break;
				}
				
				case 'page-preview':{
					
					break;
				}
				
				case 'page-layout':{
					
					break;
				} 
				case 'dir-list':{ 
			    	$dirOnly = $this->get('dir') == 1 ? true : false;
			    	$actions = $this->get('act') == 1 ? true : false;
			    	$module  = $this->get('m', null);
			    	$contr   = $this->get('c',null);    	
			    	
			    	if($module){
			    		$path		= '/modules/' . $module . '/views/scripts';
			    	}
			    	
			    	if($contr){
			    		$path      .= '/' . $contr;
			    	}
			    	
			    	$arr	 = Rhema_Util::getDir($path, $dirOnly, $actions, false);			    	
			    	$output  = Zend_Json::encode($arr);					
					
					break;
				}
				
				case 'list-modules':{
					
					break;
				}
				
				case 'list-actions':{
					
					break;
				}
				
				case 'clear_cache':{
					$cachePath 	= realpath(APPLICATION_PATH . '/../sites/' . SITE_DIR . '/cache');
					$count      = self::cleanupDirectory($cachePath);
					
					$output     = $count. ' files deleted successfully';
					break;
				}
			 
				default:
			}
			
			return $output;
		}
		
		public function get($item, $default = null){
			return isset($this->params[$item]) ? $this->params[$item] : $default;
		}
		
		public static function cleanupDirectory($dir, $count = 0) { 
		    foreach (new DirectoryIterator($dir) as $file) {
		        if ($file->isDir()) {
		            if (! $file->isDot()) {
		                self::cleanupDirectory($file->getPathname(), $count);
		            }
		        } else {
		        	$count++;
		            unlink($file->getPathname());
		        }
		    }
		    rmdir($dir);
		    return $count;
		}
		

	
	}