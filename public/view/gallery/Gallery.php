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
		foreach ($query->getResult() as $item) {
            $alt = $altPreafix . MT_Functions::getIfNotEmpty($item->description, ': '.$item->description); // photo's alternate text
			$this->_outputPhoto( $item->path,
				$this->__getPhotoKeywords($alt),
				$alt,
				$item->description,
				$item->date,
				$item->photographerId,
				$item->photographerName
			);
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
	private function _outputPhoto( $path, $keywords, $alt, $description, $date, $photographerId, $photographerName ) {
		if (!empty($photographerName)) {
			$photographerString = '<b>Fotograf:</b>&nbsp;<a href="/fotograf/' . $photographerId . '" rel="author"><span itemprop="author" itemp>' . $photographerName . '</span></a>&nbsp;|&nbsp';
		}
		$schemaDateFormat   = 'Y-m-d';
		$mtDateFormat       = 'd.m.Y - H:i:s';

		
		$descriptionHtml = preg_replace('(#\S+)', '<a href="/bilder/tag/$0">$0</a>', $description. ' ');
		
		echo '<div class="photo" itemscope itemtype="http://schema.org/ImageObject">
<!--            <span itemprob="publisher">MiRo\'s Truckstop</span>-->
				<span itemprop="keywords">'.$keywords.'</span>
			    <p><img alt="'.$alt.'" src="/bilder/'.$path.'" itemprop="contentURL"><br>
			    '.$photographerString.'
				<b>Eingestellt am:</b>&nbsp;<meta itemprop="datePublished" content="'.date($schemaDateFormat, $date).'">'.date($mtDateFormat, $date).'</p>
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
