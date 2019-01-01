<?php
namespace MT\WP\Plugin\Api;

/**
 * News view, i.e. display all news entries.
 */
class MT_News extends MT_Common
{
	
	public function __construct($id = null)
	{
		parent::__construct($id);
	}
	
	public static function name()
	{
		return 'news';
	}
	
	public static function getName()
	{
		return 'News';
	}
	
	public static function insert(array $data)
	{
		$data['date'] = time();
		return parent::insert($data);
	}
	
	public function isDeletable()
	{
		return !empty($this->id);
	}
	
	public function deleteOne()
	{
		if ($this->isDeletable()) {
			return parent::delete('id = '.$this->id);
		}
		return false;
	}
	
	/**
	 * Gibt den Zeitstempel der letzten Neuigkeit zurück
	 *
	 * @return int	Latest news timestamp
	 */
	public static function getLatestNewsTimestamp()
	{
		return parent::get_aggregate('MAX', 'date');
	}
}
