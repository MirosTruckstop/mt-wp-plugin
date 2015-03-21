<?php

class MT_View_Photographer {

	/**
	 * Date format
	 *
	 * @var string
	 */
	public static $_dateFormat = '%e. %B %Y';
	
	private $_item;

	/**
	 * [...]
	 *
	 * @param	string	$path	Photographer's id
	 * [...]
	 */
	public function __construct($id) {
		$photographer = new MT_Photographer($id);
		$this->_item = $photographer->getOne(NULL, 'OBJECT');
		var_dump($this->_item);				
		// Couldn't find photographer in database
		if( empty( $this->_item->name ) ) {
			unset( $this->_item );
			$this->_name = 'Fehler';
		} else {
			$this->_name = $this->_item->name;
			$photo = new MT_Photo();
			$this->_numPhotos = $photo->getNumPhotos($photographer->getId());
		}
	}

//	public function outputTitle()
//	{
//		echo $this->_name;
//	}
//        
//	public function outputDescription()
//	{
//		echo _("Übersicht über den Fotografen") . " " . $this->_name;
//	}

	public function outputBreadcrumb() {
		?>
				<a href="../Fotografen">Fotografen</a>&nbsp;>
				<a href=""><?php echo $this->_name; ?></a>
		<?php
	}

	public function outputContent(){
		$this->outputBreadcrumb();
		echo '<h2>'.$this->_name.'</h1>';
		if( isset( $this->_item ) ) {
			$this->_outputContentPhotographer($this->_item);
			$this->_outputContentPhotographerPhotos($this->_item);
		} else {
			// Ausgabe der Fehlermeldung
			echo "<p>" . _("Die ausgewählte Fotograf existiert nicht!"). "</p>";
		}
	}

	/**
	 * Outputs information about the photographer (Form: table)
	 *
	 * @return void
	 */
	private function _outputContentPhotographer($item)
	{
		?>
			<table class="table_quer">
			 <tr>
			  <th>Name:</th>
			  <td><?php echo $item->name; ?></td>
			 </tr>
			 <tr>
			  <th>Truckstop-Fotograf seit:</th>
			  <td><?php echo strftime(self::$_dateFormat, $item->date ); ?></td>
			 </tr>
		 		<?php
			 	if( !empty( $item->amera ) ) {
					?>
			 <tr>
			  <th>Kamera:</th>
			  <td><?php echo $item->camera; ?></td>
			 </tr>
			 		<?php
			 	}
			 	?>
			 <tr>
			  <th>Anzahl der Fotos:</th>
			  <td><?php echo $this->_numPhotos; ?></td>
			 </tr>
			</table>
		<?php
	}

	/**
	 * Outputs information about photographer's photos (Form: table)
	 *
	 * @return void
	 */
	private function _outputContentPhotographerPhotos($item) {
		if( !empty( $this->_numPhotos ) ) {
			?>
			<h2>Bilder</h2>
			<table class="table_hoch_2">
			 <tr>
			  <th>Galerie</th>
			  <th>Anzahl der Fotos</th>
			 </tr>
			<?php
			$tempCategoryID = 0;		// Save last category ID
			$tempSubcategoryID = 0;		// Save last subcategory ID

			$query = (new MT_QueryBuilder('wp_mt_'))
				->from('photo')
				->select('COUNT(wp_mt_photo.id) as numPhotos')
				->joinInner('gallery', TRUE, array('id AS galleryId', 'name as galleryName'))
				->joinInner('category', 'wp_mt_category.id = wp_mt_gallery.category', array('id AS categoryId', 'name AS categoryName'))
				->joinLeftOuter('subcategory', 'wp_mt_subcategory.id = wp_mt_gallery.subcategory', array('id AS subcategoryId', 'name subcategoryName'))
				->whereEqual('wp_mt_photo.show', 1)
				->whereEqual('photographer', $item->id)
				->groupBy(array('categoryName', 'subcategoryName', 'galleryName'))
				->orderBy(array('categoryName', 'subcategoryName', 'galleryName'));
			foreach ($query->getResult() as $row) {	
					
				// Bereich
				if( $row->categoryId != $tempCategoryID ) {
					$tempCategoryID = $row->categoryId;
					?>
			 <tr>
			  <td><u><?php echo _($row->categoryName); ?></u></td>
			  <td></td>
			 </tr>
					<?php
				}

				// Kategorie
				if( $row->subcategoryId != $tempSubcategoryID ) {
					$tempSubcategoryID = $row->subcategoryId;
					?>
			 <tr>
			  <td>&nbsp;&nbsp;»&nbsp;&nbsp;<?php echo $row->subcategoryName; ?></td>
			  <td></td>
			 </tr>
	 				<?php
				}
						
				// Galerie
				?>
			 <tr>
				 <td>&nbsp;&nbsp;&nbsp;&nbsp;»&nbsp;&nbsp;<a href="<?php echo MT_Photo::$__photoPathAbs; ?><?php echo $row->galleryId; ?>"><?php echo $row->galleryName; ?></a></td>
			  <td><?php echo $row->numPhotos; ?></td>
			 </tr>
	 			<?php
				}
		echo "
			</table>";
		}
	}
}
?>