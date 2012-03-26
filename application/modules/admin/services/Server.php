<?php
	class Admin_Service_Server extends Rhema_Cache{
	    /**
	     * Add method
	     *
	     * @param Int $param1
	     * @param Int $param2
	     * @return Int
	     */
	    public function math_add($param1, $param2) {
	        return $param1+$param2;
	    }

	    /**
	     * Logical not method
	     *
	     * @param boolean $param1
	     * @return boolean
	     */
	    public function logical_not($param1) {
	        return !$param1;
	    }

	    /**
	     * Simple array sort
	     *
	     * @param Array $array
	     * @return Array
	     */
	    public function simple_sort($array) {
	        asort($array);
	        return $array;
	    }

	    public function test(){
	    	return 'osalobua';
	    }

	    public function getSetUpData($directory, $domain){
	    	$sqlPath     = realpath(APPLICATION_PATH . '/../doctrine/data/sql/setup.sql');
	    	$config      = $this->validate($domain, $directory);
	    	$_buildList  = array('registered' => 0);

	    	if($config){
				$_buildList  = array(
				    'registered'=> 1,
				    'domain'    => $domain,
				    'directory' => $directory,
					'public_dir'    =>
						array(
						 	 "/$directory/default/images/graphics",
						 	 "/$directory/default/images/icons",
						 	 "/$directory/scripts"
					),
					'public_file'    =>
						array(
							 "/$directory/default/css/style.css",
						     "/media/css/layout.css"
					),
					'site_dir'	=>  array(
					    "/$directory/cache/"
					),
					'site_file'	=>  array(
				 		"/$directory/configs/config.ini",
					),
					'database'      => array(
						'sql'       =>  file_get_contents($sqlPath),
						'truncate'	=>  array(
												'category',
												'page',
												'user',
					                            'role',
												'menu',
												'component',
												'admin_acl',
												'page_footer',
												'page_header',
					                            'web_form',
												'page_layout',
					                            'address_book'
										),
					    'remove'	=> array(
					    					'admin_subsite',
										    'admin_license',
										    'admin_subsite_license'
										)
					),

					'config'	   => $config
				);
	    	}

			return $_buildList;
	    }

	    /**
	     * Returns a registered site's details by looking up domain name and root directory
	     * @param string $domain
	     * @param string_type $directory
	     * @return array
	     */
	    public function validate($domain, $directory){
	    	$domain  = ($domain == 'rhemasys-dev') ? 'rhema-webdesign.com' : $domain;
	    	$data    = self::getSiteDetails('Admin_Model_AdminSubsite', $domain, $directory);
	    	return $data;
	    }


	    public  function listAllModules($class, $mode = Doctrine_Core::HYDRATE_ARRAY){
			return Doctrine_Core::getTable($class)->findAll($mode);
		}

		public function getSiteModules($model, $ssid){
			$query  = Doctrine_Query::create()
						->from("$model m")
						->leftJoin('m.AdminLicence l')
						->innerJoin('l.SubsiteLicence g')
						->leftJoin('m.AdminMenu x')
						->where('g.admin_subsite_id =?', $ssid)
						->andWhere('g.is_active =?', 1)
						->orderBy('m.sequence') 
						->setHyDrationMode(Doctrine_Core::HYDRATE_ARRAY);

			return $query->execute();
		}
		
		
		public static function getContentTypes(){
			$model  = __CLASS__;
		 
			$filter = new Rhema_Dao_Filter();
			$filter->setModel(MODEL_PREFIX . 'AdminContentType')
				   ->addJoin('AdminTable', Rhema_Dao_Filter::LEFT_JOIN, array('id', 'title', 'name'))
				   ->addOrderBy('sequence')
				   ->addFields(array('id', 'code', 'title', 'color')); 
			$query = Rhema_Model_Service::createQuery($filter);   
			
			$return  = $query->execute();
	 
			return $return ? $return : array();
		}
		
		public function getModuleContent($model, $module){
			$query  = Doctrine_Query::create()
						->from("$model e")
						->where('e.code =?', $module)
						->limit(1)
						->setHyDrationMode(Doctrine_Core::HYDRATE_ARRAY);

			$result = $query->execute();
			return count($result) ? $result[0] :array();
		}

	    public function getSiteLicences($model, $ssid){
	    	$filter     = new Rhema_Dao_Filter();
	    	
	    	$filter->setModel($model)
	    		   ->addJoin('SubsiteLicence')
	    		   ///->setDebug(true)
	    		   ->addJoin('LicencedModules', Rhema_Dao_Filter::INNER_JOIN)
	    		   ->addCondition('LicencedModules.id', $ssid); 
//pd(Rhema_Model_Service::createQuery($filter));
	    	$result = Rhema_Model_Service::createQuery($filter)->execute();
	    	
	    	return count($result) ? $result : array();
    	}

    	public function getElement($model, $id){ 
			$elem  = Doctrine_Core::getTable($model)->find($id, Doctrine_Core::HYDRATE_ARRAY);
			return $elem;    		
    	}
    	
		public function getAllElements($model){
	    	$filter     = new Rhema_Dao_Filter();	    	
	    	$filter->setModel($model)
	    		   ->addOrderBy('title'); 
			$result =  Rhema_Model_Service::createQuery($filter)->execute();

			return count($result) ? $result : array();
		}
		
		public static function getSiteDetails($model, $domain, $directory){
			$result    = array();  
			$domain    = strip_tags(trim($domain));
			$directory = strip_tags(trim($directory));
	
			if($domain and $directory){ 
				$filter     = new Rhema_Dao_Filter();	    	
	    		$filter->setModel($model)
	    			   ->addCondition('root_dir', $directory)
	    			   ->addCondition('is_active', 1)
	    			   //->setDebug(true)
	    			   ->addJoin('AddressBook', Rhema_Dao_Filter::LEFT_JOIN)
	    			   ->setLimit(1); 
							
				if(substr($domain,-4) != '-dev'){
					$filter->addCondition('domain', $domain); 
				}
				
				$result = Rhema_Model_Service::createQuery($filter)->fetchOne();		 
			}

			return count($result) ? $result : array();
		}
		
		public function getDefaultTableDetails($model){
			$result = array();
			
			if(is_array($model)){
				foreach($model as $m){
					$result[$m] = $this->_getRows($m);
				}
			}else{
				$result = $this->_getRows($model);
			}
			 
			return $result ;
		}
		
		private function _getRows($model){
			$result     = array();			
			try{
				$query  = Doctrine_Query::create()
							->from("$model m") 
							->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY) ;
							
				$result = $query->execute();			
			}catch(Exception $e){
				 
			}	

			return $result ;			
		}

		private function _updateTable(){
			$result         = $this->_helper->migrateDb();			 
		}
		
 		public function executeRemoteQuery($string){
			$result   = false;
 
			$filters  = unserialize($string);
			
			if($filters instanceof Rhema_Dao_Filter){			 
				$result = Rhema_Model_Service::createQuery($filter)->execute(); 
			}
			
			return $result;
		}
 
	}