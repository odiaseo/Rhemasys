<?php
	class Ecom_Form_Cart_Confirm extends Rhema_Form_Abstract{

		public function init(){
			$this->_addFormDecorator = false;
			parent::init();

			$this->setDecorators(array(
				array(
					'ViewScript',
					array('viewScript'	=> 'Cart/_confirm-download.phtml')
				),
				'Form'
			));
			$this->setMethod('post');
			$this->setAction('');

		}
	}