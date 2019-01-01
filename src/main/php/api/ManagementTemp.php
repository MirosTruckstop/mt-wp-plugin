<?php
namespace MT\WP\Plugin\Api;

/**
 * Model of management temp.
 */
class MT_ManagementTemp extends MT_Common
{
	
	public function __construct($id = null)
	{
		parent::__construct($id);
	}

	public static function name()
	{
		return 'management_temp';
	}
}
