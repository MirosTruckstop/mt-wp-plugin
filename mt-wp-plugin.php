<?php
/*
Plugin Name: MT Plugin
Description: Wordpress plugin for MiRo's Truckstop
Author: Xennis
Version: 0.1
 */

/**
 * Plugin directory 
 */
define('MT_DIR', WP_PLUGIN_DIR . "/" . plugin_basename(dirname(__FILE__)));

/*
 * Require scripts
 */
require_once(MT_DIR . '/admin/model/form/Field.php');
require_once(MT_DIR . '/admin/view/crud/Common.php');
require_once(MT_DIR . '/admin/view/crud/Edit.php');
require_once(MT_DIR . '/admin/view/crud/List.php');

require_once(MT_DIR . '/common/Functions.php');
require_once(MT_DIR . '/common/QueryBuilder.php');

require_once(MT_DIR . '/public/model/Common.php');
require_once(MT_DIR . '/public/model/Category.php');
require_once(MT_DIR . '/public/model/Gallery.php');
require_once(MT_DIR . '/public/model/Subcategory.php');
require_once(MT_DIR . '/public/model/ManagementTemp.php');
require_once(MT_DIR . '/public/model/News.php');
require_once(MT_DIR . '/public/model/Photo.php');
require_once(MT_DIR . '/public/model/Photographer.php');

/*
 * Register activation hook 
 */
register_activation_hook( __FILE__, 'mt_register_activation' );
function mt_register_activation() {
	require_once(MT_DIR . '/config/Db.php');
	MT_Config_Db::__setup_database_tables();
	
	add_option('datum_letzte_suche', 0, NULL, FALSE);
}

/*
 * Admin scripts hook
 */
add_action('admin_enqueue_scripts', 'mt_admin_enqueue_scripts' );
function mt_admin_enqueue_scripts() {
	// Add css file
    wp_enqueue_style( 'mt-style', plugins_url('admin/css/admin.css', __FILE__));
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.11.4/jquery-ui.min.js');
	wp_enqueue_script('mt-script', plugins_url('admin/js/admin.js', __FILE__ ));
}

/**
 * Add a widget to the dashboard.
 *
 * This function is hooked into the 'wp_dashboard_setup' action below.
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
	require_once(MT_DIR.'/admin/view/DashboardWidget.php');
	$dashboardWidget = new MT_Admin_DashboardWidget();
	$dashboardWidget->outputContent();
}

/*
 * Admin notices hook
 */
//add_action('admin_notices', 'mt_admin_notice');
//function mt_admin_notice(){
//	if ($notices = get_option('mt_admin_notices')) {
//		foreach ($notices as $notice) {
//			echo "<div class='updated'><p>$notice</p></div>";
//		}
//		delete_option('mt_admin_notices');
//	}	
//}

// TODO: on init?
$mtRewriteRuleIndex = '(bilder/galerie|bilder/kategorie|fotograf)/([0-9]{1,2})$';
$mtRewriteRuleIndex2 = '(bilder/galerie)/([0-9]{1,2}),page=([0-9]{1,2})&num=([0-9]{1,2})&sort=(date|-date)$';
$mtRewriteRuleIndex3 = '(bilder/tag)/([^/]+)$';

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
	$newrules[$mtRewriteRuleIndex3] = 'index.php?pagename=mt&mtView=$matches[1]&mtTag=#$matches[2]';
	return $newrules + $rules;
}

add_filter('query_vars','mt_query_vars');
function mt_query_vars($vars){
	array_push($vars, 'mtView', 'mtId', 'mtTag', 'mtPage', 'mtNum', 'mtSort');
	return $vars;
}

/*
 * 
 */
add_shortcode('mt_photo', 'mt_photo');
function mt_photo($atts) {
    $a = shortcode_atts( array(
        'id' => '',
		'width' => '200'
    ), $atts);	
	
	$photo = new MT_Photo($a['id']);
	$item = $photo->getOne(array('id', 'path'), 'ARRAY_A');
	return '<img width="'.$a['width'].'" src="bilder/'.$item['path'].'">';
}

add_shortcode('total_number_of_photos', 'mt_add_shortcode_numPhotos');
function mt_add_shortcode_numPhotos() {
	return (new MT_Photo)->getCount();
}

add_shortcode('latest_news_date', 'mt_add_shortcode_latest_news_date');
function mt_add_shortcode_latest_news_date($atts) {
    $a = shortcode_atts( array(
        'format' => '%e. %B %Y'
    ), $atts );
	
	return strftime($a['format'], (new MT_News())->getLatestNewsTimestamp());
}

add_shortcode('latest_photo_date', 'mt_add_shortcode_latest_photo_date');
function mt_add_shortcode_latest_photo_date($atts) {
    $a = shortcode_atts( array(
        'format' => '%e. %B %Y'
    ), $atts );
	
	return strftime($a['format'], (new MT_Photo())->getLatestPhotoDate());
}

add_shortcode('mt_guestbook', 'mt_add_shortcode_guestbook');
function mt_add_shortcode_guestbook() {
	return '<iframe onload="if ( typeof ResizeIframe != \'undefined\' ) res = ResizeIframe; else if ( typeof top.ResizeIframe != \'undefined\' ) res = top.ResizeIframe; res.resize(document, this.id);" src="http://www.rosensturm.de/tinc?key=WnXUPJB7&amp;start=-1&amp;reverse=1" class="gaestebuch" id="tincInclude2624">X</iframe>';
}

add_shortcode('mt_statistics', 'mt_add_shortcode_statistics');
function mt_add_shortcode_statistics() {
	$returnString = '
			<table class="horizontalLeft">
			 <tr>
			  <th>Galerie</th>
			  <th>Anzahl der Bilder</th>
			 </tr>	
	';
	$tempCategoryId = 0;
	$tempSubcategoryId = 0;

	$query = (new MT_QueryBuilder())
		->from('photo')
		->select('COUNT(wp_mt_photo.id) as numPhotos')
		->joinInner('gallery', TRUE, array('id AS galleryId', 'name as galleryName'))
		->joinInner('category', 'wp_mt_category.id = wp_mt_gallery.category', array('id AS categoryId', 'name AS categoryName'))
		->joinLeftOuter('subcategory', 'wp_mt_subcategory.id = wp_mt_gallery.subcategory', array('id AS subcategoryId', 'name subcategoryName'))
		->whereEqual('wp_mt_photo.show', 1)
		->groupBy(array('categoryName', 'subcategoryName', 'galleryName'))
		->orderBy(array('categoryName', 'subcategoryName', 'galleryName'));
	foreach ($query->getResult() as $row) {
		// Category
		if( $row->categoryId != $tempCategoryId ) {
			$tempCategoryId = $row->categoryId;
			$returnString .= '
			 <tr>
			  <td><u>'.$row->categoryName.'</u></td>
			  <td></td>
			 </tr>';
		}

		// Subcategory
		if( $row->subcategoryId != $tempSubcategoryId) {
			$tempSubcategoryId = $row->subcategoryId;
			$returnString .= '
			 <tr>
			  <td>&nbsp;&nbsp;»&nbsp;&nbsp;'.$row->subcategoryName.'</td>
			  <td></td>
			 </tr>';
		}

		// Gallery
		$returnString .= '
			 <tr>
			  <td>&nbsp;&nbsp;&nbsp;&nbsp;»&nbsp;&nbsp;<a href="'.MT_Photo::$__photoPathAbs.$row->galleryId.'">'.$row->galleryName.'</a></td>
			  <td>'.$row->numPhotos.'</td>
			 </tr>';
	}
	$returnString .= '</table>';
	return $returnString;
}

add_shortcode('mt_recent_post', 'mt_add_shortcode_recent_post');
function mt_add_shortcode_recent_post() {
	$recent_posts = wp_get_recent_posts();
	$returnString = '';
	foreach( $recent_posts as $recent ){
		$returnString .= '<h3>'.$recent["post_title"].'</h3>'.$recent["post_content"].'<div class="postDate">Verfasst am: '.$recent["post_modified"].'</div>';
	}	
	return $returnString;
}

add_shortcode('mt_news', 'mt_add_shortcode_news');
function mt_add_shortcode_news() {
	$returnString = '';

	$dateYear_old = '';
	$dateMonth_old = '';
	$dateDay_old = '';

	$newsItems = MT_News::getAll(array('title', 'text', 'gallery', 'date' ), 'date DESC');
	foreach ($newsItems as $item) {
		// News link
		if( empty( $item->gallery ) ) {
			$news_link = '../';
		} else {
			$news_link = MT_Photo::$__photoPathAbs.$item->gallery;
		}

		// Year
		$dateYear = strftime( '%Y', $item->date );
		if( $dateYear != $dateYear_old && $dateYear != date( 'Y', time() ) ) {
			$dateYear_old = $dateYear;
			$returnString .= '
			</table>
			<hr>
			<center><h1>' . $dateYear . '</h1></center>';
		}

		// Month
		$dateMonth = strftime( '%B', $item->date );
		if($dateMonth != $dateMonth_old) {
				
			// Beim ersten Monat <table> noch nicht beenden
			if( !empty( $dateMonth_old ) ) {
				$returnString .= '</table>';
			}
			$dateMonth_old = $dateMonth;

			$returnString .= '
			<h3>' . $dateMonth . '</h3>
			<table class="table_quer" cellSpacing="4" cellPadding="1">
				<colgroup>
    				<col width="95px">
    				<col width="*">
  				</colgroup>';
					}
					
		// Day
		$returnString .= '<tr>';
				
		$dateDay = strftime( '%a, %d.%m.', $item->date );
				
		if($dateDay != $dateDay_old) {
			$dateDay_old = $dateDay;
					
			$returnString .= '<th>'. $dateDay . ':</th>';
		}
		else {
			$returnString .= '<th></th>';
		}
		$returnString .= '<td><a href="' . $news_link . '">' . $item->title . '</a><br>' . $item->text . '</td>
				</tr>';
	}
	$returnString .= '</table>';		
	return $returnString;
}


add_shortcode('mt_photographers', 'mt_add_shortcode_photographers');
function mt_add_shortcode_photographers() {
	$returnString = '<ul>';
	
	$photo = new MT_Photo();
	
	$items = MT_Photographer::getAll(array('id', 'name'), 'name');
	foreach ($items as &$item) {
		$returnString .= '<li><a href="'.MT_Photographer::$photographersPath.$item->id.'">'.$item->name.'</a>&nbsp;<span class="style_grew">('.$photo->getNumPhotos($item->id).')</span></li>';				
	}
	$returnString .= '</ul>';
	return $returnString;
}


/*
 * Admin menu hook
 */
add_action('admin_menu', 'mt_admin_menu');
function mt_admin_menu() {

    // Add a new submenu under Settings:
    add_options_page(__('Test Settings','menu-test'), __('Test Settings','menu-test'), 'manage_options', 'testsettings', 'mt_settings_page');

    // Add a new submenu under Tools:
	//add_management_page( __('Test Tools','menu-test'), __('Test Tools','menu-test'), 'manage_options', 'testtools', 'mt_tools_page');

    // Add top-level and submenu menu item
    add_menu_page('MT Bilder', 'MT Bilder', 'manage_options', 'mt-photo', null, 'dashicons-palmtree', 3);
	add_submenu_page('mt-photo', 'Fotos verwalten', 'Fotos verwalten', 'manage_options', 'mt-photo', 'mt_page_photos');
	add_submenu_page('mt-photo', 'Fotos hinzufügen', 'Fotos hinzufügen', 'manage_options', 'mt-photo-add', 'mt_page_photos_add');
	add_submenu_page('mt-photo', 'News generieren', 'News generieren', 'manage_options', 'mt-news-generate', 'mt_page_news_generate');

    add_menu_page('MT Verwaltung', 'MT Verwaltung', 'manage_options', 'mt-news', null, 'dashicons-hammer', 4);
    add_submenu_page('mt-news', 'News', 'News', 'manage_options', 'mt-news', 'mt_page_news');
    add_submenu_page('mt-news', 'Kategorien', 'Kategorien', 'manage_options', 'mt-category', 'mt_page_categories');
    add_submenu_page('mt-news', 'Unterkategorien', 'Unterkategorien', 'manage_options', 'mt-subcategory', 'mt_page_subcategories');
    add_submenu_page('mt-news', 'Galerien', 'Galerien', 'manage_options', 'mt-gallery', 'mt_page_galleries');
	add_submenu_page('mt-news', 'Fotografen', 'Fotografen', 'manage_options', 'mt-photographer', 'mt_page_photographers');
	add_submenu_page('mt-news', 'Vorschaubilder', 'Vorschaubilder', 'manage_options', 'mt-thumbnail', 'mt_page_thumbnails');
}

// mt_settings_page() displays the page content for the Test settings submenu
function mt_settings_page() {

    //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    // variables for the field and option names 
    $opt_name = 'mt_favorite_color';
    $hidden_field_name = 'mt_submit_hidden';
    $data_field_name = 'mt_favorite_color';

    // Read in existing option value from database
    $opt_val = get_option( $opt_name );

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $opt_val = $_POST[ $data_field_name ];

        // Save the posted value in the database
        update_option( $opt_name, $opt_val );

        // Put an settings updated message on the screen

?>
<div class="updated"><p><strong><?php _e('settings saved.', 'menu-test' ); ?></strong></p></div>
<?php

    }

    // Now display the settings editing screen

    echo '<div class="wrap">';

    // header

    echo "<h2>" . __( 'Menu Test Plugin Settings', 'menu-test' ) . "</h2>";

    // settings form
    
    ?>

<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<p><?php _e("Favorite Color:", 'menu-test' ); ?> 
<input type="text" name="<?php echo $data_field_name; ?>" value="<?php echo $opt_val; ?>" size="20">
</p><hr />

<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>

</form>
</div>

<?php
 
}

function mt_page_photos() {
	require_once(MT_DIR . '/admin/view/crud/PhotoEdit.php');

	$tmp = new MT_Admin_Field(NULL, NULL);
	$id = $_GET['mtId'];
	$page = (!empty($_GET['mtpage']) ? $_GET['mtpage'] : 1);
	?>
			<select name="selectGalerie" onchange="location = '?page=mt-photo&mtId=' + this.options[this.selectedIndex].value;">
				<option value="">Galerie wählen ...</option>
				<?php echo $tmp->outputAllGalleries($id); ?>
			</select>
	<?php
	
	if (!empty($id)) {
		$photoEditView = new MT_View_PhotoEdit($id, $page);
		$photoEditView->outputContent();
	}
}

function mt_page_photos_add() {
	require_once(MT_DIR . '/admin/model/PhotoSearch.php');
	require_once(MT_DIR . '/admin/view/crud/PhotoEdit.php');

	// Nach neuen Bildern suchen, wenn weniger als 8 neue Bilder in der Datenbank gespeichert sind
	if(MT_Photo::getCountNewPhotos() < 10 or $_GET['action'] === 'search') {
		(new MT_Admin_Model_PhotoSearch())->search('../bilder');
		// Datum der letzten Suche speichern
		update_option('datum_letzte_suche', time());
	}
	$photoEditView = new MT_View_PhotoEdit();
	$photoEditView->outputContent();
	
}

function mt_page_news_generate() {
	require_once(MT_DIR . '/admin/model/NewsGeneration.php');

	$newsGeneration = new MT_Admin_NewsGeneration();
	$newsData = $newsGeneration->getGeneratedNews();
//	$newsData = array_slice($newsData,0,5);

	if (!$newsGeneration->checkGenerateNews()) {
		?>
		<div class="wrap">
			<h2>News <?php echo MT_Functions::addButton('?page=mt-'.MT_News::name().'&type=edit'); ?></h2>
		<p>Es gibt keine neuen Bilder, sodass keine News generiert werden können!</p>
		</div>
		<?php
	} else {
		$editView = new MT_View_Edit(new MT_News());
		$editView->setFields(array(
			(new MT_Admin_Field('title', 'Title'))
				->setRequired()
				->setMaxLength(100),
			(new MT_Admin_Field('text', 'Text', 'text'))->setRequired(),
			(new MT_Admin_Field('gallery', 'Galerie', 'reference'))
				->setReference('gallery')
				->setRequired()
		));
		$editView->setData($newsData);
		$editView->outputContent();
	}
}

function mt_page_news() {
	if ($_GET['type'] === 'edit') {
		$editView = new MT_View_Edit( new MT_News($_GET['id']) );
		$editView->setFields(array(
			(new MT_Admin_Field('title', 'Title'))->setRequired(),
			(new MT_Admin_Field('text', 'Text'))->setRequired(),
			(new MT_Admin_Field('date', 'Datum', 'date'))->setDisabled(),
			(new MT_Admin_Field('gallery', 'Galerie', 'reference'))->setReference('gallery')
		));
		$editView->outputContent();
	} else {
		$listView = new MT_View_List( new MT_News() );
		$listView->setFields(array(
			(new MT_Admin_Field('title', 'Titel')),
			(new MT_Admin_Field('date', 'Datum', 'date'))
		));	
		$listView->setOrder( 'date DESC' );
		$listView->setPerPage(20);
		$listView->outputContent();
	}
}

function mt_page_categories() {
	if ($_GET['type'] === 'edit') {
		$editView = new MT_View_Edit( new MT_Category($_GET['id']) );
		$editView->setFields(array(
			(new MT_Admin_Field('name', 'Name'))->setRequired(),
			(new MT_Admin_Field('description', 'Beschreibung')),
			(new MT_Admin_Field('path', 'Pfad'))->setDisabled()
		));	
		$editView->outputContent();
	} else {
		$listView = new MT_View_List( new MT_Category() );
		$listView->setFields(array(
			(new MT_Admin_Field('name', 'Name')),
			(new MT_Admin_Field('path', 'Pfad'))
		));
		$listView->outputContent();
	}
}

function mt_page_subcategories() {
	if ($_GET['type'] === 'edit') {
		$editView = new MT_View_Edit( new MT_Subcategory($_GET['id']) );
		$editView->setFields(array(
			(new MT_Admin_Field('name', 'Name'))->setRequired(),
			(new MT_Admin_Field('path', 'Pfad'))->setDisabled(),
			(new MT_Admin_Field('name', 'Kategorie'))
				->setReference('category')
				->setDisabled(),			
		));	
		$editView->outputContent();
	} else {
		$listView = new MT_View_List( new MT_Subcategory() );
		$listView->setFields(array(
			(new MT_Admin_Field('name', 'Name')),
			(new MT_Admin_Field('category', 'Kategorie'))
				->setReference('category', 'name')
		));
		$listView->outputContent();
	}
}

function mt_page_galleries() {
	if ($_GET['type'] === 'edit') {
		/*
					$this->_title = 'Galerie';
			$this->_table->setTableHead( array( 'Name', 'Beschreibung', 'Pfad', 'Kategorie', 'Unterkategorie' ) );
			$this->_table->setTableFieldAttributes( array( 'required', '', 'disabled', 'disabled', 'disabled' ) );

			$query = MT_Gallery::__getSqlQuery( array( 'gallery_name', 'gallery_description', 'gallery_path' ), 'category_name', 'subcategory_name' );
*/
			
		$editView = new MT_View_Edit( new MT_Gallery($_GET['id']) );
		$editView->setFields(array(
			(new MT_Admin_Field('name', 'Name'))->setRequired(),
			(new MT_Admin_Field('description', 'Beschreibung', 'text')),
			(new MT_Admin_Field('path', 'Pfad'))
				->setDisabled(),			
			(new MT_Admin_Field('category', 'Kategorie', 'reference'))
				->setReference('category', 'name'),
			(new MT_Admin_Field('subcategory', 'Unterkategorie', 'reference'))
				->setReference('subcategory', 'name')
		));
		$editView->outputContent();
	} else {
		$listView = new MT_View_List( new MT_Gallery() );
		$listView->setFields(array(
			(new MT_Admin_Field('name', 'Name')),
			(new MT_Admin_Field('fullPath', 'Vollständiger Pfad'))
		));
		$listView->setOrder('fullPath');
		$listView->outputContent();
	}	
}

function mt_page_photographers() {
	if ($_GET['type'] === 'edit') {
		$editView = new MT_View_Edit( new MT_Photographer($_GET['id']) );
		$editView->setFields(array(
			(new MT_Admin_Field('name', 'Name'))->setRequired(),
			(new MT_Admin_Field('camera', 'Kamera')),
			(new MT_Admin_Field('date', 'Datum', 'date'))->setDisabled()
		));
		$editView->outputContent();
	} else {
		$listView = new MT_View_List( new MT_Photographer() );
		$listView->setFields(array(
			(new MT_Admin_Field('name', 'Name'))
		));
		$listView->setOrder('name');
		$listView->outputContent();
	}
}

function mt_page_thumbnails() {
//	require_once(MT_DIR . '/admin/model/PhotoResize.php');
//	$photoResize = new MT_Admin_Model_PhotoResize();
//	$photoResize->resizeAllImages(200, 200, 90);
}

?>