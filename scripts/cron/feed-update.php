#!/usr/bin/php
<?php 
/*
 * Update expired codes and search index
 */
 	ini_set('display_errors', 'off');
 	$sitePath   = realpath(__DIR__ . '/../site-list.php');
 	$siteList   = require_once ($sitePath);
 	$verbose    = ''; 	
 	$publicPath = '/var/www/vhost/public_html';
 	$tempDir    = sys_get_temp_dir();
 
	if(count($argv) > 1){
		$dir = $argv[1];
		if(isset($siteList[$dir])){
			$toProcess[$dir] = $siteList[$dir];
			$verbose = isset($argv[2]) ? ' --verbose' : '';
		}else{
			die("\n$dir does not exist\n\n");
		}
	}else{
		$toProcess = $siteList ;
	}
	
	$currentTime = date('r');
	$start       = time(); 	
	echo "\n\nStarted @ " . $currentTime;
	
	foreach($toProcess  as  $dir => $domain){
		$flag     = $tempDir . "/update-cron-{$dir}.txt";	
		if(file_exists($flag)){
			echo "\n{$dir} feed update is running started @ " . file_get_contents($flag) , "\n";
		}else{			
			if(@chdir("$publicPath/$dir")){
				file_put_contents($flag, $currentTime);
				printf("\nUpdating %s .... ", $dir); 
				$cmd  = array("php index.php --servername $domain --action feed --params=\"task=download&force=1\"{$verbose}",
							  "php index.php --servername $domain --action feed --params=\"task=generate-sql&force=1\"{$verbose}",
							  "php index.php --servername $domain --action feed --params=\"task=import\"{$verbose}",
							  "php index.php --servername $domain --action metadata --params=\"task=merge-category\"{$verbose}",
							  "php index.php --servername $domain --action metadata --params=\"task=merge-retailer\"{$verbose}",
							  "php index.php --servername $domain --action metadata --params=\"task=merge-manufacturer\"{$verbose}",	
							  //"php index.php --servername $domain --action feed --params=\"task=create-cache&reset\"{$verbose}",
							  //"php index.php --servername $domain --action lucene --params=\"task=drop-index\"{$verbose}"	,
							  //"php index.php --servername $domain --action lucene --params=\"task=create\"{$verbose}",		
							  //"php index.php --servername $domain --action lucene --params=\"task=update&limit=550\"{$verbose}"							  
				);		
				foreach($cmd as $c){
					
					printf("\nExecuting %s ... ", $c);
					passthru($c,$return); 
					$return = (array) $return ;
					printf("\n%s", current($return) );
				}
				$end = time();
				$dur = $end - $start ;
				printf("%s completed - %ss", $dir, $dur);
			 	@unlink($flag);
			}else{
				print("\n$dir does not exist \n\n");
			}
		}		
	}
		
 	$end = time();
 	$dur = $end - $start ;
 	printf("\n\nUpdate completed! @ %s - %ss\n", date('r'), $dur);