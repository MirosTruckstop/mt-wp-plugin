<?php

class MT_News extends MT_Common {
	
	public function __construct($id = NULL) {
		parent::__construct($id);
	}
	
	public static function name() {
		return 'news';
	}
	
	public static function getName() {
		return 'News';
	}
	
	public static function insert($data) {
		$data['date'] = time();
		parent::insert($data);
	}
	
	public function isDeletable() {
		return !empty($this->id);
	}
	
	######## Get ########
	
	/**
	 * Gibt den Zeitstempel der letzten Neuigkeit zurück
	 *
	 * @return	int	Latest news timestamp
	 */
	public static function getLatestNewsTimestamp() {
		return parent::get_aggregate('MAX', 'date');
	}
}