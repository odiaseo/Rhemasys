<?php
class Rhema_Controller_Action_Helper_SetupMenuTab extends Zend_Controller_Action_Helper_Abstract {
	
	public function setupMenuTab(){
		return $this;
	}
	
	public function direct($table){		
		$request	 	= $this->getRequest();
		$view 			= Zend_Layout::getMvcInstance()->getView(); 
		$root_id 		= $request->getParam('root_id', 1);		
		$displayAjax 	= $request->getParam('ajx', null);
		$minDepth       = $request->getParam('minDepth', 0);
		 		
		$menuOption 	= array( 'root_id' => $root_id);
		$menuObject 	= Rhema_Model_Service::factory($table);
		
		$treeDto 		= $menuObject->getMenuTree($menuOption, $menuObject->getModelName()); 
		
		$view->menuUrl 		= $view->url(array( 'table' => $table), 'cms-menu-tree');
		$view->currentTable = $table; 
		$view->root_id 		= $root_id;
		$view->dto 			= $treeDto;
		$view->minimumDepth = $minDepth;
		
		$urlParm 	= array( 'table' => $table,  'root_id' => $root_id);	
		$gridParam 	= Rhema_SiteConfig::getConfig('grid.jqgrid.menu-tables');	 
		$gridParam['editurl'] 	= $view->url($urlParm, 'grid-model-save');  
		$options['gridParam'] 	= $gridParam;
		$options['model'] 		= $table;
		$options['gridMargin']  = 400;
		
		$gridService 	= new Rhema_Grid_Service($options);
		$grid 			= $gridService->setType('jqGrid')->generateGrid();
		$view->gridId 	= $grid->makeGridId();
		
		if($request->isXmlHttpRequest()){
			if($displayAjax){
				$str = $gridService->ajaxDespatch($grid, 'partials/menutree');
				Rhema_Util::setAjaxData($str);
			}else{
				$view->gridData = $grid->deploy();
			}
		}else{
			$grid->jqInit();
		}
	}
	
	/**
	 * This is the tree work horse. It is called via ajax when a menu is modified
	 * It ensures that menu items are stored in the correct hierarchy.
	 *
	 */

	public function updateTree(){
		$request 	= $this->getRequest();
		$task 		= $request->getParam('task', null);
 
		if($task){
			$filter 	= new Rhema_Filter_FormatModelName();
			$node 		= $request->getParam('node', null);
			$refNode 	= $request->getParam('refNode', null);
			$rootType 	= $request->getParam('rootType', null);
			$type 		= $request->getParam('type', null);
			$model 		= $request->getParam('table', null);

			$model = $filter->filter($model);
			$table = Doctrine_Core::getTable($model);

			if($node){
				$node_id = Rhema_Util::getIdFromNode($node);
				$nodeMenu = $table->find($node_id);
			}

			if($refNode){
				$ref_id = Rhema_Util::getIdFromNode($refNode);
				$refMenu = $table->find($ref_id);
			}

			switch($task){
				case 'create' :
					{
						$label = $request->getParam('nodeText');
						$row = new $model();
						$option = array(
							'title'	=> $label,
							'label'	=> $label  . rand(0,50),
						);
						$nodeMenu 			    = Admin_Model_AdminMenu::getDefaultRow($option, $model);
						$nodeMenu->m_controller = Zend_Controller_Front::getInstance()->getDefaultControllerName();
						$nodeMenu->state('TDIRTY');
						$nodeMenu->save();

						//$rootMenu   = $table->find($root_id);
						
						$slug    = isset($nodeMenu->slug) ? $nodeMenu->slug : false;
						$node_id = $nodeMenu->id;
						if('after' == $type){
							$nodeMenu->getNode()->insertAsNextSiblingOf($refMenu);
						}elseif('before' == $type){
							$nodeMenu->getNode()->insertAsPrevSiblingOf($refMenu);
						}elseif('inside' == $type){
							$nodeMenu->getNode()->insertAsLastChildOf($refMenu);
						}

						if($slug){
							$nodeMenu->slug = $slug;
							$nodeMenu->save();
						}
						
						break;
					}
				case 'move' :
					{
						$slug  = isset($nodeMenu->slug) ? $nodeMenu->slug : false;
						if('after' == $type){
							$nodeMenu->getNode()->moveAsNextSiblingOf($refMenu);
						}elseif('before' == $type){
							$nodeMenu->getNode()->moveAsPrevSiblingOf($refMenu);
						}elseif('inside' == $type){
							$nodeMenu->getNode()->moveAsLastChildOf($refMenu);
						}

						if($slug){
							$nodeMenu->slug = $slug;
							$nodeMenu->save();
						}
						break;
					}
				case 'rename' :
					{
						$label = $request->getParam('nodeText');
						$slug  = isset($nodeMenu->slug) ? $nodeMenu->slug : false;
						$nodeMenu->title = $label;
						$nodeMenu->label = $label;
						$nodeMenu->save();
						
						if($slug){
							$nodeMenu->slug = $slug;
							$nodeMenu->save();
						}
						break;
					}

				case 'delete' :
					{
						$nodeMenu->getNode()->delete();
						break;
					}
				default :
			}

			Rhema_Cache::clearCacheOnUpdate($model);
			$return = array('node_id' => $node_id);
			
			return $return ; 
		}
	}
}