<?php
/**
 * Collection of helper functions.
 * 
 * @package common
 * @subpackage util
 */
abstract class MT_Util_Common {

	/**
	 * Gibt $string zurück, falls $var nicht leer ist.
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
	 * Besucherdaten speichern / verwalten
	 *
	 * @param	string	$sort	Sortation
	 * @param	string	$num	Number of images per page
	 * @return	array			An associative array containing the determined
	 * 							'sort', 'num'
	 */
	public static function getUserSettings($sort, $num, $page) {
/*		// User ip
		$ip = $_SERVER['REMOTE_ADDR'];
		
		$mangementTemp = new MT_ManagementTemp($ip);
		$item = $mangementTemp->getOne(NULL, 'ARRAY_A');
		$sortDb = $item['sort'];
		$numDb = $item['num'];

		// Ist die Sortierung noch nicht gesetzt
		if( $sort !== 'date' && $sort !== '-date' ) {
			// Falls vorhanden, wähle den gespeicherten Wert
			if( !empty( $sortDb ) ) {
				$sort = $sortDb;
			} else {
				$sort = 'date'; // default
			}
		}

		// Ist die Anzahl der Bilder pro Seite noch nicht gesetzt
		if($num != 5 && $num != 10 && $num != 15 && $num != 200) {
			// Falls vorhanden, wähle den gespeicherten Wert
			if( !empty( $numDb ) ) {
				$num = $numDb;
			} else {
				$num = 10; // default
			}
		}
					
		// Werte des Besuchers in Datenbank speichern
		if( empty( $sortDb ) ) {								// Werte sind noch nicht gespeichert
			$mangementTemp->insert(array(
				'ip' => $ip,
				'num' => $num,
				'sort' => $sort				
			));
		} else if( $sort != $sortDb || $num != $numDb ) {		// Gepeicherten Werte wurden verändert
			$mangementTemp->update(array(
				'num' => $num,
				'sort' => $sort
			));
		}*/

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