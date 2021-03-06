<?php

/**
 * Admin_Model_Template
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
class Admin_Model_Template extends Admin_Model_Base_Template
{

	public static function getHeader(){

	}

	public static function getFooter(){

	}
	
	public function getActiveSections($templateId, $mode = null){
    	$operator = (is_array($templateId)) ? Rhema_Dao_Filter::OP_IN : Rhema_Dao_Filter::OP_EQ;
    	$filter = new Rhema_Dao_Filter();
    	$filter->setModel(__CLASS__)
    		   ->addJoin('TemplateSections', Rhema_Dao_Filter::INNER_JOIN, array('sequence'))	
    		   ->addJoin('TemplateSections.AdminSection', Rhema_Dao_Filter::INNER_JOIN)    		   
    		   ->setOrderBy('TemplateSections.sequence') 
    		   ->addField('title')
    		   //->setDebug()
    		   //->addCondition('TemplateSections.deleted_at', '', Rhema_Dao_Filter::OP_IS_NULL)
    		   ->addCondition('id', $templateId, $operator);
    	if($mode){
    		$filter->setHydrationMode($mode);
    	}    

    	$query = Rhema_Model_Service::createQuery($filter);
    	$result = $query->execute(); 

    	return $result;		
	}
	
	public function getTemplateById($templateId, $mode = Doctrine_Core::HYDRATE_ARRAY){
		return Doctrine_Core::getTable(__CLASS__)->find($templateId, $mode);	
	}
/*	
	public static function saveSection($params){
		$templateId	 = isset($params['template_id']) ? $params['template_id'] : null;

 		if($templateId){
			$table		  = __CLASS__ ;
			$modelFilter  = new Rhema_Filter_FormatModelName();
			$tempSxnTable = $modelFilter->filter('template_section');
			$record		  = Doctrine_Core::getTable($table)->find($templateId);
			$sections 	  = $params['section_id'];
			$toAdd 		  = array_flip($sections);
			$count 		  = 0;

			$currentSections = $record->TemplateSections ;
			
			$filter  = new Rhema_Dao_Filter();
			$filter->setQueryType(Rhema_Dao_Filter::QUERY_TYPE_DELETE)
				   ->setModel($tempSxnTable) 
				   ->setDebug() 
				   ->addCondition('template_id', $templateId)
				   ->addCondition('admin_section_id', $sections, Rhema_Dao_Filter::OP_NOT_IN);
				   
			$updQuery   = Rhema_Model_Service::createQuery($filter);
			$deleted    = $updQuery->execute();
			 
			foreach($record->TemplateSections as $existField){
				$fId = $existField['admin_section_id'];
				if(isset($toAdd[$fId])){
					$existField['deleted_at'] = null;
					$existField['sequence']	  = $toAdd[$fId];
				}else{
					$existField['deleted_at'] = date('Y-m-d H:i:s', time());
				}
				unset($toAdd[$fId]);
				$count++;
			}
			$record->save();
			$record->free(true);

			if(count($toAdd)){
			 	foreach($toAdd as $sectionId => $sequence){
			 		$obj = new Admin_Model_TemplateSection();
			 		$obj->admin_section_id  = $sectionId;
			 		$obj->sequence          = $sequence;
			 		$obj->template_id = $templateId;
			 		$obj->save();
			 		$obj->free();
			 	}
			}

			$tags    = array($table);
			self::clearRelatedCacheFiles($tags);

			$message = "$count records updated, " . count($toAdd) . ' records created.';
 		}else{
 			$message = 'Template not found';
 		}

 		return $message;
	}*/
}