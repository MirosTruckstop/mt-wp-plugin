<?php

class MT_View_StaticGallery extends MT_View_Gallery {

	private $item;
	
	/**
	 * Category path
	 *
	 * @var string
	 */
	public static $_categoryPath = '../kategorie/';

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
			->from('gallery', array('id as galleryId', 'name as galleryName', 'description', 'hauptparkplatz'))
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
		$this->pagination =	MT_Functions::__outputPagination( $this->item->galleryId, $this->userSettings['page'], $this->userSettings['num'], $this->userSettings['sort'], $url[0].',');
	}

	public function outputTitle() {
		echo $this->item->galleryName;
	}

    public function outputDescription() {
		echo 'Fotogalerie ' . $this->item->galleryName . ' in der Kategorie ' . $this->item->categoryName;
	}

	public function checkWidescreen() {
		return !empty( $this->_numPhotos );
	}


	public function outputBreadcrumb() {
		$categoryLink = self::$_categoryPath . $this->item->categoryId;
			echo '
                                    <div itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
                                        <a href="' . $categoryLink . '" itemprop="url"><span itemprop="title">' . $this->item->categoryName . '</span></a>&nbsp;>'
                                     . MT_Functions::getIfNotEmpty( $this->item->subcategoryName, '<a href="' . $categoryLink . '" itemprop="url"><span itemprop="title">' . $this->item->subcategoryName . '</span></a>&nbsp;>' ) . '
                                        <a href="" itemprop="url"><span itemprop="title">' . $this->item->galleryName . '</span></a>
                                    </div>';
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
			<p>Zurück zur Übersicht: <a href="<?php echo self::$_categoryPath . $this->item->categoryId; ?>"><?php echo $this->item->categoryName; ?></a></p>
			<?php
		}
	}


	/**
	 * Output Auswahlleiste and pagination (Form: div, table)
	 *
	 * @return void
	 */
	private function _outputContentHeader() {
		// Auswahlleiste
//		$url = explode(',', $_SERVER['REQUEST_URI']);
//		$url = url[0].','
		?>
			<div id="auswahl_leiste">
				<form action="" method="get">
					<table width="100%" cellSpacing="0" cellPadding="2">
						<tr>
							<th>&nbsp;<?php echo _("Bilder"); ?>:&nbsp;<?php echo $this->_numPhotos; ?></th>
							<td>
								<input name=",page" value="<?php echo $this->userSettings['page']; ?>" type="hidden">
								<?php echo _("Bilder pro Seite"); ?>:&nbsp;
								<select name="num" size="1">
									<option value="5" <?php echo MT_Functions::selected( $this->userSettings['num'], '5' ); ?>>5</option>
									<option value="10" <?php echo MT_Functions::selected( $this->userSettings['num'], '10' ); ?>>10</option>
									<option value="15" <?php echo MT_Functions::selected( $this->userSettings['num'], '15' ); ?>>15</option>
								</select>
								&nbsp;<?php echo _("Sortieren nach"); ?>:&nbsp;
								<select name="sort" size="1">
									<option value="date" <?php echo MT_Functions::selected( $this->userSettings['sort'], 'date'); ?>><?php echo _("Einstellungsdatum"); ?>: <?php echo _("Neu - Alt"); ?></option>
									<option value="-date" <?php echo MT_Functions::selected( $this->userSettings['sort'], '-date'); ?>><?php echo _("Einstellungsdatum"); ?>: <?php echo _("Alt - Neu"); ?></option>
								</select>
								<input type="submit" value="OK" class="button">
							</td>
						</tr>
					</table>
				</form>
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
		<?php
		// Verlinkung der Galerie mit dem Hauptparkplatz
		if( !empty( $this->item->hauptparkplatz ) ) {
			?>
					<tr>
						<td colspan="3"><center><a href="http://rosensturm.de/<?php echo $this->item->hauptparkplatz; ?>.html" target="_blank"><?php $this->item->galleryName; ?> auf dem Hauptparkplatz</a></center></td>
					</tr>
			<?php
		}
		?>
				</table>
				<h2>Nutzung der Bilder</h2>
<p>Alle Bilder auf dieser Seite unterliegen dem Copyright des jeweiligen Fotografens. Es ist nicht gestattet die Bilder 
im Internet, etc. zu veröffentlichen.</p>
		<?php
	}
}
?>