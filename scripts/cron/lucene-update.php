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
		$flag     = $tempDir . "/lucene-cron-{$dir}.txt";
		$logFile  = "/var/www/vhost/rmszend/sites/{$dir}/logs/cron.log";	
		
		if(file_exists($flag)){
			div("$flag found") ;
			$startmsg = $dir . ' update is running started @ ' . file_get_contents($flag) . "\n" ;
			file_put_contents($logFile, $startmsg, FILE_APPEND);
			echo "\n$startmsg\n";
		}else{	
			$msg = 'lucene-update : ' . date('r') . ' : started  ............ ' ;
			file_put_contents($logFile, $msg, FILE_APPEND);
					
			if(@chdir("$publicPath/$dir")){
				file_put_contents($flag, $currentTime);
				printf("\nUpdating %s .... ", $dir); 
				$cmd  = array("php index.php --servername $domain --action lucene --params=\"task=update&limit=150\"{$verbose}",
							  "php index.php --servername $domain --action lucene --params=\"task=optimise\"{$verbose}",
							  "php index.php --servername $domain --action lucene --params=\"task=sync\"{$verbose}"
				);		
				foreach($cmd as $c){
					file_put_contents($logFile, " >> $c", FILE_APPEND);
					printf("\nExecuting %s ... ", $c);
					passthru($c,$return); 
					$return = (array) $return ;
					printf("\n%s", current($return) );
				}
				$end    = time();
				$dur    = $end - $start ;
				$endMsg = sprintf("%s completed in %s sec \n", $dir, $dur);
				echo $endMsg;
				
			 	@unlink($flag);
			}else{
				$endMsg = "$dir does not exist";
				print("\n$endMsg\n\n");
			}
			
			file_put_contents($logFile, "$endMsg \n" , FILE_APPEND);
		}		
	}
		
 	$end = time();
 	$dur = $end - $start ;
 	printf("\n\nUpdate completed! @ %s - %ss\n", date('r'), $dur);