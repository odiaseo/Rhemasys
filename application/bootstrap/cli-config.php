<?php


/**
 * CLI for cron jobs
 * e.g. php -f index.php -- --servername www.myvouchercodes.co.uk --action lucene
 *                          --params days=10&limit=100 --verbose
 * Use
 */
    //just ot avoid notices when in CLI, the isset on them will return same result anyway
    $_SERVER['HTTP_USER_AGENT'] = $_SERVER['REMOTE_ADDR'] = $_SERVER['REQUEST_URI'] = null;
  
    #Zend_Session::setOptions();
    // get CLI params
	$console = new Zend_Console_Getopt(
	    array(
            'verbose'         => 'Verbose mode (echo in console)', //no value, flag option
            'action=s'        => 'Cron task to perfom ( )',
	        'servername=s'    => 'The servername used to determin the subsite ID',
            'params-s'        => 'action parameters as key value pairs'
        )
    );
   
    try {
    	$console->parse(); 
    	if (!$console->action) {
    		echo $console->getUsageMessage();
    		die;
    	}
    } catch (Zend_Console_Getopt_Exception $e) {
    	echo $e->getUsageMessage();
    }
    
    define('CLI_VERBOSE', $console->verbose);