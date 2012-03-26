<?php
	class Rhema_Form_User_ChangeLoginDetails extends Rhema_Form_Login_Abstract{
		
		public function init(){
	
			$this->addElement('password', 'old-password', array(
	            'label'      => 'Old Password:',
	            'required'   => true,
	            'filters'    => array('StringTrim'),
	            'decorators' => $this->getElemDecor('old-password'),
	        	'order'		 => 8
	        ));
	        			
			$this->addElement('password', 'confirm-password', array(
	            'label'      => 'Confirm Password:',
	            'required'   => true,
	            'filters'    => array('StringTrim'),
	            'decorators' => $this->getElemDecor('confirm-password'),
	        	'order'		 => 15
	        ));
		}
	}
