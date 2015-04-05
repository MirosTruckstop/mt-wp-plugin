<?php

class MT_View_TagGallery extends MT_View_Gallery {

	private $tag;
	
	public function __construct($tag) {
		$this->tag = $tag;
		
		parent::setTitle('Bilder mit Tag '.$this->tag);
		parent::setDescription('Fotogalerie für den Tag: '.$this->tag);
		parent::setWidescreen(true);
	}
	
	public function outputContent() {
		$query = (new MT_QueryBuilder())
			->from('photo', array('id as photoId', 'path', 'description', 'date'))
			->joinLeft('photographer', TRUE, array('id as photographerId', 'name as photographerName'))
			->whereEqual('`show`', '1')
			->where("`description` LIKE '%".$this->tag."%'")
			->orderBy('date');
		
		$this->_outputContentPhotos($query, $this->tag);
	}
}