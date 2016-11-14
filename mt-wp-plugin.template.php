<?php
add_filter('the_title', 'mt_the_title', 10, 2);
function mt_the_title( $title, $id ) {
	global $view;
	if ($id == get_the_ID() && method_exists($view, 'getTitle')) {
		return $view->getTitle();
	}
	return $title;
}

add_filter('body_class', 'mt_body_class');
function mt_body_class( $classes ) {
	global $view;
	if (method_exists($view, 'checkWidescreen') && $view->checkWidescreen()) {
		return array_merge($classes, array('widescreen'));
	}
	return $classes;
};

add_filter('the_content', 'mt_the_content');
function mt_the_content( $content ) {
	global $view;
	if (method_exists($view, 'outputContent')) {
		return $view->outputContent();
	}
	return $content;
}

add_filter('mtTheme_breadcrumb_items', 'mt_mtTheme_breadcrumb_items');
function mt_mtTheme_breadcrumb_items( $items ) {
	global $view;
	if (method_exists($view, 'getBreadcrumb')) {
		return array_merge($items, $view->getBreadcrumb());
	}
	return $items;
}