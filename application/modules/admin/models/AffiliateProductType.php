<?php

/**
 * Admin_Model_AffiliateProductType
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Admin_Model_AffiliateProductType extends Admin_Model_Base_AffiliateProductType
{
	const TYPE_PRODUCT = 1 ;
	const TYPE_VOUCHER = 2 ;
	const TYPE_DEAL    = 3 ;
	
	public static function getTypeList(){		
    	$daoFilter = new Rhema_Dao_Filter();
    	$daoFilter->setModel(__CLASS__) ;
    	return Rhema_Model_Service::createQuery($daoFilter)->execute() ;
	}
}