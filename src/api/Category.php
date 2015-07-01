<?php
/**
 * Model of a category.
 *
 * @category   MT
 * @package api
 * @subpackage public
 * @deprecated since version 1.0
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
	 * @deprecated since version 1.0
	 */
	public static function insert($data) {
		$data['path'] = MT_Admin_Model_File::nameToPath($data['name']);
		if (parent::insert($data)) {
			return MT_Admin_Model_File::createDirectory($data['path']);
		}
		return FALSE;
	}

}