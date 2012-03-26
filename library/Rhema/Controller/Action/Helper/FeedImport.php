<?php 
class Rhema_Controller_Action_Helper_FeedImport extends Zend_Controller_Action_Helper_Abstract{
	const WRITE_BUFFER    = 10000;
	public $_catList      = array();
	public $_retailerList = array();
	public $_brandList    = array();
	public $_offerList    = array();
	public $_manuList     = array();
	public $productTypes  = array();
	public $tempFiles     ;
	public $fileSaved     = array();
	public $brandRegex    = '';
	private $_con         = null;
	private $_hashKeys    = array('title', 'code', 'price', 'valid_to');
 
	protected $_rootMenu    = null;
	
	private $_lockFile    = 'code-imported-lock.txt';
	private $_execFile    = 'sql-exec-lock.txt';

 

	/**
	 * @return the $_rootMenu
	 */
	public function getRootMenu($default = array()) {
		if(!$this->_rootMenu){
			$this->_rootMenu = Admin_Model_AffiliateProductCategory::getRootMenu($default); 
		}
		return $this->_rootMenu;
	}

	/**
	 * @param field_type $_rootMenu
	 */
	public function setRootMenu($_rootMenu) {
		$this->_rootMenu = $_rootMenu;
	}

	public function feedImport(){		
		return $this;
	}
 
	public function execSql($feedData){
		set_time_limit(0);
		$sqlDir         = Rhema_Util::getFeedSqlDirectory($feedData['title']) ;
		$msgType        = Rhema_Dto_UserMessageDto::TYPE_SUCCESS ;
		$return              = Rhema_Util::createSiteDirectory($sqlDir);
		$this->tempFiles     = current($return) . '/'; 
		$this->fileSaved      = array();
		
		foreach(new DirectoryIterator($this->tempFiles) as $f){
			if($f->isDot() or $f->isDir()){
				continue;
			}
			if($f->getFilename() != $this->_lockFile){
				$this->fileSaved[] = $f->getPathname();
			}
		}
		div('  >> ' . count($this->fileSaved) . ' sql files found');

		$return    = $this->executeSql($this->fileSaved);
		
		return $return ; 	
	}
	
	public function generateSql($csv, $feedId, $exec = false,  $force = false, $limit = null){ 					
		set_time_limit(0); 
		$util  = Rhema_Util::getInstance();
		$util->setMemoryLimit('1990');
					
		$memMgr    = $util->getMemoryManager();
		$memObject = $memMgr->create();
		$memObject->value['data'] = array();
		$message   = '';
			
		$startTime      = microtime(true);
		$feedModel      = MODEL_PREFIX . 'AffiliateFeed';		
		$prodModel      = MODEL_PREFIX . 'AffiliateProduct';
		$prodTable      = new $feedModel();// Doctrine_Core::getTable($feedModel);
		
		
		$feedData  	    = $prodTable->getFeedDataById($feedId, Doctrine_Core::HYDRATE_ARRAY);
 		 
		$sqlDir         = $util->getFeedSqlDirectory($feedData['title']) ;
		$msgType        = Rhema_Dto_UserMessageDto::TYPE_SUCCESS ;
		$return         = $util->createSiteDirectory($sqlDir);
		$bufferSize     = $limit ? $limit : Rhema_SiteConfig::getConfig('settings.query_buffer_size');
		$this->tempFiles     = current($return) . '/'; 	
		$filter		     = new Rhema_View_Helper_FilterMappedFeedData();
		$hashKeys        = (array) Rhema_SiteConfig::getConfig('settings.hash_keys');
		$this->_hashKeys = array_unique(array_merge($this->_hashKeys, $hashKeys));
 
 		if($exec){
 			return $this->execSql($feedData);
 		}
 		
		$foreignKeys    = array(
			'affiliate_network_id', 
			'affiliate_retailer_id',
			'affiliate_promotion_id',
		 	'affiliate_product_type_id',
			'affiliate_product_category_id',
			'affiliate_product_review_id',
			'affiliate_feed_id', 
			'network_promotion',
			'unique_hash'
		);
 		//if(false){
		if(!$force and file_exists($this->tempFiles . $this->_lockFile)){
			$msgType        = Rhema_Dto_UserMessageDto::TYPE_WARNING;
			$message        = ' >> Lock file found :  ' . $this->tempFiles . $this->_lockFile ;
			//return $this->execSql($feedData);
		}elseif(file_exists($csv) and ($handle= fopen($csv,'r')) !== false){ 	

	 		$deleted = 0;
			foreach(new DirectoryIterator($this->tempFiles) as $f){
				if(!$f->isDot()){
					$fn = $f->getPathname();
					unlink($fn );
					div($fn . ' deleted');
					$deleted++;
				}
			}
			$plural = ($deleted > 1) ? 'files' : 'file';
			div(" >>  $deleted $plural deleted"); 
			 
			$delimiter 			 = Rhema_Util_String::getDelimiter($handle); 
			$feedCols            = fgetcsv($handle, null, $delimiter);
			$feedCols 			 = array_filter($feedCols);		
			array_walk($feedCols, array('Rhema_Util_String','stripWhiteSpaces'));
			 
			$this->_catList      = $this->_listCategory();
			$this->_retailerList = $this->_listRetailers();
			$this->_brandList    = $this->_listBrands();
			$this->_offerList    = $this->_listOffers();
			$this->_manuList     = $this->_listManufacturer();

			
			$mapped   = array();
	        $values   = array();
 	         
			$mapping  = $feedData['field_mapping'] ? $feedData['field_mapping']  : $feedData['AffiliateNetwork']['field_mapping'] ;
			$mapping  = Zend_Json::decode($mapping, true);
			 
			if(!$mapping){
				return array(" >> No mapping data found", Rhema_Dto_UserMessageDto::TYPE_WARNING);
			}
	        
	        $dbCols   = Doctrine_Core::getTable($prodModel)->getColumnNames();
 
        	$done         = 0;	
        	$colSql       = $message = '';	
        	$categoryList =  $valid = $collate = $query = array();
 
        	$collection   = new Doctrine_Collection($prodModel);         	
        	$user         = Zend_Auth::getInstance()->getIdentity();
        	$now          = date(DB_DATE_FORMAT);
        	
        	$defaultValues = array(
        		'admin_subsite_id'     => Zend_Registry::get('namespace')->subsiteId,
		        'created_by'           => $user['id'],
		        'updated_by'           => $user['id'],
		        'created_at'           => $now ,
        	    'updated_at'           => $now ,
        	    'affiliate_promotion_id' => '',
        		'network_promotion'    => '',
        		'description'		   => '',
        		'title'				   => '',
        		'deeplink'			   => '',
        	    'price'				   => '',
        		'affiliate_feed_id'	   => $feedData['id'],
        	    'affiliate_network_id' => $feedData['affiliate_network_id']
        	);
 
			foreach($dbCols as $col){
	        	if(isset($mapping[$col]['columns']) and $mapping[$col]['columns']){
	        		$mapped[$col] = $mapping[$col]['columns'];
	        	}
	        }
	        
	        $unMappedFields = $this->getUnmappedFields($feedCols, $mapped);
	        $colCount       = count($feedCols);
	        					            
			$count    	  = 0;				
			$sqlCount     = 1;	
			$filename     = $this->getSqlFilename($sqlCount, $bufferSize);
			$updDate      = date(DB_DATE_FORMAT);
			//network_promotion
			$fileFooter   = sprintf(" ON DUPLICATE KEY UPDATE updated_at='%s', price = VALUES(price), deeplink = VALUES(deeplink), network_promotion = VALUES(network_promotion), index_status = 'to_update', is_expired = 0, deleted_at = NULL, description = VALUES(description) ; ",  $updDate );
			//$fileFooter   = ' ON DUPLICATE KEY UPDATE updated_at= \'' . date(DB_DATE_FORMAT) ."';"; 
			$rowSeparator = ', ' . PHP_EOL ;
			$productTable = Doctrine_Core::getTable(MODEL_PREFIX . 'AffiliateProduct');
	// pd($unMappedFields);       
			try{
	        	while (($data = fgetcsv($handle, null, $delimiter)) !== false) { 		        		
	        		$data    = array_slice($data,0, $colCount);     // sometimes there are trailing commas ','   		
	        		$values  = $defaultValues;
	        		$valid   = array_combine($feedCols, $data); 
	        		  
	        		$valid['affiliate_network_id'] = $feedData['affiliate_network_id'];        		  
	        		      		         
	        		foreach($mapped as $col => $m){ 
	        			$v = isset($valid[$m]) ? $valid[$m] : '';
	        			if($v){
	        				$v = $filter->filterMappedFeedData($col, $v, $mapping, $valid); 
	        			}
		        		$values[$col]  = mysql_escape_string($v);
			        }	        	           
 
			        $combinedData = $values + $valid;
			        $hash         = $this->getHash($combinedData);
			        $values['unique_hash'] = $hash;
			        
/* 			        if($data = $productTable->findOneBy('unique_hash', $hash, Doctrine_Core::HYDRATE_ARRAY)){
			        	div(" >>> skiping {$data['title']} : {$data['price']} : {$data['code']}");
			        	continue;
			        }  */
			        
		            $values['affiliate_product_category_id'] = $this->_getCategory($combinedData);
		            $values['affiliate_product_brand_id']    = $this->_getBrand($combinedData);
		            $values['affiliate_retailer_id']         = $this->_getRetailer($combinedData);
		            
		            $values['price']     = $this->getPrice($combinedData);
		           // $values['rrp_price'] = $this->getRrpPrice($values);
		            $promo               = $this->_getOffer($combinedData);
		            $manu                = $this->_getManufacturer($combinedData) ;
		            if($manu){
		            	$values['affiliate_product_manufacturer_id'] = $manu ;
		            }
		            if($promo){
		            	$values['affiliate_promotion_id'] = $promo ;
		            }

		            $values['affiliate_product_type_id'] = $this->getProductType($combinedData);
		            
		           // $values['slug']							 = Doctrine_Inflector::urlize($values['product_name']);
		            
		            $values['keywords']     = $this->_getKeywords($combinedData);		            
		            $values['product_data'] = mysql_escape_string($this->getAdditionalDetails($unMappedFields, $valid, $values));
 
		            $rowData  = "('" . implode("', '", array_values($values)) . "')" ; 
		            $count++;
		              		             
		            if($count%$bufferSize == 0){
		            	div($count, '');
		            	$sqlCount++;		            	          	  
		            	 
		            	$rowData     .= PHP_EOL ;  
		            	$file = basename($filename);    		            	
		            	file_put_contents($filename, $rowData, FILE_APPEND);
         				file_put_contents($filename, $fileFooter, FILE_APPEND);
         				
         				$this->fileSaved[] = $filename; 
         				div("  >>  $file file created", "", ' - ');
         				div(mu(), "\n", ''); // display memory usage
         				
         				$filename = $this->getSqlFilename($sqlCount, $bufferSize); // get next f 
         				
		            }else{
		            	if(!file_exists($filename)){
		            		$fileHeader   =  "INSERT IGNORE INTO affiliate_product (`" . implode("`, `", array_keys($values)) . "`) VALUES" . PHP_EOL; 
		            		file_put_contents($filename, $fileHeader, FILE_APPEND); 	
		            	}
		            	$rowData   .= $rowSeparator ;
		            	file_put_contents($filename, $rowData, FILE_APPEND); 		             	            	 
		            }		           
	        	}
		        
	        	if(!in_array($filename, $this->fileSaved) and $count){	 
	        		$lastRow    = str_replace($rowSeparator, ' ', $rowData) ; 
	        		$content    = file_get_contents($filename)    ;
	        		$editCnt    = str_replace($rowData, $lastRow, $content);
	        		
	        		file_put_contents($filename, $editCnt);   		
	        		file_put_contents($filename, $fileFooter, FILE_APPEND);
	        		
	        		$this->fileSaved[] = $filename;
	        		div($count, '');
	        		$file = basename($filename); 
	        		div("  >>  $file file created", "\n", '');
	        	}
	        	 				
 				$totalFiles = count($this->fileSaved);
 				if($totalFiles){
 					file_put_contents($this->tempFiles . $this->_lockFile, date('r'));
 					$message = count($this->fileSaved) . ' SQL import files created successfully';	
 				} 				
 					       			        
			}catch(Exception $e){
				$message  =  $e->getMessage();
				$msgType  = Rhema_Dto_UserMessageDto::TYPE_ERROR;
			}	
			fclose($handle); 			
		}	
				
	    return array($message, $msgType);        		        
	}	
 
	protected function _saveInsertQuery($table, $filename, $cols, $values, &$colSql = ''){
		 $query   = '';
		 $pre     = "SET autocommit=0;  SET foreign_key_checks=0; ";
		 $post    = " COMMIT; SET foreign_key_checks=1; "; 
         $colSql  = $colSql ? $colSql : "INSERT IGNORE INTO $table (`" . implode("`, `", array_keys($cols)) . "`) VALUES";
         //$query  .=  $pre ;
           
         
         $rowData      = implode(', ', $values);
         $fileHeader   =  "INSERT IGNORE INTO $table (`" . implode("`, `", array_keys($cols)) . "`) VALUES";          
         $fileFooter   = ' ON DUPLICATE KEY UPDATE updated_at= \'' . date(DB_DATE_FORMAT) ."';";
        // $fileFooter   = sprintf(" ON DUPLICATE KEY UPDATE updated_at='%s', valid_to='%s', valid_from='%s'; ", date(DB_DATE_FORMAT), $validTo, $validFrom);
	     if(file_exists($filename) and $closeFile){
         	file_put_contents($filename, $data, FILE_APPEND);
         	file_put_contents($filename, $fileFooter, FILE_APPEND);
         }         
         
         
         $query  .=  $colSql . implode(', ', $values) . ' ON DUPLICATE KEY UPDATE updated_at= \'' 
            			     . date(DB_DATE_FORMAT) ."';";
         //$query  .= $post ;	
         		            	
        // $filename   = $this->tempFiles . time() . '.sql';		            	
         if(file_put_contents($filename, $query)) {
            $this->fileSaved[] = $filename; 
            div($filename . ' file created');
            return true;
         } 	
         return false;		
	}
	
/*	public function getProductType($values){
	    if(isset($values['code']) and $values['code']){
           $typeId  = preg_match('/[\*]+/', $values['code'])  ?  3 : 2 ;
        }else{
            $typeId = 1 ;
        }	
        return $typeId ;
	}*/
	
	/**
	 * @return the $productTypes
	 */
	public function getProductType($values) {
		if(!count($this->productTypes)){
			$data = (array) Admin_Model_AffiliateProductType::getTypeList();
			foreach($data as $item){
				$slug = $item['slug'];
				$this->productTypes[$slug] = $item['id'];
			}
		}
		
	    if(isset($values['code']) and $values['code']){
           $typeId  = preg_match('/[\*]+/', $values['code'])  ?  $this->productTypes['deals'] : $this->productTypes['vouchers'] ;
        }else{
           $typeId = (int) $this->productTypes['standard'] ;
        }	
        
        return $typeId ;		
	}
		
	public function getHash($data){
		$toHash = array();
		foreach($this->_hashKeys as $h){
			if(isset($data[$h]) and $data[$h]){ 
				$toHash[] = Rhema_Util_String::correctEncoding($data[$h]);
			}
		}
		//$str = implode('', $toHash);
	    $str = Zend_Json::encode($toHash);
		//return  hash('sha512', $str);
		return  md5($str);
	}
	public function getSqlFilename($count, $bufferSize){
		$fileName = $this->tempFiles . $bufferSize  . '-' . str_pad($count, 5, '0', STR_PAD_LEFT) . '.sql';	
		return $fileName;	             
	}
	
	public function executeSql($files = array()){
		$done         = 0;
		$startTime    = microtime(true);
		$doctrine     = Doctrine_Manager::getInstance()->getCurrentConnection(); 
		asort($files);
		$msgType    = Rhema_Dto_UserMessageDto::TYPE_SUCCESS ; 
		$doctrine->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		$fileNum     = 0;
		foreach($files as $file){			
			if(basename($file) != $this->_lockFile){
	 			$str    = file_get_contents($file);
	 			$fileNum++; 						    	
		    	div(PHP_EOL . $fileNum . '. importing ' . $file, '');
		    	try{
		    		$num = $doctrine->exec($str); 
		    		//$num = $this->_doOperation($str);
		    		if($num){
		    			@unlink($file);
		    		}	
		    		div(" done! {$num} rows affected", '', '');	    		
		    	}catch(Exception $e){
		    		$msg = substr($e->getMessage(), 0,512);
 					div($msg);
 					//return array($e->getMessage(), Rhema_Dto_UserMessageDto::TYPE_ERROR);
 				}
 	    	 		    	
		    	$done++;
			}
		}
		
		//$doctrine->query("OPTIMIZE TABLE affiliate_product`"); 
		
		$duration = microtime(true) - $startTime;
        $secs     = round($duration, 2);
        
        unlink($this->tempFiles . $this->_lockFile);
        $message  = PHP_EOL . PHP_EOL . $done . ' files executed in ' . $secs . ' seconds. '; 		 
		
		return array($message, $msgType);
	}

	protected function _getVoucherCodeCategory(){
		$slug = Admin_Model_AffiliateProductCategory::VOUCHER_CATEGORY_SLUG ;
		if(!isset($this->_catList[$slug])){ 
			$catData = array(
				'title' 	=> 'Vouchers & Deals',
				'keywords'  => 'voucher codes, deals, offers',
				'slug'	    => $slug
			);
			$category = Admin_Model_AffiliateProductCategory::createCategoryMenu($catData);	
			$this->_catList[$slug]['id'] = $category['id'];			
		}	
		return (int) $this->_catList[$slug]['id'] ;	
	}
	
	protected function _getCategory($catData){		
		$catId    = 1;
		if(isset($catData['code']) and $catData['code']){ 
			return $this->_getVoucherCodeCategory($catData);
		}
		
		if(isset($catData['ADVERTISERCATEGORY'])){
			$str   = $catData['ADVERTISERCATEGORY'];
		}else{
			$str   = isset($catData['category_name']) ? strtolower($catData['category_name']) : '';
		}

		$arr        = explode('|', strtolower($str));
		$catName    = $str ? (count($arr) > 1 ? $arr[1] : $arr[0]) : '';
		$slugTitle  = Rhema_Util_String::prepareTitleForSlug($catName);	
		$catName    = html_entity_decode($catName);
		$slug       = Doctrine_Inflector::urlize($slugTitle);
			
		if($slug and isset($this->_catList[$slug])){
			$catId = (int) $this->_catList[$slug]['id'];
		}elseif($slug){					 
			//$category = new Admin_Model_AffiliateProductCategory();
			$catData['keywords']   = Rhema_Util_String::getKeywords($catData);
			$catData['categoryid'] = isset($catData['category_id']) ? $catData['category_id'] : '';
			$catData['title']      = ucwords($catName);
			$catData['slug']       = $slug; 
			try{
				//$category->save();  
			    $category = Admin_Model_AffiliateProductCategory::createCategoryMenu($catData);	 
				$this->_catList[$slug]['id'] = $category['id'];
				$category->free();
				$catId = (int) $this->_catList[$slug]['id'];			    
			}catch(Exception $e){
				div($e->getMessage());
			}			 
		} 
		return (int) $catId;
	}
	
/*	public function createCategoryMenu($data){	
		$model    		 = MODEL_PREFIX . 'AffiliateProductCategory' ;		
		$mainRoot        = $this->getRootMenu($data);
		
		$data['root_id'] = $mainRoot->id;
		$data['label']   = $data['title'] ;
		$data['m_route'] = 'mobile-category';
		$data['params']  = 'category=' . Doctrine_Inflector::urlize($data['title']);	
			 
		$catMenu = Admin_Model_AdminMenu::getDefaultRow($data, $model);					 
		$catMenu->getNode()->insertAsLastChildOf($mainRoot);	

		return $catMenu ;
	}*/
	
	public function getRrpPrice($data){
		if(isset($data['rrp_price']) and $data['rrp_price']){
			$price = $data['rrp_price'] ;
		}else{
			$price = '';
		}
		
		return preg_replace('/[^\d\.]+/i','',$price);
	}

	public function getPrice($data){
		if(isset($data['price']) and $data['price']){
			$price = $data['price'] ;
		}elseif(isset($data['display_price']) and $data['display_price']){
			$price = $data['display_price'] ;
		}elseif(isset($data['search_price']) and $data['search_price']){
			$price = $data['search_price'] ;
		}elseif(isset($data['price'])){
			$price = $data['price'] ;
		}else{
			$price = '';
		}
		
		return preg_replace('/[^\d\.]+/i','',$price);
	}
	
	protected function _getOffer($data){
		if(isset($data['promotion_details'])and $data['promotion_details']){
			$key       = 'promotion_details';
		}else{
			$offerName = null ;
			$key       = null ;
		}
		$offerName    = $key ? $data[$key] : null;	
		$slugTitle    = Rhema_Util_String::prepareTitleForSlug($offerName);
		$slug         = Doctrine_Inflector::urlize($slugTitle);
			
		if(is_numeric($slug)){
			return 1;
		}elseif($offerName and isset($this->_offerList[$slug])){
			return (int) $this->_offerList[$slug]['id'];
		}elseif($slug){					 
			$offer = new Admin_Model_AffiliatePromotion(); 
			$offer->keywords    = $this->_getKeywords(array('title' => $offerName)); 
			$offer->description = $offerName;
			$offer->title       = ucwords($offerName);
			$offer->slug        = $slug;
			try{
				$offer->save();
			}catch(Exception $e){
				div($e->getMessage());
			}			 
			
			$this->_offerList[$slug]['id'] = $offer['id'];
			$offer->free();
			return (int) $this->_offerList[$slug]['id'];
		}else{
			return 1;
		}	
	}
	
	protected function _getKeywords($data){
		return Rhema_Util_String::getKeywords($data); 
	}
	
	protected function _getBrand($data,  &$name = ''){
		$brandId    =  0 ; 
		$key        = isset($data['brand']) ? 'brand' : (isset($data['brand_name']) ? 'brand_name' : '');
		$name       = $key ? html_entity_decode($data[$key]) : '';	
		
		if($key){
			if(isset($data['program_name'])){
				$retailer    = $data['program_name'] ;			
			}elseif(isset($data['merchant_name'])){
				$retailer    = $data['merchant_name'] ; 
			}elseif(isset($data['PROGRAMNAME'])){
				$retailer    = $data['PROGRAMNAME'] ;   
			}else{
				$retailer = '';
			}
			 
			$retailer   = preg_replace('/(\.co\.uk|\.com)/', '', $retailer);
			$name       = preg_replace('/(\.co\.uk|\.com|cables|reman|value|compatible|toner|software)/i', '', $name);
			if(strlen($name) > strlen($retailer)){
				$name = str_ireplace($retailer, '', $name);
			}
			$name          = trim(str_ireplace(array('three'), array(3), $name));			
			$slug          = Doctrine_Inflector::urlize($name);	
		 
			if($slug and isset($this->_brandList[$slug])){				
				$brandId = (int) $this->_brandList[$slug]['id'];			 
			}elseif($slug){		 	 
				$brand 			  = new Admin_Model_AffiliateProductBrand();			
				$brand->title     = strlen($name) > 3 ? ucwords(strtolower($name)) : strtoupper($name);
				$brand->keywords  = $this->_getKeywords($data);
				$brand->slug      = $slug;
				//$brand->description = $name ;
							 
				try{
					$brand->save();
					$this->_brandList[$slug]['id'] = $brand['id'];
					$brandId = (int) $this->_brandList[$slug]['id'];	
					$brand->free();
									
				}catch(Exception $e){
					div($e->getMessage());
				}				 

			}
		} 
		return $brandId ? $brandId : (isset($data['title']) ? $this->_findBrand($data['title']) : 1);		
	}
	
	protected function _findBrand($text){
		$brandId  = 1;
		$text     = Doctrine_Inflector::urlize($text);
		if(strlen($this->brandRegex) < 5000 and $this->brandRegex and preg_match($this->brandRegex, $text, $matches)){
			$brand   = $matches[1] ;
			$brandId = $this->_brandList[$brand]['id']; 			 
		}
		return $brandId ;
	}
	
	protected function _getManufacturer($data){ 
		if(isset($data['MANUFACTURER'])){
			$name      = trim($data['MANUFACTURER']);
		}else{
			$name      = isset($data['manufacturer']) ? trim($data['manufacturer']): '';
		}
		
		if($name){
			$name      = trim(preg_replace('/\b(limited|ltd|plc|co|uk|inc|inc\.|-uk|\.co\.uk)\b/i', '', html_entity_decode($name)));
			$slugTitle = Rhema_Util_String::prepareTitleForSlug($name);
			$slug      = Doctrine_Inflector::urlize($slugTitle); 				
			if($slug and isset($this->_manuList[$slug])){	
				return (int) $this->_manuList[$slug]['id'];
			}elseif($slug){ 
				$manu     = new Admin_Model_AffiliateProductManufacturer();
				//$manu->fromArray($data); 
				$manu->title       = ucwords($name);
				$manu->keywords    = $this->_getKeywords($data);
				//$manu->description = $name ;
				$manu->slug        = $slug; 	
				$manu->save();			 
				
				$this->_manuList[$slug]['id'] = $manu['id'];
				$manu->free();
				return (int) $this->_manuList[$slug]['id'];	
			} 
		}else{
			return 1;
		}			
	}
		
	protected function _getRetailer($data){
		$key    = false ;
		$urlKey = 'merchant_deep_link';
		$catKey = 'merchant_category';
		$idKey  = 'program_id'; 
		$id     = 1;
		if(isset($data['program_name'])){
			$key    = 'program_name' ;			
		}elseif(isset($data['merchant_name'])){
			$key    = 'merchant_name' ;
			$idKey  = 'merchant_id';
		}elseif(isset($data['PROGRAMNAME'])){
			$key    = 'PROGRAMNAME' ;
			$urlKey = 'PROGRAMURL';
			$catKey = 'THIRDPARTYCATEGORY';
		}elseif(isset($data['MerchantName'])){ //affiliate Futures
			$key   = 'MerchantName';
			$idKey = 'MerchantID';
			$data[$urlKey] = Rhema_View_Helper_AffiliateLink::getAffiliateFutureRetailerLink($data);
		}elseif(isset($data['program'])){
			$key = 'program';
		}
		
		if($key){
			//$key       = isset($data['program_name']) ? 'program' : 'merchant';
			$name      = preg_replace('/(\sltd|\.com)/i', '', $data[$key]) ;
			$slugTitle = Rhema_Util_String::prepareTitleForSlug($name);
			$slug      = Doctrine_Inflector::urlize($slugTitle);
			$name      = strtolower(html_entity_decode($name));	
				
			if($slug and isset($this->_retailerList[$slug])){	
				return (int) $this->_retailerList[$slug]['id'];
			}elseif($slug){ 
				$retailer     = new Admin_Model_AffiliateRetailer();
				//$retailer->fromArray($data); 
				$retailer->programid = isset($data[$idKey]) ? $data[$idKey] : '' ; //$data[$key . '_id'];
				$retailer->title     = ucwords($name);
				$retailer->keywords  = $this->_getKeywords($data);
				$retailer->slug      = $slug;
				$retailer->description   = $data[$key] ;
				$retailer->program_name  = $name ;
				$retailer->image_file = isset($data['merchant_image_url']) ? $data['merchant_image_url'] :  '';
				$retailer->logo       = isset($data['merchant_thumb_url']) ? $data['merchant_thumb_url'] :  '';
				$retailer->category   = isset($data[$catKey])  ? $data[$catKey]  :  '';
				$retailer->deeplink   = isset($data[$urlKey])  ? Rhema_Util_FeedFilter::replaceAffiliateWindowIdAndClickref($data[$urlKey]) :  '';
				$retailer->affiliate_network_id = $data['affiliate_network_id'];
	
				$retailer->save();			 
				
				$this->_retailerList[$slug]['id'] = $retailer['id'];
				$retailer->free();
				
				$id =  (int) $this->_retailerList[$slug]['id'];	
			}else{
				$id = 1;
			}
		}else{
			$id = 1;
		}	

		return $id  ;
	}
	
	protected function _listRetailers(){
		$arr       = array();
		$class     = MODEL_PREFIX . 'AffiliateRetailer';
		$daoFilter = new Rhema_Dao_Filter();
		$daoFilter->setModel($class)
				  ->setIndexBy('slug')
				  ->setFields(array('id', 'title', 'slug'));
		//$list = Rhema_Model_Abstract::findAll($daoFilter);
		$list = Rhema_Model_Service::createQuery($daoFilter)->execute();
			
		foreach((array) $list as $item){
			$slug        = $item['slug'];
			$arr[$slug]  = $item ;
		} 
		
/*		if(!$list){
			$data = array('title' => 'General');
			$this->createMisc($class, $data);
		}*/		
		return $arr;
	}
	
	protected function _listBrands(){
		$arr       = array();
		$daoFilter = new Rhema_Dao_Filter();
		$class     = MODEL_PREFIX . 'AffiliateProductBrand';
		$daoFilter->setModel($class)
				  //->setIndexBy('slug')
				  ->setFields(array('id', 'title', 'slug'));
		$list = Rhema_Model_Abstract::findAll($daoFilter);
		
		foreach((array) $list as $item){
			$slug        = $item['slug'];
			$arr[$slug]  = $item ;
		}
 		 
		if(count($arr)){
			$this->brandRegex = '/(' . implode('|', array_keys($arr)) . ')/i';
		}
		
		if(!$list){
			$data = array( 'title' => 'Miscellaneous');
			$this->createMisc($class, $data);
		}
		return $arr;
	}	
	protected function _listCategory(){
		$daoFilter = new Rhema_Dao_Filter();
		$class     = MODEL_PREFIX . 'AffiliateProductCategory';
		$daoFilter->setModel($class)
				  ->setIndexBy('slug')
				  ->setFields(array('id', 'title', 'slug'));
		//$list = Rhema_Model_Abstract::findAll($daoFilter); 
		$list = Rhema_Model_Service::createQuery($daoFilter)->execute();

		return $list ? $list : array();
	}
	
	protected function _listManufacturer(){
		$daoFilter = new Rhema_Dao_Filter();
		$class     = MODEL_PREFIX . 'AffiliateProductManufacturer';
		$daoFilter->setModel($class)
				  ->setIndexBy('slug')
				  ->setFields(array('id', 'title', 'slug'));
		//$list = Rhema_Model_Abstract::findAll($daoFilter); 
		$list = Rhema_Model_Service::createQuery($daoFilter)->execute();	
		if(!$list){
			$data = array('title' => 'Unknown');
			$this->createMisc($class, $data);
		}		 
		return $list ? $list : array();
	}
		
	protected function _listOffers(){
		$daoFilter = new Rhema_Dao_Filter();
		$class     = MODEL_PREFIX . 'AffiliatePromotion';
		$daoFilter->setModel($class)
				  ->setIndexBy('slug')
				  ->setUnique(true)
				  ->setFields(array('id', 'title', 'slug'));
		//$list = Rhema_Model_Abstract::findAll($daoFilter); 
		$list = Rhema_Model_Service::createQuery($daoFilter)->execute();
		if(!$list){
			$data = array('title'     => 'No Offer',
						  'is_active' => 0);
			$this->createMisc($class, $data);
		}		 
		return $list ? $list : array();
	}
		
	protected function _doOperation($sql){	
		if(!$this->_con){
			$config = Rhema_SiteConfig::getConfig('settings.db.local');		
			$link   = mysql_connect($config['host'],$config['username'],$config['password']);
			if($link){
				$this->_con = true;
				mysql_select_db($config['dbname'], $link) or die('error database selection');
				$rows  =  mysql_query($sql); 
				$msg   = mysql_error($link);
			 	if($msg){
			 		div($msg);
			 	}
				return $rows;
			}else{
				die('error connection');
			}
		}
		 	
	}
	
	public function createMisc($class, $default = array()){
		$model		  = new $class();		
		$model->fromArray($default);
		$model->slug  = Doctrine_Inflector::urlize($default['title']);
		
		
		//$title        = 'General';
		//$model->title = $title;
		//$model->slug  = Doctrine_Inflector::urlize($title);
		$model->save();
		$model->free();
	}
	
	public function getUnmappedFields($feedCols, $mapped){
		$ignoreFields = array('PROGRAMURL', 'ADVERTISERCATEGORY', 'MANUFACTURERID', 'PROGRAMNAME');
		$mapped       = array_unique(array_merge(array_values($mapped), $ignoreFields))   ;
		$feedCols     = array_values($feedCols);
		$notMapped    = array_diff($feedCols, $mapped);
		
		return $notMapped;
	}
	
	/**
	 * Return feed values for unmapped data
	 * @param array $unMappedCols
	 * @param array $data
	 * @return Ambigous <string, mixed>
	 */
	public function getAdditionalDetails(array $unMappedCols, array $data, $values = array()){		
		$moreInfo = array();
		$dbCols   = array_keys($values);
		
		foreach($unMappedCols as $col){
			if(isset($data[$col]) and trim($data[$col]) and array_search($col, $dbCols) === false){
				$moreInfo[$col] = $data[$col];
			}
		}
		if(count($moreInfo)){
			$string    = var_export($moreInfo, true);	
			$stringRep = preg_replace('/[\s]+/', ' ', $string)	. ' ;';
		}else{
			$stringRep = ' ;';
		}
		return $stringRep ;
	}
 
 
}
	