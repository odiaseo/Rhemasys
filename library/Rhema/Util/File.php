<?php
class Rhema_Util_File {
    /* creates a compressed zip file */
    public function createZip($files = array(), $destination = '', $overwrite = false){
        //if the zip file already exists and overwrite is false, return false
        if(file_exists($destination ) && ! $overwrite){
            return false;
        }
        //vars
        $validFiles = array();
        //if files were passed in...
        if(is_array($files )){
            //cycle through each file
            foreach($files as $file){
                //make sure the file exists
                if(file_exists($file )){
                    $validFiles[] = $file;
                }
            }
        }
        //if we have good files...
        if(count($validFiles )){
            //create the archive
            $zip = new ZipArchive();
            if($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE ) !== true){
                return false;
            }
            //add the files
            foreach($validFiles as $file){
                $zip->addFile($file, $file );
            }
            //debug
            //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
            //close the zip -- done!
            $zip->close();
            //check to make sure the file exists
            return file_exists($destination );
        }else{
            return false;
        }
    }
    
    public function parseCsv($filename){
    	while(($data = fgetcsv($filename)) !== false) {
    		
    	}
       
    }
}