<?php
/**
 * Model of a photo.
 * 
 * @package public
 * @subpackage model
 */
class MT_Photo extends MT_Common {

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

	public function deleteOne() {
		if ($this->isDeletable()) {
			if (MT_Admin_Model_File::deletePhoto(parent::get_attribute('path'))) {
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
	 * @throws Exception MT_Admin_Model_File::renamePhoto
	 */
	private function renameFile($photoId, $oldFile, $galleryId) {		
		$gallery = new MT_Gallery($galleryId);
		$dirname = $gallery->get_attribute('fullPath');
		$basename = $gallery->get_attribute('path') . '_' . $photoId;
			
		$newDbFile = $dirname.$basename.'.'.strtolower(pathinfo($oldFile, PATHINFO_EXTENSION));
		return MT_Admin_Model_File::renamePhoto($oldFile, $newDbFile);
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
	 * Gibt die Anzahl der Bilder eines Fotografen zurück
	 *
	 * @return	integer	Number of photos
	 */
	public static function getNumPhotos($photographerId) {
		return parent::get_aggregate('COUNT', 'id', "photographer = '".$photographerId."' AND `show` = '1'");
	}

}