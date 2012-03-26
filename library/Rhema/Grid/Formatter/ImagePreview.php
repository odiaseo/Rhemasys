<?php
 
class Rhema_Grid_Formatter_ImagePreview extends Bvb_Grid_Formatter_Image
{
 
    public function format ($src)
    {
    	$attrs      = '';
    	$util       = Rhema_Util_String::getInstance();      	    
        $filename   = $util->getImageSource($src); 
        
        if ( count($this->_options) > 0 ) {

            foreach ( $this->_options as $key => $value ) {
            	if('src' == $key) continue;
                $attrs .= " {$key} = \"$value\" ";
            }

        }
 
        return $filename ? "<img  src=\"$filename\" $attrs>" : $src;
    }
}