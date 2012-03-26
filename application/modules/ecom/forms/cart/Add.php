<?php
	class Ecom_Form_Cart_Add extends Ecom_Form_Abstract {
		
		
		public function init(){
			$this->setDisableLoadDefaultDecorators(true);
			$this->setMethod('post');			
			$this->setAction('');
			
			$this->setDecorators(array(
				'FormElements',
				'Form'
			));
						
			 
			$this->addElement('select', 'qty', array(
				'decorators'  => array(
					'ViewHelper'
				),
				'value'			=> 1,
				'multiOptions'  => array(),
				'order'			=> 1,
				
			));

			$this->addElement('submit', 'buy-item', array(
				'decorators'  => array(
					'ViewHelper'
				), 
				'label'	=> 'Add to cart',
				'order'	=> 2,
				'class'	=> 'curved_btn'	,			
				
			));
			
			$this->addElement('hidden', 'productId', array(
				'decorators'  => array(
					'ViewHelper'
				), 
			));
			
			$this->addElement('hidden', 'returnto', array(
				'decorators'  => array(
					'ViewHelper'
				), 
			));
		}
	}