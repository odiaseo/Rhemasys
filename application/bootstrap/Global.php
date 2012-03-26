<?php   
if(!defined('ENTRY_POINT')){
	die('illegal access');
}
 
defined('APPLICATION_PATH' ) || define('APPLICATION_PATH', realpath(dirname(__FILE__ ) . '/../../application' ) );

$info    = pathinfo(ENTRY_POINT);
 
define('SITE_PATH', $info['dirname']);
define('SITE_DIR', basename($info['dirname']));
$thirdParty = realpath(APPLICATION_PATH . '/../thirdparty' );
set_include_path(implode(PATH_SEPARATOR, array(
                realpath(APPLICATION_PATH . '/../library' ),                
                $thirdParty. '/Zend' ,
                $thirdParty . '/Doctrine' ,
                $thirdParty . '/vendor' ,
                $thirdParty . '/WURFL',
                $thirdParty
                )
 ));


include ('Rhema/Constant.php');
Rhema_Constant::setInfo($info);


include_once 'Rhema/SiteConfig.php';
require_once 'Zend/Application.php';
 //var_dump($_SERVER['HTTP_USER_AGENT']); die();
 
if(isset($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'],Rhema_Constant::MOD_HEADER_KEY) !== false){
    $env = Rhema_Constant::DEV_ENV;
}else{
    $env = array_key_exists('APPLICATION_ENV', $_SERVER ) ? $_SERVER['APPLICATION_ENV'] : Rhema_Constant::PRD_ENV;
}
define('APPLICATION_ENV', $env );

include APPLICATION_PATH . '/../library/debug/debug_functions.php';

//define('APPLICATION_ENV', 'development' );
//var_dump(SITE_PATH . '/cached'); die();
$siteOptions = Rhema_SiteConfig::getInstance()->processConfigFiles(SITE_DIR );
$application = new Zend_Application(SITE_DIR, $siteOptions );
$autoloader  = Zend_Loader_Autoloader::getInstance();

//$autoloader->setDefaultAutoloader(array('Bootstrap', 'autoload') ); 
Zend_Registry::set('application', $application );

if(isset($_REQUEST['bypass'] ) and $_REQUEST['bypass']){
    $ajax = new Rhema_Ajax_Responce($_SERVER['REQUEST_URI'], $bootstrap );
    die($ajax->process($_SERVER['REQUEST_URI'] ) );
}
  
$application->bootstrap(array('session','cache'))
		    ->bootstrap()
            ->run();