<?php

/**
 * Help_Model_HelpField
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
class Help_Model_HelpField extends Help_Model_Base_HelpField
{
	
		public static function getMandatoryColumns($index = 'result'){
	    	$key   		= 'mandatory-columns-' . $index;
	    	$return     = Rhema_Util::getSessData($key);	    	
	    	
	    	if(!$return or 'development' == APPLICATION_ENV){
	    		$return     = array();
	    		$table      = __CLASS__ ;
		    	$query      = Doctrine_Query::create()
		    				->select('t.id, t.title, t.label')
		    				->from("$table  t INDEXBY t.id")
		    				->where('t.is_mandatory =?', 1)
		    				->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
		    	$result = $query->execute();
		    	
		    	foreach($result as $x => $arr){
		    		$save['title'][$arr['id']]    = $arr['title']; 
		    		$save['id'][]                 = $arr['id'];		    		
		    	}
		    	
		    	$save['result']         = $result;
		    	if(isset($save['id'])){
		    		Rhema_Util::setSessData('mandatory-columns-id',     $save['id']);
		    		Rhema_Util::setSessData('mandatory-columns-title',  $save['title']);
		    	}
		    	Rhema_Util::setSessData('mandatory-columns-result', $save['result']);
		    	
		    	$return = $save[$index];
	    	}
	    	
	    	return $return;
		}
		
		public static function listSearchableFields(){
    		$table      = __CLASS__ ;
	    	$query      = Doctrine_Query::create()
	    				->select('t.id, t.title, t.label')
	    				->from("$table t INDEXBY t.id")
	    				->where('t.is_searchable =?', 1)
	    				->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
	    	return $query->execute();			
		}
	public static function listAllFields($mode = Doctrine_Core::HYDRATE_ARRAY){
		$table = (__CLASS__) ;
		$query    = Doctrine_Query::create()->from("$table t INDEXBY t.id"); 
		if($mode){
			$query->setHydrationMode($mode);
		}								 
		return $query->execute();
	}
}