<?php
namespace MT\WP\Plugin\Frontend\View\Gallery;

use MT\WP\Plugin\Common\Util\MT_Util_Common;
use MT\WP\Plugin\Frontend\View\MT_View_Common;

/**
 * General view of a gallery.
 */
abstract class MT_View_AbstractGallery extends MT_View_Common
{
	
	const PHOTO_PATH = '/bilder';
	
	/**
	 * Output galleries photos
	 *
	 * @param array   $photos      Photos
	 * @param string  $altPreafix  Alt prefix
	 * @param boolean $isThumbView True, if photos should be displayed only as thumbnail
	 *
	 * @return void
	 */
	protected function _outputContentPhotos(array $photos, $altPreafix = '', $isThumbView = false)
	{
		if (empty($photos)) {
			_e('Keine Bilder gefunden.', MT_NAME);
			return;
		}

		foreach ($photos as $item) {
			$item->alt = $this->_getPhotoAlternateText($item->description, $altPreafix);
			$item->keywords = $this->__getPhotoKeywords($item->alt);
			if ($isThumbView) {
				$this->_outputThumb($item);
			} else {
				$this->_outputPhoto($item);
			}
		}
	}

	/**
	 * Get the alternate text of a photo. If the description and a prefix is
	 * set "<prefix>: <description>" will be returned. If only one of the
	 * strings is set, this string will be returned. Otherwise an empty string
	 * will be returned.
	 *
	 * @param string $description Description
	 * @param string $prefix      Prefix
	 *
	 * @return string
	 */
	private function _getPhotoAlternateText($description, $prefix = '')
	{
		return $prefix . MT_Util_Common::getIfNotEmpty($description, MT_Util_Common::getIfNotEmpty($prefix, ': ').$description);
	}

	/**
	 * Ouput photo (Form: paragraph)
	 *
	 * @param array $item Item
	 *
	 * @return void
	 */
	private function _outputPhoto($item)
	{
		if (!empty($item->galleryName)) {
			$galleryString = '<b>'.__('Galerie', MT_NAME).':</b>&nbsp;<a href="/bilder/galerie/'.$item->galleryId.'">'.$item->galleryName.'</a>&nbsp;|&nbsp;';
		}
		if (!empty($item->photographerName)) {
			$photographerString = '<b>'.__('Fotograf', MT_NAME).':</b>&nbsp;<a href="/fotograf/'.$item->photographerId.'" rel="author"><span itemprop="author" itemp>'.$item->photographerName.'</span></a>';
		}
		// All images from the old website have a timestamp lower then 10000
		// since they don't really have one
		if ($item->date >= 10000) {
			$schemaDateFormat = 'Y-m-d';
			$mtDateFormat = 'd.m.Y - H:i';
			$dateString = '&nbsp;|<span class="screen-small-hide">&nbsp<b>'.__('Eingestellt am', MT_NAME).':</b></span>&nbsp;<meta itemprop="datePublished" content="'.gmdate($schemaDateFormat, $item->date).'">'.date($mtDateFormat, $item->date);
		}
		if (!empty($item->description)) {
			$descriptionString = preg_replace('(#\S+)', '<a href="/bilder/tag/$0">$0</a>', $item->description. ' ');
			$descriptionString = str_replace('tag/#', 'tag/', $descriptionString);
		}
		
		echo '<div class="photo" itemscope itemtype="http://schema.org/ImageObject">
				<span itemprop="keywords">'.$item->keywords.'</span>
				<div>
					<img alt="'.$item->alt.'" src="'.self::PHOTO_PATH.'/'.$item->path.'" itemprop="contentURL"><br>
				'.$galleryString.$photographerString.$dateString.'<br>
					<span itemprop="description">'.$descriptionString.'</span>
				</div>
			</div>';
	}
	
	private function _outputThumb($item)
	{
		echo '<img alt="'.$item->alt.'" src="'.self::PHOTO_PATH.'/thumb/'.$item->path.'">';
	}

	/**
	 * Removes special chars etc and return a clear keyword string
	 *
	 * @param string $keywordsString String with keywords
	 *
	 * @return string Keyword string
	 */
	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.MethodDoubleUnderscore
	private function __getPhotoKeywords($keywordsString)
	{
		return str_replace(array('& ', '(', ')', ':', '"', 'in '), '', $keywordsString);
	}
}
