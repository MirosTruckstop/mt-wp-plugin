<?php

class MT_View_TagGallery extends MT_View_Gallery {

	private $tag;
	
	public function __construct($tag) {
		$this->tag = $tag;
	}
	
	public function outputTitle() {
		echo 'Bilder mit Tag '.$this->tag;
	}
	
    public function outputDescription() {
		echo 'Fotogalerie für den Tag: '.$this->tag;
	}	
	
	public function checkWidescreen() {
		return TRUE;
	}
	
	public function outputContent() {
		$query = (new MT_QueryBuilder())
			->from('photo', array('id as photoId', 'path', 'description', 'date'))
			->joinLeft('photographer', TRUE, array('id as photographerId', 'name as photographerName'))
			->whereEqual('`show`', '1')
			->where("`description` LIKE '%".$this->tag."%'")
			->orderBy('date')
			->limitPage($this->userSettings['page'], $this->userSettings['num']);
		
		$this->_outputContentPhotos($query, $this->tag);
	}
}