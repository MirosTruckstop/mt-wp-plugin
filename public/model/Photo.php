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
		parent::__construct('wp_mt_photo', $id);
	}

	public function __toString() {
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
	public function getLatestPhotoDate($galleryId = NULL) {
		if (!empty($galleryId)) {
			$whereCondition = "id = ".$galleryId." AND";
		}
		return parent::get_max('date', $whereCondition . "`show` = 1");
	}
	
	        /**
         * Get photo path.
         * 
         * @param   int     $id     Photo id
         * @return  string          Photo path
		 * @deprecated since version number
         */
	public function getPath() {
		return parent::get_attribute('path');
	}
	
	private function getAbsolutePath() {
		return $__photoPath . $this->getPath();
	}
	
	private function getFile() {
		return new MT_Admin_ImageFile($this->getAbsolutePath());
	}


	/**
	 * Check photo is in database
	 *
	 * @param	string		$path	Photo's  database path
	 * @return	boolean
	 * @deprecated since version number
	 */
	public function checkPhotoIsInDb($path) {
		return parent::check_dataset_exits("path = '$path'");
	}
	
	public function delete() {
		if(parent::hasId()) {
			if($this->getFile()->delete()) {
				// TODO
			} else {
				
			}
		}
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
	 * @deprecated since version number
	 */
	public function getCount( $galleryId = NULL ) {
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
		return parent::get_count('id', $whereCondition);
	}
	
	/**
	 * Gibt die Anzahl der Seiten einer Galerie (abhängig von den
	 * Benutzereinstellungen) zurück.
	 *
	 * @param	string	$id		Galleries ID
	 * @param	string	$num	Number
	 * @return	string			Number of pages
	 */
	public function getNumPages($galleryId, $num) {
		return ceil( $this->getCount($galleryId) / $num ); // ceil liefert die nächste ganze Zahl (Aufrunden)
	}
	
	/**
	 * Gibt die Anzahl der Bilder eines Fotografen zurück
	 *
	 * @return	int				Number of pictures
	 */
	public function getNumPhotos($photographerId) {
		return parent::get_count('id', "photographer = '".$photographerId."' AND `show` = '1'");
	}

}