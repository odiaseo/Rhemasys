<?php
class Help_DesignController extends Zend_Controller_Action 
{

    public function init(){
     
        /* Initialize action controller here */
		parent::init(); 
    	$this->_gridParam['editWidth']        = 870;  
    }

    public function indexAction(){
    	$request      						= $this->getRequest();  
    	$this->view->type_id			    = $request->getParam('type_id',1);
    	$this->_table 						= $request->getParam('table',HELP_PREFIX . 'HelpField'); 
    	$eParm							    = $this->_urlParams;
    	$eParm['action']					= 'save';
    	$eParm['table']				        = $this->_table;
    	$this->_gridParam['editurl']  		= $this->_utility->assemble($eParm,ADMIN_ROUTE); 
		$this->_helper->displayGrid(); 
    }
    
    public function templateAction() {
    	$request      						= $this->getRequest(); 
    	$this->_table 						=  HELP_PREFIX . 'HelpTemplate';
    	   	
    	$urlParm							= $this->_urlParams;
    	$urlParm['table']				    = $this->_table; 
    	
    	
    	$this->_gridParam['url']  			= $this->_utility->assemble($urlParm,ADMIN_ROUTE); 
    	$urlParm['action']					= 'save';
    	
    	$this->_gridParam['editurl']  		= $this->_utility->assemble($urlParm,ADMIN_ROUTE); 
    	
    	$urlParm		= array();
    	$urlParm['action']					= 'layout';
    	$urlParm['controller']				= 'design';
    	$urlParm['module']					= 'help';  
    	
 		$this->_helper->displayGrid();
 		
    }

    public function updateAction(){
    	$model      = HELP_PREFIX . 'HelpTemplate';
    	$request	= $this->getRequest();
    	$posted     = $request->getPost();
    	$oper       = $request->getParam('oper', '');
    	$table      = $request->getParam('table',$model);
 
    	//$formModel  = new Rhema_Form_Auto_ModelForm($table);
    	
    	$temp_id	= $request->getParam('template_id');	 
		$plates	    = $request->getParam('boiler_plate_id', array());
		
    	switch($oper){
    		case 'addFields':{
    			$fields   = $request->getParam('field_id', array());
    			$fields   = array_filter($fields);
    			$sequence = $request->getParam('sequence', 0);
    			$temp_id  = $request->getParam('template_id', null);
    			$readonly = $request->getParam('is-readonly', 0);
    			
    			if($temp_id){
    				$tTable = HELP_PREFIX . 'HelpTemplateField';
    				$row    = Help_Model_HelpTemplateField::getAllTemplateFields($temp_id, false); 
    			//========================= Process Template Fields	
    				$toAdd = array_flip($fields);
    				$count = 0;
    				foreach($row as $existField){    					
    					$fId = $existField['field_id'];
    					if(isset($toAdd[$fId])){
    						$existField['deleted_at'] = null;    						
    					}else{
    						$existField['deleted_at'] = date(DB_DATE_FORMAT, time());
    					}
    					unset($toAdd[$fId]);
    					$count++;
    				} 
    				$row->save();
    				
    				if(count($toAdd)){  
    					$toAdd = array_keys($toAdd);			 	    					    			 	  
	    			 	for($i=0; $i<count($toAdd); $i++){
	    			 		$obj = new $tTable();
	    			 		$obj->field_id 	  = $toAdd[$i];
	    			 		$obj->sequence 	  = $sequence;
	    			 		$obj->is_readonly = $readonly;
	    			 		$obj->template_id = $temp_id;
	    			 		$obj->save();
	    			 	}	    			 		    				
    				}  

    		//=========================== Process Boiler Plates=========================
    				$toAdd  = array_flip($plates);
      				$bTable = HELP_PREFIX . 'HelpTemplateBoilerPlate';
      				$bRow   = Help_Model_HelpTemplateBoilerPlate::getAllPlates($temp_id, false);
      				
    				foreach($bRow as $existPlate){    					
    					$fId = $existPlate['boiler_plate_id'];
    					if(isset($toAdd[$fId])){
    						$existPlate['deleted_at'] = null;    						
    					}else{
    						$existPlate['deleted_at'] = date(DB_DATE_FORMAT, time());
    					}
    					unset($toAdd[$fId]);
    					$count++;
    				} 
    				$bRow->save();
    				
    				if(count($toAdd)){  
    					$toAdd = array_keys($toAdd);
    					$bObj  = new $bTable();			 	    					    			 	  
	    			 	for($i=0; $i<count($toAdd); $i++){
	    			 		$ind = $count + $i;
	    			 		$bObj->boiler_plate_id = $toAdd[$i]; 
	    			 		$bObj->template_id     = $temp_id;
	    			 		$bObj->save();
	    			 	}	    			 		    				
    				}
  				
    			}
    			break;
    		}
    		case 'edit':
    		case 'add':{
    			$id    = $request->getParam('id', null); 
    			if($oper == 'add'){
    				$row  = new $table();
    			}elseif($id){
    				$row  = Doctrine_Core::getTable($table)->find($id); 
    			}
    			 
    			if($row){
    				foreach($row as $col => $data){
    					$posted = $request->getParam($col, null);		    					
    					if($posted != null and 'id' != $col){
    						$row->$col = $posted;
    					}
    				}
    				$row->save();
    			}
    		}		    	 
    	}
    	
    	$output['message'] = 'Records updated successfully';
    	$output['pass']	   = 1;
    	
    	$this->_utility->setAjaxData(Zend_Json::encode($output)); 
    }
    
	 
	
	public function processField($sxnId, $arr, &$return){
		$parts = explode(',', strtolower($arr));
		for($i=0; $i<count($parts); $i++){
			$seq    = $i + 1;
			if('sxn' == substr($parts[$i],0,3)) {
				continue;
			}			
			$list = str_replace('li_', '',$parts[$i]);			
			list($type, $fieldId)   = explode('-',$list);				 
			$return[$sxnId][$seq][$type][$fieldId] = 1;			 
		} 
	}
	
	public function resetLayout($template_id){
		$model 			= HELP_PREFIX . 'HelpTemplate';
		$row   			= Doctrine_Core::getTable($model)->find($template_id);
		$row->layout 	= '';
		$row->save();			
	}
	
	public function saveLayout(){
		$request	 = $this->getRequest();
		$sections    = $request->getParam('section', array());
		$template_id = $request->getParam('template_id');
		$sections    = array_filter($sections);		
		return Help_Model_HelpTemplateLayout::saveLayout($sections, $template_id);
	}
	
	public function layoutAction(){
		$request    	= $this->getRequest();
		$template_id    = $request->getParam('template_id', null);
		$task           = $request->getParam('task', null);
		$fields		    = array();
		$plates         = array();
		$sectionData    = array();
		
		if($template_id){			
			if('savelayout' == $task){
				$this->saveLayout();
			}elseif('reset' == $task){
				$numReset = Help_Model_HelpTemplateLayout::resetLayout($template_id);				
			}
				
			$currentLayout = Help_Model_HelpTemplateLayout::getLayout($template_id);	
			 
				 
			$fields = Help_Model_HelpTemplateField::getFields($template_id); 
			$plates = Help_Model_HelpTemplateBoilerPlate::getBoilerPlates($template_id); 			
		}
		$sections   = Help_Model_HelpSection::getSections();
		$mandatory                      = Help_Model_HelpField::getMandatoryColumns('result');
		//$mandatory						= $res['result'];
		$this->view->mandatoryFields    = $mandatory;
		$this->view->fields 			= $fields;
		$this->view->boilerPlates       = $plates; 
		
		foreach($sections as $sxn){
			$id               = $sxn['id'];
			$sectionData[$id] = Rhema_Util::buildLayoutSection($id,$currentLayout,$mandatory,$fields,$plates); 							
		}
		$this->view->sections           = $sections;
		$this->view->sectionData		= $sectionData;
		$this->view->templateId         = $template_id;
		$this->view->templateTitle      = $request->getParam('title');
		
		$urlParm						= array();
    	$urlParm['action']				= 'layout';
    	$urlParm['controller']			= 'design';
    	$urlParm['module']				= 'help';
    	$urlParm['task']				= 'savelayout';
    	$urlParm['template_id']		    = $template_id;
    	    	
		$this->view->formAction         = $this->_utility->assemble($urlParm,ADMIN_ROUTE);  
		
		$output['form']   				= $this->_utility->filterText($this->view->render('design/layout.phtml'));
		$output['man']					= $mandatory;
		$this->_utility->setAjaxData(Zend_Json::encode($output));
	}
	
	public function assignAction(){ 
		$table      = HELP_PREFIX . 'HelpTemplate';
		$request	= $this->getRequest();
		
		$id 		= $request->getParam('id');
		$title	    = $request->getParam('title');
		$options    = array(); // selected template fields
		$boiler     = array(); // boiler plates
		
		if($id){ 			
			$oFields = Help_Model_HelpTemplateField::getFields($id);				
			foreach($oFields as $ind => $desc){
				$options[] = $desc['Field']['id'];
			} 
			
			$oPlates = Help_Model_HelpTemplateBoilerPlate::getBoilerPlates($id);
			foreach($oPlates as $ind => $desc){
				$boiler[] = $desc['BoilerPlate'] ['id'];
			}
			$this->view->templateId 	= $id;
		}
		
		//============ save to registry to be used in form class===================
		$this->view->selectedFields = $options;  
		$this->view->usedPlates 	= $boiler; 
		$this->view->table          = $table;
		$this->view->title			= $title;
		
		$urlParm					= array();
    	$urlParm['action']			= 'update';
    	$urlParm['controller']		= 'design';
    	$urlParm['module']			= 'help';
    	$urlParm['table']			= $table; 
    	
		$this->view->formAction     = $this->_utility->assemble($urlParm, ADMIN_ROUTE);
		$this->view->divLabelSpan   = array(
								        'ViewHelper',
								        'Errors',
								        array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'elm-data')),
								        array('Label', array('tag' => 'dt', 'class' => 'elm-label') 
								    ));
 

		$output['boiler'] 		= $boiler;
		$output['form']   		= $this->_utility->filterText($this->view->render('design/assign.phtml'));
		$output['mandatory']    = Help_Model_HelpField::getMandatoryColumns('id'); 
		
		$this->_utility->setAjaxData(Zend_Json::encode($output));
	}
	
 
	public function listAction(){
  		$this->_helper->displayGrid(); 		
	}
	
	 
}
