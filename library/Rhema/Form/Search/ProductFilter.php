<?php
	class Rhema_Form_Search_ProductFilter extends Rhema_Form_Abstract {
		public function init(){
			//$this->setMethod('get');
			$isMobile = Rhema_SiteConfig::getConfig('settings.show_mobile_filters');
			$this->setAction($this->getView()->url(array('page' => 1), 'product-search-filters'),true);
			$this->addAttribs(array('id' => 'product-filter-form', 'class'=> 'rounded ui-widget-header'));
			$cur        = new Zend_Currency();
			$min        = 0.01;
			$blank      = array(0 => '--select--');
			$networks   = array(
				'3'         => '3',
				'vodafone'  => 'Vodafone',
				't-mobile'	=> 'T Mobile',
				'orange'	=> 'Orange',
				'lebara'	=> 'Lebara',
				'02'		=> 'O2'
			);
			asort($networks);
			$bList      = $cList = $pList = $gList = $blank;
			$model	    = Rhema_Model_Service::factory('affiliate_product');
			$data       = $model->getProductStatList()	;
	 				
			$brands     = $data['brands'];
			$categories = $data['category'];
			$gifts      = $data['gifts'];
			
			foreach((array) $brands as $brand){
				$bList[$brand['id']] = $brand['title'];
			}
			
			foreach((array) $categories as $cat){
				$cList[$cat['id']] = $cat['title'];
			}
			$lim = 250 ;		
			foreach((array) $gifts as $gift){
				if(!preg_match(Admin_Model_AffiliatePromotion::PROMO_REGEX, $gift['title'])){
					$gList[$gift['id']] =  $gift['title'];
				}
			}	
					
			foreach($this->getView()->getPriceRanges($min) as $range){
				foreach($range as $start => $end){
					$key         = $start . '-' . $end;
					$startPrice  = $cur->setValue($start)->toString();
		 			$enPrice     = $cur->setValue($end)->toString();	
					$pList[$key] = "$startPrice - $enPrice";
				}
			}
			$topLimit           = $end + $min;
			$enPrice            = $cur->setValue($topLimit)->toString();
			$pList["$topLimit"] = "$enPrice and above";
			
			$this->addElement('select','brand', array(
				'label'		   => 'Brand',
				'value'		   => '',
				'filters'	   => array('StripTags', 'StringTrim'),
				'multiOptions' => $bList,
				'decorators'   => $this->getElemDecor('brand')
			));		
			
			if($isMobile){
				$this->addElement('select','network', array(
					'label'		   => 'Mobile Network',
					'value'		   => '',
					'filters'	   => array('StripTags', 'StringTrim'),
					'multiOptions' => $blank + $networks,
					'decorators'   => $this->getElemDecor('network')
				));	
				
				$this->addElement('select','contract', array(
					'label'		   => 'Contract Length',
					'value'		   => '',
					'filters'	   => array('StripTags', 'StringTrim'),
					'multiOptions' => $blank + array('12' => '12 Months', '18' => '18 Months', '24' => '24 Montths'),
					'decorators'   => $this->getElemDecor('contract')
				));	
	
				$this->addElement('select','minutes', array(
					'label'		   => 'Min. Minutes',
					'value'		   => '',
					'filters'	   => array('StripTags', 'StringTrim'),
					'multiOptions' => $blank + array('150'=> '150', '300' => '300', '600' => '600', '900' => '900'),
					'decorators'   => $this->getElemDecor('minutes')
				));	
				
				$this->addElement('select','texts', array(
					'label'		   => 'Min. Texts',
					'value'		   => '',
					'filters'	   => array('StripTags', 'StringTrim'),
					'validators'   => array('Digits'),
					'multiOptions' => $blank + array('150'=> '150', '300' => '300', '600' => '600', '900' => '900'),
					'decorators'   => $this->getElemDecor('texts')
				));
			}		
			$this->addElement('select','category', array(
				'label'		   => 'Category',
				'value'		   => '',
				'filters'	   => array('StripTags', 'StringTrim'),
				'multiOptions' => $cList,
				'decorators'   => $this->getElemDecor('category')
			));
			
			$this->addElement('select','price', array(
				'label'		   => 'Price Range',
				'value'		   => '',
				'filters'	   => array('StripTags', 'StringTrim'),
				'multiOptions' => $pList,
				'decorators'   => $this->getElemDecor('price')
			));

			
			$this->addElement('select','sort', array(
				'label'		   => 'Sort Order',
				'value'		   => '',
				'decorators'   => $this->getElemDecor('sort'),
				'filters'	   => array('StripTags', 'StringTrim'),
				'multiOptions' => $blank + array( 'title-asc' => 'Title Ascending',
										 'title-desc'=>'Title Descending',
										 'price-asc'=> 'Price Ascending',
										 'price-desc'=> 'Price Descending',
								   ),
			));		
			
/*			$this->addElement('select','gift', array(
				'label'		   => 'Free Gift',
				'value'		   => '',
				'filters'	   => array('StripTags', 'StringTrim'),
				'multiOptions' => $gList,
				'decorators'   => $this->getElemDecor('gift')
			));	*/						
			$this->addElement($this->getSubmitButton('Search')); 
		}
	}