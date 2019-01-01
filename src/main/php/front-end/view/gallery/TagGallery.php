<?php
namespace MT\WP\Plugin\Frontend\View\Gallery;

/**
 * Tag gallery view, i.e. display all photos with a given (hash-) tag.
 */
class MT_View_TagGallery extends AbstractSearchGallery
{

	public function __construct($tag)
	{
		parent::__construct('#'.$tag);
		
		parent::setTitle(__('Bilder mit Tag', MT_NAME).' '.$this->query);
		parent::setDescription(__('Fotogalerie für den Tag', MT_NAME).': '.$this->query);
	}
	
	public function outputContent()
	{
		parent::outputContentByCondition("wp_mt_photo.description LIKE '%".$this->query."%'");
	}
}
