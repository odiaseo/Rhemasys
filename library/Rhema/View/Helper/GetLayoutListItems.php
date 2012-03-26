<?php
	class Rhema_View_Helper_GetLayoutListItems extends Zend_View_Helper_Abstract {
	
		public function getLayoutListItems($item, $arr = array()){
			$tips['action']				= 'tooltip';
			$tips['controller']			= 'design';
			$tips['module']				= 'cms';
     		$tipsUrl                	= $this->view->url($tips, ADMIN_ROUTE);
			$tips['action'] = 'layout';
			$aUrl    		= $this->view->url($tips, ADMIN_ROUTE); 
		 	$list			= '';
    		ksort($arr);
    		
	    	foreach($arr as $category => $data){
	    		$list              .= "<label>$category</label>";
	    		$param['uLClass']   =
	    		$param['prefix']    = "item_{$item}-";
	    		$param['rel']       = $tipsUrl . '/content_type_id/' . $item;
	    		$param['aClass']    = strtolower($item);
	    		$param['ulId']      = str_replace(' ','_', strtolower($item . '-' . $category));
	    		$param['aUrl']      = $aUrl;
	    		$list              .= $this->array2List($data, $category, $param);

	    	}
	    	return $list;
		}
		
	/**
	 * Converts an associative array into unordered list items
	 * Typically used to generate a list of layout items e.g. templates
	 * element list, components etc required when managing page layouts
	 *
	 * @param unknown_type $arr associative array
	 * @param unknown_type $param parameters to apply to the list items and hrefs
	 * @return html string of unordered list
	 */
	public static function array2List($arr, $category, $param = array()) {
		$ulClass 	= isset ( $param ['ulClass'] ) 	? $param ['ulClass'] 	: '';
		$textField 	= isset ( $param ['textField']) ? $param ['textField'] 	: 'title';
		$prefix 	= isset ( $param ['prefix'] ) 	? $param ['prefix'] 	: 'list-';
		$ulId 		= isset ( $param ['ulId'] ) 	? $param ['ulId'] 		: '';
		$rel 		= isset ( $param ['rel'] ) 		? $param ['rel'] 		: '';
		$aClass 	= isset ( $param ['aClass'] ) 	? $param ['aClass'] 	: '';
		$tipClass 	= isset ( $param ['tipClass'] ) ? $param ['tipClass'] 	: 'clue-tips';
		$aMore 		= isset ( $param ['aMore'] ) 	? $param ['aMore'] 		: '';
		$liMore 	= isset ( $param ['liMore'] ) 	? $param ['liMore'] 	: '';
		$ulMore 	= isset ( $param ['ulMore'] ) 	? $param ['ulMore'] 	: '';
		$aUrl 		= isset ( $param ['aUrl'] ) 	? $param ['aUrl'] 		: '#';
		$liClass 	= isset ( $param ['liClass'] ) 	? $param ['liClass'] 	: '';

		$str = '';
		if (count ( $arr )) {
			$str 	.= "<ul id='$ulId' class='$ulClass' $ulMore>";
			$cnt 	= 0;
			foreach ( $arr as $items ) {
				$items 	= is_object ( $items ) ? $items : ( object ) $items;
				$id 	= $items->id ? $items->id : $cnt;
				$text 	= ucwords ( $items->$textField );
				//$title = ('Breadcrumbs' == $category) ? "<span class='more-title'> - $category</span>" : '';


				if ('page' == $aClass) {
					//$temp    =  'template-' . $items->admin_template_id;
					$link = "<a href='$aUrl/template_id/$items->admin_template_id' class='$aClass'
							id='{$prefix}a-{$id}' title='$text' $aMore>$text</a>";
				} else {
					$link = $text; //. $title;
				}
				$childId = $prefix . $id;
				$str 	.= "<li id='$childId' class='$liClass' $liMore title='Click list icon to view more information'>
									<a href='#'  rel='$rel/id/$id' title='$text' class='$tipClass'><ins>&nbsp;</ins></a>$link
									<ins class='remove' style='display:none' title='#$ulId'>&nbsp;</ins>
							   </li>";
				$cnt ++;
			}
			$str .= '</ul>';
		}

		return $str;
	}		 
}