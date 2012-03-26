<?php

/**
 * Admin_Model_EventType
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
class Admin_Model_EventType extends Admin_Model_Base_EventType
{
	public function listEventTypesWithAlbumCount(){
		$daoFilter = new Rhema_Dao_Filter();
		
		$daoFilter->addOrderBy('sort_order')
				  ->setModel(__CLASS__)
				  ->addFields(array('title','description','slug'))
				  ->addJoin('Event', Rhema_Dao_Filter::INNER_JOIN, array('title'))
				  ->addGroupBy('id')
				  ->addOrderBy('title')
				  // ->setDebug()
				  ->addAggregateFieldList('Event.id', 'COUNT') ;
				  
		$list   = Rhema_Model_Service::createQuery($daoFilter)->execute();	
		// pd($list);
		return $list;	
	}
 
}