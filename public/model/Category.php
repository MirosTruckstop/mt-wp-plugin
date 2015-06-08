<?php
/**
 * Service class for categories and subcategories
 *
 * @category   MT
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

	public static function insert($data) {
		$data['path'] = MT_Functions::nameToPath($data['name']);
		MT_Functions::createDirIfNotExists(MT_Photo::PHOTO_PATH.$data['path']);
		return parent::insert($data);
	}

}