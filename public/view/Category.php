<?php

class MT_View_Category {

	/**
	 * Gallery path
	 *
	 * @var string
	 */
	private $_galleryPath = '../galerie/';
    
	/**
	 * Categories ID
	 *
	 * @var string
	 */
	private $_id;

	/**
	 * Categories name
	 *
	 * @var string
	 */
	private $_name;
	
	/**
	 * Categories description
	 *
	 * @var string
	 */
	 private $_description;

	 private $gallery;
	 private $photo;

	/**
	 * [...]
	 *
	 * @param	int	$id		Categories ID
	 * [...]
	 */
	public function __construct($id) {
		$this->_id = $id;
            
		// Construct query
		$query = (new MT_QueryBuilder('wp_mt_'))
			->from('category', array('name', 'description'))
			->whereEqual('id', $this->_id);
		$item = $query->getResultOne();
		$this->_name = $item['name'];
		$this->_description = $item['description'];
		
		// Couldn't find category
		if( empty( $this->_name ) ) {
			unset( $this->_id );
			$this->_name = _("Fehler");
		}
		
		$this->gallery = new MT_Gallery();
		$this->photo = new MT_Photo();
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
		echo '<h2>'.$this->_name.'</h2>';
		
		if( isset( $this->_id ) ) {
			if( !empty( $this->_description ) ) {
				echo '
				<p>' . $this->_description . '</p>';
			}
			$this->_outputContentGalleries();
		}
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
		$query = (new MT_QueryBuilder('wp_mt_'))
			->from('gallery', array('id AS galleryId', 'name AS galleryName', 'hauptparkplatz', 'updated'))
			->joinLeft('subcategory', 'wp_mt_subcategory.id = wp_mt_gallery.subcategory', array('id AS subcategoryId', 'name AS subcategoryName'))
			->whereEqual('wp_mt_gallery.category', $this->_id)
			->orderBy(array('wp_mt_subcategory.id', 'wp_mt_gallery.name'));

		foreach ($query->getResult('ARRAY_A') as $row) {
			$counter++;
			$this->gallery = new MT_Gallery($row['galleryId']);

			// Link der Galerie zum Hauptparkplatz
			if( !empty( $row['hauptparkplatz'] ) ) {
				$galerien_hauptparkplatz .= '
				<li>' . MT_Functions::getLinkToHauptparkplatz( $row['hauptparkplatz'], $row['galleryName'] ) . '</li>';
			}


			// Anfang der Uebersicht
			if( $counter == 1 ) {
				$numGalleries = $this->gallery->getNumGalleries($this->_id , $row['subcategoryId']);
				
				if( !empty($row['subcategoryName']) ) {
					echo '
			<h2>'.$row['subcategoryName'].'</h2>';
				}

				// Falls mindestens eine der Gallerien mit dem Hauptparkplatz verknüpft ist
				$checkLinkToHauptparkplatz = $this->gallery->checkLinkToHauptparkplatz($this->_id, $row['subcategoryId']);
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
			$this->_outputListItem( $this->_galleryPath . $row['galleryId'], $row['galleryName'], $this->photo->getCount($row['galleryId']), $this->gallery->checkGalleryIsNew() );

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
	private function _outputListItem( $link, $name, $numPhotos, $isNew ) {
  		if( $isNew ) {
			$newPhotos = '<span class="new">' . _("Neue Bilder") . '</span>';
		} else {
			$newPhotos = NULL;
		}

		echo '
				<li><a href="' . $link . '">' . $name . '</a>&nbsp;<span class="style_grew">(' . $numPhotos . ')</span>'. $newPhotos . '</li>';
	}
}
?>