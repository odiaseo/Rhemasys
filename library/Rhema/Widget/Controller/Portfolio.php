<?php
class Rhema_Widget_Controller_Portfolio extends Rhema_Widget_Abstract {
    const SOUND_KEY = 'SOUND_KEY';
    
    public function photobookMethod(){
    	
        $keyword = $this->_request->getParam('keyword', '' );
        $page = $this->_request->getParam('page', 1 );
        $category = $this->_request->getParam('category' );
        $portfolio = new Admin_Model_Portfolio();
        $model = Rhema_Model_Service::factory('event_type' );
        $searchForm = new Rhema_Form_Search_Simple();
        $hasError = false;
        $album = array();
        $url = $this->_view->url(array('category' => 'search'), 'portfolio-search' );
        $searchForm->setAction($url )->setMethod('post' );
        
        if($searchForm->isRhemaButtonSubmitted()){
            if($searchForm->isValid($this->_request->getPost() )){
                $keyword = $searchForm->getValue('keyword' );
                $category = 'search';
            }else{
                $this->_setFormError($searchForm );
                $category = 'all';
                $keyword = "";
                $hasError = true;
            }
        }
       // $this->_view->includeCss('scripts/formdesigner/css/jquery.formdesigner.css' );

        $return['paginator']  = $hasError ? array() : $portfolio->getPhotobookAlbums($page, $category, $keyword, $album );
        $return['searchForm'] = $searchForm;
        $return['photobook']  = $album;
        $return['category']   = $category;
        $return['keyword']    = $keyword;
        $return['docRoot']    = $this->_request->getServer('DOCUMENT_ROOT' );
        $return['audioFile']  = $this->_getAudioFile($return['docRoot'] );
        return $return;
    }
    
    public function listEventTypeMethod(){        
        $model = Rhema_Model_Service::factory('event_type' );
        $portfolio = new Admin_Model_Portfolio();
        $return['eventTypes'] = $model->listEventTypesWithAlbumCount();
        $return['saleItems'] = $portfolio->getSaleEvents();
        $return['locations'] = Rhema_Model_Service::factory('event' )->listEventLocationsWithAlbumCount();
        return $return;
    }
    
    public function albumPreviewMethod(){
    	$album     = $this->_request->getParam('album');
    	$portfolio = Rhema_Model_Service::factory('portfolio');
    	$albumData = $portfolio->getAlbumDetails($album);
    	
    	$imageList = $portfolio->listAlbumImages($albumData, false); 
    	
    	$return = array(
    		'prevWidth'		=> 580,
    		'prevHeight'	=> 380,
    		'imageList'	    => $imageList,
    		'albumData'	    => $albumData
    	);
   // pd($albumData);	
    	return $return;
    }
    
    protected function _getAudioFile($root = ''){
        $file = Rhema_Util::getSessData(self::SOUND_KEY );
        $song = '';
        if(! $file){
            $dir = $root . '/' . AUDIO_DIR;
            $arrSongs = array();
            if(file_exists($dir )){
                foreach(new DirectoryIterator($dir ) as $file){
                    $filename = $file->getFilename();
                    if(($file->isDot()) or $file->isDir()){
                        continue;
                    }else{
                        $arrSongs[] = $filename;
                    }
                }
            }
            if(count($arrSongs)){
                $song = @array_rand($arrSongs, 1 );
            }
            if($song){
                $file = '/' . AUDIO_DIR . $arrSongs[$song];
                Rhema_Util::setSessData(self::SOUND_KEY, $file );
            }
        }
        return $file;
    }
}