<?php
// TODO: on init?
$mtRewriteRuleIndex = '(bilder/galerie|bilder/kategorie|fotograf)/([0-9]{1,2})$';
$mtRewriteRuleIndex2 = '(bilder/galerie)/([0-9]{1,2}),page=([0-9]{1,2})&num=([0-9]{1,3})&sort=(date|-date)$';
$mtRewriteRuleIndex3 = '(bilder/tag|bilder/suche)/([^/]+)$';

add_action('wp_loaded','mt_flush_rules');
function mt_flush_rules() {
	global $mtRewriteRuleIndex;
	global $mtRewriteRuleIndex2;
	global $mtRewriteRuleIndex3;
	$rules = get_option('rewrite_rules');
	if ( !isset($rules[$mtRewriteRuleIndex]) || !isset($rules[$mtRewriteRuleIndex2]) || !isset($rules[$mtRewriteRuleIndex3]) ) {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}
}
add_filter('rewrite_rules_array', 'mt_rewrite_rules_array');
function mt_rewrite_rules_array($rules){
	global $mtRewriteRuleIndex;
	global $mtRewriteRuleIndex2;
	global $mtRewriteRuleIndex3;
	$newrules = array();
	$newrules[$mtRewriteRuleIndex] = 'index.php?pagename=mt&mtView=$matches[1]&mtId=$matches[2]';
	$newrules[$mtRewriteRuleIndex2] = 'index.php?pagename=mt&mtView=$matches[1]&mtId=$matches[2]&mtPage=$matches[3]&mtNum=$matches[4]&mtSort=$matches[5]';
	$newrules[$mtRewriteRuleIndex3] = 'index.php?pagename=mt&mtView=$matches[1]&mtSearch=$matches[2]';
	return $newrules + $rules;
}

add_filter('query_vars','mt_query_vars');
function mt_query_vars($vars){
	array_push($vars, 'mtView', 'mtId', 'mtPage', 'mtNum', 'mtSort', 'mtSearch');
	return $vars;
}

/*
 * Redirect search '?s=<query>' to '/bilder/suche/<query>'
 */
add_action( 'template_redirect', 'mt_redirect_search' );
function mt_redirect_search() {
	if ( is_search() && !is_admin() ) {
		wp_redirect( home_url( "/bilder/suche/". urlencode( get_query_var('s' ) ) ) );
		exit();
	}
}