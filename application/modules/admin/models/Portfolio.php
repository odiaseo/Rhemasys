<?php

/**
 * Admin_Model_Portfolio
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Admin_Model_Portfolio extends Admin_Model_Base_Portfolio
{
    protected $_thumb = 'thumb';

	public function getPhotobookAlbums($page, $category, $keyword = '', &$album = null){
		$filter       = new Rhema_Dao_Filter();
		$searchFields = array('{title}', '{description}', '{keyword}','{Event.location}','{Event.content}');
		$itemsPerPage = Rhema_SiteConfig::getConfig('settings.items_per_page');

		if('download' == $category and $keyword){
			$items =  $this->getDownloadableItems($keyword, $itemsPerPage, $page, $album);
			return $items;
		}

		$filter->setLimit($itemsPerPage)
			   ->addOrderBy('Event.end_at', Rhema_Dao_Filter::ORDER_DESC)
			   ->addOrderBy('EventType.sort_order')
			   ->addOrderBy('start_at',Rhema_Dao_Filter::ORDER_DESC)
			   ->setPage($page)
			   ->addJoin('Event',Rhema_Dao_Filter::INNER_JOIN)
			   ->addJoin('Event.EventType', Rhema_Dao_Filter::INNER_JOIN)
			   ->setModel(__CLASS__)
			   //->setDebug()
			   ->addJoin('Role', Rhema_Dao_Filter::LEFT_JOIN);

		if('download' == $category)	  {
			$filter->addCondition('Event.is_sale', 1);
		}elseif(($category == 'search' or $category == 'location') and $keyword){
			$cond  = array();
			$words = array_filter(explode(' ', $keyword));
			$op    = Rhema_Dao_Filter::OP_LIKE ;
			$count = ord('z');
			foreach($words as $word){
				foreach($searchFields as $field){
					$holder         = ':' . chr($count);
					$arr[]          = " $field $op $holder ";
					$param[$holder] = "%{$word}%" ;
					$count--;
				}
			}
			$aField = '(' . implode(Rhema_Dao_Filter::OP_OR, $arr) . ')';
			$filter->addCondition($aField, $param, Rhema_Dao_Filter::WITH_PLACEHOLDERS);


		}elseif(strtolower($category) != 'all'){
			$filter->addCondition('Event.EventType.slug', $category);
		}
		//pd(Rhema_Model_Service::createQuery($filter)->execute());
		return Rhema_Model_Service::getPaginator($filter, $page);
	}

	public function getDownloadableItems($keyword, $maxResult, $page, &$album = null){
		$filter = new Rhema_Dao_Filter();
		$filter->addCondition('slug', $keyword)
			   ->setModel(__CLASS__)
			   ->addFields(array('album_dir', 'title', 'description'))
			   ->setLimit(1);

		$album = Rhema_Model_Service::createQuery($filter)->fetchOne();

		$list  = $this->listAlbumImages($album);

		return Rhema_Model_Service::getArrayPaginator($list, $maxResult ,$page);
	}

	public function listAlbumImages($album, $showThumbs = true){
		$list      = array();
		$request   = Zend_Controller_Front::getInstance()->getRequest();
		$resizer   = Zend_Controller_Action_HelperBroker::getStaticHelper('imageResizer');
		$root      = $request->getServer('DOCUMENT_ROOT');
		$dirPath   = $root . '/' . PHOTOBOOK_DIR . '/' . $album['album_dir'] ;
		$dirIter   = new DirectoryIterator($dirPath);
		$thumbDir  = $dirPath . '/' . $this->_thumb ;
		$thumbSize = Rhema_SiteConfig::getConfig('settings.portfolio_thumbsize');

		if(!file_exists($showThumbs and $thumbDir)){
		    @mkdir($thumbDir, 0755, true);
		}

		foreach ($dirIter as $file => $fileInfo){
			if($fileInfo->isDot() or $fileInfo->isDir()) continue;
			$filename  = $fileInfo->getPathname();
			$file      = $fileInfo->getFilename();
			$thumbFile = str_replace($file, $this->_thumb . '/' . $file, $filename);
			$useFile   = $showThumbs ? $thumbFile : $filename ;

			if(!preg_match('/(thumb)\.[a-z]{3,4}$/i', $filename) and preg_match('/\.(jpg|gif|png)$/i', $filename)){

    			if(!file_exists($thumbFile) and $showThumbs){
    			    $resizer->load($filename) ;
    			    list($w, $h) = explode(Rhema_Widget_Controller_Image::SIZE_SEPARATOR, $thumbSize);
    		    	$resizer->resizeToHeight($h) ;
    		    	$resizer->save($thumbFile,$resizer->getImageType());
    			}

				$list[] = str_replace(array($root, '\\'), array('', '/'), $useFile);
			}
		}

		return $list ;
	}

	public function getSaleEvents(){
		$daoFilter = new Rhema_Dao_Filter();
		$daoFilter->addJoin('Event', Rhema_Dao_Filter::INNER_JOIN, array('title', 'description' ,'start_at'))
				  ->addCondition('Event.is_sale', 1)
				  ->setModel(__CLASS__)
				  ->addOrderBy('Event.start_at', Rhema_Dao_Filter::ORDER_DESC)
				  ->addFields(array('photo_count', 'description', 'slug'));
		return Rhema_Model_Service::createQuery($daoFilter)->execute();
	}

	public function getAlbumDetails($value, $field = Rhema_constant::MENU_FRONTEND_KEY){
		$daoFilter = new Rhema_Dao_Filter();
		$daoFilter->addCondition($field, $value)
				   ->setModel(__CLASS__)
				   ->addJoin('PortfolioComment',Rhema_Dao_Filter::LEFT_JOIN, array('comment' ,'created_at', 'rating'))
				   ->addJoin('Event',Rhema_Dao_Filter::INNER_JOIN, array('location','content','start_at','end_at'))
				   ->addJoin('Event.EventType',Rhema_Dao_Filter::LEFT_JOIN, array('title','description'))
				   ->addFields(array('title', 'page_count', 'photo_count', 'slug', 'album_dir'))
				    //->setDebug()
				   ->addJoin('PortfolioComment.User',Rhema_Dao_Filter::LEFT_JOIN, array('firstname', 'lastname'))
				   ->setLimit(1);
		$result = 	Rhema_Model_Service::createQuery($daoFilter)->execute();

		return $result ? current($result)  : array();
	}

	public static function updatePortfolioSlug(){
		$filter = new Rhema_Dao_Filter();
		$filter->setModel(__CLASS__);
		$filter->setHydrationMode(Doctrine_Core::HYDRATE_RECORD);

		$albums = self::findAll($filter);

		foreach($albums as $item){
			$item->slug = Doctrine_Inflector::urlize($item['title']);
			$item->save();
		}

	}

	public function getRandomFeaturedItem(){
	    $filter = new Rhema_Dao_Filter();
	    $filter->addOrderBy('rand')
	           ->setLimit(1)
	           //->setDebug(true)
	           ->addFields(array('image_file', 'slug', 'title'))
	           ->addCondition('is_feature', 1)
	           ->addCondition('image_file', null, Rhema_Dao_Filter::OP_NOT_NULL)
	           ->setModel(__CLASS__);
	    $result = 	Rhema_Model_Service::createQuery($filter)->fetchOne();

	    return $result;

	}
}