<?php

abstract class MT_Admin_Table_Common {

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
	private $title;
	protected $query;

	public function __construct($model, $cssClass = 'widefat') {
		if ($model instanceof MT_Common) {
			$this->model = $model;
			$this->cssClass = $cssClass;
			
			// Pagination
			$this->page = intval($_GET['page']);
			if( empty( $this->page ) ) {
				$this->page = 1;
			}
		}
		else {
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
		$this->fields = $fields;
	}
	
	protected abstract function outputHeadMessages();
	protected abstract function _outputTableBody();
	protected abstract function _outputTableNavBottom();

	public function outputContent() {
		?>
		<div class="wrap">
			<h2><?php echo $this->title; ?></h2>
			<div class="tablenav top">
				<?php $this->outputHeadMessages() ?>
			</div>
			<form name="<?php echo $this->model->name(); ?>" action="" method="post">
				<table class="<?php echo $this->cssClass; ?>">
					<thead>
						<?php $this->_outputTableHead(); ?>
					</thead>
					<tbody <?php echo ($this->model->name() == 'photo' ? 'class="sort"' : '') ;?>>
						<?php $this->_outputTableBody(); ?>
					</tbody>
					<tfoot>
						<?php $this->_outputTableHead(); ?>
					</tfoot>
				</table>
			</form>
			<div class="tablenav bottom">
				<?php $this->_outputTableNavBottom(); ?>
			</div>			
		</div>
<?php
	}
	
	protected function update($data, array $conditionValue = NULL) {
		if($this->model->update($data, $conditionValue) ) {
			MT_Functions::box( 'save' );
		} else {
			MT_Functions::box( 'exception', 'TODO: Fehler beim Aktu');
		}
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
		return $query->getResult('ARRAY_N');
	}

}

