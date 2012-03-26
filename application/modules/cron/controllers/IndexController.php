<?php

class IndexController extends Zend_Controller_Action{
	private $_params;
	private $_domain ;
	private $_siteConfig;
	
 
	public function init(){
 
       	$identity = Rhema_Constant::$importUser; 
        Zend_Auth::getInstance()->getStorage()->write($identity );  

        //$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		div("Cron path detected");
		$front        = Zend_Controller_Front::getInstance();            

        	
        $cacheManager = $front->getParam('bootstrap')->getPluginResource('cacheManager' )->getCacheManager();
        $defaultCache = $cacheManager->getCache('default' );
        $backend      = $defaultCache->getBackend() ;
        
        if(method_exists( $backend, 'setCacheDir')){
        	$cacheDirs 	  = array('cache/cron', 'cache/functions');
        	$cacheDirs    = Rhema_Util::createSiteDirectory($cacheDirs );
        	$backend->setCacheDir($cacheDirs[0]);
        }
        Zend_Registry::set('cache-manager', $cacheManager );        	
        	
        div("Cron cache directory found");
        			
		$params        = $front->getParam('params');
		$this->verbose = $front->getParam('verbose');
		 
		parse_str($params, $this->_params); 
		$domain        = $front->getParam('servername');
		
		div("validating domain license");
		$this->_siteConfig  = Rhema_Util::validateLicense($domain, SITE_DIR);
		
		if(!$this->_siteConfig){
			div("Invalid domain {$domain} with directory " . SITE_DIR);
			exit();
		}
		
		div("valid domain found : ({$domain})");
		
		$this->_domain = $domain;	 
	}
	
	public function indexAction(){

	}
 
	public function cacheAction(){
		 
		$filter    = new Zend_Filter_RealPath();
		$task      = isset($this->_params['task']) ? $this->_params['task'] : 'info';
		$util      = Rhema_Util::getInstance();
		div(array('calling controller action : ' . __FUNCTION__ , ' task' => $task));
		
				
		switch($task){
			case 'all':{
				$manager   = Doctrine_Manager::getInstance(); 
				$file      = $filter->filter(APPLICATION_PATH . "/../sites/" .  SITE_DIR . "/file.db");
				if(file_exists($file)){
					rename($file, "$file-bak");
					if(unlink("$file-bak")){
						div("$file deleted");
					}
				 				 
					$tableName   = SITE_DIR . 'QueryCache';
		            $cacheConn   = $manager->getConnection('sqlite' );
		            $cacheDriver = new Doctrine_Cache_Db(array('connection' => $cacheConn, 'tableName' => $tableName) );
	 
	                try{
	                     $cacheDriver->createTable();
	                     div("$file created succesffuly");
	                }catch(Exception $e){
	                	 div($e->getMessage());
	                     echo "\nUnable to create sqlite cache table";
	                }
				}				
   
				foreach(array('cache', 'stat') as $c){;
					$cachePath = $filter->filter(APPLICATION_PATH . '/../sites/' . SITE_DIR . "/$c");
					if(PHP_OS == 'WINNT'){
						@rmdir( "$cachePath-bak");
						if(file_exists($cachePath)){						
							rename($cachePath, "$cachePath-bak");					
							$count     = $util->cleanupDirectory("$cachePath-bak");
							echo "\n$count class $c files deleted";
						}
						@rmdir( "$cachePath-bak");
						//mkdir($cachePath, 0777, true);
					}else{
						div("moving $cachePath to $cachePath-bak")		;
						passthru("rm -rf $cachePath-bak");		
						passthru("mv -f $cachePath $cachePath-bak");					
						passthru("nohup rm -rf $cachePath-bak > /dev/null 2>&") ;	
						passthru("mkdir -pv $cachePath");
						div('done!', "\n", '');
					}				   
				}
							
				if(extension_loaded('apc')){  
            		file_get_contents("http://admin.rhemastudio.com/backend/apc.php?SCOPE=A&SORT1=H&SORT2=D&COUNT=20&CC=1&OB=3"); 
					div("APC user cache entries deleted successfully");
				}

				break;
			}
			case 'flush-html':{
				$cachePath = SITE_PATH . '/cached';
				if(PHP_OS == 'WINNT'){
					@rmdir( "$cachePath-bak");
					if(file_exists($cachePath)){
						rename($cachePath, "$cachePath-bak");					
						$count     = $util->cleanupDirectory("$cachePath-bak");
						echo "\n$count class $c files deleted";
					}					
				}else{
					div("moving $cachePath to $cachePath-bak")		;
					passthru("rm -rf $cachePath-bak");		
					passthru("mv -f $cachePath $cachePath-bak");					
					passthru("nohup rm -rf $cachePath-bak > /dev/null 2>&") ;	
					passthru("mkdir -pv $cachePath");
					div('done!', "\n", '');
				}				
				break;
			}
			case 'vouchers': {
				$cachePath = SITE_PATH . '/cached/deal-offer-category/vouchers-deals';
				div("deleting files in $cachePath")		;
				passthru("rm -rvf $cachePath.html");
				passthru("rm -rvf $cachePath/");
				div('done!', "\n", '');
				break;
			}
			case 'html':{
				$this->cleanupOldHtmlCacheFiles();
				break;
			}
			default:{
				echo $task . ' not found';
			}
		}
		
		//$this->_params['task'] = 'update-expired';
		//$this->feedAction();		
	}
	
    public function cleanupOldHtmlCacheFiles(){
        $now        = time();
        $threshhold = 144 * 60 * 60;
        $list       = array();
		$filter     = new Zend_Filter_RealPath();
        $start      = microtime(true);
        $mainDir    = $filter->filter(SITE_PATH .  "/cached/");

        
        $publicDir  = dirname(SITE_PATH);
        $dirs       = isset($this->_params['dir']) ? explode('|', $this->_params['dir']) : array();
        $theme      = $this->_siteConfig['subsite']['colour_scheme'];        
        
        $list[]     = "$publicDir/backend/merged/";
        $list[]     = $mainDir;
       // $list[]     = $filter->filter(SITE_PATH . "/$theme/merged/");
                        
        foreach($dirs as $d){
        	$d = trim($d);
        	if($d){
        		$list[] = "$publicDir/$d/cached/";
        		//$list[] = "$publicDir/$d/$theme/merged/";
        	}
        }
        
        foreach($list as $cacheDir){
        	$msg = '';
	        if($cacheDir and file_exists($cacheDir)){
		        echo "\n\nCleaning up cached files in $cacheDir\n";
		        $iterator  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cacheDir),
		                                          RecursiveIteratorIterator::CHILD_FIRST);
		        $list      = 0;
		        $dirCount  = 0;
		        $total     = 0;
		        $nowDate   = new Zend_Date();
		        
		        foreach ($iterator as $path) {
		        	$filename     = $path->__toString();
		            if (!$path->isDir()) {		                
		                $lastModified = filemtime($filename);
		                $now          = time();
		                $diff         = $now - $lastModified ;
		                $size         = filesize($filename) ;
						$total++;
		                if($diff > $threshhold or $size <= 5){
		                    $file = basename($filename);
		                    $diff = time() - $lastModified;
		                    if(@unlink($filename)){
		                    	div(sprintf("%s hours - Deleting %s", number_format($diff/(60*60), 1), $file), "\n", '');
		                    	$list++;		                    	
		                    }		                    
		                }
		            }elseif($this->_isDirectoryEmpty($filename)){
		                if(@rmdir($filename)){
		                    $file         = basename($filename);
		                    div("Deleting directory $file");
		                    $dirCount++;
		                }
		            }
		        }
		
		        $end  = microtime(true);
		        $msg .=  "\n $list of $total files deleted succesfully in " . number_format($end - $start, 2) . " seconds";
		        $msg .=  "\n" . $dirCount . ' empty directories deleted';
		        echo $msg;
	        } else{
	        	echo "\n$cacheDir does not exist";
	        }
        }
    }	
    
    protected  function _isDirectoryEmpty($filename){
        return ($files = @scandir($filename) and (count($files) > 2)) ? false : true ;
    }
    
    public function searchAcion(){
    	$storeList = Rhema_Model_Service::factory('affiliate_retailer')->findAll();
    	$products   = Rhema_Util_String::buildAutocompleteObject($list, $searchTerm, $filter);  
		$this->_helper->json->sendJson($products);
		
    }
	/**
	 * Affiliate feed and product related tasks
	 */
	public function feedAction(){
		$mem    = Rhema_Util::setMemoryLimit();
		div(array('calling controller action : ' . __FUNCTION__ ));
		$task   = isset($this->_params['task'])  ? $this->_params['task'] : 'info';
		$codes  = (isset($this->_params['codes']) and  $this->_params['codes'])? true : false;
		$force  = isset($this->_params['force']) ? true : false;
 
		$feedModel    = MODEL_PREFIX . 'AffiliateFeed';
	    $feedList     = Admin_Model_AffiliateFeed::getFeedList($codes);
	    
	    if(!$feedList){
	    	div('No active feed found');
	    	exit ;
	    }elseif($codes){
	    	div("\n Updating voucher codes only \n================================", "\n", '');
	    }
		
		switch($task){
			case 'generate-sql':{ 
				$helper = $this->_helper->getHelper('feedImport');
				foreach((array) $feedList as $feed){					
					$feedId         = $feed['id'];
					$cacheFile      = Rhema_Util::getFeedCacheFilename($feed['feed_url']);
					$limit 		    = isset($this->_params['list']) ? $this->_params['list'] : null;
					div("\nProcessing " . $feed['title'], "\n", '');
					try{
						list($message, $msgType)   = $helper->generateSql($cacheFile, $feedId, false, $force, $limit);						
					}catch(Exception $e){
						$message = $e->getMessage();
					}
					div($message, PHP_EOL, '');
				}
				break;
			}

			
			case 'download':{
				$cd 			= getcwd();
				$files          = array(); 
						
				$output = exec('which gunzip', $return);
				$path   = $output ? $output : current($return);	
				$plain  = array(2, 4) ; //feed types to be treated as uncompressed files	
						
				foreach((array) $feedList as $feed){
					div("\n\nProcessing " . $feed['title'], "\n", '');	
					$cacheFile      = Rhema_Util::getFeedCacheFilename($feed['feed_url'], '.gz');
					if(in_array($feed['affiliate_feed_type_id'], $plain)){ //uncompressed file
						$cacheFile      = Rhema_Util::getFeedCacheFilename($feed['feed_url'], '');
					} 
				
					$toDir          = dirname($cacheFile);					
					chdir($toDir);
					div("Change directory to {$toDir}");	

					if(file_exists($cacheFile)){
						$mtime = filemtime($cacheFile);
						$dif   = time() - $mtime;
						$date = date('Y-m-d H:i:s', $mtime);
						if($dif < 86400){							
							div(" >> $cacheFile \n >> downloaded in the last 24hrs ({$date}) \n >> skipping download");
							continue ;
						}else{
							div(" >> $cacheFile last downloaded @ {$date}");
						}
					}
					
					if($feed['affiliate_feed_type_id'] == 3){
						div("\nCompressed file found ". $feed['feed_url']. '');
					} else{						
						$files[]      = $cacheFile;
						$dir		  = dirname($cacheFile);
						
						$toDownload   = escapeshellarg($feed['feed_url']);
						$saveFile     = escapeshellarg($cacheFile);
						chdir($dir);
						
						$unzippedFile = str_replace('.gz', '', $cacheFile);
						if(file_exists($unzippedFile)){
							if($force or in_array($feed['affiliate_feed_type_id'], $plain)){
								div('deleting old file');
								@unlink($unzippedFile); 
							}else{
								div('file already downloaded, skipping download');
								continue ;
							}
						}
						
						div(PHP_EOL . 'downloading ' . $toDownload, ''); 
						$cmd = "wget -v --no-check-certificate --output-document $saveFile $toDownload";
						exec($cmd);
					}

					$file = $cacheFile;
					$file = rtrim($file, '.gz') . '.gz';
					
					if(file_exists($file)){
						div("\nUnzipping $file", '');
						echo exec("$path -vf $file");
					}						  					 		
					 
					div("\nCompleted! : {$cacheFile}\n\n", '', ''); 
					 
				};
				chdir($cd);
				break;
			}
					
			case 'import':{	
				if(isset($this->_params['reset']) and count($feedList) ){
					div('Truncating product table', '');
					$done = Rhema_Model_Abstract::rmsTruncateTable('affiliate_product');
					div('done', "\n", '');	
				} 	
				
				foreach((array) $feedList as $feed){					
					$feedId         = $feed['id'];
					div("\nProcessing " . $feed['title'], "\n", '');
					$numDel = Admin_Model_AffiliateProduct::softDeleteAll($feedId);
					div("$numDel rows deleted(soft)", "\n", '');
					$cacheFile      = Rhema_Util::getFeedCacheFilename($feed['feed_url']);
					div(PHP_EOL . 'processing ' . $feed['title']);
					list($message, $msgType)     = $this->_helper->getHelper('feedImport')
																  ->execSql($feed); 
					div($message, PHP_EOL, '');					
				
					$this->_params['reset'] = 1;
				}	

				break;
			}
			case 'create-cache':{
				$reset  = isset($this->_params['reset']) ? true : false;
				if($reset){
					div("\n=========== Cache would be reset ======================= \n");
				}
			 
			/*	
			 * @TODO routes not available here
			  			
			    div("Building ajax search option cache");
				$types     = array('category', 'voucher',  'retailer'); 				 
				$cache     = Rhema_Cache::getStatCache();				 
						
				foreach($types as $type){
					$cacheId = 'ajax_'  . md5('autocomplete' . $type);
					$data    = $this->_helper->suggestList($type);
					$cache->save($data, $cacheId, array('autocompletecached'));
					div("$type - " . count($data));	 
				}
 */
				try{
					$res = Admin_Model_AffiliateProduct::setProductStatList(null, $reset);
				}catch(Exception $e){
					echo $e->getMessage();
				}
				break;
			}
			case 'fix-brand':{
				Admin_Model_AffiliateProduct::fixBrandId();
				break;
			}
			
			case 'update-expired':{
				$done = Admin_Model_AffiliateProduct::updateExpiredCodes();
				div("$done rows were updated");				
				//$this->_params['task'] = 'update';
				//$this->luceneAction();
				break;
			}
			
			case 'batch-update':{
				div('Building doctrine index', ''); 
				$prodTable  =  new Admin_Model_AffiliateProduct();
				$prodTable->buildDoctrineIndex(); 
				div('done!', "\n", '...');				
				break;
			}
			 
		}
	}
	
	protected function tagAction(){		
		div("\nProcessing tag list", '');		
		$product  = Rhema_Model_Service::factory('affiliate_product', true, false);		
		$obj      = $product->getTagListPaginator(1, true);
		$total    = $obj->getTotalItemCount();
		div('done!', "\n", '');
		div("Tag list paginator created - " . number_format($total) . ' tags created', "\n", '');
	}
	
	public function metadataAction(){
		set_time_limit(0);
		div(array('calling controller action : ' . __FUNCTION__ ));	
		$task     = isset($this->_params['task']) ? $this->_params['task'] : 'info';				
					
		$networks = Doctrine_Core::getTable('Admin_Model_AffiliateNetwork')->findAll(Doctrine_Core::HYDRATE_ARRAY);	
			
		switch($task){
			case 'merchant':{
				$model    = new Admin_Model_AffiliateRetailer();			
				foreach((array)$networks as $data){
					//pd($data);
					if($data['merchant_metadata']){
						$cacheFile = Rhema_Util::getFeedCacheFilename ($data['merchant_metadata']);
							
						if(!file_exists($cacheFile)){
							div("$cacheFile not found");
							div("downloading merchant metadata", '');
							$downloadData = file_get_contents($data['merchant_metadata']);
							div('done',"\n", "");
							if($downloadData){
								div('Saving merchant metadata', "");
								file_put_contents($cacheFile, $downloadData);
								div('done',"\n", "");
							}
						}
						if($data['merchant_mapping']){
							div("processsing feed - #{$data['id']}", '');
							$mapping = Zend_Json::decode($data['merchant_mapping']);
							try{
								$res     = $model->updateRetailerFromCsv($cacheFile, $mapping, $data); 
							}catch(Exception $e){
								pd($e->getMessage());
							}
							div('done',"\n", "");
						}
					}
				}				
				break;
			}
			case 'category':{
				$model    = new Admin_Model_AffiliateProductCategory();	
				foreach((array)$networks as $data){
					//pd($data);
					if($data['category_metadata']){
						$cacheFile = Rhema_Util::getFeedCacheFilename ($data['category_metadata']);
							
						if(!file_exists($cacheFile)){
							div("$cacheFile not found");
							div("downloading category metadata", '');
							$downloadData = file_get_contents($data['category_metadata']);
							div('done',"\n", "");
							if($downloadData){
								div('Saving metadata', "");
								file_put_contents($cacheFile, $downloadData);
								div('done',"\n", "");
							}
						}
						if($data['category_mapping']){
							div("processsing feed - #{$data['id']}", '');
							$mapping = Zend_Json::decode($data['category_mapping']);
							try{
								$res     = $model->buildTreeFromCsv($cacheFile, $mapping, $data);
							}catch(Exception $e){
								pd($e->getMessage());
							}
							div('done',"\n", "");
						}
					}
				}
				break;
			}
			case 'merge-manufacturer':{
				div('Fixing manufacturer titles');
				$obj  = new Admin_Model_AffiliateProductManufacturer();
				$data = $obj-> fixManufacturerTitle();
				print_r($data);
				break ;
			}
					
			case 'merge-retailer':{
				div('Merging retailers with similar titles');
				$obj  = new Admin_Model_AffiliateRetailer();
				$data = $obj-> fixRetailerTitle();
				print_r($data);
				break ;
			}

			case 'retailer-logo':{
				$obj       = new Admin_Model_AffiliateRetailer();
				$list      = $obj->listRetailers(Doctrine_Core::HYDRATE_RECORD);
				$root      = Rhema_Constant::getPublicRoot();
				$imagePath = Rhema_View_Helper_GetRetailerLogo::$imageDir ;
				$count     = 0;
				$logFile   = APPLICATION_PATH . '/../sites/' . SITE_DIR . '/logs/logo-not-found.log';				
				file_put_contents($logFile , '');
				
				foreach($list as $item){
					$data = $item->toArray();
					$currentLogo = $this->view->getRetailerLogo($data);
					 
					$logo     = '' ;
					$checked  = array();
					
					$subTitle = str_replace('&', 'and', html_entity_decode($item['title']));
					$slugTitle = Rhema_Util_String::prepareTitleForSlug($item['title']);
					$missing   = Doctrine_Inflector::urlize($slugTitle);
					$urlize    = Doctrine_Inflector::urlize($item['title']);
					$urlize2   = Doctrine_Inflector::urlize($subTitle);
					$tryList   = array(
								$item['slug'],	
								$urlize,								
								$urlize2,
								$missing,
								str_replace('-', '', $urlize),
								str_replace('-', '', $urlize2)
							);
					foreach($tryList as $test){
						if(!in_array($test, $checked)){
							$checked[] = $test;
							foreach(Rhema_View_Helper_DisplayBrandLogo::$allowedExtension as $ext){
								$filename   = $test . '.' . $ext;	
								$actual     = rtrim($root,'/') . $imagePath  . $filename	;	
								//div('   >>> checking ' . $actual, '');		 			 
								if(file_exists($actual)){
									$logo    = '/..' . $imagePath . $filename ;
									//div('pass', "\n", ''); 
									break 2;
								}
								//div('fail', "\n", '');
							}
						}
					}
					
					if($logo){
						$count++;
						div(" >> {$count} {$item['title']} => {$logo}", '');
						$item->logo = $logo;
						$item->save();
						div('done', "\n", '');
					}else{
						if(!$item['logo']){ 
							$missing   = Doctrine_Inflector::urlize($slugTitle);
							file_put_contents($logFile , "$missing\n", FILE_APPEND); 
						}
					} 
				}
				
				$prodObj        = new Admin_Model_AffiliateProduct();
		    	$retailers      = $prodObj->countProductsByRetailer() ;  
		    	$prodObj->saveStatOption(array('retailers' => $retailers));
		    	div(count($retailers) . ' retailers created'); 		    	
    					 
				break;
			}
			
			case 'merge-category':{
				div('Merging categories with similar titles');
				$obj  = new Admin_Model_AffiliateProductCategory();
				$data = $obj-> fixCategoryTitle();
				
				print_r($data);	
				
				$prodObj      = new Admin_Model_AffiliateProduct();
		    	$category     = $prodObj->countProductsByCategory();  
		    	$prodObj->saveStatOption(array('category' => $category));
		    	div(count($category) . ' categories created'); 	
				
				break;
			}
			
			case 'rebuild-category-cache':{
				$obj  = new Admin_Model_AffiliateProductCategory();
				$res  = $obj->rebuildCategoryCache();
				print_r(array_combine(array('Category', 'Tree'), $res));
				break;
			}
			
			case 'swap-category':{	
				$from    = isset($this->_params['from']) ? $this->_params['from'] : false;
				$to      = isset($this->_params['to']) ? $this->_params['to'] : false;
											 
				$obj     = new Admin_Model_AffiliateProductCategory();
				$cats    = $obj->getCategory(array($from, $to));
				$done    = $obj->swapProductCategory($from, $to);
				
				if($done){
					echo "\n >> Done ({$done}) : {$cats[$from]['title']} => {$cats[$to]['title']}";
				}else{
					echo "\n >> No Product found in " . $cats[$from]['title'];
				}
				break;
			}
			
			case 'move-category-tree':{ 
				$parId    = isset($this->_params['p']) ? $this->_params['p'] : false;
				$chd      = isset($this->_params['c']) ? $this->_params['c'] : false;
				$msg      = array();
				if($parId and $chd){
					$chdId    = array_filter(explode('-', $chd));
					$table    = Doctrine_Core::getTable(MODEL_PREFIX . 'AffiliateProductCategory');
					$parMenu  = $table->find($parId); 
					
					foreach($chdId as $id){
						$chdMenu  = $table->find($id); 					
						$chdSlug  = $chdMenu->slug;				
						$done     = $chdMenu->getNode()->moveAsLastChildOf($parMenu);
						if($done){
							$chdMenu->slug = $chdSlug ;
							$chdMenu->save(); 
						}
					}	
					$newKids = array();
					
					$par     = $table->find($parId);
					$kids    = $par->getNode()->getChildren();	
					if($kids){					 
						foreach($kids as $item){
							$newKids[] = $item->title;
						}	
						$msg   = array($parMenu->title => $newKids);	
					}else{
						$msg['error']  = 'An error occurred';
					}					 
				}else{
					$msg['error'] = "Parent and child IDs required";
				}
				print_r($msg);
				break;
			}
			case 'info':
			default:{
				div(__CLASS__);
				break ;
			}
		}	
	}
	
	public function translationAction(){
		div(array('calling controller action : ' . __FUNCTION__ ));		
		$task   = isset($this->_params['task']) ? $this->_params['task'] : 'info';	
		$locale = Zend_Registry::get('Zend_Locale')->toString();
		
		switch($task){					
			case 'update-all':  
			case 'add-route':{	
		    	$options = Rhema_SiteConfig::getConfig('settings');
				$log     = realpath($options['log_dir'] . 'route-translation.log'); 
				$route   = array();
		    	if(file_exists($log) and ($handle = fopen($log,'r')) !== false){ 
		    		while (($data = fgets($handle)) !== false) { 
		    			$items   = explode(' ', $data);
		    			$route[] = end($items); 
		    		}
		    	}
		    	
		    	$route = array_filter(array_unique($route));
		    	$model = MODEL_PREFIX . 'Translation';
		    	$table = Doctrine_Core::getTable($model);
		    	
		    	foreach($route as $item){
		    		$row = $table->findOneBy('trans_key', $item);
		    		if(!$row){
		    			$row = new $model();
		    			$row->trans_key   = $item;
		    			$row->$locale     = $item;
		    			$row->file_type   = 'route';
		    			$row->save();
		    			$row->free();
		    		}
		    	} 
				if(count($route)){
		    		printf("Following routes added %s", print_r($route, true));
				}else{
					echo "\nNo missing route found";
				}
		    	unlink($log);
		    	file_put_contents($log, '');
		    	$file = Rhema_Util_TmxGenerator::getTmxFilename($locale, true, 'route');
		    	div("$file refreshed", '', '');
		    	
		    	if($task != 'update-all'){
					break;
		    	}
			}
			
			case 'add-menu':{				
				$model  = MODEL_PREFIX . 'Menu';
				$table  = Doctrine_Core::getTable($model);
				$menus  = $table->findAll();
				$list   = array();
				
				foreach($menus as $item){ 					
					if($item['title']){ 
						$list[] = $item['title'];
					}
					
					if($item['label']){
						$list[] = $item['label'];
					}
				}
				
				$list  = array_unique(array_filter($list));
		    	$model = MODEL_PREFIX . 'Translation';
		    	$table = Doctrine_Core::getTable($model);
		    	$added = array();
		    					
				foreach($list as $item){
					$key = Doctrine_Inflector::urlize($item);
					$row = $table->findOneBy('trans_key', $key);
					if(!$row){
						$row = new $model();
						$row->trans_key   = $key;
						$row->$locale     = $item;
						$row->file_type   = 'content';
						$row->save();
						$row->free();
						
						$added[$key] = $item;
					}	
				}
				
				if(count($added)){
					printf("the following items added : %s", print_r($added, true));									
					$file = Rhema_Util_TmxGenerator::getTmxFilename($locale, true);
					div("$file refreshed", '', '');
				}else{
					echo "\nNo new key found";
				}
 
				if($task != 'update-all'){
					break;
		    	}
			}
			
			case 'update-menu':{
				$transObj  	= new Admin_Model_Translation();
				$table 		= Doctrine_Core::getTable(MODEL_PREFIX . 'Menu');
				$menus 		= $table->findAll(Doctrine_Core::HYDRATE_RECORD);
				$list  		= array(); 
				
				foreach($menus as $item){ 	
					//$titleKey = $transObj->getTranslationKey($locale, $item['title']);
					$title    = $this->view->translate($item['title']);
					//if($titleKey and $titleKey != $item['title']){
						$item['title'] = $title;
					//}				
					$label    = $this->view->translate($item['label']);
					//$labelKey = $transObj->getTranslationKey($locale, $item['label']);
					//if($labelKey and $labelKey != $item['label']){
						$item['label'] = $label;
					//}
					
 					$item->save();
 					$item->free();
				}
				
				echo "\nMenu titles and labels updated";
				
				if($task != 'update-all'){
					break;
		    	}
			}
			
			case 'update-content':{
		    	$options = Rhema_SiteConfig::getConfig('settings');
				$log     = realpath($options['log_dir'] . 'content-translation.log'); 
				$route   = array();
		    	if(file_exists($log) and ($handle = fopen($log,'r')) !== false){ 
		    		while (($data = fgets($handle)) !== false) { 
		    			$items   = explode(' ', $data);
		    			$route[] = end($items); 
		    		}
		    	}
		    	
		    	$route = array_filter(array_unique($route));
		    	$model = MODEL_PREFIX . 'Translation';
		    	$table = Doctrine_Core::getTable($model);
		    	
		    	foreach($route as $item){
		    		$row = $table->findOneBy('trans_key', $item);
		    		if(!$row){
		    			$row = new $model();
		    			$row->trans_key = $item;
		    			$row->en_GB     = $item;
		    			$row->file_type = 'content';
		    			$row->save();
		    			$row->free();
		    		}
		    	} 
				if(count($route)){
		    		printf("Following routes added %s", print_r($route, true));
		    		$file = Rhema_Util_TmxGenerator::getTmxFilename($locale, true);
					div("$file refreshed", '', '');
				}else{
					echo "\nNo missing route found";
				}
		    	unlink($log);
		    	file_put_contents($log, '');				
					    	

				break;
			} 
			case 'refresh':{
				$file[] = Rhema_Util_TmxGenerator::getTmxFilename($locale, true, 'route');
				$file[] = Rhema_Util_TmxGenerator::getTmxFilename($locale, true);
				
				printf("following files refreshed %s", print_r($file, true)); 
				break;
			}
			default:
				echo "\n$task not found";
		}
	}
	
	protected function luceneAction(){
		set_time_limit(0);
		ini_set('display_errors', 'off');
		div(array('calling controller action : ' . __FUNCTION__ ));
		
		$task   = isset($this->_params['task']) ? $this->_params['task'] : 'info';
		$limit  = isset($this->_params['limit']) ? intval($this->_params['limit']) : Rhema_Search_Lucene::RECORDS_BUFFER_SIZE;
		$lucene = Rhema_Search_Lucene::getInstance(true);
		$lucene->setVerboseMode($this->verbose);
	 
		switch($task){
			case 'create':{
				$lucene->createIndex();
				break;
			}
			
			case 'update':{				 
				echo "\nRunning lucene->updateAll({$limit}) .... " ;
					$res   = $lucene->updateAll($limit);
				echo "\ndone!"; 
				break ;
			}
			case 'optimise':
			case 'optimize':{
				div('Optimising index', '');
					$lucene->optimizeIndex();
				div('done!', "\n"); 				
				break;
			}
			case 'drop-index':{
				$vb = isVerbose() ;
				$lucene->deleteIndex($vb);
				$items = $lucene->clean();
				div($items . ' products flagged to delete');
				break;
			}
			case 'remove-recycle':{
				$item = Admin_Model_AffiliateRetailerType::getRetailerType('mobile-recycle');
				if($item){							 
					$done = $lucene->removeProductByRetailerTypeFromIndex($item['id'], $limit);
					echo "\n$done documents removed from index";
				}else{
					echo "\nRetailer type not found";
				}
				break;
			}		
			case 'remote-sync':{
				$lucene->syncIndexToRemoteServers($this->_domain);			
				break;
			}			
			case 'sync':{
				$lucene->syncLocalIndex(); 			
				break;
			}
			case 'info':
			default:{
                $sizeInfo = $lucene->getIndexSize();
                echo "Index size : " . $sizeInfo[2] . PHP_EOL;
                echo "NumDocs    : " . $lucene->numDocs(). PHP_EOL;
                echo "Settings   : " . print_r(array($lucene->getSettings()), true). PHP_EOL;
				break;
			}
		}
		
	}
	
	private function _downloadFeed($url){
					$url  = $feedData['feed_url'];
					$b    = PHP_OS;
					$path = (PHP_OS == 'WINNT') ? 'C:\cygwin\bin\\' : '/usr/local/bin/';
					$cmd  = $path . 'wget -q -O ' . escapeshellarg($cacheFile) . ' ' . escapeshellarg($url); 
					exec($cmd, $output); // download the feed		
	}
}