<?php

/**
 * Admin_Model_AdminSubsiteLicence
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
class Admin_Model_AdminSubsiteLicence extends Admin_Model_Base_AdminSubsiteLicence{

	public static function getSiteModules($mode = Doctrine_Core::HYDRATE_ARRAY){
		$model 				= __CLASS__ ;
		$query = Doctrine_Query::create()
					->from("$model m")
					->innerJoin('m.AdminLicence l')
					->leftJoin('l.AdminModule g')
					->leftJoin('g.AdminMenu x')
					->andWhere('m.is_active =?', 1)
					////->useResultCache(true)
					->orderBy('m.sequence');
		if($mode){
			$query->setHyDrationMode($mode);
		}

		return $query->execute();
	}

    public static function saveSiteLicence($params, $ssid){
 		if($ssid){
			$table		= __CLASS__ ;
			$filter     = new Rhema_Dao_Filter();
			$dateTime   = new Doctrine_Expression('NOW()');
			
			$filter->setModel($table)
				   ->setBypassSoftDelete(true)
				   ->setHydrationMode(Doctrine_Core::HYDRATE_RECORD)
				   ->addCondition('admin_subsite_id', $ssid);
				   
			$record		= Rhema_Model_Service::createQuery($filter)->execute();

			$items 	    = $params['licence_id'];
			$toAdd 		= array_flip($items);
			$count 		= 0;

			foreach($record as $existField){
				$fId = $existField['admin_licence_id'];
				if(isset($toAdd[$fId])){
					$existField['deleted_at'] = null;
					$existField['is_active']  = 1;
				}else{
					$existField['deleted_at'] = $dateTime ;//date('Y-m-d H:i:s', time());
				}
				unset($toAdd[$fId]);
				$count++;
			}
			$record->save();
			$record->free();

			if(count($toAdd)){
			 	foreach($toAdd as $licenceId => $sequence){
			 		$obj = new $table();
			 		$obj->admin_licence_id  = $licenceId;
			 		$obj->is_active         = 1;
			 		$obj->admin_subsite_id  = $ssid;
			 		$obj->save();
			 		$obj->free();
			 	}
			}
			$tags  = array( MODEL_PREFIX . 'AdminSubsite',
							MODEL_PREFIX . 'AdminLicence',
							MODEL_PREFIX . 'AdminModule',
							__CLASS__
			);
			
			$numToAdd  = count($toAdd);
			$s         = Rhema_Util_String::pluralise('record', $count);
			$s1        = Rhema_Util_String::pluralise('record', $numToAdd);
			
			$message   = "$count $s updated, " . $numToAdd . ' ' . $s1 . ' created.';			
			$type      = Rhema_Dto_UserMessageDto::TYPE_SUCCESS ;
			
			Rhema_Cache::clearCacheOnUpdate($table, $tags); 
 		}else{
 			$message = "Site #id={$ssid} not found";
 			$type    = Rhema_Dto_UserMessageDto::TYPE_ERROR;
 		}

 		$return['message'] = $message;
 		$return['type'] = $type ;
 		
 		return $return;

    }
}