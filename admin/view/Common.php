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

	public function __construct( $model, $cssClass ) {
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

	public function setPerPage( $value ) {
		$this->perPage = $value;
	}
	
	public function setOrder( $value ) {
		$this->order = $value;
	}

	public function setFields($fields) {
		$this->fields = $fields;
	}
	
	/**
	 * Output table
	 *
	 * @return void
	 */	
	protected function _outputForm() {
		?>
			<form name="<?php echo $this->model; ?>" action="" method="post">
				<input type="hidden" name="id" value="<?php //echo $this->model->getId(); ?>">
				<table class="<?php echo $this->cssClass; ?>">
					<thead>
						<?php $this->_outputTableHead(); ?>
					</thead>
					<tbody>
						<?php $this->_outputTableBody(); ?>
					</tbody>
					<tfoot>
						<?php $this->_outputTableHead(); ?>
					</tfoot>
				</table>
				<div class="tablenav bottom">
					<?php $this->_outputTableNavBottom(); ?>
				</div>
			</form>
		<?php
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
	
	protected function _outputTableNavBottom() {
		return '';
	}

	protected function getResult() {
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
		
		$query = (new MT_QueryBuilder('wp_mt_'))
			->from($this->model->__toString(), $select)
			->orderBy($this->order)
			->limit($this->perPage);
		if ($this->model->hasId()) {
			$query->where('wp_mt_'.$this->model->__toString().'.id ='.$this->model->getId());
		}
		foreach ($leftJoin as $joinTable => $joinSelect) {
			$query->joinLeft($joinTable, 'wp_mt_'.$this->model->__toString().'.'.$joinTable.'=wp_mt_'.$joinTable.'.id', $joinSelect);
		}
		return $query->getResult('ARRAY_N');
	}

}

