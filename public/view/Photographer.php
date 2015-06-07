<?php

class MT_View_Photographer extends MT_View_Common {

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
			throw new Exception(__('Die ausgewählte Fotograf existiert nicht.', MT_NAME));
		}

		$this->_numPhotos = MT_Photo::getNumPhotos($this->item->id);
		parent::setTitle($this->item->name);
		parent::setDescription(__('Übersicht über den Fotografen', MT_NAME).' '.$this->item->name);
		parent::setBreadcrumb(array(
			'../fotografen' => __('Fotografen', MT_NAME),
			'' => $this->item->name
		));
	}

	public function outputContent(){
		$this->_outputContentPhotographer();
		if($this->_numPhotos > 0) {
			$this->_outputContentPhotographerPhotos();
		}
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
			  <th><?php _e('Truckstop-Fotograf seit', MT_NAME); ?>:</th>
			  <td><?php echo strftime(self::$_dateFormat, $this->item->date ); ?></td>
			 </tr>
			 <tr>
			  <th><?php _e('Anzahl der Fotos', MT_NAME); ?>:</th>
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
		?>
			<h2><?php _e('Bilder', MT_NAME); ?></h2>
			<table class="table_hoch_2">
			 <tr>
			  <th><?php _e('Galerie', MT_NAME); ?></th>
			  <th><?php _e('Anzahl der Fotos', MT_NAME); ?></th>
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
					
			// Category
			if( $row->categoryId != $tempCategoryID ) {
				$tempCategoryID = $row->categoryId;
			?>
			 <tr>
			  <td><u><?php echo _($row->categoryName); ?></u></td>
			  <td></td>
			 </tr>
			<?php
			}

			// Subcategory
			if( $row->subcategoryId != $tempSubcategoryID ) {
				$tempSubcategoryID = $row->subcategoryId;
				?>
			 <tr>
			  <td>&nbsp;&nbsp;»&nbsp;&nbsp;<?php echo $row->subcategoryName; ?></td>
			  <td></td>
			 </tr>
 				<?php
			}
						
			// Gallery
			?>
			 <tr>
				 <td>&nbsp;&nbsp;&nbsp;&nbsp;»&nbsp;&nbsp;<a href="<?php echo MT_Photo::$__photoPathAbs; ?><?php echo $row->galleryId; ?>"><?php echo $row->galleryName; ?></a></td>
			  <td><?php echo $row->numPhotos; ?></td>
			 </tr>
 			<?php
			}
		?>
			</table>
		<?php
	}
}
?>