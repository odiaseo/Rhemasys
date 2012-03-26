<?php

	class Rhema_Widget_Controller_Form extends Rhema_Widget_Abstract{

		protected $_flash;

		public function init(){
			parent::init();
			$this->_flash   = Zend_Controller_Action_HelperBroker::getStaticHelper('flashMessenger');
			/*
			 * Add jQuery tools library to forms
			  		$tooltipFile = (Rhema_Util::isOnline())
						 ? 'http://cdn.jquerytools.org/1.2.5/all/jquery.tools.min.js'
						 : '/backend/scripts/jquery.tools.min.js';

			$prequery   = Zend_Registry::get(Rhema_Constant::PRE_QUERY_KEY);
			$prequery[] = $tooltipFile;
			Zend_Registry::set(Rhema_Constant::PRE_QUERY_KEY, $prequery);
			*/
			$tooltipFile = (Rhema_Util::isOnline())
						 ? $this->_view->headScript()->prependFile('http://cdn.jquerytools.org/1.2.5/all/jquery.tools.min.js')
						 : $this->_view->includeJs('jquery.tools.min.js', Rhema_Constant::PREPEND);
			Rhema_Form_Abstract::enableFormTooltips('form');
		}

		public function loginMethod(){
			if(Zend_Auth::getInstance()->hasIdentity()){
				$this->getAuthService()->clear();
			}

			$this->_model 	= MODEL_PREFIX  . 'User';

			$loginForm 		= new Admin_Form_Login();

		 	if($loginForm->isRhemaButtonSubmitted()){
				$submittedData = $this->_request->getPost();
				if ($loginForm->isValid($submittedData)) {
					$result = $this->getAuthService()->authenticate($submittedData);
					if($result->isValid()){ 
						$returnTo = $this->_utility->getSessData('returnto');
						$this->removeCacheFiles();
						$redirector =    Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');

						$msg = new Rhema_Dto_UserMessageDto('Login was successful', 'Loggged In', Rhema_Dto_UserMessageDto::TYPE_SUCCESS);
						$this->_flash->addMessage($msg);

						if($returnTo){
							if(BASE_URL != '/'){
								$returnTo = str_replace(BASE_URL, '', $returnTo);
							}
							$this->_utility->unsetSessData('returnto'); 
						}else{
							$url['module'] 		= DEFAULT_MODULE;
							$url['controller'] 	= 'index';
							$url['action'] 		= 'index';

							$returnTo           = $this->_view->url($url, FRONT_MENU_ROUTE);
							$returnTo 			= str_replace(BASE_URL, '', $returnTo);							  
						}
						$redirector->gotoUrlAndExit($returnTo);
					}else{						 
						$message = $result->getMessages(); //'Login failed, please try again.';
						$this->_view->userMessage =  new Rhema_Dto_UserMessageDto($message, 'Login Error', Rhema_Dto_UserMessageDto::TYPE_ERROR);	 
						//$this->_flash->addMessage($msg);			 
					}
				}else{
					$this->_setFormError($loginForm);
				}
	 		}

	 		$return['form'] = $loginForm;
	 	   return $return;
		}

		public function sitesearchMethod(){
			$form 					= new Rhema_Form_Search_Simple();
			
			$parm['slug']			= 'search'; 
			$action				    = $this->_view->url($parm, 'site-default-route');
						
			if($form->isRhemaButtonSubmitted()){							

				$red = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
				$red->gotoRouteAndExist($action);
				
			}else{
	
				$form->setAction(action);
				$form->setMethod('post');
		
				$return['searchForm']   = $form;
	
				//$this->_view->searchForm = $form;
		    	//$output     			= $this->_view->render('index/search.phtml');
		    	return  $return;
			}
		}

		public function contactMethod(){
			$form 					= new Rhema_Form_Contact();
			$action				    = $this->_view->url();
			$mail     				= 'info@rhema-webdesign.com';
			$mailHide 				= $mail;
			$return['mailto'] 		= $mailHide;
			$userMessage		    = null ;
			$submitted				= $form->isRhemaButtonSubmitted();


			if($submitted){
				if ($form->isValid($this->_request->getPost())) {

					$data		 = $form->getValues();
					$toEmail     = $this->_siteConfig['subsite']['contact_email'];

					$fillname    = $data['title'] . ' ' . $data['name'];
					$message     = 'Telephone = ' . $data['telephone']  . PHP_EOL . $data['message'];

					$mail = new Zend_Mail();
					$mail->setBodyText($message);
					$mail->setFrom($data['email'], $fillname);
					$mail->addTo($toEmail, $data['title']);
					$mail->setSubject($data['subject']);

					try{
						$mail->send();
						$this->_setFormSuccessMessage($form);
					}catch(Exception $e){
						$this->_setSendErrorMessage($form);
					}

					$form->reset();

				}else{
					$this->_setFormError($form);
				}
			}

			$form->setAction($action);

			$return['contactForm']      = $form;

			return  $return;
		}



		public function registerMethod(){
			$options    = array();
			$country	= $this->_request->getParam('country');
			$locale     = null ;

			if($country){
				$locale = new Zend_Locale($country);
				$options['locale'] = $locale;
			}
			$form 		= new Rhema_Form_Register_DCC($options);
			$submitted  = $form->isRhemaButtonSubmitted();
			if($submitted){

				if($form->isValid($this->_request->getPost())){
					$filteredValues = $form->getValues();
					$address   = implode(' ', array_filter(array(
										$filteredValues['line1'],
										$filteredValues['line2'],
										$filteredValues['line3'],
										$filteredValues['city'],
										$filteredValues['state'],
										$filteredValues['region'],
										$filteredValues['post_code'],
										$filteredValues['country']
									)));
					$geoHelper  = Zend_Controller_Action_HelperBroker::getStaticHelper('Geocoder', $locale);
					$latlong    = $geoHelper->direct($address );
					$data       = array_merge($latlong, $filteredValues);
					$param      = array('table' => MODEL_PREFIX . 'AddressBook', 'oper' => 'add');
					$addRes     = Rhema_Model_Abstract::saveData($param, $data);

					if($addRes['rowId']){
						$filteredValues['address_book_id']= $res['rowId'];
						$param     = array('table' => MODEL_PREFIX . 'User', 'oper' => 'add');
						$userRes   = Rhema_Model_Abstract::saveData($param, $filteredValues);

						if($userRes['rowId']){
							$userAdd = new Admin_Model_UserAddress();
							$userAdd->address_book_id = $addRes['rowId'];
							$userAdd->user_id		  = $userRes['rowId'];
							$userAdd->save();

							$user   = $userRes['object']->toArray();
							Zend_Auth::getInstance()->getStorage()->write($user);

							$url    = isset($filteredValues[Rhema_Form_Abstract::RETURN_URL_KEY]) ?
									  $filteredValues[Rhema_Form_Abstract::RETURN_URL_KEY] : '/';

						    $form->reset();
						    Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->gotoUrlAndExit($url);
						}
					}
				}
			}

			$return['form'] = $form;
			return $return;
		}
/*
		public function initMail($smtp =  null){
			$smtp = $smtp ? $smtp : 'mail.' .  Rhema_Util::getRegisteredHostname();
			ini_set("SMTP", $smtp);
		}

			*/
		public function openIdLoginMethod(){
			$loginForm      = new Rhema_Form_Login_OpenId();
			$redirectUrl    = '/my-account';
			$userClass	    = new Rhema_Widget_Controller_User();
			$sreg 			= $userClass->getRegData();

			if($loginForm->isRhemaButtonSubmitted()){
				if($loginForm->isValid($this->_request->getPost())){
					$values   = $loginForm->getValues();
					$consumer = new Rhema_OpenId_Consumer();
					$response = Zend_Controller_Front::getInstance()->getResponse();
					$status   = $consumer->login($values['openid_identifier'], $redirectUrl, null, $sreg, $response );
				}
			}

			$return['loginForm'] = $loginForm;
			return $return;
		}
	}