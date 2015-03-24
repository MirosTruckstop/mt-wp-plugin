<?php
/**
 * Service class for categories and subcategories
 *
 * @category   MT
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
	
	public static function insert($data) {
		$data['path'] = MT_Functions::nameToPath($data['name']);
		return parent::insert($data);
	}

}