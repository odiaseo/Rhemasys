<ol>
<?php 
 	require_once '/../../thirdparty/Doctrine/Inflector.php';
 	
	$dir = getcwd () . '/../media/image/logos/retailer/medium/';
	ini_set('display_errors', 'on'); 
	$iterator = new RecursiveIteratorIterator ( new RecursiveDirectoryIterator ( $dir ), RecursiveIteratorIterator::CHILD_FIRST );
	
	foreach ( $iterator as $path ) { 
		if (!$path->isDir()) {
			$filename = realpath($path->__toString ()); 
			$file     = basename($filename);
			$file     = trim(strtolower($file), '-');
			$file     = str_replace('-.', '.', $file);
			$file     = preg_replace('/(_|\-{2,})/i', '-', $file);
			
			$newFile  = str_replace(basename($filename), $file, $filename);
			$info     = pathinfo($newFile);
			$urlize   = $info['dirname'] . DIRECTORY_SEPARATOR . Doctrine_Inflector::urlize($info['filename']) . '.' . $info['extension'];
			
			$test = array($filename, $urlize); 
			
			if(file_exists($newFile)){
				//unlink($newFile);
			}
			if($filename != $urlize){			
				@rename($filename, $urlize);
				echo "<li>" . basename($filename)  . ' => ' . basename($urlize)  .'</li>';
			}
		}
	}
?>
</ol>	
 