<?php
/**
 * Model of a subcategory.
 *
 * @category   MT
 * @package public
 * @subpackage model
 */
class MT_Subcategory extends MT_Common {
	
	public function __construct($id = NULL) {
		parent:: __construct($id);
	}
	
	public static function name() {
		return 'subcategory';
	}
	
	public static function getName() {
		return 'Unterkategorien';
	}
	
	/**
	 * Inserts a new subcategory in the database and creats it's folder.
	 * 
	 * @param array $data Data
	 * @return boolean True, if insert was successful
	 * @throws Exception If creation of the folder failed
	 */
	public static function insert($data) {
		$data['path'] = MT_Admin_Model_File::nameToPath($data['name']);
		$categoryPath = (new MT_Category($data['category']))->get_attribute('path');
		$path = $categoryPath.'/'.$data['path'];
		if (parent::insert($data)) {
			return MT_Admin_Model_File::createDirectory($path);
		}
		return FALSE;
	}

}