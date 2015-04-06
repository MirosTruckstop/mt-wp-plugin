<?php

abstract class MT_View_Gallery extends MT_View_Common {

	private $checkWidescreen;
	
	public function checkWidescreen() {
		return $this->checkWidescreen;
	}
	
	public function setWidescreen($value) {
		$this->checkWidescreen = $value;
	}
	
	/**
	 * Output galleries photos
	 *
	 * @return void
	 */
	protected function _outputContentPhotos($query, $altPreafix) {
		foreach ($query->getResult('ARRAY_A') as $item) {
			$item['alt'] = $altPreafix . MT_Functions::getIfNotEmpty($item->description, ': '.$item->description); // photo's alternate text
			$item['keywords'] = $this->__getPhotoKeywords($item['alt']);
			$this->_outputPhoto($item);
		}
	}

	/**
	 * Ouput photo (Form: paragraph)
	 *
	 * @param	string $path             	Photo's path
	 * @param	string $keywords              	Photo's keywords as string
	 * @param	string $alt              	Photo's alternate text
	 * @param	string $description      	Photo's description
	 * @param	string $date             	Photo's date as timestamp
	 * @param	string $photographerId   	Photographer's id
	 * @param	string $photographerName	Photographer's name
	 * @return	void
	 */
	private function _outputPhoto(array $item) {
		if (!empty($item['photographerName'])) {
			$photographerString = '<b>Fotograf:</b>&nbsp;<a href="/fotograf/'.$item['photographerId'].'" rel="author"><span itemprop="author" itemp>'.$item['photographerName'].'</span></a>&nbsp;|&nbsp';
		}
		if (!empty($item['galleryName'])) {
			$galleryString = '<b>Galerie:</b>&nbsp;<a href="/bilder/galerie/'.$item['galleryId'].'">'.$item['galleryName'].'</a>&nbsp;|&nbsp;';
		}
		
		$schemaDateFormat   = 'Y-m-d';
		$mtDateFormat       = 'd.m.Y - H:i:s';

		
		$descriptionHtml = preg_replace('(#\S+)', '<a href="/bilder/tag/$0">$0</a>', $item['description']. ' ');
		$descriptionHtml = str_replace('tag/#', 'tag/', $descriptionHtml);
		
		echo '<div class="photo" itemscope itemtype="http://schema.org/ImageObject">
<!--            <span itemprob="publisher">MiRo\'s Truckstop</span>-->
				<span itemprop="keywords">'.$item['keywords'].'</span>
			    <p><img alt="'.$item['alt'].'" src="/bilder/'.$item['path'].'" itemprop="contentURL"><br>
				'.$galleryString.'
			    '.$photographerString.'
				<b>Eingestellt am:</b>&nbsp;<meta itemprop="datePublished" content="'.date($schemaDateFormat, $item['date']).'">'.date($mtDateFormat, $item['date']).'</p>
			    <p><span itemprop="description">'.$descriptionHtml.'</span></p>
			</div>';
	}
        
	/**
	 * Removes special chars etc and return a clear keyword string
	 * 
	 * @param   string $keywordsString  String with keywords
	 * @return  string                  Keyword string
	 */
	private function __getPhotoKeywords( $keywordsString ) {
		return str_replace(array('& ', '(', ')', ':', '"', 'in '), '', $keywordsString);
	}	
}
