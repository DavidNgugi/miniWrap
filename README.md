MiniWrap for PEAR::DB -- Simple PEAR::DB wrapper
<hr>
### Table of Contents
**[Installation](#installation)**  
**[Initialization](#initialization)**  
**[Insert Query](#insert-query)**  
**[Update Query](#update-query)**  
**[Select Query](#select-query)**  
**[Delete Query](#delete-query)**  
**[The Future](#the-future)**

### Installation
To utilize this class, first import DBWrap.class.php into your project, and require it.
Setup your configuration data in the config file

```php
require_once ('config.php');
require_once ('DBWrap.class.php');
```
or Autoload the class with other classes in your projects using spl_autoload_register()

### Initialization
Simple initialization with utf8 charset by default:
```php
$db = DBWrap::start();
```

### Insert Query
Simple example
```php
DBWrap::start()->Query("INSERT INTO users(firstname,lastname,password) VALUES('david','ngugi','mypass')", function(){
	$log = DBWrap::getLastLog();
	print_r($log);
});
```

### Update Query
```php

DBWrap::start()->Query("UPDATE users set firstname = '$firstname', lastname = '$lastname', password = '$password' WHERE user_id = '1')", function(){
	$log = DBWrap::getLastLog();
	print_r($log);
});
```
### Select Query
A SELECT statement can be of two forms (SELECT * or s) or (SELECT SINGLE or ss)
A SELECT * statement returns a multidimensonal array while a SELECT SINGLE statement returns a flat array who's keys have to be replaced with column names before any value can be outputed.

NOTE: Replacing keys isn't mandatory but highly recommended to maintain consistency and readable code

Let's start with a SELECT * statement
The Query will take on 3 parameters. The first is the SQL statement , followed by type of statement (s or ss) the a callback. This returns multiple columns from your database

```php
// Columns to replace

DBWrap::start()->Query("SELECT * FROM users", "s", function(){
	$data = DBWrap::get();
	foreach ($data as $r){
 		echo $r['firstname'];
 	}
});

```

Next is a SELECT SINGLE Query (s), selects a single row in a table
NOTE: The number of columns have to match exactly as the column ccount in your db!

```php
$cols = Array (
	'firstName',
	'lastname',
	'password'
);

DBWrap::start()->Query("SELECT * FROM users", "ss", function(){
 	$data = DBWrap::replace_keys(DBWrap::get(), $cols);
 	foreach ($data as $r){
 		echo $r['firstname'];
 	}
});

```

### Delete Query
```php
DBWrap::start()->Query("DELETE FROM users WHERE firstname = 'david'", function(){
	$log = DBWrap::getLastLog();
	print_r($log);
});
```
###	The Future
As I learn more techniques required in developing a Database Wrapper for PHP, I'll update immediately.
The wrapper obviously needs more abstraction for the end user. The following features are up next in my to-do list on this project:

####->Method chaining
####->where() method
####->has() method
####->hasOne() method
####->Sub Queries
####->DB Functions

You can reach me on <a href = 'https://twitter.com/DavidNgugi15'>Twitter</a>
