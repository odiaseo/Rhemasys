<?php
	class Rhema_Form_Cart_AddDownload extends Rhema_Form_Abstract {

	    const VIRTUEL_PRODUCT_KEY = 'VIRTUEL_PRODUCT_KEY';

		public function init(){

			$this->setDisableLoadDefaultDecorators(true);
			$this->setMethod('post');
			$this->setAction('');

			$this->setDecorators(array(
				'FormElements',
				'Form'
			));


			$this->addElement('hidden', 'qty', array(
				'decorators'  => array(
					'ViewHelper',
			        'Errors',
				),
				'value'			=> 1,
				'filters'		=> array('StripTags', 'Digits')

			));



			$this->addElement('hidden', 'category', array(
				'decorators'  => array(
					'ViewHelper',
			        'Errors',
				),
				'filters'		=> array('StripTags')
			));
			$this->addElement('hidden', 'imageId', array(
				'decorators'  => array(
					'ViewHelper',
			        'Errors',
				),
				'filters'		=> array('StripTags')
			));

			$this->addElement('hidden', 'imagePath', array(
				'decorators'  => array(
					'ViewHelper',
			        'Errors',
				),
				'filters'		=> array('StripTags')
			));

			$this->addElement('hidden', 'downloadPath', array(
				'decorators'  => array(
					'ViewHelper',
			        'Errors',
				),
				'filters'		=> array('StripTags')
			));

			$this->addElement('select', 'productId', array(
				'decorators'  => array(
					'ViewHelper',
			        'Errors',
				),
				'multiOptions'  => $this->getProductList(),
				'filters'		=> array('StripTags')
			));

			$this->addElement('hidden', 'returnto', array(
				'decorators'  => array(
					'ViewHelper',
			        'Errors',
				),
				'value'	      => $this->getView()->url(),
				'filters'	  => array('StripTags')
			));

			$this->addElement('submit', 'add', array(
			    'ignore'	  => true,
				'decorators'  => array(
					'ViewHelper'
				),
				'class'	      => 'rounded'
			));
		}

		public function getProductList(){

		    if(Zend_Registry::isRegistered(self::VIRTUEL_PRODUCT_KEY)){
		        $return = Zend_Registry::get(self::VIRTUEL_PRODUCT_KEY);
		    }else{

    		    $ecomProd = Rhema_Model_Service::factory('ecom_product' )->listVirtualProducts();
                $currency = new Zend_Currency();
                $return   = array();
                foreach((array) $ecomProd as $item){
                    $currency->setValue($item['price'] );
                    $return[$item['id']] = $item['title'] . ' - ' . $currency;
                }

                Zend_Registry::set(self::VIRTUEL_PRODUCT_KEY, $return);
		    }

            return $return;
		}
	}