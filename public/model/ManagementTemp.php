<?php

class MT_ManagementTemp extends MT_Common {
	
	public function __construct($id = NULL) {
		parent::__construct(self::getTableName(), $id);
	}
	
	public function __toString() {
		return 'management_temp';
	}
	
	public static function getTableName() {
		return 'wp_mt_management_temp';
	}

}
