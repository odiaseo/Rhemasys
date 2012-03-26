<?php
 

/**
 * Generates Tmx translation files from translation database for a locale
 * Saves this file locally
 * @author pele.odiase
 *
 */
class Rhema_Util_TmxGenerator{

      private $_dom ;
      const DEFAULT_LOCALE  = 'en_GB';
      const CLASS_CACHE_TAG = "tmxtranslation";
      public $headerArray  = array('creationtool'         => 'self-generated',
					'creationtoolversion'   => '1.0.0',
                    'datatype'			    => 'winres',
                    'o-tmf'					=> 'abc',
                    'segtype'			    => 'sentence'
            );
    public static $filename = array('route' => false, 'content' => false);

    public function __construct(){
        $implm    = new DOMImplementation();
        $dtd      = $implm->createDocumentType('tmx', "-//LISA OSCAR:1998//DTD for Translation Memory eXchange//EN", "tmx14.dtd");
        $dom      = $implm->createDocument('', '', $dtd);
        $dom->encoding = 'UTF-8';
        $dom->formatOutput = true ;
        $this->_dom = $dom;
    }

    /**
     * Returns the tmx filename for the current locale
     * @return string
     */
    public static function getTmxFilename($locale = false, $refresh = false, $type = 'content'){
        $locale  = $locale ? $locale : Zend_Registry::get('Zend_Locale')->toString();
        $xmlFile = self::getFilename($locale, $type);
        $filter  = new Zend_Filter_RealPath();
        
        try{
	        if($refresh or !file_exists($xmlFile)){        	
	            $me   = new self();
	            $data = Admin_Model_Translation::getLocaleTranslations($locale, $type);
	            $xml  = $me->buildTmx($data);
	            $xml->save($xmlFile);
	        }
        }catch(Exception $e){
        	if(Rhema_SiteConfig::isDev()){
        		pd($e->getMessage());
        	}
        }
        return $filter->filter($xmlFile);
    }
 
    /**
     * Builds the Xml document
     * @param unknown_type $data
     * @return DOMDocument
     */
    public function buildTmx($data){
        $tmx = $this->getUnit('tmx', '', array('version' => '1.4'));
        $this->_dom->appendChild($tmx);

        $header = $this->getUnit('header', '', $this->headerArray);
        $tmx->appendChild($header);

        $body   = $this->getUnit('body');

        $tmx->appendChild($body);

        foreach($data as $transKey => $transData){
        	$transKey   = Rhema_Util_String::correctEncoding(trim($transKey));
            $tu 		= $this->getUnit('tu', '', array('tuid' => $transKey));
            foreach($transData as $loc => $val){ 
            	$val      = Rhema_Util_String::correctEncoding(trim($val));
                $useCdata = strlen(strip_tags($val)) != strlen($val) ? true : false;
                $seg = $this->getUnit('seg', $val, array(), $useCdata);
                $tuv = $this->getUnit('tuv', '', array('xml:lang' => $loc));
                $tuv->appendChild($seg);
                $tu->appendChild($tuv);
            }
            $body->appendChild($tu);
        }

        return $this->_dom;
    }

    /**
     * Creates a Tmx translation unit
     * @param unknown_type $key
     * @param unknown_type $value
     * @param unknown_type $attrib
     * @param unknown_type $cdata
     * @return DOMElement
     */
    public function getUnit($key, $value = '', $attrib = array(), $cdata = false){
        $elm   = $this->_dom->createElement($key);
        if($value){
            if($cdata){
                $text  = $this->_dom->createCDATASection($value);
            }else{
                $text  = $this->_dom->createTextNode($value);
            }
            $elm->appendChild($text);
        }

        foreach($attrib as $k => $v){
            $par = $this->_dom->createAttribute($k);
            $chd = $this->_dom->createTextNode($v);
            $par->appendChild($chd);
            $elm->appendChild($par);
        }

        return $elm;
    }
	/**
     * @return the $filename
     */
    public static function getFilename ($locale = false, $type = 'content')
    {
        if(!isset(self::$filename[$type]) or !self::$filename[$type]){
            $locale         = $locale ? $locale : Zend_Registry::get('Zend_Locale')->toString();
            self::$filename[$type] =  realpath(APPLICATION_PATH . "/../data/languages/{$type}/") . "/{$locale}.tmx";
        }
        return self::$filename[$type];
    }

}