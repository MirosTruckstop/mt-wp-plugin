<?php

class MT_View_Photographer implements MT_View_ICommon {

	/**
	 * Date format
	 *
	 * @var string
	 */
	public static $_dateFormat = '%e. %B %Y';
	
	private $item;

	/**
	 * [...]
	 *
	 * @param	string	$path	Photographer's id
	 * [...]
	 */
	public function __construct($id) {
		$this->item = (new MT_Photographer($id))->getOne();
		
		if (empty($this->item)) {
			throw new Exception('Die ausgewählte Fotograf existiert nicht.');
		}

		$this->_numPhotos = MT_Photo::getNumPhotos($this->item->id);
	}

	public function outputTitle() {
		echo $this->item->name;
	}
        
	public function outputDescription() {
		echo "Übersicht über den Fotografen " . $this->outputTitle();
	}

	public function outputBreadcrumb() {
		?>
				<a href="../fotografen">Fotografen</a>&nbsp;>
				<a href=""><?php echo $this->outputTitle(); ?></a>
		<?php
	}

	public function outputContent(){
		$this->_outputContentPhotographer();
		$this->_outputContentPhotographerPhotos();
	}

	/**
	 * Outputs information about the photographer (Form: table)
	 *
	 * @return void
	 */
	private function _outputContentPhotographer() {
		?>
			<table class="table_quer">
			 <tr>
			  <th>Name:</th>
			  <td><?php echo $this->item->name; ?></td>
			 </tr>
			 <tr>
			  <th>Truckstop-Fotograf seit:</th>
			  <td><?php echo strftime(self::$_dateFormat, $this->item->date ); ?></td>
			 </tr>
		 		<?php
			 	if( !empty( $this->item->camera ) ) {
					?>
			 <tr>
			  <th>Kamera:</th>
			  <td><?php echo $this->item->camera; ?></td>
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
	private function _outputContentPhotographerPhotos() {
		if($this->_numPhotos > 0) {
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

			$query = (new MT_QueryBuilder())
				->from('photo')
				->select('COUNT(wp_mt_photo.id) as numPhotos')
				->joinInner('gallery', TRUE, array('id AS galleryId', 'name as galleryName'))
				->joinInner('category', 'wp_mt_category.id = wp_mt_gallery.category', array('id AS categoryId', 'name AS categoryName'))
				->joinLeftOuter('subcategory', 'wp_mt_subcategory.id = wp_mt_gallery.subcategory', array('id AS subcategoryId', 'name subcategoryName'))
				->whereEqual('wp_mt_photo.show', 1)
				->whereEqual('photographer', $this->item->id)
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