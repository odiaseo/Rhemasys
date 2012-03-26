<?php
 class Rhema_Dao_FilterCondition {
 	
 	protected $_field;
 	protected $_value;
 	protected $_operator;
 	
 	const OP_EQUAL = '=';
 	const OP_GT    = '>';
 	const OP_LT    = '<';
 	
 	public function __construct($field, $value, $operator = self::OP_EQUAL){
 		$this->_field    = $field;
 		$this->_value    = $value;
 		$this->_operator = $operator;
 	}
	/**
	 * @return the $_operator
	 */
	public function getOperator() {
		return $this->_operator;
	}

	/**
	 * @param field_type $_operator
	 */
	public function setOperator($_operator) {
		$this->_operator = $_operator;
	}
	/**
	 * @return the $_field
	 */
	public function getField() {
		return $this->_field;
	}

	/**
	 * @return the $_value
	 */
	public function getValue() {
		return $this->_value;
	}

	/**
	 * @param field_type $_field
	 */
	public function setField($_field) {
		$this->_field = $_field;
	}

	/**
	 * @param field_type $_value
	 */
	public function setValue($_value) {
		$this->_value = $_value;
	}


 }