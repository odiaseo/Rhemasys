<?php

class Rhema_Widget_Controller_Post extends Rhema_Widget_Abstract {

	public function indexMethod() {

	}

	public function summaryMethod() {

	}

	public function detailMethod() {
		$slug = $this->_request->getParam ( 'slug' );
		$return = array ();
		if ($slug) {
			$return ['post'] = $this->getCached ()->getPost ( $slug );
		}

		return $return;

	}

	public function categoryMethod() {

	}

	public function archiveMethod() {

	}

	public function latestpostMethod() {
		$number  		   = (int) Rhema_SiteConfig::getConfig('settings.events_to_show'); 
		$return ['latest'] = Rhema_Model_Service::factory('blog_post')->getBlogPosts ($number, null);
		$return['icon']    = Rhema_SiteConfig::getConfig('images.icon.feed');
		return $return;
	}
	
	public function reviewMethod(){
		$lim    = (int) Rhema_SiteConfig::getConfig('settings.events_to_show'); 
		$filter = new Rhema_Dao_Filter();
		$filter->setLimit($lim)
			   ->addOrderBy('rand')	
			   //->setDebug()
			   ->addJoin('User', Rhema_Dao_Filter::LEFT_JOIN, array('firstname', 'lastname'));
		
		$return ['latest'] = Rhema_Model_Service::factory('portfolio_comment')->findAll($filter);		 
		$return['icon']    = Rhema_SiteConfig::getConfig('images.icon.comment');
		return $return;		
	}
	
	
	public function getPost($slug) {
		return Admin_Model_BlogPost::getItem ( $slug );
	}
	

	public function listCommentsMethod(){
		$mFilter      = new Rhema_Filter_FormatModelName();
		$filter       = new Rhema_Dao_Filter();
		$itemsPerPage = Rhema_SiteConfig::getConfig('settings.reviews_per_page');
		$page         = $this->_request->getParam('page', 1);
		
		$filter->setLimit($itemsPerPage)
			   ->addOrderBy('created_at', Rhema_Dao_Filter::ORDER_DESC)	
			   ->setPage($page)
			   ->setModel($mFilter->filter('portfolio_comment'))
			   //->setDebug()
			   ->addJoin('User', Rhema_Dao_Filter::LEFT_JOIN, array('firstname', 'lastname')); ;
			   
		$return['paginator'] = Rhema_Model_Service::getPaginator($filter, $page);

		return $return ;
	}
}