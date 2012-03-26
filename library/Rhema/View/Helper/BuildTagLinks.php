<?php 
class Rhema_View_Helper_BuildTagLinks extends Zend_View_Helper_Abstract {
 
	public function buildTagLinks($str){
		$list = array();
		$filter = new Rhema_Search_Analyzer();
		$str  = html_entity_decode($str);
		$str  = preg_replace('/[^a-z0-9_\-\.\%]+/i', ' ' , $str);
		$str  = str_replace('-', ' ', $str);
		$arr  = $filter->analyze(strtolower($str)) ;
		
		$arr  = array_unique(array_filter($arr));
		asort($arr);
		
		foreach($arr as $word){
			//if(strlen($word) > 1 and !in_array($word, $this->_ignoreList) and !is_numeric($word)){
				$word   = preg_replace('/[^a-z0-9_\-\%]+/i', ' ' , $word);
				$tag    = strtolower(trim($word));;
				$link   = $this->view->url(array('keyword' => $tag), 'deal-search', true);
				$list[] = "<a href='{$link}' title='search {$tag}' class='tag-item'>{$tag}</a>";
			//}
		}
		
		return implode(' ', $list); ;
	}
}