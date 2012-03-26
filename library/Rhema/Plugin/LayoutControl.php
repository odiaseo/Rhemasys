<?php
/**
 * This plugin builds the front end page layout based on backend setup
 * @author Pele
 *
 */
class Rhema_Plugin_LayoutControl extends Zend_Controller_Plugin_Abstract{

	public $headerTags = array('page_header_id', 'page_footer_id', 'page_body_id', 'page_id');
	protected $_view;
	protected $_params = array();
	
	protected $_exemptActions = array(
		'feed', 'affiliate-feed', 'outlink'
	);

	private   $_layoutObject ;
	private   $_tempObject ;
	private   $_util ;
	private   $_objectStack = array();
	
	

	public function routeStartup(Zend_Controller_Request_Abstract $request){
		//$this->debugLucene();
		//Zend_Controller_Front::getInstance()->setBaseUrl('/' . SITE_DIR);
		$this->initTranslate() ;
  
		$uri = $request->getRequestUri();
		if(strpos($uri, '/' . SITE_DIR) === 0){
			$newUrl   = str_replace('/' . SITE_DIR, '',$uri);
			$link     = $request->getScheme() . '://' . $request->getHttpHost() . $newUrl ;
			$broker   = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
			//pd($uri,$link);
			$broker->setCode(301)
				   ->gotoUrlAndExit($link);
		}
		$namespace        = Zend_Registry::get('namespace'); 
		$siteConfig       = array();
		$this->_util      = Rhema_Util::getInstance();

		if(!($namespace->subsiteId and ($siteConfig  = $namespace->siteconfig))){  
			$domain       = $this->_util->getRegisteredHostname();
			$useCachedLic = Rhema_SiteConfig::getConfig('settings.cache_licence');
			$siteConfig   = $this->_util->validateLicense($domain, SITE_DIR);
		}

		if(!$siteConfig){
			$msg    = sprintf("Site not registered for domain %s - Uri = %s, Directory = %s", $domain, $uri, SITE_DIR);
			if($log = (Zend_Registry::isRegistered('logger') ? Zend_Registry::get('logger') : false )){
				$log->debug($msg);
			}		
			die($msg);
		}else{	
			if(!$siteConfig['subsite']['colour_scheme']){
				$siteConfig['subsite']['colour_scheme'] = 'default';
			}					
			Zend_Registry::set(Rhema_Constant::SITE_THEME_KEY, $siteConfig['subsite']['colour_scheme']);
			$this->_util->setSessData(Rhema_Constant::SITE_CONFIG_KEY, $siteConfig);
		}
		 
	}

	/**
	 * Entry point. Run if not admin
	 */
	public function routeShutdown(Zend_Controller_Request_Abstract $request){
			//$routename = Zend_Controller_Front::getInstance()->getRouter()->getCurrentRouteName();
			//pd($routename);
		$this->_layoutObject = Rhema_Model_Service::factory('PageLayout');
	 	$this->_tempObject   = Rhema_Model_Service::factory('TemplateSection');
 
	 	if($request->isPost()){
	 		$cacheManager = Zend_Registry::get('cache-manager');
	 		$cacheManager->getCache('page')->getBackend()->setOption('disable_caching', true);
	 	}
	 	
		if(! ($request->isXmlHttpRequest() 
				or Rhema_Util::isCli() 
				or preg_match('/(rest|soap|setup|cron)/i', $request->getControllerName())))
			{
			
			$actCode = $request->getActionName() ;
			$slug    = $request->getParam('slug', '');
			$actCode = ($slug and $actCode != 'affiliate-feed')? $slug : $actCode ;
			
			if(preg_match('/^(rms)|\./i', $actCode)){
		 
				$actCode = str_replace(array('rms'), '', strtolower($actCode));
				$actCode = current(explode('.', $actCode));
				if(array_key_exists($actCode, Rhema_Constant::$rmsList)){
					$url    = Rhema_Constant::getRedirectUrl($actCode);
				    $broker = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
				    $broker->gotoUrlAndExit($url, array('code' => 301));					
				}
			}
			//pd($request->getParams());

			if(! $request->getParam('isControlPanel') and $actCode != 'logout'){
				$layout      = Zend_Layout::getMvcInstance();
				$this->_view = $layout->getView();
				$editMode    = $request->getParam('editmode', null);

				if($editMode != null){
					$this->_util->setSessData('editmode', $editMode);
				}
				
				$cache      = Rhema_Model_Service::getCache();
				$pageDef    = Rhema_Model_Service::factory('page')->getPageDefinition($actCode);
				$page       = ($pageDef and count($pageDef)) ? $pageDef : array();
 
				if($page){
					$itemList = array();
					$this->_util->setSessData('active-page', $page);		
					$layout->pageData      = $page;
					$headerFooterCacheId   = 'header_footer' . $page['PageHeader']['id'] . $page['PageFooter']['id']; 
					$headerAndFooter       = $this->_layoutObject->getHeaderFooterItems($page);
			 
					if($cache->test($headerFooterCacheId)){
						$itemList = $cache->load($headerFooterCacheId);
					}else{							
						foreach($headerAndFooter as $type => $typeData){    
							$itemList += $this->setLayoutItems($typeData['stack'], $type);
						}						 
						$done = $cache->save($itemList, $headerFooterCacheId);						
					}

					foreach($headerAndFooter as $type => $typeData){ 
						$sectionKey          = $type . 'Sections';
						$layout->$sectionKey = $typeData['sections'];
						$layout->$type       = $typeData['layout'];
					}
					
					$bodyCacheId = 'page' . $page['id'];
					$tempLay     = $this->_layoutObject->getPageLayout( $page['id'], $page['template_id'], 'Page'); 							
					$itemList    +=  $this->setLayoutItems($tempLay['stack']);
			
					$layout->PageBodySections = $this->_tempObject->getTemplateSections($page['template_id'], Doctrine_core::HYDRATE_ARRAY, $page['id'], true);
					$layout->PageBody         = $tempLay['layout'];
					$layout->itemList         = $itemList ;
					
				}elseif(array_search($actCode, Rhema_Constant::$exemptActions)=== false){
				   $broker   = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
				   $homePage = '/';
				   $broker->gotoUrlAndExit($homePage, array('code' => 301));
				}
			}
		}
	}
	public function preDispatch(Zend_Controller_Request_Abstract $request){ 
		if(!Rhema_Util::isCli()){
			$position 		 = $request->getParam('element-position', false);
			$view	  	     = Zend_Layout::getMvcInstance()->getView();
			$host            = current(explode(':', $request->getHttpHost()));
			$view->rhemaLink = $request->getScheme() . '://' . $host . $request->getBasePath() . '/';
			
			if($position){
				$helper = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
				$helper->setResponseSegment($position);
			}
	  
			 
			$search = $request->getParam('_search', null); 
			if($search === null and $this->_request->getParam('isControlPanel')){
				$request->setParam('ajx', 1);
			}
			Zend_Registry::set('curModule', $request->getModuleName());		
		}
	}
	
 
	/**
	 * Run through the page layout stack and execute their controllers
	 * Assign return values to the layout instance
	 * @param $stack
	 * @return none
	 */
	public function setLayoutItems($stack, $displaySection = 'PageBody'){
		//$memManager = Rhema_Util::getMemoryManager();
		$returnArr  = array();
		$view       = Zend_Layout::getMvcInstance()->getView();
		$filter     = new Zend_Filter_Word_CamelCaseToDash();
		$loggedIn   = Zend_Auth::getInstance()->hasIdentity();

		unset($this->_params['element-position']);

		foreach($stack as $stackId => $items){
			foreach($items as $itemType => $data){
				$content     = '';
				$itemId      = $data['item'];
				$formatted   = $this->_util->formatItemId('lay', $stackId);
				$res         = array();
				$res['type'] = $itemType;

				if(!isset($this->_objectStack[$itemType])){
					$this->_objectStack[$itemType] =    Rhema_Model_Service::factory($itemType);
				}

				$object = $this->_objectStack[$itemType] ;
 
				switch($itemType){
					case 'admin_element' :
						{
							$item = $object->getElement($itemId);
							if($item){
								list($controller, $method) = explode(WIDGET_SEP, $item['widget']);
								$className   = $this->_util->getWidgetClassname($controller);
								$classObject = new $className();
								$methodName  = $this->_util->getMethodName($method);
								$parameter   = array('pageSection' => $displaySection);

								if(is_object($classObject) and method_exists($classObject, $methodName)){
									$content = $classObject->$methodName($parameter);
								}else{
									$content = array('Widget method not found : Controller =' . $controller . ', method=' . $method);
								}

								$res['item']       = $item;
								$res['widgetView'] = $this->_util->getWidgetViewName($item['widget']);
								$res['content']    = $content;
								if($content){
									$view->assign($content);
								}
							}
							break;
						}
					case 'component' :
						{
							$item           = $object->getItem($itemId);
							$res['content'] =  $item['content'] ; //$this->replaceAnchorsWithText($item['content']);
							$res['item']    = $item;

							break;
						}

					case 'affiliate_product_category':
					case (substr($itemType, - 4) == 'menu') :
						$res['type']   = 'Menu';
					case (substr($itemType, - 5) == 'crumb') :
						{
							$treeOption     = array('root_id' => $itemId);
							$res['content'] = $object->getMenuTree($treeOption, $object->getModelName(), $loggedIn);
							$res['type']    = $res['type'] ? $res['type'] : 'Crumb';
							break;
						}

					case 'boiler_plate' :
						{
							$content = '';
							break;
						}

					default :
						{
							$content = '';
						}
				}

				$returnArr[$formatted] = $res ; 
			}
		}
		
		return $returnArr ;
	}
 
	/**
	 * Helper function to replace anchor links in static content with text place holders
	 * @param $data
	 * @return string
	 */
	public function replaceAnchorsWithText($data){
		/*$matches = array ();
		$regex = '/href=[\'"]+?\s*(?P<link>\S+)\s*[\'"]+?/i';
		$count = preg_match_all ( $regex, $data, $matches );

		if ($count and isset ( $matches ['link'] )) {
			foreach ( $matches ['link'] as $i => $v ) {
				$v = str_replace ( '_', '-', strtolower ( $v ) );
				$param = array ('action' => $v, 'module' => DEFAULT_MODULE, 'controller' => 'index' );
				$find [] = '/' . $matches [0] [$i] . '/';
				$replace [] = 'href="' . $this->_view->url ( $param, FRONT_MENU_ROUTE ) . '"';
			}

			$res = preg_replace ( $find, $replace, $data );
		} else {
			$res = $data;
		}

		return $res;
		*/
		return $data;
	}

    public function debugLucene(){
    	//die('top');
    	$search = Rhema_Search_Lucene::getInstance(true);
    	$search->updateAll(5);
    	die('bottom');    	
    }
    
    
    protected function  initTranslate(){
    	$logErrors = (Rhema_SiteConfig::isProd() or Rhema_Util::isAdminRoute($_SERVER['REQUEST_URI'])) ? false : true ;
    	   		 
    	$option     = Rhema_SiteConfig::getConfig('settings');
    	$logDir     = rtrim($option['log_dir']);    	
    	$cache      = Rhema_Model_Service::getCache() ; 
    	$backend    = clone $cache->getBackend();
    	$transCache = clone $cache ;
    	
    	if(method_exists($backend, 'setCacheDir')){
    		$cacheDirs  = Rhema_Util::createSiteDirectory('cache/translations', 0777);
    		$backend->setCacheDir($cacheDirs[0]);    		
    	} 
    	
    	$transCache->setBackend($backend);
 
     	 
    	$log        = new Zend_Log(new Zend_Log_Writer_Stream($logDir . 'route-translation.log')); 
    	$mainLog    = new Zend_Log(new Zend_Log_Writer_Stream($logDir . 'content-translation.log'));
      	
        $locale     = Zend_Registry::get('Zend_Locale')->toString();
        $realFilter = new Zend_Filter_RealPath();
        
    	$routeTrans = new Zend_Translate(array('adapter'  => Zend_Translate::AN_TMX,  
    											'content' => Rhema_Util_TmxGenerator::getTmxFilename($locale, false, 'route'),
    										    'cache'	  => $transCache, 
	    										'log'     => $log, 
	    										'tag'	  => 'routetranslator',    											
	    										'locale'  => $locale,
	    										'logUntranslated' => $logErrors,
	    										   ) 
	    				);
	        Zend_Controller_Router_Route::setDefaultTranslator($routeTrans); 
	        
			$langResource = $realFilter->filter(APPLICATION_PATH . '/../thirdparty/resources');
	        $formTrans    = new Zend_Translate(array(
					'adapter' => Zend_Translate::AN_ARRAY,
					'content' => $langResource,
					'locale'  => $locale,
					'scan'    => Zend_Translate::LOCALE_DIRECTORY
			));
	
			
	        $translator = new Zend_Translate(array('adapter'  => Zend_Translate::AN_TMX, 
							        				'content' => Rhema_Util_TmxGenerator::getTmxFilename($locale),
							        			    'cache'	  => $transCache, 
	        									    'log'     => $mainLog, 
							        				'locale'  => $locale,
	        										'tag'	  => 'texttranslator',
	        										'logUntranslated' => $logErrors,
	        								));
	        								 
			
			$translator->addTranslation(array('content' => $formTrans));  
	        Zend_Registry::set('Zend_Translate', $translator );
	        
	        unset($formTrans);    	 
    }   
 }