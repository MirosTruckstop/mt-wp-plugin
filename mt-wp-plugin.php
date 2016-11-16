<?php
/*
Plugin Name: MT Plugin
Plugin URI: http://github.com/MirosTruckstop/mt_wp_plugin
Description: Wordpress plugin for MiRo's Truckstop
Version: 0.1
Author: Xennis
Text Domain: mt-wp-plugin
*/

/*
 * Set timezone
 */
date_default_timezone_set(get_option('timezone_string'));

/**
 * Plugin name
 */
define('MT_NAME', dirname(plugin_basename( __FILE__ )));
/**
 * Plugin directory 
 */
define('MT_DIR', WP_PLUGIN_DIR.'/'.MT_NAME);

/*
 * Require scripts
 */
require_once(MT_DIR . '/src/back-end/model/form/Field.php');
require_once(MT_DIR . '/src/back-end/model/File.php');
require_once(MT_DIR . '/src/back-end/view/crud/Common.php');
require_once(MT_DIR . '/src/back-end/view/crud/Edit.php');
require_once(MT_DIR . '/src/back-end/view/crud/List.php');

require_once(MT_DIR . '/src/common/Functions.php');
require_once(MT_DIR . '/src/common/QueryBuilder.php');

require_once(MT_DIR . '/src/api/Common.php');
require_once(MT_DIR . '/src/api/Category.php');
require_once(MT_DIR . '/src/api/Gallery.php');
require_once(MT_DIR . '/src/api/Subcategory.php');
require_once(MT_DIR . '/src/api/ManagementTemp.php');
require_once(MT_DIR . '/src/api/News.php');
require_once(MT_DIR . '/src/api/Photo.php');
require_once(MT_DIR . '/src/api/Photographer.php');

/*
 * Register activation hook 
 */
register_activation_hook( __FILE__, 'mt_register_activation' );
function mt_register_activation() {
	require_once(MT_DIR . '/src/config/Db.php');
	MT_Config_Db::__setup_database_tables();
	
	add_option('datum_letzte_suche', 0, NULL, FALSE);
}

add_action('plugins_loaded', 'mt_load_plugin_textdomain');
function mt_load_plugin_textdomain() {
	load_plugin_textdomain(MT_NAME, false, MT_NAME.'/languages/');
}

/*
 * Admin scripts hook
 */
add_action('admin_enqueue_scripts', 'mt_admin_enqueue_scripts' );
function mt_admin_enqueue_scripts() {
	wp_enqueue_style('mt-style', plugins_url('src/back-end/css/back-end.css', __FILE__));
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.11.4/jquery-ui.min.js');
	wp_enqueue_script('mt-script', plugins_url('src/back-end/js/back-end.js', __FILE__ ));
}



require_once(MT_DIR . '/mt-wp-plugin.routing.php');
require_once(MT_DIR . '/mt-wp-plugin.widgets.php');
require_once(MT_DIR . '/mt-wp-plugin.shortcodes.php');
require_once(MT_DIR . '/mt-wp-plugin.pages.php');
require_once(MT_DIR . '/mt-wp-plugin.template.php');