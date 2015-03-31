<?php

class MT_View_PhotoEdit extends MT_Admin_View_Common {

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
	private $_linkOfThisSite = '?page=mt-photo-add';
        
	public function __construct($galleryId = NULL, $page) {
		parent::__construct(new MT_Gallery($galleryId), $page);
		parent::setFields(array(
			new MT_Admin_Field(NULL, 'Bild'),
			new MT_Admin_Field(NULL, 'Galerie / Fotograf'),
			new MT_Admin_Field(NULL, 'Beschreibung')			
		));
		parent::setPerPage(10);
		parent::setSortIsActive();
		
		// Set title
		if ($this->model->hasId()) {
			parent::setTitle('Galerie "'.$this->model->get_attribute('name').'"');
		} else {
			parent::setTitle('Bilder "Neue Bilder"');
		}
		
		// Set query
		$query = (new MT_QueryBuilder())
			->from('photo', array('id', 'path', 'date', 'gallery', 'description', 'photographer'))
			->limitPage($this->page, $this->perPage);
		if($this->model->hasId()) {
			$query->whereEqual('gallery', $this->model->getId())
				->whereEqual('`show`', '1')
				->orderBy('date DESC');
		} else {
			$query->whereEqual('`show`', '0')
				->orderBy('date DESC');
		}
		parent::setQuery($query);

        $this->_updatePhotos(stripslashes_deep($_POST['data']));
	}
        
	/**
	 * Update photos in data base.
	 * 
	 * @param array $photos Photo ID's as keys an arrays as values
	 */
	private function _updatePhotos($data) {
		if(!empty($data)) {
			foreach ($data as $index => $item) {
				// Nur wenn Checkbox aktiviert ist, wird Foto aktualisert
				if (array_key_exists('checked', $item)) {
					unset($data[$index]['checked']);

					// Neue Bilder
					if (!($this->model->hasId())) {
						$date = $data[$index]['date'];
						// If date field is just the ordering number
						if (strlen($date) <= 2) {
							// Item with the lowest ordering integer (zero) should
							// have the highest timestamp.
							// 9: Ordering index from 0 to 0
							// 5: A little distance between each item
							$data[$index]['date'] = time() + (9-$date) * 5;
						}
						// Show picture
						$data[$index]['show'] = 1;
					}
				} else {
					unset($data[$index]);
				}
			}
		}
		
		if(!empty($data)) {
			if (parent::updateOrInsertAll($data)) {
				MT_Functions::box( 'save' );
			} else {
				MT_Functions::box( 'exception', 'TODO: Fehler beim Einfügen');					
			}
		}
	}
	
	protected function outputHeadMessages() {
		if((!$this->model->hasId()) ) {
			echo '<p>Insgesamt wurden <b>'.MT_Photo::getCountNewPhotos().' neue Bilder</b> gefunden! (Letzte Suche: ' . date( 'd.m.Y - H:i', get_option( 'datum_letzte_suche') ) . ' | <a href="' . $this->_linkOfThisSite . '&action=search">Neue Suche</a>)</p>';
		}
	}

	protected function _outputTableNavBottom() {
		echo MT_Functions::submitButton();
		echo '&#160;'.MT_Functions::cancelButton('?page=mt-' . $this->model->name());
	}

	/**
	 * Output photos (Form: table row)
	 *
	 * @return void
	 */
	protected function _outputTableBody(){
		$fields = array();
		$fields['id'] = new MT_Admin_Field('id', NULL, 'hidden');
		$fields['checked'] = new MT_Admin_Field('checked', NULL, 'bool');
		$fields['path'] = new MT_Admin_Field('path', NULL, 'hidden');
		$fields['gallery'] = (new MT_Admin_Field('gallery', NULL, 'reference'))
							->setReference('gallery')
							->setRequired();
		$fields['photographer'] = (new MT_Admin_Field('photographer', NULL, 'reference'))->setReference('photographer');
		$fields['date'] = (new MT_Admin_Field('date', NULL, 'date', 'date'))->setMaxLength(10);
		$fields['description'] = new MT_Admin_Field('description', NULL, 'text', 'description-autocomplete');

		$counter = 0;			// Nummeriert die 8 Bilder
		foreach ($this->getResult() as $index => $item) {
			$file = MT_Photo::$__photoPath.$item->path;
		?>
			<tr class="tr-sort <?php echo ($counter % 2 == 1? ' alternate"' : ''); ?>">
				<td>
					<?php
					echo $fields['id']->getElement($item->id, $index);
					echo $fields['checked']->getElement(!$this->model->hasId(), $index);
					echo $fields['path']->getElement($file, $index);
					?>
				</td>
				<td><a href="?title=add&typ=photo&id=<?php echo $index; ?>"><img src="<?php echo $file; ?>" width="200px"></a></td>
				<td>
					<?php echo (empty($item->gallery) ? '<p><b>Achtung: Es wurde automatisch keine Galerie gefunden!<br>Bitte wählen sie eine aus:</b></p>' : ''); ?>
					<?php echo $fields['gallery']->getElement($item->gallery, $index); ?>
					<br /><br />
					<?php echo $fields['photographer']->getElement($item->photographer, $index); ?>
					<br /><br />
					<?php echo $fields['date']->getElement($item->date, $index);
					?>
				</td>
				<td><?php echo $fields['description']->getElement($item->description, $index); ?></td>
			</tr>
		<?php
			$counter++;
		}
	}
}
?>