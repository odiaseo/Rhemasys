<?php /* This file encoded by Raizlabs PHP Obfuscator http://www.raizlabs.com/software */ ?>
<?php
	 
	/**
	 * View helper plugin to display page contents
	 * depending on layout definitions
	 *
	 */
	class Zend_View_Helper_DisplayContent extends Zend_View_Helper_Abstract {
	 
		public static $sectionCount = 0;
		public $pageId;
		public $template_id;
		public $_front;
		public $params = array();
		
		/**
		 * Retrieves a display item from the database base on item Id and type
		 * Type ccan be either component of admin element. 
		 *
		 * @param integer $id   Item Id
		 * @param string $type Item type
		 * @param array $item Item is passed by reference
		 * @return string the content to be displayed in HTML
		 */
		public function getItem($id, $type, &$item){
			$content 		= '';
			$opt     		= strtolower($type);	
			$widget	 		= false;	
			$menu    		= false;	
			$breadcrumb 	= false;	
			$params  		= $this->params;
			$util	 		= Rhema_Util::getInstance();
			
			switch($opt){
				case 'component':{
					$item    = Admin_Model_Component::getItem($id);
					$content = $item['content'];
					break;
				}
				case 'menu':{
					$table		   = 'menu';	
					$menu	       = true;	 
					break;
				}
				case 'ecomnavigationmenu':{ 
					$table		   = 'ecom_navigation_menu';	
					$menu	       = true;	 
					break;
				}
				case 'adminelement':{ 
					$item           = Admin_Model_AdminElement::getElement($id);						
					$module         = $item['module'];
					$controller     = $item['controller'];
					$action         = $item['action']; 
					$widget		   = true;
					//$content  = $layout->getView()->render($viewName);					
					//$content  = $this->view->action($item['action'], $item['controller'],$item['module'], $params);
					break;
				}
			}
			
			if($widget){
				$moduleDir      = $this->_front->getModuleDirectory($module);
				$module			= ucfirst($module);
				$controller		= ucfirst($controller) . 'Controller';
				$className		= $module . '_' . $controller;
				$filename		= $moduleDir . '/controllers/' . $controller . '.php'; 
				$method         = $action . 'Action';	
				
				if(!class_exists($className)){
					include_once($filename);
					$resp		= new Zend_Controller_Response_Http();	
					$class 		= new $className($this->_front->getRequest(),$resp, $this->params);
				} 
				 
				$content        = call_user_method( $method, $class , $params);   	
							
			}elseif($menu or $breadcrumb){ 
	    		$option        	= array('root_id' => $id); 
	    		$navData       	= $util->getCached($type)->getMainMenu($option, $table); 	
	    		$class          = strtolower($navData['roots'][$id]['title'] . '-' . $type);
	    		if($menu){    	
					$menuTree   = $this->view->navigation()->menu($navData['container'])
		    	 				 	->setMinDepth(1)
		    	 				 	->setUlClass("ul-$class")
		    	 					->render(); 
		    	 	  
		    	 	$content   	= "<div id='$class' class='$class'>$menuTree</div>";
	    		}else{ 
 		 			$crumb 	  	=  $this->view->navigation()->breadcrumbs($navData['container']); 
 		 			$content   	= "<div class='$class-breadcrumb'>$crumb</div>";  
	    		}
		    	 
			} 
			
			return $content;			
		}
		 
		/**
		 * Builds the page layout based on its layout definition
		 * If an adminstrator is logged, it builds the section tabs and 
		 * pads the content with divs which identifies the various sections 
		 * and page lements. This allows the adminstrator to move items in the front end.
		 *
		 * @param array $sections All page sections
		 * @param array  $layout   Page layout definition
		 * @param boolean $admin    Flag to switch on padding and layout tabs
		 * @return string Page content in HTML (string) 
		 */
		public function displayContent($sections, $layout){ 
			static $sectionCount;
			$admin    		= Rhema_Util::getSessData('edit_mode');
			$edit       	= Rhema_Util::getSessData('admin');				
			$this->_front	= Zend_Controller_Front::getInstance();
			$this->params	= $this->_front->getRequest()->getParams();
			 
			$sectionCount++;
			$content   = ($admin and $edit) ? "<div class='sxnHolder' id='sxnHolder-$sectionCount'>" : '';
			if(is_array($sections)){		
	            foreach($sections as $seq => $sxnData){ 
	            	$sxnContent  = '';
	            	$sxn   		 = $sxnData['admin_section_id']; 
	            	$class 		 = $sxnData['AdminSection']['cssclass'];	                
	            	$sxnContent .= "<div class='$class' id='div_sxn-$sxn'>";
	            	$sxnContent .= $this->buildSectionTab($sxnData, $admin);						  	
                 	if(isset($layout['items'][$sxn]) and count($layout['items'][$sxn])){                 		
                 		foreach($layout['items'][$sxn] as $itemSeq => $items){ 
                    		foreach($items as $type => $combo){
                    			list($itemId, $layoutId) = explode('_',$combo);
                                $sxnContent .= "<div id='item_{$layoutId}' class='lay-item'>"
                                		    .	$this->padContent($itemSeq, $type, $itemId, $layoutId, $admin)
                                 		    .  "</div>";
                    		}
                 		}  
                 		
                 	} 
                 	$sxnContent .=  '</div>';
                 	$content    .= $sxnContent;  
	            }
			}
            $content   .= $admin ? '</div>' : '';
            return $content;
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
	     public function buildSectionTab($sxnData,$admin = false) {
	     	$arr     = $sxnData['AdminSection'];
	    	$tab     = '';
	    	if($admin){
	    		
	    		$tabid   = 'tab-'.$arr['id'];
	        	$tab    .= "<div class='admin-tab toprnd movehandle'>";
	       	 	$tab    .= "    <label class='admin-tab-label'>$arr[title]</label>
		       	 				<div id='$tabid' class='admin-tab-desc' style='display:none'>
		       	 					<span><label>ID:</label>$arr[id]</span>
		       	 					<span><label>Description:</label>$arr[description]</span>
		       	 					<span><label>CSS ID:</label>$arr[cssid]</span>
		       	 					<span><label>CSS Class:</label>$arr[cssclass]</span>
		       	 				</div>
	       	 				</div>";
	    	}
	
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
		public function padContent($itemSeq, $type, $itemId, $layoutId, $admin = false){
			$str      = $this->getItem($itemId, $type, $itemData);			
			$padded   = '';	
					
			if($admin){
				$hidden    = ('adminelement' == strtolower($type)) ? 'style="display:none"' : '';
		        $padded   .= "<div  id='content_$type-$itemData[id]'>
								<div class='elmHeader movehandle'> 
									<label>$itemData[title] </label>
									<div id='label_$type-$itemId' class='admin-tab-desc' style='display:none'>
			       	 					<span><label>ID:</label>$itemId</span>
			       	 					<span><label>Description:</label>$itemData[description]</span>
			       	 					<span><label>Item type:</label>$type</span>
			       	 				</div>
									<div class='elmButtons'>		        						
		        						<a id='edit_$itemId' href='#'><span class='ui-icon ui-icon-pencil' $hidden title='Edit page content'></span></a>
		        						<a id='del_$layoutId' href='#'><span class='ui-icon ui-icon-trash' title='Delete item'></span></a>
					            	</div>
				        		</div>";	            			
			}
			$padded  .=  $admin ? "$str</div>" : $str;

			return $padded;		
		}
		
		public function __call($method, $args){
			if('Action' == substr($method, -6)){
				$this->_forward('page');
			}
		}
	}
      
 