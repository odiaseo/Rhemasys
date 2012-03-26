<?php 
/**
 * Retrieves offer detail from title
 * @author odiaseo
 *
 */
class Rhema_View_Helper_GetInfoboxText extends Zend_View_Helper_Abstract {  

	public function getInfoboxText($title, $default = 'Great Deal'){
		if(preg_match('/([^\s]+\s+)?(\d+%)\s+([^\s]+)(.*)/i', $title, $match)){
			//pd($match);
			$list = array('off', 'discount');
			if(trim($match[1]) and !in_array(strtolower($match[3]),$list )){
				$text = sprintf("<span class='top'>%s</span><h6 class='below'>%s</h6>", $match[1], $match[2]); // $match[2] . '<h4>' .$match[1] . '</h4><br />';
			}else{
				$text = sprintf("<h6 class='top'>%s</h6><span class='below'>%s</span>", $match[2], $match[3]); 
			}
			
		}else{
			$text = '<h4>' . $default . '</h4>' ;
		}
		return $text;
	}
}