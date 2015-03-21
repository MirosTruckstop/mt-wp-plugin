<?php
/**
 * Abstract class which has helper functions to get data from the database
 */
abstract class MT_Common {

	protected $id;

	public abstract function __toString();
	public abstract static function getTableName();

	/**
     * Constructor for the database class to inject the table name
     *
     * @param String $tableName - The current table name
     */
    public function __construct($tableName, $id) {
        $this->tableName = $tableName;
		$this->id = $id;
    }
	
    /**
     * Insert data into the current data
     *
     * @param  array  $data - Data to enter into the database table
     *
     * @return InsertQuery ID or false Object
     */
    public static function insert(array $data) {
        if(empty($data)) {
            return false;
        }

        global $wpdb;
        $wpdb->insert(static::getTableName(), $data);
        return $wpdb->insert_id;
    }
	
	public function insertAll(array $data) {
		foreach ($data as $item) {
			if(self::insert($item)) {
				return false;
			}
		}
		return true;
	}
	
	public function isDeletable() {
		return false;
	}
	
	public function getOne($select = NULL, $outputType = 'OBJECT') {
		if(!empty($this->id)) {
			$query = (new MT_QueryBuilder())
				->from($this->tableName, $select)
				->whereEqual($this->tableName.'.id', $this->id);
			return $query->getResultOne($outputType);		
		}
		return FALSE;
	}
	
	public static function getAll($select = '*', $orderBy = NULL) {
		$query = (new MT_QueryBuilder())
			->from(static::getTableName(), $select)
			->orderBy($orderBy);
		return $query->getResult();
	}

    /**
     * Get all from the selected table
     *
     * @param  String $orderBy - Order by column name
     *
     * @return Table result
     */
/*    public function get( $select = '*', $orderBy = NULL, $limit = NULL, $selectBelongsTo = NULL ) {
		$query = new MT_QueryBuilder();
		$query->from($this->tableName, $select);
		if(!empty($this->belongsTo)) {
			$query->joinLeft('wp_mt_'.$this->belongsTo, $this->tableName.'.'.$this->belongsTo.'=wp_mt_'.$this->belongsTo.'.id', $selectBelongsTo);
		}
		if(!empty($this->id)) {
			$query->where($this->tableName.'.id = ' . $this->id);
		}
		$query->orderBy($orderBy);
		$query->limit($limit);
		
		//echo $query;
        return $query->getResult($this->output_type);
    }*/

	private static function _get_one_value($select, $whereCondition = NULL) {
		$query = (new MT_QueryBuilder())
			->from(static::getTableName())
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
	 * @global type $wpdb
	 * @param type $aggregateFunctionName Max, min
	 * @param type $columnName
	 * @return type
	 */
	public static function get_aggregate($aggregateFunctionName, $columnName, $whereCondition = NULL) {
		$aggregateValue = self::_get_one_value($aggregateFunctionName.'('.$columnName.')', $whereCondition);
		if($aggregateValue) {
			return $aggregateValue;
		} else {
			return 0;
		}
	}
	
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
        global $wpdb;

        if(empty($data)) {
            return false;
        }
		
		if(!empty($this->id)) {
			$conditionValue['id'] = $this->id;
		}

        return $wpdb->update( $this->tableName, $data, $conditionValue);
    }

    /**
     * Delete row on the database table
     *
     * @param  array  $conditionValue - Key value pair for the where clause of the query
     *
     * @return Int - Num rows deleted
     */
    public static function delete($whereCondition) {
        global $wpdb;
		$wpdb->query('DELETE FROM '.static::getTableName(). ' WHERE '.$whereCondition);
    }

}
?>