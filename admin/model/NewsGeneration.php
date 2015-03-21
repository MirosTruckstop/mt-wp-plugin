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
		$news = array();
		$query = new MT_QueryBuilder('wp_mt_');
		$query->from('photo')
			->select('wp_mt_gallery.name as galleryName')
			->select('wp_mt_category.name as categoryName')
			->select('wp_mt_subcategory.name as subcategoryName')
			->select('COUNT(wp_mt_gallery.id) AS numPhotos')
			->joinInner('gallery', 'wp_mt_gallery.id = wp_mt_photo.gallery', array('id', 'date'))
			->joinInner('category', 'wp_mt_category.id = wp_mt_gallery.category')
			->joinLeftOuter('subcategory', 'wp_mt_subcategory.id = wp_mt_gallery.subcategory')
			->where('wp_mt_photo.show = 1')
			->where("wp_mt_photo.date >= " . $this->timestampLatestNews)
			->groupBy('wp_mt_category.name, wp_mt_subcategory.name, wp_mt_gallery.name')
			->orderBy('numPhotos DESC');
		
		foreach ($query->getResult() as $item) {
			array_push($news, array(
				0 => $this->generateTitle($item->categoryName, $item->subcategoryName, $item->galleryName, $item->date, $item->numPhotos),
				1 => $this->generateText($item->numPhotos),
				2 => $item->id
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
		if($galleryDate  >= $this->timestampLatestNews) {				// Neue Galerie
			$title .= "Neue Galerie '" . $galleryName . "'";
		} else {														// "Nur" neue Fotos
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
