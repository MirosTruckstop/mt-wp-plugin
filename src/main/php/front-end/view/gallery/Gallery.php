<?php
namespace MT\WP\Plugin\Frontend\View\Gallery;

use \Exception as Exception;
use MT\WP\Plugin\Common\MT_QueryBuilder;
use MT\WP\Plugin\Api\MT_Category;
use MT\WP\Plugin\Api\MT_Photo;
use MT\WP\Plugin\Common\Util\MT_Util_Common;
use MT\WP\Plugin\Common\Util\MT_Util_Html;

class MT_View_Gallery extends MT_View_AbstractGallery
{

	private $item;
	private $userSettings;
	
	/**
	 * Number of photos in gallery
	 *
	 * @var int
	 */
	private $_numPhotos;

	public function __construct($id, $page, $num, $sort)
	{

		// Construct query
		$query = (new MT_QueryBuilder())
			->from('gallery', array('id as galleryId', 'name as galleryName', 'description'))
			->join('category', true, array('id AS categoryId', 'name AS categoryName'))
			->joinLeft('subcategory', true, 'name as subcategoryName')
			->whereEqual('wp_mt_gallery.id', $id);
		$this->item = $query->getResultOne();
				
		if (empty($this->item)) {
			throw new Exception('Die ausgewählte Galerie exestiert nicht.');
		}

		// Anzahl der Bilder in der Galerie
		$this->_numPhotos = MT_Photo::getCount($this->item->galleryId);
		if ($this->_numPhotos == 0) {
			throw new Exception('Die Galerie hat keine Fotos');
		}

		$this->userSettings = MT_Util_Common::getUserSettings($sort, $num, $page);
		// If page parameter is greater then the maximum
		if ($num && $page > 1 && $page > ceil($this->_numPhotos / $num)) {
			throw new Exception('Die Galerie '.$this->item->galleryId.' besitzt keine Seite '.$page);
		}

		parent::setTitle($this->item->galleryName);
		parent::setDescription('Fotogalerie ' . $this->item->galleryName . ' in der Kategorie ' . $this->item->categoryName);
		$this->_createPagination();
		$this->_createBreadcrumb();
	}
	
	private function _createPagination()
	{
		$url = explode(',', $_SERVER['REQUEST_URI']);
		$this->pagination = MT_Util_Html::__outputPagination($this->_numPhotos, $this->userSettings['page'], $this->userSettings['num'], $this->userSettings['sort'], $url[0].',');
	}
	
	private function _createBreadcrumb()
	{
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

	public function outputContent()
	{
		$query = (new MT_QueryBuilder())
			->from('photo', array('id as photoId', 'path', 'description', 'date'))
			->joinLeft('photographer', true, array('id as photographerId', 'name as photographerName'))
			->whereEqual('gallery', $this->item->galleryId)
			->whereEqual('`show`', '1')
			->orderBy('date')
			->limitPage($this->userSettings['page'], $this->userSettings['num']);

		// Sortierung der Bild nach dem Datum
		if ($this->userSettings['sort'] === 'date') {
			$query->orderBy('date DESC');
		}

		// ggf. Galeriebeschreibung
		if (!empty($this->item->description)) {
			echo '<p>' . $this->item->description . '</p>';
		}

		$this->_outputContentHeader();
		$this->_outputContentPhotos($query->getResult(), $this->item->galleryName.' (' . $this->item->categoryName . ')', $this->userSettings['num'] >= 200);
		$this->_outputContentFooter();
	}


	/**
	 * Output Auswahlleiste and pagination (Form: div, table)
	 *
	 * @return void
	 */
	private function _outputContentHeader()
	{
		$location = "location = '".$this->item->galleryId.",page=".$this->userSettings["page"]."&'+this.options[this.selectedIndex].value;";
		$locationPage1 = "location = '".$this->item->galleryId.",page=1&'+this.options[this.selectedIndex].value;";
		?>
			<div id="auswahl_leiste">
				<table width="100%" cellSpacing="0" cellPadding="2">
					<tr>
						<th class="screen-small-hide">&nbsp;<?php _e('Bilder', MT_NAME); ?>:&nbsp;<?php echo $this->_numPhotos; ?></th>
						<td>
							<label for="num"><?php _e('Bilder pro Seite', MT_NAME); ?></label>:&nbsp;
							<select name="num" size="1" onchange="<?php echo $locationPage1; ?>">
								<option value="num=5&sort=<?php echo $this->userSettings['sort']; ?>" <?php echo MT_Util_Html::selected($this->userSettings['num'], '5'); ?>>5</option>
								<option value="num=10&sort=<?php echo $this->userSettings['sort']; ?>" <?php echo MT_Util_Html::selected($this->userSettings['num'], '10'); ?>>10</option>
								<option value="num=15&sort=<?php echo $this->userSettings['sort']; ?>" <?php echo MT_Util_Html::selected($this->userSettings['num'], '15'); ?>>15</option>
								<option value="num=200&sort=<?php echo $this->userSettings['sort']; ?>" <?php echo MT_Util_Html::selected($this->userSettings['num'], '200'); ?>>200</option>								
							</select>
							&nbsp;<span class="screen-small-hide"><label for="sort"><?php _e('Sortierung', MT_NAME); ?></label>:&nbsp;</span>
							<select name="sort" size="1" onchange="<?php echo $location; ?>">
								<option value="num=<?php echo $this->userSettings['num']; ?>&sort=date" <?php echo MT_Util_Html::selected($this->userSettings['sort'], 'date'); ?>><?php _e('Neuste zuerst', MT_NAME); ?></option>
								<option value="num=<?php echo $this->userSettings['num']; ?>&sort=-date" <?php echo MT_Util_Html::selected($this->userSettings['sort'], '-date'); ?>><?php _e('Älteste zuerst', MT_NAME); ?></option>
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
	private function _outputContentFooter()
	{
		 echo $this->pagination;
	}
}
