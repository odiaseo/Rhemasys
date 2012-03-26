<?php

/**
 * Admin_Model_AdminElement
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
class Admin_Model_AdminElement extends Admin_Model_Base_AdminElement {

	public static function getElement($id){
		
		/*$res = self::getRemoteData(__FUNCTION__, array(__CLASS__, $id));
		return $res;
		*/	
	//	if(Rhema_Util::isHomeDomain()){
  			$server = Rhema_Model_Service::factory('Admin_Service_Server', false); 
			$return = $server->getElement(__CLASS__, $id);
	/*	}else{
			try{
				$client 	= Rhema_Util::getRemoteClient();
				$resp   	= $client->getElement(__CLASS__, $id)->get();
				$return     = self::getRestResult($resp, __FUNCTION__);
			}catch(Exception $e){
  				$server = new Admin_Service_Server();
				$return = $server->getElement(__CLASS__, $id);				
			}
		}
*/		
		return $return ;
	}


	public static function getAllElements($model = null){
			
	/*	$res = self::getRemoteData(__FUNCTION__, array(__CLASS__));
		return $res;
		*/
		//if(Rhema_Util::isHomeDomain()){
  			$server = Rhema_Model_Service::factory('Admin_Service_Server', false); 
			$return = $server->getAllElements(__CLASS__);
/*		}else{
			try{
				$client 	= Rhema_Util::getRemoteClient();
				$resp   	= $client->getAllElements(__CLASS__)->get();
				$return     = self::getRestResult($resp, __FUNCTION__);
			}catch(Exception $e){
  				$server = new Admin_Service_Server();
				$return = $server->getAllElements(__CLASS__);				
			}
		}*/
		return $return ;
	}
}