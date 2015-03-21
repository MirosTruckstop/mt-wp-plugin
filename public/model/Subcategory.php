<?php
/**
 * Service class for categories and subcategories
 *
 * @category   MT
 */
class MT_Subcategory extends MT_Common {
	
	protected $belongsTo = 'category';
	
	public function __construct($id = NULL) {
		parent:: __construct('wp_mt_subcategory', $id);
	}
	
	public function __toString() {
		return 'subcategory';
	}
	
	public function getName() {
		return "Unterkategorien";
	}
	
	public function insert($data) {
		$data['path'] = MT_Functions::nameToPath($data['name']);
		parent::insert($data);
	}

}