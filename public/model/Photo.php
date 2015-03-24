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
		parent::__construct($id);
	}

	public static function name() {
		return 'photo';
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
		return parent::get_aggregate('MAX', 'date', $whereCondition."`show` = 1");
	}
	
	/**
	 * Get photo path.
	 * 
	 * @param   int     $id     Photo id
	 * @return  string          Photo path
	*/
//	public static function getPath() {
//		return parent::get_attribute('path');
//	}
	
//	private static function getAbsolutePath() {
//		return self::$__photoPath . $this->getPath();
//	}
	
	/**
	 * Check photo is in database
	 *
	 * @param	string		$path	Photo's  database path
	 * @return	boolean
	 */
	public static function checkPhotoIsInDb($path) {
		return parent::get_attribute('id', 'path = `'.$path.'`');
	}
	
	// TODO: besser implementieren
	public static function renameFile($photoId, $photoFile, $galleryId) {
		$gallery = new MT_Gallery($galleryId);
		$dirname = self::$__photoPath . $gallery->get_attribute('fullPath');
		$basename = $gallery->get_attribute('path') . '_' . $photoId;
			
		$newFile = $dirname.$basename.'.'.strtolower(pathinfo($photoFile, PATHINFO_EXTENSION));
		if (rename($photoFile, $newFile)) {
			return str_replace(self::$__photoPath, '', $newFile);
		}
		return FALSE;
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