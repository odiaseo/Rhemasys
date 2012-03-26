<?php

class Ecom_Service_Cart_Item implements Ecom_Service_Cart_Item_Interface{
    public $productId;
    public $name;
    public $price;
    public $taxable;
    public $discountPercent;
    public $qty;
    public $slug;
    public $isVirtual;
    public $downloadPath;

    public function __construct($product, $qty)    {
        $this->productId           = $product['id'];
        $this->code                = $product['code'];
        $this->name                = $product['title'];
        $this->price               = (float) $product['price'];
        $this->taxable             = $product['is_taxable'];
        $this->discountPercent     = (float) $product['discount_percent'];
        $this->qty                 = (int) $qty;
        $this->slug				   = $product['slug'];
        $this->href				   = isset($product['href']) ? $product['href'] : '';
        $this->isVirtual           = (int)$product['isVirtual'];
        $this->downloadPath        = $product['downloadPath'];
        $this->thumb			   = $product['thumb']
        							 ? Admin_Model_EcomProduct::getImage($product, 'small')
        							 : '';
    }

    public function getLineCost()    {
        $price = $this->price;

        if(0 !== $this->getTotalDiscount()) {
            $discounted = ($price*$this->discountPercent)/100;
            $price      = round($price - $discounted, 2);
        }

        if($this->taxable) {
            $taxService = new Ecom_Service_Taxation();
            $price      = $taxService->addTax($price);
        }

        return $price * $this->qty;
    }

    public function getTotalDiscount(){
    	return $this->discountPercent;
    }


}