<?php /* This file encoded by Raizlabs PHP Obfuscator http://www.raizlabs.com/software */ ?>
<?php

/**
 * Help_Model_Template
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
class Help_Model_Template extends Help_Model_Base_Template{
 
 		public static function getLayout($template_id, &$obj=null){
 			$currentLayout = array();
 			$className     = __CLASS__;
 			if($template_id){
 				$query = Doctrine_Query::create()
 						->select('t.*')
 						->from("$className t")
 						->where('t.id =?', $template_id)
 						->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
 				$row           = $query->execute();
 				$obj           = $row[0];
				$layoutStr     = $obj['layout'];
			

				if($layoutStr){
					$currentLayout = Zend_Json::decode($layoutStr);
				}
 			}
 			return $currentLayout;
 		}
 		
 		public static function getTemplateFields($template_id, $mode = Doctrine_Core::HYDRATE_ARRAY){ 
	 		$table = __CLASS__ ;
			$query = Doctrine_Query::create()	
						->from("$table t")
						->leftJoin('t.BoilerPlate b')
						->leftJoin('t.Field f')
						->where('t.id =?', $template_id);
			if($mode){
				$query->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
			}
	 		return $query->execute();				
 		}
 		
  		public static function getFieldBoiler($template_id, $mode = Doctrine_Core::HYDRATE_ARRAY){ 
	 		$table = __CLASS__ ;
			$query = Doctrine_Query::create()	
						->from("$table t")
						->leftJoin('t.BoilerPlate b')
						->leftJoin('t.TemplateFields q')
						->where('t.id =?', $template_id);
			if($mode){
				$query->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
			}
	 		return $query->execute();				
 		}
	
}