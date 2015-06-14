<?php
/**
 * 
 * @package admin
 * @subpackage model
 */
class MT_Admin_Model_File {
	

	/**
	 * Returns for a name a path, i.e. removes special characters etc.
	 * 
	 * @param string $name
	 * @return string
	 */
	public static function nameToPath($name) {
		// First array: search, second array: replace
		return str_replace(array(' ', '.', '-'), array('_', '', '_'), strtolower($name));
	}

	/**
	 * Creates in the image and in the thumbnail folder a new directory.
	 * 
	 * @param string $path
	 * @return boolean True, if creation was successful
	 * @throws Exception If creation of the folder failed
	 */
	public static function createDirectory($path) {
		if (MT_Functions::createDirIfNotExists(MT_Photo::PHOTO_PATH.'/'.$path) && MT_Functions::createDirIfNotExists(MT_Photo::THUMBNAIL_PATH.'/'.$path)) {
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
}

