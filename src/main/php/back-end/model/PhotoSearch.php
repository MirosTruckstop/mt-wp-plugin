<?php
namespace MT\WP\Plugin\Backend\Model;

use MT\WP\Plugin\Api\MT_Gallery;
use MT\WP\Plugin\Api\MT_Photo;
use MT\WP\Plugin\Backend\View\Crud\MT_Admin_View_PhotoEdit;

/**
 * Search and store new photos.
 */
class MT_Admin_Model_PhotoSearch
{

	/**
	 * Timestamp
	 *
	 * @var integer
	 */
	private $time;
	
	public function __construct($paQueueClient = null)
	{
		$this->paQueueClient = $paQueueClient;
		$this->photoBaseUrl = get_bloginfo('url').'/bilder';
		return $this;
	}
	
	/**
	 * Searchs new photos in the given directory and stores them.
	 *
	 * @param string|null $dir Directory
	 *
	 * @return boolean True, if search was successful
	 */
	public function search($dir = MT_Admin_Model_File::PHOTO_PATH)
	{
		if (!is_dir($dir)) {
			return false;
		}
		$directoryHandle = opendir($dir);
		while (false !== ($basename = readdir($directoryHandle))) {
			$path = $dir.'/'.$basename;
			
			// Skip "." and ".." files and the thumbnail folder
			if ($basename == '.' || $basename == '..' || $path == MT_Admin_Model_File::THUMBNAIL_PATH) {
				continue;
			} elseif (is_dir($path)) { // Folder
				$this->search($path);
			} elseif (MT_Admin_Model_File::isPhoto($path)) { // Photo file
				// Store the photo path without PHOTO_PATH in the database
				$dbDirname = MT_Admin_Model_File::getDbPathFromDir($dir);
				$dbFile = $dbDirname.$basename;
		
				if (!isset($this->time)) {
					$this->time = time();
				} else {
					$this->time += MT_Admin_View_PhotoEdit::SECONDS_BETWEEN_PHOTOS;
				}

				// Ueberpruefen ob das Bild bereits in der Datenbank gespeichert ist
				if (!MT_Photo::checkPhotoIsInDb($dbFile)) {
					$id = MT_Photo::insert(array(
						'path'        => $dbFile,
						'name_old'    => $basename,
						'gallery'     => MT_Gallery::getIdFromPath($dbDirname),
						'date'        => $this->time,
						'show'        => 0
					));
					if ($id && $this->paQueueClient) {
						$image_uri = $this->photoBaseUrl.'/'.$dbFile;
						$this->paQueueClient->publish($image_uri, ['id' => "$id"]);
					}
				}
			}
		}
		closedir($directoryHandle);
		return true;
	}
}
