<?php

class MT_View_Category {

	/**
	 * Gallery path
	 *
	 * @var string
	 */
	public static $_galleryPath = '../galerie/';

	private $item;

	/**
	 * [...]
	 *
	 * @param	int	$id		Categories ID
	 * [...]
	 */
	public function __construct($id) {
		$this->item = (new MT_Category($id))->getOne(array('id', 'name', 'description'));
		
		if (empty($this->item)) {
			throw new Exception('Diese Kategorie existiert nicht');
		}
	}

//	public function outputTitle()
//	{
//		echo _($this->_name);
//	}
//        
//        public function outputDescription()
//	{
//		echo "Übersicht über alle Fotogalerien der Kategorie " . $this->_name;
//	}

	public function outputContent() {
		echo '<h2>'.$this->item->name.'</h2>';
		
		if( !empty( $this->item->description ) ) {
			echo '<p>' . $this->item->description . '</p>';
		}
		$this->_outputContentGalleries();
	}

	/**
	 * Outputs subcategories and galleries of the category (Form: unordered list
	 * (and table))
	 *
	 * @return	void
	 */
	private function _outputContentGalleries() {		
		$counter = 0;
		$galerien_hauptparkplatz = '';		// Speicher Links zum Hauptparkplatz zwischen, um sie später auszugeben

		// Construct query
		$query = (new MT_QueryBuilder())
			->from('gallery', array('id AS galleryId', 'name AS galleryName', 'hauptparkplatz', 'updated'))
			->joinLeft('subcategory', TRUE, array('id AS subcategoryId', 'name AS subcategoryName'))
			->whereEqual('wp_mt_gallery.category', $this->item->id)
			->orderBy(array('wp_mt_subcategory.id', 'wp_mt_gallery.name'));

		foreach ($query->getResult('ARRAY_A') as $row) {
			$counter++;
			$tmpGallery = new MT_Gallery($row['galleryId']);

			// Link der Galerie zum Hauptparkplatz
			if( !empty( $row['hauptparkplatz'] ) ) {
				$galerien_hauptparkplatz .= '
				<li>' . MT_Functions::getLinkToHauptparkplatz( $row['hauptparkplatz'], $row['galleryName'] ) . '</li>';
			}


			// Anfang der Uebersicht
			if( $counter == 1 ) {
				$numGalleries = MT_Gallery::getNumGalleries($this->item->id , $row['subcategoryId']);
				
				if( !empty($row['subcategoryName']) ) {
					echo '
			<h2>'.$row['subcategoryName'].'</h2>';
				}

				// Falls mindestens eine der Gallerien mit dem Hauptparkplatz verknüpft ist
				$checkLinkToHauptparkplatz = MT_Gallery::checkLinkToHauptparkplatz($this->item->id, $row['subcategoryId']);
				if( $checkLinkToHauptparkplatz ) {
						
					// Beginn der Tabelle
					echo '
			<table class="table_hoch" style="width: 100%">
				<colgroup>
   					<col width="50%">
   					<col width="50%">
				</colgroup>
				<tr>
					<th>Parkplatz 2</th>
					<th>Hauptparkplatz</th>
				</tr>
				<tr>
					<td>';
				}
 					
 				// Beginn der Liste
  				echo '
  			<ul>';
  			}

			// Ausgabe der Galerien
			$this->_outputListItem(self::$_galleryPath . $row['galleryId'], $row['galleryName'], MT_Photo::getCount($row['galleryId']), $tmpGallery->checkGalleryIsNew());

			// Ende der Uebersicht
				// Anzahl der Galerien in dem Bereich bzw in der Kategorie in dem Bereich
			if( $counter == $numGalleries ) {
				echo "
			</ul>";
			
				// Links zum Hauptparkplatz
				if( $checkLinkToHauptparkplatz ) {
					echo '
					</td>
					<td>
						<ul>
						'.$galerien_hauptparkplatz.' 
						</ul>
					</td>
				</tr>
			</table>';
				}
					
				// Variablen zuruecksetzen
				$counter = 0;
				$galerien_hauptparkplatz = '';
			}
		}
	}


	/**
	 * Output list item
	 *
	 * @param	string	$link		Link
	 * @param	string	$name		Link name
	 * @param	string	$numPhotos	Number of photos in gallery
	 * @param	boolean	$isNew		If gallery is new
	 * @return	void
	 */
	private function _outputListItem($link, $name, $numPhotos, $isNew) {
  		if($isNew) {
			$newPhotos = '<span class="new">Neue Bilder</span>';
		}
		echo '<li><a href="' . $link . '">' . $name . '</a>&nbsp;<span class="style_grew">(' . $numPhotos . ')</span>'. $newPhotos . '</li>';
	}
}
?>