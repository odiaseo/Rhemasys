<?php

/**
 * Admin_Model_Component
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
class Admin_Model_Component extends Admin_Model_Base_Component{

	public static function getItem($id){
		$model = __CLASS__;
		$comp  = Doctrine_Core::getTable($model)->find($id, Doctrine_Core::HYDRATE_ARRAY);
		return $comp;
	}

	public static function getComponents(){
		$model = __CLASS__ ;
		$query		= Doctrine_Query::create()
						->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
						->orderBy('title')
						->from("$model t INDEXBY t.id");
		return $query->execute();
	}

	public static function updateContent($id,$content){
		$model = __CLASS__;
		$query = Doctrine_Query::create()
					->update("$model")
					->set('content','?', $content) 
					->where("id = $id");
		$done 	=  $query->execute();
    	$tags   = array($model, 'component_id');
    	self::clearRelatedCacheFiles($tags);
		return $done	;
	}
}