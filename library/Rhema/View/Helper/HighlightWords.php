<?php
 
/**
 * Hightlight words in text
 * @author odiaseo
 *
 */
class Rhema_View_Helper_HighlightWords extends Zend_View_Helper_Abstract {
	
	public function highlightWords($text, $terms){
		$list 	 = array_unique(array_filter(explode(' ', $terms)));
		$pattern = '/(' . implode('|', $list) . ')/i';
		return preg_replace_callback($pattern , array($this, 'highlight'), $text);
	}
	
	public function highlight($match){
		$term = $match[1];
		return "<span class='match'>$term</span>";
	}
}