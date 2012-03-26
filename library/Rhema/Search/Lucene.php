<?php

class Rhema_Search_Lucene {
    /**
     * Print messages
     * @var boolean
     */
    public $echoAddedRecords = false;
    
    /**
     * Do not add elements to the index, only read database
     * @var boolean
     */
    public $simulated = false;
    
    /**
     * number of records read at each loop to avoid timeouts
     */
    const RECORDS_BUFFER_SIZE = 250;
    
    /**
     * When new documents are added to a Lucene index, they are initially stored in memory instead of being immediately written to the disk.
     * This value tells Lucene how many documents to store in memory before writing them to the disk, as well as how often to merge multiple segments together.
     * With the default value of 10, Lucene will store 10 documents in memory before writing them to a single segment on the disk. The mergeFactor value of 10 also
     * means that once the number of segments on the disk has reached the power of 10, Lucene will merge these segments into a single segment.
     * For instance, if we set mergeFactor to 10, a new segment will be created on the disk for every
     * 10 documents added to the index. When the 10th segment of size 10 is added,
     * all 10 will be merged into a single segment of size 100. When 10 such segments of size 100
     * have been added, they will be merged into a single segment containing 1000 documents, and so on.
     * Therefore, at any time, there will be no more than 9 segments in each power of 10 index size.
     * see more details at http://onjava.com/pub/a/onjava/2003/03/05/lucene.html
     *
     */
    const MERGE_FACTOR = 40; //default 10
    

    /**
     * While merging segments, Lucene will ensure that no segment with more than maxMergeDocs is created.
     * For instance, if we set maxMergeDocs to 1000, when we add the 10,000th document, instead of merging
     * multiple segments into a single segment of size 10,000, Lucene will create a 10th segment of size 1000,
     * and keep adding segments of size 1000 for every 1000 documents added.
     */
    const MAX_MERGE_DOCS = 2147483647; //default PHP_INT_MAX = 2147483647;
    

    /**
     * Zend search index
     * @var \Zend_Search_Lucene_Proxy
     */
    protected $index = null;
    /**
     * path with indexes
     * @var string
     */
    protected $indexesDir;
    
    private static $instance = null;
    
    private $verboseMode = false;
    
    protected $options = array();
    
    public static $documentModel      = 'model';
    public static $idKey         = 'affiliate_product_id';
    	
    const INDEX_STATUS_TO_UPDATE = 'to_update';
    const INDEX_STATUS_TO_INDEX  = 'to_index';
    const INDEX_STATUS_TO_DELETE = 'to_delete';
    const INDEX_STATUS_INDEXED   = 'indexed';
    const INDEX_STATUS_NO_INDEX  = 'no_index';

	const OPERATION_DELETE       = 'OPERATION_DELETE';
	const OPERATION_UPDATE       = 'OPERATION_UPDATE';
	const OPERATION_ADD          = 'OPERATION_ADD';
	
    /**
     * Singleton
     * @return Lucene
     */
    public static function getInstance($useTemp = false){
        if(self::$instance === null){
            self::$instance = new self($useTemp);
        }
        return self::$instance;
    }
    
    /**
     * ctor: Saves settings internally from INI file.
     * Index is not YET opened (that should be done manually on-demand)
     */
    private function __construct($temp = false){
        $this->options    = Rhema_SiteConfig::getConfig('settings.search');
        if($temp){
        	$this->indexesDir = $this->options ['index_dir'];
        }else{
        	$this->indexesDir = $this->options ['index_ready_dir']['path'];
        }
        if(!file_exists($this->indexesDir)){
        	mkdir($this->indexesDir, 0755, true);
        } 
    }
    /**
     * Open the index
     * In case of exception, check if the folder exists and is writable. 
     * @return \Zend_Search_Lucene_Interface
     */
    public function getIndex(){
        if($this->index === null){
            try{
                // if not opened in shm, open on disk
                if($this->index === null){
                    $this->index = Zend_Search_Lucene::open($this->indexesDir);
                }
               // $this->index->setMaxMergeDocs(self::MAX_MERGE_DOCS);
                $this->index->setMergeFactor(self::MERGE_FACTOR);
            
            }catch(Zend_Search_Lucene_Exception $e){
                
                /*check if folder exists*/
                if(! file_exists($this->indexesDir)){
                    throw new Zend_Search_Exception("Directory {$this->indexesDir} does not exist");
                }
                /*check if folder is writable*/
                if(! is_writable($this->indexesDir)){
                    trigger_error("Directory {$this->indexesDir} is not writable", E_USER_WARNING);
                }
                
                /* create index if not exists and add some codes*/
                if(strpos($e->getMessage(), "Index doesn't exists") !== false){
                    throw new Zend_Search_Lucene_Exception("Index not found at [" . $this->indexesDir . "]. Create and add permissions.");
                }
                
                // any other exception
                throw new Zend_Search_Lucene_Exception($e->getMessage(), $e->getCode());
            }
        }
        return $this->index;
    }
    
    /**
     * Create index (no docs added with this function)
     * Called when index does not exist when opening
     */
    public function createIndex(){

        $this->index = Zend_Search_Lucene::create($this->indexesDir);
        echo "index created at '{$this->indexesDir}'\n";
    }
    
    public function clean(){
		 $num = 0;
    	/*
    	 * Delete products from inactive retailers
    	 */
    	$daoFilter = new Rhema_Dao_Filter();
    	$daoFilter->setModel(MODEL_PREFIX . 'AffiliateRetailer') 
    			  ->addCondition('is_active', 0)
    			  ->addField('id') ;
    	$query   = Rhema_Model_Service::createQuery($daoFilter);
    	$query->clearResultCache();
    	$inActiveRetailers = $query->execute(); 	
 
    	if($inActiveRetailers){
    		$retailerIds = array_keys($inActiveRetailers);
    		$daoFilter = new Rhema_Dao_Filter();
    		$daoFilter->setModel(MODEL_PREFIX . 'AffiliateProduct')
    			  ->setQueryType(Rhema_Dao_Filter::QUERY_TYPE_UPDATE) 
    			  ->setUpdateList(array('index_status' => 'to_delete'))     			  
    			  ->addCondition('affiliate_retailer_id', $retailerIds, Rhema_Dao_Filter::OP_IN) ; 
    		$num += Rhema_Model_Service::createQuery($daoFilter)->execute(); 
    	} 
    			
    	/*
    	 * Remove deleted products
    	 */
    	$daoFilter = new Rhema_Dao_Filter();
    	$daoFilter->setModel(MODEL_PREFIX . 'AffiliateProduct')
    			  ->setQueryType(Rhema_Dao_Filter::QUERY_TYPE_UPDATE)
    			  ->setUpdateList(array('index_status' => 'to_delete'))
    			  //->setDebug(true)
    			  ->addCondition('index_status', array('to_update', 'indexed', 'to_index'), Rhema_Dao_Filter::OP_IN)
    			  ->addCondition('deleted_at', null, Rhema_Dao_Filter::OP_NOT_NULL);
 	  
    	$num += Rhema_Model_Service::createQuery($daoFilter)->execute();  	
    	$num += Admin_Model_AffiliateProduct::updateExpiredCodes();
    	
    	return $num ;
    }
    /**
     * Update the Lucene index starting from the given timestamp.
     * At the end it updates the last modification time (read in the future by other function launched from cron job)
     * @param int $limit set to zero or null not to use any limit
     * @return array debug info
     * @throws \PDOException when mysql goes away :(
     */
    public function updateAll($limit = self::RECORDS_BUFFER_SIZE){
        $items = $this->clean();
 		if($items){
 			div($items . ' products flagged to delete');
 		}
 
        $ret ['numberOfDocumentsBefore'] = $this->getIndex()->numDocs();
        $ret ['ItemsDeleted']            = $this->_doOperation(self::OPERATION_DELETE, self::INDEX_STATUS_TO_DELETE, self::INDEX_STATUS_NO_INDEX, $limit);
        $ret ['ProductsToReindex']       = $this->_doOperation(self::OPERATION_DELETE, self::INDEX_STATUS_TO_UPDATE, self::INDEX_STATUS_TO_INDEX, $limit);
        $ret ['ProductsIndex']           = $this->_doOperation(self::OPERATION_ADD,    self::INDEX_STATUS_TO_INDEX, self::INDEX_STATUS_INDEXED, $limit);
        $ret ['numberOfDocumentsAfter']  = $this->getIndex()->numDocs();
        $this->index->commit();
        
        return $ret;
    }
    
    protected function _doOperation($operationType, $inputStatus, $newStatus, $limit = self::RECORDS_BUFFER_SIZE){
    	ini_set('display_errors', 'off');
    	error_reporting(0);
        $daoFilter = new Rhema_Dao_Filter();
        $model     = MODEL_PREFIX . 'AffiliateProduct' ;
        
        $daoFilter->setModel($model)
                  ->setLimit($limit) 
                  ->addCondition('(index_status <> ? AND index_status = ?)', array($newStatus, $inputStatus), Rhema_Dao_Filter::OP_RAW_SQL); 
        
        if($operationType == self::OPERATION_DELETE){
        	$daoFilter->addField('id', 'index_status');
        }else{
        	$prdObj    = new $model();
            $daoFilter->addCondition('is_archived', 0) 
            		  ->removeField('product_data')  
            		  ->addCondition('is_expired', 0)
            		  ->addFields(array('id', 'description', 'title', 'affiliate_product_type_id', 'code','network_promotion'))       		  
            ; 
        }

        $query       = Rhema_Model_Service::createQuery($daoFilter);
        $query->clearResultCache();        
        $startCount  = $query->count();
        
        $query->free();
        unset($query);
        
        $this->_displayIfVerbose("\n[$operationType][$inputStatus]: $startCount records ... status will set to $newStatus");
        
        $numDocs 	= (int) $this->numDocs();
        $batch   	= 0;
        $nRows   	= 0;
        $done    	= 0;
 		$lastFirst  = 0;
 		$optimal    = ceil(50000/$limit);
 		
        div("Processing " . $startCount. " records in batches");              
        do{          	 
        	$q  = Rhema_Model_Service::createQuery($daoFilter); 
        	if($q->getResultCacheDriver()){ 
        	  	  $q->clearResultCache();
        	}                 
            $items = $q->execute(); 
            $q->free(true);
            unset($q);
                       
            $count = $items ? count($items) : 0;
            
            if($count){ 
            	$batch++;  
            	$deleted[$batch] = 0;  
            	$arr             = array();       	           
                foreach($items as $row){  
                	$arr [] = $row ['id'];              
                    if(self::OPERATION_DELETE == $operationType){
                        if($numDocs){
                        	div('  >> deleting record ID ' . $row['id'], '');
                            $del = $this->_deleteDocumentFromIndex($row['id'], $model); 
                            if($del){
                            	div("$del record(s) deleted", "\n",'');
                            }else{
                            	div('not found', "\n", '');
                            }                            
                        }
                    }elseif(self::OPERATION_ADD == $operationType){ 
                    	try{
                    		$deleted[$batch] += $this->_deleteDocumentFromIndex($row['id'], $model);  
                        	$this->_addRowToIndex($row, $model); 
                    	}catch(Exception $e){
                    		unset($arr[$row ['id']]); 
                    		// save already indexed rows to database before falling over
                    		$done   += Admin_Model_AffiliateProduct::updateIndexStatus($arr, $newStatus);
                    		div(array($e->getMessage(), $row['id']));
                    		exit();
                    	}
                    }                    
                    $nRows ++;
                }
                
                if($lastFirst === $arr[0]){
                	$text = sprintf("Loop detected. %s = %s in batch %d\n\n", $lastFirst, $arr[0], $batch);
                    die($text); 
                }elseif(count($arr)){
                	$lastFirst = $arr[0] ; 
                }
                try{
                	//div("\nupdating row index status to ". $newStatus, '');
                	$done   += Admin_Model_AffiliateProduct::updateIndexStatus($arr, $newStatus);
                	$arr     = array();
                   // div('done!', "\n", '');
                }catch(Exception $e){
                	echo $e->getMessage();
                	exit();
                }
                $percent = round(($nRows*100)/$startCount,2) . '%';
                $balance = ($startCount - $done);
                
                if($balance){
                	$msg[]    = sprintf("  >> %d records in batch %d completed, done %s, %d remaining", $count, $batch, $percent, $balance) . ' (' . mu(true) . ')';
                	if(isset($deleted[$batch]) and $deleted[$batch]){
                		$msg[]    = $deleted[$batch] . ' rows were updated';
                	}
                }else{
                	$msg[]    = sprintf("  >> %d records in batch %d completed, done %s", $count, $batch, $percent) . ' (' . mu(true) . ')';
                }
                
                div($msg, "\n", ''); 
                            
                if($batch%$optimal == 0){
                	$opStart = time();
                	div('  >>> optimizing index', '');
                	$this->optimizeIndex();
                	$opDur = time() - $opStart;
                	div("done ({$opDur} sec)", "\n", '');
                	break;
                }
                
                $msg     = array();                           
            }
       }while($count > 0);
        
        div('[end!]');
        
        return array('to process' => $startCount, 'done' => $nRows);
    }
    
    /**
     * Removes documents from index by retailer type
     * @param unknown_type $retailerTypeId
     * @param unknown_type $limit
     * @return number
     */
    public function removeProductByRetailerTypeFromIndex($retailerTypeId, $limit = 250){
    	div("\nDeleting products with retailer_type_id = $retailerTypeId from index", "\n\n", '');
    	$obj         = new Admin_Model_AffiliateProduct();
    	$paginator   = $obj->getProductIdByRetailerType($retailerTypeId, $limit, 1, false);
    	$totalPages  = count($paginator);    	 
    	$num         = 0;    	
    	$done        = 0 ;
    	
    	if($totalPages){
    		div("$totalPages pages found", "\n", '');
	    	for($i=1; $i <= $totalPages; $i++){
	    		div("  >> Processing page $i", '');
	    		$productId   = array();
	    		$paginator   = $obj->getProductIdByRetailerType($retailerTypeId, $limit,  $i, false);
	    		foreach($paginator as $item){
	    			$productId[] = $item['id'];
	    			$num += $this->_deleteDocumentFromIndex($item['id']);    			
	    		}
	    		$done  += Admin_Model_AffiliateProduct::updateIndexStatus($productId, self::INDEX_STATUS_NO_INDEX);
	    		div('done!' , "\n", '');
	    	}
	    	if($done){
	    		div("$done products set to " . self::INDEX_STATUS_NO_INDEX);
	    	}
    	}    	
    	return $num ;
    }
    
    /**
     * Delete from index, if it exists
     *
     */
    private function _deleteDocumentFromIndex($value, $model = ''){
        $field    = self::$documentModel;
        $col      = self::$idKey ;
		$count    = 0;
        $term     = new  Zend_Search_Lucene_Index_Term($value, self::$idKey) ;
		$index    = $this->getIndex(); 
        $docs     = $index->termDocs($term);
        
        foreach($docs as $hit){ 
            $index->delete($hit->id); 
            $count++;
        }
       
        return $count ;
    }
 
    
    private static function rowEncode($row){ 
        return serialize($row);
    }
    
    public static function rowDecode($row){
        return unserialize($row);
    }
    
    /**
     * Convert code row array to \Zend_Search_Lucene_Document and Add to index
     *
     * @param array $row
     * @param int $recordType self::TYPE_STORE | self::TYPE_CODE
     * @return \Zend_Search_Lucene_Document
     */
    private function _addRowToIndex($row, $recordType){ 
    	$index = $this->getIndex();
        $doc   = new Zend_Search_Lucene_Document();
 		
        $doc->addField(Zend_Search_Lucene_Field::keyword(self::$documentModel, $recordType)); 
        $doc->addField(Zend_Search_Lucene_Field::keyword(self::$idKey, $row['id']));  
        $doc->addField(Zend_Search_Lucene_Field::keyword('type',$row['affiliate_product_type_id'])); 
        $doc->addField(Zend_Search_Lucene_Field::keyword('code',$row['code']));   
        $doc->addField(Zend_Search_Lucene_Field::text('title', $row['title']));
        $doc->addField(Zend_Search_Lucene_Field::text('description',$row['description'])); 
 
        if($row['network_promotion']){
        	$doc->addField(Zend_Search_Lucene_Field::text('promotion',$row['network_promotion']));
        }
/*  
        if(isset($row['AffiliateRetailer']['tags']) and $row['AffiliateRetailer']['tags']){
        	$tags   = array_unique(array_filter(explode(',', $row['AffiliateRetailer']['tags'])));
        	array_walk($tags, array($this, 'cleanTag'));        	
        	$tagStr = implode(' ', $tags);
        	$doc->addField(Zend_Search_Lucene_Field::text('tags',$tagStr));
        }
        
        if(isset($row['AffiliateRetailer']['title']) and $row['AffiliateRetailer']['title']){
        	$doc->addField(Zend_Search_Lucene_Field::text('storeTitle', $row['AffiliateRetailer']['title']));
        }*/
 
        $index->addDocument($doc);  
        $index->commit();       
 
        return $doc;
    }
    
    public static function cleanTag($str){
    	return Doctrine_Inflector::urlize($str);
    }
    
    /**
     * Call find of zend search
     * @param unknown_type $query
     */
    public function find($query){ 
    	try{
       		return $this->getIndex()->find($query);     
    	}catch(Exception $e){
    		if(Rhema_SiteConfig::isDev()){
    			pd((string)$query, $e->getMessage());
    		}
    		return false;
    	}	 
    }
    
    /**
     * Optimize index
     */
    public function optimizeIndex(){
    	//return 'optimize not allowed';
        return $this->getIndex()->optimize();
    }
    
    /**
     * Get number of documents (deleted not included, all segments are asked)
     */
    public function numDocs(){
        return $this->getIndex()->numDocs();
    }
    
    /**
     * Drop the whole index !! be careful
     */
    public function deleteIndex(){
        
        $this->getIndex();  
        echo "deleting index at " . $this->indexesDir . " ...\n";
        $deletedFiles = 0;
        foreach(new DirectoryIterator($this->indexesDir) as $fileInfo){
            if(! $fileInfo->isDir()){
                $fname = $fileInfo->getPathname();   
                if(unlink($fname)){
                     $deletedFiles ++;
                     echo div("deleted $fname");
                }                             
            }
        }
        if($deletedFiles){
        	try{
            	$done = Admin_Model_AffiliateProduct::resetIndexStatus(); 
        	}catch(Exception $e){
        		throw $e->getMessage();
        	}
            div("\ndeleted $deletedFiles files. $done products index_status set to to_index\n");
        }
        return $deletedFiles;
    }
    
    /**
     * Return the total size of the index and the number of files
     * @return array array(#files, #size in bytes, readable format)
     */
    public function getIndexSize(){
        $this->getIndex(); //to check the existance
        $size = $nFiles = 0;
        foreach(new DirectoryIterator($this->indexesDir) as $fileName => $fileInfo){
            if(! $fileInfo->isDir()){
                $fname = $fileInfo->getPathname(); 
                $size += @filesize($fname);
                $nFiles ++;
            }
        }
        return array($nFiles, $size, sprintf('%s Mb in %d files', round($size / 1048576, 2), $nFiles));
    }
    
    /**
     * return array of settings under sites.ini->search
     * @return array
     */
    public function getSettings(){
        return Rhema_SiteConfig::getConfig('settings.search');    
    }
    
    /**
     * Setter for verbose mode
     * @param boolean $mode
     */
    public function setVerboseMode($mode){
        $this->verboseMode = $mode;
    }
    
    /**
     * Display text on the console if verbose mode is On
     * @param string $msg
     */
    private function _displayIfVerbose($msg, $newLine = PHP_EOL){
        if($this->verboseMode){
            echo $msg . $newLine;
        }
    }
    
    /**
     * Set result limit, static call
     * @param int $limit
     */
    public static function setLimit($limit){
        Zend_Search_Lucene::setResultSetLimit($limit);
    }
    
    /**
     *
     */
    protected function _executeCommand($cmd, &$output = null, &$return = null){
        echo "executing [$cmd]\n";
        exec($cmd, $output, $return);
    }
    
    public function syncIndexToRemoteServers($domain){ 
    	$diskIndexPath  = rtrim($this->options['index_dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    	    		       
 		$diskIndexPath  = realpath($diskIndexPath) . DIRECTORY_SEPARATOR; 
   		$publicPath     = '/var/www/vhost/public_html/' . SITE_DIR ;
   		
 		if(file_exists($diskIndexPath)){
	 		$remoteTempPath = $this->options['remote_sync']['path']['temp'];
	 		$remoteLivePath = $this->options['remote_sync']['path']['live'];
	 			
	 		$remoteIps      = $this->options['servers'];
	 		foreach((array)$remoteIps as $ip){
	 			unset($out);
	 			$cmd  = "rsync -rvi --stats --force --delete {$diskIndexPath} live@{$ip}:{$remoteTempPath}" ;
	 			$this->_executeCommand($cmd, $out);
	 			
	 			$sync = "ssh live@{$ip} php $publicPath/index.php --servername $domain --action lucene --params=\"task=sync\" --verbose";
	 			$this->_executeCommand($sync, $out);
	 			print_r($out);
	 		} 		 
 		}else{
 			echo "\n{$diskIndexPath} not found";
 		}
    }
        
    public function syncLocalIndex($verbose = false){
        // get paths and add final slash if not added
        $shmPath       = rtrim($this->options['index_ready_dir']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $readyFileFlag = $shmPath . $this->options ['index_ready_dir'] ['file'];
        $diskIndexPath = rtrim($this->options['index_dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR; 
 
        if(!file_exists($shmPath)){ 			
 			@mkdir($shmPath, 755, true);
 			div('live index diretory created : ' . $shmPath);
 		}
 		      
 		$diskIndexPath = realpath($diskIndexPath) . DIRECTORY_SEPARATOR;
  		$shmPath       = realpath($shmPath) . DIRECTORY_SEPARATOR; 
  
 		if(PHP_OS == 'WINNT'){
 			if(file_exists($readyFileFlag)){
 				div('removing lock file ' . $readyFileFlag);
	        	@unlink($readyFileFlag);
	        } 	
	        div('deleting old indexes in ' . $shmPath);
	        $count  = Rhema_Util::cleanupDirectory($shmPath);
	        div("$count files deleted");
	        
	         div("copying files from $diskIndexPath to $shmPath ");
	         copy_r($diskIndexPath, $shmPath, 0777);
	         file_put_contents($readyFileFlag, '');
	       
	         div("\n\n lock file created $readyFileFlag \n\n", '', '');
 		}else{	
 			@exec('which rsync', $output);        
        	$rsyncPath = current($output);            
        	div('command found - ' . $rsyncPath);
        
	        //remove lock index on shmPath from all servers (localhost included)
	        $rmFlagFileCommand = "rm -f $readyFileFlag";	 
	        $this->_executeCommand($rmFlagFileCommand);
	        
	        $commands = array("rm -rf $shmPath",               //remove internal files
						      "mkdir -p $shmPath",             //in case server is restarted or 1st time  
							  "rsync -rai --stats --force --delete {$diskIndexPath} {$shmPath}", 
	        				  "chmod -R 775 $shmPath"
	                   ); 
	            
	        foreach($commands as $command){
	            $this->_executeCommand($command);
	        }
	                  
	        // restore the readyFileFlag
	        $touchReadyFileCmd = "touch $readyFileFlag";
	        $this->_executeCommand($touchReadyFileCmd, $output);
 		}
    }
    
    /**
     * Enter description here ...
     * @param unknown_type $query
     * @param unknown_type $page
     * @param unknown_type $limit
     * @return Zend_Paginator
     */
    public static function getPaginator($query, $page = 1, $limit = 25){  ;
    	$cacheManager     = Zend_Registry::get(Rhema_Constant::CACHE_MANAGER);    			
    	$cache     		  = $cacheManager->getCache('doctrine-cache'); 			 
		$paginatorAdapter = new Rhema_Adapter_Paginator_LuceneSearch($query); 
		$paginator        = new Zend_Paginator($paginatorAdapter);
		
		$paginator->setItemCountPerPage($limit)   
				  ->setCurrentPageNumber($page); 
 		$paginator->setCache($cache); 
 		
		return $paginator;    	
    }

}
