MiniWrap for PEAR::DB -- Simple PEAR::DB wrapper
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
To utilize this class, first import Mini.class.php into your project, and require it.
Setup your configuration data in the same class file

```html
<link href="mini.css" rel = "stylesheet"/> 
```
```php
require_once ('Mini.class.php');
```
or Autoload the class with other classes in your projects using spl_autoload_register() or _autoload() functions

### Initialization
Setup the Host, User, Password, DB, table prefix constants in the MiniWrap class file.
Simple initialization with utf8 charset by default:

```php
$con = Mini::getInstance();
```
or

```php
$con = new Mini();
```
###Query types
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
$q = $con->deleteFrom("tablename")->whereOne("column","value")->exec();
```

###Where Method
In several forms:
1. Where(Array[..], type) - For multiple column Where with similar conditioning (AND, OR)
```php
$cols = Array("column1","column2");
$columnValues = Array(
						"column1" => "value1",
						"column1" => "value1"
					);

$q = $con->selectFrom("tablename", null, $cols)->Where($columnValues, "AND")->exec();
if($q){
	$results = $q->fetchRows();
	foreach($results as $r):
		// Do Something
	endforeach;
}
```
2. whereOne("column", "value") - Single column conditional

```php
$cols = Array("column1","column2");
$q = $con->selectFrom("tablename", null, $cols)->whereOne("column", "value")->exec();
if($q){
	$results = $q->fetchRows();
	foreach($results as $r):
		// Do Something
	endforeach;
}
```
3. andWhere("column","value") - used after where(), whereOne() or orWhere()

```php
$cols = Array("column1","column2");
$q = $con->selectFrom("tablename", null, $cols)->whereOne("column1", "value1")->andWhere("column2","value2")->exec();
if($q){
	$results = $q->fetchRows();
	foreach($results as $r):
		// Do Something
	endforeach;
}
```
4. orWhere("column","value") - used after where(), whereOne() or andWhere()
```php
$cols = Array("column1","column2");
$q = $con->selectFrom("tablename", null, $cols)->whereOne("column1", "value1")->orWhere("column2","value2")->exec();
if($q){
	$results = $q->fetchRows();
	foreach($results as $r):
		// Do Something
	endforeach;
}
```

###Like Method
In several forms:
The option argument must be provided. States where to place the percentage sign (%) for regExp functions ("before", "after" or "both")
The default is both. Therefore your value will be '%value%'
1. Like(column, value, option) - For Single column Like 
```php
$cols = Array("column1","column2");
$q = $con->selectFrom("tablename", null, $cols)->Like("column","value", "before")->exec();
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
$q = $con->selectFrom("tablename", null, $cols)->Like("column","value", "before")->andLike("column","value", "both")->exec();
if($q){
	$results = $q->fetchRows();
	foreach($results as $r):
		// Do Something
	endforeach;
}
```
4. orLike("column","value") - used after like() and andLike()
```php
$cols = Array("column1","column2");
$q = $con->selectFrom("tablename", null, $cols)->Like("column","value", "before")->orLike("column","value", "before")->orLike("column","value", "after")->exec();
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
$q = $con->deleteFrom("tablename")->whereOne("column","value")->exec();
if($q){
	//do something
}else{
	echo $q->getLastError();
}
```

You can also view the last Query using the getLastQuery() Method
```php
$q = $con->deleteFrom("tablename")->whereOne("column","value")->exec();
if($q){
	//do something
}else{
	echo "Error : ". $q->getLastError();
	echo "<br/>";
	echo "Query : ". $q->getLastQuery();
}

```
###	The Future
As I learn more techniques required in developing a Database Wrapper for PHP, I'll update immediately.
The following features are up next in my to-do list on this project:

####->Better Error Logging
####->Joins
####->Sub Queries
####->DB Functions
####->Support for more DB Drivers (Currently handling mysqli fabulously!)

You can reach me on <a href = 'https://twitter.com/DavidNgugi15'>Twitter</a>
