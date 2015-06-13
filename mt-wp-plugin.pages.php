<?php
/*
 * Admin menu hook
 */
add_action('admin_menu', 'mt_admin_menu');
function mt_admin_menu() {

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
	//add_submenu_page('mt-news', 'Vorschaubilder', 'Vorschaubilder', 'manage_options', 'mt-thumbnail', 'mt_page_thumbnails');
}

function mt_page_photos() {
	require_once(MT_DIR . '/admin/view/crud/PhotoEdit.php');

	$tmp = new MT_Admin_Field(NULL, NULL);
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

function mt_page_photos_add() {
	require_once(MT_DIR . '/admin/model/PhotoSearch.php');
	require_once(MT_DIR . '/admin/view/crud/PhotoEdit.php');

	// Nach neuen Bildern suchen, wenn weniger als 8 neue Bilder in der Datenbank gespeichert sind
	if(MT_Photo::getCountNewPhotos() < 10 or $_GET['action'] === 'search') {
		(new MT_Admin_Model_PhotoSearch())->search();
		// Datum der letzten Suche speichern
		update_option('datum_letzte_suche', time());
	}
	$photoEditView = new MT_Admin_View_PhotoEdit();
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
			(new MT_Admin_Field('gallery', 'Galerie'))
				->setStaticReference('gallery')
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
			(new MT_Admin_Field('gallery', 'Galerie'))->setStaticReference('gallery')
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
//	$query = (new MT_QueryBuilder())
//			->from('photo')
//			->orderBy('date');
//	$result = $query->getResult();
//	$date = 1000;
//	$photo = new MT_Photo();
//	foreach ($result as $index => $item) {
//		echo $index.$photo->date.'<br>';
//		$photo->update(array(
//			'date' => $date + $index * 10
//		), array(
//			'id' => $item->id
//		));
//	}
//	require_once(MT_DIR . '/admin/model/ThumbnailCreator.php');
//	(new MT_Admin_Model_ThumbnailCreator)->resize();
}