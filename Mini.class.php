<?php 
/**
 * Mini Class
 *
 * @category  PHP Database Access
 * @package   miniWrap
 * @author    David Ngugi <ndavidngugi@gmail.com>
 * @copyright Copyright (c) 2015
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @version   1.0.0
 **/

// Load PEAR::DB DB API file
require_once ("DB.php");

class Mini
{
	/**
     * Database instance
     *
     * @var Resurce Object
     */
	Protected static $_instance;
	/**
     * Database Connection String
     *
     * @var String
     */
	Protected $_dsn = null;
	/**
     * Database Connection Object
     *
     * @var Resource
     */
	Protected $_con = null;
	/**
     * Database credentials
     *
     * @var string
     */
	Protected $driver = 'mysqli'; // Currently Supports mysqli only (Tested)
    Protected $host = 'localhost';
    Protected $username = 'root';
    Protected $password = '';
    Protected $db = '';
    /**
     * Contains row count from query
     *
     * @var Integer
     **/
    Protected static $prefix = null;
    /**
     * Holds user-defined SQL query
     *
     * @var string
     */
	Public $_query = null;
	/**
     * Holds all executed SQL queries
     *
     * @var Array
     */
	Protected $_queries = Array();
	/**
     * Holds results from query
     *
     * @var Array
     */
	Protected $_results = Array();
		/**
     * Holds columns to be fetched
     *
     * @var Array
     */
	Protected $_columns = Array();
	  /**
     * Holds type of DB Fetch mode (ASSOC or OBJECT)
     *
     * @var string
     */
	Protected $_fetchMode = DB_FETCHMODE_ASSOC;
	/**
     * Contains Logs of all errors encountered
     *
     * @var Array
     */
	Protected $_errors= Array();
	/**
     * Contains data for ROW-specific queries
     *
     * @var string
     */
	Protected $_where = Array();
	/**
	 * Contains row count from query
	 *
	 * @var Integer
	 **/
	Protected $_count = 0;
	/**
	 * Contains rows to return from query
	 *
	 * @var Integer
	 **/
	Protected $_rowsToGet = null;
	/**
	 * Checks if user has set a WHERE on query results
	 *
	 * Prevents collisions with normal SELECT * queries via get()
	 *
	 * @var Boolean
	 **/
	Protected $_whereSet = false;

	/**
	 * Contains affected rows count from query
	 *
	 * @var Integer
	 **/
	Protected $_affectedRows = 0;	
	/**
	 * Contains the table name
	 *
	 * @var String
	 **/
	Protected $_tableName = null;
	

	 /**
	 * Constructor function
     * @return Void
     */
	Public function __construct(){
		try {
			if (!PEAR::loadExtension($this->driver)) {
				array_push($this->_errors, "<div id ='error'>DB Driver could not be loaded!</div>");
            	die($this->getLastError());
        	}
			$this->_dsn = "".$this->driver."://".$this->username.":".$this->password."@".$this->host."/".$this->db."";

			$this->_con =& DB::connect($this->_dsn);

			if (PEAR::isError($this->_con)) {
				array_push($this->_errors, $this->_con->getMessage());
				die($this->getLastError());
			}else{
				$this->_con->setFetchMode($this->_fetchMode);
			}

			self::$_instance = $this;
			
		}catch(Exception $e){
			array_push($this->_errors, $e->getMessage());
		}
	}

	/**
     * A method of returning the static instance to allow access to the
     * instantiated object from within another class.
     * Inheriting this class would require reloading connection info.
     *
     * @uses $db = Mini::getInstance();
     *
     * @return object Returns the current instance.
     */
    Public static function getInstance(){
        return self::$_instance;
    }

    /**
	 * Reset function
	 *
	 * @return void
	 **/
	Protected function reset(){
		$this->_tablename = null;
		$this->_rowsToGet = null;
		$this->_query = null;
		$this->_errors= Array();
		$this->_where = Array();
		$this->_queries = Array();
		$this->_count = 0;
		$this->_affectedRows = 0;
		$this->_whereSet = false;
        $this->_results = Array();
	}

	/**
	 * Function to se the default table prefix (if you have one that is)
	 * @param String $tbl_prefix The prefix used for your db tables. ( _ , my , tbl , ...)
	 * @return Void
	 **/
	Public function setPrefix($tbl_prefix){
		self::$prefix = (!empty($tbl_prefix) && is_string($tbl_prefix)) ? filter_var ($tbl_prefix, FILTER_SANITIZE_STRING,
                                    FILTER_FLAG_NO_ENCODE_QUOTES) : null;
	}

	/**
	 * Function to set the default data fetching mode (ASSOC or OBJECT) 
	 * @param String $mode fetch mode for query results
	 * @return void
	 **/
	Public function fetchMode($mode){
		if (!empty($mode) && is_string($mode)) 
		 	$this->_fetchMode = $this->map($mode);
	}
	

	/**
	 * Function for writing generic queries
	 * @param String $sql the SQL statement to be queried
	 * @return Object
	 **/
	Public function generic($sql){
        if (!empty($sql) && is_string($sql)) {
            $this->reset();
            $this->_query = $sql;
        }
		return $this;
	}

	/**
     * A convenient SELECT * function.
     *
     * @param string  $tableName The name of the database table to work with.
     * @param integer|array $numRows Array to define SQL limit in format Array ($count, $offset)
     *                               or only $count
     *	Adopted from MysqliDB Wrapper 
     *
     * @return Object
     */
    Public function selectFrom($tableName, $numRows = null, $columns = '*')
    {
    	$this->reset(); 
        if (empty ($columns))
            $columns = '*';

        array_push($this->_columns, $columns);
        // Prepare column string for query
        $column = is_array($columns) ? implode(', ', $columns) : $columns; 
        $this->_tableName = self::$prefix . $tableName;
        $this->_query = 'SELECT ' .
                        $column . " FROM " . $this->_tableName;
        if(isset($numRows) && is_integer($numRows))
       		$this->_rowsToGet = $numRows;
        
        return $this;
    }

    /**
     * A convenient Insert function
     * @param String $tableName The name of the database table to work with.
     * @param Array $colVal Column and value pairs
     * @return Object
     **/
    Public function insertInto($tableName, $colVal){
    	$this->reset();
    	if(!$this->walk($colVal)){
	    	$columns = array_keys($colVal);
			$values = array_values($colVal); 
			$this->_tableName = self::$prefix . $tableName;
			$column = is_array($columns) ? implode(', ', $columns) : $columns; 
			$data = is_array($values) ? "'".implode("' , ' ", $values)."'" : $this->SQLEscape($values); 
			$this->_query = "INSERT INTO ".$this->_tableName."(".$column.") VALUES(".$data.")";
			$this->execute();
			$this->_affectedRows += $this->_con->affectedRows();
			return $this;
		}else{
			$err = "<div id = 'warning'>[Warning] : Invalid column-value pair provided</div>";
	   		array_push($this->_errors, $err);
	   		return;
		}
		
    }

     /**
     * A convenient Update function
     * @param String $tableName The name of the database table to work with.
     * @param Array $colVal Column and value pairs
     * @return Object
     **/
    Public function update($tableName, $colVal){
    	$this->reset();
    	if(!$this->walk($colVal)){
            
    		$this->_tableName = self::$prefix . $tableName;
    		
    		$len = sizeof($colVal);
			$columns = array_keys($colVal);
			$values = array_values($colVal); 

			$q = "UPDATE ". $this->_tableName . " SET "; 
			$i = 0; 
   			while ($i < $len) :
	    		$q .= $this->SQLEscape($columns[$i])." = '".$this->SQLEscape($values[$i])."'";

	    		if ($i != $len-1) 
	    			$q .= " , ";
	    			
	    		$i++;
	    	endwhile;

			$this->_query .= $q;	

    	}
    	return $this;
    }

    /**
     * A convenient Delete function
     * @param String $tableName The name of the database table to work with.
     * @return Object
     **/
    Public function deleteFrom($tableName){
    	$this->reset();
    	$this->_tableName = $tableName;
    	$this->_query = "DELETE FROM ".$this->SQLEscape($this->_tableName)." ";
    	return $this;
    }

/******************************************* WHERE FUNCTIONS *************************************************/
    /**
     * A convenient SELECT...WHERE function
     * @param Array $colVal Column and value pairs
     * @param String $op condition in multiple where cases (AND , OR)
     * @return Object
     **/
    Public function Where($colVal, $op){
    	$this->_whereSet = true; 

    	if (!$this->walk()) {
    		
   			$columns = array_keys($colVal);
		    $values = array_values($colVal); 
		   	$len = sizeof($columns); 

			$q = " WHERE "; $i = 0;
			
   			while ($i < $len) :
	    		$q .= $this->SQLEscape($columns[$i])."= '".$this->SQLEscape($values[$i])."'";

	    		if ($i != $len-1) 
	    			$q .= " ".$this->$op." ";
	    			
	    		$i++;
	    	endwhile;

			$this->_query .= $q;

		}else{
			echo "<div id = 'warning'>[Warning] : Incorrect syntaxt on where() arg[0]</div><br>";
   			$err = "[Warning] : Incorrect syntaxt on where() arg[0]";
   			array_push($this->_errors, $err);
		}

		return $this;
    }


     /**
     * Convenient single column Where functions for single Items
     * Evaluate a single value 
     * @param String $column The column name 
     * @param String $value The value to query for
     * @return Object
     **/
    Public function whereOne($column, $value){
    	if (!empty($column) && !empty($value)) {
    		$this->_whereSet = true; 
    		$this->_query .= " WHERE ". $this->SQLEscape($column) . " = '".$this->SQLEscape($value)."'";
    	}
    	return $this;
    }

     /**
     * Convenient OR...WHERE for single Items
     * Appends an OR to query
     * @param String $column The column name 
     * @param String $value The value to query for
     * @return Object
     **/
    Public function orWhere($column, $value){
    	if (!empty($column) && !empty($value)) {
    		$this->_whereSet = true; 
    		$this->_query .= " OR ". $this->SQLEscape($column) . " = '".$this->SQLEscape($value)."'";
    	}
    	return $this;
    }

      /**
     * Convenient AND...WHERE for single Items
     * 	Appends an AND to query
     * @param String $column The column name 
     * @param String $value The value to query for
     * @return Object
     **/
    Public function andWhere($column, $value){
    	if (!empty($column) && !empty($value)) {
    		$this->_whereSet = true; 
    		$this->_query .= " AND ". $this->SQLEscape($column) . " = '".$this->SQLEscape($value)."'";
    	}
    	return $this;
    }

/***************************************** LIKE FUNCTIONS ************************************************/
	/**
	 * A convenient Like function
	 * @param String $column The column name 
     * @param String $value The value to query for
     * @param String $option Where to place percentage signs % ( %before, after%, %both%) 
	 * @return Object
	 **/
	Public function like($column, $value, $option){
		if (!empty($column) && !empty($value) && !empty($option)) {
			$where = ($this->_whereSet) ? ' ' : ' WHERE '; 
			if ($option == "before") {
				$this->_query .= $where. $this->SQLEscape($column) . " LIKE '%".$this->SQLEscape($value)."'";
			}else if($option == "after"){
				$this->_query .= $where. $this->SQLEscape($column) . " LIKE '".$this->SQLEscape($value)."%'";
			}else if($option == "both"){
				$this->_query .= $where. $this->SQLEscape($column) . " LIKE '%".$this->SQLEscape($value)."%'";
			}else{
				$this->_query .= $where. $this->SQLEscape($column) . " LIKE '%".$this->SQLEscape($value)."%'";
			}
		}
		return $this;
	}

	/**
	 * A convenient Like function
	 *	Appends and OR to query
     * @param String $column The column name 
     * @param String $value The value to query for
     * @param String $option Where to place percentage signs % ( %before, after%, %both%)
	 * @return Object
	 **/
	Public function orLike($column, $value, $option){
		if (!empty($column) && !empty($value) && !empty($option)) {
			if ($option == "before") {
				$this->_query .= " OR ". $this->SQLEscape($column) . " LIKE '%".$this->SQLEscape($value)."'";
			}else if($option == "after"){
				$this->_query .= " OR ". $this->SQLEscape($column) . " LIKE '".$this->SQLEscape($value)."%'";
			}else if($option == "both"){
				$this->_query .= " OR ". $this->SQLEscape($column) . " LIKE '%".$this->SQLEscape($value)."%'";
			}else{
				$this->_query .= " OR ". $this->SQLEscape($column) . " LIKE '%".$this->SQLEscape($value)."%'";
			}
		}
		return $this;
	}

	/**
	 * A convenient Like function
	 *	Appends AND to query
     * @param String $column The column name 
     * @param String $value The value to query for
     * @param String $option Where to place percentage signs % ( %before, after%, %both%)
	 * @return Object
	 **/
	Public function andLike($column, $value, $option){
		if (!empty($column) && !empty($value) && !empty($option)) {
			if ($option == "before") {
				$this->_query .= " AND ". $this->SQLEscape($column) . " LIKE '%".$this->SQLEscape($value)."'";
			}else if($option == "after"){
				$this->_query .= " AND ". $this->SQLEscape($column) . " LIKE '".$this->SQLEscape($value)."%'";
			}else if($option == "both"){
				$this->_query .= " AND ". $this->SQLEscape($column) . " LIKE '%".$this->SQLEscape($value)."%'";
			}else{
				$this->_query .= " AND ". $this->SQLEscape($column) . " LIKE '%".$this->SQLEscape($value)."%'";
			}
		}
		return $this;
	}

/***************************************** GROUPING && ORDERING FUNCTIONS ************************************************/
	/**
     * Start group function
     * Adds an openning bracket to the query string
     * @return Object
     **/
    // Public function startGroup(){
    // 	$this->_query .= " ( ";
    // 	return $this;
    // }

    /**
     * Ends group function 
     * Adds a closing bracket to the query string
     * @return Object
     **/
    // Public function endGroup(){
    // 	$this->_query .= " ) ";
    // 	return $this;
    // }

    /**
     * Group BY function 
     * Appends a GROUP BY column1, column2 ... to the query string
     * @param Array|String $cols Column(s) to group query results by 
     * @return Object
     **/
    Public function groupBy($cols){
    	$columns = (is_array($cols)) ? implode(',', $cols) : $cols;
    	$this->_query .= " GROUP BY ". $columns;
    	return $this;
    }

     /**
     * Order BY function 
     * Appends a ORDER BY ASC or DESC to the query string
     * @param Array|String $col Column(s) to order query results by
     * @param String $method Either ASC , DESC or RANDOM([0...])
     * @return Object
     **/
    Public function orderBy($col, $method = 'DESC'){
    	$column = (!empty($col) && is_string($col)) ? $col : '';
    	$m = $this->toUpper($method);
    	$opt = ( (!empty($m) && is_string($m)) ) ? $m : "DESC";
    	$this->_query .= " ORDER BY ". $column." ".$opt;
    	return $this;
    }



/******************************************* LIMIT FUNCTION ***********************************************/
    /**
     * Builds the LIMIT part of a Query statement
     *
     * @return String
     **/
    Protected function Limit(){
    	if ($this->_rowsToGet != null)
    		 return ' LIMIT '. $this->_rowsToGet;
    	else
    		return '';
    }

/*******************************************ROW FUNCTIONS***********************************************/
    /**
     * Returns the results of a select query in Array form
     *
     * @return Array
     **/
    Public function fetchRows(){
    	try{
    		$this->_con->setFetchMode($this->_fetchMode);
    		$result = $this->execute();
    		$this->_affectedRows += $this->_con->affectedRows();
    	
    		if ($this->_rowsToGet > 1 || $this->_rowsToGet == null ) {
    			$i = 0;
    			while($res = $result->fetchRow()):
		    		array_push($this->_results, $res);
		    		$i++;
		       	endwhile;
    		}else if($this->_rowsToGet == 1){
    			$res = $result->fetchRow();
    			array_push($this->_results, $res);
    		}

		    return $this->_results;
		    $this->reset();

	    }catch(Exception $e){
	    	array_push($this->_errors, $e->getMessage());
	    }
    }


/************************************** EXECUTION FUNCTIONS *************************************************/
     /**
     * Execute Single query  
     * 
     * @return DB Object
     **/
    Protected function execute(){
    	try{
    		$this->_con->autoCommit(false);
    		array_push($this->_queries, $this->_query);
	    	$res =& $this->_con->query($this->_query);
	    	$this->_con->commit();
	    	if (PEAR::isError($res)) {
	    		$this->_con->rollback();
	    		array_push($this->_errors, $res->getMessage());
	    		echo "<div id = 'error'>[At commit] DB Error Msg : ".$res->getMessage()."<br><br>Query : ".$this->_query."</div>";
	    		exit;
	    	}else{
	    		return $res;
	    	}
	    }catch(Exception $e){
	    	array_push($this->_errors, $e->getMessage());
	    }
    }

    /**
     * Function to execute chained queries
     *
     * @return Object
     **/
    Public function exec(){
    	$this->_query .= $this->Limit();
    	$this->execute();
    	return $this;
    }

/************************************** DB UTILITY FUNCTIONS *************************************************/
 	
 	/**
     * Get number of rows in table from query
     * @return Integer 
     **/
    Public function getRowCount(){
    	return $this->_count = $this->execute()->numRows();
    }

     /**
     * Get number of affected rows 
     * @return Integer 
     **/
    Public function getAffectedRows(){
    	return $this->_affectedRows;
    }
 	/**
     * Execute query  
     * @param String $setmode mode of query (ASSOC or OBJECT)
     * @return DB Object
     **/
    Protected function map($setmode){
    	$setmode = strtoupper($setmode);
    	if ( strcmp($setmode, 'OBJECT') == 1) {
    		return DB_FETCHMODE_OBJECT;
    	}else{
    		return DB_FETCHMODE_ASSOC;
    	}
    }

    /**
     * Function that gets the last error encountered
     * 
     * @return String
     **/
    Public function getLastError(){
    	return (count($this->_errors) > 0) ? array_pop($this->_errors) : "No errors found!";
    }

    /**
     * Function to get the last executed query  
     * 
     * @return String
     **/
    Public function getLastQuery(){
    	return array_pop($this->_queries);
    }

    /**
	 *  DB Utility function
	 *	Clean string and Escape SQL Injection 
	 *	@param String $data column or value to be placed in SQL query
	 * @return String Cleaned
	 **/
    Protected function SQLEscape($data){
    	$data = htmlentities(strip_tags($data));
    	return $this->_con->escapeSimple($data);
    }

/************************************** ARRAY UTILITY FUNCTIONS *************************************************/
    /**
     * Function to find any empty key or value in an array
     * @param Array $arr Array to search empty values in
     * @return Boolean
     **/
    Protected function walk($arr){
    	$emptyItems = array(); $len = sizeof($arr);
    	if (is_array($arr) && $len > 0) {	
	    	if ($this->is_multi($arr)) {
	    		foreach($arr as $key => $value){
		    		foreach($value as $k => $val) :
				   		if ( empty($k) || empty($val)) {
				   			$keyVal = "[".$k."][".$val."]";
				   			array_push($emptyItems, $keyVal);
				   		}
			   		endforeach;
			   	}
	    	}else{
	    		if ($this->is_assoc($arr)) {
	    			foreach($arr as $key => $value) :
				   		if ( empty($key) || empty($value)) {
				   			$keyVal = "[".$key."][".$value."]";
				   			array_push($emptyItems, $keyVal);
				   		}
			   		endforeach;
	    		}else{
	    			$i = 0;
	    			while ($i < $len) {
		    			if (empty($arr[$i])) {
		    				array_push($emptyItems, $arr[$i]);
		    			} 
		    			$i++;
		    		}
	    		}
	    		
	    	}
	    }
	   	return (sizeof($emptyItems) > 0) ? true : false;
    }
    /**
     * Function to find out whether an 1D array is associative or not
     * @param Array $arr 
     * @return Boolean
     **/
    Protected function is_assoc($arr){
		foreach ($arr as $key => $value) {
			if (is_numeric($key)) {
				return false;
				break;
			}else{
				return true;
				break;
			}
		}
    }
    /**
     * Function to match columns of a get() and where() fn 
     * Not implemented. I wanted to use it but found it impractical for current use
     * @param Array $cols First array
     * @param Array $colsToMatch Second Array 
     * @return Boolean
     **/
    Protected function matchColumns($cols, $colsToMatch){
    	$result = array_diff($cols, $colsToMatch);
    	return (sizeof($result) > 0) ? false : true;
    }

    /**
	 *  DB Utility function
	 *	Check if Arrays are multidimentional 2D Array or not
	 *	@param Array $a Any Array (Flat or Multidimensional)
	 * @return Boolean
	 **/
	Protected function is_multi($a){
		foreach($a as $value) { 
			return ( is_array($value) ) ? true : false;
		}
	}

/************************************** STRING UTILITY FUNCTIONS *************************************************/

	/**
	 *  DB Utility function
	 *	Convert passed to string to Uppercaase
	 *	@param $str (String) 
	 * @return String
	 **/
	Protected function toUpper($str){
		return (!empty($str) && is_string($str)) ? mb_strtoupper(mb_strtolower($str)) : $str;
		
	}

}

?>