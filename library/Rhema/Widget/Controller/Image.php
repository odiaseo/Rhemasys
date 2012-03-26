<?php

	class Rhema_Widget_Controller_Image extends Rhema_Widget_Abstract{

		protected $_imagePath  ;
		protected $_root ;
		protected $_prefix   ;
		
		const JPG           = 'JPG';
		const PNG           = 'PNG';
		const GIF           = 'GIF';
		const SLIDE_DIR     = 'slideshow';
		const THUMBNAIL_DIR = 'thumbnails';
		const SIZE_SEPARATOR = 'x';
		
		private $_validImages = array(
			self::JPG, 
			self::PNG,
			self::GIF			
		);

	    public function init(){
	    	/* Initialize action controller here */ 
	    	parent::init();
			$this->_root   = Rhema_Constant::getSiteRoot(); // $this->_request->getServer('DOCUMENT_ROOT');
			$this->_prefix = '/media/slideshow/';
	    }
 
	     
		public function slideshowMethod($params = array()){
			return $this->_getImages($params);
		}

			
		public function agileCarouselMethod(){ 
			$path     = Rhema_SiteConfig::getDomainPath('admin', 'media/css') . 'carousel.css';			
			$slideObj = new Admin_Model_Slide();
			$slides   = (array)$slideObj->getActiveSlides();	
			$ret      = Rhema_Model_Service::factory('slide_retailer');
			$cat      = Rhema_Model_Service::factory('slide_category');
			
			foreach($slides as $id => &$item){
				if($item['is_category']){
					$data = $cat->getSlideData($item['id'], $item);
				}else{
					$data = $ret->getSlideData($item['id'], $item);	
				}
				$slides[$id] = $data;
			}		
			
			$this->_view->slideData = $slides ;
			
			$this->_view->includeCss("scripts/carousel/css/agile_carousel.css", false);			
			$this->_view->includeCss($path, false);
			$this->_view->includeJs('carousel/agile_carousel.alpha.js',Rhema_Constant::APPEND , false);			
		}
		
		public function carouselMethod($params = array()){
			return $this->_getImages($params);
		}
		
		private function _getImages($params = array()){
			$filter           = new Zend_Filter_RealPath();
			$app		      = Zend_Registry::get('application'); 
			$config           = Rhema_SiteConfig::getConfig(self::SLIDE_DIR);
			$section          = array_key_exists('pageSection', $params) ? str_replace('page', '', strtolower($params['pageSection'])) : null	;	
			$pageName         = ('body' == $section) ? $this->_request->getParam('action', null) : $section;			
			$subDirectory     = isset($config[$pageName]) ? $pageName : 'default';
			$thumbSize        = isset($config[$subDirectory]['thumbnail_size']) 
								? $config[$subDirectory]['thumbnail_size'] 
								: $config['default']['thumbnail_size'];
			$list			  = array();
			
			$this->_prefix   .= $pageName . '/' ;
			$this->_imagePath = $this->_root . $this->_prefix;
			$thumbnailDir     = $this->_imagePath  . self::THUMBNAIL_DIR ;
			
			if(!file_exists($this->_imagePath)){
				mkdir($this->_imagePath, 0755, true);
			}
					
			if(!file_exists($thumbnailDir)){
				@mkdir($thumbnailDir, 0755, true);
			}
			$this->_imagePath = $filter->filter($this->_imagePath);
			$thumbnailDir = $filter->filter($thumbnailDir);
					 
			$resizer = Zend_Controller_Action_HelperBroker::getStaticHelper('imageResizer');
			
			foreach (new DirectoryIterator($this->_imagePath) as $fileInfo) {
				$pathName     = $fileInfo->getPathname();
				$mime         = mime_content_type($pathName);
				$filename     = $fileInfo->getFilename();
				$ext          = strtoupper(substr($filename, -3));
				
		    	if($fileInfo->isDot() or !in_array($ext, $this->_validImages)) {
		    		continue;
		    	}
		    	
		    	$src             = $this->_prefix . $fileInfo->getFilename();
		    	$configkey       = implode('.', array(self::SLIDE_DIR, $pageName,strtolower(substr($filename,0, -4))));
		    	$data            = (array) Rhema_SiteConfig::getConfig($configkey);
		    	$dto			 = new Rhema_Dto_SlideshowImage($data);
		    	
		    	$dto->setSrc($src);
		    	$list[$filename] = $dto;		    	
		    	$thumbnail       = $thumbnailDir . '/' . $filename;
		    	
		    	if(!file_exists($thumbnail)){
		    		$resizer->load($pathName) ;
		    		 if(strpos($thumbSize, self::SIZE_SEPARATOR) !== false){
		    			list($w, $h) = explode(self::SIZE_SEPARATOR, $thumbSize);
		    		    $resizer->resize($w, $h) ;
		    		}elseif(strpos($thumbSize, '%') !== false){
		    			$percent = str_replace('%', '', $thumbSize);
		    			$resizer->scale($percent);
		    		}elseif($thumbSize){
		    			$thumbSize = (float) $thumbSize ;
		    			$resizer->resizeToWidth($thumbSize);
		    		}
		    		$resizer->save($thumbnail,$resizer->getImageType());		    		
		    	}
			}			

			ksort($list);
			
			$return['images']    = $list ;
			$return['divId']  	 = self::SLIDE_DIR . '-' . $pageName ;
			$return['width']  	 = isset($config[$pageName]['width']) ? $config[$pageName]['width'] : '360';
			$return['height'] 	 = isset($config[$pageName]['height']) ? $config[$pageName]['height'] : '330';
			$return['delay'] 	 = isset($config[$pageName]['delay']) ? $config[$pageName]['delay'] : 5000;
			$return['prefix']	 = $this->_prefix ;
			$return['imagePath'] = $this->_imagePath ;

			return $return ;
		}
		

	}
