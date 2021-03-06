<?php

/**
 * Admin_Model_EcomDisplayTemplate
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
class Admin_Model_EcomDisplayTemplate extends Admin_Model_Base_EcomDisplayTemplate{ 
	
	public static function getDefaultTemplate($displayType){	
		$model   = __CLASS__;	
		$query   = Doctrine_Query::create()
					->select('p.id')
					->from("$model p")
					->where("p.is_default =?", 1) 
					->andWhere('p.ecom_display_type_id =?', $displayType)
					->limit(1)
					->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
		$res =  $query->execute();
		return isset($res[0]) ? $res[0]['id'] : null;
	}

	
}