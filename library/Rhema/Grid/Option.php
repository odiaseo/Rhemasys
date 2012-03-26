<?php
class Rhema_Grid_Option implements IteratorAggregate{
	 
	public function getIterator(){
		$vars = get_class_vars(get_class($this));
		return new Rhema_Util_Iterator($vars);
	}
 
}