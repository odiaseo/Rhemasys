<?php

class Ecom_AffiliateController extends Zend_Controller_Action {
	
	const PREVIEW_LIMIT = 25;
	
	
	public function indexAction(){
    	//$this->_table 				= 'affiliate_product';
    	//$this->_helper->displayGrid(); 		  	
	}
	
	public function setupAction(){
    	$this->_request->setParam('table', 'affiliate_feed');
    	$options['preGenerateHooks'] = array(
    		'addFeedColumns' => array()
    	);
    	
     	$this->_helper->displayGrid($options); 			
	}
	
	public function dataImportAction(){
    	$this->_request->setParam('table', 'affiliate_product');
    	$this->_helper->displayGrid(); 			
	}
	
	public function networkAction(){
    	$this->_request->setParam('table', 'affiliate_retailer');
    	$this->_helper->displayGrid(); 		
	}
	
	public function metadataAction(){
		set_time_limit(0);
		$networkId 		 = $this->_request->getParam('id');
		$table			 = $this->_request->getParam('table','affiliate_feed');
		$task            = $this->_request->getParam('task','preview');
		$networkModel    = MODEL_PREFIX . 'AffiliateNetwork';
		$listType	     = $this->_request->getParam('type', 'retailer');
		$feedHelper      = $this->_helper->getHelper('feedImport');
		
		$netTable		 = Doctrine_Core::getTable($networkModel);
		$data		     = $netTable->find($networkId);
		if($listType == 'retailer'){
			$key       = 'merchant_metadata';
			$prodModel = MODEL_PREFIX . 'AffiliateRetailer';
			$mapField  = 'merchant_mapping';
		}else{
			$key       = 'category_metadata';
			$prodModel = MODEL_PREFIX . 'AffiliateProductCategory';
			$mapField  = 'category_mapping';
		}
		 		
		clearstatcache();
		$cacheFile   = Rhema_Util::getFeedCacheFilename($data[$key]);
						
		if(!file_exists($cacheFile)){
			$downloadData = file_get_contents($data[$key]);
			if($downloadData){
				file_put_contents($cacheFile, $downloadData);
			}
		}
							
		switch($task){			
			case 'preview':{
				if($data and $data[$key] and $data[$key]){						 
					if(($handle = fopen($cacheFile,'r')) !== false){									
						$delimiter = Rhema_Util_String::getDelimiter($handle); 			
						$feedCols  = fgetcsv($handle, null, $delimiter);
						array_walk($feedCols, array('Rhema_Util_String','stripWhiteSpaces'));
						$count    = 0;		
						$mapped   = array();
				        $values   = array();
							        
				        $mapping = Zend_Json::decode($feedData->{$mapField}, true);
				        $dbCols  = Doctrine_Core::getTable($prodModel)->getColumnNames();
				        
				        foreach($dbCols as $col){ 
				        	if(isset($mapping[$col]['columns']) and $mapping[$col]['columns']){
					        	$mapped[$col] = (string) trim($mapping[$col]['columns']);
					        }
					    }	 
			         
				        while (($data = fgetcsv($handle, null, $delimiter)) !== false and $count < self::PREVIEW_LIMIT) {
				            $values[] = array_combine($feedCols, $data); 
				            $count++; 
				        }				 
						 						
				        fclose($handle);  
				        $unMapped = $feedHelper->getUnmappedFields($feedCols, $mapped);
				        
				        $this->view->notMapped   = $unMapped ;
				        $this->view->feedColumns = $feedCols;
				        $this->view->feedValues  = $values;
				        $this->view->mappedCols  = $mapped ; 						 
					}
				}
				break;
			}
			
			case 'map':{
				
				break;
			}
			default:{
				
				break;
			}
		}
		 
	}
	
	
	public function previewMappingAction(){
		$id 		     = $this->_request->getParam('id');
		$table		     = $this->_request->getParam('table','affiliate_network'); 
		$feedModel       = MODEL_PREFIX . 'AffiliateNetwork'; 
		$prodModel       = MODEL_PREFIX . 'AffiliateProduct';		
		$feedUrl         = false ;
		
		if($table == 'affiliate_feed'){
			$feed         = Doctrine_Core::getTable(MODEL_PREFIX . 'AffiliateFeed')->find($id);
			$networkId    = $feed['affiliate_network_id'];
			$feedUrl      = $feed['feed_url'];
			$fieldMapping = $feed->field_mapping;
		}else{
			$networkId = $id ;
		}
		
		$prodTable       = Doctrine_Core::getTable($feedModel);
		$feedData  	     = $prodTable->find($networkId);	
		$feedUrl         = $feedUrl ? $feedUrl : $feedData['feed_url'];
		$cacheFile       = Rhema_Util::getFeedCacheFilename($feedUrl);	
		
		$return['error'] = 0;
 
		
		$task           = $this->_request->getParam('task');
		$feedHelper     = $this->_helper->getHelper('feedImport');
		
		if(false and 'update' == $task){
		    $newContent = file_get_contents($feedUrl);
		    if($newContent){
		    	file_put_contents($cacheFile, $feedUrl);
		    	$message = $cacheFile . ' updated successfully';
		    	$msgType = Rhema_Dto_UserMessageDto::TYPE_SUCCESS;
		    }else{
		    	$message = 'Unable to download ' . $feedUrl ;
		    	$msgType = Rhema_Dto_UserMessageDto::TYPE_WARNING;
		    	$return['error'] = 1;
		    }
		    $this->_helper->sendAjaxMessage($message, __FUNCTION__, $msgType); 
		}elseif(file_exists($cacheFile) and ($handle= fopen($cacheFile,'r')) !== false){  
			$delimiter = Rhema_Util_String::getDelimiter($handle); 
			$feedCols  = fgetcsv($handle, null, $delimiter);	
			array_walk($feedCols, array('Rhema_Util_String','stripWhiteSpaces'));
			$count    = 0;		
			$mapped   = array();
	        $values   = array();
			$fieldMapping = $fieldMapping ? $fieldMapping : $feedData->field_mapping ;	        
	        $mapping      = Zend_Json::decode($fieldMapping, true);
	        $dbCols       = Doctrine_Core::getTable($prodModel)->getColumnNames();
	        
	        foreach($dbCols as $col){
	        	if(isset($mapping[$col]['columns']) and $mapping[$col]['columns']){
	        		$mapped[$col] = $mapping[$col]['columns'];
	        	}
	        }	 

	        $colCount = count($feedCols);
	        if($task == 'preview'){
		        while (($data = fgetcsv($handle, null, $delimiter)) !== false and $count < self::PREVIEW_LIMIT) {
		        	$data     = array_slice($data, 0, $colCount);
		            $values[] = array_combine($feedCols, $data); 
		            $count++; 
		        }
		        
		        fclose($handle);  
		        $unMapped = $feedHelper->getUnmappedFields($feedCols, $mapped);
		       // pd($unMapped, $this->_getNotMappedFields($feedCols, $mapped));
		        $this->view->notMapped   = $unMapped ;
		        $this->view->feedColumns = $feedCols;
		        $this->view->feedValues  = $values;
		        $this->view->mappedCols  = $mapped ; 
		        $this->view->mapping     = $mapping;
						
		        $data   		  = $this->view->render('affiliate/preview-mapping.phtml'); 	
		       // pd($data);	
		        $return['data']   =  utf8_encode(Rhema_Util_String::stripWhiteSpaces($data))	;
		        //pd($return);
		        $this->_helper->json->sendJson($return); 
		         
	        }else{	  
	        	list($message, $msgType)     = $feedHelper->generateSql($cacheFile, $networkId); 
	        	$this->_helper->sendAjaxMessage($message, __FUNCTION__, $msgType);
	        }
        }else{  
        	$msg = new Rhema_Dto_UserMessageDto('Feed not found -'  .$cacheFile , __FUNCTION__, Rhema_Dto_UserMessageDto::TYPE_ERROR, false);
        	$return['error'] = 1;  
        	$return['data']  = $this->view->printUserMessage($msg);
        	$this->_helper->json->sendJson($return);
        }
        
        return $message;         	
	}
	
	protected function _getNotMappedFields($feedCols, $mapped){
		$mapped    = array_unique(array_values($mapped));
		$feedCols  = array_values($feedCols);
		$notMapped = array_diff($feedCols, $mapped);
		
		return $notMapped;
	}
	
	public function metadataMappingAction(){
		$networkId 		 = $this->_request->getParam('id');
		$table			 = $this->_request->getParam('table','affiliate_feed');
		$task            = $this->_request->getParam('task','preview');
		$networkModel    = MODEL_PREFIX . 'AffiliateNetwork';
		$listType	     = $this->_request->getParam('type', 'retailer');
		$feedHelper      = $this->_helper->getHelper('feedImport');
		
		$netTable		 = Doctrine_Core::getTable($networkModel);
		$feedData		 = $netTable->find($networkId);
	 
		if($listType == 'retailer'){
			$key       = 'merchant_metadata';
			$prodModel = MODEL_PREFIX . 'AffiliateRetailer';
			$mapField  = 'merchant_mapping';
		}else{
			$key       = 'category_metadata';
			$prodModel = MODEL_PREFIX . 'AffiliateProductCategory';
			$mapField  = 'category_mapping';
		}
		
        if($this->_request->isPost()){
        	$task = $this->_request->getPost('task');
        	
        	switch($task){
        		case 'savemap':
        		default: {
        				$str = $this->_request->getParam('str');
        				parse_str($str, $mapData);  
        				$toSave  = Zend_Json::encode($mapData);
        				$feedData->field_mapping = $toSave;
        				
        				try{
        					$feedData->save();
        					$message = 'Feed mapping saved successfully';
        					$type    = Rhema_Dto_UserMessageDto::TYPE_SUCCESS;
        					$return['error'] = 0;
        				}catch(Exception $e){
							$message = $e->getMessage();
							$type    = Rhema_Dto_UserMessageDto::TYPE_ERROR;
							$return['error'] = 1;
        				}
        				$userMessage     = new Rhema_Dto_UserMessageDto($message, 'Feed Mapping', $type);		 
						$return['data']  = $this->view->printUserMessage($userMessage);
						
					
        			break;
        		}
        	}
        }else{
 
			if($feedData['feed_url']){ 
				$cacheFile    = Rhema_Util::getFeedCacheFilename($feedData['feed_url']);	
				 
				if(!file_exists($cacheFile)){					
 
					$message[]       = 'Feed not available, please download feed from affilliate network';
					$message[]       = 'Feed Url : ' . $feedData['feed_url'];
					$message[]       = 'Save as filename : ' . $cacheFile;	 
					
					$userMessage     = new Rhema_Dto_UserMessageDto($message, 'Feed Download', Rhema_Dto_UserMessageDto::TYPE_WARNING, false);	
					$this->view->userMessage = 	 $userMessage ;  		 
				}else{
					 
					if(($fp = fopen($cacheFile,'r')) !== false){
						$feedColumns = fgetcsv($fp); // get first line from the feed
						fclose($fp);
					}
					  
				};
			}
			
			$feedColumns = array_filter($feedColumns);			
			$selOptions  = array_combine($feedColumns, $feedColumns); 
			
			array_change_key_case($selOptions, CASE_LOWER);
			ksort($selOptions); 
			
			$mapped         =  Zend_Json::decode($feedData->field_mapping, true);
			$this->view->feedColumns = $selOptions;
			$this->view->feedFilters = array();
			$this->view->affData     = $feedData 	;
			$this->view->feedId      = $networkId;
			$this->view->mapped      = $mapped;
			$this->view->cacheFile   = $cacheFile ;
			$this->view->columns     = Doctrine_Core::getTable($prodModel)->getColumnNames();
			$this->view->ignoreList  = array_merge(Rhema_Grid_Adapter_DoctrineModel::$ignoreFields, Rhema_Grid_Adapter_DoctrineModel::getBannedList());	
        }

	}	
	public function mapFieldAction(){
		$table	  		 = $this->_request->getParam('table','affiliate_feed');
		$task     		 = $this->_request->getQuery('task', $this->_request->getPost('task', 'preview-mapping'));
        $listType	     = $this->_request->getPost('type', $this->_request->getParam('type', 'feed'));
        
        $feedColumns     = array();
		$tryDownload     = false;
		$tempCols        = array();
 		$mapping         = false ;
 		
		switch($listType){			
			case 'feed' :{ 
				$id       = $this->_request->getParam('id', $this->_request->getQuery('id'));
					
				if($table == 'affiliate_feed'){ 
					$feedData     = Doctrine_Core::getTable(MODEL_PREFIX . 'AffiliateFeed')->find($id);
					$networkId    = $feedData['affiliate_network_id'];
					$feedUrl      = $feedData['feed_url'];
					$mapping      = $feedData->field_mapping;
				}else{
					$networkId    = $id ;	 
				}				
					
				$feedTable       = Doctrine_Core::getTable(MODEL_PREFIX . 'AffiliateNetwork');					
				$networkData     = $feedTable->find($networkId);
				$feedData		 = $feedData ? $feedData : $networkData ;
								
				$prodModel       = MODEL_PREFIX . 'AffiliateProduct';  
				$mapField        = 'field_mapping';
				$key             = 'feed_url';
				$listType        = 'feed';
				$mapping         = $mapping ? $mapping : $networkData->field_mapping ;
		 
				break;
			} 
		
			case 'retailer':
			case 'category':{			
				$networkId 		 = $this->_request->getParam('id');
				$table			 = $this->_request->getParam('table','affiliate_feed');
				$task            = $this->_request->getParam('task','preview'); 
				$listType	     = $this->_request->getPost('type', $this->_request->getParam('type'));
				$feedHelper      = $this->_helper->getHelper('feedImport');
				
				$netTable		 = Doctrine_Core::getTable(MODEL_PREFIX . 'AffiliateNetwork');
				$feedData		 = $netTable->find($networkId);
			 
				if($listType == 'retailer'){
					$key        = 'merchant_metadata';
					$prodModel  = MODEL_PREFIX . 'AffiliateRetailer';
					$mapField   = 'merchant_mapping';
				}else{
					$tempCols   = Admin_Model_AffiliateProductCategory::$tempCols ;
					$key       	= 'category_metadata';
					$prodModel 	= MODEL_PREFIX . 'AffiliateProductCategory';
					$mapField  	= 'category_mapping';				    
				}
				$mapping    = $feedData->{$mapField};
			}
			break;
		}
		
		$return	 = array(
			'table'	=> $table,
			'error'	=> 0,
			'type'	=> $listType
		);
		
		switch ($task) {
			case 'savemap' :
				{
					$str = $this->_request->getParam ( 'str' );
					parse_str ( $str, $mapData );
					$toSave = Zend_Json::encode ( $mapData );
					$feedData->$mapField = $toSave;
					
					try {
						$feedData->save ();
						$message = 'Feed mapping saved successfully';
						$type = Rhema_Dto_UserMessageDto::TYPE_SUCCESS;
						$return ['error'] = 0;
					} catch ( Exception $e ) {
						$message = $e->getMessage ();
						$type = Rhema_Dto_UserMessageDto::TYPE_ERROR;
						$return ['error'] = 1;
					}
					$userMessage = new Rhema_Dto_UserMessageDto ( $message, 'Feed Mapping', $type );
					$return ['data'] = $this->view->printUserMessage ( $userMessage );
					
					break;
				}
			default :
				{					
					if ($feedData [$key]) {
						$cacheFile  = Rhema_Util::getFeedCacheFilename ( $feedData [$key] );
						$fileExists = file_exists ( $cacheFile );
						if($table != 'affiliate_feed' and !$fileExists){
							file_put_contents($cacheFile , file_get_contents($feedData [$key]));
						}
						clearstatcache();
						if (!$fileExists) {
							
							$message [] = 'Feed not available, please download feed from affilliate network';
							$message [] = 'Feed Url : ' . $feedData [$key];
							$message [] = 'Save as filename : ' . $cacheFile;
							
							$userMessage = new Rhema_Dto_UserMessageDto ( $message, 'Feed Download', Rhema_Dto_UserMessageDto::TYPE_WARNING, false );
							$return ['data'] = $this->view->printUserMessage ( $userMessage );
							$return ['error'] = 1;
						
						} elseif (($fp = fopen ( $cacheFile, 'r' )) !== false) {
							$delimiter = Rhema_Util_String::getDelimiter($fp); 
							$feedColumns = fgetcsv ( $fp , null, $delimiter ); // get first line from the feed
							fclose ( $fp );
							
							$feedColumns = array_filter ( $feedColumns );
							$selOptions = array_combine ( $feedColumns, $feedColumns );
							
							array_change_key_case ( $selOptions, CASE_LOWER );
							ksort ( $selOptions );
							
							$mapped    = Zend_Json::decode ($mapping, true );
							$dbColumns = Doctrine_Core::getTable ( $prodModel )->getColumnNames ();

							$filterClass   = new Rhema_Util_FeedFilter();
							$filterMethods = get_class_methods($filterClass);
							
							$this->view->feedColumns 	= $selOptions;
							$this->view->feedFilters 	= $filterMethods; 
							$this->view->feedId 		= $networkId;
							$this->view->mapped 		= $mapped;
							$this->view->cacheFile 		= $cacheFile;
							$this->view->columns 		= array_merge($dbColumns, $tempCols) ;
							$this->view->ignoreList 	= array_merge ( Rhema_Grid_Adapter_DoctrineModel::$ignoreFields, Rhema_Grid_Adapter_DoctrineModel::getBannedList () );
							
							$data = $this->view->render ( 'affiliate/map-field.phtml' );
							$return ['data'] = Rhema_Util_String::stripWhiteSpaces ( $data );
						
						}
					} else {
						$userMessage = new Rhema_Dto_UserMessageDto ( "Feed Url ({$key}) not found" , __FUNCTION__, Rhema_Dto_UserMessageDto::TYPE_WARNING, false );
						$return ['data'] = $this->view->printUserMessage ( $userMessage );
						$return ['error'] = 1;
					}
				}
		}
    
        $this->_helper->json->sendJson($return); 
	}
}