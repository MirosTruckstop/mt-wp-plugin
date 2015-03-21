<?php
/**
 * Class for searching photos
 *
 * @category   MT
 * @package    Admin
 */
class MT_Admin_PhotoSearch {

	/**
	 * Konstruiert MT_PhotoSearch Objekt
	 *
	 * @return void
	 */ 
	public function __construct(){
		// Nach neuen Bildern suchen, wenn weniger als 8 neue Bilder in der Datenbank gespeichert sind
		if(MT_Photo::getCount('neue_bilder') < '8' or $_GET['action'] === 'search') {
			$this->_searchNewPhotos( MT_Photo::$__photoPath, time() );
	
			// Datum der letzten Suche speichern
			update_option('datum_letzte_suche', time());
		}
	}

    /**
      * Get the number of new photos.
      * 
      * @return int  number of new photos
      */
    public function getNumPhotos() {
        return MT_Photo::getCount('neue_bilder');
    }
        
	/**
	 * Search new photos on webspace
	 *
	 * @param	string	$dir		Directory
	 * @param	string	$startTime	Start time
	 * @return	void
	 */
	private function _searchNewPhotos($dir, $startTime) { 
		if (!is_dir($dir)) {
			return FALSE;
		}
		$fp = opendir( $dir );
		while( $file = readdir( $fp ) ) {
			// Folder	
			if( is_dir( "$dir/$file" ) && $file != "." && $file != "..") {
//				 echo "<b>Ordner: ",$file,"</b><br>";
// TODO: überhabeparameter = neue Zeit?
				$this->_searchNewPhotos( "$dir/$file", $startTime );
			}

			// File
			$imageFile = new MT_Admin_ImageFile($dir/$file);
			if($imageFile->isPhoto()) {
				$dbDirname = str_replace( MT_Photo::$__photoPath . '/', '', $dir );
				$basename = $file;

				$dbFile = $dbDirname . '/' . $basename;
		
				// Ueberpruefen ob das Bild bereits in der Datenbank gespeichert ist
				if(!MT_Photo::checkPhotoIsInDb($dbFile)) {
					MT_Photo::insert(array(
						'path'        => $dbFile,
						'name_old'    => $basename,
						'gallery'     => MT_Gallery::getIdFromPath($dbDirname),
						'date'        => time(),
						'show'        => 0
					));
				}	
			}
		}
		closedir( $fp );
	}
}
?>