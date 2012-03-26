<?php
	class Rhema_Form_Search_AffiliateFeed extends Rhema_Form_Abstract {

		public function init(){
			$this->_addFormDecorator = false;
			parent::init();
			$action	= $this->getView()->url(array('slug' => 'search'), 'site-default-route');
		 	$view = $this->getView();
			$this->setAction($action);
			$this->addAttribs(array('id' => 'affiliate-search-form', 'class' => 'rounded ui-widget-header'));
			$suggester = new  Rhema_Controller_Action_Helper_SuggestList();
			 
			$this->addElement('text','keyword', array(  
				'value'			=> 'Type in keywords', 
			    'title'			=> 'Seperate keywords with spaces',
	            'filters'    	=> array('StringTrim', 'StripTags'), 
	            'decorators' 	=> $this->getElemDecor('search-term'),
			    'onclick'		=> 'this.value=""; jQuery(this).css("color","#444");',
			    'required'	    => true	,
				'order'		    => 5 	
			));
			
			$this->addElement('radio','type', array(  
				'label'		    => 'Filter By:',
				'value'			=> 'product',
				'multiOptions'  => $suggester->getFilters(),
	            'filters'    	=> array('StringTrim', 'StripTags'), 
	            'decorators' 	=> $this->getElemDecor('search-type'), 
			    'required'	    => true	,
				'separator'		=> '',						
				'order'		    => 25
												
			)) ;
			
			$this->getElement('type')->removeDecorator('label');
			$this->getElement('keyword')->removeDecorator('label');

			$this->addElement( new Zend_Form_Element_Image( array( 'ignore' => true,
													'name'   => get_class($this),
													'label'  => 'Search', 
													'order'  => 10,
												    'src'    => Rhema_SiteConfig::getDomainPath('admin',"media/image/graphics/small/") . 'search-button-small.png',
													'decorators' => $this->buttonDecorators)
											));
				
			$this->removeElement('csrf');
			//$param     = array('category', 'retailer');
			$param     = array();
			$data      = $suggester->listAll($param);
			$encoded   =  Zend_Json::encode($data);
			$view->collateScripts("ajaxData = $encoded ; ");
		}
	}