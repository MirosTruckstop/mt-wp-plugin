<?php

class MT_QueryBuilder {
	
	private $tablePraefix;
	private $tableName;
	private $select;
	private $join = '';
	private $groupBy = '';
	private $orderBy = '';
	private $limit = '';
	
	public function __construct($tablePraefix = 'wp_mt_') {
		$this->tablePraefix = $tablePraefix;
		return $this;
	}
	
	public function from($tableName, $select = NULL) {
		$this->tableName = $this->tablePraefix.$tableName;
		$this->addToSelect($tableName, $select);
		return $this;
	}
	
	/**
	 * 
	 * @param type $type
	 * @param string|true $joinTable
	 * @param string $joinCondition
	 * @param type $joinSelect
	 * @return \MT_QueryBuilder
	 */
	private function _generalJoin($type, $joinTable, $joinCondition, $joinSelect = NULL) {
		if (!empty($joinTable) && !empty($joinCondition)) {
			if ($joinCondition === TRUE) {
				$joinCondition = $this->tableName.'.'.$joinTable.'='.$this->tablePraefix.$joinTable.'.id';
			}
			$this->addToJoin($type, $joinTable, $joinCondition);
			$this->addToSelect($joinTable, $joinSelect);
		}
		return $this;
	}
	
	public function join($joinTable, $joinCondition, $joinSelect = NULL) {
		return $this->_generalJoin('JOIN', $joinTable, $joinCondition, $joinSelect);
	}
	
	public function joinLeft($joinTable, $joinCondition, $joinSelect = NULL) {
		return $this->_generalJoin('LEFT JOIN', $joinTable, $joinCondition, $joinSelect);
	}
	
	public function joinInner($joinTable, $joinCondition, $joinSelect = NULL) {
		return $this->_generalJoin('INNER JOIN', $joinTable, $joinCondition, $joinSelect);		
	}
	
	public function joinLeftOuter($joinTable, $joinCondition, $joinSelect = NULL) {
		return $this->_generalJoin('LEFT OUTER JOIN', $joinTable, $joinCondition, $joinSelect);				
	}
	
	public function select($select) {
		if (!empty($select)) {
			if (!empty($this->select)) {
				$this->select .= ', ';
			}
			$this->select .= $select;
		}
		return $this;
	}

	
	public function where($condition) {
		if (!empty($condition)) {
			if (empty($this->where)) {
				$this->where .= 'WHERE';
			} else {
				$this->where .= ' AND';
			}
			$this->where .= ' '.$condition;
		}
		return $this;
	}
	
	public function whereEqual($first, $second) {
		$this->where($first.' = ' . $second);
		return $this;
	}
	
	public function groupBy($groupBy) {
		if(!empty($groupBy)) {
			if (is_array($groupBy)) {
				$groupBy = implode(',', $groupBy);
			}			
			$this->groupBy = ' GROUP BY '.$groupBy;
		}
		return $this;
	}
	
	public function orderBy($orderBy) {
		if(!empty($orderBy)) {
			if (is_array($orderBy)) {
				$orderBy = implode(',', $orderBy);
			}
			$this->orderBy = ' ORDER BY '.$orderBy;
		}
		return $this;
	}	
	
	public function limit($amount, $offset = NULL) {
		if(!empty($offset) && !empty($amount)) {
//			$this->limit = ' LIMIT '.$offset.', '.$amount;
			$this->limit = ' LIMIT '.$offset.', '.$amount;
		}
		else if(!empty($amount)) {
			$this->limit = ' LIMIT '.$amount;
		}
		return $this;
	}
	
	public function limitPage($page, $amount) {
		if (!empty($page)) {
			$this->limit($amount, ($page-1) * $amount);
		}
		return $this;
	}
	
	public function __toString() {
		return $this->get();
	}
	
	public function get() {
		if (empty($this->select)) {
			$this->select = '*';
		}
		return '
			SELECT '.$this->select.'
			FROM '.$this->tableName.' '
			.$this->join.' '
			.$this->where.' '
			.$this->groupBy. ' '
			.$this->orderBy.' '
			.$this->limit;
	}
		
	private function addToJoin($type, $joinTable, $joinCondition) {
		$this->join .= ' '.$type.' '.$this->tablePraefix.$joinTable.'
				ON '.$joinCondition;
	}
	
	private function addToSelect($table, $select) {
		if (!empty($select)) {
			if (is_array($select)) {
				$this->select($this->getSelectStringFromArray($table, $select));
			} else if (is_string($select)) {
				$this->select($this->tablePraefix.$table.'.'.$select);
			}			
		}
	}	
	
	private function getSelectStringFromArray($table, $selectArray) {
		// Add table name as päfix
		$selectArray = preg_filter('/^/', $this->tablePraefix.$table . '.', $selectArray);
		// Create a string
		return implode(',', $selectArray);
	}
	
	public function getResult($output_type = 'OBJECT') {
		global $wpdb;
		return $wpdb->get_results($this->get(), $output_type);
	}
	
	public function getResultOne($output_type = 'OBJECT') {
		global $wpdb;
		return $wpdb->get_row($this->get(), $output_type);
	}
}
