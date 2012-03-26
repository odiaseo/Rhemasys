#!/usr/bin/php
<?php 
/*
 * Update expired codes clear cache files
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
	echo "\nStarted @ " . $currentTime;
	
	foreach($toProcess  as  $dir => $domain){
		$flag     = $tempDir . "/update-cron-{$dir}.txt";	
		if(file_exists($flag)){
			div("$flag found") ;
			echo "\n{$dir} update is running started @ " . file_get_contents($flag) , "\n";
		}else{			
			if(@chdir("$publicPath/$dir")){
				file_put_contents($flag, $currentTime);
				printf("\nUpdating %s .... ", $dir); 
				$cmd  = array("php index.php --servername $domain --action feed --params=\"task=update-expired\"{$verbose}",
							  "php index.php --servername $domain --action cache --params=\"task=all\"{$verbose}",
							  "php index.php --servername $domain --action feed --params=\"task=create-cache&reset=1\"{$verbose}",
							  //"php index.php --servername $domain --action lucene --params=\"task=update\"{$verbose}",
							  //"php index.php --servername $domain --action lucene --params=\"task=sync\"{$verbose}"
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