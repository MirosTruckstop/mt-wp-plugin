<?php

class MT_View_Gallery {

	/**
	 * Category path
	 *
	 * @var string
	 */
	private $_categoryPath = '../kategorie/';

	/**
	 * Galleries ID
	 *
	 * @var int
	 */
	private $_id;

	/**
	 * Galleries name
	 *
	 * @var string
	 */
	private $_name;

	/**
	 * Galleries linkt to Hauptparkplatz
	 *
	 * @var string
	 */
	private $_hauptparkplatz;

	/**
	 * Galleries category id
	 *
	 * @var string
	 */
	private $_categoryId;

	/**
	 * Galleries category name
	 *
	 * @var string
	 */
	private $_categoryName;

	/**
	 * Galleries subcategory name
	 *
	 * @var string
	 */
	private $_subcategoryName;
	
	/**
	 * Galleries description
	 *
	 * @var string
	 */
	private $_description;

	/**
	 * Number of photos in gallery
	 *
	 * @var int
	 */
	private $_numPhotos;

	/**
	 * Page number (GET)
	 *
	 * @var string
	 */
	 private $_userPage;

	/**
	 * Number of pictures per page (GET)
	 *
	 * @var string
	 */
	private $_userNum;

	/**
	 * Sort (GET)
	 *
	 * @var string
	 */
	private $_userSort;
	
	private $photo;


	/**
	 * [...]
	 *
	 * @param	int	$id	Gallery id
	 * [...]
	 */
	public function __construct($id) {
		$this->_id = $id;

		// Construct query
		$query = (New MT_QueryBuilder('wp_mt_'))
			->from('gallery', array('id as galleryId', 'name as galleryName', 'description', 'hauptparkplatz'))
			->join('category', TRUE, array('id AS categoryID', 'name AS categoryName'))
			->joinLeft('subcategory', TRUE, 'name as subcategoryName')
			->whereEqual('wp_mt_gallery.id', $this->_id);
		$item = $query->getResultOne();
				
		$this->_name = $item['galleryName'];
		$this->_description = $item['description'];
		$this->_hauptparkplatz = $item['hauptparkplatz'];
		$this->_categoryId = $item['categoryId'];
		$this->_categoryName = $item['categoryName'];
		$this->_subcategoryName = $item['subcategoryName'];

		
		$this->photo = new MT_Photo();
		
		if( empty( $this->_name ) ) {
			unset( $this->_id );
			$this->_name = 'Fehler';
		} else {
			$userSettings = MT_Functions::getUserSettings( $_GET['sort'], $_GET['num'] );
			$this->_userNum = $userSettings['num'];
			$this->_userSort = $userSettings['sort'];

			// Seitennummer
			$this->_userPage = $_GET['page'];

				// Anzahl der Seiten in dieser Galerie unter Berücksichtigung der Anzahl der Bilder
				if( empty( $this->_userPage ) || $this->_userPage > $this->photo->getNumPages($this->_id, $this->_userNum) ) {
					$this->_userPage = 1;
				}

			// Anzahl der Bilder in der Galerie
			$this->_numPhotos = $this->photo->getCount($this->_id);
		}
	}


//	public function outputTitle()
//	{
//		echo $this->_name;
//	}
//
//        public function outputDescription()
//	{
//		echo "Fotogalerie " . $this->_name . " in der Kategorie " . $this->_categoryName;
//	}

	public function checkWidescreen() {
		return !empty( $this->_numPhotos );
	}


	public function outputBreadcrumb() {
		if( isset($this->_id) ) {
			$categoryLink = $this->_categoryPath . $this->_categoryId;
				echo '
                                    <div itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
                                        <a href="' . $categoryLink . '" itemprop="url"><span itemprop="title">' . $this->_categoryName . '</span></a>&nbsp;>'
                                     . MT_Functions::getIfNotEmpty( $this->_subcategoryName, '<a href="' . $categoryLink . '" itemprop="url"><span itemprop="title">' . $this->_subcategoryName . '</span></a>&nbsp;>' ) . '
                                        <a href="" itemprop="url"><span itemprop="title">' . $this->_name . '</span></a>
                                    </div>';
		}
	}


	public function outputContent() {
		$this->outputBreadcrumb();
		echo '<h2>' . $this->_name . '</h2>';

		if( isset( $this->_id ) ) {

			// ggf. Galeriebeschreibung
			if( !empty( $this->_description ) ) {
				echo '
				<p>' . $this->_description . '</p>';
			}
			
			if( !empty( $this->_numPhotos) ) {
				$this->_outputContentHeader();
				$this->_outputContentPhotos();
				$this->_outputContentFooter();
			} else {
				// Falls sich in der Galerie noch keine Bilder befinden
				?>
			<p align="center"><img src="/design/images/baustelle.gif"></p>
			<p>In dieser Galerie befinden sich noch keine Bilder! Schau später noch einmal vorbei!</p>
			<p>Zurück zur Übersicht: <a href="../<?php echo getIfNotEmpty( $this->_subcategoryName, '../'); ?>"><?php echo $this->_categoryName; ?></a></p>
				<?php
			}
		} else {
			// Ausgabe der Fehlermeldung
			echo '
			<p>Die ausgewählte Galerie exestiert nicht!</p>';
		}
	}


	/**
	 * Output Auswahlleiste and pagination (Form: div, table)
	 *
	 * @return void
	 */
	private function _outputContentHeader()
	{
		// Auswahlleiste
		?>
			<div id="auswahl_leiste">
				<form action="" method="get">
					<table width="100%" cellSpacing="0" cellPadding="2">
						<tr>
							<th>&nbsp;<?php echo _("Bilder"); ?>:&nbsp;<?php echo $this->_numPhotos; ?></th>
							<td>
								<!--<input name="id" value="<?php //echo $this->path; ?>" type="hidden">-->
								<input name="page" value="<?php echo $this->_userPage; ?>" type="hidden">
								<?php echo _("Bilder pro Seite"); ?>:&nbsp;
								<select name="num" size="1">
									<option value="5" <?php MT_Functions::selected( $this->_userNum, '5' ); ?>>5</option>
									<option value="10" <?php MT_Functions::selected( $this->_userNum, '10' ); ?>>10</option>
									<option value="15" <?php MT_Functions::selected( $this->_userNum, '15' ); ?>>15</option>
								</select>
								&nbsp;<?php echo _("Sortieren nach"); ?>:&nbsp;
								<select name="sort" size="1">
									<option value="date" <?php MT_Functions::selected( $this->_userSort, 'date'); ?>><?php echo _("Einstellungsdatum"); ?>: <?php echo _("Neu - Alt"); ?></option>
									<option value="-date" <?php MT_Functions::selected( $this->_userSort, '-date'); ?>><?php echo _("Einstellungsdatum"); ?>: <?php echo _("Alt - Neu"); ?></option>
								</select>
								<input type="submit" value="OK" class="button">
							</td>
						</tr>
					</table>
				</form>
			</div>
		<?php

		MT_Functions::__outputPagination( $this->_id, $this->_userPage, $this->_userNum, $this->_userSort);
	}


	/**
	 * Output galleries photos
	 *
	 * @return void
	 */
	private function _outputContentPhotos() {
		$query = (new MT_QueryBuilder('wp_mt_'))
			->from('photo', array('id as photoId', 'path', 'description', 'date'))
			->joinInner('photographer', TRUE, array('id as photographerId', 'name as photographerName'))
			->whereEqual('gallery', $this->_id)
			->whereEqual('`show`', '1')
			->orderBy('date');
				
		// Sortierung der Bild nach dem Datum
		if( $this->_userSort === 'date' ) {
			$query->orderBy('date DESC');
		}
				
		// LIMIT: Zeige Bilder von, Anzahl Bilder
		$query->limit($this->_userNum, ( $this->_userPage - 1 ) * $this->_userNum);
		foreach ($query->getResult() as $row) {

            $alt = $this->_name . ' (' . $this->_categoryName . '): ' . $row->description; // photo's alternate text
			$this->_outputPhoto( $row->path,
						$this->__getPhotoKeywords($alt),
						$alt,
						$row->description,
						$row->date,
						$row->photographerId,
						$row->photographerName
			);
		}
	}


	/**
	 * Ouput photo (Form: paragraph)
	 *
	 * @param	string $path             	Photo's path
	 * @param	string $keywords              	Photo's keywords as string
	 * @param	string $alt              	Photo's alternate text
	 * @param	string $description      	Photo's description
	 * @param	string $date             	Photo's date as timestamp
	 * @param	string $photographerId   	Photographer's id
	 * @param	string $photographerName	Photographer's name
	 * @return	void
	 */
	private function _outputPhoto( $path, $keywords, $alt, $description, $date, $photographerId, $photographerName ) {
		$schemaDateFormat   = 'Y-m-d';
		$mtDateFormat       = 'd.m.Y - H:i:s';

		echo '
                        <div class="photo" itemscope itemtype="http://schema.org/ImageObject">
<!--                        <span itemprob="publisher">MiRo\'s Truckstop</span>-->
                            <span itemprop="keywords">' . $keywords . '</span>
			    <p><img alt="' . $alt . '" src="/bilder/' . $path . '" itemprop="contentURL"><br>
			    <b>' . _("Fotograf") . ':</b>&nbsp;<a href="/artikel/Fotograf/' . $photographerId . '" rel="author"><span itemprop="author" itemp>' . $photographerName . '</span></a>&nbsp;|&nbsp;
                            <b>' . _("Eingestellt am") . ':</b>&nbsp;<meta itemprop="datePublished" content="' . date( $schemaDateFormat, $date ) . '">' . date( $mtDateFormat, $date ) . '</p>
			    <p><span itemprop="description">' . $description . '</span></p>
                        </div>';
	}
        
        /**
         * Removes special chars etc and return a clear keyword string
         * 
         * @param   string $keywordsString  String with keywords
         * @return  string                  Keyword string
         */
        private function __getPhotoKeywords( $keywordsString ) {
                $remove = array('& ', '(', ')', ':', '"', 'in ');
                return str_replace($remove, '', $keywordsString);
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
						<td><?php MT_Functions::__outputPagination( $this->_id, $this->_userPage, $this->_userNum, $this->_userSort ); ?></td>
						<td><span class="nach_oben"><a href="javascript:self.scrollTo(0,0)"><?php echo _("Nach oben"); ?></a></span></td>
					</tr>
		<?php
		// Verlinkung der Galerie mit dem Hauptparkplatz
		if( !empty( $this->_hauptparkplatz ) ) {
			?>
					<tr>
						<td colspan="3"><center><a href="http://rosensturm.de/<?php echo $this->_hauptparkplatz; ?>.html" target="_blank"><?php $this->_name; ?> auf dem Hauptparkplatz</a></center></td>
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