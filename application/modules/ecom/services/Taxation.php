<?php
 
class Ecom_Service_Taxation{
    const TAXRATE = 15;
       
    public function addTax($amount)    {
        $tax = ($amount*self::TAXRATE)/100;
        $amount = round($amount + $tax,2);
        
        return $amount;
    }
}