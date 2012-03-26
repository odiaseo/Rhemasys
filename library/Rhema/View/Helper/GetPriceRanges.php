<?php 

class Rhema_View_Helper_GetPriceRanges extends Zend_View_Helper_Abstract {
	
	public function getPriceRanges($diff, $min = 0, $numRange = 10, $inv = 10){
		$range    = array(); 
 		$inc      = ceil($inv/2) ;
 		$midPoint = floor($numRange/2);
 		$end      = 0;
 		for($i=$min; $i<$numRange ; $i++){	 			 			 			
 			if($i > $midPoint){
 				$inv  += $inc ;
 				$start = $end + $diff;	
 				$end   = $end + $inv;
 			}else{
 				$start  = $i*$inv ;	
 				$end    = $start + ($inv - $diff);
 			}
 			$range[] = array($start => $end);
 		}
 		return $range;		
	}
}