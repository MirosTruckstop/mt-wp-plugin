<?php

class MT_View_PhotoEdit extends MT_Admin_Table_Common {


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
        
	public function __construct($galleryId = NULL) {
		parent::__construct(new MT_Photo(), 'widefat');
		parent::setFields(array(
			new MT_Admin_Field(NULL, '#'),
			new MT_Admin_Field(NULL, 'Bild'),
			new MT_Admin_Field(NULL, 'Galerie / Fotograf'),
			new MT_Admin_Field(NULL, 'Beschreibung')			
		));
		parent::setPerPage(8);
		
		$this->gallery = new MT_Gallery($galleryId);
		
		// Set title
		if ($this->gallery->hasId()) {
			parent::setTitle('Bilder "'.$this->gallery->get_attribute('name').'"');
		} else {
			parent::setTitle('Bilder "Neue Bilder"');
		}
		
		// Set query
		$query = (new MT_QueryBuilder())
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
		parent::setQuery($query);

        $this->_updatePhotos($_POST['photos']);
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
                            
					$data['path'] = MT_Photo::renameFile($id, $data['path'], $data['gallery']);
                            
					// Bilder einer Galerie
					if (($this->gallery->hasId())) {
						// Falls für Timestamp Quatsch eingeben wurde, behalte den alten.
						if ( !MT_Functions::isTimestampInStringForm($data['date']) ) {
							unset( $data['date'] );
							// TODO: info to user?
						}
					}
					// Neue Bilder
					else {
						$data['show'] = 1;
						$tmpDate += 2;
						$data['date'] = $tmpDate;
					}
					parent::update($data, array(
						'id' => $id
					));
				}
			}
		}
	}
	
	protected function outputHeadMessages() {
		if( !($this->gallery->hasId()) ) {
			echo '<p>Insgesamt wurden <b>'.MT_Photo::getCountNewPhotos().' neue Bilder</b> gefunden! (Letzte Suche: ' . date( 'd.m.Y - H:i', get_option( 'datum_letzte_suche') ) . ' | <a href="' . $this->_linkOfThisSite . '&action=search">Neue Suche</a>)</p>';
		}
		MT_Functions::__outputPagination($this->gallery->getId(), $this->page, $this->perPage, 'date', $this->_additionalLink . $this->gallery->getId() . '&');
	}

	protected function _outputTableNavBottom() {
		MT_Functions::__outputPagination($this->gallery->getId(), $this->page, $this->perPage, 'date', $this->_additionalLink . $this->gallery->getId() . '&');
		echo MT_Functions::submitButton();
		echo '&#160;'.MT_Functions::cancelButton('?page=mt-' . $this->model);
	}

	/**
	 * Output photos (Form: table row)
	 *
	 * @return void
	 */
	protected function _outputTableBody(){
		$fields = array();
		$fields['checked'] = new MT_Admin_Field('checked', NULL, 'bool');
		$fields['path'] = new MT_Admin_Field('path', NULL, 'hidden');
		$fields['gallery'] = (new MT_Admin_Field('gallery', NULL, 'reference'))
							->setReference('gallery')
							->setRequired();
		$fields['photographer'] = (new MT_Admin_Field('photographer', NULL, 'reference'))->setReference('photographer');
		$fields['date'] = (new MT_Admin_Field('date', NULL))->setMaxLength(10);
		$fields['description'] = new MT_Admin_Field('description', NULL, 'text');

		$counter = 0;			// Nummeriert die 8 Bilder
		foreach ($this->getResult() as $item) {
			$file = MT_Photo::$__photoPath.$item->path;
		?>
			<tr <?php echo ($counter % 2 == 1? 'class="alternate"' : ''); ?>>
				<td>
					<?php
					echo $fields['checked']->getElement(!$this->gallery->hasId(), $item->id);
					echo $fields['path']->getElement($file, $item->id);
					?>
				</td>
				<td><a href="?title=add&typ=photo&id=<?php echo $item->id; ?>"><img src="<?php echo $file; ?>" width="200px"></a></td>
				<td>
					<?php (empty($item->galleryId) ? '<p><b>Achtung: Es wurde automatisch keine Galerie gefunden!<br>Bitte wählen sie eine aus:</b></p>' : ''); ?>
					<?php echo $fields['gallery']->getElement($item->galleryId, $item->id); ?>
					<br /><br />
					<?php
					echo $fields['photographer']->getElement($item->photographerId, $item->id);
					echo $fields['date']->getElement($item->date, $item->id);
					?>
				</td>
				<td><?php echo $fields['description']->getElement($item->description, $item->id); ?></td>
			</tr>
		<?php
			$counter++;
		}
	}
}
?>