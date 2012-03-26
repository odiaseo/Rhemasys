<?php
 class Rhema_Form_Abstract extends ZendX_JQuery_Form{
	const RETURN_URL_KEY = 'returnto';
	protected $_model;
	protected $_addFormDecorator = true;

	public $buttonDecor = array(
			'ViewHelper',
			'FormElements',
			array(
					'HtmlTag',
					array(
							'tag' => 'div',
							'class' => 'button-elem')));

	public $elementDecorators = array(
			'ViewHelper',
			'Errors',
			array( array( 'data' => 'HtmlTag'), array( 'tag' => 'div', 'class' => 'element')),
			array( 'Label', array( 'tag' => 'td'), array( array( 'row' => 'HtmlTag'), array( 'tag' => 'tr'))));

	public $buttonDecorators = array(
			'ViewHelper',
			array( array( 'data' => 'HtmlTag'), array( 'tag' => 'div', 'class' => 'elm-button')) );

	public $divLabelSpan = array(
			'ViewHelper',
			'Errors',
			array( array( 'data' => 'HtmlTag'),
					array( 'tag' => 'div', 'class' => 'elm-data')),
			array(
					'Label',
					array(
							'tag' => 'dt',
							'class' => 'elm-label')));

	public $multiCheck = array(
			'ViewHelper',
			'Errors',
			array(
					array(
							'data' => 'HtmlTag'),
					array(
							'tag' => 'div',
							'class' => 'elm-multicheck')),
			array(
					'Label',
					array(
							'tag' => 'label',
							'class' => 'elm-label')));

	public $searchOperators = array(
			'eq' => 'is equal to',
			'ne' => 'not equal to',
			'lt' => 'less than',
			'le' => 'less than or equal',
			'gt' => 'greater than',
			'ge' => 'greater than or equal',
			'in' => 'is in',
			'bw' => 'begins with',
			'bn' => 'does not begin with',
			'ew' => 'ends with',
			'en' => 'does not end with',
			'cn' => 'contains',
			'nc' => 'does not contain')

	;

	public function init(){
		parent::init(); 
		if($this->_addFormDecorator){
			$this->addFormDesignerClasses();
		}
	 $this->setDisableLoadDefaultDecorators(true);
	 $this->clearDecorators();
	 $this->addDecorator('FormElements')
          ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'rhema_form'))
          ->addDecorator('Form');
 

/*		$this->addElement('hash', 'csrf', array(
				'ignore'        => true,
			 	'salt'		    => 'unique',
				'order'		    => 500,
			 	'decorators' 	=> array('ViewHelper',)
		 ));*/
	}

	public function setCompositeDecorator(){
		foreach($this->getElements() as $elem){
			$elem->setDecorators(array(
					'Composite'));
		}
	}

	public function getDecor($class = ''){
		return Rhema_Util::getDecor($class);
	}

	public function addFormDesignerClasses(){
		$uniqueId   = 'form_' . get_class($this) ;
		$class 		= (string) $this->getAttrib('class');
		$arr 		= array_merge(explode(' ', $class), array($uniqueId));
		$comb 		= (string) implode(' ', array_filter($arr));
		$view		= Zend_Layout::getMvcInstance()->getView();

		$backedUrl  = Rhema_SiteConfig::getBackendScriptsPath();
		$this->setAttrib('class',$comb);

		$view->headScript()->appendFile($backedUrl . '/formdesigner/js/jquery.formdesigner-1.0.0-min.js');
		$view->collateScripts(" jQuery('.{$uniqueId}').formDesigner(); ");		
		$view->headLink()->appendStylesheet($backedUrl . '/formdesigner/css/jquery.formdesigner.css'); 
	}

	/**
	 * Adds tooltip hover effects to form input fields
	 */
	public static function enableFormTooltips($uniqueId = '.formDesigner'){
		$view = Zend_Layout::getMvcInstance()->getView();
		$view->collateScripts(' jQuery("'.$uniqueId.' :input[title]").tooltip({
									position: "center right",
									offset: [-2, 10],
									effect: "slide",
									direction: "right",
									opacity: 0.7
								}); '
	      				);
	}

	public static function getElemDecor($field){
		$return = array( 'ViewHelper',
						 array( 'Description', array( 'tag' => 'span', 'class' => 'field-info', 'id' => "info-$field")),
						//'Errors',
						array( 'Label', array( 'requiredSuffix' => ' * ')),
						new Zend_Form_Decorator_HtmlTag(array( 'tag' => 'div', 'class' => 'elem-field', 'id' => "div-$field"))
						) ;

		return $return;
	}

	public function setModel($model){
		$this->_model = $model;
	}

	public function getModel(){
		return $this->_model;
	}

	public function getForm($name){
		if(! isset($this->_forms[$name])){
			$class = join('_', array(
					$this->_getNamespace(),
					'Form',
					$this->_getInflected($name)));

			$this->_forms[$name] = new $class(array(
					'model' => $this));
		}

		return $this->_forms[$name];
	}

	/* public function setOptions(array $options)	    {
	        if (null === $this->_classMethods) {
	            $this->_classMethods = get_class_methods($this);
	        }
	        foreach ($options as $key => $value) {
	            $method = 'set' . ucfirst($key);
	            if (in_array($method, $this->_classMethods)) {
	                $this->$method($value);
	            }
	        }
	        return $this;
	    }*/

	/**
	 * Get a resource
	 *
	 * @param string $name
	 * @return Rhema_Model_Resource_Interface
	 */
	public function getResource($name){
		if(! isset($this->_resources[$name])){
			$class = join('_', array(
					$this->_getNamespace(),
					'Resource',
					$this->_getInflected($name)));
			$this->_resources[$name] = new $class();
		}
		return $this->_resources[$name];
	}

	public function getRecaptchaElement(){
		$recaptchaKeys = Rhema_SiteConfig::getConfig('recaptcha');
		$recaptcha = new Zend_Service_ReCaptcha($recaptchaKeys['publickey'], $recaptchaKeys['privatekey'], NULL,
				array( 'theme' => 'clean',
					   'lang'  => Zend_Registry::get('Zend_Locale')->getLanguage()
				));

		$captcha = new Zend_Form_Element_Captcha('captcha',
				array(
						'order' => 900,
						'required' => true,
						'label' => 'To help us prevent span, please type the characters you see in the picture below.',
						'captcha' => 'ReCaptcha',
						'captchaOptions' => array(
								'captcha' => 'ReCaptcha',
								'service' => $recaptcha)));

		$captcha->removeDecorator('Errors');
		return $captcha;
	}

	public function getSubmitButton($label = 'Send', $options = array(), $order = 1001){
		$button = new Zend_Form_Element_Submit(
				array( 'ignore' => true,
						'name' => get_class($this),
						'label' => $label, 
						'order' => $order,
						'decorators' => $this->buttonDecorators) + $options);
		return $button;
	}

	public function isRhemaButtonSubmitted(){
		$id      = get_class($this);
		$id2     = $id . '_x';
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$method  = $this->getMethod() == 'post' ? 'getPost' : 'getQuery';;
		
		return ($request->{$method}($id, $request->getPost($id2, false)) === false) ? false : true;

	}

}