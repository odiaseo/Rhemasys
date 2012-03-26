<?php 
class Rhema_Controller_Action_Helper_DisplayGrid extends Zend_Controller_Action_Helper_Abstract {

	protected $_urlParams = array(
			'module'     => 'admin',
			'controller' => 'grid',
			'action'     => 'table',
			'table'      => '');
	protected $_gridParam;

	public $baseHref;
	public $baseUrl;
	public $headerTags = array(
			'page_header_id',
			'page_footer_id',
			'page_body_id',
			'page_id');
 
	
	public function displayGrid($options = array(), $set = true){
		return $this->direct($options, $set);
	}
	
	public function direct($options = array(), $set = true){
		$ajax   = $this->getRequest()->isXmlHttpRequest();
		$margin = $this->getRequest()->getParam('gridMargin');
		$view	= Zend_Layout::getMvcInstance()->getView();
		$util	= Rhema_Util::getInstance();
		$req    = $this->getRequest() ;
		$table  = $req->getParam('table');
		  
		if(! isset($options['model'])){
			$options['model'] = $table;
		}

		if(preg_match('/(page|page_header|page_footer)/i', $table)){
			$view->formAction     = $view->url(array('table' => $table), 'layout-manager');
			$layEditor			  = new Rhema_Controller_Action_Helper_LayoutEditor();
			$view->layoutItems    = $layEditor->getPageItems();
		}

		if(! isset($options['editurl'])){
			$urlParams 			   = isset($options['urlParams']) ? $options['urlParams'] : array();
			$urlParams['rootType'] = $req ->getParam('rootType', '');
			$urlParams['table']	   = $table ; 
			
			$editUrl = $view->url($urlParams, 'grid-model-save');
			$options['gridParam']['editurl'] = $editUrl;
		}

		if($margin){
			$options['gridMargin'] = $margin ;
			//$options['gridParam']['width'] = $width;
		}
		//$options['gridParam']['shrinkToFit'] = true;
		//$options['gridParam']['forceFit'] = true;
		//$options['gridParam']['autowidth'] = true;
		
		$gridService = new Rhema_Grid_Service($options);
		$grid        = $gridService->setType('jqGrid')->generateGrid();
		$displayAjax = $req->getParam('ajx', null);

		$return = '';

		if($ajax){
			if($displayAjax){
				$script = $gridService->ajaxDespatch($grid);
				if($set){
					$util->setAjaxData($script);

				}else{
					$return = $script;
				}
			}else{
				$grid->deploy();
			}
		}else{
			$view->caption  = $grid->getJqgParam('caption', 'Grid Table');
			$view->gridData = $grid->deploy();
			$view->gridId   = $grid->makeGridId();
		}

		return $return;
	}
	/**
	 * @return the $_urlParams
	 */
	public function getUrlParams() {
		return $this->_urlParams;
	}

	/**
	 * @param field_type $_urlParams
	 */
	public function setUrlParams($_urlParams) {
		$this->_urlParams = $_urlParams;
	}

}