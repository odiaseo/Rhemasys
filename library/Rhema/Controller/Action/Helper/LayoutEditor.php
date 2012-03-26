<?php
/**
 * Action helper used for editing page layouts
 * @author Pele
 *
 */
class Rhema_Controller_Action_Helper_LayoutEditor  extends Zend_Controller_Action_Helper_Abstract{
	
	const DEFAULT_CATEGORY = 'Miscellaneous';
	protected $_modelService ;
	protected $_itemsByTypeCode = array(); 
	
	public function __construct(){
		$this->_modelService = new Rhema_Model_Service();
	}
	
	public function direct(){
		return $this;
	}
	
    /**
     * This method returns the commonly used variables required for building
     * a pagelayout.
     *
     * @param unknown_type $getList
     * @return unknown
     */
	
	public function getTemplateDetails($templateId){
		$result   			= array();
		$tempModel          = $this->_modelService->factory('template');
		$tempSxn            = $this->_modelService->factory('template_section');
		$sections			= array();
		$sxnOrder           = array();
		 
		$data 				= $tempModel->getActiveSections($templateId);
		foreach((array) $data[$templateId]['TemplateSections'] as $item){ 
			$seq              = $item['sequence'];
			$sxnId            = $item['AdminSection']['id'];
			$sections[$sxnId] = $item['AdminSection'];
			$sxnOrder[$seq]   = $sxnId;
		}
		$result['template']    = $tempModel->getTemplateById ($templateId, Doctrine_core::HYDRATE_ARRAY);
		$result['sections']    = (array) $sections;
		$result['nosection']   = "No template section";
		$result['sectionOrd']  = $sxnOrder;
//pd($result);		
		return $result;
	}
	
	protected function _categoriseList($data, $category = array(), $field = null, $code = 'component'){
		$return      = array(); 
		$useDefault  = ($field and count($category)) ? false : true;
		foreach($data as $id => $item){
			if($useDefault == false){
				$categoryId = $item[$field];
				$categoryTitle = $category[$categoryId]['title'];
			}else{
				$categoryTitle = self::DEFAULT_CATEGORY ;
			}
			$return[$categoryTitle][$id] = $item;
    		$contentTypeId        = isset($this->_typeList['bycode'][$item]) ? $this->_typeList['bycode'][$item] : 0;
    		$this->_itemsByTypeCode[$code][$id]   = $categoryTitle;			
		}
		ksort($return);
		return $return;
	}
    public function getPageItems(){ 
		$mService        = new Rhema_Model_Service(); 
		$view		     = Zend_Layout::getMvcInstance()->getView();
     	$oMenu           = $mService->factory('admin_menu');  
     	$adminCat 		 = $mService->factory('admin_category')->getCategory();
     	$userCat 		 = $mService->factory('category')->getCategory();
     	$elements        = $mService->factory('admin_element')->getAllElements();
     	$components      = $mService->factory('component')->getComponents();  
     	$typeList        = $mService->factory('admin_content_type')->getContentTypes() ;	
     	$menuRoots	     = $oMenu->getRoots('menu');
     	$catRoots	     = $oMenu->getRoots('affiliate_product_category');
     	$ecomRoots	     = $oMenu->getRoots('ecom_navigation_menu');
     	 	
      	$result = array('items' =>	array(
							     		$typeList[1]['id'] => $this->_categoriseList($elements, $adminCat, 'admin_category_id', $typeList[1]['code']),
							      	    $typeList[2]['id'] => $this->_categoriseList($components, $userCat, 'category_id', $typeList[2]['code']),							     	 
						     			$typeList[4]['id'] => $this->_categoriseList($menuRoots, array(), null, $typeList[4]['code']),
						     			$typeList[5]['id'] => $this->_categoriseList($ecomRoots, array(), null, $typeList[5]['code']),							     	 
						     			$typeList[3]['id'] => $this->_categoriseList($menuRoots, array(), null, $typeList[3]['code']),
						     			$typeList[6]['id'] => $this->_categoriseList($ecomRoots, array(), null, $typeList[6]['code']),
						     			$typeList[9]['id'] => $this->_categoriseList($catRoots, array(), null, $typeList[9]['code']),
						     			$typeList[10]['id'] => $this->_categoriseList($catRoots, array(), null, $typeList[10]['code'])						     			
							     	),
			     		'types'  => $typeList ,
						'bycat'	 => $this->_itemsByTypeCode ,
					    'tipurl' => $view->url(array(), 'layout-tooltip')		     	    	
			     	);
			     	//pd($result['items']);
    	return $result;    	
    }
    
    public function getItems(){
    	$view		    = Zend_Layout::getMvcInstance()->getView();
    	$util		    = Rhema_Util::getInstance();
		$mService       = new Rhema_Model_Service();
		
     	$adminCat 		= Admin_Model_AdminCategory::getCategory();
     	$userCat 		= Admin_Model_Category::getCategory();
     	$elements       = Admin_Model_AdminElement::getAllElements();
     	$components     = Admin_Model_Component::getComponents();
     	$contentTypes   = $util->getCached()->getContentTypes();

     	$oMenu          = $mService->factory('menu');
     	
     	$result['AdminElement'] 	= $elements ;
     	$result['Component'] 		= $components ;
     	$menuRoots	             	= $oMenu->getRoots('Admin_Model_Menu');
		//$ecomRoots					= Admin_Model_AdminMenu::getRoots('Ecom_Model_EcomNavigationMenu');

		$result['Menu'] 					 = $menuRoots; 
	//	$result['EcomNavigationMenu']  		 = $ecomRoots;
		$result['MenuCrumb'] 				 = $menuRoots;
	//	$result['EcomNavigationMenuCrumb']   = $ecomRoots;
 
		foreach($result as $item => $data){
			foreach($data as $index => $arr){
				$byPass  = false;
    			switch($item){
    				case 'AdminElement':
    					$categoryId = $arr['admin_category_id'];
    					$global     = $adminCat;
    					break;
    				case 'Menu':
    				case 'EcomNavigationMenu':{
    					$byPass     = true;
    					$catTitle   = 'Menus';
    					break;
    				}
     				case 'MenuCrumb':
    				case 'EcomNavigationMenuCrumb':{
    					$byPass     = true;
    					$catTitle   = 'Breadcrumbs';
    					break;
    				}
    				default:
    					$categoryId = $arr['category_id'];
    					$global     = $userCat;
    					break;
    			}

    			$contentTypeId      = isset($contentTypes['bycode'][$item]) ? $contentTypes['bycode'][$item] : 0;
    			$catTitle   		= $byPass ? $catTitle : $global[$categoryId]['title'];
    			
    			$result['type-id'][$contentTypeId]        = $data;
    			$byCat[$contentTypeId][$catTitle][$index] = $arr;
			}
		}
 
		$result['bycat']			= $byCat ;
		$result['content-type']		= $contentTypes; 

    	return $result;
    }
}