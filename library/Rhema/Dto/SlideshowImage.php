<?php
class Rhema_Dto_SlideshowImage {
 
	protected $_href	 = '#';
	protected $_src      = '';
	protected $_title    = '';
	protected $_subtitle = '';

	public function __construct(array $params = null){
		foreach($params as $attr => $val){
			$method = 'set' . ucfirst($attr);
			if(method_exists($this, $method)){
				$this->$method($val);
			}
		}
	}		
 

	/**
	 * @return the $_href
	 */
	public function getHref() {
		return $this->_href;
	}

	/**
	 * @return the $_src
	 */
	public function getSrc() {
		return $this->_src;
	}

	/**
	 * @return the $_title
	 */
	public function getTitle() {
		return $this->_title;
	}

	/**
	 * @return the $_subtitle
	 */
	public function getSubtitle() {
		return $this->_subtitle;
	}
 

	/**
	 * @param field_type $_href
	 */
	public function setHref($_href) {
		$this->_href = $_href;
	}

	/**
	 * @param field_type $_src
	 */
	public function setSrc($_src) {
		$this->_src = $_src;
	}

	/**
	 * @param field_type $_title
	 */
	public function setTitle($_title) {
		$this->_title = $_title;
	}

	/**
	 * @param field_type $_subtitle
	 */
	public function setSubtitle($_subtitle) {
		$this->_subtitle = $_subtitle;
	}

	
}