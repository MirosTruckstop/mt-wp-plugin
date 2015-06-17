<?php
/**
 * 
 * @package admin
 * @subpackage model
 */
class MT_Admin_Model_File {
	
	/**
	 * Relative (from administration view) photo path
	 *
	 * @var string
	 */
	const PHOTO_PATH = '../../bilder';
	/**
	 * Relative (from administration view) path to photo thumbnail folder.
	 * 
	 * @var string 
	 */
	const THUMBNAIL_PATH = '../../bilder/thumb';
	/**
	 * Supported photo extensions
	 *
	 * @var array
	 */
	private static $__photoExtensions = array('jpg', 'jpeg');

	/**
	 * Returns the real file path from the given database file path.
	 * 
	 * @param string $path Database file path
	 * @return string Real file path
	 */
	public static function getPathFromDbPath($path) {
		return self::PHOTO_PATH.'/'.$path;
	}
	
	public static function getDbPathFromDir($dir) {
		if ($dir == self::PHOTO_PATH) {
			return '';				
		} else {
			return str_replace(self::PHOTO_PATH.'/', '', $dir).'/';					
		}
	}

	/**
	 * Returns for a name a path, i.e. removes special characters.
	 * 
	 * @param string $name
	 * @return string Path
	 */
	public static function nameToPath($name) {
		// Remove space in the front/end and make it lowercase
		$name = strtolower(trim($name));
		// Search and replace specific characters
		return strtr($name, array(
			' ' => '_',
			'-' => '_',
			'.' => '',
			'&' => '',
			'ä' => 'ae',
			'ö' => 'oe',
			'ü' => 'ue',
			'ß' => 'ss'
		));
	}

	/**
	 * Creates in the image and in the thumbnail folder a new directory.
	 * 
	 * @param string $path
	 * @return boolean True, if creation was successful
	 * @throws Exception If creation of the folder failed
	 */
	public static function createDirectory($path) {
		if (self::createDirIfNotExists(self::PHOTO_PATH.'/'.$path) && self::createDirIfNotExists(self::THUMBNAIL_PATH.'/'.$path)) {
			return TRUE;
		} else {
			throw new Exception('Could not create directory '.$path);
		}
	}
	
	/**
	 * Creates a directory if it not already exits.
	 * 
	 * @param string $path Path as string
	 * @return boolen True, if dir exits or was created
	 */
	private static function createDirIfNotExists($path) {
		if (!file_exists($path)) {
			return mkdir($path);
		}
		return TRUE;
	}

	/**
	 * Renames a photo and it's thumbnail.
	 * 
	 * @param string $oldFile Real photo path or database photo path
	 * @param string $newDbFile new database file path
	 * @return boolean True, if rename was successful
	 * @throws Exception If rename failed or $oldFile is not a file
	 */
	public static function renamePhoto($oldFile, $newDbFile) {
		// Check if the $oldFile already is a real path
		if (strpos($oldFile, self::PHOTO_PATH.'/') !== 0) {
			// Change databse path to real path
			$oldFile = self::getPathFromDbPath($oldFile);
		}
		
		if (!is_file($oldFile)) {
			throw new Exception('Rename failed: "'.$oldFile.'" is not a file');
		}
		
		$newFile = self::getPathFromDbPath($newDbFile);
		if (rename($oldFile, $newFile)) {
			if (self::createOrRenameThumbnail($oldFile, $newFile)) {
				return str_replace(self::PHOTO_PATH.'/', '', $newFile);	
			}	
		} else {
			throw new Exception('Rename failed: Could not move "'.$oldFile.'" to "'.$newFile.'"');
		}		
	}
	
	/**
	 * Delete a photo and it's thumbnail.
	 * 
	 * @param string $dbPath
	 * @return boolean
	 */
	public static function deletePhoto($dbPath) {
		if (unlink(self::PHOTO_PATH.'/'.$dbPath)) {
			$thumb = self::THUMBNAIL_PATH.'/'.$dbPath;
			if (file_exists($thumb)) {
				return unlink($thumb);
			}
			return TRUE;
		}
		return FALSE;
	}
	
	
	/**
	 * Checks if a thumbnail for $oldFile already exists. If that is the case
	 * this thumnail gets moved to the new path according to $newFile.
	 * Otherwise a thumbnail gets creted according to the path $newFile.
	 * 
	 * @param type $oldFile Old real photo path
	 * @param type $newFile New real photo path
	 * @return boolean True, if rename/create was successful
	 * @throws Exception If rename/create failed
	 */
	private static function createOrRenameThumbnail($oldFile, $newFile) {
		$oldThumbnail = str_replace(self::PHOTO_PATH, self::THUMBNAIL_PATH, $oldFile);
		$newThumbnail = str_replace(self::PHOTO_PATH, self::THUMBNAIL_PATH, $newFile);	
		
		// Thumbnail already exists
		if (file_exists($oldThumbnail)) {
			// Move thumbnail
			if (rename($oldThumbnail, $newThumbnail)) {
				return true;
			} else {
				throw new Exception('Could not rename thumbnail "'.$oldThumbnail.'" to "'.$newThumbnail.'"');
			}
		}
		// Thumbnail does not exists
		else {
			// Create thumbnail
			require_once(MT_DIR . '/src/admin/model/ThumbnailCreator.php');
			if (MT_Admin_Model_ThumbnailCreator::create($newFile, $newThumbnail)) {
				return true;
			} else {
				throw new Exception('Could not create thumbnail "'.$newThumbnail.'" for the file "'.$newFile.'"');				
			}
		}
	}
	
	/**
	 * Checks if the given file is a photo.
	 * 
	 * @param string $file Real file path as string
	 * @return boolean True, if file is a photo
	 */
	public function isPhoto($file) {
		$fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		return is_file($file) && in_array($fileExtension, self::$__photoExtensions);
	}
}