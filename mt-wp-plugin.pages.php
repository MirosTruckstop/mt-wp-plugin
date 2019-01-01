<?php
// phpcs:disable PEAR.NamingConventions.ValidFunctionName.FunctionNoCapital, PEAR.NamingConventions.ValidFunctionName.FunctionNameInvalid
use MT\WP\Plugin\Api\MT_Category;
use MT\WP\Plugin\Api\MT_Gallery;
use MT\WP\Plugin\Api\MT_News;
use MT\WP\Plugin\Api\MT_Photo;
use MT\WP\Plugin\Api\MT_Photographer;
use MT\WP\Plugin\Api\MT_Subcategory;
use MT\WP\Plugin\Backend\Model\Form\MT_Admin_Field;
use MT\WP\Plugin\Backend\Model\MT_Admin_Model_PhotoSearch;
use MT\WP\Plugin\Backend\Model\MT_Admin_NewsGeneration;
use MT\WP\Plugin\Backend\View\Crud\MT_Admin_View_PhotoEdit;
use MT\WP\Plugin\Backend\View\Crud\MT_View_Edit;
use MT\WP\Plugin\Backend\View\Crud\MT_View_List;
use MT\WP\Plugin\Common\Util\MT_Util_Html;

/**
 * Type edit
 */
const TYPE_EDIT = 'edit';

/**
 * Admin menu hook: Add pages to menu.
 */
add_action('admin_menu', function () {
	// Add top-level and submenu menu item
	add_menu_page('MT Bilder', 'MT Bilder', 'edit_others_pages', 'mt-photo', null, 'dashicons-palmtree', 3);
	add_submenu_page('mt-photo', 'Fotos verwalten', 'Fotos verwalten', 'edit_others_pages', 'mt-photo', 'mt_page_photos');
	add_submenu_page('mt-photo', 'Fotos hinzufügen', 'Fotos hinzufügen', 'edit_others_pages', 'mt-photo-add', 'mt_page_photos_add');
	add_submenu_page('mt-photo', 'News generieren', 'News generieren', 'edit_others_pages', 'mt-news-generate', 'mt_page_news_generate');

	add_menu_page('MT Verwaltung', 'MT Verwaltung', 'edit_others_pages', 'mt-news', null, 'dashicons-hammer', 4);
	add_submenu_page('mt-news', 'News', 'News', 'edit_others_pages', 'mt-news', 'mt_page_news');
	add_submenu_page('mt-news', 'Kategorien', 'Kategorien', 'edit_others_pages', 'mt-category', 'mt_page_categories');
	add_submenu_page('mt-news', 'Unterkategorien', 'Unterkategorien', 'edit_others_pages', 'mt-subcategory', 'mt_page_subcategories');
	add_submenu_page('mt-news', 'Galerien', 'Galerien', 'edit_others_pages', 'mt-gallery', 'mt_page_galleries');
	add_submenu_page('mt-news', 'Fotografen', 'Fotografen', 'edit_others_pages', 'mt-photographer', 'mt_page_photographers');
});

/**
 * Page edit photos
 *
 * @return void
 */
function mt_page_photos()
{
	$tmp = new MT_Admin_Field(null, null);
	$id = $_GET['mtId'];
	$page = (!empty($_GET['mtpage']) ? $_GET['mtpage'] : 1);
	?>
			<select name="selectGalerie" onchange="location = '?page=mt-photo&mtId=' + this.options[this.selectedIndex].value;">
				<option value=""><?php _e('Galerie wählen', MT_NAME); ?> ...</option>
				<?php echo $tmp->outputAllGalleries($id); ?>
			</select>
	<?php
	
	if (!empty($id)) {
		$photoEditView = new MT_Admin_View_PhotoEdit($id, $page);
		$photoEditView->outputContent();
	}
}

/**
 * Page add new photos
 *
 * @return void
 */
function mt_page_photos_add()
{
	$paQueueClient = null;
	// True, if the MT PA plugin exists
	if (defined('MT_PA_NAME') && defined('MT_PA_OPTION_QUEUE_TOPIC')) {
		include_once plugin_dir_path(__FILE__).'../'.MT_PA_NAME.'/src/php/QueueClient.php';
		$paQueueClient = new MT\PhotoAnalysis\QueueClient(get_option(MT_PA_OPTION_QUEUE_TOPIC));
	}

	// Nach neuen Bildern suchen, wenn weniger als 8 neue Bilder in der Datenbank gespeichert sind
	if (MT_Photo::getCountNewPhotos() < 10 or $_GET['action'] === 'search') {
		(new MT_Admin_Model_PhotoSearch($paQueueClient))->search();
		// Datum der letzten Suche speichern
		update_option('datum_letzte_suche', time());
	}
	$photoEditView = new MT_Admin_View_PhotoEdit();
	$photoEditView->outputContent();
}

/**
 * Page generate news
 *
 * @return void
 */
function mt_page_news_generate()
{
	$newsGeneration = new MT_Admin_NewsGeneration();
	$newsData = $newsGeneration->getGeneratedNews();

	if (!$newsGeneration->checkGenerateNews()) {
		?>
		<div class="wrap">
			<h2>News <?php echo MT_Util_Html::addButton('?page=mt-'.MT_News::name().'&type=edit'); ?></h2>
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
			(new MT_Admin_Field('gallery', 'Galerie'))
				->setReference('gallery')
				->setRequired()
		));
		$editView->setData($newsData);
		$editView->outputContent();
	}
}

/**
 * Page list/edit news
 *
 * @return void
 */
function mt_page_news()
{
	if ($_GET['type'] === TYPE_EDIT) {
		$editView = new MT_View_Edit(new MT_News($_GET['id']));
		$editView->setFields(array(
			(new MT_Admin_Field('title', 'Title'))->setRequired(),
			(new MT_Admin_Field('text', 'Text'))->setRequired(),
			(new MT_Admin_Field('date', 'Datum', 'date'))->setDisabled(),
			(new MT_Admin_Field('gallery', 'Galerie'))->setReference('gallery')
		));
		$editView->outputContent();
	} else {
		$listView = new MT_View_List(new MT_News());
		$listView->setFields(array(
			(new MT_Admin_Field('title', 'Titel')),
			(new MT_Admin_Field('date', 'Datum', 'date'))
		));
		$listView->setOrder('date DESC');
		$listView->setPerPage(20);
		$listView->outputContent();
	}
}

/**
 * Page list/edit categories
 *
 * @return void
 */
function mt_page_categories()
{
	if ($_GET['type'] === TYPE_EDIT) {
		$editView = new MT_View_Edit(new MT_Category($_GET['id']));
		$editView->setFields(array(
			(new MT_Admin_Field('name', 'Name'))->setRequired(),
			(new MT_Admin_Field('description', 'Beschreibung')),
			(new MT_Admin_Field('path', 'Pfad'))->setDisabled()
		));
		$editView->outputContent();
	} else {
		$listView = new MT_View_List(new MT_Category());
		$listView->setFields(array(
			(new MT_Admin_Field('name', 'Name')),
			(new MT_Admin_Field('path', 'Pfad'))
		));
		$listView->outputContent();
	}
}

/**
 * Page list/edit subcategories
 *
 * @return void
 */
function mt_page_subcategories()
{
	if ($_GET['type'] === TYPE_EDIT) {
		$fieldCategory = (new MT_Admin_Field('category', 'Kategorie'))
			->setReference('category')
			->setRequired();
		
		$id = $_GET['id'];
		if (!empty($id)) {
			$fieldCategory->setDisabled();
		}
		
		$editView = new MT_View_Edit(new MT_Subcategory($id));
		$editView->setFields(array(
			(new MT_Admin_Field('name', 'Name'))->setRequired(),
			(new MT_Admin_Field('path', 'Pfad'))->setDisabled(),
			$fieldCategory
		));
		$editView->outputContent();
	} else {
		$listView = new MT_View_List(new MT_Subcategory());
		$listView->setFields(array(
			(new MT_Admin_Field('name', 'Name')),
			(new MT_Admin_Field('category', 'Kategorie'))
				->setReference('category', 'name')
		));
		$listView->outputContent();
	}
}

/**
 * Page list/edit galleries
 *
 * @return void
 */
function mt_page_galleries()
{
	if ($_GET['type'] === TYPE_EDIT) {
		$id = $_GET['id'];
		$fieldCategory = (new MT_Admin_Field('category', 'Kategorie'))
			->setReference('category', 'name')
			->setRequired();
		$fieldSubcategory = (new MT_Admin_Field('subcategory', 'Unterkategorie'))
				->setReference('subcategory', 'name');
		if (!empty($id)) {
			$fieldCategory->setDisabled();
			$fieldSubcategory->setDisabled();
		}
		$editView = new MT_View_Edit(new MT_Gallery($id));
		$editView->setFields(array(
			(new MT_Admin_Field('name', 'Name'))
				->setRequired(),
			(new MT_Admin_Field('description', 'Beschreibung', 'text')),
			(new MT_Admin_Field('keywords', 'Keywords', 'text')),
			(new MT_Admin_Field('path', 'Pfad'))
				->setDisabled(),
			$fieldCategory,
			$fieldSubcategory
		));
		$editView->outputContent();
	} else {
		$listView = new MT_View_List(new MT_Gallery());
		$listView->setFields(array(
			(new MT_Admin_Field('name', 'Name')),
			(new MT_Admin_Field('fullPath', 'Vollständiger Pfad'))
		));
		$listView->setOrder('fullPath');
		$listView->outputContent();
	}
}

/**
 * Page list/edit photographers
 *
 * @return void
 */
function mt_page_photographers()
{
	if ($_GET['type'] === TYPE_EDIT) {
		$editView = new MT_View_Edit(new MT_Photographer($_GET['id']));
		$editView->setFields(array(
			(new MT_Admin_Field('name', 'Name'))->setRequired(),
			(new MT_Admin_Field('date', 'Datum', 'date'))->setDisabled()
		));
		$editView->outputContent();
	} else {
		$listView = new MT_View_List(new MT_Photographer());
		$listView->setFields(array(
			(new MT_Admin_Field('name', 'Name'))
		));
		$listView->setOrder('name');
		$listView->outputContent();
	}
}