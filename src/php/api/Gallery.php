<?php
/**
 * Model of a gallery.
 * 
 * @package api
 * @subpackage public
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

	/**
	 * Inserts a new gallery in the database and creates it's folder.
	 * 
	 * @param array $data
	 * @return boolean True, if insert was successful
	 * @throws Exception If $data is not valid oder creation of the folder failed
	 * @deprecated since version 1.0
	 */
	public static function insert(array $data) {
		$category = new MT_Category($data['category']);
		$subcategoryPath = '';
		
		// If a subcategory is given
		if(!empty($data['subcategory'])) {
			$subcategory = new MT_Subcategory($data['subcategory']);
			$subcategoryPath = $subcategory->get_attribute('path').'/';
			
			// Check if the given subcategory and category ID fit
			if ($category->getId() != $subcategory->get_attribute('category')) {
				throw new Exception('Kategorie mit Pfad "'.$subcategoryPath.'" ist keine Unterkategorie von Kategorie mit ID "'.$data['category'].'"');
			}
		}
		
		$data['date'] = time();
		$data['path'] = MT_Admin_Model_File::nameToPath($data['name']);
		$data['fullPath'] = $category->get_attribute('path').'/'.$subcategoryPath.$data['path'].'/';
		
		if(parent::insert($data)) {
			return MT_Admin_Model_File::createDirectory($data['fullPath']);
		}
		return FALSE;
	}
	
	/**
	 * Gibt die ID der Galerie zurück, wenn man den ganzen Pfad von dieser
	 * angibt
	 *
	 * @param	string	$id		Galleries full path
	 * @param	string			Galleries Id
	 * @return False oder ID
	 * @deprecated since version 1.0
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
	 * @deprecated since version 1.0
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
	  * @deprecated since version 0.1
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
	 * Überprüft, ob die Gallerie mit "Neu" markiert wird
	 *
	 * @param	int		$id	Gallery id
	 * @return	boolean
	 * @deprecated since version 1.0
	 */
	public function checkGalleryIsNew() {
		return (time() - MT_Photo::getLatestPhotoDate($this->id) <= self::$__newTimestamp);
	}
	
	/**
	 * Überprüft, ob sich in der Gallerie mindestens ein Foto befindet
	 *
	 * @param	string	$id		Galleries ID
	 * @return	boolean
	 * @deprecated since version 0.1
	 */
	public function checkIsPhotoInGallery() {
		return (MT_Photo::getCount($this->id) > 0);
	}

}
