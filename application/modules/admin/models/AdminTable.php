<?php

/**
 * Admin_Model_AdminTable
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
class Admin_Model_AdminTable extends Admin_Model_Base_AdminTable {

		public static function listDatabaseTables($mode = Doctrine::HYDRATE_ARRAY, $value = 'name', $option = 'title'){
			$arr      = array();
			$list     = array();

			$table    = __CLASS__ ;
			
			$filter   = new Rhema_Dao_Filter();
			$filter ->addJoin('AdminCategory', Rhema_Dao_Filter::LEFT_JOIN, array('title'))
					->addOrderBy($option)
					->setModel(__CLASS__)
					//->setDebug(true)
					->addFields(array($value, $option));
			if($mode){ 
				$filter->setHydrationMode($mode);
			} 
 	
			$query  = Rhema_Model_Service::createQuery($filter);					
			$tables = $query->execute();		  

			foreach($tables as $id => $data){
			 	$category = isset($data['AdminCategory']['title']) 
			 				? $data['AdminCategory']['title'] 
			 				: 'Miscellaneous';

				$key      = $data[$value];
				$display  = $data[$option];

				$arr[$category][$key] 	= $display; 
				$list[$key]['title'] 	= $display;
				$list[$key]['id'] 		= $data['id'];
			}

			$result['bycat']   = $arr;
			$result['bymodel'] = $list;
			$result['raw']     = $tables;

			return $result;
		}
		
		public function getTableTitles(){ 
			$labels = array();
			$filter = new Rhema_Dao_Filter();
			$filter->addAttribute(array('title', 'name'))
				   ->setModel(__CLASS__)
				   ->setIndexBy('title');
			
			$result = $this->_setFilters($filter)
						   ->fetchArray();
						   
			foreach($result as $field => $data){
				$labels[$data['name']] = $data['title'];
			}
			
			return $labels;
		}
		
	public function getIdFromTableName($sourceModel){
		$key = 'table-by-id';
		$util = Rhema_Util::getInstance();
		$byId = $util->getSessData($key);
		if(! ($byId and isset($byId[$sourceModel]) and $byId[$sourceModel])){
			$list = $this->getCached()->listDatabaseTables();
			$sourceId = isset($list['bymodel'][$sourceModel]) ? $list['bymodel'][$sourceModel]['id'] : null;
			$byId[$sourceModel] = $sourceId;
			$util->setSessData($key, $byId);
		}else{
			$sourceId = $byId[$sourceModel];
		}
		return $sourceId;
	}
}