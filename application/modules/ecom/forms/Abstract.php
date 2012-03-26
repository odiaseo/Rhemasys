<?php
	class Ecom_Form_Abstract extends Rhema_Form_Abstract  {	
		
		protected $_model;
		
		public $buttonDecor = array( 	'ViewHelper',
							'FormElements',
							 array('HtmlTag', array('tag'=>'div', 'class' => 'button-elem')
		));
							  
		public $elementDecorators = array(
	        'ViewHelper',
	        'Errors',
	        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
	        array('Label', array('tag' => 'td'),
	        array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	    ));
	
	    public $buttonDecorators = array(
	        'ViewHelper',
	        array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'elm-button')),

	    );
	    
	    public $divLabelSpan = array(
	        'ViewHelper',
	        'Errors',
	        array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'elm-data')),
	        array('Label', array('tag' => 'dt', 'class' => 'elm-label') 
	    ));
	    
 	    public $multiCheck = array(
	        'ViewHelper',
	        'Errors',
	        array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'elm-multicheck')),
	        array('Label', array('tag' => 'label', 'class' => 'elm-label') 
	    ));
	    
	    
	    public $searchOperators = array(
	    	'eq' 	=> 'is equal to',
	    	'ne'	=> 'not equal to',
	    	'lt'	=> 'less than',
	    	'le'	=> 'less than or equal',
	    	'gt'	=> 'greater than',
	    	'ge'	=> 'greater than or equal',
	    	'in'	=> 'is in',
	    	'bw'	=> 'begins with',
	    	'bn'	=> 'does not begin with',
	    	'ew'	=> 'ends with',
	    	'en'	=> 'does not end with',
	    	'cn'	=> 'contains',
	    	'nc'	=> 'does not contain'
	    
	    );
 
	    					
		public function setCompositeDecorator(){			
			foreach($this->getElements() as $elem){	
				$elem->setDecorators(array('Composite'));
			}
		}
		
		public function getDecor($class = ''){			
			return Rhema_Util::getDecor($class);			 
		}
		 
		public function getElemDecor($field){
			$return = array(
		        'ViewHelper',
		        'Errors',
		        array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class' => "elm-data-$field")),
		        array('Label', array('tag' => 'dt', 'class' => 'elm-label') 
		    ));	
		    
		    return $return;		
		}
		
		public function setModel($model){
			$this->_model = $model;
		}
		
		public function getModel(){
			return $this->_model;
		}
		
		public function getForm($name){
			try{
				if(!isset($this->_forms[$name])){
					$class = join('_', array(
								$this->_getNamespace(),
								'Form',
								$this->_getInflected($name)
					));
					
					$this->_forms[$name] = new $class(array('model' => $this));
				}
				
				return $this->_forms[$name];
			}catch(Exception $e){
				
			}
		}
		
	  
	
		/**
		 * Get a resource
		 *
		 * @param string $name
		 * @return Rhema_Model_Resource_Interface 
		 */
		public function getResource($name) 		{
	        if (!isset($this->_resources[$name])) {
	            $class = join('_', array(
	                    $this->_getNamespace(),
	                    'Resource',
	                    $this->_getInflected($name)
	            ));
	            $this->_resources[$name] = new $class();
	        }
		    return $this->_resources[$name];
		}
	}