<?php

class MT_Photo extends MT_Common {

	/**
	 * Photo path
	 *
	 * @var string
	 */
	public static $__photoPath = '../bilder/';	
	
	/**
	 * Photo path
	 *
	 * @var string
	 */
	public static $__photoPathAbs = '/bilder/galerie/';

	
	public function __construct($id = NULL) {
		parent::__construct(self::getTableName(), $id);
	}

	public function __toString() {
		return 'photo';
	}
	
	public static function getTableName() {
		return 'wp_mt_photo';
	}
	
	public function isDeletable() {
		return !empty($this->id);
	}
	
	/**
	 * Datum des zuletzt eingestellten Bildes
	 *
	 * @return	string			Latest photo timestamp
	 */
	public static function getLatestPhotoDate($galleryId = NULL) {
		if (!empty($galleryId)) {
			$whereCondition = 'id = '.$galleryId.' AND';
		}
		return parent::get_aggregate('MAX', 'date', $whereCondition . "`show` = 1");
	}
	
	/**
	 * Get photo path.
	 * 
	 * @param   int     $id     Photo id
	 * @return  string          Photo path
	*/
	public function getPath() {
		return parent::get_attribute('path');
	}
	
	private function getAbsolutePath() {
		return self::$__photoPath . $this->getPath();
	}
	
	private function getFile() {
		return new MT_Admin_ImageFile($this->getAbsolutePath());
	}


	/**
	 * Check photo is in database
	 *
	 * @param	string		$path	Photo's  database path
	 * @return	boolean
	 */
	public static function checkPhotoIsInDb($path) {
		return parent::get_attribute('id', 'path = `'.$path.'`');
	}
	
	public static function delete() {
/*		if(parent::hasId()) {
			if($this->getFile()->delete()) {
				// TODO
			} else {
				
			}
		}*/
	}
	
	public function renameFile($galleryId) {
		if(parent::hasId()) {
			$file = $this->getFile();
			$gallery = new MT_Gallery($galleryId);
			$dirname = self::$__photoPath . $gallery->get_attribute('fullPath');
			$basename = $gallery->get_attribute('path') . '_' . $this->getId();
			
			$newFile = $dirname . '/' . $basename . '.' . $file->getExtension();
			$file->rename($newFile);
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
		$whereCondition = '';
		if( isset( $galleryId ) ) {
			if($galleryId === 'neue_bilder') {
				$whereCondition .= "`show` = '0'";
			}
			else {
				$whereCondition .= "gallery = '" . $galleryId . "'
									AND `show` = 1";
			}
		} else {
			$whereCondition .= "`show` = '1'";
		}
		return parent::get_aggregate('COUNT', 'id', $whereCondition);
	}
	
	/**
	 * Gibt die Anzahl der Seiten einer Galerie (abhängig von den
	 * Benutzereinstellungen) zurück.
	 *
	 * @param	string	$id		Galleries ID
	 * @param	string	$num	Number
	 * @return	string			Number of pages
	 */
	public static function getNumPages($galleryId, $num) {
		return ceil(self::getCount($galleryId) / $num ); // ceil liefert die nächste ganze Zahl (Aufrunden)
	}
	
	/**
	 * Gibt die Anzahl der Bilder eines Fotografen zurück
	 *
	 * @return	int				Number of pictures
	 */
	public static function getNumPhotos($photographerId) {
		return parent::get_aggregate('COUNT', 'id', "photographer = '".$photographerId."' AND `show` = '1'");
	}

}