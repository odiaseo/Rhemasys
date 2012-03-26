<?php 
class Rhema_Form_AutoForm extends ZFDoctrine_Form_Model {
	protected $_ignoreFields = array(
		'created_by',
		'updated_by',		
		'updated_at',		
		'admin_subsite_id',
		'ssid',
	);
	
	protected $_readOnly = array( 
		'root_dir',
		'domain', 
		'is_active',
		'created_at',
		'renewal_at',
	);	
	
	    public function init(){
	    	parent::init();
	    	$langResource  = realpath(APPLICATION_PATH . '/../library/resources');
	        
	        $translator = new Zend_Translate(array(
	        	'adapter'	=> Zend_Translate::AN_ARRAY,
	            'content'   => $langResource,
	            'locale'	=> Zend_Registry::get('Zend_Locale'),
	            'scan'	    => Zend_Translate::LOCALE_DIRECTORY
	        ));
	        
	    //    Zend_Validate_Abstract::setDefaultTranslator($translator);
	  //      $this->setTranslator($translator);
	        
	        $this->addElement('hash', 'csrf', array(
	            'ignore' 		=> true
	        	//'decorators' 	=> array('ViewHelper',),
	        ));
	        
	    }
	    public function loadDefaultDecorators(){
	        $this->setDecorators(array(
	        	array('Description', array('tag' => 'p', 'class' => 'form-info')),
	            'FormElements',
	            array('HtmlTag', array('tag' => 'div', 'class' => 'rhema_form')),
	            'Form'
	        ));
	    }	
	    
	    protected function _postGenerate(){
	    	foreach($this->getElements() as $elm){
	    		$name = $elm->getName();
	    		if(in_array($name, $this->_ignoreFields)){
	    			$this->removeElement($name);
	    		}elseif(in_array($name, $this->_readOnly)){
	    			$elm->setAttrib('disabled', 'disabled');
	    		}
	    	}
	    }
}