<?php

	/**
	 * View helper plugin to display page contents
	 * depending on layout definitions
	 *
	 */
	class Rhema_View_Helper_DisplayContent extends Zend_View_Helper_Abstract {

		protected $_util;
		protected $_object;
		protected $_admin = 0;
		protected $_editMode = 0;
		protected $_memManager ;
		public static $sectionCount = 0;
		public $pageId;
		public $template_id;
		public $_front;
		public $params = array();
		public $contentTypes = null;
		public $siteDetails  = array();

		/**
		 * Retrieves a display item from the database base on item Id and type
		 * Type ccan be either component of admin element.
		 *
		 * @param integer $id   Item Id
		 * @param string $type Item type
		 * @param array $item Item is passed by reference
		 * @return string the content to be displayed in HTML
		 */
		public function getItem($id, $typeId, $layoutId){
			$content 		= '';
			$item			= array();
			$util	 		= Rhema_Util::getInstance();
			$formatted 		= $util->formatItemId('lay', $layoutId);
			$positionData	= $this->_object->itemList[$formatted] ;
			$type           = $this->contentTypes[$typeId]['code'];
			$theme          = Zend_Registry::get('site-theme') ;
			$cssFilter      = new Rhema_Filter_FormatCssClassName();

			switch($type){
				case 'affiliate_product_category':
				case (substr($type,-4) == 'menu'):{
					if(is_object($positionData['content'])){
						$item			= $positionData['content']->getModuleData();
						$navData 		= (object) $item ;
						$container		= $positionData['content']->getContainer();
						if($type == 'affiliate_product_category'){
							$class = $navData->title ;
						}else{
							$class = $navData->title . '-' . $type;
						}
						$class          = $cssFilter->filter($class);
						$menuTree  		= $this->view->navigation()													 
													 ->menu($container)
													 ->setUseTranslator(false)
			    	 				 				 ->setMinDepth(1)
			    	 				 				 ->setMaxDepth(1)
			    	 				 				 ->setUlClass("ul-$class")
			    	 				 				// ->setPartial('partials/menu-with-span.phtml','storefront')
			    	 								 ->render();
			    	 	$label      	= $navData->is_label ? '<h2>' . $navData->title . '</h2>' : '';
			    	 	$content   		= "<div id='$class' class='$class'>{$label}$menuTree</div>";
					}else{
						continue ;
					}
					break;
				}

				case (substr($type,-5) == 'crumb') :{
					$item			= $positionData['content']->getModuleData();
					$navData 		= (object) $item ;
					$container		= $positionData['content']->getContainer();
					$src            = $this->view->getImagePath()->icon('blue_arrow.gif')  ;
	    			$seperator  	= '<img src="' . $src. '" alt="seperator" style="margin:0 5px"/>';
 		 			$crumb 	  		=  $this->view->navigation() 		 										  
 		 										  ->breadcrumbs($container) 
 		 										  ->setSeparator($seperator)
 		 										  ->setUseTranslator(false)	 ;
 		 		    $class          = $cssFilter->filter($navData->title . '-' . $type);
 		 			$content   		= $crumb ? "<div class='$class breadcrumb'>$crumb</div>" : '';
 		 			//$item['title']  = $navData['moduleData']['title'];
					break;
				}

				case 'component' :{
					$content   		= stripslashes($positionData['content']);
					$content   		= $this->view->replaceMergeField($content);
					$item   		= $positionData['item'] ;
					$item['edit']   = true;
					break;
				}

				case 'web_form' :
				case 'admin_element' :
				default:{
					$item           = isset($this->_object->itemList[$formatted]['item']) ? $this->_object->itemList[$formatted]['item'] : 'Title not found';
 					if(isset($positionData['content']) and $positionData['content']){
						$this->view->assign($positionData['content']);
					}  
					$content		= isset($positionData['widgetView']) ? $this->view->render($positionData['widgetView']) : $positionData['content']; //$positionData;
				}
			}

 
			$return['html']   = $content;
			$return['data']   = $item ;
			$return['itemId'] = $id;

			return $return;
		}
 
		/**
		 * Builds the page layout based on its layout definition
		 * If an adminstrator is logged in, it builds the section tabs and
		 * pads the content with divs which identifies the various sections
		 * and page elements. This allows the adminstrator to move items in the front end (dnd).
		 *
		 * @param array $sections All page sections
		 * @param array  $layout   Page layout definition
		 * @param boolean $admin    Flag to switch on padding and lRayout tabs
		 * @return string Page content in HTML (string)
		 */
		public function displayContent($sections, $layout, $divId = ''){
			//$sectionCount = 0;
			$siteConfig         = Rhema_Util::getSessData(Rhema_Constant::SITE_CONFIG_KEY);
			$this->siteDetails  = $siteConfig['subsite'];
			$this->_util    	= Rhema_Util::getInstance();
			$this->_editMode    = $this->_util->getSessData('editmode',0);
			$this->_admin       = $this->_util->getSessData('admin',0);
			$this->_front		= Zend_Controller_Front::getInstance();
			$this->params		= $this->_front->getRequest()->getParams();
			$this->contentTypes = $this->contentTypes ? $this->contentTypes : Rhema_Model_Service::factory('admin_content_type')->getContentTypes();
			$this->_object		= Zend_Layout::getMvcInstance();
			//$this->_memManager  = Rhema_Util::getMemoryManager();


			$style     = !$this->_editMode  ? ' style="display:none" ' : '';
			$content   = $this->_admin  ? "<div class='sxnHolder clearfix rounded' id='sxnHolder-$divId'>" : '';
			if(is_array($sections)){
	            foreach($sections as $sxnData){
	            	$sxnContent  = '';
	            	$sxn   		 = $sxnData['AdminSection']['id'];
	            	$class 		 = $sxnData['AdminSection']['cssclass'];
	            	$sxnId		 = strtolower('sxn_' . $divId . '_' . $class . '-' . $sxn);
	            	$idString    = $this->_editMode ? "id= '$sxnId'" : '';
	            	$sxnContent .= "<div class='$class' $idString>";
	            	if($this->_admin){
	            		//$sxnContent .= $this->buildSectionTab($sxnData, $style);
	            	}
                 	if(isset($layout['items'][$sxn]) and count($layout['items'][$sxn])){
                 		foreach($layout['items'][$sxn] as $itemSeq => $items){
                    		foreach($items as $type => $combo){
                    			list($itemId, $layoutId) = explode('_',$combo);
                    			$divId       = "item_{$itemSeq}_{$type}-$itemId";
                    			$return      = $this->getItem($itemId, $type, $layoutId);
                                $sxnContent .=  $this->padContent($itemSeq, $layoutId, $type, $return, $style, $itemSeq);

                    		}
                 		}
                 	}
                 	$sxnContent .=  '</div>';
                 	$content    .= $sxnContent;
	            }
			}
            $content   .= $this->_admin ? '</div>' : ''; 

            return  $content;
		}

	     /**
	      * Builds a tab which is appended to every page sections. The tab consists of
	      * a black strip and information about the section. The is only shown isf the admin
	      * flag is switched. The logged in user must have the required priviledges
	      *
	      * @param array $sxnData        Row data of the current section
	      * @param boolean_type $admin   Admin flag
	      * @return string
	      */
	     public function buildSectionTab($sxnData, $style = '') {
	     	$arr     = $sxnData['AdminSection'];
	    	$tab     = '';
    		$tabid   = 'tab-'.$arr['id'];

        	$tab    .= "<div class='admin-tab toprnd movehandle' $style>";
       	 	$tab    .= "    <label class='admin-tab-label'>$arr[title]</label>
		       	 				<div id='$tabid' class='admin-tab-desc' style='display:none'>
		       	 					<span><label>ID:</label>$arr[id]</span>
		       	 					<span><label>Description:</label>$arr[description]</span>
		       	 					<span><label>CSS ID:</label>$arr[cssid]</span>
		       	 					<span><label>CSS Class:</label>$arr[cssclass]</span>
		       	 				</div>

       	 				</div>";

	        return $tab ;
	    }

		/**
		 * Pads each section item with a div and a top bar. This easily identifies the item
		 * and provides a handle for drag-n-drop
		 *
		 * @param integer $itemSeq  Sequence / order of the item within the section
		 * @param stringe $type     Item type. Can either be component of adminelement
		 * @param integer $itemId   Unique item identifier (it is unique by type)
		 * @param boolean $admin    Flag to switch on padding
		 * @return string HTML
		 */
	    public function padContent($itemSeq, $layoutId, $type, $arr, $style = '', $itemSeq = ''){
	    	$hideButtons  = $this->_editMode ? '' : 'style="display:none"';
	    	$itemData     = $arr['data'];
	    	$itemId       = $arr['itemId'];
	    	$id			  = $layoutId . '_' . $itemId;

	    	if($this->_admin){
	    		$hiddenEdit   = (isset($itemData['edit']) and  $itemData['edit']) ? '' : 'style="display:none"' ;
	    		$return = '<div class="wrap rounded">
	    					  <span  '. $hideButtons. ' class="editButtons"  id="'. $id . '">
	    					  		<ins class="sort-handle" title="drag to move page item"></ins>
	    							<ins class="edit" title="edit" '.$hiddenEdit.'></ins>
	    							<ins class="add" title="add"></ins>
	    							<ins class="remove" title="remove"></ins>
	    							<ins class="info" title="info">
	    								<span style="display:none" class="info-section rounded">
				       	 					<span><label>ID:</label>'.$itemId .' ('.$itemSeq.')</span>
				       	 					<span><label>Title:</label>'.$itemData['title'].'</span>
				       	 					<span><label>Description:</label>'.$itemData['description'].'</span>';
    								if(isset($itemData['widget'])){
    									$return  .= '<span><label>Widget: </label>'.$itemData['widget'].'</span>';
    								}
				       	 			$return  .= '<span><label>Item type:</label>'.$type.'</span>
				       	 				</span>
	    							</ins>
	    						</span>
	    						<span class="item-content">' . $arr['html']  . '</span>'
	    			      . '</div>';
	    	}else{
	    		$return =  $arr['html'];
	    	}

	    	return $return;
	    }

		public function padContentbk($itemSeq, $layoutId, $type, $arr, $style = '', $itemSeq = ''){
			$str       = $arr['html'] ; //$this->getItem($itemId, $type, $itemData,$layoutId);
			$itemData  = $arr['data'];
			$itemId    = $arr['itemId'];
			$padded    = '';
			$divId     = "item_{$itemSeq}_{$type}-$itemId";
			$divClass  = $this->_editMode ? 'editable-area' : 'preview-mode';

			if($this->_admin){
				$hidden    = (isset($itemData['edit']) and  $itemData['edit']) ? '' : 'style="display:none"' ;
				$padded   .= "<div id='$divId' class='lay-item ui-corner-all'>";
		        $padded   .= "<div  id='content_$type-$itemData[id]'>
								<div class='elmHeader movehandle ui-dialog-titlebar ui-corner-all layout-header ui-widget-header' $style>
									<label class='elm-tab-label'>$itemData[title]</label>
										<div id='label_$type-$itemId' class='elm-tab-desc ui-corner-bottom layout-header' style='display:none'>
				       	 					<span><label>ID:</label>$itemId ($itemSeq)</span>
				       	 					<span><label>Description:</label>$itemData[description]</span>
				       	 					<span><label>Item type:</label>$type</span>
				       	 				</div>

									<div class='elmButtons'>
		        						<a id='edit_{$layoutId}_$itemId' href='#' class='ui-corner-all'><span class='ui-icon ui-icon-pencil' $hidden title='Edit page content'>&nbsp;</span></a>
		        						<a id='del_{$itemId}_$layoutId' href='#' class='ui-corner-all'><span class='ui-icon ui-icon-trash' title='Delete item'>&nbsp;</span></a>
					            	</div>
				        		</div>";
		       $padded   .= '</div>';
			}
			$padded  .=  $this->_admin? "<div class='$divClass'>$str</div></div>" : $str;

			return $padded;
		}
	}

