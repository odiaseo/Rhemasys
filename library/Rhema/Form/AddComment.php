<?php
	class Rhema_Form_AddComment extends Rhema_Form_Abstract{

		public function init(){
			//$this->_addFormDecorator = false;
			parent::init();

	        $this->addElement('hidden', 'id', array(
	        	'style'			=> 'width:220px;',
	            'filters'       => array('StringTrim', 'StripTags'),
	            'decorators'    => $this->getElemDecor('id'),
	            'value'	        => ''
	        ));

	        $this->addElement('text', 'name', array(
	            'label'      	=> 'Name:',
	            'validators' 	=> array(array('Alpha',false, array('allowWhiteSpace' => true))
	        						 ,array('StringLength', false, array(5))),
	            'required'   	=> true,
	        	'style'			=> 'width:220px;',
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('name'),
	            'title'			=> 'Enter your full name',
	        	'value'	        => ''
	        ));

	        $this->addElement('text', 'email', array(
	            'label'      	=> 'Email:',
	            'required'   	=> true,
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('email'),
	            'style'			=> 'width:220px;',
	        	'value'	        => '',
	            'title'	        => 'Please specify a contact email address',
	        	'validators' 	=> array('EmailAddress'),
	        ));

	        $this->addElement('textarea', 'comment', array(
	            'label'      	=> 'Your Comment:',
	            'required'   	=> true,
	        	'value'	        => '',
	        	'style'			=> 'width:220px; height:80px;',
	            'filters'    	=> array('StringTrim', 'StripTags'),
	            'decorators' 	=> $this->getElemDecor('comment')
	        ));

 			$this->addElement( $this->getSubmitButton());
		}

	}