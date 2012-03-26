<?php
/**
 *
 * @author Pele
 * @version 
 */
require_once 'Zend/View/Interface.php';

/**
 * GoogleMap helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_GoogleMap extends Zend_View_Helper_Abstract {
 
	
	/**
	 * Generates a google static map using the passed address and
	 * google map API
	 */
	const API_URL       = 'http://maps.google.com/maps/api/staticmap';
	const LARGE_MAP_URL = 'http://maps.google.com/maps';
	const FORMAT_PNG    = 'png32';
	
	public function googleMap($param = array()) {
		$html     = '';
		$lang     = Zend_Registry::get('Zend_Locale')->getLanguage();
		$config   = Rhema_SiteConfig::getConfig('settings.staticmap');
		$mapParam = array(
			'center' 	=> '',
			'size'   	=> $config['size'],
			'sensor' 	=> $config['sensor'],
			'zoom'   	=> $config['zoom'],
		    'format'	=> self::FORMAT_PNG,
		    'language'	=> $lang,
			'markers'   => ''
		);
		
		list($width, $height)   = explode('x', $mapParam['size']);		
		$mapParam    			= array_merge($mapParam, $param); 
		
		if(isset($param['markers'])){
			foreach($param['markers'] as $label => $point){
				 $markers[] = "size:mid|color:0xFFFF00|label:{$label}|" . $point;
			}
			$mapParam['markers']  	= implode('&amp;markers=', $markers);
		}
				
		
		$queryString  = http_build_query($mapParam, null, '&amp;');
		$src          = self::API_URL . '?' . $queryString;
		$href		  = self::LARGE_MAP_URL . "?f=q&amp;source=s_q&amp;hl={$lang}&amp;geocode=&amp;q=" . $mapParam['center'] . '&amp;z=18';
		
		$html .= "<div id='map_canvas'>
			       <iframe frameborder='0' height='{$height}' marginheight='0' marginwidth='0' scrolling='no' src='{$src}'  width='{$width}'></iframe>
			       <div class='link'><a href='$href' title='click here for larger map' target='_blank'>View larger map</a></div>
	              </div>
	              ";
		
		
		
		return $html;
	}
 
}
