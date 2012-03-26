<?php

class Cms_SearchController extends Zend_Controller_Action
{

    public function init()    {
        /* Initialize action controller here */
        $this->rightNavView  = 'rightNavigation-search.tpl';
        
        parent::init();
    }

    /**
     * Retrieves and builds the search form located on the front page right navigation
     *
     * @return Zend_Form search form
     */
    public function getSearchForm(){
      	$searchForm     = new Priam_Form_Search_Database();
      	$urlParm		=  array('module'=>'default','controller'=>'search','action'=>'result');
    	$searchForm->setAction( $this->baseUrl . '/default/search/result'); 
    	return $searchForm;  	
    }
    
    public function indexAction() {
    	$request		= $this->getRequest(); 
		$searchForm     = $this->getSearchForm();        
	    $field			= $request->getParam('searchField','');
	    if('_at' == substr($field,-3)){
	    	$this->view->headScript()->appendScript('jQuery(document).ready(function(){priam.searchDatePicker(true); });');
	    }
        
		$this->view->layout()->searchForm = $searchForm->render();       
    }

    
    /**
     * When the search button is clicked from the search form an ajax call is made
     * This action retrieves the search parameters and builds the query
     * The database is searched and the result passed to the view
     * The view is rendered and returned via ajas
     * pagination is achieved using the Doctine Page and Layout...
     *
     */
    public function resultAction() { 
    	$request    = $this->getRequest();
    	$paging     = $request->getParam('paging', null); 
    	
   	    if($paging){
   	    	$temp 			= Priam_Util::getSessData('searchParams');
   	    	$temp['page'] 	= $paging;
   	    	   	
   	    	Zend_Registry::set('encode','html');
   	    	$request->setParams($temp );
   	    	  
   	    }else{
   	    	Priam_Util::setSessData('searchParams', $request->getParams());
   	    }
   	    
	    $post		    = $request->getParams();
    	$keywords      	= $request->getParam('keywords');
	 	$date			= $request->getParam('created_at');
	    $page			= $request->getParam('page', 1);
	    $field          = $post['searchField']; 
	    
		if($date and !$keywords){
			$request->setPost('keywords', $date);
		}elseif($keywords and !$date){
			$request->setPost('created_at', $keywords);
		} 
		
				
    	if($keywords){
    		$table    = Priam_Constant::MODEL_PREFIX . 'Document'; 
    		$keys	  = array_filter(explode(',', $post['keywords'])); 
    		
    		$query    = Doctrine_Query::create()
    						->select('t.title, t.description, t.type_id')
    						->from("$table t"); 
    						
    		if('*' != $keywords){
	    		foreach($keys as $val){
	    			$operator      = Priam_Util::getOperator($post['oper'], $val);
	    			$string[]      = "t.$post[searchField] $operator ? ";
	    			$parms[]       = $val;
	    		}
	    		
	    		$join 		 = strtoupper($post['join']);
	    		$queryString = join(" $join ", $string);
	    		
	    		$query->where($queryString, $parms);
    		}
    		
    		if(isset($post['type_id'])){
				$query->whereIn('t.type_id', $post['type_id']);
    		}
    		
    		if(isset($post['product_id'])){
				$query->whereIn('t.product_id', $post['product_id']);
    		}
    		
			$query->orderBy('t.type_id ASC, t.updated_at DESC');
			
			$query->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY); 
    					
    		//$sql   = $query->getSqlQuery();
    		
			//$pager  	= new Doctrine_Pager($query, $page, $post['limit']); 

			//====================== Pager layout definition===================== 
			//$pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' => 5 ), $pager); 
			
			//$this->view->pages = $pagerRange->rangeAroundPage();
			 
			$pagerLayout = new Doctrine_Pager_Layout(
			      new Doctrine_Pager($query, $page, $post['limit']), 
			      new Doctrine_Pager_Range_Sliding(array('chunk' => 5)),			            				      
			      $this->baseUrl . '/default/search/result/paging/{%page_number}'
			);
			$pagerLayout->setTemplate('<a href="#" rel="{%url}" id="page-{%page}" class="page-number ui-state-default">{%page}</a>');
			$pagerLayout->setSelectedTemplate('<a href="#" rel="{%url}" id="page-{%page}" class="page-number ui-state-active">{%page}</a>');
			
			$pager		= $pagerLayout->getPager();
			$result 	= $pager->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
							
			//$this->view->pagerLayout = $pagerLayout;
			//================================================================
			$byType	= array();
			foreach($result as $index => $data){
				$type             = $data['type_id'];
				$byType[$type][]  = $data;
			}
			
				$typeTable    = Priam_Constant::MODEL_PREFIX . 'Type';
			$typeQuery    = Doctrine_Query::create()
							->select('t.title, t.description')
							->from("$typeTable t INDEXBY t.id")
							->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
			$allTypes     = $typeQuery->execute();

			$this->view->searchResults  = $byType;	
			$this->view->allTypes		= $allTypes;	
			$this->view->itemUrl 		= $this->baseUrl . '/default/search/document/id/';	
			$rendered				    = $this->view->render('search/result.tpl');
			if($paging){
				$output = $rendered;
			}else{
				$output['main']    			=  $rendered;				 
				ob_start();  $pagerLayout->display(); 
				$output['pager']  		    = ob_get_clean();
			}
    	}else{
    		$output['main']  = 'No keyword entered';
    		$output['pager'] = '';
    	} 
    	
	    Priam_Util::setAjaxData($output);
			       		   	
    }
    
    /**
     * From the document id, the searched document is returned via ajax calls
     *
     */
    public function documentAction(){
    	$request       = $this->getRequest();
    	$itemid        = $request->getParam('id', null);
    	$documentType  = $request->getParam('type', null);
    	
    	
    	if($documentType and $itemid){
    		
    		$docTypeList   = Priam_Util::getSessData('doc_type_list');
    		$fieldList     =	Priam_Util::getSessData('field_list');	
    		$plateList     =	Priam_Util::getSessData('plate_list');
    		
	    	if(!$docTypeList or 'development' == APPLICATION_ENV){ 
	    		$docTable = Priam_Constant::MODEL_PREFIX . 'Type';
	    		$query = Doctrine_Query::create()
	    				->from("$docTable t INDEXBY t.id");
	    				
	    		$docTypeList = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
	    		
	    		Priam_Util::setSessData('doc_type_list', $docTypeList);		
	    	}
	    				

			if(!$fieldList or 'development' == APPLICATION_ENV){ 
	    		$fieldTable = Priam_Constant::MODEL_PREFIX . 'Field';
	    		$query = Doctrine_Query::create()
	    				->from("$fieldTable t INDEXBY t.id");
	    				
	    		$fieldList = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
	    		
	    		Priam_Util::setSessData('field_list', $fieldList);		
	    	} 

	    	if(!$plateList or 'development' == APPLICATION_ENV){ 
	    		$plateTable = Priam_Constant::MODEL_PREFIX . 'BoilerPlate';
	    		$query = Doctrine_Query::create()
	    				->from("$plateTable t INDEXBY t.id");
	    				
	    		$plateList = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
	    		
	    		Priam_Util::setSessData('plate_list', $plateList);		
	    	} 
	    	
	    	$templateId    			= isset($docTypeList[$documentType]) ? $docTypeList[$documentType]['template_id'] : null; 
	    	$layout        			= $currentLayout = Priam_Util::getLayout($templateId);  

    		$table    				= Priam_Constant::MODEL_PREFIX . 'Document'; 
    		$row 					= Doctrine_Core::getTable($table)->find($itemid, Doctrine_Core::HYDRATE_ARRAY);
    		$this->view->section 	= $this->buildDocument($layout , $row, $fieldList, $plateList); 
 
    		$output     			= $this->view->render('search/document.tpl');
	    	
    	
	    }else{
	    	$output     = 'Document not found';
	    }
    	
	    Priam_Util::setAjaxData($output);
    }
    
		public function buildDocument($layout, $rowData, $fieldList, $plateList){
			$document = array();
			for($i=0; $i<7; $i++){
				if(!isset($layout[$i])){
					$document[$i] = '';
				}else{
					$str  = "<div id='sxn-{$i}' class='doc-section'>";			    		
		   			foreach($layout[$i] as $seq => $arr){//$itemId => $data){
		   				foreach($arr as $type => $res){
		   					$fieldId   = intVal(key($res));
		   					$fieldName = isset($fieldList[$fieldId]) ? $fieldList[$fieldId]['title'] : null;
		   					if('plate' == $type){
			   					$label       = $plateList[$fieldId]['title'];
		   						$displayData = $plateList[$fieldId]['content'];	   						
		   					}elseif($fieldName and isset($rowData[$fieldName])){
		   						$label       = $fieldList[$fieldId]['label'];
		   						$displayData = $this->getItemDetail($rowData, $fieldName);
		   					}
		   					if($displayData){	
		   						$str .= "<h3>$label</h3><span id='field-$fieldName' class='doc-field'>" . $displayData . '</span>';
		   					}
		   					  
		   				}
		   			}
			   		
			   		$str .= '</div>';
			   		 
			   		$document[$i] = $str;
				}		
			}	
			return $document;			
		}
		
		public function getItemDetail($rowData, $fieldName){
			if('_id' == substr($fieldName,-3)){
				$id    = $rowData[$fieldName];
				$model =  Priam_Util::formatTableName($fieldName);
				$query = Doctrine_Query::create()
							->from("$model t INDEXBY t.id")
							->select('t.title,t.id')
							->where('t.id =?', $id)
							->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
				$res    = $query->execute();
				$data   = ucwords(strtolower($res[$id]['title']));
			}elseif('_at' == substr($fieldName,-3)){
				$data = new Zend_Date($data, Priam_Constant::DB_DATE_FOPRMAT, Zend_Registry::get('Zend_Locale'));				
			}elseif('_by' == substr($fieldName,-3)){
				$id    = $rowData[$fieldName];
				$model =  Priam_Constant::MODEL_PREFIX . 'Staff';
				$query = Doctrine_Query::create()
							->from("$model t INDEXBY t.id")
							->select('t.nickname,t.id')
							->where('t.id =?', $id)
							->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
				$res    = $query->execute();
				$data   = ucwords(strtolower($res[$id]['nickname']));					 
			}else{
				$data = ucfirst($rowData[$fieldName]);
			}
			
			return $data;
		}

}

