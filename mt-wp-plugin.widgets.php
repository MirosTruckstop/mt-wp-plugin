<?php
use MT\WP\Plugin\Backend\View\MT_Admin_DashboardWidget;

/*
 * Dashboard widget
 */
add_action('wp_dashboard_setup', function () {
	wp_add_dashboard_widget(
		'mt_dashboard_widget',
		'MiRo\'s Truckstop',
		'mt_dashboard_widget_function'
	);
});

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.FunctionNoCapital, PEAR.NamingConventions.ValidFunctionName.FunctionNameInvalid
function mt_dashboard_widget_function()
{
	// phpcs:enable
	$dashboardWidget = new MT_Admin_DashboardWidget();
	$dashboardWidget->outputContent();
}

add_action('widgets_init', function () {
	register_widget('MT\WP\Plugin\Frontend\View\Widget\MT_Widget_Random_Photo');
	register_widget('MT\WP\Plugin\Frontend\View\Widget\MT_Widget_Recent_News');
});
