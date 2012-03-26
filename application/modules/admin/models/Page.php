<?php

/**
 * Admin_Model_Page
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
class Admin_Model_Page extends Admin_Model_Base_Page{
	/**
	 *  Update page layout
	 *
	 */
	public static function updateLayout($page_id, $template_id, $itemAdd = array(), $sectionAdd = array()){

		$dateTime  			= date(DB_DATE_FORMAT, time());
		$pageModel          = __CLASS__;
		$page 			    = Doctrine_Core::getTable($pageModel)->find($page_id);
		$count				= 0;

		foreach($page->Layout as $index => $row){
			if($row['admin_template_id'] == $template_id){
				$eSxn = intVal($row['admin_section_id']);
				if(isset($itemAdd[$eSxn])){
					$contentType = $row['content_type'];
					if(isset($itemAdd[$eSxn][$contentType])){
						$itemId         = intVal($row['item']);
						if(isset($itemAdd[$eSxn][$contentType][$itemId])){
							$row['deleted_at']  	= null;
							$row['section_seq'] 	= $sectionAdd[$eSxn];
							$row['item_seq']    	= intVal($itemAdd[$eSxn][$contentType][$itemId]);
						}else{
							$row['deleted_at']       = $dateTime;
						}
						unset($itemAdd[$eSxn][$contentType][$itemId]);
 					}else{
 						$row['deleted_at'] = $dateTime;
 					}
				}else{
					$row['deleted_at'] = $dateTime;
				}
			}
			$count++;
		}


		if(count($itemAdd)){
			$i = 0;
		 	foreach($itemAdd as $aSexn => $res){
		 		foreach($res as $contentType => $itemData){
		 			foreach($itemData as $itemId => $itemSeq){
			 			$ind   = $i + $count;
				 		$page->Layout[$ind]['admin_template_id'] 	= $template_id;
				 		$page->Layout[$ind]['admin_section_id'] 	= $aSexn;
				 		$page->Layout[$ind]['section_seq']    		= $sectionAdd[$aSexn];
				 		$page->Layout[$ind]['item']					= $itemId;
				 		$page->Layout[$ind]['item_seq'] 			= $itemSeq;
				 		$page->Layout[$ind]['content_type'] 		= $contentType;
				 		$i++;
		 			}
		 		}
		 	}
		}

		$page->save();
		$page->free();
	}

	public static function getPageTemplate($page_id){
		$model = __CLASS__;
		$query = Doctrine_Query::create()
					->from("$model q")
					->where('q.id =?', $page_id)
					->select('q.template_id')
					//->useResultCache(true)
					->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
		$res   = $query->execute();
		return $res[0]['template_id'];
	}

	public function getPageDefinition($value, $key = Rhema_Constant::MENU_FRONTEND_KEY){
		try{
			$filter = new Rhema_Dao_Filter();
			$filter->setModel(__CLASS__)
				   ->addJoin('PageHeader', Rhema_Dao_Filter::LEFT_JOIN, array('id','template_id'))
				   ->addJoin('PageFooter', Rhema_Dao_Filter::LEFT_JOIN, array('id','template_id'))
				   ->setLimit(1)
			       ->addFields(array('title', 'description', 'meta_title', 'slug', 'template_id', 'keyword'))
				   ->addCondition($key, $value)
				   ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
			$query = Rhema_Model_Service::createQuery($filter);

			$data = $query->fetchOne();
		}catch(Exception $e){
			throw new Rhema_Model_Exception('Caught exception: ' . $e->getMessage());
		}
		//pd($data);
		return $data;
	}

	public function getPageById($id){
	    $filter = new Rhema_Dao_Filter();
	    $filter->setModel(__CLASS__)
	           ->addCondition('id', $id);
	    $page = Rhema_Model_Service::createQuery($filter)->fetchOne();

	    return $page;
	}

	public function updatePageMetaData($id, $data){
	    unset($data['id']);
	    $filter = new Rhema_Dao_Filter();
	    $filter->setQueryType(Rhema_Dao_Filter::QUERY_TYPE_UPDATE)
	           ->setModel(__CLASS__)
	           ->addCondition('id', $id)
	           ->setUpdateList($data);

	    return Rhema_Model_Service::createQuery($filter)->execute();
	}
}