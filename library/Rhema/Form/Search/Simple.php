<?php
	class Rhema_Form_Search_Simple extends Rhema_Form_Abstract {

		public function init(){
			$this->_addFormDecorator = false;
			parent::init();
			$action	= $this->getView()->url(array('slug' => 'search'), 'site-default-route');
			$this->setAction($action);
			$this->addElement('hidden', 'searchType', array(
				'value'		    => 'keywordSearch',
				'decorators'	=> array('ViewHelper',),
			    'filters'    	=> array('StringTrim', 'StripTags'),
			));
			$this->addElement('text','keyword', array( 
			    'label'		    => 'Keyword Search',
				'value'			=> '',
			    'title'			=> 'Seperate keywords with spaces',
	            'filters'    	=> array('StringTrim', 'StripTags'), 
	            'decorators' 	=> $this->getElemDecor('keyword'),
			    'onclick'		=> 'this.value="";',
			    'required'	    => true			
			));
 
			$button  = $this->getSubmitButton('Search');
			$this->addElement($button);
			$this->removeElement('csrf');
 
		}
	}