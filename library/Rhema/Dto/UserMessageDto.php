<?php
/**
 * Simple class to encapsulate messages sent to the user between actions
 * 
 * @author Pele Odiase
 *
 */
class Rhema_Dto_UserMessageDto
{
	
	/**
	 * Message Type
	 */
	const TYPE_ERROR = 'error';
	const TYPE_SUCCESS = 'success';
	const TYPE_WARNING = 'warning';
	
	/**
	 * @var String 
	 */
	private $_title;
	
	/**
	 * @var String 
	 */
	private $_message;
	
	/**
	 * @var String 
	 */
	private $_type;

	/**
	 * @var boolean
	 */
	private $_autoHide;
	
	public function __construct($message = '', $title = '', $type = self::TYPE_ERROR, $autoHide = true)
	{
		if(is_array($message)){  
			$str = '<ul class="usermsg-list">';
			foreach($message as $m){
				if(is_array($m)){
					$str .= '<li>' . print_r($m, true) . '</li>';
				}else{
					$str .= '<li>' . $m . '</li>';
				}			
			}
			$str .= '</ul>';
		}else{
			$str = $message ;
		}
		$this->setMessage($str);
		$this->setTitle($title);
		$this->setType($type);
		$this->setAutoHide($autoHide);
	}
	
	
	/**
	 *  Getter for Type
	 *
	 * @author Pele Odiase
	 * @since 4 Oct 2010 14:28:17
	 * @return String private variable Type
	 */
	public function getType() 
	{
	  return $this->_type;
	}
	
	/**
	 * Setter for Type
	 *
	 * @author Pele Odiase
	 * @since 4 Oct 2010 14:28:17
	 * @param String $value
	 * 
	 * @return UserMessage
	*/
	public function setType($value) 
	{
	  $this->_type = $value;
	  return $this;
	}
	
	/**
	 *  Getter for Message
	 *
	 * @author Pele Odiase
	 * @since 4 Oct 2010 14:28:01
	 * @return String private variable Message
	 */
	public function getMessage() 
	{
	  return $this->_message;
	}
	
	/**
	 * Setter for Message
	 *
	 * @author Pele Odiase
	 * @since 4 Oct 2010 14:28:01
	 * @param String $value
	 * 
	 * @return UserMessage
	*/
	public function setMessage($value) 
	{
	  $this->_message = $value;
	  return $this;
	}
	
	/**
	 *  Getter for Title
	 *
	 * @author Pele Odiase
	 * @since 4 Oct 2010 14:27:32
	 * @return String private variable Title
	 */
	public function getTitle() 
	{
	  return $this->_title;
	}
	
	/**
	 * Setter for Title
	 *
	 * @author Pele Odiase
	 * @since 4 Oct 2010 14:27:32
	 * @param String $value
	 * 
	 * @return UserMessage
	*/
	public function setTitle($value) 
	{
	  $this->_title = $value;
	  return $this;
	}
	/**
	 * @return the $_autoHide
	 */
	public function getAutoHide(){
		return $this->_autoHide;
	}

	/**
	 * @param boolean $_autoHide
	 */
	public function setAutoHide($_autoHide){
		$this->_autoHide = $_autoHide;
	}


}