<?php
class Rhema_View_Helper_DisplayProductInfoList extends Zend_View_Helper_Abstract {
	protected $_columns = array ('retailer',
								 'isbn', 
								 'brand', 
								 'manufacturer',								 
								 'fabric', 
								 'size', 
								 'colour', 
								 'gender', 
								 'delivery_cost', 
								 'delivery_period', 
								 'delivery_time',
								 'stock_quantity',
								 'type', 
								 'rrp',
								 'mobile_network', 
								 'contract_length', 
								 'inclusive_texts', 
								 'inclusive_minutes', 
								 'data_allowance', 
								 'service_provider', 
								 'handset_price' ,
								 'specification',
								 'specifications', 
						);
	protected $_cur;
	
	public function __construct() {
		$this->_cur = new Zend_Currency ();
	}
	
	public function displayProductInfoList(array $product, $class = '') {
		//pd($product);
 
		$return = '';
		$arr    = array ();
		$logos  = $this->view->displayBrandLogo($product);
		$cur    = new Zend_Currency ();
		foreach ( $this->_columns as $key ) {
			$val   = false ;
			$label = ucwords ( str_replace ( '_', ' ', $key ) );
			if (isset ( $product [$key] ) and $product [$key]) {
			 if('rrp_price' == $key or 'rrp' == $key or 'recommended_retail_price' == $key){
				$cur->setValue ( $product [$key] );	
				$arr[] = sprintf("<li><h4>RRP: </h4><span  class='strike'>%s</span></li>", ( string ) $cur);	
				continue;	
			}elseif (preg_match ( '/(cost|price)$/i', $key )) {
					if (floatval ( $product [$key] ) <= 0) {
						continue;
					}
					$cur->setValue ( $product [$key] );
					$val = ( string ) $cur;
				}else {
					$val = $product [$key];
					if ($key == 'data_allowance') {
						$val = $val ? 'Yes' : 'No';
					} elseif ($key == 'contract_length') {
						$val .= ' months';
					}
				}				
			}elseif('retailer' == $key and isset($product['AffiliateRetailer']['slug'])){
				$title = $product['AffiliateRetailer']['title'] ; 
				$logo  = $title ; 
				if(isset($product['AffiliateRetailer']['deeplink'])){
					//pd($product);
					$url  = $this->view->url(array('retailer' => $product['AffiliateRetailer']['slug']), 'affiliate-retailer', true);
					$link = $this->view->affiliateLink()->addClickRef($product, $product['AffiliateRetailer']['deeplink']);
					$val  = sprintf("<a href='%s' title='Retailer - %s' class='outlink' %s='%s'>%s</a>", 
							$url, $title , $this->view->linkAttribute, $link , $logo );
				}else{
					$url = $this->view->url(array('retailer' => $product['AffiliateRetailer']['slug']), 'affiliate-retailer', true);
					$val = sprintf("<a href='%s' title='Retailer - %s'>%s</a>", $url, $title , $logo);
				}
				
				if($logo == $title){
					$arr []  = "<li>$val</li>" ;
				}else{
					$logos[] = $val ;
				}
				 
				continue;
			}elseif('manufacturer' == $key and isset($product['AffiliateProductManufacturer']['slug']) 
				and $product['AffiliateProductManufacturer']['id'] != 1){
				$url = $this->view->url(array('manufacturer' => $product['AffiliateProductManufacturer']['slug']), 'affiliate-manufacturer', true);
				$val = sprintf("<a href='%s' title='Manufacturer - %s'>%s</a>", $url, $product['AffiliateProductManufacturer']['title'], $product['AffiliateProductManufacturer']['title']);
			}elseif('brand' == $key and isset($product['AffiliateProductBrand']['slug'])){
				$url = $this->view->url(array('brand' => $product['AffiliateProductBrand']['slug']), 'affiliate-brand', true);
				$val = sprintf("<a href='%s' title='Brand - %s'>%s</a>", $url, $product['AffiliateProductBrand']['title'], $product['AffiliateProductBrand']['title']);				
			}else{
				continue ;
			} 
			if($val){
				$arr [] = "<li><h4 class='{$key}'>{$label}: </h4>{$val}</li>";
			}
		}
		
		if(count($logos)){		 
			$return .= '<div class="brand-logo">' . implode('', $logos) . '</div>';	 
		}

		if(count($arr)){
			$return .= "<ul class='$class'>" . implode ( PHP_EOL, $arr ) . '</ul>';
		}
		return $return ;
	}
}