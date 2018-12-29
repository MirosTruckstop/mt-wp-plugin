<?php
/**
 * Common admin view.
 * 
 * @package back-end
 * @subpackage view
 */
abstract class MT_Admin_View_Common {

	/**
	 * Typ name (e.g. 'photographer')
	 *
	 * @var string
	 */
	protected $model;
	
	/**
	 * Tables css class
	 *
	 * @var string
	 */
	protected $cssClass;
		
	protected $perPage;
	
	protected $order;
	protected $fields;
	protected $page;
	protected $query;
	
	private $title;
	private $isSortActive = FALSE;
	private $pagination;

	public function __construct($model, $page = NULL, $cssClass = 'widefat') {
		if ($model instanceof MT_Common) {
			$this->model = $model;
			$this->page = $page;
			$this->cssClass = $cssClass;
		} else {
			throw new Exception("\$model is not of type MT_Common", NULL, NULL);
		}
	}
	
	public function setTitle($value) {
		$this->title = $value;
	}
	
	public function setQuery($value) {
		if ($value instanceof MT_QueryBuilder) {
			$this->query = $value;
		}
	}

	public function setPerPage($value) {
		$this->perPage = $value;
	}
	
	public function setOrder( $value ) {
		$this->order = $value;
	}

	public function setFields($fields) {
		array_unshift($fields, (new MT_Admin_Field('id', '#', 'hidden')));
		$this->fields = $fields;
	}
	
	public function setSortIsActive() {
		$this->isSortActive = TRUE;
	}
	
	protected abstract function outputHeadMessages();
	protected abstract function _outputTableBody();
	protected abstract function _outputTableNavBottom();

	public function outputContent() {
		?>
		<div class="wrap">
			<h2><?php echo $this->title; ?></h2>
			<?php echo $this->outputHeadMessages(); ?>
			<?php echo $this->getPagination(); ?>
			<!--<div class="tablenav top"></div>-->
			<form name="<?php echo $this->model->name(); ?>" action="" method="post">
				<table class="<?php echo $this->cssClass; ?>">
					<thead>
						<?php $this->_outputTableHead(); ?>
					</thead>
					<tbody <?php echo ($this->isSortActive ? 'class="sort"' : '') ;?>>
						<?php $this->_outputTableBody(); ?>
					</tbody>
					<tfoot>
						<?php $this->_outputTableHead(); ?>
					</tfoot>
				</table>
			<div class="tablenav bottom">
				<?php echo $this->getPagination(); ?>
				<?php $this->_outputTableNavBottom(); ?>
			</div>
			</form>
		</div>
<?php
	}
	
	/**
	 * 
	 * @param array $data
	 * @return boolean True, if insert/update was successful
	 * @throws Exception If inser/update failed
	 */
	protected function updateOrInsertAll($data) {
		foreach ($data as $item) {
			$id = $item['id'];
			unset($item['id']);
			if ($id === '') {
				if (!$this->model->insert($item)) {
					throw new Exception('Einfügen des Objekts fehlgeschlagen:'.$this->model);
				}
			}
			// If item has an ID, update the item
			else {
				if (!$this->model->update($item, array('id' => $id))) {
					throw new Exception('Aktualisieren des Objekts fehlgeschlafen: id='.$id);
				}
			}
		}
		return TRUE;
	}

	/**
	 * Output table head row (Form: tr, td)
	 *
	 * @return void
	 */
	protected function _outputTableHead() {
		echo '<tr>';
		for($i = 0; $i < count($this->fields); $i++) {
			$field = $this->fields[$i];
			if ($field->label == '#') {
				echo '<th class="check-column"><input type="checkbox" name="checked" value="all"></th>';
			} else {
				echo '<th>'.$field->label.'</th>';
			}
		}
		echo '</tr>';
	}

	protected function getResult() {
		if (!empty($this->query)) {
			return $this->query->getResult();
		}

		$select = array();
		$leftJoin = array();
		foreach ($this->fields as $field) {
			$reference = $field->getReference();
			if ($reference) {
				if (!is_array($leftJoin[$reference])) {
					$leftJoin[$reference] = array();
				}
				array_push($leftJoin[$reference], $field->referencedField . ' AS '.$reference);
			} else {
				array_push($select, $field->name);
			}
		}
		
		$query = (new MT_QueryBuilder())
			->from($this->model->name(), $select)
			->orderBy($this->order)
			->limit($this->perPage);
		if ($this->model->hasId()) {
			$query->where('wp_mt_'.$this->model->name().'.id ='.$this->model->getId());
		}
		foreach ($leftJoin as $joinTable => $joinSelect) {
			$query->joinLeft($joinTable, 'wp_mt_'.$this->model->name().'.'.$joinTable.'=wp_mt_'.$joinTable.'.id', $joinSelect);
		}
		return $query->getResult('ARRAY_A');
	}
	
	private function getPagination() {
		if (isset($this->page) && !isset($this->pagination)) {
			$this->pagination = static::getPagination();
		}
		return $this->pagination;
	}

}

