<?php
namespace MT\WP\Plugin\Backend\View\Crud;

use \Exception as Exception;
use MT\WP\Plugin\Api\MT_Gallery;
use MT\WP\Plugin\Api\MT_Photo;
use MT\WP\Plugin\Backend\Model\Form\MT_Admin_Field;
use MT\WP\Plugin\Backend\Model\MT_Admin_Model_File;
use MT\WP\Plugin\Common\MT_QueryBuilder;
use MT\WP\Plugin\Common\Util\MT_Util_Html;

/**
 * Admin edit photos view, i.e. view to edit photos.
 */
class MT_Admin_View_PhotoEdit extends MT_Admin_View_Common
{

	const CSS_CLASS_EDITABLE_DATA = 'editable-data';
	
	/**
	 * When photos get uploaded, they get the current date as timestamp. But
	 * between two phots there should be a distance of some seconds.
	 */
	const SECONDS_BETWEEN_PHOTOS = 10;
	
	private $gallery;

	/**
	 * Link to this site
	 *
	 * Note: Workaround only
	 *
	 * @var string
	 */
	private $_linkOfThisSite = '?page=mt-photo-add';

	public function __construct($galleryId = null, $page = null)
	{
		parent::__construct(new MT_Photo(), $page);
		parent::setFields(array(
			new MT_Admin_Field(null, 'Bild'),
			new MT_Admin_Field(null, 'Galerie / Fotograf'),
			new MT_Admin_Field(null, 'Beschreibung')
		));
		parent::setPerPage(10);
		
		$this->gallery = new MT_Gallery($galleryId);
		
		// Set title
		if ($this->gallery->hasId()) {
			parent::setTitle('Galerie "'.$this->gallery->get_attribute('name').'"');
		} else {
			parent::setTitle('Bilder "Neue Bilder"');
			parent::setSortIsActive();
		}
		
		// Set query
		$query = (new MT_QueryBuilder())
			->from('photo', array('id', 'path', 'date', 'gallery', 'description', 'detected_text', 'photographer'))
			->limitPage($this->page, $this->perPage);
		if ($this->gallery->hasId()) {
			$query->whereEqual('gallery', $this->gallery->getId())
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
	 * @param array $data Photo ID's as keys an arrays as values
	 *
	 * @return void
	 */
	private function _updatePhotos($data)
	{
		if (!empty($data)) {
			foreach ($data as $index => $item) {
				// Update photo only, if checkbox is activated. Otherwise
				// continue
				if (!array_key_exists('checked', $item)) {
					unset($data[$index]);
					continue;
				}

				unset($data[$index]['checked']);

				// New photo
				if (!($this->gallery->hasId())) {
					$date = $data[$index]['date'];
					// If date field is just the ordering number
					if (strlen($date) <= 2) {
						// Item with the lowest ordering integer (zero) should
						// have the highest timestamp.
						// 9: Ordering index from 0 to 9
						$data[$index]['date'] = time() + ($this->perPage - 1 - $date) * self::SECONDS_BETWEEN_PHOTOS;
					}
					// Show picture
					$data[$index]['show'] = 1;
				} else { // Photo in a gallery
					// If the ID of the gallery did not change
					if ($data[$index]['gallery'] == $this->gallery->getId()) {
						unset($data[$index]['gallery']);
					}
				}
			}
		}
		
		if (!empty($data)) {
			try {
				parent::updateOrInsertAll($data);
				MT_Util_Html::box('save');
			} catch (Exception $e) {
				MT_Util_Html::box('exception', $e->getMessage());
			}
		}
	}
	
	protected function outputHeadMessages()
	{
		if ((!$this->gallery->hasId())) {
			echo '<p>Insgesamt wurden <b>'.MT_Photo::getCountNewPhotos().' neue Bilder</b> gefunden! (Letzte Suche: ' . date('d.m.Y - H:i', get_option('datum_letzte_suche')) . ' | <a href="' . $this->_linkOfThisSite . '&action=search">Neue Suche</a>)</p>';
		}
	}

	protected function _outputTableNavBottom()
	{
		echo MT_Util_Html::submitButton();
		echo '&#160;'.MT_Util_Html::cancelButton('?page=mt-' . $this->model->name());
	}

	/**
	 * Output photos (Form: table row)
	 *
	 * @return void
	 */
	protected function _outputTableBody()
	{
		$fields = array();
		$fields['id'] = new MT_Admin_Field('id', null, 'hidden');
		$fields['checked'] = new MT_Admin_Field('checked', null, 'bool');
		$fields['path'] = new MT_Admin_Field('path', null, 'hidden');
		$fields['gallery'] = (new MT_Admin_Field('gallery', null, null, self::CSS_CLASS_EDITABLE_DATA))
			->setReference('gallery')
			->setRequired();
		$fields['photographer'] = (new MT_Admin_Field('photographer', null, null, self::CSS_CLASS_EDITABLE_DATA))
			->setReference('photographer');
		$fields['date'] = (new MT_Admin_Field('date', null, 'date', 'date '.self::CSS_CLASS_EDITABLE_DATA))
			->setMaxLength(19);
		$fields['description'] = new MT_Admin_Field('description', null, 'text', 'description-autocomplete '.self::CSS_CLASS_EDITABLE_DATA);
		$fields['detected_text'] = new MT_Admin_Field('detected_text', null, 'hidden');

		$counter = 0;			// Nummeriert die 8 Bilder
		foreach ($this->getResult() as $index => $item) {
			$file = MT_Admin_Model_File::getPathFromDbPath($item->path);
			?>
			<tr class="tr-sort <?php echo ($counter % 2 == 1? ' alternate"' : ''); ?>">
				<td>
					<?php
						echo $fields['id']->getElement($item->id, $index);
						echo $fields['checked']->getElement(!$this->gallery->hasId(), $index);
						// Hidden fields:
						echo $fields['path']->getElement($file, $index);
						echo $fields['detected_text']->getElement($item->detected_text, $index);
					?>
				</td>
				<td><img src="<?php echo $file; ?>" width="200px"></td>
				<td>
					<?php //echo (empty($item->gallery) ? '<p><b>Achtung: Es wurde automatisch keine Galerie gefunden!<br>Bitte wählen sie eine aus:</b></p>' : ''); ?>
					<?php echo $fields['gallery']->getElement($item->gallery, $index); ?>
					<br /><br />
					<?php echo $fields['photographer']->getElement($item->photographer, $index); ?>
					<br /><br />
					<?php echo $fields['date']->getElement($item->date, $index);
					?>
				</td>
				<td><?php
				echo $fields['description']->getElement($item->description, $index);
				if (!empty($item->detected_text)) {
					echo '<br>'.__('Im Bild erkannter Text').': '.$item->detected_text;
				}
				?></td>
			</tr>
			<?php
			$counter++;
		}
	}
	
	protected function getPagination()
	{
		return MT_Util_Html::__outputPagination(MT_Photo::getCount($this->gallery->getId()), $this->page, $this->perPage, 'date', '?page=mt-photo&mtId='.$this->gallery->getId().'&mt');
	}
}
