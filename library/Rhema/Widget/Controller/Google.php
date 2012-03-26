<?php

	class Rhema_Widget_Controller_Google extends Rhema_Widget_Abstract{

		public function mapMethod(){
			$script = "http://www.google.com/jsapi?key=" . $this->_apikey;
			$view   = Zend_Layout::getMvcInstance()->getView();
			//$view->headScript()->appendFile($script) ->appendScript('google.load("maps", "3");');
		}

		public function adsenseMethod(){
			$return['pubId']     = Rhema_SiteConfig::getConfig('settings.google_publisher_id');
			$return['channelId'] = Rhema_SiteConfig::getConfig('settings.google_adsense_channel');
			return $return;
		}
			
		public function adsenseSidebarMethod(){
			$return['pubId']     = Rhema_SiteConfig::getConfig('settings.google_publisher_id');
			$return['channelId'] = Rhema_SiteConfig::getConfig('settings.google_adsense_channel');
			return $return;
		}		
		
		public function staticmapMethod(){
			$mapData   = array();
			$config    = Rhema_Util::getSessData(Rhema_Constant::SITE_CONFIG_KEY);
			
			if(array_key_exists('AddressBook', $config['subsite']) and !empty($config['subsite']['AddressBook'])){
				$mapData['markers']   = array();
				$addressData          = $config['subsite']['AddressBook'];				 
				$center               = urlencode(Rhema_Util_String::addressArrayToString($addressData));				 
				$mapData['center']    = $center;
				$mapData['markers'][] = $center;				
			}
			
			$return['mapData'] = $mapData;
			
			return $return;			
		}
		
		public function buildHtml($address){
			$html = '
			<!DOCTYPE html>
				<html>
				<head>
				<meta name="viewport" content="initial-scale=1.0, user-scalable=yes" />
				<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
				<style type="text/css">
				  html { height: 100% }
				  body { height: 100%; margin: 0px; padding: 0px }
				  #map_canvas { height: 100% }
				</style>
				<title>Google Maps JavaScript API v3 Example: Map Simple</title>
				<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
				<script type="text/javascript">

					function initialize() {
					    var myLatlng = new google.maps.LatLng(50.5283648, -0.1282415);
					    var myOptions = {
					      zoom: 8,
					      center: myLatlng,
					      mapTypeId: google.maps.MapTypeId.ROADMAP
					    }
					    var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
					  }

					function codeAddress() {
					    var geocoder = new google.maps.Geocoder();
					    var address = "Novotel, london st pancras,100-110 Euston Road NW1 2AJ";
					    geocoder.geocode( { "address": address}, function(results, status) {
					      if (status == google.maps.GeocoderStatus.OK) {
					        //map.setCenter(results[0].geometry.location);
					        var marker =  results;
					      } else {
					        alert("Geocode was not successful for the following reason: " + status);
					      }
					    });
					  }
				</script>
				</head>
				<body onload="initialize()">
				  <div id="map_canvas"></div>

				</body>
				</html>';

			return $html;
		}
 
	}