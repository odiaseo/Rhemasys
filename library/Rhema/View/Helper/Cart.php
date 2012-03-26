<?php
/**
 * Zend_View_Helper_Cart
 *
 * Helper for all shopping cart
 *
 * @category   Storefront
 * @package    Storefront_View_Helper
 * @copyright  Copyright (c) 2008 Keith Pope (http://www.thepopeisdead.com)
 * @license    http://www.thepopeisdead.com/license.txt     New BSD License
 */
class Rhema_View_Helper_Cart extends Zend_View_Helper_Abstract {
	protected $_item;
	protected $_model;

    public $cartModel;
    public $directory;


    public function cart($item = null, $model = null)    {
        $this->cartModel 			= new Ecom_Service_Cart();
        $this->_item				= $item;
    	$this->_currentModel		= $model;
    	$this->directory 			= str_replace('ecom','',strtolower($model));

        return $this;
    }

    public function getSummary()    {
        $currency = new Zend_Currency();
        $itemCount = count($this->cartModel);

        if (0 == $itemCount) {
            return '<p>No Items</p>';
        }

        $html  = '<p>Items: ' . $itemCount;
        $html .= ' | Total: '.$currency->toCurrency($this->cartModel->getSubTotal());
        $html .= '<br /><a href="';
        $html .= $this->view->url(array( 'slug' => 'shopping-cart'), 'site-default-route', true );
        $html .= '" class="curved_btn">View Cart</a></p>';

        return $html;
    }

    public function addForm(Ecom_Model_EcomProduct $product)    {
        $form = $this->cartModel->getForm('cartAdd');
       	$qty  = $form->getElement('qty');
       	$qty->setMultiOptions($this->buildQtyArray());

        $form->populate(array(
            'productId' => $product->id,
            'returnto'  => $this->view->url()
        ));


        $form->setAction($this->view->url(array(
        		'module'		=> 'ecom',
            	'controller' 	=> 'cart',
           		'action' 		=> 'add'
            ),
            ADMIN_ROUTE,
            true
        ));
        return $form;
    }

    public function buildQtyArray($max = 10){
    	$arr = array();

    	for($v=0; $v<$max; $v++){
    		$val = $v+1;
    		$arr[$val] = $val;
    	}

    	return $arr;
    }

    public function cartTable($tableName = 'cartTable')    {
        $cartTable = $this->cartModel->getForm($tableName);
        $frmAction = $this->view->url(array('action' => 'update' ), 'ecommerce-task' );
        $cartTable->setAction($frmAction);

        $cartTable->populate(array(
            'returnto'  => $this->view->url()
        ));

        // add qty elements, use subform so we can easily get them later
        $qtys = new Zend_Form_SubForm();

        foreach($this->cartModel as $item) {
            $qtys->addElement('text', (string) $item->productId,
                array(
                    'value' 		=> $item->qty,
                    'belongsTo' 	=> 'quantity',
                    'class'			=> 'spinner',
                    'decorators' 	=> array(
                        'ViewHelper'
                    ),
                )
            );
        }
        $cartTable->addSubForm($qtys, 'qtys');

        // add shipping options
        $cartTable->addElement('select', 'shipping', array(
            'decorators' => array(
                'ViewHelper'
            ),
            'multiOptions'  => $this->_getShippingMultiOptions(),
            'onChange' 		=> 'this.form.submit();',
            'value' 		=> $this->cartModel->getShippingCost()
        ));

        return $cartTable;
    }

    public function formatAmount($amount)    {
 		$locale    = Zend_Registry::get(('Zend_Locale'));
        $currency  = new Zend_Currency($locale);
        return $currency->toCurrency($amount);
    }

    public function buildDisplay($arr = array(), $replace = array()){
    	$dispIndex 					= 'attr';
		$subject['tab']		    	= array();
		$subject[$dispIndex]		= array();
		if(!count($replace)){
			$replace[$dispIndex]		= array();
		}

		foreach($arr as $data){
			$id          			= $data['EcomAttribute']['id'];
			$index       			= $data['EcomAttribute']['is_tab'] ? 'tab' : 'attr';
			$sequence    			= $data['sequence'];
			$label       			= $data['EcomAttribute']['is_labeled'] ? "<label>$data[label]</label>": '';
			$class       			= strtolower($data['EcomAttribute']['placeholder']);
			$subject[$index][]    	= "<div class='template_unit $class'>" . $label . $data['EcomAttribute']['template'] . '</div>';
			$placeHolder			= $data['EcomAttribute']['placeholder'];
			if($placeHolder){
				$pattern						= "/$placeHolder/";
				$replace[$index][$pattern]    	= $this->getReplacement($data['EcomAttribute']['placeholder']);
			}
		}


    	$output 	= preg_replace(array_keys($replace[$dispIndex]), array_values($replace[$dispIndex]), implode(' ', $subject[$dispIndex]));

    	return $output;
    }

    public function getObject(){
    	if($this->_currentModel and isset($this->_item[$this->_currentModel])){
    		$object 	= $this->_item[$this->_currentModel];
    	}else{
    		$object 	= $this->_item;
    	}

    	return $object;
    }

    public function getReplacement($pattern){
    	$value  	   = '';
    	$object 	   = $this->getObject();

    	$imageParams[] = 32;
    	$imageParams[] = 32;

    	switch($pattern){
    		case 'PRICE'		: $value = $this->formatAmount(floatval($object['price']));  break;
    		case 'RRP' 			: $value = $this->formatAmount(floatval($object['rrp'])); break;
    		case 'BUY' 			: $value = $this->addForm($object); break;
    		case 'THUMBNAIL'	: $value = $object->getImage('thumbnail', $this->directory, $imageParams); break;
    		case 'STD_IMAGE'	: $value = $object->getImage('large', $this->directory, $imageParams); break;
    		case 'ZOOM_IMAGE'	: $value = $object->getImage('zoom', $this->directory, $imageParams); break;
    		case 'IMAGE_SIZE'	: $value = " style='width:$imageParams[0]; height:$imageParams[1];' ";
    		case 'TITLE'		: $value = $this->getTitle();  break;
    		default: $value = '';
    	}

    	return $value;
    }

    protected function getTitle(){
    	if($this->_currentModel == 'EcomProduct'){
    		$title = $this->_item[$this->_currentModel]['title'];
    	}else{
    		$title = $this->_item['title'];
    	}
    	return $title;
    }

    private function _getShippingMultiOptions()    {
        $currency = new Zend_Currency();
        $shipping = new Ecom_Service_Shipping();
        $options  = array(0 => 'Please Select');

        foreach($shipping->getShippingOptions() as  $method) {
        	$key     = $method['title'];
        	$value   = $method['cost'];
            $options["$value"] = $key . ' - ' . $currency->toCurrency($value);
        }

        return $options;
    }
}
