<?php 

/**
 * Database Configuration file
 **/

$config = Array
		(
			"DRIVER" => "mysqli", // Type of DB ( mysqli(default), Postgres, SQLite, iBase)
			"HOST" => "", // Host server URL
			"USER" => "",	// User credential for connection
			"PASSWORD" => "",	// Server password if set
			"DB" => ""	// 
		);
/*
 *	Create configuration constants
 *
*/
foreach ($config as $key => $value) {
	define($key, $value);
}


?>
