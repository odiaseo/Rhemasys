<?php 

function getCommandList($type, $domain, $verbose){
	
	 $commands['lucene']     = array( "php index.php --servername $domain --action lucene --params=\"task=update\"{$verbose}",
								      "php index.php --servername $domain --action lucene --params=\"task=optimise\"{$verbose}",
								      "php index.php --servername $domain --action lucene --params=\"task=sync\"{$verbose}" 
	 );
	 	 
	 $commands['buildcache']  = array("php index.php --servername $domain --action feed --params=\"task=create-cache&reset=1\"{$verbose}" 	 );
	 
	 $commands['expired']     = array("php index.php --servername $domain --action feed --params=\"task=update-expired\"{$verbose}"	 );	
	  	 
	 $commands['clearcache']  = array( 
								  "php index.php --servername $domain --action cache --params=\"task=all\"{$verbose}", 
	 					          "php index.php --servername $domain --action cache --params=\"task=flush-html\"{$verbose}",
	 					          "php index.php --servername $domain --action feed --params=\"task=create-cache\"{$verbose}"
	 );
	 
	 $commands['html']  = array("php index.php --servername $domain --action cache --params=\"task=html\"{$verbose}");
	 $commands['tag']   = array("php index.php --servername $domain --action tag {$verbose}");
	 	 
	 $commands['feed']    = array("php index.php --servername $domain --action feed --params=\"task=download\"{$verbose}",
								  "php index.php --servername $domain --action feed --params=\"task=generate-sql&force=1\"{$verbose}",
								  "php index.php --servername $domain --action feed --params=\"task=import\"{$verbose}",
								  "php index.php --servername $domain --action metadata --params=\"task=merge-category\"{$verbose}",
								  "php index.php --servername $domain --action metadata --params=\"task=merge-retailer\"{$verbose}",
								  "php index.php --servername $domain --action metadata --params=\"task=merge-manufacturer\"{$verbose}",
	 							  "php index.php --servername $domain --action tag {$verbose}" 
	 );
	 
	$commands['vouchers'] = array("php index.php --servername $domain --action feed --params=\"task=download&force=1&codes=1\"{$verbose}",
								  "php index.php --servername $domain --action feed --params=\"task=generate-sql&force=1&codes=1\"{$verbose}",
								  "php index.php --servername $domain --action feed --params=\"task=import&codes=1\"{$verbose}",
								  "php index.php --servername $domain --action cache --params=\"task=vouchers\"{$verbose}",
								 // "php index.php --servername $domain --action metadata --params=\"task=merge-category\"{$verbose}",
								 // "php index.php --servername $domain --action metadata --params=\"task=merge-retailer\"{$verbose}",
								 // "php index.php --servername $domain --action metadata --params=\"task=merge-manufacturer\"{$verbose}" 
	 );	 
 	 return isset($commands[$type]) ? $commands[$type] : array();
}