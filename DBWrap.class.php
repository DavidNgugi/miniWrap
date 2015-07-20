<?php 
/**
 * MysqliDb Class
 *
 * @category  PHP Database Access
 * @package   miniWrap
 * @author    David Ngugi <ndavidngugi@gmail.com>
 * @copyright Copyright (c) 2015
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @version   2.1
 **/
require_once "DB.php";
require_once "config/config.php";

class DBWrap
{
	/**
     * General DB data variables
     *
     */
	private $_results = Array(),
			$_count = 0;
			
	Public static $_log = Array();

	Private static $arrResults = Array();
	/**
     * DB connection
     *
     * @var Object
     */
    protected $_dsn;
    /**
     * DB instance
     *
     * @var Object
     */
    protected $_con;
    /**
     * The SQL Query Type to be prepared and executed Select(s) or Select Single(ss)
     *
     * @var String
     */
    protected $_QueryType;

     /**
     * The SQL statement
     *
     * @var String
     */
    protected $_sql;

    /**
	 * Variable holding the class Instance
	 *
	 * @var Object
	 **/
	Public static $instance;

	/**
	 * Class Instantiation function to track instances
	 *
	 * @return Object
	 **/	
	Public static function start(){
		if (!isset(self::$instance)) {
			self::$instance = new DBWrap();
		}
		return self::$instance;
	}

	/**
	 * Constructor function
	 *
	 * @return void
	 **/
	Public function __construct(){
		$this->_dsn = "".DRIVER."://".USER.":".PASSWORD."@".HOST."/".DB."";
		$this->_con = DB::connect($this->_dsn);
		if (DB::isError($this->_con)) {
			die( "Could not connect : ". $this->_con->getMessage() . " \n");
		}
	}

	/**
	 * Reset function
	 *
	 * @return void
	 **/
	Protected function reset(){
		$this->_results = [];
	}
	
	/**
	 * Get Results function
	 *
	 * @return String
	 **/
	Public function results(){
		return $this->_results;
	}

	/**
	 *  DB Query function
	 *	@param $stmt (String) The Query statement
	 *	@param $type (String) The type of query SELECT * (s) , SELECT single (ss) or any other if left null
	 *	@param $callback (Function) Callback function
	 * @return Object
	 **/
	Public function Query($stmt, $type, $callback){
		
		$this->_sql = strip_tags($stmt);
		$this->_QueryType = mb_strtolower($type);
		try {
			// Enable callback functionality
			if (is_callable($callback)) { call_user_func( $callback, $this->QueryBuilder() ); }
			$this->reset();
		}catch(Exception $e){
			die();
			array_push(self::$_log, $e->getMessage());
		}
	}

	/**
	 *  DB QueryBuilder function
	 *
	 * @return Array
	 **/
	Protected function QueryBuilder(){

		$this->_con->setFetchMode(DB_FETCHMODE_OBJECT); // Get data as Objects

		if (isset($this->_QueryType) && is_string($this->_QueryType)) {
			try{
				switch ($this->_QueryType) {
					// SELECT statement (SELECT * )
					case 's':
						$result =& $this->_con->query($this->_sql);
						while($data = $result->fetchRow()){ array_push($this->_results, $data); }
						return self::convertToArray($this->_results); $this->reset(); break;
					// SELECT SINGLE statement (SELECT with WHERE)
					case 'ss':
						$data =& $this->_con->query($this->_sql)->fetchRow();
						return ($this->_con->query($this->_sql)->numRows() == 1) ? self::convertToArray($data) : null;
						$this->reset(); break;
						// FOR INSERT, UPDATE or DELETE statements
					default:
						$data =& $this->_con->query($this->_sql);
						return (DB::isError($data)) ? array_push(self::$_log,  $e->getMessage()) : array_push(self::$_log, "Query was successful");
						break;
				}
			}catch(Exception $e){
				die();
				array_push(self::$_log, $e->getMessage());
			}
		}
	}


	/**
	 *  DB Utility function
	 *	Convert Objects in data Array and Objects to Arrays (Create hash table)
	 *	@param @dataset The Result array from Query()
	 * @return Array
	 **/
	Protected static function convertToArray($dataset){
		$arr = ( ( is_array($dataset) || is_object($dataset) ) && (sizeof($dataset) >= 1) ) ? $dataset : array();
		foreach ($arr as $key => $value) {
			array_push(self::$arrResults, (is_object($dataset)) ? $value : (array)$value); 
		}
		return self::$arrResults;
	}
			
	/**
	 *  DB getColumns function
	 *
	 * @return Array
	 **/
	Public static function get(){
		return self::$arrResults;
	}

	/**
	 *  DB Utility function
	 *	Check if Arrays are multidimentional or not
	 *	@param $a (Array) Any Array (Flat or Multidimensional)
	 * @return Boolean
	 **/
	// Protected static function is_multi($a){
	// 	foreach($a as $value) { 
	// 		return ( is_array($value) ) ? true : false;
	// 	}
		
	// }

	/**
	 *  DB Utility function
	 *	Replace numerical keys to human-readable ones
	 *	@param $arr (Array) Result array of Select Single (ss) Query
	 *	@param $columns (Array) Array with the table columns
	 * @return Array
	 **/
	Public static function replace_keys($arr, $columns){
		if (is_array($columns) && (sizeof($arr) == sizeof($columns))) {
			$keys = array_keys($columns); $i = 0;
			while ($i < sizeof($arr)) {
				foreach ($columns as $v) {
					$keys[$i] = $v;
					$arr = array_combine($keys, $arr);
					$i++;
				}
			}
		}
		
		return $arr;	
	}

	/**
	 * GetLastError function
	 *	Simply Extracts the last error from $_log Array
	 * @return String
	 **/
	Public static function getLastLog(){
		$log = array_pop(self::$_log);
		return ( empty($log) ) ? "No Message logged" : $log ;
	}

	/**
	 *  DB Utility function
	 *	Extract keys from multidimensional array and return array with key values
	 *	@param $multiArr (Array) Multidimensional Array
	 * @return Array
	 **/
	// Protected static function extractKeys($multiArr){
	// 	$keyArr = array();
	// 	if (isset($multiArr) && self::is_multi($multiArr)) {
	// 		foreach ($multiArr as $key => $value) {
	// 			array_push($keyArr, $value);
	// 		}
	// 	}
	// 	return $keyArr;
	// }





}

?>
