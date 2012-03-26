<?php
/*

 Created on 27.08.2009 by Alexander Kaupp

 Version: 0.2.4

 Author: Alexander Kaupp aka tanila ( tanila at tanila dot org )

 Software License Agreement (BSD License)
 Copyright (c) 2009, tanila.de tanila.org
 All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following
   disclaimer in the documentation and/or other materials provided with the  distribution.
* Neither the name of tanila.de and tanila.org nor the names of its contributors may be used to endorse or promote products
   derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

 */

class Rhema_Cssmin_bak {
	public	$version = '0.2.4';
			// default config
	public	$_config = array(
				'suffix' => '-min',		// file suffix
				'showstat' => true,		// show stat
				'clevel' => 0,			// 0 = all lb, 1 = 1 line 1 rule
				'semicolon' => true,		// remove last semicolon
				'quotes' => true,		// remove optional quotes
				'zerounits' => true,		// remove units and sign from 0 values
				'zerodotvalues' => true,	// reduces 0.2 to .2
				'fontweight' => true,		// compress font-weight
				'backgroundshorthand' => false	// use bg-shorthands buggy atm thererore diabled
			);
			// output formatting
	public	$_fOut = array(
				"%s{%s}",
				"%s{%s}\n"
			);
			// todo: find @ rules
	public	$_regExes = array(
				'cssblock' => '/([0-9a-z\_\-\,\:\#\*\.\>\[\]\+\[\]\s]*){([0-9a-z\_\!\:\;\-\+\#\.\,\/\*\%\'\"\(\)\s=]*)}/ism' ,
				'comments' => '!/\*[^*]*\*+([^/][^*]*\*+)*/!'
			);
	public		$_sAtRules = array();
	public		$_sBytes = 0;
	public		$_time = 0;

public function   __construct($_filename='', $_config=array() ) {
	$this->setConfig($_config);
	if (!empty($_filename)) $this->minifyFile($_filename);
/*
	if ($this->_config['showstat']) {
		echo 'tanilacssmin results:'."\n";
		echo 'time: '.round($this->_time , 3) ." ms\n";
		echo 'saved: '.$this->_sBytes." byte\n";
	}
	*/
}
public function  setConfig($_config) {
	if (!empty($_config)) {
		$this->_config = array_merge($this->_config, $_config) ;
		if (abs( $this->_config['clevel']) > count($this->_fOut )-1 )
			 $this->_config['clevel'] = count( $this->_fOut ) -1;
	}
}
public function  minifyFile($_filename){
	$_css = $this->readFile($_filename);
	$_newfilename= $this->getNewFilename($_filename);
	$_cssNew = $this->minifyString($_css);
	$this->writeFile($_cssNew,$_newfilename);
}
public function  minifyString($_str){
	$t= microtime();
	$_str = strip_tags($_str);
	$_arr = $this->cssToArray($_str);

	$this->optimizeProperties($_arr);

	$_result = $this->arrayToCss($_arr);
	$this->_time = ( microtime() -  $t ) *1000;
	$this->_sBytes  =strlen($_str) - strlen($_result);
	return $_result;
}
public function  getNewFilename($_filename){
	$_fileExt = end(explode(".", basename($_filename) ));
	$_fileNoExt = str_replace('.'.$_fileExt,'',$_filename  );
	$_result = $_fileNoExt.$this->_config['suffix'].'.'.$_fileExt;
	if ($_result == $_filename ) die('input and output is the same file. Stopped ');
	return $_result;
}
public function  readFile($_filename) {
	if (empty($_filename) ) die ("Error: No css-file specified!\n\n");
	if (!file_exists($_filename)) die("Error: File: $_filename not found!\n\n");
	return  strip_tags(file_get_contents($_filename));
}
public function  writeFile($_str,$_filename) {
		$handle = @fopen($_filename, "w");
		if (!$handle) {
			fclose($handle);
			die("ERROR: Can not write to: $fn");
		}
		fwrite ($handle, $_str);
		fclose($handle);
}
public function  stripComments($_str){
	return  preg_replace($this->_regExes['comments'] ,'',$_str);
}
public function  stripMultipleSpaces($_str) {
	return preg_replace("/( +)/", ' ', $_str);
}
public function  stripWhitespace($_str){
	return str_replace(array("\n", "\r", "\t"),'', $_str);
}
public function  strip_quotes($_str){
	$f = array('("','(\'','")','\')');
	$t = array('(','(',')',')');
	return str_replace($f, $t, $_str);
}
public function  strip_zero_units($_str){
	return trim(eregi_replace('([^0-9])0(px|em|\%)', '\\10', ' ' . $_str));
}
public function  strip_zerodot_values($_str){
	return trim(eregi_replace('([^0-9])0\.([0-9]+)em', '\\1.\\2em', ' ' . $_str));
}
public function  compress_font_weight($_str){
	// normal is not safe atm font-weight, -variant
		$f = array('bold');
		$t = array('700');
	return str_replace($f, $t, $_str);
}
public function  cleanCssSelStr($_str){
	//$_str  = $this->stripMultipleSpaces($_str);
	$_str = trim($_str);
	$f = array(', ',' ,');
	$t = array(',',',');
	return str_replace($f, $t, $_str);
}
public function  cleanCssPropStr($_str){
		$_str = trim($_str);
		$f = array(': ',' :',', ',' ,',' ;','; ');
		$t = array(':',':',',',',',';',';');
		$_str = str_replace($f, $t, $_str);
		// not 4 data URLs

		if (!stristr($_str,'data:image/') && $this->_config['zerounits']==true )
			$_str = $this->strip_zero_units($_str);

		if ( $this->_config['zerodotvalues'] )
			$_str = $this->strip_zerodot_values($_str);
 		if ( $this->_config['semicolon'] )
			$_str = rtrim($_str,';');
		if ( $this->_config['quotes'] )
			$_str = $this->strip_quotes($_str);
		if ( $this->_config['fontweight'] )
			$_str = $this->compress_font_weight($_str);

	return $_str;
}
public function  cssToArray($_str){
	$_str = $this->stripComments($_str);
	$_str = $this->stripWhitespace($_str);
	$_str = $this->stripMultipleSpaces($_str);
	$_str = stripslashes($_str);
	//$_str = $this->strip_quotes($_str);

//"@import(.*?);"

	preg_match_all("/@.*?;/is" ,$_str ,$this->_sAtRules);
//	print_r($this->_sAtRules);

	$_matches = '';
	preg_match_all($this->_regExes['cssblock'] ,$_str ,$_matches);
	//print_r();

	$i =0;
	$_result = array();

	// cleaning selectors
	foreach( $_matches[1] as $_sel) {
		$_tmpselectors = $this->cleanCssSelStr($_sel);
		// removed double selector names sort asc
		$_result[$i]['selectors'] = array_unique( explode(',',$_tmpselectors) ) ;
		// sort selectors asc to find equal selector rules
		//asort($_result[$i]['selectors']);
		$i++;
	}

	$i =0;
	$_tmpStr = '';
	// cleaning property value pairs
	foreach( $_matches[2] as $_prop) {
		$_tmpStr = $this->cleanCssPropStr($_prop);

		// remove emty css rule
		if (empty($_tmpStr))
			unset($_result[$i]);
		else {

		// dataurl exception:
		// todo_ @import, @namespace, @charset, protect url(http://xyz.xyz)
		if ( stristr($_tmpStr,'data:image/') ) {
			$_result[$i]['propertiestring']=$_tmpStr;
		} else {
			$_result[$i]['propertiestring'] = '';


			$tmp = explode(';',$_tmpStr);
			foreach($tmp as $_kv) {

				// todo: url(http://asdfas.gif) problems
				$_keyvaluesplit=explode(':',$_kv);
					// todo: 1st shortcuts to long
					// arraykey = propertyname => results in automatic strip oberwritten properties
					if (!( $_keyvaluesplit[1]=='' )) {
						// force css properties to lower
						$_pKey = strtolower($_keyvaluesplit[0]);
						$_result[$i]['properties'][$_pKey] = $_keyvaluesplit[1];
					}
				}
			}	// not empty
		}	// not dataurl
			//print_r( $_result[$i] );
	//	}
		$i++;
	}

	return $_result;
}

/*
 * property array to property string
 */

public function  propToString($_arr){
	$_result = '';
	foreach($_arr as $_arrkey => $_arrval) {
		$_result .= $_arrkey.':'.$_arrval.';';
	}
	if ($this->_config['semicolon']) $_result = rtrim($_result,';');
	return $_result;
}

public function  arrayToCss($_arr){
	$_result = '';

	$_atGlue = ($this->_config['clevel'] >0 ) ? "\n" : '';
	$_result  .= implode($_atGlue,$this->_sAtRules[0]);
	$_result  .= $_atGlue;

	$_fstr = $this->_fOut[ $this->_config['clevel'] ];
	// loop all rules
	foreach($_arr as $_arrval){
		$_selectors  = implode(',', $_arrval['selectors']) ;
		// not empty means css rule is bypassed
		if (empty( $_arrval['propertiestring'] )) {
			$_propstr = $this->propToString($_arrval['properties']);
			$_result .= sprintf($_fstr , $_selectors,$_propstr);
		} else {
			$_result .=$_selectors.'{'.$_arrval['propertiestring'].'}';
		}
	}
	return $_result;
}

public function  optimizeProperties(&$_arrProp) {

	// rules loop
	foreach($_arrProp as $_arrkey => $_arrval){
	//$_selectors  = implode(',', $_arrval['selectors']) ;
	//$_propstr = $this->propToString($_arrval['properties']);
	//$_result[$i]['properties'] = $this->optimizeBackground($_arrval['properties']);
	//$_result[$_arrkey]['properties']
		$_properties = $_arrProp[$_arrkey]['properties'];
		if (!empty($_properties)) {
			if ($this->_config['backgroundshorthand']) 
				$_arrProp[$_arrkey]['properties'] = $this->optimizeBackground($_properties);
			//print_r($_arrProp[$_arrkey]['properties']);
		}
//die();
	}	// rules loop

	return $_arrProp;
}
public function  optimizeBackground(&$_aProperties){

	$_bgPropertyDefaults = array(
		'background' => '',
		'background-color' => 'transparent',
		'background-image' => 'none',
		'background-repeat' => 'repeat',
		'background-position' => 'top left',
		'background-attachment' => 'scroll'
	);

	$_bgProperties = array(
		'background' ,
		'background-color' ,
		'background-image' ,
		'background-repeat',
		'background-position',
		'background-attachment'

	);
// return if no BG-prop is in propArray
$_keys = $this->getPropIntersection($_bgPropertyDefaults,$_aProperties);
//$_keys = array_intersect($_aProperties, $_bgPropertyDefaults);
if ( !count($_keys) )
return $_aProperties;


// background with url?

$ok = false;

//$_aProperties[$key]
if ( array_key_exists('background-image',$_aProperties) ) $ok = true;
	else
if ( array_key_exists('background',$_aProperties) ) {
	$test = $this->explodeBackgroundProp($_aProperties['background']);
	if ($test['background-image'] !== 'none') $ok = true;
}

if (!$ok) return $_aProperties;



//if (!array_key_exists('background',$_keys) || !array_key_exists('background-image',$_keys) )  return $_aProperties;

	$_result['background-color'] ='transparent';
	$_result['background-image'] =  'none';
	$_result['background-repeat'] = 'repeat';
	$_result['background-position'] = '0% 0%';
	$_result['background-attachment'] = 'scroll';


	// BG-Prop-loop
	foreach($_keys as $key){
		$_current = $_aProperties[$key];

		//if($key=='background') $_result = array_merge( $_result , $this->explodeBackgroundProp($_current));

		if($key=='background') $_result = $this->explodeBackgroundProp($_current);

		if($key=='background-color') $_result['background-color'] = $this->findHexColor($_current,'transparent');
		if($key=='background-image')  $_result[ 'background-image'] = $this->findURL($_current,'none');
		if($key=='background-repeat') $_result[ 'background-repeat'] = $this->findBGRepeat($_current,'repeat');
		if($key=='background-position') $_result[ 'background-position'] = $this->findBGPosition($_current,'0% 0%');
		if($key=='background-attachment') $_result[ 'background-attachment'] = $this->findBGAttachement($_current,'scroll');

		//$_result = array_merge(   $_aProperties, $_result) ;

	//$this->explodeBackgroundProp($_aProperties[$key]);
	} // BG-Prop-loop
	// remove properties with browser-default values
	if ( $_result['background-color'] == 'transparent') unset($_result['background-color']);
	if ( $_result['background-image'] == 'none') unset($_result['background-image']);
	if ( $_result['background-repeat'] == 'repeat') unset($_result['background-repeat']);
	if ( $_result['background-position'] == '0% 0%') unset($_result['background-position']);
	if ( $_result['background-attachment'] == 'scroll') unset($_result['background-attachment']);


//	background:red url(image.png) repeat top left scroll;

unset( $_aProperties['background'] );
unset( $_aProperties['background-color'] );
unset( $_aProperties['background-image'] );
unset( $_aProperties['background-repeat'] );
unset( $_aProperties['background-position'] );
unset( $_aProperties['background-attachment'] );

	$_resVal =  array_values($_result);
	print_r(  $_resVal );
	$_result2['background'] =implode(' ',$_resVal);

	$_result2 = array_merge($_aProperties,$_result2);
	//if ( strlen( implode(' ', array_values($_result2))) >= strlen( implode(' ', array_values($_aProperties))) )
		//return $_aProperties;

	//if (!empty($_result2))
	return $_result2;

}

public function  explodeBackgroundProp($_sBgProp){
		$_result = array();
		$_result[ 'background-color'] = $this->findHexColor($_sBgProp,'transparent') ;
		$_result[ 'background-image'] = $this->findURL($_sBgProp,'none');
		$_result[ 'background-repeat'] = $this->findBGRepeat($_sBgProp,'repeat');
		$_result[ 'background-position'] = $this->findBGPosition($_sBgProp,'0% 0%');
		$_result[ 'background-attachment'] = $this->findBGAttachement($_sBgProp,'scroll');

//print_r($_result);
//die();
	return $_result;
}
// public function  compressBackGroundPosition()
// center center = center
// cente = 50%
// top left or left top = remove
// 0% 0% = remove

public function  findBGAttachement($_str,$_defaultVal=''){
	$_regExTextual = "/\s*(scroll|fixed)/is";
	$_matches = '';
	$mCnt = preg_match($_regExTextual,$_str, $_matches);
	if ($mCnt)
		return $_matches[0];
	else return $_defaultVal;
}

public function  findBGPosition($_str,$_defaultVal=''){
	// numerical position
	//$_regExNumerical = "/(\-?[0-9]+(px|em|%|pt|pc|in|mm|cm|ex)?){2}/is";
	$_regExNumerical = "/(\-?[0-9]+(px|em|%|pt|pc|in|mm|cm|ex)?)\s(\-?[0-9]+(px|em|%|pt|pc|in|mm|cm|ex)?)/is";
	$_matches = '';
	$mCnt = preg_match($_regExNumerical,$_str, $_matches);
	if ($mCnt) {
		return $_matches[0];
	} else {
//		$_regExTextual = "/(top|bottom|center)\s+(left|right|center)/is";
		$_regExTextual = "/(top|bottom|center|left|right)\s+(top|bottom|center|left|right)/is";
		$_matches = '';
		$mCnt = preg_match($_regExTextual,$_str, $_matches);

		if ($mCnt)
			return $_matches[0];
		else return $_defaultVal;
	}

}

// not safe atm: but works used only in bgimageprop parsing
public function  findBGRepeat($_str,$_defaultVal=''){
	if ( stristr($_str,'no-repeat')) return 'no-repeat';
	if ( stristr($_str,'repeat-x')) return 'repeat-x';
	if ( stristr($_str,'repeat-y')) return 'repeat-y';
	if ( stristr($_str,'repeat')) return 'repeat';
	return $_defaultVal;
}

public function  findURL($_str,$_defaultVal=''){
	$_regEx = "/url\([.\/:]+\)/is";
	$_matches = '';
	$mCnt = preg_match($_regEx,$_str, $_matches);
	if ($mCnt) {
		return $_matches[0];
	} else return $_defaultVal;

}
// returns valid hexcolors #000 or #000000
// illegal hex-color will not returned
public function  findHexColor($_str,$_defaultVal){
	$_result = $_defaultVal;
	$_regEx = "/#[0-9a-z]{3,6}/is";
	$_matches = '';
	$mCnt = preg_match($_regEx,$_str, $_matches);
	if ($mCnt) {
			$_result = (strlen($_matches[0]) == 4 || strlen($_matches[0]) == 7) ? $_matches[0] : $_defaultVal;
	}
	return $_result;
}

public function  getPropIntersection($_aDefinition,$_aProperties){
	return array_keys(  array_intersect_key($_aProperties,$_aDefinition) );
}

}	// class tanilacssmin

