<?php
	class Help_Form_KeywordSearch extends Rhema_Form_Abstract {
		
		public function init(){
			
			$util		= Rhema_Util::getInstance();  
			$helpTypes  = Help_Model_HelpType::listAllTypes();
			$modules    = Admin_Model_AdminModule::listAllModules();
	       		
			$this->setMethod('post');
			$this->addAttribs(array('id' => 'search-form', 'name' => 'search-form'));
			
			$fields     = Help_Model_HelpField::listSearchableFields();
			
			$this->addElement('select','searchField', array(
				'label' 		=> 'Field: ',
				'value'			=> 'title',
				'multiOptions'	=> $util->generateOptionArray($fields,'title','label'),
				'required'		=> true 
			));
			
			$this->addElement('select', 'oper', array(
				'label'			=> '',
				'multiOptions' 	=> $this->searchOperators,
				'value'			=> 'cn',
				'required'		=> true
			));
			

						
			$this->addElement('text','keywords', array(
				'label'         => 'Keywords: (comma delimited) ',
				'required'		=> true,
				'filters'    	=> array('StringTrim'),
             	'validators' 	=> array(
                			array('validator' => 'StringLength', 
                					'options' => array(3))
                			),
                'id'			=> 'by-keyword'
			));

 
			 
			
			$this->addElement('radio','join', array(
				'label'         => 'Join type',
				'multiOptions'	=> array('and' => 'and', 'or' => 'or'),
				'value'			=> 'and',
				'required'		=> true
			));
			
			$this->addElement('select','limit', array(
				'label'         => 'Record limit',
				'multiOptions'	=> array(1 => 1, 5 => 5, 10 => 10, 25 => 25, 50 => 50,100 => 100),
				'value'			=> 25,
				'order'			=> 20,
				'required'		=> true
			)); 
			
			$this->addElement('multiCheckbox','module_id', array(
				'label'         => 'Filter by Module',
				'multiOptions'	=> $util->generateOptionArray($modules,'module_dir','title', false),
				'value'			=> ''
			)); 

			$this->addElement('multiCheckbox','type_id', array(
				'label'         => 'Filter by help type',
				'multiOptions'	=> $util->generateOptionArray($helpTypes,'id','title', false),
				'value'			=> ''
			)); 
			
	        $this->addElement('button','submit', array(
	        	'label'			=> 'Search',
	        	'ignore'		=> true,
	        	'class'			=> 'curved-btn',
	        	'decorators'	=> $this->buttonDecorators,
	        	'order'			=> 100,
	        	'id'			=> 'front-search-button'	
	        ));
 
		}
	}