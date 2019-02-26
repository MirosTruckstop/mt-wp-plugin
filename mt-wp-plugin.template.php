<?php
use \Exception as Exception;
use MT\WP\Plugin\Common\Util\MT_Util_Common;
use MT\WP\Plugin\Frontend\View\Gallery\MT_View_Gallery;
use MT\WP\Plugin\Frontend\View\Gallery\MT_View_SearchGallery;
use MT\WP\Plugin\Frontend\View\Gallery\MT_View_TagGallery;
use MT\WP\Plugin\Frontend\View\MT_View_Category;
use MT\WP\Plugin\Frontend\View\MT_View_Photographer;

add_filter('document_title_parts', function ($title) {
	global $view;

	// TODO: Remove this hack
	$viewType = get_query_var('mtView');
	if (!empty($viewType)) {
		set_view($viewType);
	}

	if (method_exists($view, 'getTitle')) {
		$title['title'] = $view->getTitle();
	}

	return $title;
});

add_filter('the_title', 'mt_the_title', 10, 2);
// phpcs:disable PEAR.NamingConventions.ValidFunctionName.FunctionNoCapital, PEAR.NamingConventions.ValidFunctionName.FunctionNameInvalid
function mt_the_title($title, $id)
{
	// phpcs:enable
	global $view;

	if ($id == get_the_ID() && method_exists($view, 'getTitle')) {
		return $view->getTitle();
	}
	return $title;
}

add_filter('body_class', function ($classes) {
	global $view;
	if (method_exists($view, 'checkWidescreen') && $view->checkWidescreen()) {
		return array_merge($classes, array('widescreen'));
	}
	return $classes;
});

add_filter('the_content', function ($content) {
	global $view;
	if (method_exists($view, 'outputContent')) {
		return $view->outputContent();
	}
	return $content;
});

add_filter('mtTheme_breadcrumb_items', function ($items) {
	global $view;
	if (method_exists($view, 'getBreadcrumb')) {
		return array_merge($items, $view->getBreadcrumb());
	}
	return $items;
});

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.FunctionNoCapital, PEAR.NamingConventions.ValidFunctionName.FunctionNameInvalid
function set_view($viewType)
{
	// phpcs:enable
	global $view;

	$id = intval(get_query_var('mtId'));
	$search = urldecode(get_query_var('mtSearch'));
	
	try {
		$view = createView($viewType, $id, $search);
	} catch (Exception $e) {
		// get_template_part('content', 'none');
		//header('HTTP/1.0 404 Not Found');
		MT_Util_Common::log($e->getMessage());
	}
}

/**
 * @param string $viewType View type
 * @param int    $id       Id
 * @param string $search   Search
 *
 * @return MT_View_AbstractGallery
 * @throws Exception When $viewType is unknown
 * @throws Exception When view creation failed
 */
function createView($viewType, $id, $search)
{
	// phpcs:disable PEAR.WhiteSpace.ScopeIndent.IncorrectExact
	switch ($viewType) {
		case 'bilder/galerie':
			return new MT_View_Gallery($id, get_query_var('mtPage', 1), get_query_var('mtNum', 10), get_query_var('mtSort', 'date'));
		case 'bilder/tag':
			return new MT_View_TagGallery($search);
		case 'bilder/suche':
			return new MT_View_SearchGallery($search);
		case 'bilder/kategorie':
			return new MT_View_Category($id);
		case 'fotograf':
			return new MT_View_Photographer($id);
	}
	// phpcs:enable
	throw new Exception('Unknown view type: '.$viewType);
}
