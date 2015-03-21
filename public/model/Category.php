<?php
/**
 * Service class for categories and subcategories
 *
 * @category   MT
 */
class MT_Category extends MT_Common {
	
	public function __construct($id = NULL) {
		parent::__construct('wp_mt_category', $id);
	}
	
	public function __toString() {
		return 'category';
	}


	public function insert($data) {
		$data['path'] = MT_Functions::nameToPath($data['name']);
		parent::insert($data);
	}
	
	public function getName() {
		return "Kategorien";
	}
	

}