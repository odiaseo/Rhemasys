<?php 
class Rhema_View_Helper_DisplayAlphaList extends Zend_View_Helper_Abstract {
	
	public function displayAlphaList($routePrefix, $current = null, $id = 'alpha-list'){
		$route   = $routePrefix . '-alpha-list';
		$list    = (array) Admin_Model_AffiliateProduct::getProductStatList($routePrefix . 'Count');
		$start   = ord('a');
		$end     = ord('z');
 
		printf("<div id='%s' class='clearfix'><ul>", $id);
 
		for($i=$start; $i<= $end; $i++){
			$alpha = chr($i);
			$class = ($alpha == $current) ? ' active' : '';
			$href  = $this->view->url(array('letter' => $alpha), $route);
			$num   = isset($list[$alpha]) ? $list[$alpha] : 0;
			
			if($num){
				printf("<li class='ui-state-default alpha rounded%s'><a href='%s' title='%s available'>%s</a></li>", $class, $href, $num, strtoupper($alpha));
			}else{
				printf("<li class='ui-state-default no-item alpha rounded%s'><a href='%s'>%s</a></li>", $class, $href, strtoupper($alpha));
			}
		}
					
		$class = ('0-9' == $current) ? ' active' : '';
		$href  = $this->view->url(array('letter' => '0-9'), $route);
		$num   = isset($list['numbers']) ? $list['numbers'] : 0;
		if($num){
			printf("<li class='ui-state-default rounded%s'><a href='%s' title='%s' available'>0-9</a></li>", $class, $href, $num);
		}		
		
		print('</ul></div>');		
	}
}