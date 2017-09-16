<?php
/**
 * Collection of helper functions.
 * 
 * @package common
 * @subpackage util
 */
abstract class MT_Util_Common {

	/**
	 * Returns $string, if $var is not empty.
	 *
	 * @param	string		$var		Variable
	 * @param	string		$string		String
	 * @return	void|string	$string
	 */
	public static function getIfNotEmpty( $var, $string ) {
		if( !empty ( $var ) ) {
			return $string;
		}
	}

	/**
	 * Creates array for user settings.
	 *
	 * @param	string	$sort	Sorting
	 * @param	string	$num	Number of images per page
	 * @return	array			An associative array containing the determined
	 * 							'sort', 'num'
	 */
	public static function getUserSettings($sort, $num, $page) {
		return array(
			'sort'	=> $sort,
			'num'	=> $num,
			'page'	=> $page
		);
	}

	/**
	 * Check of the given string is a timestamp.
	 *
	 * @param string $timestamp Timestamp as string
	 * @return boolean True, if it is a timestamp
	 */
	public static function isTimestampInStringForm( $timestamp ) {
		return $timestamp == strval(intval($timestamp));
	}
	
	/**
	 * Trims the value for the given key, when the key exists.
	 * 
	 * @param array $array
	 * @param string $key
	 */
	public static function trimArrayEntry(array &$array, $key) {
		if ( array_key_exists($key, $array) ) {
			$array[$key] = trim($array[$key]);
		}
	}

	/**
	 * Logs the given string in the default PHP log file.
	 */
	public static function log($string) {
		error_log(addslashes($string));
	}

}