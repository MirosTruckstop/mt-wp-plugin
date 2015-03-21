<?php
/**
 * Service class for categories and subcategories
 *
 * @category   MT
 */
class MT_Category extends MT_Common {
	
	public function __construct($id = NULL) {
		parent::__construct(self::getTableName(), $id);
	}

	public function __toString() {
		return 'category';
	}
	
	public static function getTableName() {
		return 'wp_mt_category';
	}

	public function getName() {
		return "Kategorien";
	}

	public static function insert($data) {
		$data['path'] = MT_Functions::nameToPath($data['name']);
		parent::insert($data);
	}	

}