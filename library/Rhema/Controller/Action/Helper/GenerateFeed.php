<?php
class Rhema_Controller_Action_Helper_GenerateFeed extends Zend_Controller_Action_Helper_Abstract{
	
	protected $_type = 'rss';
	
	public function generateFeed(){
		return $this;
	}
	
	public function affiliate(Rhema_Dto_FeedDto $dto, $type){
		
		$language  = Zend_Registry::get('Zend_Locale')->getLanguage();
		$generator = 'RhemaStudio Content Management System'; 
		$util      = Rhema_Util_String::getInstance(); 
		$view      = Zend_Layout::getMvcInstance()->getView();		
		$feed      = new Zend_Feed_Writer_Feed();
		$feedLink  = $dto->getLink() . $view->url();
		
		$title     = $dto->getTitle() ? $view->replaceMergeField($dto->getTitle()) : ' ';
		$desc      = $dto->getDescription() ? $view->replaceMergeField($dto->getDescription()) : ' ';
		 
		$feed->setTitle($title);
		$feed->setLink($dto->getLink());
		$feed->setFeedLink($feedLink, $this->getType());
		$feed->setLanguage($language);
		$feed->setDateModified(time());
		$feed->setDescription($desc);
		$feed->setGenerator($generator);
		$feed->setEncoding('UTF-8');
		
		foreach($dto->getItems()  as $item){
			$item    = isset($item['doc']) ? $item['doc'] : $item ;
			$crt     = isset($item['created_at']) ? strtotime($item['created_at']) : time();
			$mod     = isset($item['updated_at']) ? strtotime($item['updated_at']) : time();
			$content = '';
					
			if(!$item['id'] or !$item['title']) { 
				continue;
			}			
			switch($type){ 
				case 'category':{
					$url   = $view->url(array('category' => $item['slug']), 'mobile-category', true);
					break ;
				}
				
				case 'retailer':{
					$url   = $view->url(array('retailer' => $item['slug']), 'affiliate-retailer', true);				
					break;
				}
				
				case 'brand':{
					$url   = $view->url(array('brand' => $item['slug']), 'affiliate-brand', true);				
					break;
				}
				
				
				case 'manufacturer':{
					$url   = $view->url(array('manufacturer' => $item['slug']), 'affiliate-manufacturer', true);				
					break;
				}
							
				case 'promotion':{
					//$url   = $view->url(array('promotion' => $item['slug'], 'id' => $item['id']), 'affiliate-promotion', true);				
					//break;
				}				
				default:
				case 'vouchers':
				case 'top50':{
				 
					$title = Doctrine_Inflector::urlize($item['title']);
					$url   = $view->url(array('title' => $title, 'id' => $item['id']), 'affiliate-product-detail', true);
					$content = $item['network_promotion'] ;
					break;
				}	
				
			}
			
			$guid    = md5($url) ;
			$url     = $dto->getLink() .  $url;								
			$entry   = $feed->createEntry();
			$desc    = $util->correctEncoding(trim($item['description']));
			$desc    = ($desc and $desc != $item['title']) ? $desc  : 'N/A'; 
			$title	 = $title ? $util->correctEncoding($item['title']) : "";
			$title	 = $title ? $title : "Product Feeds";
			$content = $content ? $content : ' '; 
			
			$entry->setTitle($title);
			$entry->setLink($url);
			$entry->setId($guid);
			$entry->setDateModified($mod);
			$entry->setDateCreated($crt);
			$entry->setDescription($desc);
			 
			$entry->setContent($content);
			$feed->addEntry($entry);
		
		}
		
		$output = $feed->export($this->getType());
		
		return $output;		
	}
	
	public function rss(Rhema_Dto_FeedDto $dto){		 
		
		$language = Zend_Registry::get('Zend_Locale')->getLanguage();
		$generator = 'RhemaSys - Rhema Webdesign Management System'; 
		 
		$view     = Zend_Layout::getMvcInstance()->getView();		
		$feed     = new Zend_Feed_Writer_Feed();
		$feedLink = $dto->getLink() . $view->url(array(), 'blog-rss-feed');
		
		$title    = $view->replaceMergeField($dto->getTitle());
		$desc     = $view->replaceMergeField($dto->getDescription());
		
		$feed->setTitle($title);
		$feed->setLink($dto->getLink());
		$feed->setFeedLink($feedLink, $this->getType());
		$feed->setLanguage($language);
		$feed->setDateModified(time());
		$feed->setDescription($desc);
		$feed->setGenerator($generator);
		$feed->setEncoding('UTF-8');
		
		foreach($dto->getItems() as $item){
			$crt   = strtotime($item['created_at']);
			$mod   = strtotime($item['updated_at']);
			$url   = $dto->getLink() .  $view->url(array('slug' => $item['slug']), BLOG_ROUTE, true);
			$guid  = $url . '/' . $item['id'];			
			$entry = $feed->createEntry();
			$desc  = $item['description'] 
					 ? $item['description'] 
					 : $item['excerpt'] ;
					 
			$content = $item['content'] 
					 ? $item['content'] 
					 : ' ';
					 
			$user  = $item['User'];
			
			$entry->setTitle($item['title']);
			$entry->setLink($url);
			$entry->setId($guid);
			$entry->setDateModified($mod);
			$entry->setDateCreated($crt);
			$entry->setDescription($desc);
			$entry->addAuthor(array(
				'name'  => $user['firstname'] . ' ' . $user['lastname'],
			    'email' => $user['email'],
			    'uri'	=> $user['url']
			));
			$entry->setContent($item['content']);
			$feed->addEntry($entry);
		
		}
		
		$output = $feed->export($this->getType());
		
		return $output;
	}
	/**
	 * @return the $_type
	 */
	public function getType(){
		return $this->_type;
	}
	
	/**
	 * @param field_type $_type
	 */
	public function setType($_type){
		$this->_type = $_type;
		return $this;
	}

}