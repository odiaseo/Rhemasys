<?php
	class Rhema_Form_User_ChangePersonalDetails extends Rhema_Form_Abstract{
		public function init(){
			parent::init();
			$util  			= Rhema_Util::getInstance();
			
			$this->addElement('select', 'salutation_id', array(
				'label'			=> 'Title',
			    'value'			=> '1',
				'multiOptions'	=> $util->getEditOptions('Admin_Model_Salutation', 'salutation_id', 'id', 'title', true),
				'decorators' 	=> $this->getElemDecor('salutation_id'),
			));	 
			
			$this->addElement('text', 'firstname', array(
				'label'			=> 'First Name',
	            'required'   	=> true,
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('firstname'),
	            'description'	=> ''
			));

			$this->addElement('text', 'lastname', array(
				'label'			=> 'Surname',
	            'required'   	=> true,
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('lastname'),
	            'description'	=> ''
			));
			
			$this->addElement('text', 'nickname', array(
				'label'			=> 'Nickname',
	            'required'   	=> true,
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('nickname'),
	            'description'	=> ''
			));	
						
			$this->addElement('text', 'email', array(
				'label'			=> 'Email',
	            'required'   	=> true,
			    'validators'	=> array('EmailAddress'),
	            'filters'    	=> array('StringTrim', 'StripTags', 'StringToLower'),
	            'decorators' 	=> $this->getElemDecor('email'),
	            'description'	=> ''
			));	
			
			$this->addElement('text', 'telephone', array(
				'label'			=> 'Telephone',
	            'required'   	=> true, 
				'validators'	=> array('Digits'),
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('telephone'),
	            'description'	=> ''
			));
			
			$this->addElement('text', 'mobile', array(
				'label'			=> 'Mobile',
	            'required'   	=> true, 
				'validators'	=> array('Digits'),
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('mobile'),
	            'description'	=> ''
			));
			
			$this->addElement('text', 'fax', array(
				'label'			=> 'Fax',
	            'required'   	=> true, 
				'validators'	=> array('Digits'),
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('fax'),
	            'description'	=> ''
			));
			
			$this->addElement('text', 'company', array(
				'label'			=> 'Company',
	            'required'   	=> true,
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('company'),
	            'description'	=> ''
			));	
			
			$this->addElement('text', 'website', array(
				'label'			=> 'Nickname',
	            'required'   	=> true,
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('website'),
	            'description'	=> ''
			));	
		}
	}