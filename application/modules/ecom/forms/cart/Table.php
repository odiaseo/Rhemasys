<?php
	class Ecom_Form_Cart_Table extends Rhema_Form_Abstract{

		public function init(){
			$this->_addFormDecorator = false;
			parent::init();

			$this->setDecorators(array(
				array(
					'ViewScript',
					array('viewScript'	=> 'Cart/_cart.phtml')
				),
				'Form'
			));
			$this->setMethod('post');
			$this->setAction('');

			$this->addElement('submit', 'update-cart', array(
				'decorators'	=> array('ViewHelper'),
				'label' 		=> 'Update'
			));

			$this->addElement('hidden', 'returnto', array(
				'decorators'  => array(
					'ViewHelper'
				),
			));
		}
	}