<?php
/**
 * Search and store new photos.
 *
 * @category   MT
 * @package    admin
 * @subpackage model
 */
class MT_Admin_Model_PhotoSearch {

	/**
	 * Timestamp
	 * 
	 * @var integer
	 */
	private $time;
	
	public function __construct() {
		return $this;
	}
	
	/**
	 * Searchs new photos in the given directory and stores them.
	 *
	 * @param	string|null $dir Directory
	 * @return	boolean True, if search was successful
	 */
	public function search($dir = MT_Admin_Model_File::PHOTO_PATH) { 
		if (!is_dir($dir)) {
			return FALSE;
		}
		$directoryHandle = opendir( $dir );
		while(false !== ($basename = readdir($directoryHandle))) {
			$path = $dir.'/'.$basename;
			
			// Skip "." and ".." files and the thumbnail folder
			if($basename == '.' || $basename == '..' || $path == MT_Admin_Model_File::THUMBNAIL_PATH) {
				continue;
			}
			// Folder	
			else if(is_dir($path)) {
				$this->search($path);
			}
			// Photo file
			else if(MT_Admin_Model_File::isPhoto($path)) {
				// Store the photo path without PHOTO_PATH in the database
				$dbDirname = MT_Admin_Model_File::getDbPathFromDir($dir);
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
		return TRUE;
	}

}