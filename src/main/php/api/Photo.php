<?php
namespace MT\WP\Plugin\Api;

use MT\WP\Plugin\Backend\Model\MT_Admin_Model_File;
use MT\WP\Plugin\Common\MT_QueryBuilder;
use MT\WP\Plugin\Common\Util\MT_Util_Common;

/**
 * Model of a photo.
 */
class MT_Photo extends MT_Common
{

	/**
	 * Photo path
	 *
	 * @var string
	 */
	const GALLERY_PATH_ABS = '/bilder/galerie';

	
	public function __construct($id = null)
	{
		parent::__construct($id);
	}

	public static function name()
	{
		return 'photo';
	}
	
	public function isDeletable()
	{
		return !empty($this->id);
	}

	public function deleteOne()
	{
		if ($this->isDeletable()) {
			if (MT_Admin_Model_File::deletePhoto(parent::get_attribute('path'))) {
				return parent::delete('id = '.$this->id);
			}
		}
		return false;
	}
	
	/**
	 * Datum des zuletzt eingestellten Bildes.
	 *
	 * @param string $galleryId ID of the gallery
	 *
	 * @return string Latest photo timestamp
	 */
	public static function getLatestPhotoDate($galleryId = null)
	{
		if (!empty($galleryId)) {
			$whereCondition = 'gallery = '.$galleryId.' AND';
		}
		return parent::get_aggregate('MAX', 'date', $whereCondition."`show` = 1");
	}
	
	/**
	 * @param array $data           Data
	 * @param array $conditionValue Condition
	 *
	 * @return boolean
	 * @throws Exception MT_Admin_Model_File::renamePhoto
	 */
	public function update(array $data, array $conditionValue = null)
	{
		// If $data contains key 'gallery' and 'path and if an ID is given
		if (!empty($data['gallery']) && !empty($data['path']) && !empty($conditionValue['id'])) {
			$data['path'] = $this->renameFile($conditionValue['id'], $data['path'], $data['gallery']);
		} else {
			unset($data['path']);
		}
		// If $data contains key 'date'
		if (!empty($data['date']) && !MT_Util_Common::isTimestampInStringForm($data['date'])) {
			$data['date'] = strtotime($data['date']);
			// Falls für Timestamp Quatsch eingeben wurde, behalte den alten.
			if (!MT_Util_Common::isTimestampInStringForm($data['date'])) {
				unset($data['date']);
			}
		}

		MT_Util_Common::trimArrayEntry($data, 'description');

		$data['search_text'] = self::__createSearchText($data);
		unset($data['detected_text']); // Filled by photo analysis plugin only

		return parent::update($data, $conditionValue);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.MethodDoubleUnderscore
	private static function __createSearchText($data)
	{
		$result = $data['description'].' '.$data['detected_text'];
		return strlen($result) > 1000 ? substr($result, 0, 999) : $result;
	}

	/**
	 * Check photo is in database
	 *
	 * @param string $path Photo's  database path
	 *
	 * @return boolean
	 */
	public static function checkPhotoIsInDb($path)
	{
		return parent::get_attribute('id', "path = '".$path."'");
	}
	
	/**
	 * Reanmes a photo and it's thumbnail.
	 *
	 * @param integer $photoId   ID of the photo
	 * @param string  $oldFile   Current file path (can be the real or the database path)
	 * @param integer $galleryId ID of the photos gallery
	 *
	 * @return string|false New path if rename of the photo and it's thumbnail
	 *	was successful. False otherwise.
	 * @throws Exception MT_Admin_Model_File::renamePhoto
	 */
	private function renameFile($photoId, $oldFile, $galleryId)
	{
		$gallery = new MT_Gallery($galleryId);
		$dirname = $gallery->get_attribute('fullPath');
		$basename = $gallery->get_attribute('path') . '_' . $photoId;
			
		$newDbFile = $dirname.$basename.'.'.strtolower(pathinfo($oldFile, PATHINFO_EXTENSION));
		return MT_Admin_Model_File::renamePhoto($oldFile, $newDbFile);
	}
	
	/**
	 * Gibt die Anzahl aller Bilder (ist keine ID gesetzt) bzw. die Anzahl der
	 * Bilder in einer Galerie / der neuen Bilder (ID ist gesetzt) zurück.
	 *
	 * @param string|null $galleryId Gallery's ID
	 *
	 * @return string Number of pictures
	 */
	public static function getCount($galleryId = null)
	{
		if (isset($galleryId)) {
			$whereCondition = " AND gallery = '" . $galleryId . "'";
		}
		return parent::get_aggregate('COUNT', 'id', '`show` = 1'.$whereCondition);
	}
	
	public static function getCountNewPhotos()
	{
		return parent::get_aggregate('COUNT', 'id', '`show` = 0');
	}
	
	/**
	 * Gibt die Anzahl der Bilder eines Fotografen zurück
	 *
	 * @param string $photographerId ID
	 *
	 * @return integer Number of photos
	 */
	public static function getNumPhotos($photographerId)
	{
		return parent::get_aggregate('COUNT', 'id', "photographer = '".$photographerId."' AND `show` = '1'");
	}
	
	public static function getRandom($select)
	{
		$query = (new MT_QueryBuilder())
			->from(static::name(), $select)
			->orderBy('RAND()')
			->limit(1);
		return $query->getResultOne();
	}
}
