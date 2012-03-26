<?php 
	class Rhema_Form_Login_OpenId extends Rhema_Form_Abstract{
		
		public function init(){
			$this->setMethod('post');
			$googleEndpoint = Rhema_SiteConfig::getConfig('settings.openid.google');
			 
			$this->addElement('radio', 'openid_identifier', array(
				'multiOptions'	=> array($googleEndpoint => 'Google')
			));
			
			$this->addElement( $this->getSubmitButton('Login')); 
		}
	}
