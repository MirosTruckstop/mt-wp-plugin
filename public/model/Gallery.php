<?php
/**
 * Model of a gallery.
 * 
 * @package public
 * @subpackage model
 */
class MT_Gallery extends MT_Common {

	/**
     * Saves full paths of all galleries.
     * 
     * @var array   Gallery id as key and path as value
     */
    static $__allGalleryPaths;
	
	/**
	 * How long a gallery is marked as 'new'
	 *
	 * NOTE: 604800 = 1 Week (60s * 60m * 24h * 7d)
	 *
	 * @var int
	 */
	static $__newTimestamp = 604800;
	
	public function __construct($id = NULL) {
		parent::__construct($id);
	}
	
	public static function name() {
		return 'gallery';
	}
	
	public static function getName() {
		return 'Galerien';
	}

	public static function insert($data) {
		$data['date'] = time();
		$data['path'] = MT_Admin_Model_File::nameToPath($data['name']);
		return parent::insert($data);
	}
	
	/**
	 * Gibt die ID der Galerie zurück, wenn man den ganzen Pfad von dieser
	 * angibt
	 *
	 * @param	string	$id		Galleries full path
	 * @param	string			Galleries Id
	 * @return False oder ID
	 */
	public static function getIdFromPath($path) {
		// Add a backslash, if path doesn't end with one
		if(substr($path, -1) != '/') {
			$path .= '/';
		}
		return array_search($path, self::__getAllGalleryPaths());	
	}

	/**
	 * Get full path of all galleries.
	 * 
	 * @return array    Gallery id as key and path as value
	 */
	public static function __getAllGalleryPaths() {
		if (empty(self::$__allGalleryPaths)) {
			$query = new MT_QueryBuilder();
			$query->from('gallery', array('id, fullPath'));
			foreach ($query->getResult() as $item) {
				self::$__allGalleryPaths[$item->id] = $item->fullPath;				
			}
		}
		return self::$__allGalleryPaths;
	}
		
    /**
      * Get full path of a gallery.
      * 
      * @param int $id Gallery id
      * @return string   Full path
      */
    public function getFullPath() {
        $galleryPaths = self::__getAllGalleryPaths();
        return $galleryPaths[$this->id];
    }
	
	/**
	 * Gibt die Anzahl der Galerien in der Category bzw. Subcategory zurück
	 *
	 * @param	string	$categoryID		Categories ID
	 * @param	string	$subcategoryID	Subcategories ID
	 * @return	string					Number of galleries
	 * @throws	Exception
	 */
	public static function getNumGalleries($categoryId, $subcategoryId = '0') {
		return parent::get_aggregate('COUNT', 'name', "category = '" . $categoryId . "' AND subcategory = '" . $subcategoryId . "'");
	}
	
	/**
	 * Überprüft, ob die Gallerien in der Kategorie bzw. Unterkategorie mit dem
	 * Hauptparkplatz verknüpft sind
	 *
	 * @param	string		$categorieID		Categories ID
	 * @param	string		$subcategorieID		Subategories ID
	 * @return	boolean
	 * @throws	Exception
	 */
	public static function checkLinkToHauptparkplatz( $categorieId, $subcategorieId = '0' ) {
		return (parent::get_attribute('id', "category = '". $categorieId . "' AND subcategory = '" . $subcategorieId . "' AND hauptparkplatz != ''")) != FALSE;
	}
	
	/**
	 * Überprüft, ob die Gallerie mit "Neu" markiert wird
	 *
	 * @param	int		$id	Gallery id
	 * @return	boolean
	 */
	public function checkGalleryIsNew() {
		return (time() - MT_Photo::getLatestPhotoDate($this->id) <= self::$__newTimestamp);
	}
	
	/**
	 * Überprüft, ob sich in der Gallerie mindestens ein Foto befindet
	 *
	 * @param	string	$id		Galleries ID
	 * @return	boolean
	 */
	public function checkIsPhotoInGallery() {
		return (MT_Photo::getCount($this->id) > 0);
	}

}
