<?php

class MT_Admin_ImageFile {
	
	private $file;
	
	/**
	 * Supported photo extensions
	 *
	 * @var array
	 */
	public static $__photoExtensions = array( "jpg", "jpeg", "png" );
	
	/**
	 * Maximum allowed size of photos (X-Size)
	 *
	 * @var int
	 */
	static $__photoMaxSize = 750;
	
	public function __construct($file) {
		$this->file = $file;
	}
	
	public function isFile() {
		return is_file($this->file);
	}
	
	public function getExtension() {
		return strtolower(pathinfo($this->file, PATHINFO_EXTENSION));
	}
	
	public function getWeight() {
		return imagesx($this->file);
	}
	
	public function isPhoto() {
		return $this->isFile() && in_array($this->getExtension(), self::$__photoExtensions);
	}
	
		/**
	 * Check image size
	 *
 	 * @param	string		$file	Real path
	 * @return	boolean
	 * @throws	Exception
	 */
// TODO: getimagesize() 
	public function checkSizeIsOk() {
		return ($this->getWeight() <= $__photoMaxSize);
	}
	
	public function rename($newName) {
		return rename($this->file, $newName);
	}
	
	public function delete() {
		return unlink($this->file);
	}
}