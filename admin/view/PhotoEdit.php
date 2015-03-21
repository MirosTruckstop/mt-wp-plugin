<?php

class MT_View_PhotoEdit extends MT_Admin_Table_Common {

	/**
	 * Photo path
	 *
	 * @var string
	 */
	private $_photoPath = '../bilder/';


	private $gallery;

	
	/**
	 * Workaround to fix pagination link!
	 *
	 * @var string
	 */
	private $_additionalLink = 'title=add-photo&id=';
        
	/**
	 * Link to this site
	 * 
	 * Note: Workaround only
	 * 
	 * @var string
	 */
	private $_linkOfThisSite = '?title=add-photo';
        
	public function __construct($galleryId) {
		parent::__construct(new MT_Photo(), 'widefat');
		parent::setFields(array(
			new MT_Admin_Field(NULL, '#'),
			new MT_Admin_Field(NULL, 'Bild'),
			new MT_Admin_Field(NULL, 'Galerie / Fotograf'),
			new MT_Admin_Field(NULL, 'Beschreibun')			
		));
		parent::setPerPage(8);
		
		// GET
		$this->gallery = new MT_Gallery($galleryId);

		$action = $_GET['action'];
		
        $this->_updatePhotos($_POST['photos']);
		
		// Neue Bilder
		if( !$this->gallery->hasId() ) {

			new MT_Admin_PhotoSearch();
		}
	}
        
	/**
	 * Update photos in data base.
	 * 
	 * @param array $photos Photo ID's as keys an arrays as values
	 */
	private function _updatePhotos( $photos ) {
            if( !empty( $photos) ) {
                    $tmpDate = time();

                    foreach ($photos as $id => $data) {
                        // Nur wenn Checkbox aktiviert ist, wird Foto aktualisert
                        if (array_key_exists('checked', $data)) {
                            unset($data['checked']);
                            
							$photoM = new MT_Photo($id);
/*							
 * HERE weiter arbeiten
 * 
 */
                            $data['path'] = MT_Admin_Photo::__renamePhoto($data['path'], $id, $data['gallery']);
                            $data['path'] = str_replace( $this->_photoPath, '', $data['path'] );
                            
                            // Neue Bilder
                            if ( !($this->gallery->hasId()) ) {
                                $data['show'] = 1;
                                $tmpDate += 2;
                                $data['date'] = $tmpDate;
                            }
                            // Bilder einer Galerie
                            else {
                                // Falls für Timestamp Quatsch eingeben wurde, behalte den alten.
                                if ( !MT_Functions::isTimestampInStringForm( $data['date'] ) ) {
                                    unset( $data['date'] );
                                    // TODO: info to user?
                                }
                            }
							$this->model->update($data, array(
								'id' => $id
							));
                        }
                    }
            }
	}

	public function outputContent() {
		?>
			<div class="wrap">
			<h2>Bilder "<?php
		if( $this->gallery->hasId() ) { 
			echo $this->gallery->get_attribute('name');
		} else {
			echo 'Neue Bilder';
		}
			?>"</h2>
		<?php
			if( !($this->gallery->hasId()) ) {
				$photoSearch = new MT_Admin_PhotoSearch();
				$numNewPhotos = $photoSearch->getNumPhotos();
                            
				echo '
			<p>Insgesamt wurden <b>' . $numNewPhotos .' neue Bilder</b> gefunden! (Letzte Suche: ' . date( 'd.m.Y - H:i', get_option( 'datum_letzte_suche') ) . ' | <a href="' . $this->_linkOfThisSite . '&action=search">Neue Suche</a>)</p>';
			}
			if( ( $this->gallery->hasId() && $this->gallery->checkIsPhotoInGallery() ) || ( !( $this->gallery->hasId() ) && $numNewPhotos > 0)) {
				//echo '<div class="tablenav top">';
				MT_Functions::__outputPagination( $this->gallery->getId(), $this->page, $this->perPage, 'date', $this->_additionalLink . $this->gallery->getId() . '&');
				//echo "</div>";
				parent::_outputForm();
				MT_Functions::__outputPagination( $this->gallery->getId(), $this->page, $this->perPage, 'date', $this->_additionalLink . $this->gallery->getId() . '&');
			}
			echo "<div>";
	}
	
	protected function _outputTableNavBottom() {
		echo MT_Functions::submitButton();
		echo '&#160;'.MT_Functions::cancelButton( '?page=mt-' . $this->model );
	}

	/**
	 * Output photos (Form: table row)
	 *
	 * @return void
	 */
	protected function _outputTableBody(){
		$query = (new MT_QueryBuilder('wp_mt_'))
			->from('photo', array('id', 'path', 'date', 'gallery', 'description', 'photographer'))
			->limitPage($this->page, $this->perPage);
		if($this->gallery->hasId()) {
			$query->whereEqual('gallery', $this->gallery->getId())
				->whereEqual('`show`', '1')
				->orderBy('date DESC');
		} else {
			$query->whereEqual('`show`', '0')
				->orderBy('path ASC');
		}
		
		$counter = 0;			// Nummeriert die 8 Bilder
		foreach ($query->getResult() as $row) {
			$this->_outputTableRow($counter, $row->id, $row->path, $row->date, $row->gallery, $row->photographer, $row->description);
			$counter++;
		}
	}

	/**
	 * Output table row
	 *
	 * @param	string		$counter        	Number of row
	 * @param	string      	$id             	Photo ID
	 * @param	string      	$path          		Photo path
	 * @param	int      	$date          		Photo date timestamp
	 * @param	string|null 	$galleryId      	Photo gallery ID
	 * @param	string|null 	$photographerID		Photographers ID
	 $ @param	string|null 	$description    	Photo description
	 * @return	void
	 */
	private function _outputTableRow( $counter, $id, $path, $date, $galleryId = NULL, $photographerId = NULL, $description = NULL ) {
		$file =  $this->_photoPath . $path;
		?>
					<tr <?php echo ($counter % 2 == 1? 'class="alternate"' : ''); ?>>
						<td>
							<input
								type="checkbox"
								name="photos[<?php echo $id; ?>][checked]"
								value="checked" <?php MT_Functions::checked( $this->gallery->getId(), NULL); ?>>
							<input
								type="hidden"
								name="photos[<?php echo $id; ?>][photo_path]"
								value="<?php echo $file; ?>"></td>
						<td><a href="?title=add&typ=photo&id=<?php echo $id; ?>"><img src="<?php echo $file; ?>" width="200px"></a></td>
						<td>
							<?php
			if( empty( $galleryId ) ) {
						echo "<p><b>Achtung: Es wurde automatisch keine Galerie gefunden!<br>Bitte wählen sie eine aus:</b></p>";
			}
			?>
							<select name="photos[<?php echo $id; ?>][photo_gallery]" size="1">
								<?php echo MT_Functions::outputAllGalleries( $galleryId ) ; ?>
							</select><br /><br />
							<select name="photos[<?php echo $id; ?>][photo_photographer]" size="1">
								<?php $this->_outputAllPhotographers( $photographerId ); ?>
							</select>
							<input
								type="text"
								name="photos[<?php echo $id; ?>][photo_date]"
								value="<?php echo $date; ?>"
								maxlength="10" />
						</td>
						<td>
							<textarea
								name="photos[<?php echo $id; ?>][photo_description]"
								cols="28"
								rows="4"><?php echo $description; ?></textarea>
						</td>
					</tr>
		<?php
	}

	/**
	 * Output all photographers (Form: <option>)
	 *
	 * @param	string	$selectedPhotographer	Selected photographer
	 * @return	void
	 */
	private function _outputAllPhotographers($selectedPhotographer = 1) {
		$result = MT_Photographer::getAll(array('id', 'name'), 'name');
		foreach ($result as $row) {
			echo '<option value="'.$row->id.'" '.MT_Functions::selected($selectedPhotographer, $row->id).'>'.$row->name.'</option>';
		}
	}

}

?>