<?php

class MT_View_StaticGallery extends MT_View_Gallery {

	private $item;
	private $userSettings;
	
	/**
	 * Number of photos in gallery
	 *
	 * @var int
	 */
	private $_numPhotos;

	/**
	 * [...]
	 *
	 * @param	int	$id	Gallery id
	 * [...]
	 */
	public function __construct($id, $page, $num, $sort) {

		// Construct query
		$query = (New MT_QueryBuilder())
			->from('gallery', array('id as galleryId', 'name as galleryName', 'description'))
			->join('category', TRUE, array('id AS categoryId', 'name AS categoryName'))
			->joinLeft('subcategory', TRUE, 'name as subcategoryName')
			->whereEqual('wp_mt_gallery.id', $id);
		$this->item = $query->getResultOne();
				
		if (empty($this->item)) {
			throw new Exception('Die ausgewählte Galerie exestiert nicht.');
		}
		
		$this->userSettings = MT_Functions::getUserSettings($sort, $num, $page);
		// Anzahl der Seiten in dieser Galerie unter Berücksichtigung der Anzahl der Bilder
//		if($this->userSettings['page'] > MT_Photo::getNumPages($this->item->galleryId, $this->userSettings['num'])) {
//			$this->userSettings[page] = 1;
//		}

		// Anzahl der Bilder in der Galerie
		$this->_numPhotos = MT_Photo::getCount($this->item->galleryId);
		
		// Pagination
		$url = explode(',', $_SERVER['REQUEST_URI']);
		$this->pagination =	MT_Functions::__outputPagination($this->_numPhotos, $this->userSettings['page'], $this->userSettings['num'], $this->userSettings['sort'], $url[0].',');

		parent::setTitle($this->item->galleryName);
		parent::setDescription('Fotogalerie ' . $this->item->galleryName . ' in der Kategorie ' . $this->item->categoryName);
		parent::setWidescreen($this->_numPhotos > 0);

		// Breadcrumb
		$categoryLink = MT_Category::$_categoryPath . $this->item->categoryId;
		$breadcrumb = array(
			$categoryLink => $this->item->categoryName
		);
		if (!empty($this->item->subcategoryName)) {
			$breadcrumb[$categoryLink . '#'] = $this->item->subcategoryName;
		}
		$breadcrumb[''] = $this->item->galleryName;
		parent::setBreadcrumb($breadcrumb);
	}

	public function outputContent() {
		$query = (new MT_QueryBuilder())
			->from('photo', array('id as photoId', 'path', 'description', 'date'))
			->joinLeft('photographer', TRUE, array('id as photographerId', 'name as photographerName'))
			->whereEqual('gallery', $this->item->galleryId)
			->whereEqual('`show`', '1')
			->orderBy('date')
			->limitPage($this->userSettings['page'], $this->userSettings['num']);
				
		// Sortierung der Bild nach dem Datum
		if($this->userSettings['sort'] === 'date') {
			$query->orderBy('date DESC');
		}
		
		// ggf. Galeriebeschreibung
		if (!empty( $this->item->description)) {
			echo '<p>' . $this->item->description . '</p>';
		}
			
		if ($this->_numPhotos > 0) {
			$this->_outputContentHeader();
			$this->_outputContentPhotos($query, $this->item->galleryName.' (' . $this->item->categoryName . ')');
			$this->_outputContentFooter();
		} else {
			// Falls sich in der Galerie noch keine Bilder befinden
			?>
			<p align="center"><img src="<?php echo wp_get_attachment_url(123); ?>"></p>
			<p>In dieser Galerie befinden sich noch keine Bilder! Schau später noch einmal vorbei!</p>
			<p>Zurück zur Übersicht: <a href="<?php echo MT_Category::$_categoryPath . $this->item->categoryId; ?>"><?php echo $this->item->categoryName; ?></a></p>
			<?php
		}
	}


	/**
	 * Output Auswahlleiste and pagination (Form: div, table)
	 *
	 * @return void
	 */
	private function _outputContentHeader() {
		$location = "location = '".$this->item->galleryId.",page=".$this->userSettings["page"]."&'+this.options[this.selectedIndex].value;";
		?>
			<div id="auswahl_leiste">
				<table width="100%" cellSpacing="0" cellPadding="2">
					<tr>
						<th>&nbsp;Bilder:&nbsp;<?php echo $this->_numPhotos; ?></th>
						<td>
							Bilder pro Seite:&nbsp;
							<select name="num" size="1" onchange="<?php echo $location; ?>">
								<option value="num=5&sort=<?php echo $this->userSettings['sort']; ?>" <?php echo MT_Functions::selected($this->userSettings['num'], '5'); ?>>5</option>
								<option value="num=10&sort=<?php echo $this->userSettings['sort']; ?>" <?php echo MT_Functions::selected($this->userSettings['num'], '10'); ?>>10</option>
								<option value="num=15&sort=<?php echo $this->userSettings['sort']; ?>" <?php echo MT_Functions::selected($this->userSettings['num'], '15'); ?>>15</option>
							</select>
							&nbsp;Sortieren nach:&nbsp;
							<select name="sort" size="1" onchange="<?php echo $location; ?>">
								<option value="num=<?php echo $this->userSettings['num']; ?>&sort=date" <?php echo MT_Functions::selected($this->userSettings['sort'], 'date'); ?>>Einstellungsdatum: Neu - Alt</option>
								<option value="num=<?php echo $this->userSettings['num']; ?>&sort=-date" <?php echo MT_Functions::selected($this->userSettings['sort'], '-date'); ?>>Einstellungsdatum: Alt - Neu</option>
							</select>
						</td>
					</tr>
				</table>
			</div>
			<?php echo $this->pagination; ?>
		<?php
	}


	/**
	 * Output pagination, link to Hauptparkplatz, etc. (Form: table)
	 *
	 * @return void
	 */
	private function _outputContentFooter() {	
		?>
				<table class="seitenleiste">
					<colgroup>
						<col width="100px" />
						<col width="550px" />
						<col width="100px" />
					</colgroup>
					<tr>
						<td></td>
						<td><?php echo $this->pagination; ?></td>
						<td><span class="nach_oben"><a href="javascript:self.scrollTo(0,0)">Nach oben</a></span></td>
					</tr>
				</table>
				<h2>Nutzung der Bilder</h2>
<p>Alle Bilder auf dieser Seite unterliegen dem Copyright des jeweiligen Fotografens. Es ist nicht gestattet die Bilder 
im Internet, etc. zu veröffentlichen.</p>
		<?php
	}
}
?>