<?php
namespace MT\WP\Plugin\Frontend\View;

use \Exception as Exception;
use MT\WP\Plugin\Api\MT_Category;
use MT\WP\Plugin\Api\MT_Gallery;
use MT\WP\Plugin\Api\MT_Photo;
use MT\WP\Plugin\Common\MT_QueryBuilder;

/**
 * Category view, i.e. display category properties and its galleries.
 */
class MT_View_Category extends MT_View_Common
{

	/**
	 * Gallery path
	 *
	 * @var string
	 */
	public static $_galleryPath = '../galerie/';

	private $item;

	public function __construct($id)
	{
		$this->item = (new MT_Category($id))->getOne(array('id', 'name', 'description'));
		
		if (empty($this->item)) {
			throw new Exception(__('Diese Kategorie existiert nicht', MT_NAME));
		}
		
		parent::setTitle($this->item->name);
		parent::setDescription(__('Übersicht über alle Fotogalerien der Kategorie', MT_NAME).' '.$this->item->name);
	}

	public function outputContent()
	{
		if (!empty($this->item->description)) {
			echo '<p>' . $this->item->description . '</p>';
		}
		$this->_outputContentGalleries();
	}

	/**
	 * Outputs subcategories and galleries of the category (Form: unordered list
	 * (and table))
	 *
	 * @return void
	 */
	private function _outputContentGalleries()
	{
		$counter = 0;

		// Construct query
		$query = (new MT_QueryBuilder())
			->from('gallery', array('id AS galleryId', 'name AS galleryName'))
			->joinLeft('subcategory', true, array('id AS subcategoryId', 'name AS subcategoryName'))
			->whereEqual('wp_mt_gallery.category', $this->item->id)
			->orderBy(array('wp_mt_subcategory.id', 'wp_mt_gallery.name'));

		foreach ($query->getResult('ARRAY_A') as $row) {
			$counter++;
			$tmpGallery = new MT_Gallery($row['galleryId']);

			// Anfang der Uebersicht
			if ($counter == 1) {
				$numGalleries = MT_Gallery::getNumGalleries($this->item->id, $row['subcategoryId']);
				
				if (!empty($row['subcategoryName'])) {
					echo '<h3>'.$row['subcategoryName'].'</h3>';
				}

				// Beginn der Liste
				echo '<ul>';
			}

			// Ausgabe der Galerien
			$numPhotos = MT_Photo::getCount($row['galleryId']);
			if ($numPhotos > 0) {
				$this->_outputListItem(self::$_galleryPath.$row['galleryId'], $row['galleryName'], MT_Photo::getCount($row['galleryId']), $tmpGallery->checkGalleryIsNew());
			}

			// Ende der Uebersicht
				// Anzahl der Galerien in dem Bereich bzw in der Kategorie in dem Bereich
			if ($counter == $numGalleries) {
				echo '</ul>';
					
				// Variablen zuruecksetzen
				$counter = 0;
			}
		}
	}

	/**
	 * Output list item
	 *
	 * @param string  $link      Link
	 * @param string  $name      Link name
	 * @param string  $numPhotos Number of photos in gallery
	 * @param boolean $isNew     If gallery is new
	 *
	 * @return void
	 */
	private function _outputListItem($link, $name, $numPhotos, $isNew)
	{
		if ($isNew) {
			$newPhotos = '<span class="new">'.__('Neue Bilder', MT_NAME).'</span>';
		}
		echo '<li><a href="' . $link . '">' . $name . '</a>&nbsp;<span class="style_grew">(' . $numPhotos . ')</span>'. $newPhotos . '</li>';
	}
}
