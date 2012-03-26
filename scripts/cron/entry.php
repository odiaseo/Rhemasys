#!/usr/bin/php
<?php 
/*
 * Update expired codes clear cache files
 */
	$currentDir = dirname(__FILE__);
	require_once $currentDir  . '/commands.php';
	include_once $currentDir  . '/../../library/debug/debug_functions.php';
	
 	ini_set('display_errors', 'off');
 	$sitePath   = realpath($currentDir  . '/../site-list.php');
 	$siteList   = require_once ($sitePath);
 	$verbose    = ''; 	
 	$publicPath = realpath($currentDir . '/../../../public_html');
 	if(!file_exists($publicPath)){
 		$publicPath = realpath($currentDir  . '/../../public');
 	}
 
 	$tempDir    = sys_get_temp_dir();
 	$aCount     = count($argv);
 	 
	if($aCount > 2){
		$type = $argv[1];
		$dir  = $argv[2];
		if(isset($siteList[$dir])){
			$toProcess[$dir] = $siteList[$dir];
			$verbose = isset($argv[3]) ? ' --verbose' : '';
		}else{
			die("\n$dir directory does not exist\n\n");
		}
	}elseif($aCount > 1){
		$type = $argv[1];
		$toProcess = $siteList ;
	}
	
	$currentTime = date('r');
	 			
	foreach($toProcess  as  $dir => $domain){
		$start    = time();
		$flag     = $tempDir . "/{$dir}-cron.txt";
		$logFile  = realpath($currentDir . "/../../sites/{$dir}/logs/") . '/cron.log';	
 
		if(file_exists($flag)){
			div("$flag found") ;
			$startmsg = "\n   ** $currentTime | $dir | $type : cron is still running started by " . file_get_contents($flag) ;
			file_put_contents($logFile, $startmsg, FILE_APPEND);
			echo "\n >> $startmsg\n";
			exit;
		}else{	
			echo "\nStarted $dir | $type | $verbose @ " . $currentTime;	
			$msg = "$type cron started @ $currentTime"  ;
			file_put_contents($logFile, "\n$msg", FILE_APPEND);
					
			if(@chdir("$publicPath/$dir")){
				div(" >> $flag created") ;
				file_put_contents($flag, "$type @ $currentTime");
				printf("\n >> updating %s .... \n", $dir); 
				$cmd  = getCommandList($type, $domain, $verbose);  
				foreach($cmd as $c){
					$time = date('H:i:s');
					file_put_contents($logFile, "\n >> $time => $c", FILE_APPEND);
					printf("\n >> executing %s ... \n", $c);
					passthru($c,$return); 
					$return = (array) $return ;
					printf("\n%s", current($return) );
				}
				$end    = time();
				$dur    = ($end - $start) ;
				if($dur > 3600){
					$str = number_format(($dur/3600), 2) . ' hrs';
				}elseif($dur > 60){
					$str = number_format(($dur/60),2) . ' mins';
				}else{
					$str = $dur . ' sec';
				}
 
				$endMsg = sprintf("completed in %s\n", $str);
				echo $endMsg;
				
			 	@unlink($flag);
			}else{
				$endMsg = "$publicPath/$dir does not exist";
				print("\n >> $endMsg\n\n");
			}
			
			file_put_contents($logFile, "\n$endMsg" , FILE_APPEND);
		}		
	}
		
 	$end = time();
 	$dur = $end - $start ;
 	printf("\n\nCompleted! @ %s - %ss\n", date('r'), $dur);