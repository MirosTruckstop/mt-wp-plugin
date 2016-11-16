<?php
/*
 * Dashboard widget
 */
add_action('wp_dashboard_setup', 'mt_wp_dashboard_setup');
function mt_wp_dashboard_setup() {
	wp_add_dashboard_widget(
		'mt_dashboard_widget',
		'MiRo\'s Truckstop',
		'mt_dashboard_widget_function'
	);
}
function mt_dashboard_widget_function() {
	require_once(MT_DIR.'/src/back-end/view/DashboardWidget.php');
	$dashboardWidget = new MT_Admin_DashboardWidget();
	$dashboardWidget->outputContent();
}

function mt_widgets_init() {
	require_once(MT_DIR . '/src/front-end/widget/MT_Widget_Recent_News.php');
	register_widget( 'MT_Widget_Recent_News' );
}

add_action( 'widgets_init', 'mt_widgets_init' );