<?php
class Rhema_View_Helper_DisplayFeaturedCategoryColumn extends Zend_View_Helper_Abstract {
	public $titleLength = 25 ;
	
	public function displayFeaturedCategoryColumn(){
		return $this;
	}
	
	public function displayColumn($categories){
		$helper   = Rhema_Util_String::getInstance();
		$affData  = Admin_Model_AffiliateProduct::getProductStatList();
		$giftList = isset($affData['gifts']) ? $affData['gifts']  : array();
		$count    = 0;
		foreach($categories as $feature){ 
			$count++; 
			$class = ($count == 3) ? ' last' : ''; 
			$cat     = $feature['AffiliateProductCategory'];			
			$catLink = $this->view->url(array('category' => $cat['slug']),'mobile-category', true); ?>
			<div class="featured-col rounded<?php echo $class; ?>">
				<h2><a href="<?php echo $catLink; ?>" title="<?php echo $cat['title']; ?>"><?php echo $feature['title']; ?></a></h2>
					<h3><?php echo $feature['description']; ?></h3><ul><?php 
					foreach($feature['AffiliateProduct'] as $item){  
						$title   = Doctrine_Inflector::urlize($item['title']);
						if($item['affiliate_promotion_id'] > 1){
							$giftTitle = 'Free ' . $giftList[$item['affiliate_promotion_id']]['title']; 
							$giftspan = "<ins class='gift-icon' title='{$giftTitle}'>&nbsp;</ins>";
						}else{
							$giftspan =  '';
						}
						$prodLink     = $this->view->url(array( 'title'	=> $title , 'id'	=> $item['id'] ),'affiliate-product-detail', true, false);
						$retailerLink = $this->view->url(array('retailer' => $item['AffiliateRetailer']['slug']), 'affiliate-retailer', true); ?>
						<li><h4><a href="<?php echo $prodLink; ?>" title="View product details : <?php echo $item['title']; ?>"><?php echo $helper->shortenTitle($item['title'], strlen($item['title'])); ?></a></h4>
						<h5><a href="<?php echo $retailerLink; ?>" title="View all <?php echo $item['AffiliateRetailer']['title']; ?> products"><?php echo $item['AffiliateRetailer']['title']; ?></a></h5>
						<?php echo $giftspan; ?></li>
<?php    			}
				?></ul> 
				<div class="more-cat"><a href="<?php echo $catLink; ?>" title="<?php echo $cat['title']; ?>">view more ... </a></div>
			</div><?php 
		}
	}
	public function getProducts($products){
		$helper    = Rhema_Util_String::getInstance();
		$affData   = Admin_Model_AffiliateProduct::getProductStatList();
		$giftList  = isset($affData['gifts']) ? $affData['gifts']  : array();
 		$cur       = new Zend_Currency();?>
		<ul><?php 
		foreach($products as $item){ 
			$cur->setValue($item['price']); 
			$item         = $this->view->populateAffiliateProduct($item);	
			$disp         = Rhema_Util_String::correctEncoding($item['title'])	;	 
			$title        = Doctrine_Inflector::urlize($disp);
			$anchor       = sprintf("<span class='prod-span product-price'>%s</span>", $cur);
			$deepLink     = $this->view->affiliateLink()->getDeeplink($item, $anchor, 'promo', true);
			$prodLink     = $this->view->url(array( 'title'	=> $title , 'id'	=> $item['id'] ),'affiliate-product-detail', true, false);
			$retailerLink = $this->view->url(array('retailer' => $item['AffiliateRetailer']['slug']), 'affiliate-retailer', true); ?>
			<li class="rounded"><h4><a href="<?php echo $prodLink; ?>" title="<?php echo $item['title']; ?>">
			<?php echo $helper->shortenTitle($disp, 20); ?>
			</a></h4><a href="<?php echo $prodLink; ?>"><img src="<?php echo $item['image_url']; ?>" alt='<?php echo $item['title']; ?>'/></a>
			<?php echo $deepLink?> 
			<h5><a href="<?php echo $retailerLink; ?>" title="<?php echo $item['AffiliateRetailer']['title']; ?> products"><?php echo $item['AffiliateRetailer']['title']; ?></a></h5>
			 </li>
<?php    			}
				?></ul>  <?php 
		}
}