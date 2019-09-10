<?php
// phpcs:disable PEAR.Commenting.FileComment.WrongStyle
/*
Plugin Name: MT Plugin
Plugin URI: https://github.com/MirosTruckstop/mt_wp_plugin
Description: Wordpress plugin for MiRo's Truckstop
Version: 1.0.0
Author: Xennis
Text Domain: mt-wp-plugin
*/
// phpcs:enable

/*
 * Set locale for correct dates
 */
setlocale(LC_ALL, get_locale());

/*
 * Set timezone
 */
date_default_timezone_set(get_option('timezone_string'));

/**
 * Plugin name
 */
define('MT_NAME', dirname(plugin_basename(__FILE__)));
/**
 * Plugin directory
 */
define('MT_DIR', WP_PLUGIN_DIR.'/'.MT_NAME);
/**
 * PHP source directory
 */
define('MT_DIR_SRC_PHP', MT_DIR.'/src/main/php');

require_once plugin_dir_path(__FILE__).'/vendor/autoload.php';

/*
 * Register activation hook
 */
register_activation_hook(__FILE__, function () {
	include_once MT_DIR_SRC_PHP . '/config/Db.php';
	MT_Config_Db::__setup_database_tables();
	
	add_option('datum_letzte_suche', 0, null, false);
});

add_action('plugins_loaded', function () {
	load_plugin_textdomain(MT_NAME, false, MT_NAME.'/languages/');
});

/*
 * Admin scripts hook
 */
add_action('admin_enqueue_scripts', function () {
	wp_enqueue_style('mt-style', plugins_url('/dist/back-end.css', __FILE__));
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.11.4/jquery-ui.min.js');
	wp_enqueue_script('mt-script', plugins_url('src/js/back-end/back-end.js', __FILE__));
});

/**
 * Include style sheet.
 */
add_action('wp_enqueue_scripts', function () {
	wp_enqueue_style('mt-style', plugins_url('/dist/front-end.css', __FILE__));
});

require_once MT_DIR . '/mt-wp-plugin.routing.php';
require_once MT_DIR . '/mt-wp-plugin.widgets.php';
require_once MT_DIR . '/mt-wp-plugin.shortcodes.php';
require_once MT_DIR . '/mt-wp-plugin.pages.php';
require_once MT_DIR . '/mt-wp-plugin.template.php';
