<?php

/**
 * DTO used to transfer feed metadata from action controllers to
 * the XML feed generator
 *
 * @author pele.odiase
 *
 */
class Rhema_Dto_FeedDto{
	
	/**
	 * Feed title
	 * @var string
	 */
	protected $_title = '';
	/**
	 * Feed description
	 * @var string
	 */
	protected $_description = '';
	/**
	 * Feed keyword
	 * @var string
	 */
	protected $_keywords = '';
	/**
	 * Muli-dimentional array or Zend_Paginator Object
	 * @var Zend_Paginator
	 */
	protected $_items = array();
	/**
	 * Feed output type (rss or atom)
	 * @var string
	 */
	protected $_feedType = '';
	/**
	 * Sites URL
	 * @var string
	 */
	protected $_link = '';
	
	public function __construct(){
	
	}
	/**
	 * @return the $_link
	 */
	public function getLink(){
		return $this->_link;
	}
	
	/**
	 * @param field_type $_link
	 */
	public function setLink($_link){
		$this->_link = $_link;
		return $this;
	}
	
	/**
	 * @return the $_feedType
	 */
	public function getFeedType(){
		return $this->_feedType;
	}
	
	/**
	 * @param field_type $_feedType
	 */
	public function setFeedType($_feedType){
		$this->_feedType = $_feedType;
		return $this;
	}
	
	/**
	 * @return the $_title
	 */
	public function getTitle(){
		return $this->_title;
	}
	
	/**
	 * @return the $_description
	 */
	public function getDescription(){
		return $this->_description;
	}
	
	/**
	 * @return the $_keywords
	 */
	public function getKeywords(){
		return $this->_keywords;
	}
	
	/**
	 * @param field_type $_title
	 */
	public function setTitle($_title){
		$this->_title = $_title;
		return $this;
	}
	
	/**
	 * @param field_type $_description
	 */
	public function setDescription($_description){
		$this->_description = $_description;
		return $this;
	}
	
	/**
	 * @param field_type $_keywords
	 */
	public function setKeywords($_keywords){
		$this->_keywords = $_keywords;
		return $this;
	}
	/**
	 * @return the $_items
	 */
	public function getItems(){
		return $this->_items;
	}
	
	/**
	 * @param Zend_Paginator $_items
	 */
	public function setItems($_items){
		$this->_items = (array) $_items;
		return $this;
	}

}