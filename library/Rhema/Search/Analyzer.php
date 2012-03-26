<?php 

class Rhema_Search_Analyzer extends Doctrine_Search_Analyzer_Standard{

	public function analyze($text, $encoding = null)
    {
    	$list  = parent::analyze($text, $encoding);
    	return Rhema_Util_String::removeStopWords($list, true);
    }
}