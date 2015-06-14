<?php
/**
 * Model of management temp.
 * 
 * @package public
 * @subpackage model
 */
class MT_ManagementTemp extends MT_Common {
	
	public function __construct($id = NULL) {
		parent::__construct($id);
	}

	public static function name() {
		return 'management_temp';
	}

}
