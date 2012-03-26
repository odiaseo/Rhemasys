<?php
/**
 * Paginator adapter for paging lucene search results
 * @author odiaseo
 *
 */
class Rhema_Adapter_Paginator_LuceneSearch implements Zend_Paginator_Adapter_Interface
{
     
    protected $_query; 
    protected $_rowCount;
    protected $_cacheId ; 
 
    public function __construct(Zend_Search_Lucene_Search_Query $query)
    {
    	$this->_cacheId = 'lucene_search_' . md5("$query");
        $this->_query   = $query;          
    }
 
    public function getItems($offset, $itemsPerPage)
    {
		$publishedSet  = array();
		$search        = Rhema_Search_Lucene::getInstance()->getIndex();
		$resultSet     = $this->_getCachedResult();	  
		$end  		   = $offset + $itemsPerPage; 
		$rowId         = array();
		$isDev         = Rhema_SiteConfig::isDev();
		
		if($this->_rowCount < $end){
			$end = $offset + $this->_rowCount ;
		}
		$key = Rhema_Search_Lucene::$idKey;
		for($i= $offset; $i < $end ; $i++){
			if(isset($resultSet[$i]['id'])){
				try{
					$currentItem = $search->getDocument($resultSet[$i]['id'])->{$key};
					$rowId[]     = $currentItem;
				}catch(Exception $e){
					if($isDev){
						pd('id #' . $resultSet[$i]['id'] . ' not found in search index');
					}
				}
				 
			}
		}	
	 
		if(count($rowId)){
			$prodData = Rhema_Model_Service::factory('affiliate_product')->getProductById($rowId);
			if($prodData){
				foreach($rowId as $id)	{
					if(isset($prodData[$id])){
						$publishedSet[$id] = $prodData[$id] ;
					}
				}
			}
		}
		//pd($publishedSet);
		return $publishedSet ;		
    }

    /**
     * Count results
     *
     * @return int
     */
    public function count()
    {
        if ($this->_rowCount === null) {
        	$res = $this->_getCachedResult();
            $this->_rowCount = $res ? count($res) : 0;
        }
        return $this->_rowCount;
    }

    /**
     * Set the row count
     *
     * @param int $rowCount
     */
    public function setRowCount($rowCount)
    {
        $this->_rowCount = $rowCount;
        return $this;
    }
    
    private function _getCachedResult(){ 
    	$cache     = Rhema_Cache::getDefaultCache();
    	$resultSet = array();
		if(!$resultSet = $cache->load($this->_cacheId)){ 
			Rhema_Util::setMemoryLimit(1200);
			if(Rhema_SiteConfig::isDev()){
				Zend_Search_Lucene_Search_QueryParser::dontSuppressQueryParsingExceptions() ;
			} 

			if(is_string($this->_query)){
				try{			 
					$query    = Zend_Search_Lucene_Search_QueryParser::parse($this->_query);
				}catch(Zend_Search_Lucene_Search_QueryParserException $e){
					echo "Query syntax error: " . $e->getMessage() . "\n" ;
				}
			}else{
				$query = $this->_query ;
			}
			
			$search   = Rhema_Search_Lucene::getInstance();
			try{
				$res  = $search->find($query);		
			}catch(Exception $e){
				$res  = false;
			}
		 			
			if($res){
				foreach($res as $hit){
					$resultSetEntry 		 = array();
					$resultSetEntry['id']    = $hit->id;
					$resultSetEntry['score'] = $hit->score ;
					$resultSet[] 			 = $resultSetEntry ; 					
				}				
				$cache->save($resultSet, $this->_cacheId, array(__CLASS__));
			}else{
				$resultSet = array();
			}			
		} 
		return $resultSet ;    	
    }
}