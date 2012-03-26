<?php

class Rhema_View_Helper_ReplaceMergeField extends Zend_View_Helper_Abstract {

	public static $record = array();
	
	public function replaceMergeField($value){

	        $siteConfig    = Rhema_Util::getSessData(Rhema_Constant::SITE_CONFIG_KEY);
			$siteDetails   = $siteConfig['subsite'];
			$featuredThumb = '';
			$filter        = new Zend_Filter_Word_DashToSeparator(' ');
			$request       = Zend_Controller_Front::getInstance()->getRequest();


			if(isset($siteDetails['AddressBook']['id'])){
				$address = Rhema_Util_String::addressArrayToString($siteDetails['AddressBook']);
			}else{
				$address = '';
			}
			
			
/*			if(Rhema_SiteConfig::getConfig('settings.portfolio.show_featured')){
			    $porfolio      = Rhema_Model_Service::factory('portfolio');
			    $featuredItem  = $porfolio->getRandomFeaturedItem();

			    if($featuredItem){
			        $item = (object) $featuredItem;
			        $href = $this->view->url(array('album'=> $item->slug, 'useThumb' => 0), 'portfolio-album-preview');
			        $featuredThumb = "<a href='{$href}' title='{$item->title} - Featured Photobook Album'><img src='{$item->image_file}'
			        					alt='Featured Photobook' width='124' height='81' border='0' class='feature-thumb' /></a>";
			    }
			}*/

			$category    = $request->getParam('category', '');			
			$searchTitle = Zend_Registry::isRegistered(Rhema_Constant::SEARCH_TERM_TITLE) 
						   ? Zend_Registry::get(Rhema_Constant::SEARCH_TERM_TITLE)
						   : '';
			$keyword  = $request->getParam('keyword', $searchTitle);
			$category = preg_replace('/(search|location)/i', '', $category);
			$page     = $request->getParam('page');
			
			$data     = array(
				'[site-name]'         => $siteDetails['title'],
				'[site-email]'        => $siteDetails['contact_email'],
				'[site-address]'      => $address ,
				'[telephone]'		  => $siteDetails['telephone'],
			    '[featured-thumb]'    => $featuredThumb,
				'[page]'			  => $page ? $page : '',
				'[page-name]'		  => $this->view->layout()->pageData['title'],
				'[category]'		  => $category ? $filter->filter($category) : '',
			    '[letter]'		      => $request->getParam('letter'), 	
				'[keyword]'		      => $keyword ? $filter->filter($keyword) : '',
				'[search-title]'	  => $searchTitle,
			    '[year]'	          => date('Y')
			);
 
			$pattern = array_keys($data);
			$replace = array_values($data);

			$string  = str_replace($pattern, $replace, $value);
			$res     = preg_replace_callback('/\bsrc=([^\s\+]+)\b/i', array($this, "getAbsoluteUrl"), $string);
						
			return $res;
	}
	
	public function getAbsoluteUrl($match){		 
		if(substr($match[1], 0, 4 == 'http')){
			return 'src="' . $match[1] . '"';
		}
		$file   = str_replace(array("'", '"'), '', $match[1]);
		$util   = Rhema_Util_String::getInstance(); 

		$path     = Rhema_Constant::getSiteRoot() . '/' . ltrim($file, '/') ; 
		if(!file_exists($path)){
			$path     = Rhema_Constant::getPublicRoot() . '/' . ltrim($file, '/') ; 	 
		}
		
		$path = str_replace(array("'", '"', '//'), array('','','/'), str_replace(DIRECTORY_SEPARATOR, '/', $path));
		$res  = $util->directoryToUrl($path); //  str_replace(self::$pattern, self::$replace, $path); 
		//self::$record[$file] = $path . ' | ' . $res ;  
		//pd(self::$record);
		return 'src="' . $res . '"';
	}
}
