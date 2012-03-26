<?php
class Admin_Service_Image {
	public $font = 'JI-GABLED.TTF';
	private $bgColor;
	private $drwColor;
	private $imgSize;
	private $cavSizeW = 20;
	private $cavSizeH = 10;
	private $bnCavSizeW = 30;
	private $bnCavSizeH = 20;
	private $bnSize = '0,0';
	public $dExt = 'jpg';
	public $imgExt = array ('.gif', '.png', '.jpg', '.ico' );
	public $image;
	public $image_type;

	public function __construct() {
		//parent::__construct();
		putenv ( 'GDFONTPATH=C:\WINDOWS\fonts' ); //sets font path for the Gd library
	}

	public function _init() {
		$this->_entity = $this;
	}

	public function getDrwColor() {
		$color = '204,204,204';
		return $color;

	}
	public function getBgColor() {
		$bg = '255,255,255';
		return $bg;

	}
	public function getImgPath($imgName) {
		$style = $this->style;
		$imgDir = imgPath . cDelim . $style . cDelim . 'buttons' . cDelim;

		if (! file_exists ( $imgDir )) {
			mkdir ( $imgDir, 0777, true );
		}
		$path = $imgDir . $imgName;

		return $path;
	}

	public function crtButton($w, $h, $filename, $ext = '.jpg') {

		$img = imageCreate ( $this->cavSizeW, $this->cavSizeH );
		$this->bgColor = imageColorAllocate ( $img, 255, 255, 255 );
		$this->drwColor = imageColorAllocate ( $img, 204, 204, 204 );

		$x1 = 0;
		$x2 = $x1 + $w;
		$y1 = 0;
		$y2 = $y1 + $h;
		imagefilledrectangle ( $img, $x1, $y1, $x2, $y2, $this->drwColor );

		$style = $this->style;
		$imgDir = imgPath . cDelim . $style . cDelim . 'buttons';
		if (! file_exists ( $imgDir ))
			mkdir ( $imgDir );

		$fullPath = $imgDir . cDelim . $filename . $ext;

		imagePNG ( $img, $fullPath );
		imagedestroy ( $img );
	}

	public function crtThumb($add) {
		$n_width = 100; // Fix the width of the thumb nail images
		$n_height = 100; // Fix the height of the thumb nail imaage


		$tsrc = "thimg/" . $_FILES [userfile] [name]; // Path where thumb nail image will be stored
		//echo $tsrc;
		if (! ($_FILES [userfile] [type] == "image/pjpeg" or $_FILES [userfile] [type] == "image/gif")) {
			echo "Your uploaded file must be of JPG or GIF. Other file types are not allowed<BR>";
			exit ();
		}

		if (@$_FILES [userfile] [type] == "image/gif") {
			$im = ImageCreateFromGIF ( $add );
			$width = ImageSx ( $im ); // Original picture width is stored
			$height = ImageSy ( $im ); // Original picture height is stored
			$newimage = imagecreatetruecolor ( $n_width, $n_height );
			imageCopyResized ( $newimage, $im, 0, 0, 0, 0, $n_width, $n_height, $width, $height );

			if (function_exists ( "imagegif" )) {
				Header ( "Content-type: image/gif" );
				ImageGIF ( $newimage, $tsrc );
			} elseif (function_exists ( "imagejpeg" )) {
				Header ( "Content-type: image/jpeg" );
				ImageJPEG ( $newimage, $tsrc );
			}
			chmod ( "$tsrc", 0777 );
		} ////////// end of gif file thumb nail creation//////////


		////////////// starting of JPG thumb nail creation//////////
		if ($_FILES [userfile] [type] == "image/pjpeg") {
			$im = ImageCreateFromJPEG ( $add );
			$width = ImageSx ( $im ); // Original picture width is stored
			$height = ImageSy ( $im ); // Original picture height is stored
			$newimage = imagecreatetruecolor ( $n_width, $n_height );
			imageCopyResized ( $newimage, $im, 0, 0, 0, 0, $n_width, $n_height, $width, $height );
			ImageJpeg ( $newimage, $tsrc );
			chmod ( "$tsrc", 0777 );
		}
	}

	public function getImage($dir, $str) {
		$img = '';
		if (is_dir ( $dir )) {
			foreach ( new DirectoryIterator ( $dir ) as $file ) {
				$filename = $file->getFilename ();
				if ((! $file->isDot ()) && ! $file->isDir () && ($filename != basename ( $_SERVER ['PHP_SELF'] ))) {
					if (strpos ( $filename, $str ) !== false) {
						$img = $file->getPathName ();
						break;
					}
				}
			}
		}
		return $img;
	}

	public function getExt($Img) {
		$ext = '';
		if ($Img != '') {
			if (substr ( $Img, - 4, - 3 ) == '.'){
				$ext = strtolower ( substr ( $Img, - 3 ) );
			}else if (substr ( $Img, - 5, - 4 ) == '.'){
				$ext = strtolower ( substr ( $Img, - 4 ) );
			}
		}
		return $ext;
	}

	public function crtBnFrmImg($newImg, $txt, $bgImg = '', $angle = 0) {
		if ($bgImg == '') {
			$style = $this->style;
			$ext = $this->dExt;
			$bgImgPath = imgPath . cDelim . $style . cDelim . 'buttons' . cDelim;
			$bgImg = 'dBgImg.' . $ext;
		}
		//$color = $this->style;
		$ext = $this->getExt ( $bgImg );
		$ext = $ext == '' ? $this->dExt : $ext;

		chdir ( $bgImgPath );
		$rt = getcwd ();
		// $bgImg = $bgImgPath.$bgImg;


		switch ($ext) {
			case 'png' :
				$image = ImageCreateFromPNG ( $bgImg );
				break;
			case 'jpg' :
			case 'jpeg' :
				$image = imagecreatefromjpeg ( 'dBgImg.jpg' );
				break;
			default :
				$image = ImageCreateFromGIF ( $bgImg );
				break;
		}
		$fntExt = $this->getExt ( $this->font );

		if ($fntExt == 'pfb') {
			$font = ImagePsLoadFont ( $this->font );
		} else
			$font = fontDir . $this->font;

		$size = 12;
		list ( $x, $y ) = $this->imgCenter ( $image, $txt, $font, $size );

		switch ($fntExt) {
			case 'pfb' :
				{
					$font = ImagePsLoadFont ( 'arial.pfb' );
					ImagePSText ( $image, $txt, $font, $size, $this->drwColor, $this->bgColor, $x, $y );
					ImagePSFreeFont ( $font );
					break;
				}
			case 'ttf' :
				{
					ImageTTFText ( $image, $size, $angle, $x, $y, $this->drwColor, $font, $txt );
					break;
				}
		}

		$ext = $this->getExt ( $newImg );
		$ext = $ext == '' ? $this->dExt : $ext;
		$newImg = "$newImg.$ext";

		switch ($ext) {
			case 'png' :
				ImagePNG ( $image, $newImg );
				break;
			case 'jpeg' :
			case 'jpg' :
				ImageJPEG ( $image, $newImg );
				break;
			default :
				ImageGIF ( $image, $newImg );
				break;
		}
		imageDestroy ( $image );

		return $bgImgPath . cDelim . $newImg;
	}

	public function imgCenter($image, $text, $font, $size, $space = 0, $tightness = 0, $angle = 0) {
		//find the size of image
		$x1 = ImageSX ( $image );
		$y1 = ImageSY ( $image );

		//determine font extension
		$ext = $this->getExt ( $font );

		switch ($ext) {
			case 'pfb' :
				{
					list ( $xl, $yl, $xr, $yr ) = ImagePSBBox ( $text, $font, $size, $space, $tightness, $angle );
					break;
				}
			default :
				{
					list ( $xl, $yl, $xr, $yr ) = ImageTTFBBox ( $size, $angle, $font, $text );
					break;
				}
		}

		//compute center
		$x = intval ( ($x1 - $xr) / 2 );
		$y = intval ( ($y1 - $yr) / 2 );

		return array ($x, $y );
	}

	public function printImage($random_number) {
		header ( "Content-type: image/jpeg" );
		$im = @imagecreate ( 100, 20 ) or die ( "Cannot Initialize new GD image stream" );
		$background_color = imagecolorallocate ( $im, 255, 255, 255 );
		$text_color = imagecolorallocate ( $im, 0, 0, 0 );

		for($i = 0; $i < strlen ( $random_number ); $i ++) {
			$display = substr ( $random_number, $i, 1 );
			$x = ($i * 20) + rand ( 3, 6 );
			$y = rand ( 3, 6 );
			imagestring ( $im, 5, $x, $y, $display, $text_color );
		}

		for($i = 1; $i < 100; $i ++) {
			$cor_x = rand ( 1, 100 );
			$cor_y = rand ( 1, 20 );
			imagesetpixel ( $im, $cor_x, $cor_y, $text_color );
		}

		imagejpeg ( $im );
		imagedestroy ( $im );
	} // End printImage


	public function load($filename) {
		$image_info = getimagesize ( $filename );
		$this->image_type = $image_info [2];
		if ($this->image_type == IMAGETYPE_JPEG) {
			$this->image = imagecreatefromjpeg ( $filename );
		} elseif ($this->image_type == IMAGETYPE_GIF) {
			$this->image = imagecreatefromgif ( $filename );
		} elseif ($this->image_type == IMAGETYPE_PNG) {
			$this->image = imagecreatefrompng ( $filename );
		}
	}
	public function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 75, $permissions = null) {
		if ($image_type == IMAGETYPE_JPEG) {
			imagejpeg ( $this->image, $filename, $compression );
		} elseif ($image_type == IMAGETYPE_GIF) {
			imagegif ( $this->image, $filename );
		} elseif ($image_type == IMAGETYPE_PNG) {
			imagepng ( $this->image, $filename );
		}
		if ($permissions != null) {
			chmod ( $filename, $permissions );
		}
	}
	public function output($image_type = IMAGETYPE_JPEG) {
		if ($image_type == IMAGETYPE_JPEG) {
			imagejpeg ( $this->image );
		} elseif ($image_type == IMAGETYPE_GIF) {
			imagegif ( $this->image );
		} elseif ($image_type == IMAGETYPE_PNG) {
			imagepng ( $this->image );
		}
	}
	public function getWidth() {
		return imagesx ( $this->image );
	}
	public function getHeight() {
		return imagesy ( $this->image );
	}
	public function resizeToHeight($height) {
		$ratio = $height / $this->getHeight ();
		$width = $this->getWidth () * $ratio;
		$this->resize ( $width, $height );
	}
	public function resizeToWidth($width) {
		$ratio = $width / $this->getWidth ();
		$height = $this->getheight () * $ratio;
		$this->resize ( $width, $height );
	}
	public function scale($scale) {
		$width = $this->getWidth () * $scale / 100;
		$height = $this->getheight () * $scale / 100;
		$this->resize ( $width, $height );
	}
	public function resize($width, $height) {
		$new_image = imagecreatetruecolor ( $width, $height );
		imagecopyresampled ( $new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth (), $this->getHeight () );
		$this->image = $new_image;
	}
}
