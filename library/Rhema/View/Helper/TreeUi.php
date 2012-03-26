<?php
class Rhema_View_Helper_TreeUi extends Zend_View_Helper_Abstract {
 
    /**
     * Render a nested array as a set of nested <ul>s.
     *
     * @param array|instanceof Iterator $list
     * @return string
     */
    public function treeUl($list)   {
        $output = '';
        if (is_array($list) || $list instanceof Iterator) {
            if (count($list) > 0) {
                $output = "<ul>\n";
                foreach ($list as $item) {
                    $output .= "\t<li>";
                    if(is_string($item)) {
                        $output .= $this->_view->escape($item);
                    } else {
                        $output .= $this->treeUl($item);
                    }
                    $output .= "</li>\n";
                }
                $output .= "</ul>\n";
            }
        }
        return $output;
    }
    
    public function displayListItem($text){
    	return '<li>' . $text . '</li>';
    }
    
    public function buildTree($tree){
    	
    }
}
