<?php /* This file encoded by Raizlabs PHP Obfuscator http://www.raizlabs.com/software */ ?>
<?php

/**
 * Help_Model_Section
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
class Help_Model_Section extends Help_Model_Base_Section
{
	public static function getSections($mode = Doctrine_Core::HYDRATE_ARRAY){
		$model  = __CLASS__ ;
		$query = Doctrine_Query::create()
					->from("$model t INDEXBY t.id")
					->orderBy('t.sequence');
		if($mode){
			$query->setHydrationMode($mode);
		}
		return $query->execute();
	}
}