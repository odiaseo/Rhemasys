<?php

/**
 * Admin_Model_AffiliateProductCategory
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Admin_Model_AffiliateProductCategory extends Admin_Model_Base_AffiliateProductCategory
{
	public static $tempCols = array('affiliateParentId', 'affiliateCategoryId');
	const VOUCHER_CATEGORY_SLUG = 'vouchers-deals' ;
	const SIBLINGS    = 1;
	const DESCENDANTS = 2;
	
	public static function listCategory($prefix = null, $mode = Doctrine_Core::HYDRATE_ARRAY){
		$filter = new Rhema_Dao_Filter();
		$filter->setModel(__CLASS__)
			   //->setDebug(true)
			   ->setHydrationMode($mode)
			   ->addOrderBy('id')
			   ->addOrderBy('title');
 
    	if('0-9' == $prefix){
	    	for($i=0; $i<=9; $i++){
	    		$filter->addCondition('title', $i, Rhema_Dao_Filter::OP_LLIKE,Rhema_Dao_Filter::OP_OR);
	    	}
    	}else if($prefix){
    		$filter->addCondition('title', $prefix, Rhema_Dao_Filter::OP_LLIKE);
    	}    	
    	//return pd(Rhema_Model_Service::createQuery($filter));
		return Rhema_Model_Service::createQuery($filter)->execute();
	}
	
	public function searchCategoryIndex($searchTerm, $perPage = null, $page = 1){
		$perPage  = $perPage ? $perPage : Rhema_SiteConfig::getConfig('settings.items_per_page');
		$prd      = Doctrine_Core::getTable(__CLASS__); 			
		$filter =  new Rhema_Dao_Filter($perPage, $page);
    	$filter->addFields(array('title', 'id','slug'))
    			  ->setModel(__CLASS__)  ;
  	   
		
		$query  = Rhema_Model_Service::createQuery($filter);		
		$res    = $prd->search($searchTerm, $query);	

		return $res->execute();
	}	
	/**
	 * Create product category menu (nested set) from merchant data downloaded from 
	 * affiliate networks. Find existing category is present, update data and hierachy
	 * as required. Fields should be mapped before building the tree
	 * @param unknown_type $file
	 * @param unknown_type $mapping
	 */
	public function buildTreeFromCsv($file, $mapping = array(), $neworkData = array()){
		set_time_limit(0);
		$model       = __CLASS__ ;
		$table       = Doctrine_Core::getTable($model);
		$dbCols      = array_merge($table->getColumnNames() , self::$tempCols);
		$rootMenu    = $table->find(1);
		$mapped	     = array();
		$mController = Zend_Controller_Front::getInstance()->getDefaultControllerName();
		
	    foreach($dbCols as $col){
        	if(isset($mapping[$col]['columns']) and $mapping[$col]['columns']){
        		$mapped[$col] = Doctrine_Inflector::urlize($mapping[$col]['columns']);
        	}
        }
  
		if(file_exists($file) and ($handle= fopen($file,'r')) !== false){
			$cols = fgetcsv($handle);
			foreach($cols as $col){
				$cln        = Doctrine_Inflector::urlize($col);
				$feedCols[] = $cln;
			}
			while ( ($data = fgetcsv ( $handle )) !== false ) {				
				$values    = array_combine ( $feedCols, $data );
				foreach($mapped as $dbCol => $feedCol){
					$id        = $values['category-id'];
					//$list[$id] = $values;
					$val = (isset($values[$feedCol])) ? $values[$feedCol] : '' ;
					$list[$id][$dbCol] = $val ;
					 
				}
			}
			//pd($list);
			fclose ( $handle );
			$filterClass = new Rhema_Util_FeedFilter();
      
			foreach ( $list as $id => $valid ) {
				$record = array();
				foreach($valid as $col => $value){ 
					if(isset($mapping[$col]['filters']) and $mapping[$col]['filters']){
						foreach((array) $mapping[$col]['filters'] as $method){
							$value = $filterClass->{$method}($value);
						}
					}
					$record[$col] = $value ;
				}
				
				$record['m_controller'] 		= $mController ;
				$record['label']				= $record['title'];	
				$record['affiliate_network_id'] = $neworkData['id']; 
				$slug   = Doctrine_Inflector::urlize($record['title']);
				
				$entry  = $table->findOneBy('slug',$slug, Doctrine_Core::HYDRATE_RECORD);			
				div('Processing ' . $record['title'], ''); 
				if(!$entry){						 
					$entry 			= self::createCategoryMenu($record);;				
				}
				$parentId   = $record['affiliateParentId'];
				if($parentId){
					$parentSlug = Doctrine_Inflector::urlize($list[$parentId]['title']);
					if($parentSlug != $slug){
						$parentMenu = $table->findOneBy('slug', $parentSlug, Doctrine_Core::HYDRATE_RECORD);
						
						if(!$parentMenu){  
							$list[$parentId]['affiliate_network_id'] = $neworkData['id'];
							$parentMenu = self::createCategoryMenu($list[$parentId]); 
						}				 
						
						if(!$parentMenu->getNode()->isDescendantOf($entry)){
							$entry->getNode()->moveAsLastChildOf($parentMenu);
							$entry->slug = $slug;
							$entry->save();						
							$parentMenu->free();
						}
					}
				}
				$entry->free();	
				div('done!', "\n", '')	;		 
			}
		}
	
	}
	/*
	 * 		        foreach($mapped as $col => $m){ 
	        		$values[$col]  = isset($valid[$m]) ? $valid[$m] : '';
	        		$values[$col]  = mysql_escape_string($values[$col]);
		        }
	 */
	public function buildTree($theNodes, $ID = 0) {
		$tree = array ();
		
		if (is_array ( $theNodes )) {
			foreach ( $theNodes as $node ) {
				if ($node ["ParentID"] == $ID)
                array_push($tree, $node);
            }

        for($x = 0; $x < count($tree); $x++)
            {
            $tree[$x]["Children"] = $this->buildTree($theNodes, $tree[$x]["ID"]);
            }

        return($tree);
        }
    }
    	
	public function getCategory($id, $key = 'id', $mode = Doctrine_Core::HYDRATE_ARRAY){
		$filter = new Rhema_Dao_Filter();
		$filter->setModel(__CLASS__)
			   ->setHydrationMode($mode)		
			   ;
			   //->setDebug(true)			   
		if(is_array($id)){
			$filter->addCondition($key, $id, Rhema_Dao_Filter::OP_IN);
			$return = Rhema_Model_Service::createQuery($filter)->execute();
		}else{
			$filter->addCondition($key, $id);		
			$return = Rhema_Model_Service::createQuery($filter)->fetchOne();
		}	
		
		return $return ;	
	}
	public function countProductsByCategory(){
    	$daoFilter = new Rhema_Dao_Filter();
    	$daoFilter->setModel(__CLASS__) 
    			  ->addJoin('AffiliateProduct', Rhema_Dao_Filter::LEFT_JOIN, array('id'))
    			  ->addFields(array('id', 'title', 'slug'))
    			  ->addGroupBy('id')
    			  ->addOrderBy('title')
    			//  ->setDebug(true)
    			  ->addAggregateFieldList('AffiliateProduct.id', 'COUNT');
		  
    	$return = Rhema_Model_Service::createQuery($daoFilter)->execute();
     
    	return $return;
	}

	public function getKeywords(){
    	$daoFilter = new Rhema_Dao_Filter();
    	$daoFilter->setModel(__CLASS__)
    			  ->addCondition('keywords', null, Rhema_Dao_Filter::OP_NOT_NULL)
    			  ->addFields(array('keywords')) ;   			 			   	  
    	return Rhema_Model_Service::createQuery($daoFilter)->execute()		;
	}
	
	public function createCategoryMenu($data){	
		$model    		 = __CLASS__ ;		
		$mainRoot        = self::getRootMenu($data);
		
		$data['root_id'] = $mainRoot->id;
		$data['label']   = $data['title'] ;
		$data['m_route'] = 'mobile-category';
		if(!isset($data['slug']) or !$data['slug']){
			$slugTitle       = Rhema_Util_String::prepareTitleForSlug($data['title']);
			$data['slug']    = Doctrine_Inflector::urlize($data['title']);
		}
		$data['params']  = 'category=' . $data['slug'] ;	
			 
		$catMenu = self::getDefaultRow($data, $model);					 
		$catMenu->getNode()->insertAsLastChildOf($mainRoot);	
		$catMenu->slug = $data['slug'] ;
		$catMenu->save();
		
		return $catMenu ;
	}
	
	/**
	 * @return the $_rootMenu
	 */
	public static function getRootMenu($default = array()) { 			
		$model    = __CLASS__ ;
		$rootMenu = Doctrine_Core::getTable($model)->getTree()->fetchRoot(1);
		if(!$rootMenu){
			$title = 'Discount Categories' ;
			$row   = new $model();
			$slug  = Doctrine_Inflector::urlize($title);
			$row->fromArray($default);
			$row->title    = $title;
			$row->label    = $title ;
			$row->root_id  = 1; 
			$row->level    = 0 ;
			$row->slug     = $slug;
			$row->sequence = 1;
										
			$row->save(); 				
			$row->getTable()->getTree()->createRoot($row);	
			$row->slug     = $slug ;
			$row->save();
			$rootMenu = $row ;
		}
		 
		return $rootMenu;
	}
	
	public function fixCategoryTitle(){
		$data  = array();
		$list  = $this->listCategory(null, Doctrine_Core::HYDRATE_RECORD);
		foreach($list as $item){
			$title       = html_entity_decode($item['title']) ;
			$name        = Rhema_Util_String::prepareTitleForSlug($title);
			$slug        = Doctrine_Inflector::urlize($name);
			$item->title = $title;
			
			if($item->slug != $slug){
				$exist = $this->getCategory($slug, 'slug');
				if($exist){
					$prod = $this->swapProductCategory($item['id'], $exist['id']); 
				    $data['moved'][] = "{$item['id']} moved to {$exist['id']} ({$prod})"; 
				}elseif($slug){					
					$data['update'][] = "{$item->slug} updated to {$slug}";
					$item->slug = $slug ;					 
				}
			}
			$item->save();					
		}			
		return $data ;
	}	
	
	public function swapProductCategory($from, $to){
		$daoFilter = new Rhema_Dao_Filter();
		$daoFilter->setModel(MODEL_PREFIX . 'AffiliateProduct')
			      ->setUpdateList(array('affiliate_product_category_id' => $to)) 
			      ->setQueryType(Rhema_Dao_Filter::QUERY_TYPE_UPDATE)
			      ->addCondition('affiliate_product_category_id', $from);
	   return Rhema_Model_Service::createQuery($daoFilter)->execute(); 		
	}
	
	public static function getTopLevelCategories($mode = Doctrine_Core::HYDRATE_ARRAY){
    	$daoFilter = new Rhema_Dao_Filter();
    	$daoFilter->setModel(__CLASS__)
    			  ->addCondition('level', 1, Rhema_Dao_Filter::OP_EQ)
    			  ->setHydrationMode($mode)
    			  ->addOrderBy('title')
    			  ->addFields(array('id', 'title', 'slug')) ;   			 			   	  
    	return Rhema_Model_Service::createQuery($daoFilter)->execute();				
	}
	
	
	public function buildTopLevelCategory($catList = array()){ 
		$list      = array();  ;
		$topLevel  = $this->getTopLevelCategories(Doctrine_Core::HYDRATE_RECORD);
		$countById = array();

		if(isset($catList[1]['countid']) and $catList[1]['countid']){ // products present in toplevel
			$miscCategory = $this->getCategory('miscellaneous', 'slug');
			if(!$miscCategory){				
				$data = array(
					'title' => 'Miscellaneous'
				);
				$miscCategory = $this->createCategoryMenu($data);
			}
			
			$done = $this->swapProductCategory(1, $miscCategory['id']);
			div("\n$done products moved from root category to {$miscCategory['title']}");
		}
		
		foreach($topLevel as $cat){			
			$grpTotal = 0;
			$children = $cat->getNode()->getDescendants();  
			$sorted   = array();
			if($children){
				foreach($children as $child){
					$cnt   = isset($catList[$child->id]['countid']) ? $catList[$child->id]['countid'] : 0;
					$sorted[$child->title] = array('title' => $child->title, 'id' => $child->id, 'slug' => $child->slug, 'count' => $cnt) ;	
					$grpTotal += $cnt;
				}
				ksort($sorted);				 
			} 
			
			$grpTotal += isset($catList[$cat->id]['countid']) ? $catList[$cat->id]['countid'] : 0; 
			if($grpTotal){
				$par    = array('title' => $cat->title, 'id' => $cat->id, 'slug' => $cat->slug, 'count' => $grpTotal) ;			
				$list[$cat->id] = array('parent' => $par, 'children' => $sorted);
			}
		}
		
		return $list ; 	
	}
	
	public function getSubcategory($catId){
		$list     = array();
		$type     = null;
		$category = Doctrine_Core::getTable(__CLASS__)->find($catId);
		if($category and $category['level'] > 0){
			$node = $category->getNode();
			if($node->hasChildren()){
				$list = $category->getNode()->getDescendants();
				$type = self::DESCENDANTS ;
			}elseif($category and $category['level'] > 1){
				$list = $category->getNode()->getSiblings();
				$type = self::SIBLINGS;
			}
		}
		return array($list , $type);
	}
	
	public function rebuildCategoryCache(){
		$cache	    = Rhema_Cache::getStatCache();
		$object 	= new Admin_Model_AffiliateProduct();
		$category   = $object->countProductsByCategory();
		$tree		= $this->buildTopLevelCategory($category);
		
		$cache->save($category, Admin_Model_AffiliateProduct::COUNT_STAT_KEY . 'category',  array('Admin_Model_AffiliateProduct',  'category'));
		$cache->save($tree, Admin_Model_AffiliateProduct::COUNT_STAT_KEY . 'categoryTree',  array('Admin_Model_AffiliateProduct',  'categoryTree'));
		
		return array(count($category), count($tree));
	}
}