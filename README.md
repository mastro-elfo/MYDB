# MYDB

Comments in code

## MYDB::last_query
The last executed query.

## MYDB::query
Sends a generic query. Also saves the query string to `$this->last_query`.

### Params
* string `$query` The query string

### Return
Returns `false` on failure. For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries will return a `mysqli_result` object. For other successful queries will return `true`. See also [http://php.net/manual/en/mysqli.query.php](http://php.net/manual/en/mysqli.query.php).

## MYDB::insert
Creates and sends an 'INSERT INTO' query.

### Params
* string `$table` The table name
* array	`$data` Associative array. Keys are field names, Values are field values.

### Return
The last insert id on success, 0 on failure

### Example

```php
$db->insert('Table', [
	'Field1' => 'Value1',
	'Field2' => 'Value2'
]);
```

## MYDB::update
Creates and sends an 'UPDATE' query.

### Params
* string `$table` The table name
* array `$data` Associative array. Keys are field names, Values are field values.
* array `$where` WHERE clause. Items are joined with AND.

### Return
`true` on success, `false` on failure

## MYDB::upsert
Create and sends an 'INSERT INTO' query. On duplicate key sends an 'UPDATE' query.

### Params
* string `$table` The table name
* array `$data` Associative array. Keys are field names, Values are field values.
* array `$where` WHERE clause. Items are joined with AND.

### Return
Returns `false` on failure. For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries will return a `mysqli_result` object. For other successful queries will return `true`. See also [http://php.net/manual/en/mysqli.query.php](http://php.net/manual/en/mysqli.query.php).

## MYDB::select
Create and sends a 'SELECT' query.

### Params
* string `$table` Table name
* mixed `$fields` array of fields names or `'*'` for all
* array `$where`  WHERE clause. Items are joined with AND.
* array `$orderby` ORDER BY clause. `[field_name => 'ASC|DESC', ...]`
* mixed `$limit` LIMIT clause. Use one number to limit results number or two comma separated numbers for paging.

### Return
For successful queries return a `mysqli_result` object. Returns `false` on failure.

## MYDB::delete
Creates and sends a 'DELETE' query.

### Params
* string `$table` Table name
* array `$where`  WHERE clause. Items are joined with AND.

### Return
`true` on success, `false` on failure