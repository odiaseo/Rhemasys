<?php 

class Rhema_View_Helper_LimitTextDisplayed extends Zend_View_Helper_Abstract {
	
	public function limitTextDisplayed($text, $limit = 400, $moreText = ''){
		
		if(stristr($text, '</p>')){
			$text  = str_replace(array('<p>?</p>', '<p></p>'), '', $text);			
		}
		$return = $text ; 
		$mainStr = '';
			
		if(strlen($text) > ($limit + 100)){
			$parts = explode(' ', $text);
			do{
				$mainStr .= ' ' . array_shift($parts);
			}while(strlen($mainStr) < $limit);
			
			$return = sprintf("%s <a href='#' class='toggle-more'>more info >></a> <span style='display:none'>%s %s
			 <a href='#' class='toggle-less'><< hide info</a></span>", $mainStr, implode(' ', $parts), $moreText); 
		} 

		return $return ;
	}
}