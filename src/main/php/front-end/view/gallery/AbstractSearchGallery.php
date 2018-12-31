<?php
/**
 * Abstract search gallery view to display search results.
 * 
 * @package front-end
 * @subpackage view
 */
abstract class AbstractSearchGallery extends MT_View_AbstractGallery {

	protected $query;
	
	public function __construct($query) {
		$this->query = $query;
	}
	
	public function outputContentByCondition($whereCondition, $orderBy = 'date', $limit=75) {
		$query = (new MT_QueryBuilder())
			->from('photo', array('id AS photoId', 'path', 'description', 'date'))
			->join('gallery', TRUE, array('id AS galleryId', 'name AS galleryName'))
			->joinLeft('photographer', TRUE, array('id AS photographerId', 'name AS photographerName'))
			->whereEqual('`show`', '1')
			->where($whereCondition)
			->orderBy($orderBy)
			->limit($limit);

		$this->_outputContentPhotos($query->getResult());
	}
}
