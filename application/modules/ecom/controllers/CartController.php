<?php


class Ecom_CartController extends Zend_Controller_Action {
    protected $_cartModel;
    protected $_catalogModel;

    public function init()    {
        $this->_cartModel    = new Ecom_Service_Cart();
        $this->_catalogModel = new Admin_Model_EcomProduct();
    }

    public function addAction()    {
    	$prodId   = $this->_getParam('productId', null);
    	$qty      = $this->_getParam('qty', 1);
    	$return   = $this->_getParam('returnto', '/');

        $product  = $this->_catalogModel->getProductById($prodId);

        if (!$product) {
        	$message       = 'Product could not be added to cart as it does not exist';
        	$messageType   =   Rhema_Dto_UserMessageDto::TYPE_WARNING;
        }else{
        	if($product['is_virtual']){
        		$product = $this->_prepareVirtualProduct($product);
        	}
	       try{
	        	$this->_cartModel->addItem($product, $qty);
	        	$message = 'Product added successfully';
	        	$messageType =   Rhema_Dto_UserMessageDto::TYPE_SUCCESS;
	       }catch(Exception $e){
	       		$message = $e->getMessage();
	       		$messageType =   Rhema_Dto_UserMessageDto::TYPE_ERROR;
	       }
        }
        $userMessage =  new Rhema_Dto_UserMessageDto($message, 'Add to basket', $messageType);
        $this->_helper->flashMessenger($userMessage);
        $redirector = $this->getHelper('redirector');

        return $redirector->gotoUrlAndExit($return);
    }

    public function _prepareVirtualProduct(array $product){
    	$form 	 = new Rhema_Form_Cart_AddDownload();
    	$posted	 = $this->getRequest()->getPost();

    	if($form->isValid($posted)){
        	$values  = $form->populate($posted)->getValues();


        	$product['title']     = $values['imageId'] . ' - ' . $values['category'];
        	$product['id']        = $values['imageId'];
        	$product['thumb']     = $values['imagePath'];
        	$product['href']      = $values['returnto'];
        	$product['downloadPath']    = $values['downloadPath'];
        	$product['isVirtual']       = 1;


        	return $product ;
    	}else{
    	    throw('Unable to add product');
    	}
    }

    public function viewAction()    {
        $this->view->cartModel = $this->_cartModel;
    }

    public function updateAction()    {
        foreach($this->_getParam('quantity', array()) as $id => $value) {
            $product = $this->_catalogModel->getProductById($id);
            if ($product) {
                $this->_cartModel->addItem($product, $value, 1);
            }
        }

        foreach($this->_getParam('toDelete', array()) as $id => $value){
        	$this->_cartModel->removeItem($id);
        }

        /* Should really get from the shippingModel! */
        $this->_cartModel->setShippingCost($this->_getParam('shipping'));

        $return 	= $this->_getParam('returnto');
 		$redirector = $this->getHelper('redirector');

        return $redirector->gotoUrlAndExit($return);
    }

    public function removeAction(){

    }

    public function summaryAction(){

    }

    public function basketAction(){

    }

    public function checkoutAction(){


    }
}
