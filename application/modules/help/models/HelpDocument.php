<?php

/**
 * Help_Model_HelpDocument
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
class Help_Model_HelpDocument extends Help_Model_Base_HelpDocument
{
	public function findHelp($id){
		$model = __CLASS__ ;
		$query = Doctrine_Query::create()
				 ->select('h.content')
				 ->from("$model h")
				 ->where('h.id =?', $id)
				 ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
		return $query->execute();
	}
}