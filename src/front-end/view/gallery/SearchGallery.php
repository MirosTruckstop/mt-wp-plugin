<?php
/**
 * Search gallery view.
 * 
 * @package front-end
 * @subpackage view
 */
class MT_View_SearchGallery extends AbstractSearchGallery {

	public function __construct($query) {
		parent::__construct($query);
		
		parent::setTitle(__('Ergebnisse für "'.$query.'"', MT_NAME) );
		parent::setDescription(__('Suchergebnisse für '.$query, MT_NAME) );
	}
	
	public function outputContent() {
		$query = strtolower($this->query);
		$condition = "wp_mt_photo.description LIKE '%".$query."%'";
		$condition .= " OR wp_mt_photo.path LIKE '%".$query."%'";
		parent::outputContentByCondition($condition);
	}
}