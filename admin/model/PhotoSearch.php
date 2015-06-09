<?php
/**
 * Class for searching photos
 *
 * @category   MT
 * @package    Admin
 */
class MT_Admin_Model_PhotoSearch {

	private $time;
	
	/**
	 * Supported photo extensions
	 *
	 * @var array
	 */
	private static $__photoExtensions = array('jpg', 'jpeg');
	
	public function __construct() {
		return $this;
	}
	
	/**
	 * Searchs new photos in the given directory and stores them.
	 *
	 * @param	string|undefined $dir Directory
	 * @return	void|boolean False, if $dir is not a directory
	 */
	public function search($dir = MT_Photo::PHOTO_PATH) { 
		if (!is_dir($dir)) {
			return FALSE;
		}
		$directoryHandle = opendir( $dir );
		while(false !== ($basename = readdir($directoryHandle))) {
			$path = $dir.'/'.$basename;
			
			// Skip "." and ".." files and the thumbnail folder
			if($basename == '.' || $basename == '..' || $path == MT_Photo::THUMBNAIL_PATH) {
				continue;
			}
			// Folder	
			else if(is_dir($path)) {
				$this->search($path);
			}
			// Photo file
			else if($this->isPhoto($path)) {
				$dbDirname = str_replace(MT_Photo::PHOTO_PATH.'/', '', $dir).'/';
				$dbFile = $dbDirname.$basename;
		
				if (!isset($this->time)) {
					$this->time = time();
				} else {
					$this->time += MT_Admin_View_PhotoEdit::SECONDS_BETWEEN_PHOTOS;
				}

				// Ueberpruefen ob das Bild bereits in der Datenbank gespeichert ist
				if(!MT_Photo::checkPhotoIsInDb($dbFile)) {
					MT_Photo::insert(array(
						'path'        => $dbFile,
						'name_old'    => $basename,
						'gallery'     => MT_Gallery::getIdFromPath($dbDirname),
						'date'        => $this->time,
						'show'        => 0
					));
				}	
			}
		}
		closedir($directoryHandle);
	}
	
	private function isPhoto($file) {
		$fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		return is_file($file) && in_array($fileExtension, self::$__photoExtensions);
	}
}
?>