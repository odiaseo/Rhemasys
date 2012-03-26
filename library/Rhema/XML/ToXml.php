<?php
/*

  XML Methods
  --------------------------------------------------------------
    XmlToArray
      Usage: $xml = new XmlToArray(xmldata(string),ignorefields(array 1,2,3),replacefields(array OLD => NEW),show attributes?,put attribs in seperate array)
         $array = $xml->Return();

    ArrayToXml
      Usage: $array = new ArrayToXml()
         $xml = $array->Return();
  --------------------------------------------------------------
*/

    class Rhema_Xml_ToXml {
    public  $_data;
    public  $_name = Array();
    public  $_rep  = Array();
    public  $_parser = 0;
    public  $_ignore,$_err,$_errline,$_replace,$_attribs,$_parent;
    public  $_level = 0;
    
	
	public function __construct( &$data, $replace = Array(), $attribs = Array() ) {
		$this->_attribs = $attribs;
		$this->_replace = $replace;
		$this->_data = $this->_processArray( $data );
	}
	
	public function & getXML() {
		return $this->_data;
	}
	
	public function _processArray( &$array, $level = 0, $parent = '' ) {
		//ksort($array);
		$return = '';
		foreach ( (array) $array as $name => $value ) {
			$tlevel = $level;
			$isarray = false;
			$attrs = '';
			
			if ( is_array( $value ) && ( sizeof( $value ) > 0 ) && array_key_exists( 0, $value ) ) {
				$tlevel = $level - 1;
				$isarray = true;
			}
			elseif ( ! is_int( $name ) ) {
				if ( ! isset( $this->_rep[$name] ) )
					$this->_rep[$name] = 0;
				$this->_rep[$name]++;
			}
			else {
				$name = $parent;
				if ( ! isset( $this->_rep[$name] ) )
					$this->_rep[$name] = 0;
				$this->_rep[$name]++;
			}
			
			if ( ! isset( $this->_rep[$name] ) )
				$this->_rep[$name] = 0;
			
			if ( isset( $this->_attribs[$tlevel][$name][$this->_rep[$name] - 1] ) && is_array( $this->_attribs[$tlevel][$name][$this->_rep[$name] - 1] ) ) {
				foreach ( (array) $this->_attribs[$tlevel][$name][$this->_rep[$name] - 1] as $aname => $avalue ) {
					unset( $value[$aname] );
					$attrs .= " $aname=\"$avalue\"";
				}
			}
			if ( $this->_replace[$name] )
				$name = $this->_replace[$name];
			
			is_array( $value ) ? $output = $this->_processArray( $value, $tlevel + 1, $name ) : $output = htmlspecialchars( $value );
			
			$isarray ? $return .= $output : $return .= "<$name$attrs>$output</$name>\n";
		}
		return $return;
	}
    }