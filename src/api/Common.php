<?php
/**
 * Common model
 * 
 * @package api
 * @subpackage public
 */
abstract class MT_Common {

	protected $id;
	public static $dbPreafix = 'wp_mt_';

	public abstract static function name();

	/**
	 * Constructor for the database class to inject the table name
	 *
	 * @param String $tableName - The current table name
	 */
	public function __construct($id) {
		$this->id = $id;
	}
	
	private static function getTableName() {
		return self::$dbPreafix.static::name();
	}
	
	/**
	 * Insert data into the current data
	 *
	 * @param  array  $data - Data to enter into the database table
	 *
	 * @return int|bool InsertQuery ID or false
	 */
	public static function insert(array $data) {
		if(empty($data)) {
			return FALSE;
		}

		global $wpdb;
		$wpdb->insert(self::getTableName(), $data);
		return $wpdb->insert_id;
	}
	
	public function isDeletable() {
		return false;
	}
	
	/**
	 * @deprecated since version 1.0
	 */
	public function getOne($select = NULL, $outputType = 'OBJECT') {
		if(!empty($this->id)) {
			$query = (new MT_QueryBuilder())
				->from(static::name(), $select)
				->whereEqual(self::getTableName().'.id', $this->id);
			return $query->getResultOne($outputType);		
		}
		return FALSE;
	}
	
	/**
	 * @deprecated since version 1.0
	 */
	public static function getAll($select = '*', $orderBy = NULL, $limit = Null) {
		$query = (new MT_QueryBuilder())
			->from(static::name(), $select)
			->orderBy($orderBy)
			->limit($limit);
		return $query->getResult();
	}

	private static function _get_one_value($select, $whereCondition = NULL) {
		$query = (new MT_QueryBuilder())
			->from(static::name())
			->select($select)
			->where($whereCondition);
		$result = $query->getResult('ARRAY_N');
		$value = $result[0][0];
		if (!empty($value)) {
			return $value;
		} else {
			return FALSE;
		}
	}
		
	/**
	 * 
	 * @param string $aggregateFunctionName Aggregate function, e.g. 'MAX', 'AVG'
	 * @param string $columnName Name of the column
	 * @return integer Results of aggregate
	 */
	public static function get_aggregate($aggregateFunctionName, $columnName, $whereCondition = NULL) {
		$aggregateValue = self::_get_one_value($aggregateFunctionName.'('.$columnName.')', $whereCondition);
		if($aggregateValue) {
			return $aggregateValue;
		} else {
			return 0;
		}
	}
	
	/**
	 * 
	 * @param string $columnName Name of the column
	 * @param null|string $whereCondition
	 * @return string Attribute
	 * @deprecated since version 1.0
	 */
	public function get_attribute($columnName, $whereCondition = NULL) {
		if (empty($whereCondition) && $this->hasId()) {
			return self::_get_one_value($columnName, 'id=' . $this->id);
		}
		elseif (!empty($whereCondition)) {
			return self::_get_one_value($columnName, $whereCondition);
		}
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function hasId() {
		return !empty($this->id);
	}

	/**
	 * Update a table record in the database
	 *
	 * @param  array  $data           - Array of data to be updated
	 * @param  array  $conditionValue - Key value pair for the where clause of the query
	 *
	 * @return Updated object
	 */
	public function update(array $data, array $conditionValue = NULL) {
		if(empty($data)) {
			return FALSE;
		}
		
		if(!empty($this->id)) {
			$conditionValue['id'] = $this->id;
		}

		global $wpdb;
	return $wpdb->update(self::getTableName(), $data, $conditionValue);
	}

	/**
	 * Delete row on the database table
	 *
	 * @param  array  $conditionValue - Key value pair for the where clause of the query
	 *
	 * @return Int - Num rows deleted
	 * @deprecated since version 1.0
	 */
	public static function delete($whereCondition) {
		global $wpdb;
		$wpdb->query('DELETE FROM '.self::getTableName(). ' WHERE '.$whereCondition);
		return TRUE;
	}

}
?>