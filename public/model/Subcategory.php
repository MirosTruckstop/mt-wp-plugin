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
	 * Inserts a new category in the database and creats the folder.
	 * 
	 * @param array $data
	 * @return boolean True, if insert was successful
	 * @throws Exception If creation of the folder failed
	 */
	public static function insert($data) {
		$data['path'] = MT_Functions::nameToPath($data['name']);
		$categoryPath = (new MT_Category($data['category']))->get_attribute('path');
		$path = MT_Photo::PHOTO_PATH.'/'.$categoryPath.'/'.$data['path'];
		if (parent::insert($data)) {
			if (MT_Functions::createDirIfNotExists($path)) {
				return TRUE;
			} else {
				throw new Exception('Could not create folder '.$path);
			}
		}
		return FALSE;
	}

}