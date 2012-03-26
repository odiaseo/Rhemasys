<?php
	class Help_SearchController extends  Zend_Controller_Action {
		
		protected $_gridParam;
		protected $_gridId ;
		protected $_gridPager;
		protected $_fields;
		protected $_table ;
		
		public function init(){
			parent::init();
			$this->_gridId 					= 'list';
			$this->_gridPager 				= 'doc_pager'; 
			$this->_table       			= HELP_PREFIX  . 'HelpDocument';
			$this->_gridParam['width']	    = 870;	
			$this->_gridParam['editWidth']	= 870;
 			$this->router =   Zend_Controller_Front::getInstance()->getRouter();
	    	/*$jsFiles[]    = '/cluetip/jquery.cluetip.js';
	    	$jsFiles[]    = '/autocomplete/jquery.autocomplete.js';
    		$cssFile[]    = SCRIPT_PATH . '/cluetip/jquery.cluetip.css';
    		$cssFile[]    = SCRIPT_PATH . '/autocomplete/jquery.autocomplete.css';
    		
    	
    		$this->_Utility->setHeaderFiles($jsFiles);
    		$this->_Utility->setHeaderFiles($cssFile,'uniqueCss'); 	
    		*/
		}
	 
	    public function typeAction(){  
	    	$request      					= $this->getRequest();
	    	$type_id						= $request->getParam('type_id');
	    	$allTypes						= $this->getCached()->getTypes(); 
	    	$this->_gridParam['caption']    = $allTypes[$type_id]['title'] ; 
	    	
	    	$urlParm = array('module'=>'help','controller'=>'index','action'=>'getdata',
					'type_id'=>$type_id,'table'=>$this->_table);
	    	
	    	$this->_gridParam['url']  		= $this->_utility->assemble($urlParm,ADMIN_ROUTE);
 
	    	
	    	$param['controller']			= 'search';
	    	$param['action']				= 'save';
	    	$this->_gridParam['editUrl']  	= $this->_utility->assemble($urlParm,ADMIN_ROUTE);
	    	
	    	$this->_helper->displayGrid(); 
	    }	
 
		
		public function indexAction(){				
			$allTypes						= $this->getCached()->getTypes(); 		
			$this->view->allTypes			= $allTypes;  
			$this->_helper->displayGrid();
		}
	

		
	    public function typeSearch(){
	    	$request                    = $this->getRequest();
	    	$document_type_id           = $request->getparam('type', null);
	    	$docs   					= Zend_Registry::get('docById');
   	
	    	if(!$document_type_id and count($docs)){
	    		$fistDoc 				= current($docs);
	    		$document_type          = strtolower($fistDoc['title']);
	    		$document_type_id       = $fistDoc['id'];
	    	}
	    	
	    	if($document_type_id and isset($docs[$document_type_id])){
 				$caption 	= ucwords($docs[$document_type_id]['title']); 
		    	$formName	= $this->_table . '-form';	
		    	$formClass	= $this->_table ;	 
		    	
		    	 	
		    		    	    	
		    	$layout		= Zend_Layout::getMvcInstance();
		    		    		    		    	 	
				$this->_gridParam['caption'] 	= $caption;		
				$this->_gridParam['height'] 	= 800;	
				$this->_gridParam['width'] 	    = 900;	    	
		    	$this->view->modelName   		= $this->_table;
		    	
		    	$baseUrl						= Zend_Controller_Front::getBaseUrl();	
		    	
				$param = array('module'=>'admin','controller'=>'grid','action'=>'index',
					'type_id'=>$type_id,'table'=>$this->_table);
	    	
	    		$this->_gridParam['url']  		= $this->_utility->assemble($param ,ADMIN_ROUTE);			
		    	//$this->_gridParam['url']		= $baseUrl . '/kb/document/grid/type_id/' . $document_type_id; 
		    	
		    	$this->_Utility->generateGridScript($this->_table, $document_type_id, $this->_gridParam);	
 
	    	}
	    	//$this->view->headScript()->appendFile( '/scripts/autocomplete/jquery.autocomplete.js');
	    	//$this->view->headLink()->appendStylesheet( '/scripts/autocomplete/jquery.autocomplete.css');	    	
	    }
	    

	    public function keywordAction(){
	    	$this->view->searchForm 	= $this->getCached()->getSearchForm();
	    }
	    
	    public function getSearchForm(){ 
	      	$searchForm     = new Help_Form_KeywordSearch();
			$param 			= array('module'=>'help','controller'=>'search','action'=>'result');	    	
	    	$rmAction 		= $this->_utility->assemble($param,ADMIN_ROUTE);
	    	
    		$searchForm->setAction($rmAction); 
    		return $searchForm;    		      	
	    }

	    public function resultAction(){
	    	$request    = $this->getRequest();
	    	$paging     = $request->getParam('paging', null); 	    	
	   	    if($paging){
	   	    	$temp 			= Priam_Util::getSessData('searchParams');
	   	    	$temp['page'] 	= $paging;	   	    	   	
	   	    	Zend_Registry::set('encode','html');
	   	    	$request->setParams($temp );	   	    	  
	   	    }else{
	   	    	$this->_utility->setSessData('searchParams', $request->getParams());
	   	    }
	   	    
		    $post		    = $request->getParams();
	    	$keywords      	= $request->getParam('keywords');
		    $page			= $request->getParam('page', 1);
		    $field          = $post['searchField'];  
					
	    	if($keywords){
	    		if('_at' == substr($field,-3)){
		    		list($y,$m,$d)  = explode('-', $keywords);
		    		$keywords       = date(DB_DATE_FORMAT ,mktime(0,0,0,$m,$d,$y));
		    	}
	    		
	    		$table    = HELP_PREFIX . 'HelpDocument'; 
	    		$keys	  = array_filter(explode(',', $post['keywords'])); 
	    		
	    		$query    = Doctrine_Query::create()
	    						->select('t.title, t.description, t.type_id, t.question')
	    						->from("$table t"); 
	    						
	    		if('*' != $keywords){
		    		foreach($keys as $val){
		    			$operator      = $this->_utility->getOperator($post['oper'], $val);
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
	    		
	    		if(isset($post['module_id'])){
					$query->whereIn('t.module', $post['module_id']);
	    		}
	    		
				$query->orderBy('t.type_id ASC, t.updated_at DESC');				
				$query->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY); 

				$urlParam 	 = array('module'=>'help','controller'=>'search','action'=>'result','paging'=>'{%page_number}');	    	
	    		 
				$pagerLayout = new Rhema_Model_PagerLayout(
				      new Doctrine_Pager($query, $page, $post['limit']), 
				      new Doctrine_Pager_Range_Sliding(array('chunk' => 10)),			            				      
				      $this->_utility->assemble($urlParam, ADMIN_ROUTE)
				);
				$pagerLayout->setTemplate('<a href="#" rel="{%url}" id="page-{%page}" class="page-number ui-state-default">{%page}</a>');
				$pagerLayout->setSelectedTemplate('<a href="#" rel="{%url}" id="page-{%page}" class="page-number ui-state-active">{%page}</a>');
				
				$pager		= $pagerLayout->getPager();
				$result 	= $pager->execute(array(), Doctrine_Core::HYDRATE_ARRAY); 
				
				$byType	= array();
				foreach($result as $index => $data){
					$type             = $data['type_id'];
					$byType[$type][]  = $data;
				}
				 
				$this->view->searchResults  = $byType;	
				$this->view->allTypes		= $this->getCached()->getTypes(); 
				
				//$this->view->itemUrl 		= $this->baseUrl . '/help/index/display/id/';	
				$rendered				    = $this->_utility->filterText($this->view->render('search/result.phtml'));
				
				if($paging){
					$output = $rendered;
				}else{
					$output['main']    			=  $rendered;	 
					$output['pager']  		    = $pagerLayout->display();
				}
	    	}else{
	    		$output['main']  = 'No keyword entered';
	    		$output['pager'] = '';
	    	} 
	    	
		    $this->_utility->setAjaxData(Zend_Json::encode($output));	    	
	    }
	    
	    public function getdataAction(){
	    	$request     = $this->getRequest();
	    	$gridAction  = $request->getParam('gridAction');
	    	$recordId    = $request->getParam('rowid');
	    	$recordTable = $request->getParam('rowTable');
	    	
	    	switch($gridAction){	    		
	    		case 'byId' :{
			    	$query = Doctrine_Query::create()
			    				->from("$recordTable t")
			    				->where('t.id =?', $recordId);
			    	$result = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			    	if(count($result)){
			    		$result[] = $request->getParam('formId');
			    	}
			    	$this->_utility->setAjaxData($result);	    			
	    			break;
	    		}
	    		default:
	    	}
	    	 
	    }
	    
	    public function saveAction(){
	    	$request	 = $this->getRequest();	    	
	    	$oper        = $request->getParam('oper', 'add');
	    	$posted      = $request->getPost();	    	 	
		    $docid		 = $request->getPost('id', null);		    	 
		    $table       = Doctrine_Core::getTable($this->_table);
		   try{    	    	
			    if($oper == 'add' or !$docid){
			    	$row = new $this->_table();
			    	$row->state(Doctrine_Record::STATE_TDIRTY);
			    }else{
			    	$row =  $table->find($docid);
			    }
			    
			    if($row){
			    	$allCols       = $table->getColumns(); 
			    	
			    	foreach($posted as $col => $data){
			    		if($col != 'id' and isset($allCols[$col])){
			    			$row->$col 	= $data;
			    		}
			    	}
			    	
			    	$row->save();
			    }
		   } catch(Doctrine_Validator_Exception $e){
		   		$error = $row->getErrorStackAsString();
		   }

	    } 

	    
	}