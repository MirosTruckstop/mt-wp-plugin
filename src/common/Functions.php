<?php
/**
 * Collection of helper functions.
 * 
 * @package common
 */
abstract class MT_Functions {

	/**
	 * Output "selected"
	 *
	 * @param	string	$first		The first of two values to compare 
	 * @param	string	$second		The second argument, which gets compared to the first
	 * @return	void
	 */
	public static function selected( $first, $second ) {
		if( $first == $second ) {
			return ' selected';
		} else {
			return '';
		}
	}

    /**
      * Output information box.
      * 
      * @param   string      $typ    E.g. 'exception' or 'delete'
      * @param   string|null $text   Textmeldung
      * @return  void
      */
	public static function box( $typ, $text = NULL ) {
		if( $typ === 'exception' ) {
			$id = 'red';
			$text = 'Der folgende Fehler ist aufgetreten:<br><br>' . $text;
		}
		else if( $typ === 'save' ) {
			$id = 'green';
			$text = 'Daten wurden erfolgreich gespeichert!';
		}
		else if( $typ === 'delete' ) {
			$id = 'green';
			$text = 'Daten wurden erfolgreich gelöscht!';
		} 
		else if( $typ === 'notDelete' ) {
			$id = 'red';
			$text = 'Daten konnten nicht gelöscht werden!';
		}

		echo '<div class="box" id="' . $id . '">' . $text . '</div>';		
	}

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


	#_______ Button _______#

	/**
	 * Output button
	 *
	 * @param	string	$link	Link
	 * @param	string	$text	Text
	 * @param	string	$typ	Typ
	 * @return	void
	 */
	public static function button( $link, $text, $typ = 'button' ) {
		echo '<a class="button" href="' . $link . '">' . $text . '</a>';
	}
	
	public static function addButton($link) {
		return '<a href="' . $link . '" class="add-new-h2">Erstellen</a>';
	}

	/**
	 * Output submit button
	 *
	 * @return	void
	 */
	public static function submitButton() {
		echo '<input type="submit" value="Änderung speichern" class="button button-primary">';
	}

	/**
	 * Output cancel button
	 *
	 * @return	void
	 */
	public static function cancelButton( $link ) {
		if ( !empty( $link) ) {
			self::button($link, 'Abbrechen', 'button');
		} else {
			self::button( 'javascript:history.back()', 'Abbrechen', 'button');
		}
	}
        
	/**
	 * Überprüft ob ein String ein Timestamp ist.
	 *
	 * @param	string	$timestamp	Timestamp as string
	 * @return	boolean			True, if it is a timestamp
	 */
	public static function isTimestampInStringForm( $timestamp ) {
		return $timestamp == strval(intval($timestamp));
	}
	

	/**
	 * Output pagination
	 * 
	 * Note: $additionalLink is a workaround to fix links in admin area.
	 *
	 * @param	string|null	$id		Galleries ID
	 * @param	string		$page           Page number
	 * @param	string		$num            Number of pictures per page
	 * @param	string		$sort           Sortation
	 * @param	string          $baseUrl String added before pagination link
	 * @return	void 
	 */
	public static function __outputPagination($totalNumberOfItem, $page, $num, $sort, $baseUrl = NULL) {
		$resultString = '<div id="seiten_leiste"><p>';
		
		$anzahl_seiten = ceil($totalNumberOfItem / $num );

		// Eine Seite zurueck
		if($page > 1) {
			$resultString .= self::_outputPaginationLink($page - 1, $num, $sort, '« '.__('Zurück', MT_NAME), $baseUrl);
		} else {
			$resultString .= '<span class="style_grew">« '.__('Zurück', MT_NAME).'</span>';
		}
		$resultString .= '&nbsp;&nbsp;|&nbsp;&nbsp;<b>'.__('Seite', MT_NAME).'</b>';
	
        $points = TRUE;
                
		// Die einzelnen Seiten
		for($i = 1; $i <= $anzahl_seiten; $i++) {
			//echo '&nbsp;&nbsp;';
			if($i == $page) {
				$resultString .= '&nbsp;&nbsp;<b>' . $i . '</b>';
			} else if( abs($i - $page) < 10 || $i == 1 || $i == $anzahl_seiten ) {
				$resultString .= '&nbsp;&nbsp;';
				$resultString .= self::_outputPaginationLink($i, $num, $sort, $i, $baseUrl);
				$points = TRUE;
			} else if( $points ) {
				$resultString .= '&nbsp;&nbsp;...';
				$points = FALSE;
			}
		}
	
		// Eine Seite vor
		$resultString .= '&nbsp;&nbsp;|&nbsp;&nbsp;';
		if( $page == $anzahl_seiten ) {
			$resultString .= '<span class="style_grew">'.__('Weiter', MT_NAME).' »</span>';
		} else {
			$resultString .= self::_outputPaginationLink($page + 1, $num, $sort, __('Weiter', MT_NAME) . ' »', $baseUrl);
		}
		$resultString .= '</p></div>';
		return $resultString;
	}

	/**
	 * Output pagination's link
	 *
         * Note: $additionalLink is a workaround to fix links in admin area.
         * 
	 * @param	string	$page           Page number
	 * @param	string	$num            Number of photos per page
	 * @param	string	$sort           Photo sort
	 * @param	string	$text           Link text
         * @param       string  $additionalLink String added before pagination link
	 * @return	void
	 */
	private function _outputPaginationLink($page, $num, $sort, $text, $baseUrl) {
		return '<a href="'.$baseUrl.'page='.$page.'&num='.$num.'&sort='.$sort.'">'.$text.'</a>';
	}
	
}