<?php

/**
 * Admin_Model_User
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
class Admin_Model_User extends Admin_Model_Base_User
{
	public function __toString(){  
		return $this->firstname . ' ' . $this->lastname;
	}
}