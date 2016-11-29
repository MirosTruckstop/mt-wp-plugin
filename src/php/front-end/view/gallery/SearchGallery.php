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
		MT_Util_Common::log('Query search: '. $this->query);
		$query = strtolower($this->query);
		$condition .= "wp_mt_photo.path LIKE '%".$query."%'";

		// Create from query string 'hello world' conditation
		// "OR (description LIKE '%hello%' AND description LIKE '%world%')"
		$queryParts = explode(' ', $query);
		$queryPartsCount = count($queryParts);
		if ($queryPartsCount > 0) {
			$condition .= " OR (";
			for ($i = 0; $i < $queryPartsCount; $i++) {
				if ($i > 0) {
					$condition .= ' AND ';
				}
				$condition .= " wp_mt_photo.description LIKE '%".$queryParts[$i]."%'";
			}
			$condition .= ")";
		}

		parent::outputContentByCondition($condition);
	}
}