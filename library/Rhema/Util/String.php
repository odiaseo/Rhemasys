<?php 
class Rhema_Util_String extends Rhema_Util{
	public static $pattern;
	public static $replace;
	public static $memoryManager ;
	
	public static $stopWords = array(
		'and', 'or', 'is', 'the', 'with', 'by','a','up', 'at','be', 'in','on', 'for', 'in', 'of', 'off',
		'deal', 'amp', 'offer', 'within', 'this','why','was', 'you', 'yours', 'what', 'your','these', 'from',
		'co', 'uk', 'com', 'to', 'get', 'into', 'any', 'when', 'use', 'will', 'all', 'only', 'are', 'lots','no'
	);
 
	public static function buildAutocompleteObject($list, $searchTerm = '', $filter = 'product'){
		$products = array();
		if(Zend_Layout::getMvcInstance()){
			$view     = Zend_Layout::getMvcInstance()->getView();
		}else{
			$view     = new Zend_View();
		}
		foreach($list as $hit){
			$count = '' ;
			if(isset($hit['doc'])){
				$item = $hit['doc'];
			}else{
				$item = $hit;
			}
 
			if(isset($item['countid']) and !$item['countid']){
				continue ;
			}elseif(isset($item['countid'])){
				$count = " ({$item['countid']})";
			}
			
			$obj        = new stdClass();
			switch($filter){
				case 'retailer' :{	
					$url  = $view->url(array('retailer' => $item['slug']), 'affiliate-retailer', true);	
					break;
				}
				
				case 'category':{	
					$url  = $view->url(array('category' => $item['slug']), 'mobile-category', true);
					break ;
				}
				case 'brand':{	
					$url  = $view->url(array('brand' => $item['slug']), 'affiliate-brand', true);
					break ;
				}				
				case 'voucher':
				case 'product':
				default:{
					$url  = $view->url(array( 'title'	=> Doctrine_Inflector::urlize($item['title']),
	    											'id'	=> $item['id']
	    											),'affiliate-product-detail', true, false);					
				}	
			}		

			
			$obj->value = $searchTerm ? $searchTerm : $item['title'];
			$obj->url   = $url ;
			$obj->label = $item['title'] . $count;
			$products[] = $obj ;
		}

		return $products ;
	}
				
    public static function getInstance(){
        if(null === self::$_instance or !(self::$_instance instanceof Rhema_Util_String)){
            self::$_instance = new self();
            self::$memoryManager = self::getMemoryManager();
        }
        return self::$_instance;
    }	
    
	protected function __construct(){
		self::$pattern = array(			
			str_replace(DIRECTORY_SEPARATOR, '/', Rhema_Constant::getSiteRoot()),
			str_replace(DIRECTORY_SEPARATOR, '/', Rhema_Constant::getBackendPath()),
			str_replace(DIRECTORY_SEPARATOR, '/', Rhema_Constant::getPublicRoot())
		);
		
		self::$replace = array(			
			Rhema_SiteConfig::getStaticPath(),
			Rhema_SiteConfig::getBackendPath(),
			Rhema_SiteConfig::getDomainPath('admin')
		);
		//pd(self::$pattern, self::$replace);
	}
	
 
	public static function addressArrayToString($addressData){
		$data  = array(   $addressData['line1'] ,
								$addressData['line2'],
								$addressData['line3'],
								$addressData['city'],
								$addressData['state'],
								$addressData['post_code'],
								$addressData['region'],
								$addressData['country'],
							);
		$string = implode(', ', array_filter($data));		
		return $string;
	}
	
	public static function pluralise($word, $count = 1){
		return ($count > 1) ? $word . 's' : $word;
	}
	
	public static function stripWhiteSpaces(&$data){
		$data = trim($data);
		$data = preg_replace("/(\t|\r|\n)/", '', $data);
		return $data ;
	}
	
	public static function filterSearchTerms($terms){
		 return strtolower(preg_replace('/[^a-z0-9_\-\.\% ]+/i', '' , $terms));
	}
	
	public function directoryToUrl($path){
		$res = str_replace(self::$pattern, self::getReplace(), $path);
		return $res;
	}
	
	public function getImageSource($path, $root = ''){ 
		if($path and strpos($path, 'http') === false){
			$path    = str_replace('/globalmedia/', '/../media/', $path);
			$root    = $root ? $root : Rhema_Constant::getSiteRoot();
			$filter  = new Zend_Filter_RealPath(); 
			$path    = ltrim($path, '/');
			$absPath = $filter->filter($root . $path);
			$absPath = $absPath ? $absPath : $root . $path ;
			$absPath = str_replace(DIRECTORY_SEPARATOR, '/', $absPath);

			if(file_exists($absPath)){
				$img   = $this->directoryToUrl($absPath);
			}else{
				$img   = false;
			}
			return $img ;
		}else{
			return $path ;
		}
		
	}
		
	public static function shortenTitle($title, $length = 25){
		if(strlen($title) > $length){
			return substr($title, 0,  $length) . '...';
		}
		return $title ;
	}
	
	public static function removeStopWords($list, $unique = false){
		$list      = array_filter($list, array(new self(), "removeSingleChar"));
		$list      = array_diff($list, self::$stopWords); 
		array_walk($list, array(new self(), "cleanupTags")); 
		return $unique ? array_unique($list) : $list ;
	}
	
	public static function cleanupTags(&$val, $key){
		return trim(strtolower($val),'- ._\/');
	}
	
	public static function removeSingleChar($a){
		$len = strlen($a);
		return (!is_numeric($a) and $len > 1 and $len <= 25);
	}
	
	public static function prepareTitleForSlug($text){
		$text  = strip_tags(html_entity_decode($text));
		$text  = Rhema_Util_String::correctEncoding($text);
		$name  = str_replace( array('.com', '.co.uk', '.org'), '', $text);	
		$name  = preg_replace('/\b(payg|contracts|europe|and|limited|ltd|plc|co|uk|inc|other|inc\.|-uk|[^a-z0-9\-\_\s])\b/i', '', $name);
		$name  = preg_replace('/([^a-z0-9\-\_\s]+)$/i', '', $name);		
		return $name ;
	}
	
	
/*	public function processList(&$list, $str, $delimiter = ',', $unique = false){
 
		$tagList  = array_filter(explode($delimiter, $str));		
		$tagList  = self::removeStopWords($tagList, $unique);	
		if($tagList and is_array($tagList)){	
			self::buildList($list,$tagList);
		}		 
	}*/
	
	public static function buildList(array $arr){
		$count = count($arr);
		$list  = array();
		if($count){
			do{
				$tag    = array_shift($arr); 
				self::addToList($list, $tag) ; 
			 	
				$tok    = strtok($tag, " -.");
				while ($tok !== false) {
					self::addToList($list, $tok) ; 
				    $tok    = strtok(" -");
				}

				$count-- ;
			}	while($count > 0);
		}
		return $list ;
	}

	public static function addToList(&$list, $v){
		$v = trim($v, '. -');
		if($v and self::removeSingleChar($v) and !in_array($v, self::$stopWords)){
			$v = trim($v);
			if(isset($list[$v])){
				$list[$v]++;
			}else{
				$list[$v] = 1;
			}
		}
	}
	
	public static function getKeywords($data){
		$keywords   = array();
		$globalKeys = array('deal', 'offer', 'discount');
		
		$fields     = array('category_name', 
						  'category_path', 
						  'THIRDPARTYCATEGORY',
						  'TITLE',
						  'AUTHOR',
						  'ARTIST',
						  'MANUFACTURER',
						  'title',
						  'fabric',
						  'keywords',
						  'strapline',
					      'KEYWORDS',
						  'brand',
						  'isbn',
						  'code',	
						  'manufacturer',
						  'colour',
						  'program_name',
						  'merchant_name',
						  'network_promotion',
						  'merchant_category',
						  'product_name');
		
		foreach($fields as $fld){
			if(isset($data[$fld]) and $data[$fld]){  
				$str        = html_entity_decode($data[$fld]);
				$str        = preg_replace('/[^a-z0-9\-_\.\s\%]|\s{2,}|\b(for|and|or|the|with|you)\b/i', '', $str);
				$items      = explode(' ', $str);
				$items      = self::removeStopWords($items);
				$keywords   = array_merge($keywords, $items); 
			}
		}	
		
		$keywords = array_merge($keywords, $globalKeys);
		$keywords = array_flip(array_change_key_case(array_flip($keywords), CASE_LOWER));
		 
		$keywords = array_unique($keywords);
		shuffle($keywords);
		
		return count($keywords) ? implode(', ', $keywords) : '';
	}
	
	public static function correctEncoding($data) {
		$text 			 = '';
	    $currentEncoding = mb_detect_encoding($data, 'auto', true);
	    if(strtolower($currentEncoding) != 'utf-8'){
	    	$text = utf8_encode($data);
	    }
	    if(!$text){
	    	$text = $data;
	    }
	    return $text;
	}
	
	public static function getDelimiter($handle){
		$firstLine = fgets($handle)	;
		if(strpos($firstLine, '|') !== false){
			$delimiter = '|';
		}else{
			$delimiter = ',';
		}
		rewind($handle);

		return $delimiter ;
	}
	/**
	 * @return the $replace
	 */
	public static function getReplace() {
		return array(
			Rhema_SiteConfig::getStaticPath(),
			Rhema_SiteConfig::getBackendPath(),
			Rhema_SiteConfig::getDomainPath('admin')
		);		
	}

	
}