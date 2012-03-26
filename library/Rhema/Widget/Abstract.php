<?php
	class Rhema_Widget_Abstract extends Rhema_Cache_Abstract{

		protected $_request ;
		protected $_utility;
		protected $_identityColumn;
		protected $_credentialColumn;
		protected $_siteConfig;
		protected $_apikey = 'ABQIAAAArvafpywJs5ZDwWOF00wwNxT30Mgbw2s7KFAKvLj8BP0iHIJQfBQc6NjnKzs0IvH_V32Nuw1WF4UmdQ';

		protected $_view;

		public function init(){
			parent::init();
			$namespace               = Zend_Registry::get('namespace');
			$this->_siteConfig       = $namespace->siteconfig;
			$this->_request          = Zend_Controller_Front::getInstance()->getRequest();
			$this->_utility  	     = Rhema_Util::getInstance();
			$this->_view			 = Zend_Layout::getMvcInstance()->getView();
			$this->_view->addThisUrl = Rhema_SiteConfig::getConfig('settings.socialmedia.addthis.href');
		}


		public function getAuthService() {
			if (Zend_Registry::isRegistered ( 'auth_server' )) {
				return Zend_Registry::get ( 'auth_server' );
			} else {
				$server = Admin_Service_Authenticate::getInstance();
				Zend_Registry::set ( 'auth_server', $server );
				return $server;
			}
		}
		public function removeCacheFiles(){
			if(!NO_CACHE){
				$cache     = Zend_Registry::get('pageCache');
				$cache->clean();
			}
		}


		/**
		 * Sets error message when error occurs when trying to send
		 * email
		 * @param unknown_type $form
		 */
		protected function _setSendErrorMessage($form){
			$type     = Rhema_Dto_UserMessageDto::TYPE_ERROR;
			$title    = 'Error sending email';
			$message  = 'An error occurred when sending you inquiry. Please send email to : ' . $this->_siteconfig['subsite']['contact_email'] . PHP_EOL;
			$message .= 'We are sorry for any inconvinience this may cause.';
			$this->_view->userMessage = new Rhema_Dto_UserMessageDto($message, $title, $type);
		}

		/**
		 * Sets error message retrieved from the form
		 * assigning the value to the view to be rendered in the layout
		 * @param unknown_type $form
		 */
		protected function _setFormError($form){
			$errors = $form->getMessages();
			$msg    = array();
			$title  = 'The following errors occurred: ' ;
			foreach((array)$errors as $field => $data){
				foreach((array)$data as $type => $m){
					$msg[] = ucwords($field) . ': ' . $m ;
				}
			}
			$this->_view->userMessage = new Rhema_Dto_UserMessageDto($msg, $title, Rhema_Dto_UserMessageDto::TYPE_ERROR, false);

		}

		/**
		 * Display form submission success message
		 * @param unknown_type $form
		 */
		protected function _setFormSuccessMessage($form){
			$message = 'Thank you for you inquiry. A member of staff will contact you shortly.';
			$type    = Rhema_Dto_UserMessageDto::TYPE_SUCCESS ;
			$title   = 'Email sent successfully';
			$this->_view->userMessage = new Rhema_Dto_UserMessageDto($message, $title, $type);
		}
	}