<?php
	class Admin_Form_Setting extends Rhema_Form_Abstract {
		
		public function init(){
			parent::init();
			$settings = Admin_Model_AdminSetting::getSettings();
			 
			
			foreach($settings as $elem){
				$name  = "setting[$elem[id]]";
				$type  = $elem['field_type'];
				
				$this->addElement($type, $name, array(
					'label'		=> $elem['title'],	
					'value'		=> $elem['param']			
				));
				
				if($type == 'select'){
					$sel       = $this->getElement($name);
					$tableName = $elem['AdminTable']['name'];
					$multi     = array();
					if($tableName){
						$multi     = Rhema_Model_Abstract::getEditOptions('Admin_Model_AdminSetting',$tableName,'id', 'title', true);
					}
					$sel->setMultiOptions($multi);
				}
			}
			$this->addElement('reset', 'reset', array(
				'value'		=> 'Reset',
				'ignore'	=> true
			));
						
			$this->addElement('submit', 'update', array(
				'value'		=> 'Update',
				'ignore'	=> true
			));
		}
		
		 
	}