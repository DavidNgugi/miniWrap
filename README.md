MiniWrap for PEAR::DB -- Simple PEAR::DB wrapper

<a href = "https://travis-ci.org/DavidNgugi/miniWrap" title = "Success" target="_blank"><img src="https://travis-ci.org/DavidNgugi/miniWrap.svg?branch=master"/></a>

<hr>

### Table of Contents

**[Installation](#installation)**  
**[Initialization](#initialization)**  
**[Insert Query](#insert-query)**  
**[Update Query](#update-query)**  
**[Select Query](#select-query)**  
**[Delete Query](#delete-query)**
**[Where Method](#where-method)**
**[Like Method](#like-method)**  
**[Grouping Results](#grouping-results)** 
**[Ordering Results](#ordering-results)**
**[Getting Row Count](#getting-row-count)**
**[Error Handling](#error-handling)**   
**[The Future](#the-future)**

### Installation

You can install via composer 

```bash
composer require davidngugi/miniwrap
```

### Initialization
Setup the Host, User, Password, DB, table prefix constants in the MiniWrap class file.
Simple initialization with utf8 charset by default:

```php

use MiniWrap\Mini;

$con = Mini::getInstance();
```

or

```php

use MiniWrap\Mini;

$con = new Mini();
```

### Query types
One can use the pre-built methods in MiniWrap or use the generic() method that allows you to write raw SQL statements.
Example:

```php
$q = $con->generic("SELECT * FROM Users WHERE user_id = 1")->exec();
```

Method chaining is allowed for methods that form a query. E.g. Select and Where, update and where, select and orderBy etc

### Insert Query
Simple example
The insertInto() method takes 2 arguments:
1. Table name
2. Array with Column Value pair

```php
$columnValues = Array(
						"column1" => "value1",
						"column1" => "value1"
					);

$q = $con->insertInto("tablename", $columnValues)->exec();
```

### Update Query
Similar to the insertInto() method

```php
$columnValues = Array(
						"column1" => "value1",
						"column1" => "value1"
					);

$q = $con->update("tablename", $columnValues)->exec();

```

### Select Query
Mini uses the selectFrom() Method that takes 3 arguments. The first being the tablename, second is the number of rows (LIMIT) and the third is the column(s).
Multiple columns can be inputed as a one-dimensional array.
The fetchRows() method is used to retrieve an associative Array of results
A SELECT statement can be of several forms:

1. For all columns (place an Asterisk as the 3rd argument)
```php
$q = $con->selectFrom("tablename", null, "*")->exec();
if($q){
	$results = $q->fetchRows();
	foreach($results as $r):
		// Do Something
	endforeach;
}
```

2. For a single column
```php
$q = $con->selectFrom("tablename", null, "columnname")->exec();
if($q){
	$results = $q->fetchRows();
	foreach($results as $r):
		// Do Something
	endforeach;
}
```

3. For several specified columns (Use a 1D Array)
```php
$cols = Array("column1","column2");
$q = $con->selectFrom("tablename", null, $cols)->exec();
if($q){
	$results = $q->fetchRows();
	foreach($results as $r):
		// Do Something
	endforeach;
}
```

### Delete Query
```php
$q = $con->deleteFrom("tablename")->where("column","value")->exec();
```

### Where Method
In several forms:
1. Where(Array[..], type) - For multiple column Where with similar conditioning (AND, OR)
```php
$cols = Array("column1","column2");
$columnValues = Array(
						"column1" => "value1",
						"column1" => "value1"
					);

$q = $con->selectFrom("tablename", null, $cols)->whereMany($columnValues, "AND")->exec();
if($q){
	$results = $q->fetchRows();
	foreach($results as $r):
		// Do Something
	endforeach;
}
```
2. where("column", "conditon", "value") - Single column conditional
- The conditon should be one of the following (=, >=, <=, >, <, !=). Default is =

```php
$cols = Array("column1","column2");
$q = $con->selectFrom("tablename", null, $cols)->where("column", "!=", "value")->exec();
if($q){
	$results = $q->fetchRows();
	foreach($results as $r):
		// Do Something
	endforeach;
}
```

3. andWhere("column","value") - used after whereMany(), where() or orWhere()
- Also takes a conditon that should be one of the following (=, >=, <=, >, <, !=). Default is =
```php
$cols = Array("column1","column2");
$q = $con->selectFrom("tablename", null, $cols)->where("column1", "value1")->andWhere("column2","value2")->exec();
if($q){
	$results = $q->fetchRows();
	foreach($results as $r):
		// Do Something
	endforeach;
}
```

4. orWhere("column","value") - used after where(), where() or andWhere()
- Also takes a conditon that should be one of the following (=, >=, <=, >, <, !=). Default is =
```php
$cols = Array("column1","column2");
$q = $con->selectFrom("tablename", null, $cols)->where("column1", "value1")->orWhere("column2","value2")->exec();
if($q){
	$results = $q->fetchRows();
	foreach($results as $r):
		// Do Something
	endforeach;
}
```

### Like Method
Comes in several forms:
Your value should be '%value%' or '%value' or 'value%'
1. Like(column, value) - For Single column Like 
```php
$cols = Array("column1","column2");
$q = $con->selectFrom("tablename", null, $cols)->Like("column","%value%")->exec();
if($q){
	$results = $q->fetchRows();
	foreach($results as $r):
		// Do Something
	endforeach;
}
```

2. andLike("column", "value") - used after like() and orLike()

```php
$cols = Array("column1","column2");
$q = $con->selectFrom("tablename", null, $cols)->Like("column","%value")->andLike("column2","%value%")->exec();
if($q){
	$results = $q->fetchRows();
	foreach($results as $r):
		// Do Something
	endforeach;
}
```
4. orLike("column","value") - used after like() and andLike()
```php
$cols = Array("column1","column2", "column3");
$q = $con->selectFrom("tablename", null, $cols)->Like("column","%value%")->orLike("column2","%value")->orLike("column3","value%")->exec();
if($q){
	$results = $q->fetchRows();
	foreach($results as $r):
		// Do Something
	endforeach;
}
```

###	Grouping Results
```php
$q = $con->selectFrom("tablename", null, "*")->groupBy("column")->exec();
if($q){
	$results = $q->fetchRows();
	foreach($results as $r):
		// Do Something
	endforeach;
}
```

###	Ordering Results
```php
$q = $con->selectFrom("tablename", null, "*")->orderBy("column")->exec();
if($q){
	$results = $q->fetchRows();
	foreach($results as $r):
		// Do Something
	endforeach;
}
```

###	Getting Row Count
Use the getRowCount() Method
```php
$q = $con->selectFrom("tablename", null, "*")->groupBy("column")->exec();
if($q){
	$results = $q->fetchRows();
	if($q->getRowCount() > 1)
		foreach($results as $r):
			// Do Something
		endforeach;
}
```

###	Error handling
A much better error handler is in development but in the meantime, in order to get the generated error, use the getLastError() method. E.g
```php
$q = $con->deleteFrom("tablename")->where("column","value")->exec();
if($q){
	//do something
}else{
	echo $q->getLastError();
}
```

You can also view the last Query using the getLastQuery() Method
```php
$q = $con->deleteFrom("tablename")->where("column","value")->exec();
if($q){
	//do something
}else{
	echo "Error : ". $q->getLastError();
	echo "<br/>";
	echo "Query : ". $q->getLastQuery();
}

```

###	The Future
I'll continue updating as I learn more techniques required in developing a Database Wrapper for PHP.
The following features are up next in my to-do list on this project:

####->Better Error Logging
####->Joins
####->Sub Queries
####->DB Functions
####->Support for more DB Drivers (Currently handling PDO fabulously!)
####->Tests & Query Validation
####->Optimizations

You can reach me on <a href = 'https://twitter.com/DavidNgugi15'>Twitter</a>

### Support

If you would love to support the continuous development and maintenance of this package, please consider buying me a coffee.

<a href = "https://www.buymeacoffee.com/DavidNgugi" title = "Buy Me a Coffee" target="_blank"><img src="https://github.com/DavidNgugi/miniWrap/blob/master/coffee.jpg?raw=true" width="240px" height ="150px"/></a>

# License

This package is open-sourced software licensed under the [MIT Licence](https://github.com/DavidNgugi/miniWrap/blob/master/LICENSE)
