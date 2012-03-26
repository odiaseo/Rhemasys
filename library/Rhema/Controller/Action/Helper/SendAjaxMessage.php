<?php 
class Rhema_Controller_Action_Helper_SendAjaxMessage extends Zend_Controller_Action_Helper_Abstract {
		
	public function direct($message, $title = null, $type = Rhema_Dto_UserMessageDto::TYPE_WARNING, $autoclose = null){
		$autoclose     = ($autoclose === null)						 
						 ? (($type == Rhema_Dto_UserMessageDto::TYPE_SUCCESS)? true : false)
						 :  $autoclose ;
		$userMessage   = new Rhema_Dto_UserMessageDto($message, $title, $type, $autoclose);
		$helper        = new Rhema_View_Helper_PrintUserMessage();
		$return        = $helper->printUserMessage($userMessage);

		$this->getResponse()->setBody($return)
						->sendResponse();
		exit();
	}

	public function sendAjaxMessage($message, $title = null, $type = Rhema_Dto_UserMessageDto::TYPE_WARNING, $autoclose = null){
		return $this->direct($message, $title, $type, $autoclose);		
	}	
}