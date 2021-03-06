<?php
namespace MT\WP\Plugin\Frontend\View\Gallery;

use MT\WP\Plugin\Common\Util\MT_Util_Common;

/**
 * Search gallery view.
 */
class MT_View_SearchGallery extends AbstractSearchGallery
{

	public function __construct($query)
	{
		parent::__construct($query);
		parent::setTitle(__('Ergebnisse für "'.$query.'"', MT_NAME));
		parent::setDescription(__('Suchergebnisse für '.$query, MT_NAME));
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.MethodDoubleUnderscore
	private static function __createCondition($query)
	{
		$words = explode(' ', $query);
		$condition = "MATCH(search_text) AGAINST ('";
		foreach ($words as $word) {
			$condition .= "+$word* ";
		}
		$condition .= "' IN BOOLEAN MODE)";
		return $condition;
	}
	
	public function outputContent()
	{
		MT_Util_Common::log('Query search: '. $this->query);
		$condition = self::__createCondition($this->query);
		parent::outputContentByCondition($condition);
	}
}
