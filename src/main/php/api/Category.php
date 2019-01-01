<?php
namespace MT\WP\Plugin\Api;

use MT\WP\Plugin\Backend\Model\MT_Admin_Model_File;

/**
 * Model of a category.
 */
class MT_Category extends MT_Common
{
	
	/**
	 * Category path
	 *
	 * @var string
	 */
	public static $_categoryPath = '../kategorie/';

	public function __construct($id = null)
	{
		parent::__construct($id);
	}

	public static function name()
	{
		return 'category';
	}
	
	public static function getName()
	{
		return 'Kategorien';
	}

	/**
	 * Inserts a new category in the database and creats it's folder.
	 *
	 * @param array $data Data
	 *
	 * @return boolean True, if insert was successful
	 * @throws Exception If creation of the folder failed
	 */
	public static function insert(array $data)
	{
		$data['path'] = MT_Admin_Model_File::nameToPath($data['name']);
		if (parent::insert($data)) {
			return MT_Admin_Model_File::createDirectory($data['path']);
		}
		return false;
	}
}
