<?php
/**
 * Enthält einige hilfreiche Funktionen
 *
 */
abstract class MT_Functions {

	public static function nameToPath($name) {
		// First array: search, second array: replace
		return str_replace(array(' ', '.'), array('_', ''), $name);
	}
	
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
	 * Output "checked"
	 *
	 * @param	string	$first		The first of two values to compare 
	 * @param	string	$second		The second argument, which gets compared to the first
	 * @return	void
	 */
	public function checked($first, $second) {
		if( $first == $second ) {
			echo ' checked';
		} else {
			echo '';
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
	 * Returns link to Hauptparkplatz
	 *
	 * @param	string	$link	Link number
	 * @param	string	$name	Link name
	 * @return	string			Link
	 */
	public static function getLinkToHauptparkplatz($link, $name) {
		return '<a href="http://www.rosensturm.de/'.$link.'.html" target="_blank">'.$name .'</a>';
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
	public static function getUserSettings($sort, $num) {
		// User ip
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
		if($num != 5 && $num != 10 && $num != 15) {
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
				'id' => $ip,
				'num' => $num,
				'sort' => $sort				
			));
		} else if( $sort != $sortDb || $num != $numDb ) {		// Gepeicherten Werte wurden verändert
			$mangementTemp->update(array(
				'num' => $num,
				'sort' => $sort
			));
		}

		return array(
			'sort'	=> $sort,
			'num'	=> $num
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
//		echo '<a class="button" href="' . $link . '"><button type="' . $typ . '">' . $text . '</button></a>';
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
		return ( strlen($timestamp) == 10 && $timestamp === strval(intval($timestamp)) );
	}
		

	/**
	 * Outputs all galleries (Form: <optgroup>, <option>)
	 *
	 * @param	string|null		$selectedGallery	Selected gallery
	 * @return	void
	 */
	public static function outputAllGalleries( $selectedGallery = NULL ) {	
		$resultString = '';
		$tempOptgroup = NULL;
		
		$query = (new MT_QueryBuilder('wp_mt_'))
			->from('gallery', array( 'id' ))
			->select('wp_mt_category.name as categoryName')
			->select('wp_mt_subcategory.name as subcategoryName')
			->select('wp_mt_gallery.name as galleryName')
			->join('category', 'wp_mt_category.id = wp_mt_gallery.category')
			->joinLeft('subcategory', 'wp_mt_subcategory.id = wp_mt_gallery.subcategory')
			->orderBy(array('wp_mt_category.name', 'wp_mt_subcategory.name', 'wp_mt_gallery.name'));
		//TODO IS CALLED EACH TIME
		//echo $query;
		$result = $query->getResult('ARRAY_A');
		foreach ($result as $row) {
			$optgroup = $row['categoryName'] . MT_Functions::getIfNotEmpty( $row['subcategoryName'], ' > ' . $row['subcategoryName'] );
			if( $tempOptgroup != $optgroup ) {
				$tempOptgroup = $optgroup;
				// Nicht beim ersten Mal beenden
				if( isset( $tempOptgroup ) ) {
					$resultString .= '</optgroup>';
				}
				$resultString .= '
				<optgroup label="' . $optgroup .'">';
			}
			$resultString .= '
					<option value="'.$row['id'].'"'.($row['id'] == $selectedGallery ? ' selected' : '').'>'.$row['galleryName'].'</option>
			';
		}
		$resultString .= '</optgroup>';
		return $resultString;
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
         * @param       string          $additionalLink String added before pagination link
	 * @return	void 
	 */
	public static function __outputPagination( $id, $page, $num, $sort, $additionalLink = NULL ) {
		$photo = new MT_Photo();
		// Anzahl der Seiten in der Galerie
		if( isset( $id ) ) {
			$anzahl_seiten = $photo->getNumPages($id, $num);
		} else {
			$anzahl_seiten = $photo->getNumPages('neue_bilder', $num);
		}

		echo '
				<div id="seiten_leiste">
					<p>';
	
		// Eine Seite zurueck
		if( $page != 1) {
			self::_outputPaginationLink( $page - 1, $num, $sort, '« ' . _("Zurück"), $additionalLink);
		} else {
			echo '<span class="style_grew">« ' . _("Zurück") . '</span>';
		}
		echo '&nbsp;&nbsp;|&nbsp;&nbsp;<b>' . _("Seite") . ':</b>';
	
                $points = TRUE;
                
		// Die einzelnen Seiten
		for($page_naechste = 1; $page_naechste <= $anzahl_seiten; $page_naechste++) {
			//echo '&nbsp;&nbsp;';
			if( $page_naechste == $page ) {
				echo '&nbsp;&nbsp;<b>' . $page_naechste . '</b>';
			} else if( abs($page_naechste - $page) < 10 || $page_naechste == 1 || $page_naechste == $anzahl_seiten ) {
                                echo '&nbsp;&nbsp;';
                                self::_outputPaginationLink( $page_naechste, $num, $sort, $page_naechste, $additionalLink );
                                $points = TRUE;
                        } else if( $points ) {
                                echo '&nbsp;&nbsp;...';
                                $points = FALSE;
			}
		}
	
		// Eine Seite vor
		echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
		if( $page != $anzahl_seiten ) {
			self::_outputPaginationLink( $page + 1, $num, $sort, _("Weiter") . ' »', $additionalLink);
		} else {
			echo '<span class="style_grew">' . _("Weiter") . ' »</span>';
		}
		echo "</p>
			</div>";
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
	private function _outputPaginationLink( $page, $num, $sort, $text, $additionalLink = 'NULL' ) {
		echo '<a href="?' . $additionalLink . 'page=' . $page . '&num=' . $num . '&sort=' . $sort . '">' . $text . '</a>';
	}
	
}