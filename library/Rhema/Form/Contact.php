<?php
	class Rhema_Form_Contact extends Rhema_Form_Abstract{

		public function init(){
			parent::init();
			$this->setMethod('post');
			$util  			= Rhema_Util::getInstance();
			$view			= Zend_Layout::getMvcInstance()->getView();
			$salut		    = Rhema_Model_Abstract::_getAllRecords('salutation');
			$subj           = Rhema_Model_Abstract::_getAllRecords('subject');
			$charLength     = Rhema_SiteConfig::getConfig('settings.feedback_minimum_character');
			
	        $this->addElement('select', 'title', array(
	            'label'      	=> 'Title:',
	            'required'   	=> true,
	            'filters'   	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('title'),
	            'multiOptions'  => Rhema_Util::generateOptionArray($salut, 'id', 'title', true) 
	        ));

	        $this->addElement('text', 'name', array(
	            'label'      => 'Name:',
	            'validators' => array(array('Alpha',false, array('allowWhiteSpace' => true))
	        						 ,array('StringLength', false, array(5))),
	            'required'   => true,
	            'filters'    => array('StringTrim', 'StripTags'),
	            'decorators' => $this->getElemDecor('name'),

	            'title'	=> 'Enter your full name'
	        ));

	        $this->addElement('text', 'email', array(
	            'label'      	=> 'Email:',
	            'required'   	=> true,
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('email'),
	            'title'	        => 'Please specify a contact email address',
	        	'validators' 	=> array('EmailAddress'),
	        ));

	        $this->addElement('text', 'telephone', array(
	            'label'      	=> 'Telephone:',
	            'required'   	=> false,
	            'filters'    	=> array('StringTrim'),
	            'decorators' 	=> $this->getElemDecor('telephone'),
	            'title'	        => 'Provide a contact number'
	        ));

	        $this->addElement('select', 'subject', array(
	            'label'      	=> 'Subject:',
	            'required'   	=> true,
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('subject'),
	        	'title'	        => 'Select a subject area',
	            'multiOptions'  => Rhema_Util::generateOptionArray($subj, 'title', 'title', true)
	        ));

	        $this->addElement('textarea', 'message', array(
	            'label'      	=> "Please enter your message in the space provided below (min {$charLength} characters)",
	            'required'   	=> true,
	            'validators' 	=> array(),
	            'rows'			=> 5 ,
	        	'title'			=> 'maximum allowrd',
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'validators'    => array(array('StringLength', false, array($charLength))),
	            'decorators' 	=> $this->getElemDecor('message')
	        ));
 
	        // Add recaptcha element using recaptcha service
	        $this->addElement( $this->getRecaptchaElement());
	        
	        // Add the submit button
 			$this->addElement( $this->getSubmitButton());

 			$this->removeElement('csrf');
	        
		}
	}
