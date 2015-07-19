<?php 

/**
 * Database Configuration file
 **/

$config = Array
		(
			"DRIVER" => "mysqli", // Type of DB (MySQL, Postgres, SQLite, iBase)
			"HOST" => "localhost", // Host server URL
			"USER" => "root",	// User credential for connection
			"PASSWORD" => "",	// Server password if set
			"DB" => "register"	//	
		);
/*
 *	Create configuration constants
 *
*/
foreach ($config as $key => $value) {
	define($key, $value);
}


?>