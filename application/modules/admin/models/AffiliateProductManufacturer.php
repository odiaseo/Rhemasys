<?php

/**
 * Admin_Model_AffiliateProductManufacturer
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Admin_Model_AffiliateProductManufacturer extends Admin_Model_Base_AffiliateProductManufacturer
{
	public function getManufacturer($id, $key = 'id'){
		$filter = new Rhema_Dao_Filter();
		$filter->setModel(__CLASS__)
			   ->addCondition($key, $id); 
		return Rhema_Model_Service::createQuery($filter)->fetchOne();		
	}
		
	public static function listManufacturers($prefix = null, $mode = Doctrine_Core::HYDRATE_ARRAY){
    	$daoFilter = new Rhema_Dao_Filter();
    	$daoFilter->setModel(__CLASS__)    			  
			      ->setHydrationMode($mode)
    	          ->addOrderBy('title');
    	          
    	if('0-9' == $prefix){
	    	for($i=0; $i<=9; $i++){
	    		$daoFilter->addCondition('title', $i, Rhema_Dao_Filter::OP_LLIKE,Rhema_Dao_Filter::OP_OR);
	    	}
    	}else if($prefix){
    		$daoFilter->addCondition('title', $prefix, Rhema_Dao_Filter::OP_LLIKE);
    	}	
    		  
    	return Rhema_Model_Service::createQuery($daoFilter)->execute();		
	}
	
	public function fixManufacturerTitle(){
		$data  = array();
		$list  = $this->listManufacturers(null, Doctrine_Core::HYDRATE_RECORD);
		foreach($list as $item){
			$title       = html_entity_decode($item['title']) ;
			$name        = Rhema_Util_String::prepareTitleForSlug($title);
			$slug        = Doctrine_Inflector::urlize($name);
			$item->title = $title ;
			
			if($item->slug != $slug){
				$exist = $this->getManufacturer($slug, 'slug');
				if($exist){
					$daoFilter = new Rhema_Dao_Filter();
					$daoFilter->setModel(MODEL_PREFIX . 'AffiliateProduct')
						      ->setUpdateList(array('affiliate_product_manufacturer_id' => $exist['id']))
						      //->setDebug(true)
						      ->setQueryType(Rhema_Dao_Filter::QUERY_TYPE_UPDATE)
						      ->addCondition('affiliate_product_manufacturer_id', $item['id']);
				   $prod = Rhema_Model_Service::createQuery($daoFilter)->execute(); 
				   $data['moved'][] = "{$item['id']} moved to {$exist['id']} ({$prod})"; 
				}else{					
					$data['update'][] = "{$item->slug} updated to {$slug}";
					$item->slug = $slug ;					
				}
			}
			$item->save();					
		}			
		return $data ;
	}
}