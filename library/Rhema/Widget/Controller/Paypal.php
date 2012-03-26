<?php
class Rhema_Widget_Controller_Paypal extends Rhema_Widget_Abstract {

    public function paynowMethod(){

        $cart     = new Ecom_Service_Cart();

        $catTotal = $cart->getTotal();

        if($catTotal){
            $form     = new Rhema_Form_Paypal_Paynow();
            $data     = array(
                'amount'	=> $catTotal,
                'item_name'	=> 'Image Download'
            );

            $form->populate($data);
        }else{
            $form = '';
        }

        $return['button'] = $form;
        return $return;
    }

}