<?php

class MT_Photographer extends MT_Common {

	/**
	 * Photographers path
	 *
	 * @var string
	 */
	public static $photographersPath = 'Fotograf/';
	
	public function __construct($id = NULL) {
		parent::__construct($id);
	}
	
	public static function name() {
		return 'photographer';
	}
	
	public static function getName() {
		return 'Fotografen';
	}
	
	public static function insert($data) {
		$data['date'] = time();
		parent::insert($data);
	}
	
	public function isDeletable() {
		return !empty($this->id);
	}
	
}