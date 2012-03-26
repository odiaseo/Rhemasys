<?php
	class Admin_Form_Menu extends Rhema_Form_Abstract   {
		
		public function init(){
			
			$this->setAction(Zend_Registry::get('baseUrl') . '/cms/menu/index');			
			$this->setMethod('post');
			$this->addAttribs(array('id' => 'menu-form', 'name' => 'menu-form')); 
	
	  		$this->addElement('Radio', 'root_id', array(
							'label'			=> 'Select menu root',	
							'required'		=> true,
							'decorators'	=> $this->getDecor('menu-position')			
			));
			 	  		
	  		$this->addElement('Radio', 'rootType', array(
							'label'			=> 'Select menu type',	
							'required'		=> true,
							'multiOptions'	=> array('admin' => 'Admin',
													 'site'	 => 'Site'),
							'decorators'	=>  $this->getDecor('menu-type') 		
			));
 		 
	
	  		$this->addElement('Multiselect', 'children', array(
							'label'			=> 'Select sub menus',	
							'required'		=> true,
							'class'			=> 'multiselect',
							'style'			=> 'width:646px;',
							'size'			=> 15,
							'decorators'	=> $this->getDecor('menu-child') 
													
			));	
	 		  	
			$this->addElement('hidden','baseUrl',array(
		  					'id'			=> 'baseUrl',
		  					'value'			=> Zend_Registry::get('baseUrl'),
		  					'decorators' 	=> $this->getDecor('blank')
		  	));
		  	
 
		}
	}