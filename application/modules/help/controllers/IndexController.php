<?php
class Help_IndexController extends Zend_Controller_Action 
{

    public function init()
    {
        /* Initialize action controller here */
        	parent::init(); 
			$this->router =   Zend_Controller_Front::getInstance()->getRouter();
    }

    public function indexAction() {
    }

     public function getdataAction(){ 
     	
    	$request      					= $this->getRequest();
    	$this->_table                   = $request->getParam('table',HELP_PREFIX . 'HelpDocument');
    	$type_id						= $request->getParam('type_id');
    	$allTypes						= $this->getCached()->getTypes(); 
    	$this->_gridParam['caption']    = $allTypes[$type_id]['title'] ; 
    	
    	$par = array('module'=>'help','controller'=>'index','action'=>'getdata','table' => $this->_table,'type_id',$type_id);
    	
    	$this->_gridParam['url']  		= $this->_utility->assemble($par, ADMIN_ROUTE); 
    	
    	$par['action'] 					= 'save';
    	$par['controller']				= 'search';
    	$this->_gridParam['editUrl']  	= $this->_utility->assemble($par, ADMIN_ROUTE); 
    	
    	$oGrid      = $this->getCached()->getGrid($this->_table, $this->_gridParam, $type_id);     	 
     	$jsonString = $oGrid->getdata();   
    	Rhema_Util::setAjaxData($jsonString); 
     }
        
    public function finderAction(){
    	$model  = HELP_PREFIX . 'HelpType';
    	$query  = Doctrine_Query::create()
    			->select('t.id,t.title')
    			->from("$model t")
    			->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
    	$types   = $query->execute();
    	
    	$this->view->doctype = $types;	    	
    	$html    = $this->view->layout()->render('autocomplete'); 
    	Rhema_Util::setAjaxData(Zend_Json::encode($html));
    }
    
    public function suggestAction(){
    	$request = $this->getRequest();
    	$q       = $request->getParam('q');
    	$type    = $request->getParam('type');
    	$limit   = $request->getParam('limit', 20);
    	$res     = array();
    	
 
		$string   = array();
		$parms    = array();
		$docModel = HELP_PREFIX  . 'HelpDocument';
		$fields   = $this->getCached()->getSearchFields();
		
		$query    = Doctrine_Query::create()
						->select('t.id,t.title')
						->from("$docModel t")
						->where('t.type_id =?', $type)
						->limit($limit)
						->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY); 
						
		$operator      = $this->_utility->getOperator('cn', $q);
		
		foreach($fields as $name => $val){	    			
			$string[]      = "t.$name $operator ? ";
			$parms[]       = $q;
		}
		
		$queryString = join(" OR ", $string);	    		
		$query->andWhere($queryString, $parms);	    		  		
		$output      	= $query->execute();
		$par 			= array('module'=>'help','controller'=>'index','action'=>'display','type' => $type);
		foreach($output as $doc){
			$par['id']   = $doc['id'];
			$href        = $this->router->assemble($par, ADMIN_ROUTE);
			$res[$docId] = "<a href='#' title='$doc[title]' rel='$href' class='rel-tips'>$doc[title]</a>";
								
		}
    	 

    	
    	foreach ($res as $key => $value) {
			echo "$key|$value\n";
		}
		die(); 
    }
    
    public function getSearchFields(){
    	return Help_Model_Field::listSearchableFields();
    }
    
     public function displayAction(){
    	$itemid        = $this->_getParam('id', null);
    	$documentType  = $this->_getParam('type', null);
    	$encode        = $this->_getParam('encode', 1); ;	
    	
    	if($documentType and $itemid){
    		$items         = $this->getCached()->getDocFieldPlates();    		
    		$docTypeList   =  $items['types'];  
    		$fieldList     =  $items['field'];   	
    		$plateList     =  $items['plate']; 	    	
	    	$templateId    			= isset($docTypeList[$documentType]) ? $docTypeList[$documentType]['template_id'] : null; 
	    	$layout        			= $this->getCached()->getLayout($templateId);  
    		   
    		$this->view->section 	= $this->getCached()->buildDocument($layout , $itemid, $fieldList, $plateList);  
    		$output     			= $this->_utility->filterText($this->view->render('index/document.phtml'));	
    		    	  	
	    }else{
	    	$output     = 'Document not found';
	    }
    	
	    if($encode){
	    	$output = Zend_Json::encode($output);
	    }
	    $this->_utility->setAjaxData($output);
    }
 		
    public function buildDocument($layout, $itemid, $fieldList, $plateList){
    	$table   = HELP_PREFIX . 'HelpDocument';
		$rowData = Doctrine_Core::getTable($table)->find($itemid, Doctrine_Core::HYDRATE_ARRAY);
		$document = array();
		foreach($layout as $sxnId => $sData){ 
			$str  = "<div id='sxn-{$sxnId}' class='doc-section'>";			    		
			foreach($sData as $seq => $arr){ 
				foreach($arr as $type => $res){
					foreach($res as $fieldId => $itemSeq){							 
						$fieldName = isset($fieldList[$fieldId]) ? $fieldList[$fieldId]['title'] : null;
						if($fieldName){
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
					  
				}
			}	   		
	   		$str .= '</div>';		   		 
		   	$document[$sxnId] = $str;			 		
		}	
		return $document;			
	}
		
		
	public function getItemDetail($rowData, $fieldName){
		if('_id' == substr($fieldName,-3)){
			$id    = $rowData[$fieldName];
			$model =  $this->_utility->formatTableName($fieldName);
			$query = Doctrine_Query::create()
						->from("$model t INDEXBY t.id")
						->select('t.title,t.id')
						->where('t.id =?', $id)
						->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
			$res    = $query->execute();
			$data   = ucwords(strtolower($res[$id]['title']));
		}elseif('_at' == substr($fieldName,-3)){
			$data = new Zend_Date($data, DB_DATE_FORMAT, Zend_Registry::get('Zend_Locale'));				
		}elseif('_by' == substr($fieldName,-3)){
			$id    = $rowData[$fieldName];
			$model =  MODEL_PREFIX . 'User';
			$query = Doctrine_Query::create()
						->from("$model t INDEXBY t.id")
						->select('t.firstname,t.lastname')
						->where('t.id =?', $id)
						->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
			$res    = $query->execute();
			$data   = ucwords(strtolower($res[$id]['firstname'] . ' ' . $res[$id]['lastname']));					 
		}else{
			$data = ucfirst($rowData[$fieldName]);
		}
		
		return $data;
	}
	
	public function getDocFieldPlates(){
		$docTypeList = Help_Model_HelpType::listAllTypes(); 
		$fieldList   = Help_Model_HelpField::listAllFields();  
		$plateList   = Help_Model_HelpBoilerPlate::getBoilerPlates(); 
		
		$return['types'] = $docTypeList;
		$return['field'] = $fieldList;
		$return['plate'] = $plateList;
		
		return $return;
	}
	
	public function getLayout($template_id){
		$currentLayout = array();
		if($template_id){
			$currentLayout     =  Help_Model_TemplateLayout::getLayout($template_id); 
		}
		return $currentLayout;
	}
}
   