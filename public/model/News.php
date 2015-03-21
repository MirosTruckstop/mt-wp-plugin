<?php

class MT_News extends MT_Common {
	
	protected $belongsTo = 'gallery';

	public function __construct($id = NULL) {
		parent::__construct('wp_mt_news', $id);
	}
	
	public function __toString() {
		return 'news';
	}
	
	public function insert($data) {
		$data['date'] = time();
		parent::insert($data);
	}
	
	public function isDeletable() {
		return !empty($this->id);
	}
	
	######## Get ########
	
	public function getName() {
		return 'News';
	}
	
	/**
	 * Gibt den Zeitstempel der letzten Neuigkeit zurück
	 *
	 * @return	int	Latest news timestamp
	 */
	public function getLatestNewsTimestamp() {
		return parent::get_max('date');
	}
}