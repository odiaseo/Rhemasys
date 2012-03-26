<?php
class SetupController extends Zend_Controller_Action{

	public function indexAction(){
		$client  	= new Zend_Rest_Client(REST_SERVER);
		$domain     = $this->_utility->getRegisteredHostname();
		$dir        = SITE_DIR;
		$resp       = $client->getSetUpData(SITE_DIR, $domain)->get();
		$valid      = 0;

		if($resp->isSuccess()){
			$data   = $resp->getSetUpData;
			$valid  = (int) $data->registered;
		}

		if($valid){
			clearstatcache();

			$prefix = implode(DIRECTORY_SEPARATOR, array(realpath(APPLICATION_PATH . '/../'),
															 PUBLIC_DIR
														));
			foreach((array)$data->public_dir  as $item){
				$filename = $prefix . $item ;
				 if(!file_exists($filename )){
					mkdir($filename , 0777, true);
				}
			}

			foreach((array)$data->public_file  as $item){
				$filename = $prefix . $item ;
				$dir      = dirname($filename);

				if(!file_exists($dir)){
					mkdir($dir, 0777, true);
				}

				file_put_contents($filename, '');
			}

			$prefix = realpath(APPLICATION_PATH . '/../sites');
			foreach((array)$data->site_dir  as $item){
				$dirname = $prefix . $item ;
				 if(!file_exists($dirname)){
					mkdir($dirname, 0777, true);
				}
			}

			foreach((array)$data->site_file  as $item){
				$filename = $prefix . $item ;
				$dir      = dirname($filename);

				if(!file_exists($dir)){
					mkdir($dir, 0777, true);
				}

				file_put_contents($filename, '');
			}
		}
	}
}