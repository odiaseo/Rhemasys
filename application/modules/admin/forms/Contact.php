<?php
	class Admin_Form_Contact extends Rhema_Form_Abstract {
		
		public function init(){
			
			$table 	= MODEL_PREFIX . 'Inquiry';
			
			$grid   = new Admin_Service_Grid();
			$model  = $grid->getColumnModel($table,'title');
			 
			 
			$this->setDescription('contact us please');
			
			$this->addElement('select', 'salutation', array( 
			));
			
			$this->addElement('text', 'firstname', array( 
			));
			
			$this->addElement('text', 'surname', array( 
			));
			
			$this->addElement('text', 'email', array( 
			));
			
			$this->addElement('select', 'subject', array( 
			));
			
			$this->addElement('textarea', 'message', array( 
			));
			
			$elems = $this->getElements();
			foreach($elems as $e){
				$label = $util->getLabel($e->getName());
				$e->setLabel($label);
			}
		}
		
	}