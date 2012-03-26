<?php
/**
 * Image manipulation helper
 * @author Pele
 *
 */
class Rhema_Controller_Action_Helper_ImageResizer  extends Zend_Controller_Action_Helper_Abstract {

	protected $_image;
	protected $_imageType;
	
	public function imageResizer(){
		return $this;
	}
	
	/**
	 * @return the $image
	 */
	public function getImage() {
		return $this->_image;
	}

	/**
	 * @return the $imageType
	 */
	public function getImageType() {
		return $this->_imageType;
	}

	/**
	 * @param field_type $image
	 */
	public function setImage($image) {
		$this->_image = $image;
	}

	/**
	 * @param field_type $imageType
	 */
	public function setImageType($imageType) {
		$this->_imageType = $imageType;
	}

	public function load($filename) {
		$imageInfo = getimagesize ( $filename );
		$this->_imageType = $imageInfo [2];
		if ($this->_imageType == IMAGETYPE_JPEG) {
			$this->_image = imagecreatefromjpeg ( $filename );
		} elseif ($this->_imageType == IMAGETYPE_GIF) {
			$this->_image = imagecreatefromgif ( $filename );
		} elseif ($this->_imageType == IMAGETYPE_PNG) {
			$this->_image = imagecreatefrompng ( $filename );
		}
		return $this;
	}
	public function save($filename, $imageType = IMAGETYPE_JPEG, $compression = 75, $permissions = null) {
		if ($imageType == IMAGETYPE_JPEG) {
			imagejpeg ( $this->_image, $filename, $compression );
		} elseif ($imageType == IMAGETYPE_GIF) {
			imagegif ( $this->_image, $filename );
		} elseif ($imageType == IMAGETYPE_PNG) {
			imagepng ( $this->_image, $filename );
		}
		if ($permissions != null) {
			chmod ( $filename, $permissions );
		}
		//imagedestroy($this->_image);
		return $this;
	}
	public function output($imageType = IMAGETYPE_JPEG) {
		if ($imageType == IMAGETYPE_JPEG) {
			imagejpeg ( $this->_image );
		} elseif ($imageType == IMAGETYPE_GIF) {
			imagegif ( $this->_image );
		} elseif ($imageType == IMAGETYPE_PNG) {
			imagepng ( $this->_image );
		}
		return $this;
	}
	public function getWidth() {
		return imagesx ( $this->_image );
	}
	public function getHeight() {
		return imagesy ( $this->_image );
	}
	public function resizeToHeight($height) {
		$ratio = $height / $this->getHeight ();
		$width = $this->getWidth () * $ratio;
		$this->resize ( $width, $height );
		return $this;
	}
	public function resizeToWidth($width) {
		$ratio = $width / $this->getWidth ();
		$height = $this->getHeight () * $ratio;
		$this->resize ( $width, $height );
		return $this;
	}
	public function scale($scale) {
		$width = $this->getWidth () * $scale / 100;
		$height = $this->getHeight () * $scale / 100;
		$this->resize ( $width, $height );
		return $this;
	}
	public function resize($width, $height) {
		$newImage = imagecreatetruecolor ( $width, $height );
		imagecopyresampled ( $newImage, $this->_image, 0, 0, 0, 0, $width, $height, $this->getWidth (), $this->getHeight () );
		$this->_image = $newImage;
		return $this;
	}
}
