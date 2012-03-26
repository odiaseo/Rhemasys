<?php
	class Ecom_Form_Cart_Download extends Rhema_Form_Abstract{

		public function init(){
			$this->_addFormDecorator = false;
			parent::init();

			$this->setDecorators(array(
				array(
					'ViewScript',
					array('viewScript'	=> 'Cart/_download-cart.phtml')
				),
				'Form'
			));
			$this->setMethod('post');
			$this->setAction('');

			$this->addElement('submit', 'update-cart', array(
				'decorators'	=> array('ViewHelper'),
				'label' 		=> 'Update',
			    'class'	        => 'curved-btn blue'
			));

			$this->addElement('hidden', 'returnto', array(
				'decorators'  => array(
					'ViewHelper'
				)
			));
		}
	}