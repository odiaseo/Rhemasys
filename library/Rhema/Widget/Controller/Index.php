<?php

	class Rhema_Widget_Controller_Index extends Rhema_Widget_Abstract{

		public function indexMethod(){
		}

		public function countryListMethod(){
			return array();
		}

		public function bannerMethod(){
	    	//$output     = $this->view->render('index/banner.phtml');
	    	//return  $output;
		}

		public function featureMethod(){
			//$feat       			= $this->_utility->getCached()->getFeaturedItems();
			//$this->view->keys       = array(0,1,2,3); //array_rand($feat, 4);
			//$this->view->featured 	= $feat;
	    	//$output     			= $this->view->render('index/feature.phtml');
	    	//return  $output;
		}

		public function mediaMethod(){
			$config = Rhema_Util::getSessData(Rhema_Constant::SITE_CONFIG_KEY);
			$return['siteDetails'] = $config['subsite'];
			
			return $return;
		}

		public function resultMethod(){

		}

		public function latestnewsMethod(){
			$number 		  = Rhema_SiteConfig::getConfig('settings.events_to_show');
			$return['news']   = Admin_Model_News::getLatestNews($number);	
			$return['icon']   = Rhema_SiteConfig::getConfig('images.icon.news');		
			return $return;
		}
		
		public function latestEventsMethod(){
			$number 		  = Rhema_SiteConfig::getConfig('settings.events_to_show');
			$return['events'] = Admin_Model_Event::getLatestEvents($number);	
			$return['icon']   = Rhema_SiteConfig::getConfig('images.icon.event');		
			return $return;			
		}
		
		public function socialise(){
			$return = array();
			return $return;
		}

 
	}