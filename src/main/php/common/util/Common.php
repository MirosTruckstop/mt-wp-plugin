<?php
namespace MT\WP\Plugin\Common\Util;

/**
 * Collection of helper functions.
 */
abstract class MT_Util_Common
{

	/**
	 * Returns $string, if $var is not empty.
	 *
	 * @param string $var    Variable
	 * @param string $string String
	 *
	 * @return void|string $string
	 */
	public static function getIfNotEmpty($var, $string)
	{
		if (!empty($var)) {
			return $string;
		}
	}

	/**
	 * Creates array for user settings.
	 *
	 * @param string $sort Sorting
	 * @param string $num  Number of images per page
	 * @param string $page Page
	 *
	 * @return array An associative array
	 */
	public static function getUserSettings($sort, $num, $page)
	{
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
	 *
	 * @return boolean True, if it is a timestamp
	 */
	public static function isTimestampInStringForm($timestamp)
	{
		return $timestamp == strval(intval($timestamp));
	}
	
	/**
	 * Trims the value for the given key, when the key exists.
	 *
	 * @param array  $array Array
	 * @param string $key   Key
	 *
	 * @return void
	 */
	public static function trimArrayEntry(array &$array, $key)
	{
		if (array_key_exists($key, $array)) {
			$array[$key] = trim($array[$key]);
		}
	}

	/**
	 * Logs the given string in the default PHP log file.
	 *
	 * @param string $string Log message
	 *
	 * @return void
	 */
	public static function log($string)
	{
		error_log(addslashes($string));
	}
}
