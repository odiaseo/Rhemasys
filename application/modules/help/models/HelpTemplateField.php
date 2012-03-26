<?php

/**
 * Help_Model_HelpTemplateField
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
class Help_Model_HelpTemplateField extends Help_Model_Base_HelpTemplateField
{
public static function getFields($template_id, $mode = Doctrine_Core::HYDRATE_ARRAY){
			$table = __CLASS__;
			$query = Doctrine_Query::create()
					->select('m.title, m.description, m.label, t.*')
					->from("$table t INDEXBY t.help_field_id")
					->where('t.help_template_id =? ', $template_id)
					->leftJoin('t.HelpField m')					
					->orderBy('m.label');
			if($mode){
				$query->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
			}
 
			return $query->execute();		
		}
		
		
		public static function getAllTemplateFields($template_id, $mode = Doctrine_Core::HYDRATE_ARRAY){
			$table = __CLASS__;
			$query = Doctrine_Query::create()
					->from("$table t")		
					->where('t.help_template_id =?', $template_id)	
					->andWhere('t.deleted_at IS NOT NULL OR t.deleted_at IS NULL');	
			if($mode){
				$query->setHydrationMode($mode);
			}
 
			return $query->execute();			
		}
		
		public function getTemplateFields($templateId){
			$table 	= __CLASS__;			
	    	$query  = Doctrine_Query::create()
	                ->select('tf.help_field_id,f.title')
					->from("$table tf")
					->where('tf.help_template_id =?', $templateId)
					->leftJoin('tf.HelpField f') 
					->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
			return $query->execute(); 
		}
}