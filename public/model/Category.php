<?php
/**
 * Model of a category.
 *
 * @category   MT
 * @package public
 * @subpackage model
 */
class MT_Category extends MT_Common {
	
	/**
	 * Category path
	 *
	 * @var string
	 */
	public static $_categoryPath = '../kategorie/';

	public function __construct($id = NULL) {
		parent::__construct($id);
	}

	public static function name() {
		return 'category';
	}
	
	public static function getName() {
		return 'Kategorien';
	}

	/**
	 * Inserts a new category in the database and creats it's folder.
	 * 
	 * @param array $data Data
	 * @return boolean True, if insert was successful
	 * @throws Exception If creation of the folder failed
	 */
	public static function insert($data) {
		$data['path'] = MT_Functions::nameToPath($data['name']);
		if (parent::insert($data)) {
			// Create category folder and thumbnail folder
			if (MT_Functions::createDirIfNotExists(MT_Photo::PHOTO_PATH.'/'.$data['path']) && MT_Functions::createDirIfNotExists(MT_Photo::THUMBNAIL_PATH.'/'.$data['path'])) {
				return TRUE;
			} else {
				throw new Exception('Could not create folder '.$data['path']);
			}
		}
		return FALSE;
	}

}