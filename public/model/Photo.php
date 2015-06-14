<?php
/**
 * Model of a photo.
 * 
 * @package public
 * @subpackage model
 */
class MT_Photo extends MT_Common {
	
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
	 * Photo path
	 *
	 * @var string
	 */
	const GALLERY_PATH_ABS = '/bilder/galerie';

	
	public function __construct($id = NULL) {
		parent::__construct($id);
	}

	public static function name() {
		return 'photo';
	}
	
	public function isDeletable() {
		return !empty($this->id);
	}
	
//	public static function delete() {
//		$path = parent::get_attribute('path');
//		if (parent::delete('id = '.$this->getId())) {
//			$photoDeleted = unlink(self::$__photoPath.$path);
//			$thumbDeleted = unlink(self::$thumbnailPath.$path);
//			if ($photoDeleted && $thumbDeleted) {
//				return TRUE;
//			} else {
//				throw new Exception('Foto: '.$path.', ');
//			}
//		}
//		return FALSE;
//	}
	

	public function deleteOne() {
		if ($this->isDeletable()) {
			$file = self::PHOTO_PATH.'/'.parent::get_attribute('path');
			if (unlink($file)) {
				return parent::delete('id = '.$this->id);			
			}
		}
		return FALSE;
	}
	
	/**
	 * Datum des zuletzt eingestellten Bildes
	 *
	 * @return	string			Latest photo timestamp
	 */
	public static function getLatestPhotoDate($galleryId = NULL) {
		if (!empty($galleryId)) {
			$whereCondition = 'gallery = '.$galleryId.' AND';
		}
		return parent::get_aggregate('MAX', 'date', $whereCondition."`show` = 1");
	}
	
    public function update(array $data, array $conditionValue = NULL) {
		if (!empty($data['gallery'])) {
			$data['path'] = $this->renameFile($conditionValue['id'], $data['path'], $data['gallery']);
		} else {
			unset($data['path']);
		}
		if (!MT_Functions::isTimestampInStringForm($data['date'])) {
			$data['date'] = strtotime($data['date']);
			// Falls für Timestamp Quatsch eingeben wurde, behalte den alten.
			if (!MT_Functions::isTimestampInStringForm($data['date']) ) {
				unset($data['date']);
			}
		}
		return parent::update($data, $conditionValue);
	}
	
	/**
	 * Check photo is in database
	 *
	 * @param	string		$path	Photo's  database path
	 * @return	boolean
	 */
	public static function checkPhotoIsInDb($path) {
		return parent::get_attribute('id', "path = '".$path."'");
	}
	
	/**
	 * Reanmes a photo and it's thumbnail.
	 * 
	 * @param type $photoId
	 * @param string $oldFile
	 * @param type $galleryId
	 * @return string|false, new path if rename of the photo and it's thumbnail
	 *	was successful. False otherwise.
	 * @throws Exception If $oldFile is not a file or rename failed
	 */
	private function renameFile($photoId, $oldFile, $galleryId) {
		if (!is_file($oldFile)) {
			throw new Exception('Rename failed: "'.$oldFile.'" is not a file');
		}
		
		$gallery = new MT_Gallery($galleryId);
		$dirname = self::PHOTO_PATH.'/'.$gallery->get_attribute('fullPath');
		$basename = $gallery->get_attribute('path') . '_' . $photoId;
			
		$newFile = $dirname.$basename.'.'.strtolower(pathinfo($oldFile, PATHINFO_EXTENSION));
		if (rename($oldFile, $newFile)) {
			if ($this->createOrRenameThumbnail($oldFile, $newFile)) {
				return str_replace(self::PHOTO_PATH.'/', '', $newFile);	
			}	
		} else {
			throw new Exception('Rename failed: Could not move "'.$oldFile.'" to "'.$newFile.'"');
		}
	}
	
	/**
	 * Checks if a thumbnail for $oldFile already exists. If that is the case
	 * this thumnail gets moved to the new path according to $newFile.
	 * Otherwise a thumbnail gets creted according to the path $newFile.
	 * 
	 * @param type $oldFile Old photo path
	 * @param type $newFile New photo path
	 * @return boolean True, if rename/create was successful
	 * @throws Exception If rename/create failed
	 */
	private function createOrRenameThumbnail($oldFile, $newFile) {
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
			require_once(MT_DIR . '/admin/model/ThumbnailCreator.php');
			if (MT_Admin_Model_ThumbnailCreator::create($newFile, $newThumbnail)) {
				return true;
			} else {
				throw new Exception('Could not create thumbnail "'.$newThumbnail.'" for the file "'.$newFile.'"');				
			}
		}
	}
	
	/**
	 * Gibt die Anzahl aller Bilder (ist keine ID gesetzt) bzw. die Anzahl der
	 * Bilder in einer Galerie / der neuen Bilder (ID ist gesetzt) zurück.
	 *
	 * @param	string|null		$galleryId	Gallery's ID
	 * @return	string				Number of pictures
	 */
	public static function getCount($galleryId = NULL) {
		if (isset($galleryId)) {
			$whereCondition = " AND gallery = '" . $galleryId . "'";
		}
		return parent::get_aggregate('COUNT', 'id', '`show` = 1'.$whereCondition);
	}
	
	public static function getCountNewPhotos() {
		return parent::get_aggregate('COUNT', 'id', '`show` = 0');
	}
	
	/**
	 * Gibt die Anzahl der Seiten einer Galerie (abhängig von den
	 * Benutzereinstellungen) zurück.
	 *
	 * @param	string	$id		Galleries ID
	 * @param	string	$num	Number
	 * @return	string			Number of pages
	 */
//	public static function getNumPages($galleryId, $num) {
//		return ceil(self::getCount($galleryId) / $num ); // ceil liefert die nächste ganze Zahl (Aufrunden)
//	}
	
	/**
	 * Gibt die Anzahl der Bilder eines Fotografen zurück
	 *
	 * @return	int				Number of pictures
	 */
	public static function getNumPhotos($photographerId) {
		return parent::get_aggregate('COUNT', 'id', "photographer = '".$photographerId."' AND `show` = '1'");
	}

}