<?php

/**
 * Admin_Model_AdminLicence
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
class Admin_Model_AdminLicence extends Admin_Model_Base_AdminLicence{

    public static function getSiteLicences($ssid){
    	$res = self::getRemoteData(__FUNCTION__, array(__CLASS__, $ssid));
		return $res;
		
/*    	if(Rhema_Util::isHomeDomain()){
    		$server = Rhema_Model_Service::factory('Admin_Service_Server', false); 
			return $server->getSiteLicences(__CLASS__, $ssid);
    	}else{
  			$client 	= Rhema_Util::getRemoteClient();
			$resp   	= $client->getSiteLicences(__CLASS__, $ssid)->get();
			return self::getRestResult($resp, __FUNCTION__);
    	}*/
    }

}