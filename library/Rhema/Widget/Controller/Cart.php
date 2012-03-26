<?php

class Rhema_Widget_Controller_Cart extends Rhema_Widget_Abstract{
    protected $_cartModel;
    protected $_catalogModel;

    public function __construct()    {
    	parent::__construct();

        $this->_cartModel    = new Ecom_Service_Cart();
        $this->_catalogModel = new Admin_Model_EcomProduct();
    }

    public function addMethod()    {
        $product = $this->_catalogModel->getProductById($this->_getParam('productId'));

        if (null === $product) {
            throw new Exception('Product could not be added to cart as it does not exist');
        }

        $this->_cartModel->addItem($product, $this->_getParam('qty'));

        $return 	= $this->_getParam('returnto');
        $returnTo   = str_replace(BASE_URL, '', $return );
		$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        //$redirector = $this->getHelper('redirector');

        return $redirector->gotoUrl($returnTo);
    }

    public function viewMethod()    {
        $this->view->cartModel = $this->_cartModel;
    }

    public function updateMethod()    {
        foreach($this->_getParam('quantity') as $id => $value) {
            $product = $this->_catalogModel->getProductById($id);
            if (null !== $product) {
                $this->_cartModel->addItem($product, $value, 1);
            }
        }

        /* Should really get from the shippingModel! */
        $this->_cartModel->setShippingCost($this->_getParam('shipping'));

        $return 	= $this->_getParam('returnto');
        $returnTo   = str_replace(BASE_URL, '', $return );
        $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');

 		//$redirector = $this->getHelper('redirector');

        return $redirector->gotoUrl($returnTo);
    }

    public function removeMethod(){

    }

    public function summaryMethod(){

    }

    public function basketMethod(){
		$return['cartTable'] = 'cartDownload'; // change to cartTable for standard shopping cart
		return $return;
    }

    public function confirmationMethod(){
		$return['cartTable'] = 'cartConfirm';
		$postData            = $this->_request->getPost();
        $return['vars']      = $postData ;

         Admin_Model_EcomOrder::saveTransaction($postData);

        return $return;
    }
}
