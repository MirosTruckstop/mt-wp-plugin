<?php
/**
 * Abstract class which has helper functions to get data from the database
 */
abstract class MT_Common
{
    /**
     * The current table name
     *
     * @var boolean
     */
    private $tableName = false;
	private $output_type = 'OBJECT';
	protected $id;
	protected $belongsTo;
	protected $belongsToOpt;
	
	public function __toString() {
		return 'MT_Common';
	}

	/**
     * Constructor for the database class to inject the table name
     *
     * @param String $tableName - The current table name
     */
    public function __construct($tableName, $id)
    {
        $this->tableName = $tableName;
		$this->id = $id;
    }
	
	public function setOutputType($value) {
		$this->output_type = $value;
	}
	
	public function getTableName() {
		return $this->tableName;
	}

    /**
     * Insert data into the current data
     *
     * @param  array  $data - Data to enter into the database table
     *
     * @return InsertQuery ID or false Object
     */
    public function insert(array $data)
    {
        global $wpdb;

        if(empty($data)) {
            return false;
        }

        $wpdb->insert($this->tableName, $data);
        return $wpdb->insert_id;
    }
	
	public function insertAll(array $data) {
		foreach ($data as $item) {
			if($this->insert($item)) {
				return false;
			}
		}
		return true;
	}
	
	public function isDeletable() {
		return false;
	}
	
	public function getOne($select = NULL, $outputType = NULL) {
		if(!empty($this->id)) {
			$query = (new MT_QueryBuilder())
				->from($this->tableName, $select)
				->whereEqual($this->tableName.'.id', $this->id);
			return $query->getResultOne($outputType);		
		}
	}
	
	public function getAll($select = '*', $orderBy = NULL) {
		$query = (new MT_QueryBuilder())
			->from($this->tableName, $select)
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
    public function get( $select = '*', $orderBy = NULL, $limit = NULL, $selectBelongsTo = NULL ) {
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
    }

	private function _get_one_value($select, $whereCondition = NULL) {
		$query = (new MT_QueryBuilder())
			->from($this->tableName)
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
	private function _get_aggregate($aggregateFunctionName, $columnName, $whereCondition) {
		$aggregateValue = $this->_get_one_value($aggregateFunctionName.'('.$columnName.')', $whereCondition);
		if($aggregateValue) {
			return $aggregateValue;
		} else {
			return 0;
		}
	}
	
	public function get_count($columnName, $whereCondition = NULL) {
		return $this->_get_aggregate('COUNT', $columnName, $whereCondition);		
	}
	
	public function get_max($columnName, $whereCondition = NULL) {
		return $this->_get_aggregate('MAX', $columnName, $whereCondition);
	}
	
	public function get_attribute($columnName, $whereCondition = NULL) {
		if (empty($whereCondition) && $this->hasId()) {
			return $this->_get_one_value($columnName, 'id=' . $this->id);
		}
		elseif (!empty($whereCondition)) {
			return $this->_get_one_value($columnName, $whereCondition);
		}
	}
	
	public function check_dataset_exits($whereCondition) {
		//return (!empty($this->_get_one_value('id', $whereCondition)));
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function hasId() {
		return !empty($this->id);
	}

    /**
     * Get a value by a condition
     *
     * @param  Array $conditionValue - A key value pair of the conditions you want to search on
     * @param  String $condition - A string value for the condition of the query default to equals
     *
     * @return Table result
     */
    public function get_by(array $conditionValue, $condition = '=')
    {
        global $wpdb;

        $sql = 'SELECT * FROM `'.$this->tableName.'` WHERE ';

        foreach ($conditionValue as $field => $value) {
            switch(strtolower($condition))
            {
                case 'in':
                    if(!is_array($value))
                    {
                        throw new Exception("Values for IN query must be an array.", 1);
                    }

                    $sql .= $wpdb->prepare('`%s` IN (%s)', $field, implode(',', $value));
                break;

                default:
                    $sql .= $wpdb->prepare('`'.$field.'` '.$condition.' %s', $value);
                break;
            }
        }

        $result = $wpdb->get_results($sql);

        return $result;
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
    public function delete(array $conditionValue) {
        global $wpdb;

        $deleted = $wpdb->delete( $this->tableName, $conditionValue );

        return $deleted;
    }
	
	public function deleteAll($whereCondition) {
		global $wpdb;
		$sql = "DELETE FROM `".$this->tableName."` WHERE ".$whereCondition;	
		$wpdb->query($sql);		
	}
	
	public static function __createTable($tableName, $sql) {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();		
		
		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "mt_$tableName` ($sql) $charset_collate;";	
		$wpdb->query($sql);
	}
}
?>