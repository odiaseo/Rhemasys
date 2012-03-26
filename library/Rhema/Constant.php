<?php

define('AJAX_NAMESPACE', 'Rhema-Ajax');
define('CONTEXT_ADMIN', 'admin');
define('CONTEXT_SITE', 'site');
define('DEFAULT_MODULE', 'storefront');
define('SESS_NAMESPACE', 'rhemasys');
define('DB_DATE_FORMAT', 'Y-m-d H:i:s');
define('FRONT_MENU_ROUTE', 'site-default-route'); 'storefront-menu';
define('ADMIN_ROUTE', 'backend-menu');
define('HELP_ROUTE', 'tooltip');

define('NAV_ROUTE', 'product-nav');
define('MENU_PAGE_MAP', 'slug');

define('BRANCH_ROUTE', 'branch-category-navigation');
define('PRODUCT_ROUTE', 'product-detail');
define('BLOG_ROUTE', 'blog-post');
define('CATEGORY_ROUTE', 'branch-category-navigation');
define('CATEGORY_MAP', 'slug');
define('WEB_SETTING_INDEX', 'web-setting');
define('TEMP_ARRAY', 'template-array');
define('ADMIN_URL_CODE', 'master');

define('ADMIN_SITE_DIR', 'sites');
define('WIDGET_SEP', '~');

define('REST_SERVER', 'http://www.affiliate-marketing-platform.com/rest/index');
define('RHEMASYS_HOME', 'affiliate-marketing-platform.com');
define('PHOTOBOOK_DIR' ,  'media/portfolio/photobook');
define('AUDIO_DIR' ,  SITE_DIR . '/media/audio/');
//=================== Nodes =============================
define('NODE_GRID', 'grid');

//================== Prefixes=============================
define('ADMIN_PREFIX', 'Admin_Model_');
define('MODEL_PREFIX', ADMIN_PREFIX);
define('HELP_PREFIX', ADMIN_PREFIX);
define('ECOM_PREFIX', ADMIN_PREFIX);
define('BLOG_PREFIX', ADMIN_PREFIX);
define('HEADFILE_PREFIX', 'rms-file-');

define('PUBLIC_KEY', '6LdFeLsSAAAAAF0VKct0PtgJ0b0k6u-tEPdWT_jp');
define('PRIVATE_KEY', '6LdFeLsSAAAAAAwW90OJw1qL5CcjBnFZlREDelZK');

//================== Suffixes ============================


//================== Paths ==============================
define('SCRIPT_PATH', '/../backend/scripts');
define('CSS_PATH', '/../backend/css');
define('ACL_RULE', APPLICATION_PATH . '/configs/acl_rule.php');
define('WIDGET_PATH', realpath(APPLICATION_PATH . '/../library/Rhema/Widget'));

//================= DB Table patterms ======================

define('REGEX_TABLE_ADMIN', '/^' . MODEL_PREFIX . '(?=Admin)[a-z]+$/i');
define('REGEX_TABLE_SITE', '/^' . MODEL_PREFIX . '(?!Admin)[a-z]+$/i');
define('REGEX_TABLE_PAGE', '/^' . MODEL_PREFIX . '(?=Page_)[a-z]+$/i');
define('REGEX_TABLE_ALL', '/^(' . MODEL_PREFIX . ')[a-z]+$/i');

final class Rhema_Constant{
	const SITE_THEME_KEY   = 'site-theme';
	const SITE_CONFIG_KEY  = 'site-config';
	const NAVDATA_KEY      = 'navData';
	const WIDGET_SUFFIX    = 'Method';
	const ROUTE_GRID_INDEX = 'grid-index';
	const ROUTE_GRID_SAVE  = 'grid-model-save';
	const GRID_NODE		   = 'grid' ;
	const FRONT_MENU_ROUTE = 'site-default-route';

	const APPEND  = 'append';
	const PREPEND = 'prepend';
	const DATA_DICTIONARY = 'data-dictionary';
	const USER_ROLE_KEY   = 'user-role';
	const WEB_ENABLED_KEY = 'web-enabled';

	const MENU_MODULE 			= 'm_module';
	const MENU_CONTROLLER	  	= 'm_controller';
	const MENU_ACTION 			= 'm_action';
	const MENU_FRONTEND_KEY		= 'slug';

	const CACHE_MANAGER			= 'cache-manager';
	const CONTEXT_SITE          = 'site';
	const CONTEXT_ADMIN         = 'admin';
	const SCRIPT_INDEX          = 'SCRIPT_INDEX';
	const MESSAGE_DIV			= 'message-div-area'; // value is used in rhemasys.js

	const DEV_ENV               = 'development';
	const PRD_ENV               = 'production';
	const MOD_HEADER_KEY        = 'rms-dev';
	const PRE_QUERY_KEY         = 'PRE_QUERY_KEY';
	const PAGE_CACHE_OBJECT     = 'PAGE_CACHE_OBJECT';

	const REMOTE_DATA_CACHE     = 'REMOTE_DATA_CACHE';
	const SEARCH_TERM_TITLE     = 'SEARCH_TERM_TITLE';
	public static $importUser   =  array("id" => "1","firstname" => "Pele","lastname" => "Odiase","image_file" => null,"visits" => "0","ssid" => "1",
                					"Role" => array("id" => "8","title" => "Super","sequence" => "10"));
	public static $group	    = array();
	public static $root         = '';
	public static $info			= array();
	
	public static $rmsList		= array(
		'bookpages'	    => 'portfolio',
		'contactus'     => 'contact-us',
		'aboutus'	    => 'about-us',
		'articles'	    => 'blog',
		'giftitem'      => 'search',
		'help'          => 'contact-us',
	    'faq'	        => 'contact-us',
	    'prices'        => 'portfolio',
		'sitemap'       => '/',
		'smileys'	    => '/',
		'testimonies'   => 'photobook-reviews-and-comments',
	    'testimories'   => 'photobook-reviews-and-comments',
		'userfullinks'  => 'index',
		'reviews'	    => 'photobook-reviews-and-comments',
		'error'		    => 'index',
		'guestbook'     => 'photobook-reviews-and-comments',
	    'specialoffers' => 'search',
		'webdesign'     => 'search',
	    'portfolio'     => 'portfolio'		 
	);	
 		
	public static $exemptActions = array(
		'feed', 'affiliate-feed', 'outlink'
	);
	/**
	 * @return the $info
	 */
	public static function getInfo() {
		return Rhema_Constant::$info;
	}

	public static function getRedirectUrl($actionCode){
		$url = $actionCode;
		if(isset(self::$rmsList[$actionCode])){
			$url = self::$rmsList[$actionCode];
			if($url == 'search'){
				$view = Zend_Layout::getMvcInstance()->getView();
				$url  = $view->url(array('keyword' => $actionCode), 'deal-search');
			}
		}
		return $url;
	}
	/**
	 * @param field_type $info
	 */
	public static function setInfo($info) {
		Rhema_Constant::$info = $info;
	}

	public static function getPublicRoot(){
		$info = self::getInfo();
		$dir  = dirname($info['dirname']);
		return rtrim($dir, DIRECTORY_SEPARATOR) . '/';
	}
	
	public static function getSiteRoot(){
		$info = self::getInfo();
		$dir  = $info['dirname'];
		return rtrim($dir, DIRECTORY_SEPARATOR) . '/';		
	}
	
	public static function getBackendPath(){
		return self::getPublicRoot() . 'backend/';
	}
	
	public static function getCssList($context, $theme = 'default'){	
		$dir         = SITE_DIR ; 
		$publicPath  = self::getPublicRoot();
		$backendPath = self::getBackendPath() ;
		$sitePath    = self::getSiteRoot();
		
		self::$group[self::CONTEXT_SITE]['css'] =  array( $backendPath . 'scripts/jquery/css/redmond/jquery-ui-1.8.9.custom.css', 
													$publicPath  . 'media/css/global.css', 
													$publicPath  . 'media/css/affiliate.css'													
												);		
	
		self::$group[self::CONTEXT_ADMIN]['css'] =   array($backendPath . 'scripts/jquery/css/redmond/jquery-ui-1.8.9.custom.css'
								, $backendPath . 'scripts/grid/css/ui.jqgrid.css' 
								, $backendPath . 'scripts/multiselect/css/ui.multiselect.css'
								, $backendPath . 'scripts/cluetip/jquery.cluetip.css'
								, $publicPath .  'media/css/global.css'
								, $backendPath . 'css/layout.css' 
								, $backendPath . 'css/general.css'
								, $backendPath . 'css/adminStyle.css'
								, $backendPath . 'scripts/jstree/themes/default/style.css'
								, $backendPath . 'scripts/jstree/themes/checkbox/style.css'
								, $backendPath . 'scripts/jpicker/css/jPicker-1.1.6.min.css' 								
							 	, $backendPath . 'scripts/dateplustimepicker/css/jquery-dateplustimepicker.min.css' 
							 //	, $backendPath . 'css/tool.css'
							);
		return isset(self::$group[$context]['css']) ? 	self::$group[$context]['css'] : array();	
	}
	
	public static function getJssList($context = self::CONTEXT_SITE,  $theme = 'default'){
		$dir         = SITE_DIR ; 
		$publicPath  = self::getPublicRoot();
		$backendPath = self::getBackendPath() . 'scripts/';
		$sitePath    = self::getSiteRoot();
		
		self::$group[self::CONTEXT_ADMIN]['js'] = array( 						
							  $backendPath . 'dock/jquery.jqDock.min.js'
							, $backendPath . 'dateplustimepicker/js/jquery-dateplustimepicker.min.js'	
							, $backendPath . 'jstree/jquery.tree.min.js'
							, $backendPath . 'jquery.lazyload.mini.js' 
							, $backendPath . 'multiselect/js/plugins/localisation/jquery.localisation-min.js'
							, $backendPath . 'multiselect/js/plugins/tmpl/jquery.tmpl.1.1.1.js'
							, $backendPath . 'multiselect/js/plugins/blockUI/jquery.blockUI.js'
							, $backendPath . 'multiselect/js/ui.multiselect.js'
							, $backendPath . 'cluetip/jquery.cluetip.js'
							, $backendPath . 'jstree/plugins/jquery.tree.checkbox.js'
       						, $backendPath . 'jstree/plugins/jquery.tree.contextmenu.js'
 							, $backendPath . 'grid/js/i18n/grid.locale-en.js'
 							, $backendPath . 'grid/src/grid.tbltogrid.js'
							, $backendPath . 'grid/js/jquery.jqGrid.min.js' 
						 						 	 
					);


		self::$group[self::CONTEXT_SITE]['js'] =  array(  
							$backendPath 	. 'global.js',
							$publicPath		. 'media/js/general-scripts.js' , 
							 $backendPath 	. 'jquery.lazyload.mini.js',
							 //$backendPath . 'featuredimagezoomer.js',							 
							 $backendPath 	. 'infinitecarousel/jquery.infinitecarousel2.min.js',
							// '//connect.facebook.net/en_US/all.js',
							 'http://s7.addthis.com/js/250/addthis_widget.js' ,
							 $backendPath . 'autocomplete/jquery.ui.autocomplete.html.js',
						);
		return isset(self::$group[$context]['js']) ? 	self::$group[$context]['js'] : array();
	}

}

 
