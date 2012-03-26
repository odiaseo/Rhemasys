<?php

class Rhema_View_Helper_QuickMenu extends Zend_View_Helper_Abstract{

	public function QuickMenu(){
		$menus = Rhema_SiteConfig::getConfig('quick_menus');
		$user  = Zend_Auth::getInstance()->getIdentity();
		//$menus['current_user']['title'] = $user['firstname'] . ' ' . $user['lastname'];
		$html = '<ul>';

		foreach(array_reverse($menus) as $id => $item){
			$item  = (object) $item;
			$href  = $item->route ? $this->view->url(array(),$item->route) : '#';
			if('clear-cache' == $item->route){
				$opt   = $this->buildCacheOptions();
				$html .= "<li><a href='#' title='$item->title' class='quick_menu_item' alt='$item->ajax'><ins id='$id'></ins></a>{$opt}</li>";
				
			}elseif('current_user' != $id){
				$html .= "<li><a class='quick_menu_item' href='{$href}'alt='$item->ajax' title='$item->title'>
						<ins id='$id'></ins></a></li>";
			}
		}
		$html .= '<li id="current-user">' .  sprintf("%s %s (%d visits)", $user['firstname'], $user['lastname'], $user['visits']) . '</li>';
		$html .= '</ul>';
		
		return $html;
	}
	
	public function buildCacheOptions(){
		
		$html  = "<ul class='rounded ui-widget-content ui-corner-all'>";
		$html .= "<li><a href='%s' alt='1'>Configuration cache</a></li>";
		$html .= "<li><a href='%s' alt='1'>Logs</a></li>";
		$html .= "<li><a href='%s' alt='1'>Translations Files (Tmx)</a></li>";
		$html .= "<li><a href='%s' alt='1'>Pages</a></li>";
		$html .= "<li><a href='%s' alt='1'>Backend cache</a></li>";
		$html .= "<li><a href='%s' alt='1'>Frontend cache</a></li>";
		 
		$html .= "<li><a href='%s' alt='1'>Frontend Css/Js Files</a></li>";
		$html .= "<li><a href='%s' alt='1'>Backend Css/Js Files</a></li>";
		$html .= "<li><a href='%s' alt='1'>Database Cache</a></li>";
		$html .="</ul>";
				
		return sprintf($html, $this->view->url(array('type' => 'config'), 'clear-cache'),
							  $this->view->url(array('type' => 'logs'), 'clear-cache'),
							  $this->view->url(array('type' => 'tmx'), 'clear-cache'),
							  $this->view->url(array('type' => 'page'), 'clear-cache'),
							  $this->view->url(array('type' => 'backend-dynamic'), 'clear-cache'),
							  $this->view->url(array('type' => 'frontend-dynamic'), 'clear-cache'),
							  $this->view->url(array('type' => 'frontend-static'), 'clear-cache'),
							  $this->view->url(array('type' => 'backend-static'), 'clear-cache'),
							  $this->view->url(array('type' => 'db'), 'clear-cache')
				
				);
	}


}