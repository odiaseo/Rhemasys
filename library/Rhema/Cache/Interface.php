<?php
	interface  Rhema_Cache_Interface{
		public function setCache(Rhema_Cache_Abstract $cache) ;		
		public function setCacheOptions(array $options) ;		
		public function getCacheOptions() ;		
		public function getCached($tagged = null) ;
	}