<?php

class MT_Admin_NewsGeneration {
	
	/**
	 * Timestamp of the last news
	 * 
	 * @var int 
	 */
	private $timestampLatestNews;
	
	public function __construct() {
		$this->timestampLatestNews = MT_News::getLatestNewsTimestamp();
	}

	/**
	 * Überprüft, ob seit der letzten News-Generierung neue Bilde hinzugekommen sind,
	 * d.h. ob es überhaupt News zum Generieren gibt
	 *
	 * @return	boolean
	 */
	public function checkGenerateNews() {
		return ($this->timestampLatestNews < MT_Photo::getLatestPhotoDate() );
	}
	
	public function getGeneratedNews() {
		$query = (new MT_QueryBuilder())
			->from('photo')
			->select('wp_mt_gallery.name as galleryName')
			->select('wp_mt_category.name as categoryName')
			->select('wp_mt_subcategory.name as subcategoryName')
			->select('COUNT(wp_mt_gallery.id) AS numPhotos')
			->joinInner('gallery', TRUE, array('id', 'date'))
			->joinInner('category', 'wp_mt_category.id = wp_mt_gallery.category')
			->joinLeftOuter('subcategory', 'wp_mt_subcategory.id = wp_mt_gallery.subcategory')
			->whereEqual('wp_mt_photo.show', 1)
			->where("wp_mt_photo.date >= " . $this->timestampLatestNews)
			->groupBy('wp_mt_category.name, wp_mt_subcategory.name, wp_mt_gallery.name')
			->orderBy('numPhotos DESC');

		$news = array();
		foreach ($query->getResult() as $item) {
			array_push($news, array(
				'title' => $this->generateTitle($item->categoryName, $item->subcategoryName, $item->galleryName, $item->date, $item->numPhotos),
				'text' => $this->generateText($item->numPhotos),
				'gallery' => $item->id
			));
		}
		return $news;
	}
	
	private function generateTitle($catgegoryName, $subcategoryName, $galleryName, $galleryDate, $numPhotos) {
		$title = $catgegoryName;
		if( !empty($subcategoryName) ) {
			$title .= ' > ' . $subcategoryName;
		}
		$title .= ': ';
		// New gallery
		if($galleryDate  >= $this->timestampLatestNews) {				
			$title .= "Neue Galerie '" . $galleryName . "'";
		}
		// New photos only
		else {
			if($numPhotos != 1) {
				$title .= 'Neue Bilder';
			} else {
				$title .= 'Neues Bild';
			}
			$title .= " in der Galerie '" . $galleryName . "'";
		}
		return $title;
	}
	
	private function generateText($numPhotos) {
		$text = $numPhotos . ' ';
		if($numPhotos > 1) {
			$text .= 'neue Bilder';
		} else {
			$text .= 'neues Bild';
		}
		return $text;
	}
}
