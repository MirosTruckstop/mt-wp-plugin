<?php

add_filter( 'wp_title', function( $title ) {
	global $view;
	if (method_exists($view, 'outputTitle')) {
		return $view->outputTitle();
	}
	return $title;
} );

add_filter( 'body_class', function( $classes ) {
	global $view;
	if (method_exists($view, 'checkWidescreen') && $view->checkWidescreen()) {
		return array_merge($classes, array('widescreen'));
	}
	return $classes;
} );