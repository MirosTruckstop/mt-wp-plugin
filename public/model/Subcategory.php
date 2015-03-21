<?php
/**
 * Service class for categories and subcategories
 *
 * @category   MT
 */
class MT_Subcategory extends MT_Common {
	
	public function __construct($id = NULL) {
		parent:: __construct(self::getTableName(), $id);
	}
	
	public function __toString() {
		return 'subcategory';
	}
	
	public static function getTableName() {
		return 'wp_mt_subcategory';
	}
	
	public function getName() {
		return "Unterkategorien";
	}
	
	public static function insert($data) {
		$data['path'] = MT_Functions::nameToPath($data['name']);
		parent::insert($data);
	}

}