<?php

class MT_Photographer extends MT_Common {

	/**
	 * Photographers path
	 *
	 * @var string
	 */
	public static $photographersPath = 'fotograf/';
	
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
		return parent::insert($data);
	}
	
	public function isDeletable() {
		return !empty($this->id);
	}
	
	public function deleteOne() {
		// Only delete photographers with no photo
		if ($this->isDeletable() && MT_Photo::getNumPhotos($this->id) == 0) {
			return parent::delete('id = '.$this->id);
		}
		return FALSE;
	}
	
}