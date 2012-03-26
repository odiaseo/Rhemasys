<?php 
	class Rhema_View_Helper_SlideShow extends Zend_View_Helper_Abstract {
		
		private $_divId;
		private $_width;
		private $_height ;
		private $_delay;
		
		/**
		 * @return the $_divId
		 */
		public function getDivId() {
			return $this->_divId;
		}
	
			/**
		 * @return the $_width
		 */
		public function getWidth() {
			return $this->_width;
		}
	
			/**
		 * @return the $_height
		 */
		public function getHeight() {
			return $this->_height;
		}
	
			/**
		 * @return the $_delay
		 */
		public function getDelay() {
			return $this->_delay;
		}
	
			/**
		 * @param field_type $_divId
		 */
		public function setDivId($_divId) {
			$this->_divId = $_divId;
		}
	
			/**
		 * @param field_type $_width
		 */
		public function setWidth($_width) {
			$this->_width = $_width;
		}
	
			/**
		 * @param field_type $_height
		 */
		public function setHeight($_height) {
			$this->_height = $_height;
		}
	
			/**
		 * @param field_type $_delay
		 */
		public function setDelay($_delay) {
			$this->_delay = $_delay;
		}
	
			
		public function SlideShow($options = array()){
			foreach($options as $key => $value){
				$method = 'set' . ucfirst($key) ;
				if(method_exists($this, $method)){
					$this->$method($value);
				}
			}	
			
			return $this;
		}
		
		public function getScript(){
			$divId = $this->getDivId() ;
			$delay = $this->getDelay();
			
			$slideScript ="
					jQuery(function() { 
						jQuery('.slideshow').fadeIn();
					    jQuery('img','#{$divId}').bind('click', function(){
							document.location = jQuery(this).attr('alt');
						});
		    			setInterval(function(){
								gbl.slideSwitch('img', '#{$divId}');
							}, {$delay} );
					});
				";		

			return $slideScript ;
		}
		
		public function getCSS(){
			$divId  = $this->getDivId() ;
			$width  = $this->getWidth();
			$height = $this->getHeight();
			
			$css = " 
				#$divId {
					position: relative;
					height: {$height}px;
				    width : {$width}px;
				    margin:0 auto;
				}
				
				#$divId img {
					position: absolute;
					top: 0;
					left: 0;
					z-index: 8;
					opacity: 0.0;
				}
				
				#$divId img.active {
					z-index: 10;
					opacity: 1.0;
				}
				
				#$divId img.last-active {
					z-index: 9;
				} ";	

			return $css ;
		}
		
		public function getCaptions(Rhema_Dto_SlideshowImage $dto = null){
			$title    = $dto->getTitle();
			$subTitle = $dto->getSubtitle();
			$html     = '';
			
			if($title or $subTitle){
				$html  .= '<p class="caption">';
			}
			if($title){
				$html  .= $title ;
			}
			
			if($subTitle){
				$html  .= '<br />'.$subTitle;
			}
			
			if($title or $subTitle){
				$html  .= '</p>';
			}
			
 			return $html;			
		}
		 
		public function getCarouselScript($id, $useThumbs = 0){
			$scriptPath = Rhema_SiteConfig::getBackendScriptsPath();
			$jsVars     = '';
			if($useThumbs){
				$carouselDims = Rhema_SiteConfig::getConfig('carousel.thumbnail');
				$jsVars = "thumbnailHeight   : '{$carouselDims['height']}px', thumbnailWidth    : '{$carouselDims['width']}px'";
			}			
			$useString  = 	$useThumbs ? 'false' : 'true' ;
			$script =" 
					 var headBanner = jQuery('div.sxnHeadBanner');
					  
					  jQuery('#$id').infiniteCarousel({
						imagePath         : '{$scriptPath}infinitecarousel/images/', 
						transitionSpeed   : 600,
						displayTime       : 8000,
						autoStart         : $useThumbs,
						displayThumbnailNumbers : $useString, {$jsVars}
					   }).show('slow'); 
					   
					  
					  if(!headBanner.hasClass('carousel')){
	                 	headBanner.addClass('carousel'); 
	                  } ";
			$script .= $useThumbs ? $this->useThumbnails($id) : '';
			
			return $script ;
		}
		
		public function useThumbnails($id){
			$string     = ''; 
			$thumbDir   = Rhema_Widget_Controller_Image::THUMBNAIL_DIR ;
			$string    .= " gbl.addCarouselThumbBackground('#$id', '{$thumbDir}');";
			 
			return $string ;
		}
		
		public function getCarouselCss($id, $width){
			$css = "#{$id} ul {
				list-style: none;
				width:{$width}px;
				margin: 0;
				padding: 0;
				position:relative;
			}
			#$id li {
				display:inline;
				float:left;
			}	
			#{$id} img {
			   border:0px;
			} 
			";	
			
			return $css;			
		}
	}