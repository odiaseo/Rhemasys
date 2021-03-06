<?php

/**
 * Admin_Model_EcomProduct
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
class Admin_Model_EcomProduct extends Admin_Model_Base_EcomProduct{

	public function getProductById($id){ 
		$filter = new Rhema_Dao_Filter();
		$filter->setModel(__CLASS__)
			   ->addCondition('id', $id);
			   
		return Rhema_Model_Service::createQuery($filter)->fetchOne(); 
	}
	
	public function listVirtualProducts(){
		$filter = new Rhema_Dao_Filter();
		$filter->setModel(__CLASS__)
			   ->addCondition('is_virtual', 1);
			   
		return Rhema_Model_Service::createQuery($filter)->execute();
	}
 
}