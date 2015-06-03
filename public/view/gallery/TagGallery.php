<?php

class MT_View_TagGallery extends MT_View_Gallery {

	private $tag;
	
	public function __construct($tag) {
		$this->tag = $tag;
		
		parent::setTitle(__('Bilder mit Tag', 'mt-wp-plugin').' '.$this->tag);
		parent::setDescription(__('Fotogalerie für den Tag', 'mt-wp-plugin').': '.$this->tag);
		parent::setWidescreen(true);
	}
	
	public function outputContent() {
		$query = (new MT_QueryBuilder())
			->from('photo', array('id AS photoId', 'path', 'description', 'date'))
			->join('gallery', TRUE, array('id AS galleryId', 'name AS galleryName'))
			->joinLeft('photographer', TRUE, array('id AS photographerId', 'name AS photographerName'))
			->whereEqual('`show`', '1')
			->where("wp_mt_photo.description LIKE '%".$this->tag."%'")
			->orderBy('date');
		
		$this->_outputContentPhotos($query, $this->tag);
	}
}