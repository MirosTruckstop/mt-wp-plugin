<?php
abstract class MT_Config_Db {
	
	public static function __setup_database_tables() {
		MT_Common::__createTable('category', "
			`id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(25) NOT NULL,
			`path` varchar(30) NOT NULL,
			`description` text NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `path` (`path`)		
		");
		MT_Common::__createTable('gallery', "
			`id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
			`category` tinyint(2) unsigned NOT NULL,
			`subcategory` tinyint(2) unsigned NOT NULL DEFAULT '0',
			`name` varchar(35) NOT NULL,
			`description` text NOT NULL,
			`path` varchar(40) NOT NULL,
			`fullPath` varchar(100) NOT NULL,
			`hauptparkplatz` tinyint(2) unsigned NOT NULL DEFAULT '0',
			`date` int(10) unsigned NOT NULL,
			`updated` int(10) unsigned NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `fullPath` (`fullPath`)		
		");	
		MT_Common::__createTable('management_temp', "
			`ip` varchar(15) NOT NULL DEFAULT '0',
			`num` tinyint(2) unsigned NOT NULL DEFAULT '10',
			`sort` varchar(5) NOT NULL DEFAULT 'date',
			`date` int(10) unsigned NOT NULL,
			PRIMARY KEY (`ip`)		
		");
		MT_Common::__createTable('news', "
			`id` smallint(3) unsigned NOT NULL AUTO_INCREMENT,
			`title` varchar(100) NOT NULL,
			`text` text NOT NULL,
			`gallery` tinyint(2) unsigned NOT NULL,
			`date` int(10) unsigned NOT NULL,
			PRIMARY KEY (`id`)		
		");
		MT_Common::__createTable('photo', "
			`id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
			`path` varchar(100) NOT NULL,
			`name_old` varchar(30) NOT NULL,
			`gallery` tinyint(2) unsigned NOT NULL,
			`description` text NOT NULL,
			`photographer` tinyint(2) unsigned NOT NULL DEFAULT '1',
			`date` int(10) unsigned NOT NULL,
			`show` tinyint(1) unsigned NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			UNIQUE KEY `photo_path` (`path`)	
		");
		MT_Common::__createTable('photographer', "
			`id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(40) NOT NULL,
			`camera` varchar(20) NOT NULL,
			`date` int(10) unsigned NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			UNIQUE KEY `name` (`name`)
		");
		MT_Common::__createTable('subcategory', "
			`id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
			`category` tinyint(2) unsigned NOT NULL,
			`name` varchar(30) NOT NULL,
			`path` varchar(35) NOT NULL,
			PRIMARY KEY (`id`)
		");		
	}
}