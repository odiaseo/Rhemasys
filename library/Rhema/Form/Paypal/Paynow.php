<?php
class Rhema_Form_Paypal_Paynow extends Rhema_Form_Abstract{
/*
 * Paypal Paynow button
 */
    public function init(){
        $options = Rhema_SiteConfig::getConfig('settings.paypal');

        $this->setAction($options['action']);
        $this->setMethod('post');
        $domain  = 'http://' . Zend_Controller_Front::getInstance()->getRequest()->getHttpHost();


        $this->addElement('hidden', 'cmd', array(
            'value'	=> '_xclick',
            'decorators' => array('ViewHelper',)
        ));

        $this->addElement('hidden', 'amount', array(
            'value' => 0,
             'decorators' => array('ViewHelper',)
        ));

        $this->addElement('hidden', 'cancel_ return', array( //trasaction cancelled
            'value' => $domain  . $this->getView()->url(array('slug' => 'shopping-cart'), 'default'),
             'decorators' => array('ViewHelper',)
        ));
        $this->addElement('hidden', 'return', array( // order confirmation page
            'value' => $domain  . $this->getView()->url(array('slug' => 'order-confirmation'), 'default'),
             'decorators' => array('ViewHelper',)
        ));

        $this->addElement('hidden', 'rm', array( //paypal return method
            'value' => 2,
             'decorators' => array('ViewHelper',)
        ));

        $this->addElement('hidden', 'currency_ code', array(
            'value' => 'GBP',
             'decorators' => array('ViewHelper',)
        ));

        $this->addElement('hidden', 'invoice', array(
            'value' => 'RMS-' . date('Ymd-His'),
             'decorators' => array('ViewHelper',)
        ));

        $this->addElement('hidden', 'item_name', array( //paypal return method
            'value' => date('Ymd-His', gmmktime()),
             'decorators' => array('ViewHelper',)
        ));

        $this->addElement('hidden', 'business', array( //paypal return method
            'value' => $options['business'],
             'decorators' => array('ViewHelper',)
        ));

        $this->addElement('hidden', 'bn', array( //paypal return method
            'value' => 'RhemaStudio_BuyNow_WPS_' . Zend_Registry::get('Zend_Locale')->getRegion(),
             'decorators' => array('ViewHelper',)
        ));

        $this->addElement('image','submit', array(
            'src'    => $options['button'],
            'border' => "0",
            'alt'    => "PayPal - The safer, easier way to pay online.",
            'decorators' => array('ViewHelper',)
        ));
    }
}
?>