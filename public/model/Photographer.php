<?php

class MT_Photographer extends MT_Common {

	/**
	 * Photographers path
	 *
	 * @var string
	 */
	public static $photographersPath = 'Fotograf/';
	
	public function __construct($id = NULL) {
		parent::__construct(self::getTableName(), $id);
	}
	
	public function __toString() {
		return 'photographer';
	}
	
	public static function getTableName() {
		return 'wp_mt_photographer';
	}
	
	public function getName() {
		return 'Fotografen';
	}
	
	public static function insert($data) {
		$data['date'] = time();
		parent::insert($data);
	}
	
	public function isDeletable() {
		return !empty($this->id);
	}
	
	######## Check ########
	
	/**
	 * Gibt true zurück, wenn mehr als ein Bild von dem Photographen
     * exisitiert.
	 *
	 * @param	int		$id		Photographer's ID
	 * @return	boolean				True, if photographer has more then one phot
	 */
	public function hasPhotos() {
		return ($this->getNumPhotos > 0);
	}	
	
}