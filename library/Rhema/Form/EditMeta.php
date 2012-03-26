<?php
	class Rhema_Form_EditMeta extends Rhema_Form_Abstract{

		public function init(){

			parent::init();

	        $this->addElement('textarea', 'meta_title', array(
	            'label'      	=> 'Meta Title:',
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('metatitle'),
	            'title'			=> 'Enter your page meta title',
	            'required'	    => true,
	        	'value'	        => ''
	        ));

	        $this->addElement('textarea', 'keyword', array(
	            'label'      	=> 'Enter Meta Keyword:',
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('metakeyword'),
	            'title'	        => 'Enter meta keywords separated by comma'
	        ));

	        $this->addElement('textarea', 'description', array(
	            'label'      	=> 'Enter Meta Description:',
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('metadescription')
	        ));

	        $this->removeElement('csrf');
 			//$this->addElement( $this->getSubmitButton());
		}

	}